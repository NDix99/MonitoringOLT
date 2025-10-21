<?php

namespace App\Console\Commands;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\Metric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ndum\LaravelSnmp\Facades\Snmp;

class PollOltData extends Command
{
    protected $signature = 'olt:poll {--olt-id= : Specific OLT ID to poll} {--force : Force polling even if not due}';
    protected $description = 'Poll OLT data via SNMP for ZTE C320 devices';

    // ZTE C320 specific OIDs
    private $oids = [
        'onu_status' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3', // ZXGPON-SERVICE-MIB::zxGponOntPhaseState
        'onu_serial' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2', // ZXGPON-SERVICE-MIB::zxGponOntSerialNumber
        'onu_rx_power' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.4', // ZXGPON-SERVICE-MIB::zxGponOntRxPower
        'onu_tx_power' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.5', // ZXGPON-SERVICE-MIB::zxGponOntTxPower
        'onu_model' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.6', // ZXGPON-SERVICE-MIB::zxGponOntModel
        'onu_vendor' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.7', // ZXGPON-SERVICE-MIB::zxGponOntVendor
    ];

    public function handle()
    {
        $oltId = $this->option('olt-id');
        $force = $this->option('force');

        $query = Olt::where('is_active', true);
        
        if ($oltId) {
            $query->where('id', $oltId);
        }

        $olts = $query->get();

        if ($olts->isEmpty()) {
            $this->error('No active OLTs found to poll.');
            return 1;
        }

        $this->info("Polling {$olts->count()} OLT(s)...");

        foreach ($olts as $olt) {
            if (!$force && !$this->shouldPoll($olt)) {
                $this->line("Skipping OLT {$olt->name} - not due for polling yet.");
                continue;
            }

            $this->pollOlt($olt);
        }

        $this->info('Polling completed.');
        return 0;
    }

    private function shouldPoll(Olt $olt): bool
    {
        $lastPoll = $olt->updated_at;
        $interval = $olt->polling_interval;
        
        return $lastPoll->addSeconds($interval)->isPast();
    }

    private function pollOlt(Olt $olt)
    {
        $this->line("Polling OLT: {$olt->name} ({$olt->ip_address})");

        try {
            // Configure SNMP connection
            $snmp = Snmp::host($olt->ip_address)
                ->community($olt->community_string)
                ->version($olt->snmp_version)
                ->port($olt->snmp_port);

            // Get ONU data
            $this->pollOnuData($snmp, $olt);

            // Update OLT last poll time
            $olt->touch();

            $this->info("✓ Successfully polled OLT {$olt->name}");

        } catch (\Exception $e) {
            $this->error("✗ Failed to poll OLT {$olt->name}: " . $e->getMessage());
            Log::error("SNMP polling failed for OLT {$olt->name}", [
                'olt_id' => $olt->id,
                'ip' => $olt->ip_address,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function pollOnuData($snmp, Olt $olt)
    {
        try {
            // Walk through ONU table to get all ONUs
            $onuData = $snmp->walk($this->oids['onu_status']);

            foreach ($onuData as $oid => $statusValue) {
                // Extract ONU index from OID
                $onuIndex = $this->extractOnuIndex($oid);
                
                if (!$onuIndex) continue;

                // Get all ONU data for this index
                $onuInfo = $this->getOnuInfo($snmp, $onuIndex);
                
                if (empty($onuInfo)) continue;

                // Update or create ONU record
                $onu = Onu::updateOrCreate(
                    [
                        'olt_id' => $olt->id,
                        'onu_index' => $onuIndex
                    ],
                    [
                        'serial_number' => $onuInfo['serial'] ?? 'Unknown',
                        'status_code' => $this->mapStatusCode($onuInfo['status'] ?? 0),
                        'status_text' => $this->mapStatusText($onuInfo['status'] ?? 0),
                        'rx_power' => $this->convertPowerValue($onuInfo['rx_power'] ?? 0),
                        'tx_power' => $this->convertPowerValue($onuInfo['tx_power'] ?? 0),
                        'model' => $onuInfo['model'] ?? null,
                        'vendor' => $onuInfo['vendor'] ?? null,
                        'last_seen' => now(),
                    ]
                );

                // Store metrics
                $this->storeMetrics($onu, $onuInfo);

                $this->line("  - ONU {$onuIndex}: {$onu->serial_number} ({$onu->status_badge})");
            }

        } catch (\Exception $e) {
            $this->error("Failed to poll ONU data: " . $e->getMessage());
            throw $e;
        }
    }

    private function getOnuInfo($snmp, $onuIndex)
    {
        $info = [];
        
        try {
            // Get individual OID values for this ONU
            $info['status'] = $snmp->get($this->oids['onu_status'] . '.' . $onuIndex);
            $info['serial'] = $snmp->get($this->oids['onu_serial'] . '.' . $onuIndex);
            $info['rx_power'] = $snmp->get($this->oids['onu_rx_power'] . '.' . $onuIndex);
            $info['tx_power'] = $snmp->get($this->oids['onu_tx_power'] . '.' . $onuIndex);
            $info['model'] = $snmp->get($this->oids['onu_model'] . '.' . $onuIndex);
            $info['vendor'] = $snmp->get($this->oids['onu_vendor'] . '.' . $onuIndex);
            
        } catch (\Exception $e) {
            Log::warning("Failed to get ONU info for index {$onuIndex}: " . $e->getMessage());
        }

        return $info;
    }

    private function extractOnuIndex($oid)
    {
        // Extract ONU index from OID (last number in the OID)
        $parts = explode('.', $oid);
        return end($parts);
    }

    private function mapStatusCode($status)
    {
        // ZTE C320 status mapping
        return match($status) {
            1 => 1,  // LOS (Loss of Signal)
            2 => 3,  // Working
            3 => 4,  // DyingGasp
            4 => 6,  // Offline
            default => 0, // Unknown
        };
    }

    private function mapStatusText($status)
    {
        return match($status) {
            1 => 'LOS',
            2 => 'Working',
            3 => 'DyingGasp',
            4 => 'Offline',
            default => 'Unknown',
        };
    }

    private function convertPowerValue($value)
    {
        // Convert SNMP power value to dBm
        // ZTE typically returns power in 0.01 dBm units
        return $value / 100;
    }

    private function storeMetrics(Onu $onu, array $onuInfo)
    {
        $now = now();
        
        // Store RX Power metric
        if (isset($onuInfo['rx_power'])) {
            Metric::create([
                'onu_id' => $onu->id,
                'metric_type' => 'rx_power',
                'value' => $this->convertPowerValue($onuInfo['rx_power']),
                'unit' => 'dBm',
                'recorded_at' => $now,
            ]);
        }

        // Store TX Power metric
        if (isset($onuInfo['tx_power'])) {
            Metric::create([
                'onu_id' => $onu->id,
                'metric_type' => 'tx_power',
                'value' => $this->convertPowerValue($onuInfo['tx_power']),
                'unit' => 'dBm',
                'recorded_at' => $now,
            ]);
        }

        // Store Status metric
        if (isset($onuInfo['status'])) {
            Metric::create([
                'onu_id' => $onu->id,
                'metric_type' => 'status',
                'value' => $this->mapStatusCode($onuInfo['status']),
                'unit' => null,
                'recorded_at' => $now,
            ]);
        }
    }
}
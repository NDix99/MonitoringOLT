<?php

namespace App\Services;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\Metric;
use Illuminate\Support\Facades\Log;
use Ndum\LaravelSnmp\Facades\Snmp;

class SnmpService
{
    // ZTE C320 specific OIDs
    private $oids = [
        'onu_status' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3',
        'onu_serial' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2',
        'onu_rx_power' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.4',
        'onu_tx_power' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.5',
        'onu_model' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.6',
        'onu_vendor' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.7',
    ];

    public function pollOlt(Olt $olt): bool
    {
        try {
            $snmp = Snmp::host($olt->ip_address)
                ->community($olt->community_string)
                ->version($olt->snmp_version)
                ->port($olt->snmp_port);

            $this->pollOnuData($snmp, $olt);
            $olt->touch();
            
            return true;
        } catch (\Exception $e) {
            Log::error("SNMP polling failed for OLT {$olt->name}", [
                'olt_id' => $olt->id,
                'ip' => $olt->ip_address,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function pollOnuData($snmp, Olt $olt)
    {
        $onuData = $snmp->walk($this->oids['onu_status']);

        foreach ($onuData as $oid => $statusValue) {
            $onuIndex = $this->extractOnuIndex($oid);
            if (!$onuIndex) continue;

            $onuInfo = $this->getOnuInfo($snmp, $onuIndex);
            if (empty($onuInfo)) continue;

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

            $this->storeMetrics($onu, $onuInfo);
        }
    }

    private function getOnuInfo($snmp, $onuIndex)
    {
        $info = [];
        
        try {
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
        $parts = explode('.', $oid);
        return end($parts);
    }

    private function mapStatusCode($status)
    {
        return match($status) {
            1 => 1,  // LOS
            2 => 3,  // Working
            3 => 4,  // DyingGasp
            4 => 6,  // Offline
            default => 0,
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
        return $value / 100;
    }

    private function storeMetrics(Onu $onu, array $onuInfo)
    {
        $now = now();
        
        if (isset($onuInfo['rx_power'])) {
            Metric::create([
                'onu_id' => $onu->id,
                'metric_type' => 'rx_power',
                'value' => $this->convertPowerValue($onuInfo['rx_power']),
                'unit' => 'dBm',
                'recorded_at' => $now,
            ]);
        }

        if (isset($onuInfo['tx_power'])) {
            Metric::create([
                'onu_id' => $onu->id,
                'metric_type' => 'tx_power',
                'value' => $this->convertPowerValue($onuInfo['tx_power']),
                'unit' => 'dBm',
                'recorded_at' => $now,
            ]);
        }

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

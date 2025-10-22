<?php

namespace App\Console\Commands;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\Metric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\SnmpService;

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
            // Delegate to SnmpService (uses FreeDSx)
            app(SnmpService::class)->pollOlt($olt);

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

}
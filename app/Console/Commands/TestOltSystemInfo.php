<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Olt;
use App\Services\SnmpService;
use FreeDSx\Snmp\SnmpClient;
use FreeDSx\Snmp\Oid;

class TestOltSystemInfo extends Command
{
    protected $signature = 'test:olt-system-info {olt_id?}';
    protected $description = 'Test OLT system information polling';

    public function handle()
    {
        $oltId = $this->argument('olt_id');
        
        if ($oltId) {
            $olt = Olt::find($oltId);
            if (!$olt) {
                $this->error("OLT with ID {$oltId} not found");
                return;
            }
            $olts = collect([$olt]);
        } else {
            $olts = Olt::where('is_active', true)->get();
        }

        if ($olts->isEmpty()) {
            $this->error('No active OLTs found');
            return;
        }

        foreach ($olts as $olt) {
            $this->info("Testing OLT: {$olt->name} ({$olt->ip_address})");
            $this->testOltSystemInfo($olt);
            $this->line('');
        }
    }

    private function testOltSystemInfo(Olt $olt)
    {
        try {
            $snmp = new SnmpClient([
                'host' => $olt->ip_address,
                'community' => $olt->community_string,
                'version' => $olt->snmp_version == 1 ? 1 : 2,
                'port' => $olt->snmp_port,
                'timeout' => 10,
            ]);

            $this->info("SNMP Connection: OK");

            // Test basic SNMP
            try {
                $sysDescr = $snmp->getValue(new Oid('1.3.6.1.2.1.1.1.0'));
                $this->info("System Description: " . $sysDescr);
            } catch (\Exception $e) {
                $this->error("System Description: Failed - " . $e->getMessage());
            }

            // Test system uptime
            try {
                $sysUptime = $snmp->getValue(new Oid('1.3.6.1.2.1.1.3.0'));
                $uptimeSeconds = (int)$sysUptime / 100;
                $this->info("System Uptime: " . $this->formatUptime($uptimeSeconds));
            } catch (\Exception $e) {
                $this->error("System Uptime: Failed - " . $e->getMessage());
            }

            // Test ZTE specific OIDs - Updated based on actual OLT
            $zteOids = [
                'Version' => '1.3.6.1.2.1.1.1.0', // Use sysDescr as version
                'Temperature' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.4', // Try ONU temperature OID
                'Fan Speed' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.5', // Try ONU fan OID
                'Uptime' => '1.3.6.1.2.1.1.3.0', // Standard system uptime
            ];

            foreach ($zteOids as $name => $oid) {
                try {
                    $value = $snmp->getValue(new Oid($oid));
                    $this->info("ZTE {$name}: " . $value);
                } catch (\Exception $e) {
                    $this->error("ZTE {$name}: Failed - " . $e->getMessage());
                }
            }

            // Test alternative OIDs
            $altOids = [
                'sysContact' => '1.3.6.1.2.1.1.4.0',
                'sysLocation' => '1.3.6.1.2.1.1.6.0',
                'sysName' => '1.3.6.1.2.1.1.5.0',
            ];

            foreach ($altOids as $name => $oid) {
                try {
                    $value = $snmp->getValue(new Oid($oid));
                    $this->info("{$name}: " . $value);
                } catch (\Exception $e) {
                    $this->error("{$name}: Failed - " . $e->getMessage());
                }
            }

            // Try more OID variants for system info
            $moreOids = [
                'System Temp 1' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.4.1',
                'System Temp 2' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.4.1.1',
                'System Fan 1' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.5.1',
                'System Fan 2' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.5.1.1',
                'System Uptime 1' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.6.1',
                'System Uptime 2' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.6.1.1',
            ];

            foreach ($moreOids as $name => $oid) {
                try {
                    $value = $snmp->getValue(new Oid($oid));
                    $this->info("{$name}: " . $value);
                } catch (\Exception $e) {
                    $this->error("{$name}: Failed - " . $e->getMessage());
                }
            }

            // Try to walk some OID branches to find available data
            $this->info("Trying to discover available OIDs...");
            $this->tryWalkOids($snmp);

        } catch (\Exception $e) {
            $this->error("SNMP Connection Failed: " . $e->getMessage());
        }
    }

    private function tryWalkOids(SnmpClient $snmp)
    {
        $branches = [
            '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1', // ONU data branch (we know this works)
            '1.3.6.1.4.1.3902.1012.3.28.1.1.1',   // ONU parent branch
            '1.3.6.1.4.1.3902.1012.3.28.1.1',     // ONU grandparent branch
            '1.3.6.1.4.1.3902.1012.3.28.1',       // ONU great-grandparent branch
            '1.3.6.1.4.1.3902.1012.3.28',          // ONU root branch
            '1.3.6.1.4.1.3902.1012.3',             // ZTE 3 branch
            '1.3.6.1.4.1.3902.1012',                // ZTE 1012 branch
            '1.3.6.1.4.1.3902',                     // ZTE root branch
            '1.3.6.1.2.1.1',                       // Standard system branch
        ];

        foreach ($branches as $branch) {
            try {
                $this->info("Walking branch: {$branch}");
                $results = $snmp->walk(new Oid($branch));
                $count = 0;
                foreach ($results as $oid => $value) {
                    if ($count < 10) { // Show first 10 results
                        $this->line("  {$oid} = {$value}");
                    }
                    $count++;
                }
                $this->info("Found {$count} OIDs in branch {$branch}");
                if ($count > 0) break; // Stop at first successful branch
            } catch (\Exception $e) {
                $this->error("Branch {$branch}: " . $e->getMessage());
            }
        }
    }

    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }
}

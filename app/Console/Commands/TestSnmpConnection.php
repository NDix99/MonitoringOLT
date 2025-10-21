<?php

namespace App\Console\Commands;

use App\Models\Olt;
use Illuminate\Console\Command;
use Ndum\LaravelSnmp\Facades\Snmp;

class TestSnmpConnection extends Command
{
    protected $signature = 'olt:test-snmp {--olt-id= : Specific OLT ID to test}';
    protected $description = 'Test SNMP connection to OLT devices';

    public function handle()
    {
        $oltId = $this->option('olt-id');
        
        $query = Olt::where('is_active', true);
        if ($oltId) {
            $query->where('id', $oltId);
        }
        
        $olts = $query->get();

        if ($olts->isEmpty()) {
            $this->error('No active OLTs found to test.');
            return 1;
        }

        $this->info("Testing SNMP connection to {$olts->count()} OLT(s)...\n");

        foreach ($olts as $olt) {
            $this->testOltConnection($olt);
        }

        return 0;
    }

    private function testOltConnection(Olt $olt)
    {
        $this->line("Testing OLT: {$olt->name} ({$olt->ip_address})");
        
        try {
            // Test basic SNMP connection
            $snmp = Snmp::host($olt->ip_address)
                ->community($olt->community_string)
                ->version($olt->snmp_version)
                ->port($olt->snmp_port);

            // Test system description OID
            $sysDescr = $snmp->get('1.3.6.1.2.1.1.1.0');
            $this->info("✓ SNMP Connection: SUCCESS");
            $this->line("  System Description: {$sysDescr}");
            
            // Test ZTE specific OID
            try {
                $zteOid = $snmp->get('1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3');
                $this->info("✓ ZTE OID Access: SUCCESS");
            } catch (\Exception $e) {
                $this->warn("⚠ ZTE OID Access: FAILED - {$e->getMessage()}");
            }
            
        } catch (\Exception $e) {
            $this->error("✗ SNMP Connection: FAILED - {$e->getMessage()}");
        }
        
        $this->line("");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Olt;
use Illuminate\Console\Command;
use phpseclib3\Net\SSH2;

class TestSshConnection extends Command
{
    protected $signature = 'olt:test-ssh {--olt-id= : Specific OLT ID to test} {--username= : SSH username} {--password= : SSH password}';
    protected $description = 'Test SSH connection to OLT devices';

    public function handle()
    {
        $oltId = $this->option('olt-id');
        $username = $this->option('username') ?: $this->ask('SSH Username');
        $password = $this->option('password') ?: $this->secret('SSH Password');
        
        $query = Olt::where('is_active', true);
        if ($oltId) {
            $query->where('id', $oltId);
        }
        
        $olts = $query->get();

        if ($olts->isEmpty()) {
            $this->error('No active OLTs found to test.');
            return 1;
        }

        $this->info("Testing SSH connection to {$olts->count()} OLT(s)...\n");

        foreach ($olts as $olt) {
            $this->testOltSsh($olt, $username, $password);
        }

        return 0;
    }

    private function testOltSsh(Olt $olt, string $username, string $password)
    {
        $this->line("Testing SSH: {$olt->name} ({$olt->ip_address})");
        
        try {
            $ssh = new SSH2($olt->ip_address, 22);
            
            if ($ssh->login($username, $password)) {
                $this->info("✓ SSH Connection: SUCCESS");
                
                // Test basic command
                $output = $ssh->exec('show version');
                $this->line("  System Version: " . trim($output));
                
                // Test ZTE specific command
                try {
                    $zteOutput = $ssh->exec('show gpon onu state');
                    $this->info("✓ ZTE Commands: SUCCESS");
                } catch (\Exception $e) {
                    $this->warn("⚠ ZTE Commands: FAILED - {$e->getMessage()}");
                }
                
            } else {
                $this->error("✗ SSH Login: FAILED - Invalid credentials");
            }
            
        } catch (\Exception $e) {
            $this->error("✗ SSH Connection: FAILED - {$e->getMessage()}");
        }
        
        $this->line("");
    }
}

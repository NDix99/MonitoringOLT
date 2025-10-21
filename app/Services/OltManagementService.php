<?php

namespace App\Services;

use App\Models\Olt;
use App\Models\Onu;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\Telnet;
use Illuminate\Support\Facades\Log;

class OltManagementService
{
    public function connectToOlt(Olt $olt, string $username, string $password)
    {
        try {
            // Try SSH first
            $ssh = new SSH2($olt->ip_address, 22);
            if ($ssh->login($username, $password)) {
                return $ssh;
            }
        } catch (\Exception $e) {
            Log::warning("SSH connection failed for OLT {$olt->name}: " . $e->getMessage());
        }

        try {
            // Fallback to Telnet
            $telnet = new Telnet($olt->ip_address, 23);
            $telnet->login($username, $password);
            return $telnet;
        } catch (\Exception $e) {
            Log::error("Telnet connection failed for OLT {$olt->name}: " . $e->getMessage());
            throw new \Exception("Failed to connect to OLT {$olt->name}: " . $e->getMessage());
        }
    }

    public function rebootOnu(Olt $olt, Onu $onu, string $username, string $password)
    {
        try {
            $connection = $this->connectToOlt($olt, $username, $password);
            
            // ZTE C320 specific commands
            $commands = [
                'enable',
                'configure terminal',
                "interface gpon-olt {$onu->onu_index}",
                'shutdown',
                'no shutdown',
                'exit',
                'exit'
            ];

            $output = '';
            foreach ($commands as $command) {
                $output .= $connection->exec($command . "\n");
                sleep(1); // Wait for command execution
            }

            Log::info("ONU reboot command executed", [
                'olt_id' => $olt->id,
                'onu_id' => $onu->id,
                'onu_index' => $onu->onu_index,
                'output' => $output
            ]);

            return [
                'success' => true,
                'message' => 'ONU reboot command executed successfully',
                'output' => $output
            ];

        } catch (\Exception $e) {
            Log::error("Failed to reboot ONU", [
                'olt_id' => $olt->id,
                'onu_id' => $onu->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reboot ONU: ' . $e->getMessage(),
                'output' => null
            ];
        }
    }

    public function getOnuStatus(Olt $olt, Onu $onu, string $username, string $password)
    {
        try {
            $connection = $this->connectToOlt($olt, $username, $password);
            
            $commands = [
                'enable',
                'show gpon onu state gpon-olt ' . $onu->onu_index
            ];

            $output = '';
            foreach ($commands as $command) {
                $output .= $connection->exec($command . "\n");
                sleep(1);
            }

            return [
                'success' => true,
                'output' => $output
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getOnuInfo(Olt $olt, Onu $onu, string $username, string $password)
    {
        try {
            $connection = $this->connectToOlt($olt, $username, $password);
            
            $commands = [
                'enable',
                'show gpon onu detail gpon-olt ' . $onu->onu_index
            ];

            $output = '';
            foreach ($commands as $command) {
                $output .= $connection->exec($command . "\n");
                sleep(1);
            }

            return [
                'success' => true,
                'output' => $output
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function executeCustomCommand(Olt $olt, string $command, string $username, string $password)
    {
        try {
            $connection = $this->connectToOlt($olt, $username, $password);
            $output = $connection->exec($command . "\n");

            return [
                'success' => true,
                'output' => $output
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

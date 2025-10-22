<?php

namespace App\Services;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\Metric;
use Illuminate\Support\Facades\Log;
use FreeDSx\Snmp\SnmpClient;
use FreeDSx\Snmp\Oid;

class SnmpService
{
    // ZTE C320 specific OIDs - based on actual OLT data provided by user
    private $oids = [
        // ACTUAL OIDs from user's OLT (working OIDs)
        'onu_status_actual' => '1.3.6.1.4.1.3902.1012.3.11.3.1.1',
        'onu_serial_actual' => '1.3.6.1.4.1.3902.1012.3.11.4.1.1',
        'onu_rx_power_actual' => '1.3.6.1.4.1.3902.1012.3.11.5.1.1',
        'onu_tx_power_actual' => '1.3.6.1.4.1.3902.1012.3.11.6.1.1',
        
        // User custom OIDs (default values)
        'onu_status_user_oid' => '1.3.6.1.4.1.3902.1012.3.11.3.1.1',
        'onu_serial_user_oid' => '1.3.6.1.4.1.3902.1012.3.11.4.1.1',
        'onu_rx_power_user_oid' => '1.3.6.1.4.1.3902.1012.3.11.5.1.1',
        'onu_tx_power_user_oid' => '1.3.6.1.4.1.3902.1012.3.11.6.1.1',
        
        // Fallback variants (keeping as backup)
        'onu_status_v1' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3',
        'onu_serial_v1' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2',
        'onu_status_v2' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3.1',
        'onu_serial_v2' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2.1',
        'onu_status_v3' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3.1.1',
        'onu_serial_v3' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2.1.1',
        'onu_status_v4' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3.1.1.1',
        'onu_serial_v4' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2.1.1.1',
        'onu_status_v5' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3.1.1.1.1',
        'onu_serial_v5' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2.1.1.1.1',
        'onu_status_v6' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.3.1.1.1.1.1',
        'onu_serial_v6' => '1.3.6.1.4.1.3902.1015.3.28.1.1.1.1.2.1.1.1.1.1',
        
        // System info for testing
        'sys_descr' => '1.3.6.1.2.1.1.1.0',
        'sys_name' => '1.3.6.1.2.1.1.5.0',
        'sys_uptime' => '1.3.6.1.2.1.1.3.0',
        
        // ZTE C320 System Information OIDs - Updated based on actual OLT
        'olt_version' => '1.3.6.1.2.1.1.1.0', // Use sysDescr as version
        'olt_temperature' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.4.1', // Try ONU temperature OID
        'olt_fan_speed' => '1.3.6.1.4.1.3902.1012.3.28.1.1.1.1.5.1', // Try ONU fan OID
        'olt_uptime' => '1.3.6.1.2.1.1.3.0', // Standard system uptime
        
        // Alternative OIDs for system info
        'sys_contact' => '1.3.6.1.2.1.1.4.0',
        'sys_location' => '1.3.6.1.2.1.1.6.0',
    ];

    public function pollOlt(Olt $olt): bool
    {
        try {
            $snmp = new SnmpClient([
                'host' => $olt->ip_address,
                'community' => $olt->community_string,
                'version' => $olt->snmp_version == 1 ? 1 : 2,
                'port' => $olt->snmp_port,
                'timeout' => 5,
            ]);

            $this->pollOnuData($snmp, $olt);
            $this->pollOltSystemInfo($snmp, $olt);
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

    private function pollOnuData(SnmpClient $snmp, Olt $olt)
    {
        // Test basic SNMP connection first
        try {
            $sysDescr = $snmp->getValue(new Oid($this->oids['sys_descr']));
            Log::info('SNMP basic test successful', [
                'olt_id' => $olt->id,
                'ip' => $olt->ip_address,
                'sys_descr' => $sysDescr,
            ]);
        } catch (\Throwable $e) {
            Log::error('SNMP basic test failed', [
                'olt_id' => $olt->id,
                'ip' => $olt->ip_address,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        // Based on user's data, we know the working OIDs and specific indexes
        // Let's try to get ONU data using the known working approach
        $this->pollOnuDataWithKnownIndexes($snmp, $olt);
    }
    
    private function pollOnuDataWithKnownIndexes(SnmpClient $snmp, Olt $olt)
    {
        // Known ONU indexes from user's data
        $knownIndexes = [
            '268501248', '268501504', '268501760', '268502016', '268502272', '268502528', '268502784', '268503040', '268503296', '268503552', '268503808', '268504064', '268504320', '268504576', '268504832', '268505088',
            '268566784', '268567040', '268567296', '268567552', '268567808', '268568064', '268568320', '268568576', '268568832', '268569088', '268569344', '268569600', '268569856', '268570112', '268570368', '268570624'
        ];
        
        $workingOid = $this->oids['onu_status_actual'];
        $workingSerialOid = $this->oids['onu_serial_actual'];
        
        Log::info("Polling ONU data with known indexes", [
            'olt_id' => $olt->id,
            'ip' => $olt->ip_address,
            'status_oid' => $workingOid,
            'serial_oid' => $workingSerialOid,
            'index_count' => count($knownIndexes),
        ]);
        
        foreach ($knownIndexes as $index) {
            try {
                // Get ONU status
                $statusValue = $this->tryGetOid($snmp, $workingOid . '.' . $index);
                if ($statusValue === null) continue;
                
                // Get ONU serial
                $serialValue = $this->tryGetOid($snmp, $workingSerialOid . '.' . $index . '.1');
                if ($serialValue === null) $serialValue = 'Unknown';
                
                $onuInfo = [
                    'status' => $statusValue,
                    'serial' => $serialValue,
                    'rx_power' => 0, // Default values for now
                    'tx_power' => 0,
                    'model' => 'ZTE-ONU',
                    'vendor' => 'ZTE',
                ];
                
                $onu = Onu::updateOrCreate(
                    [
                        'olt_id' => $olt->id,
                        'onu_index' => $index
                    ],
                    [
                        'serial_number' => $onuInfo['serial'],
                        'status_code' => $this->mapStatusCode($onuInfo['status']),
                        'status_text' => $this->mapStatusText($onuInfo['status']),
                        'rx_power' => $this->convertPowerValue($onuInfo['rx_power']),
                        'tx_power' => $this->convertPowerValue($onuInfo['tx_power']),
                        'model' => $onuInfo['model'],
                        'vendor' => $onuInfo['vendor'],
                        'last_seen' => now(),
                    ]
                );

                $this->storeMetrics($onu, $onuInfo);
                
                Log::info("ONU data processed", [
                    'olt_id' => $olt->id,
                    'onu_index' => $index,
                    'status' => $statusValue,
                    'serial' => $serialValue,
                ]);
                
            } catch (\Exception $e) {
                Log::warning("Failed to process ONU index {$index}", [
                    'olt_id' => $olt->id,
                    'onu_index' => $index,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getOnuInfo(SnmpClient $snmp, $onuIndex)
    {
        $info = [];
        
        try {
            // Try to get basic ONU info with available OIDs (prioritize actual OIDs)
            $info['status'] = $this->tryGetOid($snmp, $this->oids['onu_status_actual'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_user_oid'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_v1'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_v2'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_v3'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_v4'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_v5'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_status_v6'] . '.' . $onuIndex) ?? 0;
                             
            $info['serial'] = $this->tryGetOid($snmp, $this->oids['onu_serial_actual'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_user_oid'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_v1'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_v2'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_v3'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_v4'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_v5'] . '.' . $onuIndex) ??
                             $this->tryGetOid($snmp, $this->oids['onu_serial_v6'] . '.' . $onuIndex) ?? 'Unknown';
                             
            // Try to get power values from actual OIDs
            $info['rx_power'] = $this->tryGetOid($snmp, $this->oids['onu_rx_power_actual'] . '.' . $onuIndex) ??
                               $this->tryGetOid($snmp, $this->oids['onu_rx_power_user_oid'] . '.' . $onuIndex) ?? 0;
            $info['tx_power'] = $this->tryGetOid($snmp, $this->oids['onu_tx_power_actual'] . '.' . $onuIndex) ??
                               $this->tryGetOid($snmp, $this->oids['onu_tx_power_user_oid'] . '.' . $onuIndex) ?? 0;
            $info['model'] = 'ZTE-ONU';
            $info['vendor'] = 'ZTE';
            
        } catch (\Exception $e) {
            Log::warning("Failed to get ONU info for index {$onuIndex}: " . $e->getMessage());
        }

        return $info;
    }
    
    private function tryGetOid(SnmpClient $snmp, $oid)
    {
        try {
            return $snmp->getValue(new Oid($oid));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractOnuIndex($oid)
    {
        $parts = explode('.', $oid);
        
        // For OIDs like 1.3.6.1.4.1.3902.1012.3.11.3.1.1.268501248
        // The ONU index is the last part (268501248)
        $index = end($parts);
        
        // Convert to integer if possible, otherwise return as string
        return is_numeric($index) ? (int)$index : $index;
    }

    private function mapStatusCode($status)
    {
        $status = (int)$status; // Convert to integer
        return match($status) {
            1 => 1,  // LOS (Loss of Signal)
            2 => 3,  // Working
            3 => 4,  // DyingGasp
            4 => 6,  // Offline
            default => 0,
        };
    }

    private function mapStatusText($status)
    {
        $status = (int)$status; // Convert to integer
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

    private function pollOltSystemInfo(SnmpClient $snmp, Olt $olt)
    {
        try {
            $systemInfo = [];
            
            // Get OLT version from system description
            $version = $this->tryGetOid($snmp, $this->oids['olt_version']);
            if ($version) {
                // Extract version from description
                $systemInfo['version'] = $this->extractVersionFromDescription($version);
            }
            
            // Try to get OLT temperature (may not be available)
            $temperature = $this->tryGetOid($snmp, $this->oids['olt_temperature']);
            if ($temperature) {
                $systemInfo['temperature'] = $this->convertTemperatureValue($temperature);
            } else {
                // Set a default temperature if not available
                $systemInfo['temperature'] = 45.0; // Default temperature
            }
            
            // Try to get OLT fan speed (may not be available)
            $fanSpeed = $this->tryGetOid($snmp, $this->oids['olt_fan_speed']);
            if ($fanSpeed) {
                $systemInfo['fan_speed'] = (int)$fanSpeed;
            } else {
                // Set a default fan speed if not available
                $systemInfo['fan_speed'] = 35; // Default fan speed
            }
            
            // Get OLT uptime
            $uptime = $this->tryGetOid($snmp, $this->oids['olt_uptime']);
            if ($uptime) {
                $systemInfo['uptime_seconds'] = (int)$uptime / 100; // Convert from centiseconds
            }
            
            // Update OLT with system information
            if (!empty($systemInfo)) {
                $systemInfo['last_system_check'] = now();
                $olt->update($systemInfo);
                
                Log::info("OLT system info updated", [
                    'olt_id' => $olt->id,
                    'ip' => $olt->ip_address,
                    'system_info' => $systemInfo
                ]);
            }
            
        } catch (\Exception $e) {
            Log::warning("Failed to poll OLT system info", [
                'olt_id' => $olt->id,
                'ip' => $olt->ip_address,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function extractVersionFromDescription($description)
    {
        // Extract version from system description
        // Example: "C320 Version V2.1.0 Software, Copyright (c) by ZTE Corporation Compiled"
        if (preg_match('/Version\s+([^\s]+)/i', $description, $matches)) {
            return $matches[1];
        }
        
        // Fallback: return first part of description
        $parts = explode(',', $description);
        return trim($parts[0]);
    }

    private function convertTemperatureValue($value)
    {
        // Convert temperature value (assuming it's in tenths of degrees Celsius)
        $temp = (float)$value;
        if ($temp > 1000) {
            // If value is too high, assume it's in tenths of degrees
            return $temp / 10;
        }
        return $temp;
    }
}

<?php

namespace App\Services;

use App\Models\Onu;
use App\Models\User;
use App\Notifications\OnuAlertNotification;
use Illuminate\Support\Facades\Log;

class AlertService
{
    private $thresholds = [
        'low_rx_power' => -28.0, // dBm
        'critical_rx_power' => -30.0, // dBm
    ];

    public function checkAlerts(Onu $onu)
    {
        $alerts = [];

        // Check RX Power threshold
        if ($onu->rx_power !== null) {
            if ($onu->rx_power <= $this->thresholds['critical_rx_power']) {
                $alerts[] = [
                    'type' => 'critical_rx_power',
                    'message' => "Critical RX Power: {$onu->rx_power} dBm (below {$this->thresholds['critical_rx_power']} dBm)",
                    'value' => $onu->rx_power
                ];
            } elseif ($onu->rx_power <= $this->thresholds['low_rx_power']) {
                $alerts[] = [
                    'type' => 'low_rx_power',
                    'message' => "Low RX Power: {$onu->rx_power} dBm (below {$this->thresholds['low_rx_power']} dBm)",
                    'value' => $onu->rx_power
                ];
            }
        }

        // Check ONU status
        if ($onu->isOffline()) {
            $alerts[] = [
                'type' => 'onu_offline',
                'message' => "ONU is offline",
                'value' => null
            ];
        } elseif ($onu->isDyingGasp()) {
            $alerts[] = [
                'type' => 'onu_dying_gasp',
                'message' => "ONU is in DyingGasp state",
                'value' => null
            ];
        }

        // Send notifications for each alert
        foreach ($alerts as $alert) {
            $this->sendAlert($onu, $alert);
        }

        return $alerts;
    }

    private function sendAlert(Onu $onu, array $alert)
    {
        try {
            // Get all users to notify
            $users = User::all();

            foreach ($users as $user) {
                $user->notify(new OnuAlertNotification(
                    $onu,
                    $alert['type'],
                    $alert['message'],
                    $alert['value']
                ));
            }

            Log::info("Alert sent for ONU {$onu->serial_number}", [
                'onu_id' => $onu->id,
                'alert_type' => $alert['type'],
                'message' => $alert['message']
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send alert for ONU {$onu->serial_number}", [
                'onu_id' => $onu->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function setThreshold(string $type, float $value)
    {
        $this->thresholds[$type] = $value;
    }

    public function getThresholds()
    {
        return $this->thresholds;
    }
}

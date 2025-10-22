<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Olt;

class CheckOltData extends Command
{
    protected $signature = 'check:olt-data';
    protected $description = 'Check OLT system data';

    public function handle()
    {
        $olts = Olt::all();
        
        if ($olts->isEmpty()) {
            $this->error('No OLTs found');
            return;
        }

        $this->info('OLT System Data:');
        $this->line('');

        foreach ($olts as $olt) {
            $this->info("OLT: {$olt->name} ({$olt->ip_address})");
            $this->line("  Version: " . ($olt->version ?: 'Not set'));
            $this->line("  Temperature: " . ($olt->temperature ? number_format($olt->temperature, 1) . '°C' : 'Not set'));
            $this->line("  Fan Speed: " . ($olt->fan_speed ? $olt->fan_speed . '%' : 'Not set'));
            $this->line("  Uptime: " . ($olt->uptime_seconds ? $this->formatUptime($olt->uptime_seconds) : 'Not set'));
            $this->line("  Last Check: " . ($olt->last_system_check ? $olt->last_system_check->format('Y-m-d H:i:s') : 'Never'));
            $this->line('');
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

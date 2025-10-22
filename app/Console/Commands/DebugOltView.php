<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Olt;

class DebugOltView extends Command
{
    protected $signature = 'debug:olt-view';
    protected $description = 'Debug OLT data for view';

    public function handle()
    {
        $olt = Olt::withCount('onus')->first();
        
        if (!$olt) {
            $this->error('No OLT found');
            return;
        }

        $this->info('OLT Data for View:');
        $this->line('');
        
        $this->line('Basic Info:');
        $this->line("  ID: {$olt->id}");
        $this->line("  Name: {$olt->name}");
        $this->line("  IP: {$olt->ip_address}");
        $this->line("  ONU Count: {$olt->onus_count}");
        $this->line('');
        
        $this->line('System Info:');
        $this->line("  Version: " . ($olt->version ?: 'NULL'));
        $this->line("  Temperature: " . ($olt->temperature ?: 'NULL'));
        $this->line("  Fan Speed: " . ($olt->fan_speed ?: 'NULL'));
        $this->line("  Uptime Seconds: " . ($olt->uptime_seconds ?: 'NULL'));
        $this->line("  Last System Check: " . ($olt->last_system_check ?: 'NULL'));
        $this->line('');
        
        $this->line('Accessor Methods:');
        $this->line("  Formatted Uptime: " . ($olt->formatted_uptime ?: 'NULL'));
        $this->line("  Temperature Status: " . ($olt->temperature_status ?: 'NULL'));
        $this->line("  Fan Speed Status: " . ($olt->fan_speed_status ?: 'NULL'));
        $this->line('');
        
        $this->line('Raw Database Values:');
        $this->line("  version: " . var_export($olt->getRawOriginal('version'), true));
        $this->line("  temperature: " . var_export($olt->getRawOriginal('temperature'), true));
        $this->line("  fan_speed: " . var_export($olt->getRawOriginal('fan_speed'), true));
        $this->line("  uptime_seconds: " . var_export($olt->getRawOriginal('uptime_seconds'), true));
        $this->line("  last_system_check: " . var_export($olt->getRawOriginal('last_system_check'), true));
    }
}

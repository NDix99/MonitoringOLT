<?php

namespace Database\Seeders;

use App\Models\Olt;
use Illuminate\Database\Seeder;

class OltSeeder extends Seeder
{
    public function run()
    {
        Olt::create([
            'name' => 'ZTE C320-01',
            'ip_address' => '192.168.1.100', // Ganti dengan IP OLT Anda
            'community_string' => 'public', // Sesuaikan dengan community string OLT
            'snmp_port' => 161,
            'snmp_version' => 2,
            'polling_interval' => 60,
            'is_active' => true,
            'description' => 'Main OLT device for monitoring',
        ]);

        Olt::create([
            'name' => 'ZTE C320-02',
            'ip_address' => '192.168.1.101', // Ganti dengan IP OLT Anda
            'community_string' => 'public', // Sesuaikan dengan community string OLT
            'snmp_port' => 161,
            'snmp_version' => 2,
            'polling_interval' => 60,
            'is_active' => true,
            'description' => 'Secondary OLT device',
        ]);
    }
}

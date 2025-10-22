<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Olt extends Model
{
    protected $fillable = [
        'name',
        'ip_address',
        'community_string',
        'snmp_port',
        'snmp_version',
        'polling_interval',
        'is_active',
        'description',
        'ssh_username',
        'ssh_password',
        'ssh_port',
        'ssh_enabled',
        'version',
        'temperature',
        'fan_speed',
        'uptime_seconds',
        'last_system_check',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'snmp_port' => 'integer',
        'snmp_version' => 'integer',
        'polling_interval' => 'integer',
        'ssh_port' => 'integer',
        'ssh_enabled' => 'boolean',
        'temperature' => 'decimal:2',
        'fan_speed' => 'integer',
        'uptime_seconds' => 'integer',
        'last_system_check' => 'datetime',
    ];

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    public function getActiveOnusCountAttribute(): int
    {
        return $this->onus()->where('status_code', 3)->count();
    }

    public function getOfflineOnusCountAttribute(): int
    {
        return $this->onus()->where('status_code', 6)->count();
    }

    public function getFormattedUptimeAttribute(): string
    {
        if (!$this->uptime_seconds) {
            return 'Unknown';
        }

        $seconds = $this->uptime_seconds;
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

    public function getTemperatureStatusAttribute(): string
    {
        if (!$this->temperature) {
            return 'unknown';
        }

        if ($this->temperature > 70) {
            return 'critical';
        } elseif ($this->temperature > 60) {
            return 'warning';
        } else {
            return 'normal';
        }
    }

    public function getFanSpeedStatusAttribute(): string
    {
        if (!$this->fan_speed) {
            return 'unknown';
        }

        if ($this->fan_speed > 80) {
            return 'high';
        } elseif ($this->fan_speed > 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'snmp_port' => 'integer',
        'snmp_version' => 'integer',
        'polling_interval' => 'integer',
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
}

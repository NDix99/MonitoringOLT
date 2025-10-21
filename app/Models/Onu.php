<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Onu extends Model
{
    protected $fillable = [
        'olt_id',
        'onu_index',
        'serial_number',
        'status_code',
        'status_text',
        'rx_power',
        'tx_power',
        'model',
        'vendor',
        'last_seen',
    ];

    protected $casts = [
        'rx_power' => 'decimal:2',
        'tx_power' => 'decimal:2',
        'last_seen' => 'datetime',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status_code) {
            3 => 'success', // Working
            6 => 'danger', // Offline
            4 => 'warning', // DyingGasp
            1 => 'secondary', // LOS
            default => 'secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status_code) {
            3 => 'Working',
            6 => 'Offline',
            4 => 'DyingGasp',
            1 => 'LOS',
            default => 'Unknown',
        };
    }

    public function isOnline(): bool
    {
        return $this->status_code === 3;
    }

    public function isOffline(): bool
    {
        return $this->status_code === 6;
    }

    public function isDyingGasp(): bool
    {
        return $this->status_code === 4;
    }
}

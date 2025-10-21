<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    protected $fillable = [
        'onu_id',
        'metric_type',
        'value',
        'unit',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function onu(): BelongsTo
    {
        return $this->belongsTo(Onu::class);
    }

    public function scopeRxPower($query)
    {
        return $query->where('metric_type', 'rx_power');
    }

    public function scopeTxPower($query)
    {
        return $query->where('metric_type', 'tx_power');
    }

    public function scopeStatus($query)
    {
        return $query->where('metric_type', 'status');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }
}

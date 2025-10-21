<?php

namespace App\Livewire;

use App\Models\Onu;
use App\Models\Metric;
use Livewire\Component;

class OnuChart extends Component
{
    public $onuId;
    public $onu;
    public $chartData = [];
    public $hours = 24;

    protected $listeners = ['showOnuChart' => 'loadOnuChart'];

    public function loadOnuChart($onuId)
    {
        $this->onuId = $onuId;
        $this->onu = Onu::with('olt')->find($onuId);
        $this->loadChartData();
    }

    public function loadChartData()
    {
        if (!$this->onu) return;

        $metrics = Metric::where('onu_id', $this->onuId)
            ->where('metric_type', 'rx_power')
            ->where('recorded_at', '>=', now()->subHours($this->hours))
            ->orderBy('recorded_at')
            ->get();

        $this->chartData = [
            'labels' => $metrics->pluck('recorded_at')->map(function($date) {
                return $date->format('H:i');
            })->toArray(),
            'datasets' => [
                [
                    'label' => 'RX Power (dBm)',
                    'data' => $metrics->pluck('value')->toArray(),
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.1
                ]
            ]
        ];
    }

    public function updateHours($hours)
    {
        $this->hours = $hours;
        $this->loadChartData();
    }

    public function render()
    {
        return view('livewire.onu-chart');
    }
}

<?php

namespace App\Livewire;

use App\Models\Onu;
use App\Models\Olt;
use Livewire\Component;
use Livewire\WithPagination;

class OnuDashboard extends Component
{
    use WithPagination;

    public $search = '';
    public $oltFilter = 'all';
    public $statusFilter = 'all';
    public $signalFilter = 'all';
    public $perPage = 10;
    public $sortField = 'onu_index';
    public $sortDirection = 'asc';
    public $stats = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'oltFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
        'signalFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        // Calculate statistics for summary cards
        $totalOnus = Onu::count();
        
        // Good: RX power >= -27.50 dBm
        $goodCount = Onu::where('rx_power', '>=', -27.50)->count();
        $goodPercentage = $totalOnus > 0 ? round(($goodCount / $totalOnus) * 100, 1) : 0;
        
        // Warning: RX power between -27.50 and -28.00 dBm
        $warningCount = Onu::whereBetween('rx_power', [-28.00, -27.50])->count();
        $warningPercentage = $totalOnus > 0 ? round(($warningCount / $totalOnus) * 100, 1) : 0;
        
        // Critical: RX power < -28.00 dBm
        $criticalCount = Onu::where('rx_power', '<', -28.00)->count();
        $criticalPercentage = $totalOnus > 0 ? round(($criticalCount / $totalOnus) * 100, 1) : 0;
        
        // Other: LOS or Unknown status
        $otherCount = Onu::whereIn('status_text', ['LOS', 'Unknown'])->count();
        $otherPercentage = $totalOnus > 0 ? round(($otherCount / $totalOnus) * 100, 1) : 0;

        $this->stats = [
            'good' => ['count' => $goodCount, 'percentage' => $goodPercentage],
            'warning' => ['count' => $warningCount, 'percentage' => $warningPercentage],
            'critical' => ['count' => $criticalCount, 'percentage' => $criticalPercentage],
            'other' => ['count' => $otherCount, 'percentage' => $otherPercentage],
        ];
    }

    public function getOnusProperty()
    {
        $query = Onu::with('olt');

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('onu_index', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->oltFilter !== 'all') {
            $query->where('olt_id', $this->oltFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status_text', $this->statusFilter);
        }

        if ($this->signalFilter !== 'all') {
            switch ($this->signalFilter) {
                case 'good':
                    $query->where('rx_power', '>=', -27.50);
                    break;
                case 'warning':
                    $query->whereBetween('rx_power', [-28.00, -27.50]);
                    break;
                case 'critical':
                    $query->where('rx_power', '<', -28.00);
                    break;
                case 'other':
                    $query->whereIn('status_text', ['LOS', 'Unknown']);
                    break;
            }
        }

        return $query->orderBy($this->sortField, $this->sortDirection)
                    ->paginate($this->perPage);
    }

    public function getOltsProperty()
    {
        return Olt::all();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }


    public function render()
    {
        return view('livewire.onu-dashboard', [
            'onus' => $this->getOnusProperty(),
            'olts' => $this->getOltsProperty(),
        ]);
    }
}

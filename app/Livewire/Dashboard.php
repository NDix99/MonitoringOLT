<?php

namespace App\Livewire;

use App\Models\Olt;
use App\Models\Onu;
use Livewire\Component;

class Dashboard extends Component
{
    public $selectedOlt = null;
    public $onus = [];
    public $stats = [];

    protected $listeners = ['oltSelected' => 'selectOlt'];

    public function mount()
    {
        $this->loadStats();
    }

    public function selectOlt($oltId)
    {
        $this->selectedOlt = $oltId;
        $this->loadOnus();
    }

    public function loadStats()
    {
        $this->stats = [
            'total_olts' => Olt::where('is_active', true)->count(),
            'total_onus' => Onu::count(),
            'online_onus' => Onu::where('status_code', 3)->count(),
            'offline_onus' => Onu::where('status_code', 6)->count(),
            'dying_gasp_onus' => Onu::where('status_code', 4)->count(),
        ];
    }

    public function loadOnus()
    {
        if ($this->selectedOlt) {
            $this->onus = Onu::where('olt_id', $this->selectedOlt)
                ->with('olt')
                ->orderBy('status_code')
                ->orderBy('serial_number')
                ->get();
        }
    }

    public function refreshData()
    {
        $this->loadStats();
        if ($this->selectedOlt) {
            $this->loadOnus();
        }
    }

    public function render()
    {
        $olts = Olt::where('is_active', true)->get();
        
        return view('livewire.dashboard', [
            'olts' => $olts
        ]);
    }
}

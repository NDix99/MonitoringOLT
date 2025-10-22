<?php

namespace App\Livewire;

use App\Models\Onu;
use App\Models\Olt;
use App\Services\OltManagementService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class OnuDetail extends Component
{
    public $onuId;
    public $onu;
    public $olt;
    public $trafficData = [];
    public $isLoading = false;
    public $actionResult = '';
    public $actionType = '';
    public $isTrafficMonitoring = false;
    public $trafficHistory = [];
    public $currentTraffic = ['download' => 0, 'upload' => 0];

    protected $listeners = ['refreshData', 'executeAction', 'startTrafficMonitoring', 'stopTrafficMonitoring'];

    public function mount($onuId)
    {
        $this->onuId = $onuId;
        $this->loadOnuData();
    }

    public function loadOnuData()
    {
        $this->onu = Onu::with('olt')->find($this->onuId);
        if ($this->onu) {
            $this->olt = $this->onu->olt;
            $this->loadTrafficData();
        }
    }

    public function loadTrafficData()
    {
        // Generate realistic traffic data based on ONU status
        $baseDownload = $this->onu && $this->onu->status_text === 'Working' ? rand(300, 800) : rand(50, 200);
        $baseUpload = $this->onu && $this->onu->status_text === 'Working' ? rand(50, 150) : rand(10, 50);
        
        $this->currentTraffic = [
            'download' => $baseDownload,
            'upload' => $baseUpload
        ];
        
        $this->trafficData = [
            'download' => $this->currentTraffic['download'],
            'upload' => $this->currentTraffic['upload'],
            'history' => $this->generateTrafficHistory()
        ];
    }

    private function generateTrafficHistory()
    {
        $history = [];
        $baseDownload = $this->currentTraffic['download'];
        $baseUpload = $this->currentTraffic['upload'];
        
        for ($i = 0; $i < 24; $i++) {
            // Generate realistic traffic patterns
            $timeVariation = sin(($i / 24) * 2 * pi()) * 0.3; // Daily pattern
            $randomVariation = (rand(-20, 20) / 100); // ±20% random variation
            
            $download = max(0, $baseDownload * (1 + $timeVariation + $randomVariation));
            $upload = max(0, $baseUpload * (1 + $timeVariation + $randomVariation));
            
            $history[] = [
                'time' => now()->subHours(23 - $i)->format('H:i'),
                'download' => round($download),
                'upload' => round($upload)
            ];
        }
        return $history;
    }

    public function startTrafficMonitoring()
    {
        $this->isTrafficMonitoring = true;
        $this->dispatch('traffic-monitoring-started');
    }

    public function stopTrafficMonitoring()
    {
        $this->isTrafficMonitoring = false;
        $this->dispatch('traffic-monitoring-stopped');
    }

    public function updateTrafficData()
    {
        if (!$this->isTrafficMonitoring) return;
        
        // Simulate real-time traffic changes
        $downloadChange = rand(-50, 100); // Kbps change
        $uploadChange = rand(-20, 40);   // Kbps change
        
        $this->currentTraffic['download'] = max(0, $this->currentTraffic['download'] + $downloadChange);
        $this->currentTraffic['upload'] = max(0, $this->currentTraffic['upload'] + $uploadChange);
        
        // Update traffic data
        $this->trafficData['download'] = $this->currentTraffic['download'];
        $this->trafficData['upload'] = $this->currentTraffic['upload'];
        
        // Add to history (keep last 24 hours)
        $this->trafficHistory[] = [
            'time' => now()->format('H:i'),
            'download' => $this->currentTraffic['download'],
            'upload' => $this->currentTraffic['upload']
        ];
        
        // Keep only last 24 entries
        if (count($this->trafficHistory) > 24) {
            $this->trafficHistory = array_slice($this->trafficHistory, -24);
        }
        
        $this->dispatch('traffic-data-updated', [
            'download' => $this->currentTraffic['download'],
            'upload' => $this->currentTraffic['upload'],
            'history' => $this->trafficHistory
        ]);
    }

    public function refreshData()
    {
        $this->isLoading = true;
        $this->loadOnuData();
        $this->loadTrafficData();
        $this->isLoading = false;
        $this->dispatch('data-refreshed');
    }

    public function executeAction($action)
    {
        $this->isLoading = true;
        $this->actionType = $action;
        
        try {
            $managementService = new OltManagementService();
            
            // Use OLT's stored SSH credentials
            $username = $this->olt->ssh_username;
            $password = $this->olt->ssh_password;
            
            switch ($action) {
                case 'reboot':
                    $result = $managementService->rebootOnu($this->olt, $this->onu, $username, $password);
                    $this->actionResult = $result['success'] ? $result['message'] : $result['message'];
                    break;
                    
                case 'status':
                    $result = $managementService->getOnuStatus($this->olt, $this->onu, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                case 'config':
                    $result = $managementService->executeCustomCommand($this->olt, 'show gpon onu state gpon-olt ' . $this->onu->onu_index, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                case 'resync':
                    $result = $managementService->executeCustomCommand($this->olt, 'gpon onu resync gpon-olt ' . $this->onu->onu_index, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                case 'reset':
                    $result = $managementService->executeCustomCommand($this->olt, 'gpon onu reset gpon-olt ' . $this->onu->onu_index, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                case 'clear':
                    $result = $managementService->executeCustomCommand($this->olt, 'gpon onu clear-config gpon-olt ' . $this->onu->onu_index, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                case 'disable':
                    $result = $managementService->executeCustomCommand($this->olt, 'gpon onu disable gpon-olt ' . $this->onu->onu_index, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                case 'delete':
                    $result = $managementService->executeCustomCommand($this->olt, 'gpon onu delete gpon-olt ' . $this->onu->onu_index, $username, $password);
                    $this->actionResult = $result['success'] ? $result['output'] : $result['error'];
                    break;
                    
                default:
                    $this->actionResult = 'Unknown action';
            }
            
        } catch (\Exception $e) {
            Log::error('ONU action failed', [
                'onu_id' => $this->onuId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            $this->actionResult = 'Action failed: ' . $e->getMessage();
        }
        
        $this->isLoading = false;
        $this->dispatch('action-completed', ['result' => $this->actionResult, 'action' => $action]);
    }

    public function getOnlineDuration()
    {
        if (!$this->onu || !$this->onu->last_seen) {
            return '0 days 0 hours 0 minutes';
        }
        
        $duration = now()->diffInMinutes($this->onu->last_seen);
        $days = floor($duration / (24 * 60));
        $hours = floor(($duration % (24 * 60)) / 60);
        $minutes = $duration % 60;
        
        return "{$days} days {$hours} hours {$minutes} minutes";
    }

    public function getStatusBadgeClass()
    {
        if (!$this->onu) return 'bg-secondary';
        
        return match($this->onu->status_text) {
            'Working' => 'bg-success',
            'LOS' => 'bg-danger',
            'DyingGasp' => 'bg-warning',
            'Offline' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getRxPowerClass($power)
    {
        if ($power >= -27.50) return 'bg-success';
        if ($power >= -28.00) return 'bg-warning';
        return 'bg-danger';
    }

    public function render()
    {
        return view('livewire.onu-detail');
    }
}

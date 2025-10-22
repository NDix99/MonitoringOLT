<div class="container-fluid">
    @if($onu)
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('onus') }}">All ONUs</a></li>
                <li class="breadcrumb-item active">View ONU</li>
            </ol>
        </nav>

        <!-- ONU Details Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <h4 class="mb-0">ONU Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>OLT:</strong>
                            </div>
                            <div class="col-8">
                                {{ $olt->name ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Serial Number:</strong>
                            </div>
                            <div class="col-8">
                                <span class="me-2">{{ $onu->serial_number ?: 'N/A' }}</span>
                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Name:</strong>
                            </div>
                            <div class="col-8">
                                <span class="me-2">{{ $onu->onu_index }}</span>
                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Gpon Onu:</strong>
                            </div>
                            <div class="col-8">
                                <span class="badge bg-warning me-2">1/2/7:{{ $onu->onu_index }}</span>
                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Actual Type:</strong>
                            </div>
                            <div class="col-8">
                                {{ $onu->model ?: 'F660V5.2' }}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>OLT / Onu RX:</strong>
                            </div>
                            <div class="col-8">
                                <span class="badge {{ $this->getRxPowerClass($onu->rx_power) }} me-1">
                                    {{ number_format($onu->rx_power, 3) }} dBm
                                </span>
                                /
                                <span class="badge {{ $this->getRxPowerClass($onu->tx_power) }} ms-1">
                                    {{ number_format($onu->tx_power, 3) }} dBm
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Status:</strong>
                            </div>
                            <div class="col-8">
                                <span class="badge {{ $this->getStatusBadgeClass() }}">
                                    {{ $onu->status_text }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Onu Type:</strong>
                            </div>
                            <div class="col-8">
                                <span class="me-2">{{ $onu->vendor ?: 'ZTE' }}-{{ $onu->model ?: 'F660' }}</span>
                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Online Duration:</strong>
                            </div>
                            <div class="col-8">
                                {{ $this->getOnlineDuration() }}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Description:</strong>
                            </div>
                            <div class="col-8">
                                <small class="text-muted me-2">
                                    {{ json_encode([
                                        'alamat' => 'Goito',
                                        'latitude' => '-7.2447597',
                                        'longitude' => '111.4991977',
                                        'ODP' => 'Belum Diketahui',
                                        'eth_data' => '',
                                        'wifi_data' => ''
                                    ]) }}
                                </small>
                                <button class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traffic Monitoring -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Traffic Monitoring</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="trafficMonitoringToggle" 
                               wire:model="isTrafficMonitoring" wire:click="startTrafficMonitoring">
                        <label class="form-check-label" for="trafficMonitoringToggle">
                            Real-time Monitoring
                        </label>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="traffic-indicator me-2" id="trafficIndicator"></div>
                        <small class="text-muted" id="lastUpdate">Last update: {{ now()->format('H:i:s') }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Current Speed -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cloud-download-alt text-danger me-2"></i>
                            <strong>Download Speed:</strong>
                            <span class="ms-2 traffic-download" id="currentDownload">
                                {{ number_format($trafficData['download'], 1) }} Kbps
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-cloud-upload-alt text-primary me-2"></i>
                            <strong>Upload Speed:</strong>
                            <span class="ms-2 traffic-upload" id="currentUpload">
                                {{ number_format($trafficData['upload'], 1) }} Kbps
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Traffic Chart -->
                <div class="row">
                    <div class="col-12">
                        <canvas id="trafficChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">ONU Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-primary" wire:click="executeAction('reboot')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-redo"></i> Reboot
                            </button>
                            
                            <button class="btn btn-primary" wire:click="executeAction('status')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-globe"></i> Get Status
                            </button>
                            
                            <button class="btn btn-primary" wire:click="executeAction('config')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-file-alt"></i> Show Config
                            </button>
                            
                            <button class="btn btn-success" wire:click="executeAction('resync')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-sync"></i> Resync Config
                            </button>
                            
                            <button class="btn btn-warning" wire:click="executeAction('reset')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-redo"></i> Reset ONU
                            </button>
                            
                            <button class="btn btn-info" wire:click="executeAction('clear')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-gem"></i> Clear Config
                            </button>
                            
                            <button class="btn btn-dark" wire:click="executeAction('disable')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-power-off"></i> Disable ONU
                            </button>
                            
                            <button class="btn btn-danger" wire:click="executeAction('delete')" 
                                    @if($isLoading) disabled @endif>
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Result -->
                @if($actionResult)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>{{ ucfirst($actionType) }} Result:</strong> {{ $actionResult }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Loading Indicator -->
                @if($isLoading)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span>Executing {{ $actionType }} action...</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Refresh Button -->
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary" wire:click="refreshData" @if($isLoading) disabled @endif>
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>

    @else
        <div class="alert alert-danger">
            <h4>ONU Not Found</h4>
            <p>The requested ONU could not be found.</p>
            <a href="{{ route('onus') }}" class="btn btn-primary">Back to ONUs</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let trafficChart = null;
    let trafficUpdateInterval = null;
    let isMonitoring = false;

    document.addEventListener('livewire:init', () => {
        // Initialize Traffic Chart
        const ctx = document.getElementById('trafficChart');
        if (ctx) {
            const trafficData = @json($trafficData['history']);
            
            trafficChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: trafficData.map(item => item.time),
                    datasets: [{
                        label: 'Download',
                        data: trafficData.map(item => item.download),
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Upload',
                        data: trafficData.map(item => item.upload),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1000,
                            ticks: {
                                stepSize: 100
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Traffic Monitoring Toggle
        const toggle = document.getElementById('trafficMonitoringToggle');
        if (toggle) {
            toggle.addEventListener('change', function() {
                if (this.checked) {
                    startTrafficMonitoring();
                } else {
                    stopTrafficMonitoring();
                }
            });
        }

        // Listen for Livewire events
        Livewire.on('traffic-monitoring-started', () => {
            startTrafficMonitoring();
        });

        Livewire.on('traffic-monitoring-stopped', () => {
            stopTrafficMonitoring();
        });

        Livewire.on('traffic-data-updated', (data) => {
            updateTrafficDisplay(data[0]);
        });

        // Auto refresh ONU data every 30 seconds (not traffic)
        setInterval(() => {
            @this.call('refreshData');
        }, 30000);
    });

    function startTrafficMonitoring() {
        if (isMonitoring) return;
        
        isMonitoring = true;
        updateTrafficIndicator(true);
        
        // Update traffic data every 5 seconds
        trafficUpdateInterval = setInterval(() => {
            @this.call('updateTrafficData');
        }, 5000);
        
        console.log('Traffic monitoring started');
    }

    function stopTrafficMonitoring() {
        if (!isMonitoring) return;
        
        isMonitoring = false;
        updateTrafficIndicator(false);
        
        if (trafficUpdateInterval) {
            clearInterval(trafficUpdateInterval);
            trafficUpdateInterval = null;
        }
        
        console.log('Traffic monitoring stopped');
    }

    function updateTrafficDisplay(data) {
        // Update current speed display
        const downloadElement = document.getElementById('currentDownload');
        const uploadElement = document.getElementById('currentUpload');
        
        if (downloadElement) {
            downloadElement.textContent = data.download.toFixed(1) + ' Kbps';
            downloadElement.style.color = data.download > 500 ? '#dc3545' : data.download > 200 ? '#ffc107' : '#28a745';
        }
        
        if (uploadElement) {
            uploadElement.textContent = data.upload.toFixed(1) + ' Kbps';
            uploadElement.style.color = data.upload > 100 ? '#dc3545' : data.upload > 50 ? '#ffc107' : '#28a745';
        }

        // Update chart
        if (trafficChart && data.history) {
            trafficChart.data.labels = data.history.map(item => item.time);
            trafficChart.data.datasets[0].data = data.history.map(item => item.download);
            trafficChart.data.datasets[1].data = data.history.map(item => item.upload);
            trafficChart.update('active');
        }

        // Update last update time
        const lastUpdateElement = document.getElementById('lastUpdate');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = 'Last update: ' + new Date().toLocaleTimeString();
        }
    }

    function updateTrafficIndicator(active) {
        const indicator = document.getElementById('trafficIndicator');
        if (indicator) {
            if (active) {
                indicator.innerHTML = '<i class="fas fa-circle text-success"></i>';
                indicator.title = 'Monitoring Active';
            } else {
                indicator.innerHTML = '<i class="fas fa-circle text-muted"></i>';
                indicator.title = 'Monitoring Inactive';
            }
        }
    }

    // Initialize traffic indicator
    document.addEventListener('DOMContentLoaded', () => {
        updateTrafficIndicator(false);
    });
</script>

<style>
    .traffic-indicator {
        font-size: 0.8rem;
    }
    
    .traffic-download, .traffic-upload {
        font-weight: bold;
        transition: color 0.3s ease;
    }
    
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
</style>
@endpush

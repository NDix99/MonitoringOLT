<div>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">OLT Monitoring Dashboard</h1>
                <p class="text-muted">Real-time monitoring of ZTE C320 OLT devices</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total OLTs</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_olts'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-server fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Online ONUs</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['online_onus'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Offline ONUs</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['offline_onus'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">DyingGasp ONUs</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['dying_gasp_onus'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OLT Selection and ONU List -->
        <div class="row">
            <!-- OLT List -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">OLT Devices</h6>
                        <button wire:click="refreshData" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        @forelse($olts as $olt)
                            <div class="card mb-2 cursor-pointer" 
                                 wire:click="selectOlt({{ $olt->id }})"
                                 style="cursor: pointer; {{ $selectedOlt == $olt->id ? 'border-left: 4px solid #007bff;' : '' }}">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">{{ $olt->name }}</h6>
                                    <p class="card-text small text-muted mb-1">{{ $olt->ip_address }}</p>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-success">Online: {{ $olt->active_onus_count }}</small>
                                        <small class="text-danger">Offline: {{ $olt->offline_onus_count }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No OLT devices found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- ONU List -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            @if($selectedOlt)
                                ONUs for {{ $olts->where('id', $selectedOlt)->first()->name ?? 'Selected OLT' }}
                            @else
                                Select an OLT to view ONUs
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($selectedOlt && count($onus) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Serial Number</th>
                                            <th>Status</th>
                                            <th>RX Power</th>
                                            <th>TX Power</th>
                                            <th>Model</th>
                                            <th>Last Seen</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($onus as $onu)
                                            <tr>
                                                <td>
                                                    <code>{{ $onu->serial_number }}</code>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $onu->status_color }}">
                                                        {{ $onu->status_badge }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($onu->rx_power)
                                                        <span class="{{ $onu->rx_power < -28 ? 'text-danger' : 'text-success' }}">
                                                            {{ number_format($onu->rx_power, 2) }} dBm
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($onu->tx_power)
                                                        {{ number_format($onu->tx_power, 2) }} dBm
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>{{ $onu->model ?? 'Unknown' }}</td>
                                                <td>
                                                    @if($onu->last_seen)
                                                        {{ $onu->last_seen->diffForHumans() }}
                                                    @else
                                                        <span class="text-muted">Never</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-info" 
                                                                wire:click="$emit('showOnuChart', {{ $onu->id }})"
                                                                title="View Chart">
                                                            <i class="fas fa-chart-line"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning" 
                                                                wire:click="$emit('showManagementModal', {{ $onu->olt_id }}, {{ $onu->id }})"
                                                                title="Manage ONU">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($selectedOlt)
                            <p class="text-muted">No ONUs found for this OLT.</p>
                        @else
                            <p class="text-muted">Please select an OLT to view its ONUs.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh script -->
    <script>
        setInterval(function() {
            @this.call('refreshData');
        }, 30000); // Refresh every 30 seconds
    </script>
</div>
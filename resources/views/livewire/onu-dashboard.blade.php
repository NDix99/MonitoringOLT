<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">All-ONUs</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">All-ONUs</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Good Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="card-title mb-1">Good</h6>
                            <span class="badge bg-success">≥ -27.50 dBm</span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ $stats['good']['percentage'] }}%</h4>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-chart-bar text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">RX OLT ({{ $stats['good']['count'] }})</small>
                    </div>
                    <div class="progress mb-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['good']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-chart-bar text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">RX ONU ({{ $stats['good']['count'] }})</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['good']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="card-title mb-1">Warning</h6>
                            <span class="badge bg-warning">-27.50 ~ -28.00 dBm</span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ $stats['warning']['percentage'] }}%</h4>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-chart-bar text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">RX OLT ({{ $stats['warning']['count'] }})</small>
                    </div>
                    <div class="progress mb-2" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['warning']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-chart-bar text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">RX ONU ({{ $stats['warning']['count'] }})</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['warning']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="card-title mb-1">Critical</h6>
                            <span class="badge bg-danger"><-28.00 dBm</span>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ $stats['critical']['percentage'] }}%</h4>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-chart-bar text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">RX OLT ({{ $stats['critical']['count'] }})</small>
                    </div>
                    <div class="progress mb-2" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: {{ $stats['critical']['percentage'] }}%"></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-chart-bar text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">RX ONU ({{ $stats['critical']['count'] }})</small>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: {{ $stats['critical']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="card-title mb-1">Other</h6>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">{{ $stats['other']['percentage'] }}%</h4>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-times text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">LOS ({{ $stats['other']['count'] }})</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                            <i class="fas fa-question text-white" style="font-size: 10px;"></i>
                        </div>
                        <small class="text-muted">N/A ({{ $stats['other']['count'] }})</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ONUs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">ONUs</h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            All OLTs
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" wire:click="$set('oltFilter', 'all')">All OLTs</a></li>
                            @foreach($olts as $olt)
                                <li><a class="dropdown-item" href="#" wire:click="$set('oltFilter', '{{ $olt->id }}')">{{ $olt->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            All Cards
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" wire:click="$set('statusFilter', 'all')">All Cards</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('statusFilter', 'Working')">Working</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="$set('statusFilter', 'LOS')">LOS</a></li>
                        </ul>
                    </div>
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            All Ports
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Ports</a></li>
                        </ul>
                    </div>
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            All Types
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Types</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Status:</span>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm {{ $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('statusFilter', 'all')">
                                <i class="fas fa-check text-success"></i>
                            </button>
                            <button type="button" class="btn btn-sm {{ $statusFilter === 'Working' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('statusFilter', 'Working')">
                                <i class="fas fa-plug text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-sm {{ $statusFilter === 'LOS' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('statusFilter', 'LOS')">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center justify-content-end">
                        <span class="me-2">Signal:</span>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm {{ $signalFilter === 'good' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('signalFilter', 'good')">
                                <i class="fas fa-signal text-success"></i>
                            </button>
                            <button type="button" class="btn btn-sm {{ $signalFilter === 'warning' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('signalFilter', 'warning')">
                                <i class="fas fa-signal text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-sm {{ $signalFilter === 'critical' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('signalFilter', 'critical')">
                                <i class="fas fa-signal text-danger"></i>
                            </button>
                            <button type="button" class="btn btn-sm {{ $signalFilter === 'other' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="$set('signalFilter', 'other')">
                                <i class="fas fa-ellipsis-h text-secondary"></i>
                            </button>
                        </div>
                        <a href="{{ route('onus.export') }}" class="btn btn-success btn-sm ms-3">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination and Search -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Show</span>
                        <select class="form-select form-select-sm" style="width: auto;" wire:model.live="perPage">
                            <option value="5">5 entries</option>
                            <option value="10">10 entries</option>
                            <option value="25">25 entries</option>
                            <option value="50">50 entries</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">Search:</span>
                            <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search ONUs...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input">
                            </th>
                            <th wire:click="sortBy('olt.name')" style="cursor: pointer;">
                                OLT <i class="fas fa-sort"></i>
                            </th>
                            <th wire:click="sortBy('onu_index')" style="cursor: pointer;">
                                Name <i class="fas fa-sort"></i>
                            </th>
                            <th>Description</th>
                            <th>PPPoE</th>
                            <th>Gpon Onu</th>
                            <th wire:click="sortBy('status_text')" style="cursor: pointer;">
                                Status <i class="fas fa-sort"></i>
                            </th>
                            <th wire:click="sortBy('rx_power')" style="cursor: pointer;">
                                RX OLT <i class="fas fa-sort"></i>
                            </th>
                            <th wire:click="sortBy('tx_power')" style="cursor: pointer;">
                                RX ONU <i class="fas fa-sort"></i>
                            </th>
                            <th wire:click="sortBy('serial_number')" style="cursor: pointer;">
                                Serial Number <i class="fas fa-sort"></i>
                            </th>
                            <th>Actual Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($onus as $onu)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input">
                                </td>
                                <td>{{ $onu->olt ? $onu->olt->name : 'N/A' }}</td>
                                <td>{{ $onu->onu_index }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ json_encode([
                                            'alamat' => 'Goito',
                                            'latitude' => '-7.2447597',
                                            'longitude' => '111.4991977',
                                            'ODP' => 'Belum Diketahui',
                                            'eth_data' => '',
                                            'wifi_da' => ''
                                        ]) }}
                                    </small>
                                </td>
                                <td>{{ $onu->serial_number }}@menden.net</td>
                                <td>1/2/7:{{ $onu->onu_index }}</td>
                                <td>
                                    @if($onu->status_text === 'Working')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Online
                                        </span>
                                    @elseif($onu->status_text === 'LOS')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times"></i> LOS
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-question"></i> {{ $onu->status_text }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($onu->rx_power >= -27.50)
                                        <span class="badge bg-success">
                                            <i class="fas fa-chart-bar"></i> {{ number_format($onu->rx_power, 3) }} dBm
                                        </span>
                                    @elseif($onu->rx_power >= -28.00)
                                        <span class="badge bg-warning">
                                            <i class="fas fa-chart-bar"></i> {{ number_format($onu->rx_power, 3) }} dBm
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-chart-bar"></i> {{ number_format($onu->rx_power, 3) }} dBm
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($onu->tx_power >= -27.50)
                                        <span class="badge bg-success">
                                            <i class="fas fa-chart-bar"></i> {{ number_format($onu->tx_power, 3) }} dBm
                                        </span>
                                    @elseif($onu->tx_power >= -28.00)
                                        <span class="badge bg-warning">
                                            <i class="fas fa-chart-bar"></i> {{ number_format($onu->tx_power, 3) }} dBm
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-chart-bar"></i> {{ number_format($onu->tx_power, 3) }} dBm
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $onu->serial_number ?: 'N/A' }}</td>
                                <td>{{ $onu->model ?: 'F660V5.2' }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-cog"></i> Setting
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No ONUs found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $onus->firstItem() ?? 0 }} to {{ $onus->lastItem() ?? 0 }} of {{ $onus->total() }} entries
                </div>
                <div>
                    {{ $onus->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

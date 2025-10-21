<div>
    @if($onu)
        <div class="modal fade" id="onuChartModal" tabindex="-1" role="dialog" aria-labelledby="onuChartModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="onuChartModalLabel">
                            ONU Chart - {{ $onu->serial_number }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>OLT:</strong> {{ $onu->olt->name }}<br>
                                <strong>Serial:</strong> {{ $onu->serial_number }}<br>
                                <strong>Status:</strong> 
                                <span class="badge badge-{{ $onu->status_color }}">{{ $onu->status_badge }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Current RX Power:</strong> 
                                @if($onu->rx_power)
                                    <span class="{{ $onu->rx_power < -28 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($onu->rx_power, 2) }} dBm
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                                <br>
                                <strong>Last Seen:</strong> 
                                @if($onu->last_seen)
                                    {{ $onu->last_seen->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </div>
                        </div>

                        <!-- Time Range Selector -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm {{ $hours == 1 ? 'btn-primary' : 'btn-outline-primary' }}"
                                            wire:click="updateHours(1)">1 Hour</button>
                                    <button type="button" 
                                            class="btn btn-sm {{ $hours == 6 ? 'btn-primary' : 'btn-outline-primary' }}"
                                            wire:click="updateHours(6)">6 Hours</button>
                                    <button type="button" 
                                            class="btn btn-sm {{ $hours == 24 ? 'btn-primary' : 'btn-outline-primary' }}"
                                            wire:click="updateHours(24)">24 Hours</button>
                                    <button type="button" 
                                            class="btn btn-sm {{ $hours == 168 ? 'btn-primary' : 'btn-outline-primary' }}"
                                            wire:click="updateHours(168)">7 Days</button>
                                </div>
                            </div>
                        </div>

                        <!-- Chart Container -->
                        <div class="row">
                            <div class="col-12">
                                <canvas id="onuChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('onuChart').getContext('2d');
                
                const chartData = @json($chartData);
                
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: 'RX Power (dBm)'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Time'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' dBm';
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });

                // Show modal when component is loaded
                $('#onuChartModal').modal('show');
                
                // Update chart when data changes
                Livewire.on('chartUpdated', () => {
                    chart.data = @json($chartData);
                    chart.update();
                });
            });
        </script>
    @endif
</div>
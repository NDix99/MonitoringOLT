<div>
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">OLT Management</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" wire:model="username" placeholder="Enter OLT username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" wire:model="password" placeholder="Enter OLT password">
                                </div>
                            </div>
                        </div>

                        @if($selectedOnu)
                            <div class="mb-3">
                                <h6>ONU Management</h6>
                                <div class="d-grid gap-2 d-md-flex">
                                    <button class="btn btn-warning" wire:click="rebootOnu">
                                        <i class="fas fa-power-off me-1"></i>
                                        Reboot ONU
                                    </button>
                                    <button class="btn btn-info" wire:click="getOnuStatus">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Get ONU Status
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <h6>Custom Command</h6>
                            <div class="input-group">
                                <input type="text" class="form-control" wire:model="command" placeholder="Enter CLI command">
                                <button class="btn btn-primary" wire:click="executeCommand">
                                    <i class="fas fa-play me-1"></i>
                                    Execute
                                </button>
                            </div>
                        </div>

                        @if($output)
                            <div class="mb-3">
                                <h6>Command Output</h6>
                                <pre class="bg-light p-3 border rounded" style="max-height: 300px; overflow-y: auto;">{{ $output }}</pre>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
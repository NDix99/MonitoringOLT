<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-terminal me-2"></i>
                SSH Settings
            </h2>
            <p class="text-muted">Configure SSH credentials for OLT management</p>
        </div>
    </div>

    <div class="row">
        <!-- OLT Selection -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>
                        Select OLT
                    </h5>
                </div>
                <div class="card-body">
                    @if($olts->count() > 0)
                        <div class="list-group">
                            @foreach($olts as $olt)
                                <button type="button" 
                                        class="list-group-item list-group-item-action {{ $selectedOlt && $selectedOlt->id == $olt->id ? 'active' : '' }}"
                                        wire:click="oltSelected({{ $olt->id }})">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $olt->name }}</h6>
                                        <small class="text-muted">{{ $olt->ip_address }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $olt->description ?: 'No description' }}</small>
                                        <div>
                                            @if($olt->ssh_enabled)
                                                <span class="badge bg-success">SSH Enabled</span>
                                            @else
                                                <span class="badge bg-secondary">SSH Disabled</span>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-server fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No OLTs found. Please add an OLT first.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SSH Configuration -->
        <div class="col-md-8">
            @if($selectedOlt)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            SSH Configuration - {{ $selectedOlt->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveSettings">
                            <div class="row">
                                <!-- SSH Username -->
                                <div class="col-md-6 mb-3">
                                    <label for="sshUsername" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        SSH Username
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="sshUsername"
                                           wire:model="sshUsername"
                                           placeholder="Enter SSH username">
                                </div>

                                <!-- SSH Port -->
                                <div class="col-md-6 mb-3">
                                    <label for="sshPort" class="form-label">
                                        <i class="fas fa-network-wired me-1"></i>
                                        SSH Port
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="sshPort"
                                           wire:model="sshPort"
                                           min="1" 
                                           max="65535"
                                           placeholder="22">
                                </div>
                            </div>

                            <!-- SSH Password -->
                            <div class="mb-3">
                                <label for="sshPassword" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    SSH Password
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="sshPassword"
                                           wire:model="sshPassword"
                                           placeholder="Enter SSH password">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- SSH Enabled -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="sshEnabled"
                                           wire:model="sshEnabled">
                                    <label class="form-check-label" for="sshEnabled">
                                        <i class="fas fa-power-off me-1"></i>
                                        Enable SSH Management
                                    </label>
                                </div>
                                <small class="text-muted">
                                    When enabled, this OLT can be managed via SSH commands
                                </small>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-outline-primary"
                                        wire:click="testConnection"
                                        wire:loading.attr="disabled"
                                        @if($isTesting) disabled @endif>
                                    <span wire:loading.remove wire:target="testConnection">
                                        <i class="fas fa-plug me-1"></i>
                                        Test Connection
                                    </span>
                                    <span wire:loading wire:target="testConnection">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Testing...
                                    </span>
                                </button>

                                <button type="submit" 
                                        class="btn btn-success"
                                        wire:loading.attr="disabled"
                                        @if($isSaving) disabled @endif>
                                    <span wire:loading.remove wire:target="saveSettings">
                                        <i class="fas fa-save me-1"></i>
                                        Save Settings
                                    </span>
                                    <span wire:loading wire:target="saveSettings">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Saving...
                                    </span>
                                </button>

                                <button type="button" 
                                        class="btn btn-outline-secondary"
                                        wire:click="resetForm">
                                    <i class="fas fa-undo me-1"></i>
                                    Reset
                                </button>
                            </div>
                        </form>

                        <!-- Test Result -->
                        @if($testResult)
                            <div class="mt-3">
                                <div class="alert {{ str_contains($testResult, '✅') ? 'alert-success' : 'alert-danger' }}">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $testResult }}
                                </div>
                            </div>
                        @endif

                        <!-- Save Result -->
                        @if($saveResult)
                            <div class="mt-3">
                                <div class="alert {{ str_contains($saveResult, '✅') ? 'alert-success' : 'alert-danger' }}">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $saveResult }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-server fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Select an OLT to configure SSH settings</h5>
                        <p class="text-muted">Choose an OLT from the list to configure its SSH credentials</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- SSH Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        SSH Configuration Guide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog me-1"></i> ZTE C320 Configuration</h6>
                            <div class="bg-light p-3 rounded">
                                <code>
                                    configure terminal<br>
                                    ip ssh server<br>
                                    ip ssh version 2<br>
                                    username admin password admin<br>
                                    enable password admin
                                </code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt me-1"></i> Security Notes</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-1"></i> Use strong passwords</li>
                                <li><i class="fas fa-check text-success me-1"></i> Change default credentials</li>
                                <li><i class="fas fa-check text-success me-1"></i> Enable SSH version 2 only</li>
                                <li><i class="fas fa-check text-success me-1"></i> Test connection before saving</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('sshPassword');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'fas fa-eye';
        }
    }

    // Auto-refresh OLT list every 30 seconds
    setInterval(() => {
        @this.call('loadOlts');
    }, 30000);
</script>
@endpush
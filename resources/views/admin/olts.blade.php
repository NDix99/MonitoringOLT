@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-server me-2"></i>
                OLT Device Management
            </h1>
            <p class="text-muted">Manage OLT devices and configurations</p>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">OLT Devices</h6>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOltModal">
                <i class="fas fa-plus me-1"></i>
                Add OLT Device
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Community</th>
                            <th>Port</th>
                            <th>ONUs</th>
                            <th>Status</th>
                            <th>Last Poll</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($olts as $olt)
                            <tr>
                                <td>{{ $olt->id }}</td>
                                <td>{{ $olt->name }}</td>
                                <td>{{ $olt->ip_address }}</td>
                                <td>{{ $olt->community_string }}</td>
                                <td>{{ $olt->snmp_port }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $olt->onus_count }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $olt->is_active ? 'success' : 'danger' }}">
                                        {{ $olt->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if($olt->updated_at)
                                        {{ $olt->updated_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.olts.edit', $olt) }}" class="btn btn-sm btn-warning" title="Edit OLT">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-info test-snmp-btn" 
                                                data-olt-id="{{ $olt->id }}" 
                                                title="Test SNMP">
                                            <i class="fas fa-network-wired"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary test-ssh-btn" 
                                                data-olt-id="{{ $olt->id }}" 
                                                data-olt-ip="{{ $olt->ip_address }}"
                                                title="Test SSH">
                                            <i class="fas fa-terminal"></i>
                                        </button>
                                        <form action="{{ route('admin.olts.toggle', $olt) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-{{ $olt->is_active ? 'danger' : 'success' }}" 
                                                    title="{{ $olt->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $olt->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.olts.destroy', $olt) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this OLT?')"
                                                    title="Delete OLT">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No OLT devices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($olts->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $olts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add OLT Modal -->
<div class="modal fade" id="addOltModal" tabindex="-1" aria-labelledby="addOltModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOltModalLabel">Add New OLT Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.olts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">OLT Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ip_address" class="form-label">IP Address</label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="community_string" class="form-label">Community String</label>
                                <input type="text" class="form-control" id="community_string" name="community_string" value="public">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="snmp_port" class="form-label">SNMP Port</label>
                                <input type="number" class="form-control" id="snmp_port" name="snmp_port" value="161">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="snmp_version" class="form-label">SNMP Version</label>
                                <select class="form-control" id="snmp_version" name="snmp_version">
                                    <option value="1">SNMP v1</option>
                                    <option value="2" selected>SNMP v2c</option>
                                    <option value="3">SNMP v3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="polling_interval" class="form-label">Polling Interval (seconds)</label>
                                <input type="number" class="form-control" id="polling_interval" name="polling_interval" value="60">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add OLT Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SSH Test Modal -->
<div class="modal fade" id="sshTestModal" tabindex="-1" aria-labelledby="sshTestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sshTestModalLabel">Test SSH Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sshTestForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ssh_username" class="form-label">SSH Username</label>
                        <input type="text" class="form-control" id="ssh_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="ssh_password" class="form-label">SSH Password</label>
                        <input type="password" class="form-control" id="ssh_password" name="password" required>
                    </div>
                    <div id="ssh-result" class="mt-3" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Test SSH</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test SNMP Connection
    document.querySelectorAll('.test-snmp-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const oltId = this.dataset.oltId;
            const originalText = this.innerHTML;
            
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            fetch(`/admin/olts/${oltId}/test-snmp`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ SNMP Connection: SUCCESS\nSystem: ' + data.system_description);
                        this.innerHTML = '<i class="fas fa-check text-success"></i>';
                    } else {
                        alert('✗ SNMP Connection: FAILED\n' + data.message);
                        this.innerHTML = '<i class="fas fa-times text-danger"></i>';
                    }
                })
                .catch(error => {
                    alert('✗ SNMP Connection: FAILED\n' + error.message);
                    this.innerHTML = '<i class="fas fa-times text-danger"></i>';
                })
                .finally(() => {
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 3000);
                });
        });
    });
    
    // Test SSH Connection
    let currentOltId = null;
    document.querySelectorAll('.test-ssh-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentOltId = this.dataset.oltId;
            const oltIp = this.dataset.oltIp;
            document.getElementById('sshTestModalLabel').textContent = `Test SSH Connection - ${oltIp}`;
            new bootstrap.Modal(document.getElementById('sshTestModal')).show();
        });
    });
    
    // SSH Test Form
    document.getElementById('sshTestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const resultDiv = document.getElementById('ssh-result');
        
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Testing SSH connection...</div>';
        
        fetch(`/admin/olts/${currentOltId}/test-ssh`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check"></i> SSH Connection: SUCCESS<br>
                        <small>Output: ${data.output}</small>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times"></i> SSH Connection: FAILED<br>
                        <small>${data.message}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times"></i> SSH Connection: FAILED<br>
                    <small>${error.message}</small>
                </div>
            `;
        });
    });
});
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Add New OLT Device</h4>
                    <a href="{{ route('admin.olts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to OLTs
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.olts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">OLT Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                           id="ip_address" name="ip_address" value="{{ old('ip_address') }}" 
                                           placeholder="192.168.1.100" required>
                                    @error('ip_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="community_string" class="form-label">SNMP Community <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('community_string') is-invalid @enderror" 
                                           id="community_string" name="community_string" value="{{ old('community_string', 'public') }}" required>
                                    @error('community_string')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="snmp_port" class="form-label">SNMP Port <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('snmp_port') is-invalid @enderror" 
                                           id="snmp_port" name="snmp_port" value="{{ old('snmp_port', 161) }}" 
                                           min="1" max="65535" required>
                                    @error('snmp_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="snmp_version" class="form-label">SNMP Version <span class="text-danger">*</span></label>
                                    <select class="form-select @error('snmp_version') is-invalid @enderror" 
                                            id="snmp_version" name="snmp_version" required>
                                        <option value="1" {{ old('snmp_version', 2) == 1 ? 'selected' : '' }}>SNMP v1</option>
                                        <option value="2" {{ old('snmp_version', 2) == 2 ? 'selected' : '' }}>SNMP v2c</option>
                                        <option value="3" {{ old('snmp_version', 2) == 3 ? 'selected' : '' }}>SNMP v3</option>
                                    </select>
                                    @error('snmp_version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="polling_interval" class="form-label">Polling Interval (seconds) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('polling_interval') is-invalid @enderror" 
                                           id="polling_interval" name="polling_interval" value="{{ old('polling_interval', 60) }}" 
                                           min="30" max="3600" required>
                                    <div class="form-text">Recommended: 60-300 seconds</div>
                                    @error('polling_interval')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active (Enable monitoring)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Optional description for this OLT device">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.olts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add OLT Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

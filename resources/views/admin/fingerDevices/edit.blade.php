@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Modify Biometric Device Settings</h4>
    <p class="text-secondary font-13 mb-0">Update names, locations, and override security states of registered biometric readers.</p>
</div>
@endsection

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8 col-lg-6">
        <div class="card saas-card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-pencil text-primary mr-2"></i> Update Settings: {{ $fingerDevice->name }}</h5>
            </div>
            
            <form method="POST" action="{{ route('finger_device.update', $fingerDevice->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    
                    @if($errors->any())
                        <div class="alert alert-danger mb-3" style="border-radius: 8px;">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Device Unique ID (Read-only)</label>
                        <input type="text" class="form-control form-control-saas bg-light" value="{{ $fingerDevice->device_id }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Serial Number (Read-only)</label>
                        <input type="text" class="form-control form-control-saas bg-light" value="{{ $fingerDevice->serialNumber }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Device Name</label>
                        <input type="text" name="name" class="form-control form-control-saas" value="{{ old('name', $fingerDevice->name) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">IP Address</label>
                        <input type="text" name="ip" class="form-control form-control-saas" value="{{ old('ip', $fingerDevice->ip) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Terminal Location</label>
                        <input type="text" name="location" class="form-control form-control-saas" value="{{ old('location', $fingerDevice->location) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Status</label>
                        <select name="status" class="form-control form-control-saas" required>
                            <option value="Online" {{ $fingerDevice->status === 'Online' ? 'selected' : '' }}>Online / Active</option>
                            <option value="Offline" {{ $fingerDevice->status === 'Offline' ? 'selected' : '' }}>Offline</option>
                            <option value="Inactive" {{ $fingerDevice->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Blocked" {{ $fingerDevice->status === 'Blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                </div>
                
                <div class="card-footer bg-light border-top p-3 px-4 d-flex justify-content-between">
                    <a href="{{ route('finger_device.index') }}" class="btn btn-saas btn-saas-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

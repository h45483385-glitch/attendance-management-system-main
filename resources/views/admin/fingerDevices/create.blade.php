@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Register Biometric Device</h4>
    <p class="text-secondary font-13 mb-0">Add connection protocols, unique device identifiers, and locations for biometric terminals.</p>
</div>
@endsection

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8 col-lg-6">
        <div class="card saas-card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-server text-primary mr-2"></i> Device Parameters</h5>
            </div>
            
            <form method="POST" action="{{ route('finger_device.store') }}">
                @csrf
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
                        <label class="font-weight-bold text-muted font-12 mb-1">Device Name</label>
                        <input type="text" name="name" class="form-control form-control-saas" value="{{ old('name') }}" placeholder="e.g. Main Entrance Reader" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Device Unique ID</label>
                        <input type="text" name="device_id" class="form-control form-control-saas" value="{{ old('device_id') }}" placeholder="e.g. ATT-BIO-001" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">IP Address</label>
                        <input type="text" name="ip" class="form-control form-control-saas" value="{{ old('ip') }}" placeholder="e.g. 192.168.1.201" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Device Type</label>
                        <select name="type" class="form-control form-control-saas" required>
                            <option value="Fingerprint">Fingerprint Machine</option>
                            <option value="Face Recognition">Face Recognition Terminal</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Terminal Location</label>
                        <input type="text" name="location" class="form-control form-control-saas" value="{{ old('location') }}" placeholder="e.g. Lobby Entrance Floor 1" required>
                    </div>

                </div>
                
                <div class="card-footer bg-light border-top p-3 px-4 d-flex justify-content-between">
                    <a href="{{ route('finger_device.index') }}" class="btn btn-saas btn-saas-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4 shadow-sm">Register Device</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

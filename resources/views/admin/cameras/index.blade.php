@extends('layouts.master')

@section('css')
<style>
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
    }
    
    .status-badge {
        font-weight: 600;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-connected { background-color: #dcfce7; color: #166534; }
    .badge-disconnected { background-color: #f1f5f9; color: #475569; }
    .badge-disabled { background-color: #fee2e2; color: #991b1b; }
    .badge-error { background-color: #fef3c7; color: #d97706; }

    .form-switch-saas {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 22px;
    }
    .form-switch-saas input { opacity: 0; width: 0; height: 0; }
    .slider-saas {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 20px;
    }
    .slider-saas:before {
        position: absolute;
        content: "";
        height: 16px; width: 16px;
        left: 3px; bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .slider-saas { background-color: #22C55E; }
    input:checked + .slider-saas:before { transform: translateX(22px); }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Attendance Cameras</h4>
    <p class="text-secondary font-13 mb-0">Register and monitor face recognition cameras linked to biometric terminals.</p>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        <!-- Validation Banners -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                <strong class="font-weight-bold"><i class="ti-check-box mr-1"></i> Success:</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                <strong class="font-weight-bold"><i class="ti-alert mr-1"></i> Error:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card saas-card">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-video-camera text-primary mr-2"></i> Camera Registry</h5>
                <button class="btn btn-saas btn-saas-primary px-4 shadow-sm" data-toggle="modal" data-target="#addCameraModal">
                    <i class="ti-plus mr-1"></i> Register Camera
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Camera Details</th>
                                <th>IDs & Association</th>
                                <th>IP Connection</th>
                                <th>Camera Type</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Active</th>
                                <th>Last Pulse</th>
                                <th class="text-right" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cameras as $cam)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3" style="background: #f1f5f9; color: #475569; box-shadow: none;">
                                            <i class="ti-video-camera font-16"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $cam->camera_name }}</h6>
                                            <span class="text-muted font-11">Branch: {{ $cam->branch ?? 'N/A' }} | Gate: {{ $cam->gate ?? 'N/A' }}</span><br>
                                            <span class="text-muted font-11">Loc: {{ $cam->location }} | Dept: {{ $cam->assigned_department ?? 'All' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark font-12 d-block">Cam: {{ $cam->camera_id }}</span>
                                    <span class="text-muted font-11">Dev: {{ $cam->device_id ?? 'None' }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-secondary font-12" style="font-family: monospace;">{{ $cam->ip }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary font-11">{{ $cam->camera_type }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusClass = 'badge-disconnected';
                                        if(in_array($cam->status, ['Connected', 'ONLINE'])) $statusClass = 'badge-connected';
                                        elseif($cam->status === 'Disabled') $statusClass = 'badge-disabled';
                                        elseif(in_array($cam->status, ['Error', 'OFFLINE', 'DEGRADED'])) $statusClass = 'badge-error';
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $cam->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <label class="form-switch-saas">
                                        <input type="checkbox" class="camera-status-toggle" data-id="{{ $cam->id }}" {{ $cam->status !== 'Disabled' ? 'checked' : '' }}>
                                        <span class="slider-saas"></span>
                                    </label>
                                </td>
                                <td>
                                    <span class="text-muted font-12">{{ $cam->last_seen ? \Carbon\Carbon::parse($cam->last_seen)->format('M d, h:i A') : 'Never' }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="action-icon-btn" data-toggle="modal" data-target="#editCameraModal{{ $cam->id }}" title="Edit"><i class="ti-pencil"></i></button>
                                        <form action="{{ route('cameras.destroy', $cam->id) }}" method="POST" onsubmit="return confirm('Remove this camera registration?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn text-danger" title="Delete"><i class="ti-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No face recognition cameras configured. Click "Register Camera" to begin.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($cameras->hasPages())
                <div class="card-footer bg-white border-top p-4">
                    {{ $cameras->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- ==============================================
     ADD CAMERA MODAL
=============================================== -->
<div class="modal fade" id="addCameraModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-video-camera text-primary mr-2"></i> Register Camera Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cameras.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Camera Name</label>
                        <input type="text" name="camera_name" class="form-control form-control-saas" required placeholder="e.g. Main Entrance Dome 1">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Camera ID (Unique String)</label>
                        <input type="text" name="camera_id" class="form-control form-control-saas" required placeholder="e.g. CAM-ENT-001">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">IP Address / ONVIF URL</label>
                        <input type="text" name="ip" class="form-control form-control-saas" required placeholder="e.g. 192.168.1.180:554/live">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Associated Biometric Terminal</label>
                        <select name="device_id" class="form-control form-control-saas">
                            <option value="">No Association</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->device_id }}">{{ $dev->name }} (ID: {{ $dev->device_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Camera Type</label>
                        <select name="camera_type" class="form-control form-control-saas" required>
                            <option value="IP Camera">IP Camera (RTSP)</option>
                            <option value="ONVIF Camera">ONVIF Compliant Device</option>
                            <option value="USB Webcam">USB / Direct Webcam</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Assigned Location</label>
                        <input type="text" name="location" class="form-control form-control-saas" required placeholder="e.g. Ground Lobby North Corner">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Assigned Department (Access Boundary)</label>
                        <input type="text" name="assigned_department" class="form-control form-control-saas" placeholder="e.g. Engineering (Leave blank for all)">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4">Register Camera</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     EDIT CAMERA MODALS
=============================================== -->
@foreach($cameras as $cam)
<div class="modal fade" id="editCameraModal{{ $cam->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-pencil text-primary mr-2"></i> Modify Camera Settings: {{ $cam->camera_name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cameras.update', $cam->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Camera Name</label>
                        <input type="text" name="camera_name" class="form-control form-control-saas" value="{{ $cam->camera_name }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Camera ID (Unique String)</label>
                        <input type="text" name="camera_id" class="form-control form-control-saas" value="{{ $cam->camera_id }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">IP Address / ONVIF URL</label>
                        <input type="text" name="ip" class="form-control form-control-saas" value="{{ $cam->ip }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Associated Biometric Terminal</label>
                        <select name="device_id" class="form-control form-control-saas">
                            <option value="">No Association</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->device_id }}" {{ $cam->device_id == $dev->device_id ? 'selected' : '' }}>{{ $dev->name }} (ID: {{ $dev->device_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Camera Type</label>
                        <select name="camera_type" class="form-control form-control-saas" required>
                            <option value="IP Camera" {{ $cam->camera_type === 'IP Camera' ? 'selected' : '' }}>IP Camera (RTSP)</option>
                            <option value="ONVIF Camera" {{ $cam->camera_type === 'ONVIF Camera' ? 'selected' : '' }}>ONVIF Compliant Device</option>
                            <option value="USB Webcam" {{ $cam->camera_type === 'USB Webcam' ? 'selected' : '' }}>USB / Direct Webcam</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Assigned Location</label>
                        <input type="text" name="location" class="form-control form-control-saas" value="{{ $cam->location }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Assigned Department</label>
                        <input type="text" name="assigned_department" class="form-control form-control-saas" value="{{ $cam->assigned_department }}" placeholder="e.g. Engineering (Leave blank for all)">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Connection State</label>
                        <select name="status" class="form-control form-control-saas" required>
                            <option value="Connected" {{ $cam->status === 'Connected' ? 'selected' : '' }}>Connected</option>
                            <option value="Disconnected" {{ $cam->status === 'Disconnected' ? 'selected' : '' }}>Disconnected</option>
                            <option value="Disabled" {{ $cam->status === 'Disabled' ? 'selected' : '' }}>Disabled</option>
                            <option value="Error" {{ $cam->status === 'Error' ? 'selected' : '' }}>Error</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // AJAX status toggle switch click handler
    document.querySelectorAll('.camera-status-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            let camId = this.getAttribute('data-id');
            let isChecked = this.checked;
            let self = this;
            
            Swal.fire({
                title: 'Updating camera configuration status...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });
            
            fetch(`/cameras/${camId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Updated!', data.message, 'success');
                } else {
                    self.checked = !isChecked; // revert checkbox
                    Swal.fire('Failed', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.close();
                self.checked = !isChecked; // revert checkbox
                Swal.fire('Connection Error', error.message, 'error');
            });
        });
    });
</script>
@endsection

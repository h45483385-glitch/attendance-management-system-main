@extends('layouts.master')

@section('css')
<style>
    .staff-card { border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; }
    .table-modern td, .table-modern th { vertical-align: middle; border-top: 1px solid #f1f5f9; padding: 15px; }
    .table-modern thead th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
    .avatar-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; color: white; background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
    
    .biometric-actions-group { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
    .btn-action-custom { width: 160px; text-align: center; border: none; font-weight: 600; border-radius: 8px; padding: 7px 10px; font-size: 12px; transition: all 0.2s; color: white !important; }
    
    .btn-scan-face { background: linear-gradient(135deg, #10b981, #059669); }
    .btn-fingerprint { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .btn-edit-emp { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .btn-delete-emp { background: linear-gradient(135deg, #ef4444, #dc2626); }
    
    .camera-frame { width: 100%; height: 350px; background: #000; border-radius: 12px; overflow: hidden; position: relative; border: 4px solid #e2e8f0; }
    #videoElement { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
    .scan-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 2px dashed rgba(16, 185, 129, 0.5); border-radius: 12px; pointer-events: none; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Staff & Biometrics Management</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Staff & Biometrics Hub</li>
    </ol>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- MAIN EMPLOYEE & BIOMETRIC DIRECTORY -->
        <div class="card staff-card mb-4">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mt-0 font-weight-bold text-dark"><i class="ti-id-badge text-primary mr-2"></i> Employee Directory & Biometric Hub</h4>
                        <p class="text-muted font-13 mb-0">Manage staff details, department, shifts, and configure biometrics.</p>
                    </div>
                    <div>
                        <button class="btn btn-primary font-weight-bold px-4 rounded-pill shadow-sm" data-toggle="modal" data-target="#addEmployeeModal">
                            <i class="ti-plus mr-2"></i> Add New Employee
                        </button>
                    </div>
                </div>

                <!-- Modern Table -->
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee Details</th>
                                <th>Department & Position</th>
                                <th>Assigned Shift</th>
                                <th>Biometrics</th>
                                <th class="text-right" style="min-width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-box mr-3">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $emp->name }}</h6>
                                            <span class="text-muted font-12"><i class="ti-email mr-1"></i> {{ $emp->email }}</span><br>
                                            <span class="text-primary font-weight-bold font-11">ID: #{{ $emp->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light px-2 py-1 font-weight-bold text-dark border">{{ $emp->department ?? 'General' }}</span><br>
                                    <span class="text-muted font-12 font-weight-bold mt-1 d-inline-block">{{ $emp->position ?? 'Staff' }}</span>
                                </td>
                                <td>
                                    @php $currentShift = $emp->schedules->first(); @endphp
                                    <span class="badge badge-info px-2 py-1 font-weight-bold">
                                        {{ $currentShift ? ucfirst($currentShift->slug) : 'Not Assigned' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success px-2 py-1 font-11"><i class="ti-check"></i> Face ID</span>
                                    <span class="badge badge-success px-2 py-1 font-11"><i class="ti-check"></i> Fingerprint</span>
                                </td>
                                <td class="text-right">
                                    <div class="biometric-actions-group">
                                        <!-- Edit Button Trigger -->
                                        <button class="btn btn-action-custom btn-edit-emp" data-toggle="modal" data-target="#editEmployeeModal{{ $emp->id }}">
                                            <i class="ti-pencil mr-1"></i> Edit Details
                                        </button>
                                        <button onclick="openCameraModal('{{ $emp->id }}', '{{ $emp->name }}')" class="btn btn-action-custom btn-scan-face">
                                            <i class="ti-reload mr-1"></i> Re-Scan Face
                                        </button>
                                        <!-- Delete Form -->
                                        <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Delete this employee and all logs?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action-custom btn-delete-emp">
                                                <i class="ti-trash mr-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No employees found in the database.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     1. ADD NEW EMPLOYEE MODAL
=============================================== -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Add New Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter employee name">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="Enter valid email">
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted">Department</label>
                            <input type="text" name="department" class="form-control" placeholder="e.g. Engineering">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted">Job Position</label>
                            <input type="text" name="position" class="form-control" placeholder="e.g. Developer">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Work Schedule / Shift <span class="text-danger">*</span></label>
                        <select name="schedule" class="form-control" required>
                            <option value="">-- Select Shift --</option>
                            @isset($schedules)
                                @foreach($schedules as $sched)
                                    <option value="{{ $sched->slug }}">{{ ucfirst($sched->slug) }} ({{ $sched->time_in }} - {{ $sched->time_out }})</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">PIN Code (Password) <span class="text-danger">*</span></label>
                        <input type="password" name="pin_code" class="form-control" required placeholder="Create PIN">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     2. EDIT EMPLOYEE MODALS LOOP
=============================================== -->
@foreach($employees as $emp)
<div class="modal fade" id="editEmployeeModal{{ $emp->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-pencil text-primary mr-2"></i> Edit Employee: {{ $emp->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employees.update', $emp->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $emp->name }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ $emp->email }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ $emp->department }}">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-muted">Job Position</label>
                            <input type="text" name="position" class="form-control" value="{{ $emp->position }}">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Work Schedule / Shift <span class="text-danger">*</span></label>
                        <select name="schedule" class="form-control" required>
                            @php $assignedSched = $emp->schedules->first(); @endphp
                            @isset($schedules)
                                @foreach($schedules as $sched)
                                    <option value="{{ $sched->slug }}" {{ $assignedSched && $assignedSched->id == $sched->id ? 'selected' : '' }}>
                                        {{ ucfirst($sched->slug) }} ({{ $sched->time_in }} - {{ $sched->time_out }})
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">New PIN Code (Leave blank to keep current)</label>
                        <input type="password" name="pin_code" class="form-control" placeholder="Enter new PIN if changing">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- ==============================================
     3. LIVE CAMERA SCAN MODAL
=============================================== -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-camera text-primary mr-2"></i> Face Recognition Setup</h5>
                <button type="button" class="close text-danger" onclick="closeCamera()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted mb-4">Please ask <strong id="scanEmpName" class="text-primary">Employee</strong> to look at the camera.</p>
                <div class="camera-frame mb-3 shadow-sm">
                    <video id="videoElement" autoplay playsinline></video>
                    <div class="scan-overlay"></div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light font-weight-bold px-4" onclick="closeCamera()">Cancel</button>
                <button type="button" class="btn btn-success font-weight-bold px-5 shadow" onclick="captureFace()"><i class="ti-target mr-2"></i> Capture & Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let video = document.querySelector("#videoElement");
    let mediaStream = null;
    let activeEmpId = null;

    function openCameraModal(empId, empName) {
        activeEmpId = empId;
        document.getElementById('scanEmpName').innerText = empName;
        $('#cameraModal').modal('show');

        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    mediaStream = stream;
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    $('#cameraModal').modal('hide');
                    Swal.fire('Camera Error', 'Please check webcam permissions.', 'error');
                });
        }
    }

    function closeCamera() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        $('#cameraModal').modal('hide');
    }

    function captureFace() {
        let canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        let ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        let imageDataBase64 = canvas.toDataURL('image/jpeg');

        Swal.fire({
            title: 'Processing Biometrics...',
            html: 'Please wait while facial data is securely indexed.',
            didOpen: () => { Swal.showLoading() }
        });

        let captureUrl = "/employees/" + activeEmpId + "/capture-face";

        fetch(captureUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ image: imageDataBase64 })
        })
        .then(response => response.json())
        .then(data => {
            closeCamera();
            if (data.status) {
                Swal.fire('Success!', data.message, 'success').then(() => { window.location.reload(); });
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            closeCamera();
            Swal.fire('Server Error', error.message, 'error');
        });
    }
</script>
@endsection
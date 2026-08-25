@extends('layouts.master')

@section('css')
<style>
    /* Modern Card & Table Design */
    .staff-card { border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; }
    .table-modern td, .table-modern th { vertical-align: middle; border-top: 1px solid #f1f5f9; padding: 15px; }
    .table-modern thead th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }

    .avatar-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; color: white; background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }

    /* Uniform Action Buttons Customization */
    .biometric-actions-group { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
    .btn-action-custom { width: 160px; text-align: center; border: none; font-weight: 600; border-radius: 8px; padding: 7px 10px; font-size: 12px; transition: all 0.2s; color: white !important; }
    
    .btn-scan-face { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
    .btn-scan-face:hover { transform: translateY(-2px); }

    .btn-fingerprint { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); }
    .btn-fingerprint:hover { transform: translateY(-2px); }

    .btn-delete-emp { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
    .btn-delete-emp:hover { transform: translateY(-2px); }

    /* Camera Modal Custom CSS */
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

        <!-- TOP HARDWARE STATUS BAR -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card staff-card p-3 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-1 font-weight-bold">Biometric Machine Status</h6>
                        <h4 class="mb-0 text-success font-weight-bold"><i class="ti-pulse mr-2"></i> Connected (IP: 192.168.1.201)</h4>
                    </div>
                    <span class="badge badge-success px-3 py-2" style="font-size: 12px; border-radius: 20px;">Online</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card staff-card p-3 d-flex flex-row align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted font-12 text-uppercase mb-1 font-weight-bold">Total Registered Staff</h6>
                        <h4 class="mb-0 text-primary font-weight-bold"><i class="ti-id-badge mr-2"></i> Active Profiles</h4>
                    </div>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold"><i class="ti-reload mr-1"></i> Sync Device</button>
                </div>
            </div>
        </div>

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
                        <p class="text-muted font-13 mb-0">Manage staff details, configure face recognition, and sync fingerprints.</p>
                    </div>
                    <div>
                        <!-- Add New Employee Button -->
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
                                <th>Employee</th>
                                <th>Contact Details</th>
                                <th>Face ID Status</th>
                                <th>Fingerprint Status</th>
                                <th class="text-right" style="min-width: 200px;">Biometric Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DYNAMIC EMPLOYEE ROWS FROM DATABASE -->
                            @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-box mr-3" style="background: linear-gradient(135deg, #0ea5e9, #2563eb);">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $emp->name }}</h6>
                                            <span class="text-muted font-12 font-weight-bold">ID: #{{ $emp->id }} | {{ $emp->position ?? 'Staff' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="text-dark font-weight-bold"><i class="ti-email text-muted mr-1"></i> {{ $emp->email }}</div></td>
                                <td>
                                    <span class="badge px-3 py-1 font-12" style="background: #d1fae5; color: #059669; border-radius: 20px;">
                                        <i class="ti-check mr-1"></i> Active ID
                                    </span>
                                </td>
                                <td>
                                    <span class="badge px-3 py-1 font-12" style="background: #d1fae5; color: #059669; border-radius: 20px;">
                                        <i class="ti-check mr-1"></i> Synced
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="biometric-actions-group">
                                        <button onclick="openCameraModal('{{ $emp->id }}', '{{ $emp->name }}')" class="btn btn-action-custom btn-scan-face">
                                            <i class="ti-reload mr-1"></i> Scan / Re-Scan
                                        </button>
                                        <button onclick="registerFingerprint('{{ $emp->id }}', '{{ $emp->name }}')" class="btn btn-action-custom btn-fingerprint">
                                            <i class="ti-reload mr-1"></i> Fingerprint
                                        </button>
                                        <!-- Cascade Delete Form Trigger -->
                                        <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee? This will remove their login, attendance, and all records.');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action-custom btn-delete-emp">
                                                <i class="ti-trash mr-1"></i> Delete Employee
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No employees found in the database. Please add a new employee.</td>
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
     1. ADD NEW EMPLOYEE MODAL (With Shift Dropdown)
=============================================== -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header bg-light" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Add New Employee & Assign Shift</h5>
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
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Job Position / Role</label>
                        <input type="text" name="position" class="form-control" placeholder="e.g. Junior Developer, Receptionist">
                    </div>

                    <!-- Shift & Schedule Dropdown -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">Work Schedule / Shift <span class="text-danger">*</span></label>
                        <select name="schedule" class="form-control" required>
                            <option value="">-- Select Shift --</option>
                            @isset($schedules)
                                @foreach($schedules as $sched)
                                    <option value="{{ $sched->slug }}">{{ $sched->name ?? $sched->slug }} ({{ $sched->time_in ?? '09:00' }} - {{ $sched->time_out ?? '18:00' }})</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted">PIN Code (Password) <span class="text-danger">*</span></label>
                        <input type="password" name="pin_code" class="form-control" required placeholder="Create a secure PIN or password">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">Save & Link Pipeline</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     2. LIVE CAMERA SCAN MODAL WITH S3 UPLOAD
=============================================== -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 bg-light" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-camera text-primary mr-2"></i> Face Enrollment Setup</h5>
                <button type="button" class="close text-danger" onclick="closeCamera()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted mb-4">Please ask <strong id="scanEmpName" class="text-primary">Employee</strong> to look directly at the camera.</p>
                <div class="camera-frame mb-3 shadow-sm">
                    <video id="videoElement" autoplay playsinline></video>
                    <div class="scan-overlay"></div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light font-weight-bold px-4" onclick="closeCamera()">Cancel</button>
                <button type="button" class="btn btn-success font-weight-bold px-5 shadow" onclick="captureFace()"><i class="ti-target mr-2"></i> Capture & Save to AWS S3</button>
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
                    Swal.fire('Camera Error', 'Please allow camera permissions or check if a webcam is connected.', 'error');
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
            title: 'Uploading to AWS S3...',
            html: 'Please wait while facial biometrics are securely stored and indexed in the cloud.',
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
            body: JSON.stringify({
                image: imageDataBase64
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server returned status ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            closeCamera();
            if (data.status) {
                Swal.fire('Success!', data.message, 'success').then(() => {
                    window.location.reload(); 
                });
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            closeCamera();
            Swal.fire('Server Error', 'Could not connect to the server. Error: ' + error.message, 'error');
        });
    }

    function registerFingerprint(empId, empName) {
        Swal.fire({
            title: 'Fingerprint Scanner Ready',
            text: 'Please place finger on the biometric hardware device for ' + empName + ' (ID: #' + empId + ')',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Simulate Scan',
            confirmButtonColor: '#f59e0b'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Registered!', 'Fingerprint template successfully synced from biometric device.', 'success');
            }
        });
    }
</script>
@endsection
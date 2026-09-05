@extends('layouts.master')

@section('css')
<style>
    /* Design System & Premium Aesthetics */
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.005); 
        border: 1px solid #f1f5f9; 
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    /* Stats Bar Customization */
    .stat-badge-glow {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    /* Tables & Modern Layouts */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .table-modern td, .table-modern th { 
        vertical-align: middle; 
        border-top: 1px solid #f1f5f9; 
        padding: 16px 20px; 
        font-size: 14px;
        color: #334155;
    }
    .table-modern thead th { 
        background-color: #f8fafc; 
        border-bottom: 2px solid #e2e8f0; 
        color: #475569; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 11px; 
        letter-spacing: 0.8px; 
    }
    .table-modern tbody tr {
        transition: background-color 0.15s;
    }
    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .avatar-circle { 
        width: 42px; 
        height: 42px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 600; 
        font-size: 16px; 
        color: white; 
        background: linear-gradient(135deg, #0ea5e9, #2563eb); 
        box-shadow: 0 4px 10px rgba(14, 165, 233, 0.15); 
    }
    
    /* Custom Responsive Badges */
    .status-badge {
        font-weight: 600;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-active { background-color: #dcfce7; color: #166534; }
    .badge-inactive { background-color: #fee2e2; color: #991b1b; }
    .badge-bio-success { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-bio-pending { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    
    /* Clean Forms & Inputs */
    .form-control-saas {
        display: block;
        width: 100%;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
        color: #334155;
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control-saas:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    
    /* Action Controls */
    .btn-saas {
        font-weight: 600;
        border-radius: 8px;
        padding: 9px 18px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
        border: none;
    }
    .btn-saas-primary { background: linear-gradient(135deg, #0ea5e9, #2563eb); color: white !important; }
    .btn-saas-primary:hover { opacity: 0.95; transform: translateY(-1px); }
    .btn-saas-secondary { background-color: #f1f5f9; color: #334155 !important; }
    .btn-saas-secondary:hover { background-color: #e2e8f0; }
    .btn-saas-danger { background-color: #fee2e2; color: #991b1b !important; }
    .btn-saas-danger:hover { background-color: #fecaca; }
    
    .action-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.15s;
    }
    .action-icon-btn:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    
    /* Detail View Styles */
    .detail-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 1px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }
    
    /* Mobile Cards (Transforms Table on Mobile viewports) */
    .employee-mobile-card {
        display: none;
    }
    
    @media (max-width: 768px) {
        .desktop-table-container {
            display: none;
        }
        .employee-mobile-card {
            display: block;
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 15px;
        }
        .employee-mobile-card:last-child {
            border-bottom: none;
        }
    }

    /* ── Face Recognition Popup ─────────────────────────────────────── */
    /* Keep the modal narrow so it sits comfortably on any screen */
    #cameraModal .modal-dialog {
        max-width: 420px;
    }

    /* Camera frame: fixed, compact bounding box centred in the popup */
    .camera-frame {
        position: relative;
        width: 100%;
        max-width: 340px;
        height: 240px;          /* compact — shows face clearly, no scroll */
        border-radius: 12px;
        overflow: hidden;
        background: #0f172a;
        border: 2px solid #e2e8f0;
    }

    /* Video fills the frame, cropped to maintain aspect ratio */
    #videoElement {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Subtle corner-bracket scan overlay */
    .scan-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        border-radius: 12px;
        box-shadow: inset 0 0 0 3px rgba(17, 111, 183, 0.25);
    }
    .scan-overlay::before,
    .scan-overlay::after {
        content: '';
        position: absolute;
        width: 28px;
        height: 28px;
        border-color: #116fb7;
        border-style: solid;
    }
    .scan-overlay::before {
        top: 10px; left: 10px;
        border-width: 3px 0 0 3px;
        border-radius: 4px 0 0 0;
    }
    .scan-overlay::after {
        bottom: 10px; right: 10px;
        border-width: 0 3px 3px 0;
        border-radius: 0 0 4px 0;
    }
    /* ─────────────────────────────────────────────────────────────── */
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Employee Directory</h4>
    <p class="text-secondary font-13 mb-0">Manage employee records, roles, work shifts, and biometric registration states.</p>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        <!-- Global Alert Messaging -->
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
                <strong class="font-weight-bold"><i class="ti-alert mr-1"></i> Form Validation Failed:</strong>
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

        <!-- 1. COMPACT DIRECTORY STATISTICS -->
        <div class="row mb-4" id="stats-bar">
            <div class="col-md-2-5 col-sm-6 col-xs-12 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #e0f2fe; color: #0284c7; box-shadow: none;">
                            <i class="ti-user"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['total'] }}</h5>
                            <span class="text-muted font-12">Total Staff</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 col-xs-12 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #dcfce7; color: #166534; box-shadow: none;">
                            <i class="ti-check"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['active'] }}</h5>
                            <span class="text-muted font-12">Active Staff</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 col-xs-12 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #fee2e2; color: #991b1b; box-shadow: none;">
                            <i class="ti-na"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['inactive'] }}</h5>
                            <span class="text-muted font-12">Deactivated</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 col-xs-12 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #e0f2fe; color: #0ea5e9; box-shadow: none;">
                            <i class="ti-face-smile"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['face'] }}</h5>
                            <span class="text-muted font-12">Face Enrolled</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 col-xs-12 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #fef3c7; color: #d97706; box-shadow: none;">
                            <i class="ti-hand-point-up"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['fingerprint'] }}</h5>
                            <span class="text-muted font-12">Finger Enrolled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. SEARCH & ADVANCED FILTERS BLOCK -->
        <div class="card saas-card mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('employees.index') }}">
                    <div class="row align-items-end">
                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Search Employee</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="ti-search text-muted"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control form-control-saas border-left-0" style="padding-left: 0;" placeholder="Search name, email, or ID..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Department</label>
                            <select name="department" class="form-control form-control-saas">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Job Position</label>
                            <select name="position" class="form-control form-control-saas">
                                <option value="">All Positions</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos }}" {{ request('position') === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Status</label>
                            <select name="status" class="form-control form-control-saas">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $stat)
                                    <option value="{{ $stat }}" {{ request('status') === $stat ? 'selected' : '' }}>{{ $stat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-3 mb-lg-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Biometrics</label>
                            <select name="biometric" class="form-control form-control-saas">
                                <option value="">All Types</option>
                                <option value="face" {{ request('biometric') === 'face' ? 'selected' : '' }}>Face ID Only</option>
                                <option value="fingerprint" {{ request('biometric') === 'fingerprint' ? 'selected' : '' }}>Fingerprint Only</option>
                                <option value="both" {{ request('biometric') === 'both' ? 'selected' : '' }}>Face & Fingerprint</option>
                                <option value="none" {{ request('biometric') === 'none' ? 'selected' : '' }}>Not Registered</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-12 text-lg-right text-left">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-saas btn-saas-secondary w-100" style="padding: 10px;"><i class="ti-filter"></i></button>
                                @if(request()->anyFilled(['search', 'department', 'position', 'status', 'biometric']))
                                    <a href="{{ route('employees.index') }}" class="btn btn-saas btn-saas-danger ml-2" style="padding: 10px;" title="Clear Filters"><i class="ti-close"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. EMPLOYEE MAIN DATA CARD -->
        <div class="card saas-card mb-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="m-0 font-weight-bold text-dark"><i class="ti-menu-alt text-primary mr-2"></i> Employee Directory</h5>
                </div>
                <div>
                    <button class="btn btn-saas btn-saas-primary px-4 shadow-sm" data-toggle="modal" data-target="#addEmployeeModal">
                        <i class="ti-plus mr-1"></i> Add Employee
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                
                <!-- Desktop Responsive Table View -->
                <div class="table-responsive desktop-table-container">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee Details</th>
                                <th>Department & Job</th>
                                <th>Assigned Shift</th>
                                <th class="text-center">Biometrics</th>
                                <th class="text-center">Status</th>
                                <th class="text-right" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3" style="overflow: hidden;">
                                            @if($emp->face_photo_path)
                                                <img src="{{ $emp->face_photo_path }}" alt="{{ $emp->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            @else
                                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $emp->name }}</h6>
                                            <span class="text-muted font-12 d-block"><i class="ti-email mr-1"></i> {{ $emp->email }}</span>
                                            <span class="text-secondary font-weight-bold font-11">ID: #{{ $emp->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light px-2 py-1 font-weight-bold border">{{ $emp->department ?? 'General' }}</span>
                                    <span class="text-muted font-12 d-block mt-1">{{ $emp->position ?? 'Staff' }}</span>
                                </td>
                                <td>
                                    @php $currentShift = $emp->schedules->first(); @endphp
                                    @if($currentShift)
                                        <span class="font-weight-bold text-dark font-13">{{ ucfirst($currentShift->slug) }}</span>
                                        <span class="text-muted font-12 d-block mt-1"><i class="ti-time mr-1"></i> {{ \Carbon\Carbon::parse($currentShift->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($currentShift->time_out)->format('h:i A') }}</span>
                                    @else
                                        <span class="text-secondary font-12">Not Assigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        <span class="status-badge {{ $emp->face_enrolled ? 'badge-bio-success' : 'badge-bio-pending' }}">
                                            <i class="{{ $emp->face_enrolled ? 'ti-check' : 'ti-help' }}"></i> Face ID
                                        </span>
                                        <span class="status-badge {{ $emp->fingerprint_enrolled ? 'badge-bio-success' : 'badge-bio-pending' }} mt-1">
                                            <i class="{{ $emp->fingerprint_enrolled ? 'ti-check' : 'ti-help' }}"></i> Fingerprint
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge {{ strtolower($emp->status) === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        <span class="stat-badge-glow" style="background-color: {{ strtolower($emp->status) === 'active' ? '#166534' : '#991b1b' }}; margin: 0;"></span>
                                        {{ $emp->status ?? 'Active' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="action-icon-btn" data-toggle="modal" data-target="#detailsModal{{ $emp->id }}" title="View Details"><i class="ti-eye"></i></button>
                                        <button class="action-icon-btn" data-toggle="modal" data-target="#editEmployeeModal{{ $emp->id }}" title="Edit Details"><i class="ti-pencil"></i></button>
                                        <button onclick="openCameraModal('{{ $emp->id }}', '{{ $emp->name }}')" class="action-icon-btn" title="Scan Face"><i class="ti-camera"></i></button>
                                        <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return deleteEmployee(event, this);" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn text-danger" title="Delete"><i class="ti-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ti-user mb-2" style="font-size: 32px; display: block; opacity: 0.5;"></i>
                                    No employee records match the filters. Click "Add Employee" to register a new staff member.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile stacked layout -->
                <div class="d-md-none">
                    @forelse($employees as $emp)
                    <div class="employee-mobile-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle mr-3" style="width: 36px; height: 36px; font-size: 14px; overflow: hidden;">
                                    @if($emp->face_photo_path)
                                        <img src="{{ $emp->face_photo_path }}" alt="{{ $emp->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    @else
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $emp->name }}</h6>
                                    <span class="text-muted font-11">#{{ $emp->id }} | {{ $emp->position ?? 'Staff' }}</span>
                                </div>
                            </div>
                            <span class="status-badge {{ strtolower($emp->status) === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                {{ $emp->status ?? 'Active' }}
                            </span>
                        </div>
                        <div class="row bg-light rounded p-2 mb-3 mx-0">
                            <div class="col-6 font-12 text-muted">Dept: <strong class="text-dark">{{ $emp->department ?? 'General' }}</strong></div>
                            <div class="col-6 font-12 text-muted text-right">Face: <strong class="{{ $emp->face_enrolled ? 'text-success' : 'text-warning' }}">{{ $emp->face_enrolled ? 'Yes' : 'No' }}</strong></div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-saas btn-saas-secondary px-3 py-1 font-11" data-toggle="modal" data-target="#detailsModal{{ $emp->id }}"><i class="ti-eye mr-1"></i> View</button>
                            <button class="btn btn-sm btn-saas btn-saas-secondary px-3 py-1 font-11" data-toggle="modal" data-target="#editEmployeeModal{{ $emp->id }}"><i class="ti-pencil mr-1"></i> Edit</button>
                            <button onclick="openCameraModal('{{ $emp->id }}', '{{ $emp->name }}')" class="btn btn-sm btn-saas btn-saas-secondary px-3 py-1 font-11"><i class="ti-camera mr-1"></i> Scan</button>
            <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return deleteEmployee(event, this);" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-saas btn-saas-danger px-3 py-1 font-11"><i class="ti-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted px-3">
                        <i class="ti-user mb-2" style="font-size: 32px; display: block; opacity: 0.5;"></i>
                        No employee records found matching the filters.
                    </div>
                    @endforelse
                </div>

                <!-- Desktop/Mobile Unified Pagination -->
                @if($employees->hasPages())
                <div class="card-footer bg-white border-top p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="text-muted mb-2 mb-sm-0 font-13">
                            Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} employees
                        </div>
                        <div class="mb-2 mb-sm-0">
                            {{ $employees->links() }}
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     1. ADD NEW EMPLOYEE MODAL (SaaS Redesign)
=============================================== -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Register New Staff Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST" onsubmit="return onSubmitForm(event, this)">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Form Sections -->
                    <div class="row">
                        <div class="col-md-6 pr-md-4 border-right">
                            <h6 class="detail-section-title">Personal Details</h6>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-saas" required placeholder="e.g. John Doe">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-saas" required placeholder="e.g. johndoe@company.com">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">PIN Code (Password) <span class="text-danger">*</span></label>
                                <input type="password" name="pin_code" class="form-control form-control-saas" required placeholder="4+ character login PIN">
                            </div>
                        </div>
                        
                        <div class="col-md-6 pl-md-4">
                            <h6 class="detail-section-title">Employment & Shift Mapping</h6>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Department</label>
                                <input type="text" name="department" class="form-control form-control-saas" placeholder="e.g. Engineering">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Job Position / Designation</label>
                                <input type="text" name="position" class="form-control form-control-saas" placeholder="e.g. Senior Developer">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Employment Type</label>
                                <select name="employment_type" class="form-control form-control-saas">
                                    <option value="Permanent">Permanent</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Intern">Intern</option>
                                    <option value="Apprentice">Apprentice</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Work Shift Schedule <span class="text-danger">*</span></label>
                                <select name="schedule" class="form-control form-control-saas" required style="cursor: pointer;">
                                    <option value="" selected disabled>-- Choose Work Shift --</option>
                                    @if(isset($schedules) && count($schedules) > 0)
                                        @foreach($schedules as $sched)
                                            <option value="{{ $sched->slug }}">{{ ucwords(str_replace('-', ' ', $sched->slug)) }} ({{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }})</option>
                                        @endforeach
                                    @else
                                        <option value="morning-shift">Morning Shift (09:00 AM - 06:00 PM)</option>
                                        <option value="mid-shift">Mid Shift (01:00 PM - 10:00 PM)</option>
                                        <option value="night-shift">Night Shift (10:00 PM - 07:00 AM)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary submit-btn px-4">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     2. EDIT EMPLOYEE MODALS LOOP (SaaS Redesign)
=============================================== -->
@foreach($employees as $emp)
<div class="modal fade" id="editEmployeeModal{{ $emp->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.12);">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-pencil text-primary mr-2"></i> Modify Staff Record: {{ $emp->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employees.update', $emp->id) }}" method="POST" onsubmit="return onSubmitForm(event, this)">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    
                    <div class="row">
                        <div class="col-md-6 pr-md-4 border-right">
                            <h6 class="detail-section-title">Personal Information</h6>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-saas" value="{{ $emp->name }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-saas" value="{{ $emp->email }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">New PIN Code (Leave blank to keep current)</label>
                                <input type="password" name="pin_code" class="form-control form-control-saas" placeholder="Enter new PIN code if changing">
                            </div>
                        </div>
                        
                        <div class="col-md-6 pl-md-4">
                            <h6 class="detail-section-title">Employment Mapping</h6>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Department</label>
                                <input type="text" name="department" class="form-control form-control-saas" value="{{ $emp->department }}">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted font-12 mb-1">Job Position / Designation</label>
                                <input type="text" name="position" class="form-control form-control-saas" value="{{ $emp->position }}">
                            </div>
                            <div class="row">
                                <div class="col-sm-6 form-group mb-3">
                                    <label class="font-weight-bold text-muted font-12 mb-1">Work Shift Schedule <span class="text-danger">*</span></label>
                                    <select name="schedule" class="form-control form-control-saas" required style="cursor: pointer;">
                                        <option value="" {{ !$emp->schedules->first() ? 'selected' : '' }} disabled>-- Select Work Shift --</option>
                                        @php $assignedSched = $emp->schedules->first(); @endphp
                                        @if(isset($schedules) && count($schedules) > 0)
                                            @foreach($schedules as $sched)
                                                <option value="{{ $sched->slug }}" {{ $assignedSched && $assignedSched->id == $sched->id ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace('-', ' ', $sched->slug)) }} ({{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="morning-shift" selected>Morning Shift (09:00 AM - 06:00 PM)</option>
                                            <option value="mid-shift">Mid Shift (01:00 PM - 10:00 PM)</option>
                                            <option value="night-shift">Night Shift (10:00 PM - 07:00 AM)</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-6 form-group mb-3">
                                    <label class="font-weight-bold text-muted font-12 mb-1">Employment Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control form-control-saas" required>
                                        <option value="Active" {{ $emp->status === 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ $emp->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary submit-btn px-4">Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- ==============================================
     3. DETAILS DIALOG MODAL (SaaS Detail Cards)
=============================================== -->
@foreach($employees as $emp)
<div class="modal fade" id="detailsModal{{ $emp->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="card mb-0 border-0">
                <div class="card-header bg-light p-4 text-center border-0 position-relative">
                    <button type="button" class="close position-absolute" style="top: 15px; right: 15px;" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="avatar-circle mx-auto mb-3" style="width: 70px; height: 70px; font-size: 24px; overflow: hidden;">
                        @if($emp->face_photo_path)
                            <img src="{{ $emp->face_photo_path }}" alt="{{ $emp->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                        @endif
                    </div>
                    <h5 class="font-weight-bold text-dark m-0">{{ $emp->name }}</h5>
                    <p class="text-muted font-12 m-0">{{ $emp->position ?? 'Staff' }} | {{ $emp->department ?? 'General' }}</p>
                    <span class="status-badge {{ strtolower($emp->status) === 'active' ? 'badge-active' : 'badge-inactive' }} mt-2">
                        {{ $emp->status ?? 'Active' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <ul class="nav nav-pills justify-content-center mb-3" id="pills-tab-{{ $emp->id }}" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold font-12" id="pills-info-tab-{{ $emp->id }}" data-toggle="pill" href="#pills-info-{{ $emp->id }}" role="tab">Employment</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold font-12" id="pills-bio-tab-{{ $emp->id }}" data-toggle="pill" href="#pills-bio-{{ $emp->id }}" role="tab">Biometrics</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent-{{ $emp->id }}">
                        <!-- Tab 1: Info -->
                        <div class="tab-pane fade show active" id="pills-info-{{ $emp->id }}" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-6 mb-3">
                                    <span class="text-muted font-11 d-block text-uppercase">Employee ID</span>
                                    <strong class="text-dark font-14">#{{ $emp->id }}</strong>
                                </div>
                                <div class="col-6 mb-3">
                                    <span class="text-muted font-11 d-block text-uppercase">Email Address</span>
                                    <strong class="text-dark font-14">{{ $emp->email }}</strong>
                                </div>
                                <div class="col-6 mb-3">
                                    <span class="text-muted font-11 d-block text-uppercase">Department</span>
                                    <strong class="text-dark font-14">{{ $emp->department ?? 'General' }}</strong>
                                </div>
                                <div class="col-6 mb-3">
                                    <span class="text-muted font-11 d-block text-uppercase">Job Position</span>
                                    <strong class="text-dark font-14">{{ $emp->position ?? 'Staff' }}</strong>
                                </div>
                                <div class="col-12 mb-3">
                                    <span class="text-muted font-11 d-block text-uppercase">Assigned Schedule</span>
                                    @php $assignedSched = $emp->schedules->first(); @endphp
                                    @if($assignedSched)
                                        <strong class="text-dark font-14">
                                            {{ ucfirst($assignedSched->slug) }} ({{ \Carbon\Carbon::parse($assignedSched->time_in)->format('h:i A') }} - {{ \Carbon\Carbon::parse($assignedSched->time_out)->format('h:i A') }})
                                        </strong>
                                    @else
                                        <strong class="text-secondary font-14">Not Assigned</strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 2: Biometrics -->
                        <div class="tab-pane fade" id="pills-bio-{{ $emp->id }}" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle mr-2" style="width: 28px; height: 28px; font-size: 11px; background: #e0f2fe; color: #0284c7; box-shadow: none;">
                                                <i class="ti-face-smile"></i>
                                            </div>
                                            <span class="font-weight-bold font-13 text-dark">Facial Recognition (Face ID)</span>
                                        </div>
                                        <span class="status-badge {{ $emp->face_enrolled ? 'badge-active' : 'badge-inactive' }}">
                                            {{ $emp->face_enrolled ? 'Enrolled' : 'Not Setup' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle mr-2" style="width: 28px; height: 28px; font-size: 11px; background: #fef3c7; color: #d97706; box-shadow: none;">
                                                <i class="ti-hand-point-up"></i>
                                            </div>
                                            <span class="font-weight-bold font-13 text-dark">Fingerprint Scanner</span>
                                        </div>
                                        <span class="status-badge {{ $emp->fingerprint_enrolled ? 'badge-active' : 'badge-inactive' }}">
                                            {{ $emp->fingerprint_enrolled ? 'Enrolled' : 'Not Setup' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12 text-center mt-2">
                                    <button onclick="closeModalAndScan('{{ $emp->id }}', '{{ $emp->name }}')" class="btn btn-saas btn-saas-primary w-100"><i class="ti-camera mr-1"></i> Launch Face Biometric Scan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- ==============================================
     4. LIVE CAMERA SCAN MODAL
=============================================== -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-camera text-primary mr-2"></i> Face Recognition Setup</h5>
                <button type="button" class="close text-danger" onclick="closeCamera()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-3 px-4 text-center">
                <p class="text-muted mb-2" style="font-size:13px;">Ask <strong id="scanEmpName" class="text-primary">Employee</strong> to look directly at the webcam.</p>
                <div class="camera-frame mb-3 shadow-sm mx-auto">
                    <video id="videoElement" autoplay playsinline></video>
                    <div class="scan-overlay"></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top justify-content-center py-3 px-4">
                <button type="button" class="btn btn-saas btn-saas-secondary px-4" onclick="closeCamera()">Cancel</button>
                <button type="button" class="btn btn-saas btn-saas-primary px-5 shadow" onclick="captureFace()"><i class="ti-target mr-2"></i> Capture & Save</button>
            </div>
        </div>
    </div>
</div>
{{-- Base URL for face-capture endpoint — avoids hardcoded strings in JS --}}
<meta name="capture-face-base-url" content="{{ url('/employees') }}">
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
                    Swal.fire('Camera Error', 'Webcam permissions are required to scan facial biometrics.', 'error');
                });
        }
    }

    function closeModalAndScan(empId, empName) {
        $('#detailsModal' + empId).modal('hide');
        setTimeout(() => {
            openCameraModal(empId, empName);
        }, 400);
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
            title: 'Processing Facial Recognition...',
            html: 'Securely uploading and indexing facial features in AWS Rekognition.',
            didOpen: () => { Swal.showLoading() }
        });

        const captureBaseUrl = document.querySelector('meta[name="capture-face-base-url"]').content;
        let captureUrl = captureBaseUrl + "/" + activeEmpId + "/capture-face";

        fetch(captureUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ image: imageDataBase64 })
        })
        .then(response => response.json())
        .then(data => {
            closeCamera();
            if (data.status) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => { window.location.reload(); });
            } else {
                Swal.fire('Scan Failed', data.message, 'error');
            }
        })
        .catch(error => {
            closeCamera();
            Swal.fire('Server Connection Failed', error.message, 'error');
        });
    }

    function onSubmitForm(event, form) {
        event.preventDefault();
        let submitBtn = form.querySelector('.submit-btn');
        let originalText = "";
        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Saving...';
        }
        
        let formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            $('.modal').modal('hide');
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            refreshEmployeeList();
        })
        .catch(error => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
            let errorMsg = error.message || 'An unexpected error occurred.';
            if (error.errors) {
                errorMsg = Object.values(error.errors).flat().join('<br>');
            }
            Swal.fire('Error', errorMsg, 'error');
        });
        
        return false;
    }

    function deleteEmployee(event, form) {
        event.preventDefault();
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the employee and all their attendance records!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire('Deleted!', data.message, 'success');
                    refreshEmployeeList();
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to delete employee.', 'error');
                });
            }
        });
        
        return false;
    }

    function refreshEmployeeList() {
        let url = new URL(window.location.href);
        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            let parser = new DOMParser();
            let doc = parser.parseFromString(html, 'text/html');
            
            // Update Stats Bar (targeted by stable id, not fragile CSS selector)
            let statsRow = document.getElementById('stats-bar');
            if (statsRow && doc.getElementById('stats-bar')) {
                statsRow.innerHTML = doc.getElementById('stats-bar').innerHTML;
            }
            
            // Update Table
            let tableContainer = document.querySelector('.desktop-table-container');
            if (tableContainer && doc.querySelector('.desktop-table-container')) {
                tableContainer.innerHTML = doc.querySelector('.desktop-table-container').innerHTML;
            }
            
            // Update Mobile Cards
            let mobileCards = document.querySelector('.d-md-none');
            if (mobileCards && doc.querySelector('.d-md-none')) {
                mobileCards.innerHTML = doc.querySelector('.d-md-none').innerHTML;
            }
            
            // Update Pagination
            let cardFooter = document.querySelector('.card-footer');
            if (cardFooter && doc.querySelector('.card-footer')) {
                cardFooter.innerHTML = doc.querySelector('.card-footer').innerHTML;
            } else if (doc.querySelector('.card-footer')) {
                document.querySelector('.card.saas-card').appendChild(doc.querySelector('.card-footer'));
            } else if (cardFooter) {
                cardFooter.remove();
            }
        });
    }
</script>
@endsection
@extends('layouts.master')

@section('css')
<style>
    /* SaaS Redesign Styles */
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
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
        background: linear-gradient(135deg, #116fb7, #22C55E); 
        box-shadow: 0 4px 10px rgba(17, 111, 183, 0.15); 
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
    .badge-online { background-color: #dcfce7; color: #166534; }
    .badge-offline { background-color: #f1f5f9; color: #475569; }
    .badge-inactive { background-color: #fef3c7; color: #d97706; }
    .badge-blocked { background-color: #fee2e2; color: #991b1b; }
    
    .status-dot-glow {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Biometric Terminals</h4>
    <p class="text-secondary font-13 mb-0">Register, authorize, and manage fingerprint machines and hardware log integrations.</p>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        
        <!-- Validation Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                <strong class="font-weight-bold"><i class="ti-check-box mr-1"></i> Success:</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- STATISTICS -->
        <div class="row mb-4">
            <div class="col-md-2-5 col-sm-6 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #e0f2fe; color: #0284c7; box-shadow: none;">
                            <i class="ti-server"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['total'] }}</h5>
                            <span class="text-muted font-11">Total Terminals</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #dcfce7; color: #166534; box-shadow: none;">
                            <i class="ti-pulse"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['connected'] }}</h5>
                            <span class="text-muted font-11">Connected (Online)</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #fee2e2; color: #991b1b; box-shadow: none;">
                            <i class="ti-plug"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['offline'] }}</h5>
                            <span class="text-muted font-11">Offline Devices</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #e0f2fe; color: #0ea5e9; box-shadow: none;">
                            <i class="ti-shield"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['enrolled'] }}</h5>
                            <span class="text-muted font-11">Enrolled Staff</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2-5 col-sm-6 mb-3">
                <div class="card saas-card h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-circle mr-3" style="background: #fef3c7; color: #d97706; box-shadow: none;">
                            <i class="ti-timer"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold text-dark">{{ $stats['pending'] }}</h5>
                            <span class="text-muted font-11">Pending Bio Setup</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN TERMINAL DIRECTORY -->
        <div class="card saas-card mb-4">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="m-0 font-weight-bold text-dark"><i class="ti-harddrives text-primary mr-2"></i> Terminal List</h5>
                </div>
                <div>
                    <a class="btn btn-saas btn-saas-primary mr-2 px-3" href="{{ route('finger_device.create') }}">
                        <i class="ti-plus mr-1"></i> Register Device
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Device Details</th>
                                <th>Device ID & Serial</th>
                                <th>Security Token</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Hardware Sync</th>
                                <th class="text-right" style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devices as $finger_device)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3" style="background: #f1f5f9; color: #475569; box-shadow: none;">
                                            <i class="ti-server font-16"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $finger_device->name }}</h6>
                                            <span class="text-muted font-11">Branch: {{ $finger_device->branch ?? 'N/A' }} | Gate: {{ $finger_device->gate ?? 'N/A' }}</span><br>
                                            <span class="text-muted font-11">IP: {{ $finger_device->ip }} | Loc: {{ $finger_device->location ?? 'General' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark font-12 d-block">ID: {{ $finger_device->device_id ?? 'N/A' }}</span>
                                    <span class="text-muted font-11">S/N: {{ $finger_device->serialNumber ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($finger_device->token)
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="font-11 text-secondary font-weight-bold mr-1" id="token-text-{{ $finger_device->id }}" style="font-family: monospace;">••••••••••••••••</span>
                                            <button class="btn btn-sm btn-link p-0 text-primary" onclick="toggleToken('{{ $finger_device->id }}', '{{ $finger_device->token }}')" title="Show Token"><i class="ti-eye"></i></button>
                                        </div>
                                    @else
                                        <span class="text-muted font-11">None</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($finger_device->status === 'Blocked')
                                        <span class="status-badge badge-blocked">
                                            <span class="status-dot-glow" style="background-color: #991b1b;"></span> Blocked
                                        </span>
                                    @elseif($finger_device->status === 'Inactive')
                                        <span class="status-badge badge-inactive">
                                            <span class="status-dot-glow" style="background-color: #d97706;"></span> Inactive
                                        </span>
                                    @elseif(isset($finger_device->is_online) && $finger_device->is_online)
                                        <span class="status-badge badge-online">
                                            <span class="status-dot-glow" style="background-color: #166534;"></span> Online
                                        </span>
                                    @else
                                        <span class="status-badge badge-offline">
                                            <span class="status-dot-glow" style="background-color: #475569;"></span> Offline
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a class="btn btn-sm btn-saas btn-saas-secondary py-1 px-2 font-11 ajax-action-btn" href="{{ route('finger_device.add.employee', $finger_device->id) }}">
                                            Push DB
                                        </a>
                                        <a class="btn btn-sm btn-saas btn-saas-secondary py-1 px-2 font-11 ajax-action-btn" href="{{ route('finger_device.get.attendance', $finger_device->id) }}">
                                            Pull Logs
                                        </a>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end gap-1">
                                        @if($finger_device->status === 'Blocked')
                                        <form action="{{ route('finger_device.activate', $finger_device->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-saas btn-saas-primary py-1 px-2 font-11 mr-1" title="Unblock"><i class="ti-unlock mr-1"></i> Activate</button>
                                        </form>
                                        @else
                                        <form action="{{ route('finger_device.block', $finger_device->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-saas btn-saas-danger py-1 px-2 font-11 mr-1" title="Block"><i class="ti-lock mr-1"></i> Block</button>
                                        </form>
                                        @endif
                                        
                                        <a class="action-icon-btn" href="{{ route('finger_device.edit', $finger_device->id) }}" title="Edit"><i class="ti-pencil"></i></a>

                                        <form action="{{ route('finger_device.destroy', $finger_device->id) }}" method="POST" onsubmit="return confirm('Remove this device registration from the database?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn text-danger" title="Remove"><i class="ti-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ti-server mb-2" style="font-size: 32px; display: block; opacity: 0.5;"></i>
                                    No biometric fingerprint hardware configured yet. Click "Register Device" to get started.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleToken(id, token) {
        let textSpan = document.getElementById('token-text-' + id);
        if (textSpan.innerText === '••••••••••••••••') {
            textSpan.innerText = token;
        } else {
            textSpan.innerText = '••••••••••••••••';
        }
    }

    // AJAX Action Buttons Handler
    $(document).on('click', '.ajax-action-btn', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        let actionText = $(this).text().trim();

        Swal.fire({
            title: actionText + '...',
            html: 'Communicating with biometric hardware. Please wait.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() }
        });

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Oops', response.message || 'Operation failed.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Connection Failed', 'Failed to communicate with the hardware biometric device. Ensure device is powered on and connected to local network.', 'error');
            }
        });
    });
</script>
@endsection
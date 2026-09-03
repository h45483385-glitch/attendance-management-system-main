@extends('layouts.master')

@section('css')
<style>
    /* SaaS Style Customizations */
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
    }
    
    .avatar-circle { 
        width: 38px; 
        height: 38px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 600; 
        font-size: 14px; 
        color: white; 
        background: linear-gradient(135deg, #116fb7, #22C55E); 
    }

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
    <h4 class="page-title text-dark font-weight-bold">System Administrators</h4>
    <p class="text-secondary font-13 mb-0">Manage HR and Administrative accounts, passwords, security statuses, and access permissions.</p>
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
                <strong class="font-weight-bold"><i class="ti-alert mr-1"></i> Operations Failed:</strong>
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
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Active Admin Accounts</h5>
                <button class="btn btn-saas btn-saas-primary px-4 shadow-sm" data-toggle="modal" data-target="#addUserModal">
                    <i class="ti-plus mr-1"></i> Add Account
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name & Profile</th>
                                <th>Email Address</th>
                                <th>Role</th>
                                <th class="text-center">Account Status</th>
                                <th>Last Active IP</th>
                                <th>Last Active Time</th>
                                <th class="text-right" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $user->name }}</h6>
                                            <span class="text-muted font-11">ID: #{{ $user->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $user->roles->first()->name ?? 'No Role' }}</span>
                                </td>
                                <td class="text-center">
                                    <label class="form-switch-saas">
                                        <input type="checkbox" class="status-toggle-checkbox" data-id="{{ $user->id }}" {{ $user->status === 'Active' ? 'checked' : '' }} {{ auth()->id() == $user->id ? 'disabled' : '' }}>
                                        <span class="slider-saas"></span>
                                    </label>
                                </td>
                                <td>
                                    <span class="text-secondary font-12 font-weight-bold">{{ $user->last_login_ip ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted font-12">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y h:i A') : 'Never Logged In' }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="action-icon-btn" data-toggle="modal" data-target="#editUserModal{{ $user->id }}" title="Edit"><i class="ti-pencil"></i></button>
                                        @if(auth()->id() != $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete this admin account permanently?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn text-danger" title="Delete"><i class="ti-trash"></i></button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                <div class="card-footer bg-white border-top p-4">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- ==============================================
     ADD ADMIN ACCOUNT MODAL
=============================================== -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Register Admin User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Full Name</label>
                        <input type="text" name="name" class="form-control form-control-saas" required placeholder="e.g. Ram HR">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-saas" required placeholder="e.g. ram@ams.com">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Security Role</label>
                        <select name="role_id" class="form-control form-control-saas" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Account State</label>
                        <select name="status" class="form-control form-control-saas" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Password</label>
                        <input type="password" name="password" class="form-control form-control-saas" required placeholder="Minimum 6 characters">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-saas" required placeholder="Repeat password">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 px-4">
                    <button type="button" class="btn btn-saas btn-saas-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==============================================
     EDIT ADMIN ACCOUNT MODALS
=============================================== -->
@foreach($users as $user)
<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom p-4">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-pencil text-primary mr-2"></i> Modify Admin Account: {{ $user->name }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Full Name</label>
                        <input type="text" name="name" class="form-control form-control-saas" value="{{ $user->name }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-saas" value="{{ $user->email }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Security Role</label>
                        <select name="role_id" class="form-control form-control-saas" required>
                            @php $assignedRole = $user->roles->first(); @endphp
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $assignedRole && $assignedRole->id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Account State</label>
                        <select name="status" class="form-control form-control-saas" required {{ auth()->id() == $user->id ? 'disabled' : '' }}>
                            <option value="Active" {{ $user->status === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $user->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if(auth()->id() == $user->id)
                            <input type="hidden" name="status" value="Active">
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">New Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control form-control-saas" placeholder="Enter new password if changing">
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted font-12 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-saas" placeholder="Repeat new password">
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
    // AJAX status toggle checkbox click handler
    document.querySelectorAll('.status-toggle-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            let userId = this.getAttribute('data-id');
            let isChecked = this.checked;
            let self = this;
            
            Swal.fire({
                title: 'Updating account status...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });
            
            fetch(`/users/${userId}/toggle-status`, {
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

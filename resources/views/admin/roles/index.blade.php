@extends('layouts.master')

@section('css')
<style>
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
    }
    
    .role-tab-btn {
        text-align: left;
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: var(--text-normal);
        background: transparent;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .role-tab-btn:hover {
        background-color: #f1f5f9;
        color: var(--primary-blue);
    }
    .role-tab-btn.active {
        background-color: rgba(17, 111, 183, 0.08);
        color: var(--primary-blue);
        border-color: rgba(17, 111, 183, 0.15);
    }

    .permission-group-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.8px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 6px;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Roles & Access Control</h4>
    <p class="text-secondary font-13 mb-0">Configure Role-Based Access Control permissions for administrative staff groups.</p>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        
        <!-- Validation Messages -->
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

        <div class="row mt-3">
            <!-- 1. LEFT SIDEBAR: ROLES SELECTION -->
            <div class="col-md-3 mb-4">
                <div class="card saas-card">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="m-0 font-weight-bold text-dark"><i class="ti-lock text-primary mr-2"></i> System Roles</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            @foreach($roles as $index => $role)
                                <a class="role-tab-btn nav-link {{ $index === 0 ? 'active' : '' }}" id="v-pills-role-tab-{{ $role->id }}" data-toggle="pill" href="#v-pills-role-{{ $role->id }}" role="tab">
                                    <span><i class="ti-shield mr-2"></i> {{ $role->name }}</span>
                                    <i class="ti-angle-right"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. RIGHT CONTENT: PERMISSIONS ASSIGNMENT GRID -->
            <div class="col-md-9 mb-4">
                <div class="tab-content" id="v-pills-tabContent">
                    @foreach($roles as $index => $role)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="v-pills-role-{{ $role->id }}" role="tabpanel">
                            
                            <div class="card saas-card">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">{{ $role->name }} Access Rights</h5>
                                        <small class="text-secondary">Configure what members of this role group can view and perform.</small>
                                    </div>
                                    @if($role->slug === 'admin')
                                        <span class="badge badge-success px-3 py-2"><i class="ti-check-box mr-1"></i> Full System Access</span>
                                    @endif
                                </div>
                                
                                @if($role->slug === 'admin')
                                    <!-- Read Only message for Administrator -->
                                    <div class="card-body py-5 text-center text-muted">
                                        <i class="ti-shield text-success mb-3" style="font-size: 40px; opacity: 0.8;"></i>
                                        <h5 class="font-weight-bold text-dark">Hardcoded Superuser Account</h5>
                                        <p class="mx-auto" style="max-width: 480px; font-size: 13px;">The System Administrator role possesses full structural capabilities by default. For safety, these permissions are immutable and cannot be restricted.</p>
                                    </div>
                                @else
                                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="card-body">
                                            @foreach($availablePermissions as $groupName => $perms)
                                                <div class="mb-4">
                                                    <h6 class="permission-group-title">{{ $groupName }}</h6>
                                                    <div class="row">
                                                        @foreach($perms as $key => $label)
                                                            <div class="col-md-6 mb-3">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" class="custom-control-input" id="check-{{ $role->id }}-{{ Str::slug($key) }}" {{ is_array($role->permissions) && in_array($key, $role->permissions) ? 'checked' : '' }}>
                                                                    <label class="custom-control-label font-weight-normal text-dark font-14" style="cursor: pointer;" for="check-{{ $role->id }}-{{ Str::slug($key) }}">
                                                                        {{ $label }}
                                                                        <span class="text-secondary font-11 d-block mt-1">Allows: <code>{{ $key }}</code></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="card-footer bg-light border-top p-3 px-4 text-right">
                                            <button type="submit" class="btn btn-saas btn-saas-primary px-4 shadow-sm">
                                                <i class="ti-save mr-1"></i> Save Role Permissions
                                            </button>
                                        </div>
                                    </form>
                                @endif
                                
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

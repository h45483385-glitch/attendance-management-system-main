@extends('layouts.master')

@section('css')
<style>
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Security Policies</h4>
    <p class="text-secondary font-13 mb-0">Configure brute-force lockouts, session timeouts, and password length policies.</p>
</div>
@endsection

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-8 col-lg-6">
        
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

        <div class="card saas-card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom p-4">
                <h5 class="m-0 font-weight-bold text-dark"><i class="ti-shield text-primary mr-2"></i> Security Policy Controls</h5>
            </div>
            
            <form method="POST" action="{{ route('security.settings.update') }}">
                @csrf
                <div class="card-body p-4">
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark font-13 mb-1"><i class="ti-lock text-primary mr-1"></i> Max Failed Login Attempts</label>
                        <span class="text-muted font-11 d-block mb-2">Lock account temporary after consecutive failed passwords.</span>
                        <input type="number" name="max_failed_attempts" class="form-control form-control-saas" value="{{ $settings['max_failed_attempts'] }}" min="3" max="20" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark font-13 mb-1"><i class="ti-timer text-primary mr-1"></i> Lockout Duration (Minutes)</label>
                        <span class="text-muted font-11 d-block mb-2">Duration an account remains locked out before automatically unlocking.</span>
                        <input type="number" name="lockout_duration" class="form-control form-control-saas" value="{{ $settings['lockout_duration'] }}" min="1" max="1440" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark font-13 mb-1"><i class="ti-time text-primary mr-1"></i> Idle Session Timeout (Minutes)</label>
                        <span class="text-muted font-11 d-block mb-2">Force logout administrative users after specific inactive duration.</span>
                        <input type="number" name="session_timeout" class="form-control form-control-saas" value="{{ $settings['session_timeout'] }}" min="5" max="1440" required>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark font-13 mb-1"><i class="ti-text text-primary mr-1"></i> Minimum Password Length</label>
                        <span class="text-muted font-11 d-block mb-2">Enforced minimum character count during password updates or user creation.</span>
                        <input type="number" name="password_min_length" class="form-control form-control-saas" value="{{ $settings['password_min_length'] }}" min="6" max="32" required>
                    </div>

                </div>
                
                <div class="card-footer bg-light border-top p-3 px-4 text-right">
                    <button type="submit" class="btn btn-saas btn-saas-primary px-4 shadow-sm">Save Policies</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

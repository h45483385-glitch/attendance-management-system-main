@extends('layouts.master-blank')

@section('content')

<style>
    body { 
        background-color: #f8fafc; 
        font-family: 'Poppins', sans-serif;
    }
    .login-wrapper { 
        max-width: 450px !important; 
        width: 100% !important; 
        margin: 7% auto !important; 
        padding: 0 15px; 
    }
    .login-card { 
        border-radius: 16px; 
        border: 1px solid #f1f5f9; 
        box-shadow: 0 20px 40px rgba(17, 111, 183, 0.05); 
        overflow: hidden; 
        background: #ffffff; 
    }
    
    .login-header { 
        background: linear-gradient(135deg, #116fb7, #0d558d); 
        padding: 45px 20px 55px 20px; 
        color: white; 
        text-align: center; 
        position: relative; 
    }
    .login-header h4 { 
        font-weight: 700; 
        font-size: 24px; 
        letter-spacing: 0.5px; 
        margin-bottom: 6px;
    }
    .login-header p { 
        font-size: 14px; 
        opacity: 0.85; 
    }
    
    .logo-circle { 
        width: 80px; 
        height: 80px; 
        background: white; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        margin: -40px auto 20px auto; 
        box-shadow: 0 8px 20px rgba(17, 111, 183, 0.1); 
        position: relative; 
        z-index: 2; 
        border: 4px solid #f8fafc; 
    }
    .logo-circle i { 
        font-size: 38px; 
        background: -webkit-linear-gradient(135deg, #116fb7, #22C55E); 
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
    }

    .form-control-saas { 
        border-radius: 8px; 
        padding: 12px 15px; 
        border: 1px solid #cbd5e1; 
        font-size: 14px; 
        color: #334155; 
        width: 100%;
        transition: all 0.2s;
    }
    .form-control-saas:focus { 
        border-color: #116fb7; 
        outline: none;
        box-shadow: 0 0 0 3px rgba(17, 111, 183, 0.15); 
    }
    .form-group label { 
        font-weight: 600; 
        color: #475569; 
        font-size: 13px; 
    }
    
    .btn-saas-primary { 
        background: linear-gradient(135deg, #116fb7, #0d558d); 
        color: white; 
        border: none; 
        border-radius: 8px; 
        padding: 12px 24px; 
        font-weight: 700; 
        font-size: 14px; 
        width: 100%; 
        transition: all 0.3s; 
        box-shadow: 0 4px 15px rgba(17, 111, 183, 0.2); 
        cursor: pointer;
    }
    .btn-saas-primary:hover { 
        transform: translateY(-1px); 
        box-shadow: 0 8px 20px rgba(17, 111, 183, 0.3); 
        color: white; 
    }
</style>

<div class="login-wrapper">
    <div class="card login-card">
        
        <!-- Header Section -->
        <div class="login-header">
            <h4>Update Password</h4>
            <p class="mb-0">Choose a new password for your account</p>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4 pt-0">
            
            <!-- Floating Logo -->
            <div class="logo-circle">
                <i class="mdi mdi-key-variant"></i>
            </div>

            <form class="form-horizontal mt-3" method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Input -->
                <div class="form-group mb-3">
                    <label for="email" class="d-block mb-1">{{ __('E-Mail Address') }}</label>
                    <input id="email" type="email" class="form-control-saas @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert" style="display: block;">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="form-group mb-3">
                    <label for="password" class="d-block mb-1">{{ __('New Password') }}</label>
                    <input id="password" type="password" class="form-control-saas @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                    @error('password')
                        <span class="invalid-feedback" role="alert" style="display: block;">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password Input -->
                <div class="form-group mb-4">
                    <label for="password-confirm" class="d-block mb-1">{{ __('Confirm New Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control-saas" name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="form-group mt-4 mb-0">
                    <button class="btn-saas-primary" type="submit">
                        Reset Password <i class="mdi mdi-lock-open-outline ml-1"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
    
    <div class="text-center mt-4 text-muted" style="font-size: 12px; font-weight: 500; letter-spacing: 0.3px;">
        &copy; 2026 Attendance Management System. <br> Powered by Pragnaware Solutions.
    </div>
</div>

@endsection

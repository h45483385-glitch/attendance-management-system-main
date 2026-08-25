@extends('layouts.master-blank')

@section('content')

<style>
    /* Modern Indigo Login Theme */
    body { background-color: #f8fafc; }
    .login-wrapper { max-width: 480px !important; width: 100% !important; margin: 6% auto !important; padding: 0 15px; }
    .login-card { border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.08); overflow: hidden; background: #ffffff; }
    
    .login-header { background: linear-gradient(135deg, #4f46e5, #312e81); padding: 40px 20px 50px 20px; color: white; text-align: center; position: relative; }
    .login-header h4 { font-weight: 700; font-size: 24px; letter-spacing: 0.5px; }
    .login-header p { font-size: 14px; opacity: 0.8; }
    
    /* Logo Circle overlapping header and body */
    .logo-circle { width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: -40px auto 20px auto; box-shadow: 0 8px 20px rgba(0,0,0,0.1); position: relative; z-index: 2; border: 4px solid #f8fafc; }
    .logo-circle i { font-size: 36px; background: -webkit-linear-gradient(135deg, #4f46e5, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .form-control { border-radius: 8px; padding: 12px 15px; border: 1px solid #cbd5e1; font-size: 14px; color: #334155; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .form-group label { font-weight: 600; color: #475569; font-size: 13px; }
    
    .btn-indigo { background: linear-gradient(135deg, #4f46e5, #4338ca); color: white; border: none; border-radius: 8px; padding: 12px; font-weight: 700; font-size: 15px; width: 100%; transition: all 0.3s; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); }
    .btn-indigo:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4); color: white; }
    
    .forgot-link { color: #4f46e5; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
    .forgot-link:hover { color: #312e81; text-decoration: underline; }
    
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before { background-color: #4f46e5; border-color: #4f46e5; }
</style>

<div class="login-wrapper">
    <div class="card login-card">
        
        <!-- Header Section -->
        <div class="login-header">
            <h4 class="m-b-5">Welcome Back!</h4>
            <p class="mb-0">Sign in to continue to AMS Portal</p>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4 pt-0">
            
            <!-- Floating Logo -->
            <div class="logo-circle">
                <i class="mdi mdi-fingerprint"></i>
            </div>

            <form class="form-horizontal mt-3" method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Input -->
                <div class="form-group mb-4">
                    <label for="email">{{ __('Email Address') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-email-outline text-muted"></i></span>
                        </div>
                        <input id="email" type="email" class="form-control border-left-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="mb-0">{{ __('Password') }}</label>
                        <!-- Forgot Password Link -->
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                <i class="mdi mdi-lock-reset mr-1"></i>Forgot Password?
                            </a>
                        @endif
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="mdi mdi-lock-outline text-muted"></i></span>
                        </div>
                        <input id="password" type="password" class="form-control border-left-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
               
                <!-- Remember Me & Submit -->
                <div class="form-group row mt-4 mb-0 align-items-center">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="remember" style="font-size: 13px; font-weight: 500; cursor: pointer;">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-right text-center">
                        <button class="btn btn-indigo" type="submit">
                            Secure Log In <i class="mdi mdi-login-variant ml-1"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    
    <div class="text-center mt-4 text-muted" style="font-size: 12px; font-weight: 500;">
        &copy; 2026 Attendance Management System. <br> Pragnaware Solutions.
    </div>
</div>
<!-- end wrapper-page -->

@endsection

@section('script')
@endsection
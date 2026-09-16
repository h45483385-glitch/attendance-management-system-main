@extends('layouts.master-blank')

@section('content')

<style>
    /* Premium SaaS Login Theme */
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
    
    /* Logo Circle overlapping header and body */
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
    
    .forgot-link { 
        color: #116fb7; 
        font-size: 13px; 
        font-weight: 600; 
        text-decoration: none; 
        transition: 0.2s; 
    }
    .forgot-link:hover { 
        color: #0d558d; 
        text-decoration: underline; 
    }
    
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before { 
        background-color: #116fb7; 
        border-color: #116fb7; 
    }
</style>

<div class="login-wrapper">
    <div class="card login-card">
        
        <!-- Header Section -->
        <div class="login-header">
            <h4>Welcome Back!</h4>
            <p class="mb-0">Sign in to continue to AMS Portal</p>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4 pt-0">
            
            <!-- Floating Logo -->
            <div class="logo-circle">
                <i class="mdi mdi-fingerprint"></i>
            </div>

            <!-- Auth Mode Switcher -->
            <div class="d-flex justify-content-center mb-4 border-bottom pb-2" style="gap: 10px;">
                <button type="button" id="tab-btn-pwd" class="btn btn-sm btn-link text-primary font-weight-bold" onclick="switchLoginTab('pwd')" style="text-decoration: none; border-bottom: 2px solid #116fb7; border-radius: 0;">
                    <i class="mdi mdi-lock mr-1"></i> Password
                </button>
                <button type="button" id="tab-btn-face" class="btn btn-sm btn-link text-muted font-weight-bold" onclick="switchLoginTab('face')" style="text-decoration: none; border-radius: 0;">
                    <i class="mdi mdi-face-recognition mr-1"></i> Face ID
                </button>
                <button type="button" id="tab-btn-bio" class="btn btn-sm btn-link text-muted font-weight-bold" onclick="switchLoginTab('bio')" style="text-decoration: none; border-radius: 0;">
                    <i class="mdi mdi-fingerprint mr-1"></i> Windows Hello
                </button>
            </div>

            <!-- 1. Password Login Form -->
            <div id="section-password">
                <form class="form-horizontal mt-2" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-group mb-4">
                        <label for="email" class="d-block mb-2">{{ __('Email Address') }}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px;"><i class="mdi mdi-email-outline text-muted font-16"></i></span>
                            </div>
                            <input id="email" type="email" class="form-control-saas border-left-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your corporate email" style="border-radius: 0 8px 8px 0;">
                            @error('email')
                                <span class="invalid-feedback" role="alert" style="display: block;">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password" class="mb-0">{{ __('Password') }}</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="forgot-link">
                                    <i class="mdi mdi-lock-reset mr-1"></i>Forgot Password?
                                </a>
                            @endif
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px;"><i class="mdi mdi-lock-outline text-muted font-16"></i></span>
                            </div>
                            <input id="password" type="password" class="form-control-saas border-left-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your secure password" style="border-radius: 0 8px 8px 0;">
                            @error('password')
                                <span class="invalid-feedback" role="alert" style="display: block;">
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
                                <label class="custom-control-label font-13" for="remember" style="font-weight: 500; cursor: pointer; color: #475569;">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-right text-center">
                            <button class="btn-saas-primary" type="submit">
                                Secure Log In <i class="mdi mdi-login-variant ml-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 2. Face ID Passwordless Login -->
            <div id="section-face" style="display: none;" class="text-center">
                <p class="text-muted font-13 mb-3">Passwordless login with live facial verification. Look at the camera to authenticate.</p>
                <div class="camera-preview-box mb-3 rounded border" style="background:#0f172a; height: 230px; position: relative; overflow: hidden;">
                    <video id="face-login-video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                    <div id="face-scan-line" style="position: absolute; top:0; left:0; right:0; height: 2px; background: #22c55e; box-shadow: 0 0 10px #22c55e; display: none;"></div>
                </div>
                <div class="d-flex" style="gap: 10px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill rounded" onclick="startFaceLoginCamera()">
                        <i class="mdi mdi-camera mr-1"></i> Start Camera
                    </button>
                    <button type="button" id="btn-scan-face-login" class="btn btn-success btn-sm flex-fill rounded font-weight-bold" onclick="captureAndLoginFace()">
                        <i class="mdi mdi-face-recognition mr-1"></i> Authenticate Face
                    </button>
                </div>
            </div>

            <!-- 3. Windows Hello / Device Biometrics -->
            <div id="section-bio" style="display: none;" class="text-center py-2">
                <div class="p-3 bg-light rounded-lg mb-3">
                    <i class="mdi mdi-shield-check text-primary" style="font-size: 42px;"></i>
                    <h6 class="font-weight-bold text-dark mt-2 mb-1">Zero-Storage Biometrics</h6>
                    <p class="text-muted font-12 mb-0">Authenticate instantly using Windows Hello, fingerprint sensor, or Touch ID via secure FIDO2 signals.</p>
                </div>
                <button type="button" class="btn-saas-primary" onclick="loginWithWebAuthn()">
                    <i class="mdi mdi-fingerprint mr-1"></i> Authenticate with Windows Hello
                </button>
            </div>

        </div>
    </div>
    
    <div class="text-center mt-4 text-muted" style="font-size: 12px; font-weight: 500; letter-spacing: 0.3px;">
        &copy; 2026 Attendance Management System. <br> Powered by Pragnaware Solutions.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let loginStream = null;

    function switchLoginTab(tab) {
        document.getElementById('section-password').style.display = (tab === 'pwd') ? 'block' : 'none';
        document.getElementById('section-face').style.display = (tab === 'face') ? 'block' : 'none';
        document.getElementById('section-bio').style.display = (tab === 'bio') ? 'block' : 'none';

        ['pwd', 'face', 'bio'].forEach(t => {
            let btn = document.getElementById('tab-btn-' + t);
            if (t === tab) {
                btn.className = 'btn btn-sm btn-link text-primary font-weight-bold';
                btn.style.borderBottom = '2px solid #116fb7';
            } else {
                btn.className = 'btn btn-sm btn-link text-muted font-weight-bold';
                btn.style.borderBottom = 'none';
            }
        });

        if (tab === 'face') {
            startFaceLoginCamera();
        } else {
            stopFaceLoginCamera();
        }
    }

    function startFaceLoginCamera() {
        if (loginStream) return;
        navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 640 }, height: { ideal: 480 } } })
            .then(stream => {
                loginStream = stream;
                document.getElementById('face-login-video').srcObject = stream;
            })
            .catch(err => {
                Swal.fire('Camera Error', 'Unable to access webcam: ' + err.message, 'error');
            });
    }

    function stopFaceLoginCamera() {
        if (loginStream) {
            loginStream.getTracks().forEach(t => t.stop());
            loginStream = null;
        }
    }

    function captureAndLoginFace() {
        const video = document.getElementById('face-login-video');
        if (!loginStream || !video.videoWidth) {
            Swal.fire('Camera Not Ready', 'Please start the webcam first.', 'warning');
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const base64Image = canvas.toDataURL('image/jpeg', 0.85);

        Swal.fire({
            title: 'Verifying Face ID...',
            text: 'Matching facial features with enrolled profiles.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('{{ route("face.login") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ image: base64Image })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                stopFaceLoginCamera();
                Swal.fire({
                    icon: 'success',
                    title: 'Authentication Successful!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirectUrl;
                });
            } else {
                Swal.fire('Login Failed', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Server Connection Error', err.message, 'error');
        });
    }

    function loginWithWebAuthn() {
        if (!window.PublicKeyCredential) {
            Swal.fire('Not Supported', 'WebAuthn is not supported by your browser.', 'error');
            return;
        }

        Swal.fire({
            title: 'Touch Fingerprint / Windows Hello',
            text: 'Please authenticate on your local device...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Request login challenge
        fetch('{{ route("webauthn.login.challenge") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(opts => {
            if (!opts.challenge) throw new Error(opts.message || 'Challenge failed');
            
            // Convert challenge buffer
            opts.challenge = Uint8Array.from(atob(opts.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
            if (opts.allowCredentials) {
                opts.allowCredentials = opts.allowCredentials.map(c => ({
                    ...c,
                    id: Uint8Array.from(atob(c.id.replace(/-/g, '+').replace(/_/g, '/')), ch => ch.charCodeAt(0))
                }));
            }

            return navigator.credentials.get({ publicKey: opts });
        })
        .then(assertion => {
            const credential = {
                id: assertion.id,
                rawId: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))),
                type: assertion.type,
                response: {
                    authenticatorData: btoa(String.fromCharCode(...new Uint8Array(assertion.response.authenticatorData))),
                    clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(assertion.response.clientDataJSON))),
                    signature: btoa(String.fromCharCode(...new Uint8Array(assertion.response.signature)))
                }
            };

            return fetch('{{ route("webauthn.login.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(credential)
            });
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Biometrics Verified!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = res.redirectUrl || '{{ route("admin") }}';
                });
            } else {
                Swal.fire('Verification Failed', res.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Authentication Cancelled / Failed', err.message, 'error');
        });
    }
</script>

@endsection
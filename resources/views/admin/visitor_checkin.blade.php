@extends('layouts.master')

@section('content')

<style>
    .visitor-card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; overflow: hidden; }
    .card-header-indigo { background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; padding: 25px; }
    .form-control { border-radius: 8px; border: 1px solid #cbd5e1; padding: 12px 15px; font-size: 14px; background-color: #f8fafc; }
    .form-control:focus { border-color: #4f46e5; background-color: #ffffff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .form-group label { font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    
    .btn-indigo { background: linear-gradient(135deg, #4f46e5, #4338ca); color: white; border: none; font-weight: 600; border-radius: 8px; padding: 12px 30px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); transition: all 0.3s; }
    .btn-indigo:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4); }
    .btn-outline-indigo { border: 2px solid #4f46e5; color: #4f46e5; background: transparent; font-weight: 600; border-radius: 8px; padding: 10px 20px; transition: all 0.3s; }
    .btn-outline-indigo:hover { background: #4f46e5; color: white; }

    .webcam-container { width: 100%; max-width: 320px; margin: 0 auto; border-radius: 12px; overflow: hidden; border: 4px solid #e2e8f0; position: relative; background: #000; }
    #webcam { width: 100%; height: auto; display: block; }
    #snapshot-preview { width: 100%; height: auto; display: none; border-radius: 8px; }
    
    .badge-display { background: #e0e7ff; color: #3730a3; padding: 15px; border-radius: 10px; text-align: center; border: 2px dashed #4f46e5; margin-bottom: 20px; }
    .badge-display h2 { margin: 0; font-weight: 800; letter-spacing: 2px; font-size: 24px; }
</style>

<div class="container-fluid pt-4">
    <!-- Main Form wraps both Steps -->
    <form action="{{ route('visitor.store') }}" method="POST" id="checkinForm" enctype="multipart/form-data">
        @csrf

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card visitor-card mt-3 mb-5">
                    
                    <div class="card-header card-header-indigo d-flex align-items-center">
                        <i class="mdi mdi-account-badge-outline display-4 mr-3" style="font-size: 32px;"></i>
                        <div>
                            <h3 class="card-title mb-0" style="font-weight: 700; font-size: 20px;">Step 1: Visitor Basic Info</h3>
                            <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Enter the guest's details before capturing biometrics</p>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label>Visitor Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <!-- FIXED: 10-digit strict validation -->
                                <input type="text" name="phone" class="form-control" required minlength="10" maxlength="15" pattern="[0-9]+" title="Enter a valid phone number (minimum 10 digits)" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="Contact number (Min 10 digits)">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label>Person to Meet <span class="text-danger">*</span></label>
                                <input type="text" name="person_to_meet" class="form-control" required placeholder="Who are they meeting?">
                            </div>
                            <div class="col-md-6 form-group mb-4">
                                <label>Company / Organization</label>
                                <input type="text" name="company" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group mb-4">
                                <label>Purpose of Visit <span class="text-danger">*</span></label>
                                <input type="text" name="purpose" class="form-control" required placeholder="e.g. Client Meeting, Interview, Delivery">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right p-4" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-indigo" onclick="openBiometricModal()">
                            Proceed to Biometrics <i class="mdi mdi-arrow-right ml-2"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Step 2: The Smart Check-in Biometric Pop-up (Modal) -->
        <div class="modal fade" id="biometricModal" data-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; border: none;">
                    
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title font-weight-bold text-dark">
                            <i class="mdi mdi-camera-iris text-primary mr-2"></i> Step 2: Biometric & Govt ID Verification
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" onclick="stopWebcam()" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body p-4">
                        <div class="row">
                            
                            <!-- Left Side: Live Webcam -->
                            <div class="col-md-6 text-center border-right">
                                <h6 class="font-weight-bold text-muted mb-3 text-uppercase">Capture Photo</h6>
                                
                                <div class="webcam-container mb-3 shadow-sm">
                                    <video id="webcam" autoplay playsinline></video>
                                    <img id="snapshot-preview" src="">
                                    <canvas id="canvas" class="d-none"></canvas>
                                </div>
                                
                                <button type="button" id="captureBtn" class="btn btn-outline-indigo btn-block" onclick="takeSnapshot()">
                                    <i class="mdi mdi-camera"></i> Capture Photo
                                </button>
                                <button type="button" id="retakeBtn" class="btn btn-light btn-block d-none" onclick="retakePhoto()">
                                    <i class="mdi mdi-refresh"></i> Retake
                                </button>
                                
                                <input type="hidden" name="visitor_photo" id="photo_data" required>
                            </div>

                            <!-- Right Side: Auto-Badge & Govt ID -->
                            <div class="col-md-6 pl-md-4 mt-4 mt-md-0">
                                <h6 class="font-weight-bold text-muted mb-3 text-uppercase">Visitor Badge & ID</h6>
                                
                                <div class="badge-display">
                                    <p class="mb-1 font-12 font-weight-bold text-uppercase">Generated Badge ID</p>
                                    <h2 id="auto_badge_id">VIS-{{ date('Ymd') }}-***</h2>
                                </div>

                                <div class="form-group mb-4">
                                    <label>Govt ID Type <span class="text-danger">*</span></label>
                                    <select name="id_type" class="form-control" required style="cursor: pointer;">
                                        <option value="" disabled selected>Select ID Type...</option>
                                        <option value="Aadhar">Aadhar Card</option>
                                        <option value="PAN">PAN Card</option>
                                        <option value="Driving License">Driving License</option>
                                    </select>
                                </div>

                                <div class="form-group mb-4">
                                    <label>ID Number <span class="text-danger">*</span></label>
                                    <input type="text" name="id_number" class="form-control" required placeholder="Enter the exact ID number" autocomplete="off">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" onclick="stopWebcam()">Cancel</button>
                        <button type="submit" class="btn btn-indigo font-weight-bold" id="finalSubmitBtn">
                            <i class="mdi mdi-cloud-upload mr-2"></i> Confirm & Generate Badge
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </form>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const preview = document.getElementById('snapshot-preview');
    const photoDataInput = document.getElementById('photo_data');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    let webcamStream = null;

    function openBiometricModal() {
        const form = document.getElementById('checkinForm');
        
        // 1. Validate Basic Info (Step 1) before opening webcam
        const basicInputs = form.querySelectorAll('.card-body input[required]');
        let isValid = true;
        basicInputs.forEach(input => {
            if (!input.checkValidity()) {
                input.reportValidity();
                isValid = false;
            }
        });

        if (!isValid) return; // Stop if form is incomplete

        // 2. Generate Random Badge ID
        const randomNum = Math.floor(Math.random() * 900) + 100;
        const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, "");
        document.getElementById('auto_badge_id').innerText = `VIS-${dateStr}-${randomNum}`;

        // 3. Open Modal
        $('#biometricModal').modal('show');

        // 4. Start Webcam
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    webcamStream = stream;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    preview.style.display = 'none';
                    captureBtn.classList.remove('d-none');
                    retakeBtn.classList.add('d-none');
                })
                .catch(function (error) {
                    console.error("Webcam Error: ", error);
                    alert("Unable to access Web Camera. Please check permissions.");
                });
        }
    }

    function takeSnapshot() {
        if (!webcamStream) return;
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataURL = canvas.toDataURL('image/jpeg', 0.9);
        
        photoDataInput.value = dataURL;
        preview.src = dataURL;
        video.style.display = 'none';
        preview.style.display = 'block';
        captureBtn.classList.add('d-none');
        retakeBtn.classList.remove('d-none');
    }

    function retakePhoto() {
        photoDataInput.value = "";
        preview.style.display = 'none';
        video.style.display = 'block';
        captureBtn.classList.remove('d-none');
        retakeBtn.classList.add('d-none');
    }

    function stopWebcam() {
        if (webcamStream) {
            const tracks = webcamStream.getTracks();
            tracks.forEach(track => track.stop());
            webcamStream = null;
        }
    }

    document.getElementById('checkinForm').addEventListener('submit', function(e) {
        if (photoDataInput.value === "") {
            e.preventDefault();
            alert("Please Capture the Visitor's Photo before checking in!");
        }
    });
</script>

@endsection
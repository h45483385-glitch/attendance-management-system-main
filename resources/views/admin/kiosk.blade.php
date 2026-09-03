<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- SweetAlert for Popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 h-screen flex flex-col items-center justify-center relative">

    <div class="bg-white p-8 rounded-xl shadow-lg text-center max-w-lg w-full relative z-10">
        <h1 class="text-3xl font-bold mb-2 text-gray-800">Daily Kiosk</h1>
        
        <!-- Toggle Tabs -->
        <div class="flex justify-center mb-4 border-b border-gray-200">
            <button id="tab-face" class="px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-semibold focus:outline-none transition-colors">Face ID</button>
            <button id="tab-fallback" class="px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold focus:outline-none transition-colors">Credential Login</button>
        </div>

        <!-- FACE ID SECTION -->
        <div id="section-face">
            <p class="text-gray-500 mb-6 text-sm">Please look at the camera to Check In or Check Out.</p>

            <!-- Camera Wrapper -->
            <div class="relative w-full h-[280px] bg-black rounded-lg overflow-hidden mb-6 border border-gray-300">
                <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
                
                <!-- Status Overlay -->
                <div id="status-overlay" class="hidden absolute bottom-0 left-0 w-full bg-black/80 p-3 flex flex-col items-center justify-center">
                    <p id="status-text" class="text-white font-bold text-[13px] mb-1.5"></p>
                    <p id="office-status" class="text-xs font-semibold flex items-center"></p>
                </div>
            </div>

            <button id="scan-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-lg w-full transition duration-300 disabled:opacity-50">
                Scan Face
            </button>
        </div>

        <!-- FALLBACK AUTH SECTION -->
        <div id="section-fallback" class="hidden text-left">
            <p class="text-gray-500 mb-4 text-sm text-center">Use your Employee ID and PIN to authenticate securely.</p>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Employee ID or Email</label>
                <input type="text" id="fallback-identifier" class="shadow appearance-none border rounded w-full py-3 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter Employee ID">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Secure PIN</label>
                <input type="password" id="fallback-password" class="shadow appearance-none border rounded w-full py-3 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter PIN">
            </div>
            
            <button id="fallback-btn" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3.5 px-6 rounded-lg w-full transition duration-300">
                Secure Check-In
            </button>
        </div>
    </div>

    <!-- Privacy Consent Modal (Tailwind custom) -->
    <div id="privacy-modal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-sm w-full text-center">
            <i class="fa-solid fa-shield-halved text-4xl text-blue-500 mb-4"></i>
            <h2 class="text-xl font-bold mb-2">Privacy Consent</h2>
            <p class="text-gray-600 text-sm mb-6">This kiosk requires access to your camera for facial recognition. Your biometric data is processed securely and is never stored as raw images. Do you consent to activate the camera?</p>
            <div class="flex space-x-3">
                <button id="consent-deny" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg font-semibold transition">Deny</button>
                <button id="consent-allow" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition">Allow</button>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const scanBtn = document.getElementById('scan-btn');
        const statusOverlay = document.getElementById('status-overlay');
        const statusText = document.getElementById('status-text');
        const officeStatus = document.getElementById('office-status');
        
        // Tabs
        const tabFace = document.getElementById('tab-face');
        const tabFallback = document.getElementById('tab-fallback');
        const sectionFace = document.getElementById('section-face');
        const sectionFallback = document.getElementById('section-fallback');

        // Fallback Elements
        const fallbackBtn = document.getElementById('fallback-btn');
        const fallbackIdentifier = document.getElementById('fallback-identifier');
        const fallbackPassword = document.getElementById('fallback-password');

        // Consent Modal
        const privacyModal = document.getElementById('privacy-modal');
        const consentAllow = document.getElementById('consent-allow');
        const consentDeny = document.getElementById('consent-deny');

        let currentLat = null;
        let currentLng = null;
        let currentAddress = "Location fetched";
        let isInsideOffice = false;
        let cameraStream = null;

        const officeLat = 12.9715987; 
        const officeLng = 77.5945627;
        const allowedRadius = 200; 

        // Tab Switching Logic
        tabFace.addEventListener('click', () => {
            tabFace.classList.add('border-blue-600', 'text-blue-600');
            tabFace.classList.remove('border-transparent', 'text-gray-500');
            tabFallback.classList.remove('border-blue-600', 'text-blue-600');
            tabFallback.classList.add('border-transparent', 'text-gray-500');
            sectionFace.classList.remove('hidden');
            sectionFallback.classList.add('hidden');
        });

        tabFallback.addEventListener('click', () => {
            tabFallback.classList.add('border-blue-600', 'text-blue-600');
            tabFallback.classList.remove('border-transparent', 'text-gray-500');
            tabFace.classList.remove('border-blue-600', 'text-blue-600');
            tabFace.classList.add('border-transparent', 'text-gray-500');
            sectionFallback.classList.remove('hidden');
            sectionFace.classList.add('hidden');
            
            // Turn off camera if running
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
        });

        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; 
            const p1 = lat1 * Math.PI/180;
            const p2 = lat2 * Math.PI/180;
            const dp = (lat2-lat1) * Math.PI/180;
            const dl = (lon2-lon1) * Math.PI/180;
            const a = Math.sin(dp/2) * Math.sin(dp/2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl/2) * Math.sin(dl/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async (position) => {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                const distance = getDistance(currentLat, currentLng, officeLat, officeLng);
                isInsideOffice = distance <= allowedRadius;
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentLat}&lon=${currentLng}`);
                    const data = await res.json();
                    currentAddress = data.display_name;
                } catch(e) {}
            }, () => { console.log("Location access denied."); });
        }

        // Hardware Detection & Consent Flow
        window.addEventListener('DOMContentLoaded', async () => {
            // Wait for user to interact with Face tab if it's open
            privacyModal.classList.remove('hidden');
        });

        consentDeny.addEventListener('click', () => {
            privacyModal.classList.add('hidden');
            tabFallback.click(); // Switch to fallback
            Swal.fire('Camera Disabled', 'Please use the secure Credential Login instead.', 'info');
        });

        consentAllow.addEventListener('click', async () => {
            privacyModal.classList.add('hidden');
            
            // 1. Check for hardware existence first
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                showHardwareError();
                return;
            }

            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const hasVideo = devices.some(device => device.kind === 'videoinput');
                
                if (!hasVideo) {
                    showHardwareError();
                    return;
                }

                // 2. Request stream
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                cameraStream = stream;
                video.srcObject = stream;

            } catch (err) {
                console.error("Camera access error:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Permission Denied',
                    text: 'Camera access was denied by the browser. Please allow permissions or use Credential Login.',
                    confirmButtonColor: '#3085d6'
                });
                tabFallback.click();
            }
        });

        function showHardwareError() {
            Swal.fire({
                icon: 'warning',
                title: 'Hardware Missing',
                text: 'Your device does not have a built-in camera or webcam. Please use the Credential Login method.',
                confirmButtonColor: '#3085d6'
            });
            tabFallback.click(); // Auto-switch
        }

        // --- FACE ID SCAN ---
        scanBtn.addEventListener('click', async () => {
            if (!cameraStream) {
                Swal.fire('Error', 'Camera is not active.', 'error');
                return;
            }

            scanBtn.disabled = true;
            scanBtn.textContent = 'Matching...';
            scanBtn.classList.add('opacity-70', 'cursor-not-allowed');
            statusOverlay.classList.add('hidden');

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const imageData = canvas.toDataURL('image/jpeg'); 

            try {
                const response = await fetch('/scan-face', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        image: imageData,
                        latitude: currentLat,
                        longitude: currentLng,
                        address: currentAddress
                    })
                });

                const result = await response.json();
                statusOverlay.classList.remove('hidden');
                
                if (result.success) {
                    statusText.textContent = result.message || `Face Verified! Check-IN successful!`;
                    
                    if (isInsideOffice) {
                        officeStatus.className = "text-green-400 text-xs font-semibold flex items-center tracking-wide";
                        officeStatus.innerHTML = '<i class="fa-solid fa-location-dot mr-1.5"></i> Inside Office';
                    } else {
                        officeStatus.className = "text-rose-400 text-xs font-semibold flex items-center tracking-wide";
                        officeStatus.innerHTML = '<i class="fa-solid fa-location-dot mr-1.5"></i> Outside Office';
                    }
                } else {
                    statusText.textContent = result.message || "Face not recognized.";
                    officeStatus.innerHTML = "";
                }
            } catch (error) {
                statusOverlay.classList.remove('hidden');
                statusText.textContent = 'Network error. Please try again.';
                officeStatus.innerHTML = "";
            }

            setTimeout(() => {
                scanBtn.disabled = false;
                scanBtn.textContent = 'Scan Face';
                scanBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                statusOverlay.classList.add('hidden');
            }, 4000);
        });

        // --- FALLBACK LOGIN ---
        fallbackBtn.addEventListener('click', async () => {
            const identifier = fallbackIdentifier.value.trim();
            const password = fallbackPassword.value.trim();

            if (!identifier || !password) {
                Swal.fire('Error', 'Please enter both Employee ID and PIN.', 'error');
                return;
            }

            fallbackBtn.disabled = true;
            fallbackBtn.textContent = 'Authenticating...';
            fallbackBtn.classList.add('opacity-70', 'cursor-not-allowed');

            try {
                const response = await fetch('/fallback-checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        identifier: identifier,
                        password: password,
                        latitude: currentLat,
                        longitude: currentLng,
                        address: currentAddress
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Authenticated',
                        text: result.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    fallbackIdentifier.value = '';
                    fallbackPassword.value = '';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Access Denied',
                        text: result.message
                    });
                }
            } catch (error) {
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            }

            fallbackBtn.disabled = false;
            fallbackBtn.textContent = 'Secure Check-In';
            fallbackBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        });
    </script>
</body>
</html>
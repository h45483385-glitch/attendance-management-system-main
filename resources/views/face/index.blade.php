<!DOCTYPE html>
<html>
<head>
    <title>Face Attendance System</title>
</head>
<body>

<h2>📸 Face Attendance System</h2>

<video id="video" width="400" height="300" autoplay></video>
<br><br>

<button onclick="capture()">📷 Capture Face</button>

<canvas id="canvas" width="400" height="300" style="display:none;"></canvas>

<p id="status"></p>

<script>

// start camera
const video = document.getElementById('video');

navigator.mediaDevices.getUserMedia({ video: true })
.then(stream => {
    video.srcObject = stream;
});

// capture image
function capture() {

    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    context.drawImage(video, 0, 0, 400, 300);

    const imageData = canvas.toDataURL('image/png');

    document.getElementById('status').innerText = "Sending face...";

    fetch('/api/face-attendance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            emp_id: 111,
            image: imageData
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        document.getElementById('status').innerText =
            data.message + " (" + data.action + ")";
    })
    .catch(err => {
        console.log(err);
        document.getElementById('status').innerText = "Error";
    });
}

</script>

</body>
</html>
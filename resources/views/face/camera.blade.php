<!DOCTYPE html>
<html>
<head>
    <title>Live Face Camera</title>

    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js"></script>

    <style>
        body {
            margin: 0;
            background: #111;
            color: white;
            text-align: center;
            font-family: Arial;
        }

        video {
            border-radius: 10px;
            margin-top: 20px;
        }

        canvas {
            position: absolute;
        }

        .wrapper {
            position: relative;
            display: inline-block;
        }

        h2 {
            margin-top: 15px;
        }
    </style>
</head>

<body>

<h2>🎥 Live Face Recognition Camera</h2>

<div class="wrapper">
    <video id="video" width="720" height="560" autoplay muted></video>
</div>

<script>
const video = document.getElementById('video');

// 1. Start camera
navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
        video.srcObject = stream;
    });

// 2. Load models
Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
    faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
    faceapi.nets.faceLandmark68Net.loadFromUri('/models')
]).then(startDetection);

function startDetection() {

    const canvas = faceapi.createCanvasFromMedia(video);
    document.body.append(canvas);

    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {

        const detections = await faceapi.detectAllFaces(
            video,
            new faceapi.TinyFaceDetectorOptions()
        ).withFaceLandmarks().withFaceDescriptors();

        const resized = faceapi.resizeResults(detections, displaySize);

        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

        faceapi.draw.drawDetections(canvas, resized);

    }, 200);

}
</script>

</body>
</html>
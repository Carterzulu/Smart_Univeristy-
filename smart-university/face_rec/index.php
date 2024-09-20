<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Comparison</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Image Comparison</h1>
        <div class="webcam-container">
            <video id="video" autoplay></video>
            <button id="capture">Capture Image</button>
        </div>
        <canvas id="canvas" style="display: none;"></canvas>
        <div id="result"></div>
    </div>

    <div id="loading" class="hidden">
        <div class="spinner"></div>
        <p>Comparing images...</p>
    </div>

    <script>
        const video = document.getElementById('video');
        const captureButton = document.getElementById('capture');
        const resultDiv = document.getElementById('result');
        const loadingDiv = document.getElementById('loading');

        // Access the webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
            });

        // Capture the image and send it to the server
        captureButton.addEventListener('click', () => {
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert image to Base64
            const dataURL = canvas.toDataURL('image/png');

            // Show loading animation
            loadingDiv.classList.remove('hidden');

            // Send the image to Python for comparison
            fetch('http://localhost:5000/index', {
                method: 'POST',
                body: JSON.stringify({ image: dataURL }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingDiv.classList.add('hidden');
                resultDiv.textContent = data.message;
            })
            .catch(error => {
                loadingDiv.classList.add('hidden');
                resultDiv.textContent = 'Error occurred during comparison.';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>

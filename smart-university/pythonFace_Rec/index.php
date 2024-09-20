<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration and Attendance System</title>
    <style>
        .hidden { display: none; }
        .container { width: 60%; margin: 0 auto; text-align: center; }
        .form-container { margin: 20px 0; }
        .webcam-container { margin: 10px 0; }
        #result { margin-top: 20px; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Registration and Attendance System</h1>

        <!-- Registration Form -->
        <div class="form-container">
            <h2>Register Student</h2>
            <input type="text" id="studentName" name="name" placeholder="Enter Student Name">
          
            <div class="webcam-container">
                <video id="video" width="320" height="240" autoplay></video>
            </div>
            <button id="register">Register</button>
        </div>

        <!-- Attendance Form -->
        <div class="form-container">
            <h2>Mark Attendance</h2>
            <div class="webcam-container">
                <video id="videoAttendance" width="320" height="240" autoplay></video>
            </div>
            <button id="markAttendance">Mark Attendance</button>
        </div>

        <!-- Hidden Canvas for Image Capture -->
        <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>

        <!-- Result Display -->
        <div id="result"></div>
        <div id="loading" class="hidden">Processing...</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Access DOM elements
            const video = document.getElementById('video');
            const videoAttendance = document.getElementById('videoAttendance');
            const registerButton = document.getElementById('register');
            const markAttendanceButton = document.getElementById('markAttendance');
            const resultDiv = document.getElementById('result');
            const loadingDiv = document.getElementById('loading');

            if (!video || !videoAttendance || !registerButton || !markAttendanceButton || !resultDiv || !loadingDiv) {
                console.error("One or more DOM elements are missing.");
                return;
            }

            // Access webcam for registration and attendance
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                    videoAttendance.srcObject = stream;
                })
                .catch(error => {
                    console.error('Error accessing webcam:', error);
                    resultDiv.textContent = 'Error accessing webcam. Please check your camera settings.';
                });

            // Register student (capture image and send to server)
            registerButton.addEventListener('click', () => {
                const canvas = document.getElementById('canvas');
                const context = canvas.getContext('2d');
                const studentName = document.getElementById('studentName').value;
              //  const course = document.getElementById('course').value;

                if (!studentName) {
                    resultDiv.textContent = "Please enter both the student name and course.";
                    return;
                }

                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL('image/png');

                // Show loading indicator
                loadingDiv.classList.remove('hidden');

                // Send image and student data to server
                fetch('register.php', {
                    method: 'POST',
                    body: JSON.stringify({ image: dataURL, name: studentName }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    loadingDiv.classList.add('hidden');
                    resultDiv.textContent = data.message || 'Registration completed successfully.';
                })
                .catch(error => {
                    loadingDiv.classList.add('hidden');
                    resultDiv.textContent = 'Error occurred during registration.';
                    console.error('Error:', error);
                });
            });

            // Mark attendance (capture image and send to server)
            markAttendanceButton.addEventListener('click', () => {
                const canvas = document.getElementById('canvas');
                const context = canvas.getContext('2d');

                context.drawImage(videoAttendance, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL('image/png');

                // Show loading indicator
                loadingDiv.classList.remove('hidden');

                // Send image to Python for attendance marking
                fetch('http://localhost:5000/app', {
                    method: 'POST',
                    body: JSON.stringify({ image: dataURL }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    loadingDiv.classList.add('hidden');
                    resultDiv.textContent = data.message || 'Attendance marked successfully.';
                })
                .catch(error => {
                    loadingDiv.classList.add('hidden');
                    resultDiv.textContent = 'Error occurred during attendance marking.';
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>

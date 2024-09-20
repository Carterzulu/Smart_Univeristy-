<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

function getCourseNames($conn) {
    $sql = "SELECT courseCode,name FROM tblcourse";
    $result = $conn->query($sql);

    $courseNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courseNames[] = $row;
        }
    }

    return $courseNames;
}
function getVenueNames($conn) {
    $sql = "SELECT className FROM tblvenue";
    $result = $conn->query($sql);
    $venueNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $venueNames[] = $row;
        }
    }

    return $venueNames;
}
function getUnitNames($conn) {
    $sql = "SELECT unitCode,name FROM tblunit";
    $result = $conn->query($sql);

    $unitNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $unitNames[] = $row;
        }
    }

    return $unitNames;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);

    if (!empty($attendanceData)) {
        foreach ($attendanceData as $data) {
            $studentID = $data['studentID'];
            $attendanceStatus = $data['attendanceStatus'];
            $course = $data['course'];
            $unit = $data['unit'];
            $date = date("Y-m-d"); 

            $sql = "INSERT INTO tblattendance(studentRegistrationNumber, course, unit, attendanceStatus, dateMarked)  
                    VALUES ('$studentID', '$course', '$unit', '$attendanceStatus', '$date')";
            
            if ($conn->query($sql) === TRUE) {
                $message = " Attendance Recorded Successfully For $course : $unit on $date";
            } else {
                echo "Error inserting attendance data: " . $conn->error . "<br>";
            }
        }
    } else {
        echo "No attendance data received.<br>";
    }
} else {
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../admin/img/logo/attnlg.png" rel="icon">
  <title>lecture Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
  <script defer src="face-api.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>


<body>

<?php include 'includes/topbar.php';?>
    <section class="main">
        <?php include 'includes/sidebar.php';?>
    <div class="main--content">
    <div id="messageDiv" class="messageDiv"  style="display:none;" > </div>

    <form class="lecture-options" id="selectForm">
    <select required name="course" id="courseSelect"  onChange="updateTable()">
        <option value="" selected>Select Course</option>
        <?php
        $courseNames = getCourseNames($conn);
        foreach ($courseNames as $course) {
            echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
        }
        ?>
    </select>

    <select required name="unit" id="unitSelect" onChange="updateTable()">
        <option value="" selected>Select Unit</option>
        <?php
        $unitNames = getUnitNames($conn);
        foreach ($unitNames as $unit) {
            echo '<option value="' . $unit["unitCode"] . '">' . $unit["name"] . '</option>';
        }
        ?>
    </select>
    
    <select required name="venue" id="venueSelect" onChange="updateTable()">
        <option value="" selected>Select Venue</option>
        <?php
        $venueNames = getVenueNames($conn);
        foreach ($venueNames as $venue) {
            echo '<option value="' . $venue["className"] . '">' . $venue["className"] . '</option>';
        }
        ?>
    </select>
   
    </form>
    <div class="attendance-button">
      <button id="startButton" class="add" >Launch Facial Recognition</button>
      <button id="endButton"class="add" style="display:none">End Attendance Process</button>
      <button id="endAttendance" class="add" >END Attendance Taking</button>
    </div>
   
    <div class="video-container" style="display:none;">
        <video  id="video" width="600" height="450" autoplay></video>
        <canvas id="overlay"></canvas>
    </div>

    <div class="table-container">

                <div id="studentTableContainer" >
               

                    
                </div>
                
    </div>

</div>
</section>
    <script>

 </script>
   
<script>


    
var labels = [];
let detectedFaces = [];
let sendingData = false; 

function updateTable() {
    var selectedCourseID = document.getElementById('courseSelect').value;
    var selectedUnitCode = document.getElementById('unitSelect').value;
    var selectedVenue = document.getElementById("venueSelect").value;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'manageFolder.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                labels = response.data;

                if (selectedCourseID && selectedUnitCode && selectedVenue) {
                  updateOtherElements();
                }             
         document.getElementById('studentTableContainer').innerHTML = response.html;
            } else {
                console.error('Error:', response.message);
            }
        }

    };
    xhr.send('courseID=' + encodeURIComponent(selectedCourseID) +
    '&unitID=' + encodeURIComponent(selectedUnitCode) +
    '&venueID=' + encodeURIComponent(selectedVenue))
    
    ;
    }

    function markAttendance(detectedFaces) {
        document.querySelectorAll('#studentTableContainer tr').forEach(row => {
            const registrationNumber = row.cells[0].innerText.trim();
            if (detectedFaces.includes(registrationNumber)) {
                row.cells[5].innerText = 'present';
            }
        });
    }

   

    


function updateOtherElements(){
   
const video = document.getElementById("video");
const videoContainer = document.querySelector(".video-container");
const startButton = document.getElementById("startButton");
let webcamStarted = false;
let modelsLoaded = false;


Promise.all([
  faceapi.nets.ssdMobilenetv1.loadFromUri("/weights"),
  faceapi.nets.faceRecognitionNet.loadFromUri("/weights"),
  faceapi.nets.faceLandmark68Net.loadFromUri("/weights"),
]).then(() => {
  modelsLoaded = true;
});
startButton.addEventListener("click", async () => {
    videoContainer.style.display="flex";
  if (!webcamStarted && modelsLoaded) {
      startWebcam();
      webcamStarted = true;
  }
});




function startWebcam() {
  navigator.mediaDevices
      .getUserMedia({
          video: true,
          audio: false,
      })
      .then((stream) => {
          video.srcObject = stream;
          videoStream = stream; 
      })
      .catch((error) => {
          console.error(error);
      });

}
async function getLabeledFaceDescriptions() {
    const labeledDescriptors = [];

    for (const label of labels) {
        const descriptions = [];

        for (let i = 1; i <= 2; i++) {
            try {
                const img = await faceapi.fetchImage(`./labels/${label}/${i}.png`);
                const detections = await faceapi
                    .detectSingleFace(img)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (detections) {
                    descriptions.push(detections.descriptor);
                } else {
                    console.log(`No face detected in ${label}/${i}.png`);
                }
            } catch (error) {
                console.error(`Error processing ${label}/${i}.png:`, error);
            }
        }

        if (descriptions.length > 0) {
            detectedFaces.push(label);
            labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(label, descriptions));
        }
    }

    return labeledDescriptors;
}


video.addEventListener("play", async () => {
    const labeledFaceDescriptors = await getLabeledFaceDescriptions();
    const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors);

    const canvas = faceapi.createCanvasFromMedia(video);
    videoContainer.appendChild(canvas);

    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {
        const detections = await faceapi
            .detectAllFaces(video)
            .withFaceLandmarks()
            .withFaceDescriptors();

        const resizedDetections = faceapi.resizeResults(detections, displaySize);

        canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

        const results = resizedDetections.map((d) => {
            return faceMatcher.findBestMatch(d.descriptor);
        });
        detectedFaces = results.map(result => result.label);
        markAttendance(detectedFaces);

            results.forEach((result, i) => {
            const box = resizedDetections[i].detection.box;
            const drawBox = new faceapi.draw.DrawBox(box, {
                label: result,
            });
            drawBox.draw(canvas);
        });
    }, 100);
})};



function sendAttendanceDataToServer() {
    const attendanceData = [];

    document.querySelectorAll('#studentTableContainer tr').forEach((row, index) => {
        if (index === 0) return; 
        const studentID = row.cells[0].innerText.trim(); 
        const course = row.cells[2].innerText.trim();
        const unit = row.cells[3].innerText.trim();
        const attendanceStatus = row.cells[5].innerText.trim(); 

        attendanceData.push({ studentID,course,unit,attendanceStatus });
    });

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'takeAttendance.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                showMessage('Attendance recorded successfully.');
            } else {
                showMessage('Error: Unable to record attendance.');
            }
        }
    };

    xhr.send(JSON.stringify(attendanceData));
}
function showMessage(message) {
    var messageDiv = document.getElementById('messageDiv');
    messageDiv.style.display="block";
    messageDiv.innerHTML = message;
    console.log(message);
    messageDiv.style.opacity = 1;
    setTimeout(function() {
      messageDiv.style.opacity = 0;
    }, 5000);
  }
function stopWebcam() {
    if (videoStream) {
        const tracks = videoStream.getTracks();

        tracks.forEach((track) => {
            track.stop();
        });

        video.srcObject = null;
        videoStream = null;
    }
}

document.getElementById("endAttendance").addEventListener("click", function() {
    sendAttendanceDataToServer();
    const videoContainer = document.querySelector(".video-container");
     videoContainer.style.display="none";
    stopWebcam();

});

</script>
<script  src="javascript/main.js"></script>





</body>
</html>
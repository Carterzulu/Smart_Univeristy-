<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendancefacerec";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("message" => "Database connection failed: " . $conn->connect_error)));
}

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'];

$image = $data['image'];

// Decode the base64 image
$image_parts = explode(";base64,", $image);
$image_base64 = base64_decode($image_parts[1]);

// Store the image in the database
$stmt = $conn->prepare("INSERT INTO students (name,face_data) VALUES (?,?)");

// Bind the parameters: name is a string, and image_base64 is a BLOB
$stmt->bind_param("sb", $name, $null); // Use a placeholder ($null) for the blob

// Send the binary data (image) using send_long_data
$stmt->send_long_data(1, $image_base64); // The second parameter (1) is the index for the blob


// $stmt->bind_param("sb", $name, $image_base64);

if ($stmt->execute()) {
    echo json_encode(array("message" => "Student registered successfully."));
} else {
    echo json_encode(array("message" => "Error occurred during registration."));
}

$stmt->close();
$conn->close();
?>

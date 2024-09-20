from flask import Flask, request, jsonify
import cv2
import numpy as np
import base64
import mysql.connector
from datetime import datetime
from flask_cors import CORS

app = Flask(__name__)

# Enable CORS for all routes
CORS(app, resources={r"/*": {"origins": "*"}})

# Database connection
def get_db_connection():
    try:
        return mysql.connector.connect(
            host="localhost",
            user="root",
            password="",  # Ensure this is the correct password for your MySQL user
            database="attendancefacerec"
        )
    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
        return None

# Convert base64 image to OpenCV image
def base64_to_image(base64_string):
    try:
        img_data = base64.b64decode(base64_string.split(',')[1])
        np_array = np.frombuffer(img_data, np.uint8)
        img = cv2.imdecode(np_array, cv2.IMREAD_COLOR)
        return img
    except Exception as e:
        print(f"Error decoding image: {e}")
        return None

# Perform facial recognition
def recognize_face(captured_image):
    try:
        conn = get_db_connection()
        if conn is None:
            return None  # Return if there's a DB connection error
        
        cursor = conn.cursor()
        cursor.execute("SELECT id, face_data FROM students")
        students = cursor.fetchall()
        conn.close()

        for id, face_data in students:
            # Decode the stored face
            np_array = np.frombuffer(face_data, np.uint8)
            stored_face = cv2.imdecode(np_array, cv2.IMREAD_COLOR)

            # Compare the captured image with the stored face
            if compare_faces(captured_image, stored_face):
                return id

        return None
    except Exception as e:
        print(f"Error recognizing face: {e}")
        return None

# Compare two faces (placeholder function)
def compare_faces(face1, face2):
    try:
        face1_resized = cv2.resize(face1, (200, 200))
        face2_resized = cv2.resize(face2, (200, 200))

        diff = cv2.absdiff(face1_resized, face2_resized)
        non_zero_count = np.count_nonzero(diff)

        return non_zero_count < 1000  # Adjust threshold for better accuracy
    except Exception as e:
        print(f"Error comparing faces: {e}")
        return False

# Mark attendance
def mark_attendance(id):
    try:
        conn = get_db_connection()
        if conn is None:
            print("Error: Unable to connect to database")
            return

        cursor = conn.cursor()

        current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        cursor.execute("INSERT INTO attendance (student_id, timestamp) VALUES (%s, %s)", (student_id, current_time))

        conn.commit()
        conn.close()
    except Exception as e:
        print(f"Error marking attendance: {e}")

# Route for marking attendance
@app.route('/app', methods=['POST'])
def mark_attendance_route():
    try:
        data = request.get_json()
        if 'image' not in data:
            return jsonify({'message': 'No image provided'}), 400

        captured_image = base64_to_image(data['image'])
        if captured_image is None:
            return jsonify({'message': 'Error processing image'}), 400

        id = recognize_face(captured_image)

        if id is None:
            return jsonify({'message': 'Face not recognized. Attendance not marked.'}), 404

        mark_attendance(id)
        return jsonify({'message': f'Attendance marked for student ID {student_id}'})

    except Exception as e:
        print(f"Error in /app route: {e}")
        return jsonify({'message': 'Server error occurred'}), 500

# Home route to prevent 404 on root URL
@app.route('/')
def home():
    return "Welcome to the Attendance System API!"

if __name__ == '__main__':
    app.run(debug=False)  # Set to True for development, False for production

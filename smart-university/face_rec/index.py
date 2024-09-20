from flask import Flask, request, jsonify
import cv2
import numpy as np
import base64
import mysql.connector

app = Flask(__name__)

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="your_username",
        password="your_password",
        database="your_database"
    )

# Convert base64 image to OpenCV image
def base64_to_image(base64_string):
    img_data = base64.b64decode(base64_string.split(',')[1])
    np_array = np.frombuffer(img_data, np.uint8)
    img = cv2.imdecode(np_array, cv2.IMREAD_COLOR)
    return img

# Fetch saved image from database
def get_saved_image():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT image_data FROM images WHERE id = 1")  # Fetch image with ID 1
    result = cursor.fetchone()
    conn.close()

    if result:
        img_data = result[0]
        np_array = np.frombuffer(img_data, np.uint8)
        saved_image = cv2.imdecode(np_array, cv2.IMREAD_COLOR)
        return saved_image
    else:
        return None

# Compare two images using OpenCV
def compare_images(img1, img2):
    # Resize images to the same size for comparison
    img1_resized = cv2.resize(img1, (200, 200))
    img2_resized = cv2.resize(img2, (200, 200))

    # Compute the difference between the two images
    diff = cv2.absdiff(img1_resized, img2_resized)
    non_zero_count = np.count_nonzero(diff)

    return non_zero_count == 0  # Return True if images are identical

@app.route('/compare', methods=['POST'])
def compare():
    data = request.get_json()
    if 'image' not in data:
        return jsonify({'message': 'No image provided'}), 400

    captured_image = base64_to_image(data['image'])
    saved_image = get_saved_image()

    if saved_image is None:
        return jsonify({'message': 'Saved image not found in the database'}), 404

    if compare_images(captured_image, saved_image):
        return jsonify({'message': 'Images match!'})
    else:
        return jsonify({'message': 'Images do not match.'})

if __name__ == '__main__':
    app.run(debug=True)

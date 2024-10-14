<?php
session_start();
include 'config.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file was uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $album_id = $_POST['album_id'];
        $file_name = $_FILES['photo']['name'];
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        $file_error = $_FILES['photo']['error'];

        // Validate file size (5MB max)
        if ($file_size > 5 * 1024 * 1024) {
            die("File size exceeds the limit of 5MB.");
        }

        // Validate file type (only allow images)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file_tmp);
        if (!in_array($file_type, $allowed_types)) {
            die("Only JPG, PNG, and GIF files are allowed.");
        }

        // Define a target directory
        $target_dir = "uploads/"; // Ensure this directory exists and is writable
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Move the uploaded file to the target directory
        $target_file = $target_dir . basename($file_name);
        if (move_uploaded_file($file_tmp, $target_file)) {
            // Prepare the SQL query to insert the photo into the database
            $stmt = $conn->prepare("INSERT INTO foto (AlbumID, FileName, UploadDate) VALUES (?, ?, NOW())");
            if ($stmt) { // Check if the statement prepared successfully
                $stmt->bind_param("is", $album_id, $file_name);
                
                // Execute the statement and check for errors
                if ($stmt->execute()) {
                    echo "Photo uploaded successfully.";
                } else {
                    echo "Error uploading photo: " . $stmt->error; // Use $stmt->error instead of $conn->error
                }

                $stmt->close();
            } else {
                echo "Error preparing the statement: " . $conn->error; // Handle statement preparation error
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}

// Close the database connection
$conn->close();
?>

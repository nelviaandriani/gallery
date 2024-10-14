<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['Level'])) {
    header('Location: login.php');
    exit();
}

// Check if the photo ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fotoID = $_GET['id'];

    // Fetch the photo data
    $query = "SELECT * FROM foto WHERE FotoID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $fotoID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Check if the form is submitted
        if (isset($_POST['submit'])) {
            $judulFoto = $_POST['judulFoto'];
            $deskripsiFoto = $_POST['deskripsiFoto'];

            // Handle the file upload
            if ($_FILES['fotoFile']['error'] == UPLOAD_ERR_OK) {
                // Set the upload directory
                $uploadDir = 'images/'; // Make sure this directory exists and is writable
                $fileName = basename($_FILES['fotoFile']['name']);
                $targetFilePath = $uploadDir . $fileName;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['fotoFile']['tmp_name'], $targetFilePath)) {
                    // Update the photo data in the database with the new file path
                    $updateQuery = "UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ?, LokasiFile = ? WHERE FotoID = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("sssi", $judulFoto, $deskripsiFoto, $targetFilePath, $fotoID);

                    if ($stmt->execute()) {
                        header('Location: viewfoto.php?id=' . $fotoID); // Redirect to the view photo page
                        exit();
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                } else {
                    echo "Error uploading file.";
                }
            } else {
                // If no new file is uploaded, just update the title and description
                $updateQuery = "UPDATE foto SET JudulFoto = ?, DeskripsiFoto = ? WHERE FotoID = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssi", $judulFoto, $deskripsiFoto, $fotoID);

                if ($stmt->execute()) {
                    header('Location: viewfoto.php?id=' . $fotoID); // Redirect to the view photo page
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    } else {
        echo "Photo not found.";
        exit();
    }
} else {
    echo "Invalid photo ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Photo</title>
</head>
<body>
    <h2>Edit Photo</h2>
    <form method="POST" enctype="multipart/form-data"> <!-- Added enctype for file upload -->
        <label for="judulFoto">Title:</label>
        <input type="text" name="judulFoto" id="judulFoto" value="<?= htmlspecialchars($row['JudulFoto']) ?>" required>
        <br>
        
        <label for="deskripsiFoto">Description:</label>
        <textarea name="deskripsiFoto" id="deskripsiFoto" required><?= htmlspecialchars($row['DeskripsiFoto']) ?></textarea>
        <br>

        <label for="fotoFile">Upload New Photo (optional):</label>
        <input type="file" name="fotoFile" id="fotoFile">
        <br>

        <button type="submit" name="submit">Update Photo</button>
    </form>
</body>
</html>
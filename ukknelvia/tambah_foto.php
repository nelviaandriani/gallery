<?php
session_start();
include 'config.php';

// Cek apakah ID album disediakan dan valid
if (isset($_GET['album_id']) && is_numeric($_GET['album_id'])) {
    $albumID = $_GET['album_id'];
} else {
    echo "Invalid album ID.";
    exit();
}

// Cek apakah form telah disubmit
if (isset($_POST['submit'])) {
    $JudulFoto = mysqli_real_escape_string($conn, $_POST['JudulFoto']);
    $DeskripsiFoto = mysqli_real_escape_string($conn, $_POST['DeskripsiFoto']);
    $TanggalUnggah = date('Y-m-d'); // Tanggal saat ini
    $UserID = $_SESSION['UserID']; // Ambil UserID dari session

    // Proses upload file
    $targetDir = "images/";
    $fileName = basename($_FILES['LokasiFile']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $fotoTmp = $_FILES['LokasiFile']['tmp_name']; // File temporary

    // Cek apakah file merupakan gambar valid
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        // Upload file
        if (move_uploaded_file($fotoTmp, $targetFilePath)) {
            // Query untuk menyimpan detail foto ke database
            $stmt = $conn->prepare("
                INSERT INTO foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $JudulFoto, $DeskripsiFoto, $TanggalUnggah, $targetFilePath, $albumID, $UserID);
            
            if ($stmt->execute()) {
                // Jika berhasil, arahkan kembali ke halaman album
                header("Location: lihat_album.php?album_id=" . $albumID);
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "File upload failed.";
        }
    } else {
        echo "Only JPG, JPEG, PNG, and GIF files are allowed.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto</title>
    <link rel="stylesheet" href="tambah_foto.css"> <!-- Link to CSS -->
</head>
<body>
    <h1>Tambah Foto</h1>
    <form action="tambah_foto.php?album_id=<?= $albumID ?>" method="POST" enctype="multipart/form-data">
        <label for="JudulFoto">Photo Title:</label>
        <input type="text" name="JudulFoto" id="JudulFoto" required>

        <label for="DeskripsiFoto">Description:</label>
        <textarea name="DeskripsiFoto" id="DeskripsiFoto" required></textarea>

        <label for="LokasiFile">Choose Photo:</label>
        <input type="file" name="LokasiFile" id="LokasiFile" accept=".jpg, .jpeg, .png, .gif" required>

        <button type="submit" name="submit">Upload Photo</button>
    </form>
</body>
</html>
<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['Level'])) {
    header('Location: login.php');
    exit();
}

// Check if the album ID is provided and is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $albumID = $_GET['id'];

    // Fetch the album data
    $query = "SELECT * FROM album WHERE AlbumID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $albumID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        echo "Album not found.";
        exit();
    }
} else {
    echo "Invalid album ID.";
    exit();
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $namaAlbum = mysqli_real_escape_string($conn, $_POST['namaAlbum']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tanggalDibuat = $_POST['tanggalDibuat']; // New date input

    // Update the text fields only (no photo update)
    $updateQuery = "UPDATE album SET NamaAlbum = ?, Deskripsi = ?, TanggalDibuat = ? WHERE AlbumID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssi", $namaAlbum, $deskripsi, $tanggalDibuat, $albumID);

    if ($stmt->execute()) {
        header('Location: index.php'); // Redirect to the home page after update
        exit();
    } else {
        echo "Error: " . $stmt->error; // Display error message if the update fails
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album</title>
    <link rel="stylesheet" href="edit_album.css"> <!-- Link to your CSS file -->
</head>
<body>
    <h2>Edit Album</h2>
    <form method="POST"> <!-- Form without enctype since no file upload -->
        <label for="namaAlbum">Nama Album:</label>
        <input type="text" name="namaAlbum" value="<?= htmlspecialchars($row['NamaAlbum']) ?>" required>
        
        <label for="deskripsi">Deskripsi:</label>
        <textarea name="deskripsi" required><?= htmlspecialchars($row['Deskripsi']) ?></textarea>
        
        <label for="tanggalDibuat">Tanggal Dibuat:</label>
        <input type="date" name="tanggalDibuat" value="<?= htmlspecialchars($row['TanggalDibuat']) ?>" required>

        <button type="submit" name="submit">Update Album</button>
    </form>
    <br>
    <a href="index.php" style="text-decoration: none;">
        <button class="back-button">Kembali ke Beranda</button>
    </a>
</body>
</html>
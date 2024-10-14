<?php
session_start();
include 'config.php';

// Cek apakah form telah di-submit
if (isset($_POST['submit'])) {
    $NamaAlbum = mysqli_real_escape_string($conn, $_POST['NamaAlbum']);
    $Deskripsi = mysqli_real_escape_string($conn, $_POST['Deskripsi']);
    $TanggalDibuat = date('Y-m-d'); // Tanggal saat ini
    $UserID = $_SESSION['UserID']; // Ambil ID pengguna dari session

    // Query untuk menambahkan album baru
    $stmt = $conn->prepare("
        INSERT INTO album (NamaAlbum, Deskripsi, TanggalDibuat, UserID) 
        VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $NamaAlbum, $Deskripsi, $TanggalDibuat, $UserID);

    if ($stmt->execute()) {
        // Dapatkan AlbumID dari album yang baru saja ditambahkan
        $albumID = $stmt->insert_id;
        // Setelah berhasil, arahkan ke halaman tambah foto dengan AlbumID
        header("Location: tambah_foto.php?album_id=" . $albumID);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Tutup statement
    $stmt->close();
}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Album</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link ke file CSS Anda -->
</head>
<body>
    <h1>Tambah Album Baru</h1>
    <form action="tambah_album.php" method="POST">
        <label for="NamaAlbum">Nama Album:</label>
        <input type="text" name="NamaAlbum" id="NamaAlbum" required>

        <label for="Deskripsi">Deskripsi:</label>
        <textarea name="Deskripsi" id="Deskripsi" required></textarea>

        <button type="submit" name="submit">Tambah Album</button>
    </form>
</body>
</html>
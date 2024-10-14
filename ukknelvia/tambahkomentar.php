<?php
session_start();
include 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['UserID'])) {
    echo "You must be logged in to perform this action.";
    exit();
}

$userID = $_SESSION['UserID']; // ID pengguna yang sedang login

// Cek apakah FotoID dan isi komentar diberikan
if (isset($_POST['foto_id']) && is_numeric($_POST['foto_id']) && isset($_POST['isi_komentar']) && !empty($_POST['isi_komentar'])) {
    $fotoID = $_POST['foto_id'];
    $isiKomentar = htmlspecialchars($_POST['isi_komentar']);
    $username = $_SESSION['Username']; // Ambil nama pengguna dari sesi

    // Buat tanggal komentar manual menggunakan PHP
    $tanggalKomentar = date('Y-m-d H:i:s'); // Format YYYY-MM-DD HH:MM:SS

    // Query untuk menambahkan komentar
    $addCommentQuery = "INSERT INTO komentarfoto (FotoID, UserID, Username, IsiKomentar, TanggalKomentar) VALUES (?, ?, ?, ?, ?)";
    $addCommentStmt = $conn->prepare($addCommentQuery);
    $addCommentStmt->bind_param("iisss", $fotoID, $userID, $username, $isiKomentar, $tanggalKomentar);

    if ($addCommentStmt->execute()) {
        // Jika komentar berhasil ditambahkan, arahkan kembali ke halaman foto
        header("Location: viewfoto.php?id=$fotoID");
        exit();
    } else {
        echo "Error adding comment: " . $conn->error;
    }
} else {
    echo "Invalid input.";
    exit();
}

// Tutup koneksi
$addCommentStmt->close();
$conn->close();
?>

<?php
session_start();
include 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['UserID'])) {
    echo "You must be logged in to perform this action.";
    exit();
}

$userID = $_SESSION['UserID'];

// Cek apakah ID foto diberikan
if (isset($_POST['foto_id']) && is_numeric($_POST['foto_id'])) {
    $fotoID = $_POST['foto_id'];

    if ($_POST['action'] == 'like') {
        // Tambahkan like
        $likeQuery = "INSERT INTO likefoto (FotoID, UserID) VALUES (?, ?)";
        $stmt = $conn->prepare($likeQuery);
        $stmt->bind_param("ii", $fotoID, $userID);
        $stmt->execute();
    } elseif ($_POST['action'] == 'unlike') {
        // Hapus like
        $unlikeQuery = "DELETE FROM likefoto WHERE FotoID = ? AND UserID = ?";
        $stmt = $conn->prepare($unlikeQuery);
        $stmt->bind_param("ii", $fotoID, $userID);
        $stmt->execute();
    }
}

// Kembali ke halaman sebelumnya
$albumID = $_GET['album_id'];
header("Location: viewfoto.php?album_id=$albumID");
exit();
?>
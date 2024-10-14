<?php
session_start();
include 'config.php'; // Pastikan config.php memiliki koneksi yang valid

// Inisialisasi variabel
$fotoID = null;
$albumID = null;

// Cek apakah ID foto dan album diberikan
if (isset($_GET['id']) && isset($_GET['album_id'])) {
    $fotoID = $_GET['id'];
    $albumID = $_GET['album_id'];

    // Validasi apakah ID foto dan album berupa angka
    if (!is_numeric($fotoID) || !is_numeric($albumID)) {
        $_SESSION['message'] = "ID foto atau album tidak valid.";
        header("Location: lihat_foto.php?album_id=" . htmlspecialchars($albumID));
        exit();
    }

    // Query untuk menghapus foto
    $query = "DELETE FROM foto WHERE FotoID = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $fotoID); // Ikat parameter dengan tipe integer
        $stmt->execute();

        // Periksa apakah ada baris yang terpengaruh
        if ($stmt->affected_rows > 0) {
            // Foto berhasil dihapus
            $_SESSION['message'] = "Foto berhasil dihapus.";
        } else {
            // Gagal menghapus foto
            $_SESSION['message'] = "Gagal menghapus foto. Foto tidak ditemukan.";
        }

        $stmt->close(); // Tutup statement
    } else {
        // Terjadi kesalahan pada query
        $_SESSION['message'] = "Terjadi kesalahan saat mempersiapkan query: " . $conn->error;
    }
} else {
    // Jika parameter tidak diberikan
    $_SESSION['message'] = "ID foto atau album tidak diberikan.";
}

// Redirect kembali ke halaman album
header("Location: lihat_foto.php?album_id=" . htmlspecialchars($albumID));
exit();

// Tutup koneksi database
$conn->close();
?>

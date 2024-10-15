<?php
$servername = "localhost"; // Sesuaikan dengan konfigurasi server Anda
$username = "root";        // Username database
$password = "";            // Password database
$dbname = "gallery";       // Nama database

// Membuat koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

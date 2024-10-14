<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

include 'config.php'; // Sertakan file konfigurasi database

// Mengambil detail pengguna yang login
$userID = $_SESSION['UserID'];
$userLevel = $_SESSION['Level']; // Ambil level pengguna (Admin atau User)

// Inisialisasi variabel untuk pencarian
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = trim($_POST['search']);
}

// Query untuk mengambil semua album
if ($searchTerm) {
    // Pencarian berdasarkan nama album atau deskripsi untuk semua album
    $query = "SELECT * FROM album WHERE (NamaAlbum LIKE ? OR Deskripsi LIKE ?) GROUP BY AlbumID";
    $likeTerm = '%' . $searchTerm . '%'; // Persiapkan wildcard untuk pencarian
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $likeTerm, $likeTerm);
} else {
    // Mengambil semua album tanpa filter UserID
    $query = "SELECT * FROM album GROUP BY AlbumID";
    $stmt = $conn->prepare($query);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Album</title>
    <link rel="stylesheet" href="index.css">
    <style>
        /* Tambahkan styling sesuai kebutuhan */
    </style>
</head>
<body>
    <header>
        <h1>Selamat datang <?= htmlspecialchars($_SESSION['UserName'] ?? '') ?>!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Album</a></li>
                <li><a href="tambah_album.php">Tambah Album</a></li>
                <?php if ($userLevel === 'Admin'): // Hanya tampilkan menu Admin jika level adalah Admin ?>
                    <li><a href="admin.php">Album Admin</a></li>
                   
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div>
            <!-- Form Pencarian -->
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Cari album..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="album-list">
            <h2>Semua Album</h2>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="album-item">
                        <h3><?= htmlspecialchars($row['NamaAlbum']) ?></h3>
                        <p><?= htmlspecialchars($row['Deskripsi']) ?></p>
                        <p><small>Dibuat pada: <?= htmlspecialchars($row['TanggalDibuat']) ?></small></p>
                        <a href="lihat_foto.php?album_id=<?= htmlspecialchars($row['AlbumID']) ?>">Lihat Foto</a>

                        <!-- User dan Admin bisa mengedit album -->
                        <a href="edit_album.php?id=<?= htmlspecialchars($row['AlbumID']) ?>">Edit Album</a>

                        <!-- Hanya Admin bisa menghapus album -->
                        <?php if ($userLevel == 'Admin'): ?>
                            <a href="delete_album.php?id=<?= htmlspecialchars($row['AlbumID']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus album ini?');">Hapus Album</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada album yang ditemukan. Anda bisa <a href="tambah_album.php">menambahkan album</a>.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

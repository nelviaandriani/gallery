<?php
session_start();
include 'config.php';

// Cek apakah ID album diberikan
if (isset($_GET['album_id'])) {
    $albumID = $_GET['album_id'];
    if (!is_numeric($albumID)) {
        echo "ID album tidak valid. Nilai yang diberikan: " . htmlspecialchars($albumID);
        exit();
    }

    // Ambil foto untuk album yang ditentukan
    $query = "SELECT * FROM foto WHERE AlbumID = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $albumID);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        echo "Terjadi kesalahan saat mempersiapkan pernyataan.";
        exit();
    }
} else {
    echo "ID album tidak diberikan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foto dalam Album</title>
    <link rel="stylesheet" href="lihat_foto.css">
    <style>
        .photo-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        .photo-details {
            flex-basis: 100%;
        }
        .back-home {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-home:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <h1>Foto dalam Album</h1>
    <a class="add-photo-button" href="tambah_foto.php?album_id=<?= htmlspecialchars($albumID) ?>">Tambah Foto Baru</a>
    
    <!-- Tambahkan tombol Kembali ke Beranda -->
    <a class="back-home" href="index.php">Kembali ke Beranda</a>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="photo-container">
                <!-- Photo details -->
                <div class="photo-details">
                    <h3><?= htmlspecialchars($row['JudulFoto']) ?></h3>
                    <p><?= htmlspecialchars($row['DeskripsiFoto']) ?></p>
                    <img src="<?= htmlspecialchars($row['LokasiFile']) ?>" alt="<?= htmlspecialchars($row['JudulFoto']) ?>" width="200">

                    <div class="photo-actions">
                        <a href="viewfoto.php?id=<?= $row['FotoID'] ?>">Lihat Detail</a> |
                        <a href="edit_foto.php?id=<?= $row['FotoID'] ?>">Edit Foto</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Tidak ada foto ditemukan dalam album ini.</p>
    <?php endif; ?>
</body>
</html>

<?php
// Tutup pernyataan dan koneksi
$stmt->close();
$conn->close();
?>
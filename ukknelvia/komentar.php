<?php
session_start();
include 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah Foto ID disediakan
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fotoID = $_GET['id'];

    // Ambil komentar untuk foto tersebut, ambil juga tanggal komentar
    $query = "SELECT UserID, Komentar, likes, DATE_FORMAT(TanggalKomentar, '%d %M %Y') AS TanggalKomentarTerformat FROM komentar WHERE FotoID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $fotoID);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "ID foto tidak valid.";
    exit();
}

// Tambahkan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $komentar = $_POST['komentar'];
    $userID = $_SESSION['UserID'];
    
    $insertQuery = "INSERT INTO komentar (FotoID, UserID, Komentar) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iis", $fotoID, $userID, $komentar);
    
    if ($stmt->execute()) {
        header('Location: komentar.php?id=' . $fotoID);
        exit();
    } else {
        echo "Terjadi kesalahan saat menambahkan komentar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentar Foto</title>
</head>
<body>
    <h2>Komentar untuk Foto</h2>
    <form action="komentar.php?id=<?= $fotoID ?>" method="POST">
        <textarea name="komentar" placeholder="Tambahkan komentar..." required></textarea>
        <br>
        <button type="submit">Kirim Komentar</button>
    </form>
    
    <h3>Komentar:</h3>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div>
                <p><strong><?= htmlspecialchars($row['UserID']) ?>:</strong> <?= htmlspecialchars($row['Komentar']) ?></p>
                <p>Suka: <?= $row['likes'] ?> <a href="like.php?komentar_id=<?= $row['KomentarID'] ?>">Sukai</a></p>
                <!-- Tampilkan tanggal upload komentar -->
                <p><small>Diunggah pada: <?= htmlspecialchars($row['TanggalKomentarTerformat']) ?></small></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Belum ada komentar.</p>
    <?php endif; ?>
</body>
</html>

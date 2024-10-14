<?php
session_start();
include 'config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['UserID'])) {
    echo "You must be logged in to view this page.";
    exit();
}

$userID = $_SESSION['UserID']; // ID pengguna yang sedang login

// Cek apakah FotoID diberikan
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fotoID = $_GET['id'];

    // Ambil detail foto berdasarkan FotoID dengan format tanggal
    $query = "SELECT FotoID, JudulFoto, DeskripsiFoto, LokasiFile, AlbumID, DATE_FORMAT(TanggalUnggah, '%d %M %Y') AS TanggalUnggah FROM foto WHERE FotoID = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $fotoID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Cek apakah pengguna sudah like foto ini
            $likeCheckQuery = "SELECT * FROM likefoto WHERE FotoID = ? AND UserID = ?";
            $likeStmt = $conn->prepare($likeCheckQuery);
            $likeStmt->bind_param("ii", $fotoID, $userID);
            $likeStmt->execute();
            $likeResult = $likeStmt->get_result();
            $userLiked = $likeResult->num_rows > 0;

            // Hitung total likes pada foto ini
            $likeCountQuery = "SELECT COUNT(*) as totalLikes FROM likefoto WHERE FotoID = ?";
            $likeCountStmt = $conn->prepare($likeCountQuery);
            $likeCountStmt->bind_param("i", $fotoID);
            $likeCountStmt->execute();
            $likeCountResult = $likeCountStmt->get_result();
            $totalLikes = $likeCountResult->fetch_assoc()['totalLikes'];
        } else {
            echo "Foto tidak ditemukan.";
            exit();
        }
    } else {
        echo "Error preparing statement.";
        exit();
    }
} else {
    echo "ID foto tidak valid.";
    exit();
}

// Tangani aksi like/unlike
if (isset($_POST['like'])) {
    if ($userLiked) {
        // Jika sudah like, lakukan unlike
        $unlikeQuery = "DELETE FROM likefoto WHERE FotoID = ? AND UserID = ?";
        $unlikeStmt = $conn->prepare($unlikeQuery);
        $unlikeStmt->bind_param("ii", $fotoID, $userID);
        $unlikeStmt->execute();
    } else {
        // Jika belum like, tambahkan like
        $likeQuery = "INSERT INTO likefoto (FotoID, UserID) VALUES (?, ?)";
        $likeStmt = $conn->prepare($likeQuery);
        $likeStmt->bind_param("ii", $fotoID, $userID);
        $likeStmt->execute();
    }
    // Refresh halaman setelah like/unlike
    header("Location: viewfoto.php?id=$fotoID");
    exit();
}

// Tangani pengiriman komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_komentar'])) {
    $komentar = trim($_POST['komentar']);

    // Pastikan komentar tidak kosong
    if (!empty($komentar)) {
        $tanggalKomentar = date('Y-m-d H:i:s'); // Format tanggal secara manual

        $komentarQuery = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES (?, ?, ?, ?)";
        $komentarStmt = $conn->prepare($komentarQuery);
        $komentarStmt->bind_param("iiss", $fotoID, $userID, $komentar, $tanggalKomentar);
        $komentarStmt->execute();
        $komentarStmt->close(); // Tutup statement komentar

        // Refresh halaman setelah komentar ditambahkan
        header("Location: viewfoto.php?id=$fotoID");
        exit();
    }
}

// Ambil semua komentar untuk foto ini
$komentarQuery = "SELECT k.*, u.Username FROM komentarfoto k JOIN user u ON k.UserID = u.UserID WHERE k.FotoID = ? ORDER BY k.TanggalKomentar DESC";
$komentarStmt = $conn->prepare($komentarQuery);
$komentarStmt->bind_param("i", $fotoID);
$komentarStmt->execute();
$komentarResult = $komentarStmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($row['JudulFoto']) ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional: Link ke file CSS -->
    <script>
        function printPhoto(photoSrc, photoTitle) {
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Cetak Foto</title>');
            printWindow.document.write('<style>body { text-align: center; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h1>' + photoTitle + '</h1>');
            printWindow.document.write('<img src="' + photoSrc + '" style="max-width: 100%; height: auto;" />');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</head>
<body>
    <h1><?= htmlspecialchars($row['JudulFoto']) ?></h1>
    <p><strong>Deskripsi:</strong> <?= htmlspecialchars($row['DeskripsiFoto']) ?></p>
    <p><strong>Tanggal Unggah:</strong> <?= htmlspecialchars($row['TanggalUnggah']) ?></p> <!-- Menampilkan tanggal unggah -->
    <img src="<?= htmlspecialchars($row['LokasiFile']) ?>" alt="<?= htmlspecialchars($row['JudulFoto']) ?>" width="400">

    <div>
        <a href="edit_foto.php?id=<?= $row['FotoID'] ?>">Edit Foto</a> <!-- Link untuk mengedit foto -->
    </div>

    <!-- Menampilkan total likes -->
    <p><strong>Total Likes:</strong> <?= $totalLikes ?></p>

    <!-- Tombol like/unlike dan print -->
    <form method="post">
        <?php if ($userLiked): ?>
            <button type="submit" name="like">Unlike</button>
        <?php else: ?>
            <button type="submit" name="like">Like</button>
        <?php endif; ?>
        <button type="button" onclick="printPhoto('<?= htmlspecialchars($row['LokasiFile']) ?>', '<?= htmlspecialchars($row['JudulFoto']) ?>')">Print Foto</button> <!-- Tombol Print Foto -->
    </form>

    <!-- Kembali ke halaman album -->
    <a href="lihat_foto.php?album_id=<?= $row['AlbumID'] ?>">Kembali ke Album</a>

    <!-- Bagian Komentar -->
    <div class="comments-section">
        <h2>Komentar</h2>

        <!-- Form untuk mengirim komentar -->
        <form method="post">
            <textarea name="komentar" rows="4" placeholder="Tulis komentar Anda di sini..." required></textarea><br>
            <button type="submit" name="submit_komentar">Kirim Komentar</button>
        </form>

        <!-- Tampilkan komentar -->
        <?php if ($komentarResult->num_rows > 0): ?>
            <ul>
                <?php while ($komentar = $komentarResult->fetch_assoc()): ?>
                    <li>
                        <strong><?= htmlspecialchars($komentar['Username']) ?>:</strong> <?= htmlspecialchars($komentar['IsiKomentar']) ?>
                        <br><small><?= htmlspecialchars($komentar['TanggalKomentar']) ?></small> <!-- Tanggal komentar -->
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Belum ada komentar.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
// Tutup statement dan koneksi
$stmt->close();
$likeStmt->close();
$likeCountStmt->close();
$komentarStmt->close();
$conn->close();
?>

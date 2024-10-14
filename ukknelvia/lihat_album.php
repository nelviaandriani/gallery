<?php
session_start();
include 'config.php';

$userID = $_SESSION['UserID'];
$query = "SELECT * FROM album WHERE UserID = '$userID'";
$result = mysqli_query($conn, $query);
?>

<h1>Your Albums</h1>
<link rel="stylesheet" href="lihat_album.css">
<?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="album-container">
        <h3><?= htmlspecialchars($row['NamaAlbum']) ?></h3>
        <p><?= htmlspecialchars($row['Deskripsi']) ?></p>

        <?php
        // Ambil foto pertama sebagai cover album
        $albumID = $row['AlbumID'];
        $fotoQuery = "SELECT LokasiFile FROM foto WHERE AlbumID = '$albumID' LIMIT 1";
        $fotoResult = mysqli_query($conn, $fotoQuery);
        $fotoRow = mysqli_fetch_assoc($fotoResult);

        if ($fotoRow): ?>
            <img src="<?= htmlspecialchars($fotoRow['LokasiFile']) ?>" alt="Foto Album" width="200">
        <?php else: ?>
            <p>No cover photo available</p>
        <?php endif; ?>

        <div class="album-actions">
            <a href="edit_album.php?id=<?= $row['AlbumID'] ?>">Edit Album</a>
            <a href="lihat_foto.php?album_id=<?= $row['AlbumID'] ?>">View Photos</a>
        </div>
    </div>
<?php endwhile; ?>
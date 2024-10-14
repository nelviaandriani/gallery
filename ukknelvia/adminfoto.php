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

// Query untuk mengambil semua foto
if ($searchTerm) {
    // Pencarian berdasarkan judul foto, deskripsi, atau FotoID
    $query = "SELECT * FROM foto WHERE (JudulFoto LIKE ? OR DeskripsiFoto LIKE ? OR FotoID = ?) GROUP BY FotoID";
    $likeTerm = '%' . $searchTerm . '%'; // Persiapkan wildcard untuk pencarian
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $likeTerm, $likeTerm, $searchTerm);
} else {
    // Mengambil semua foto tanpa filter UserID
    $query = "SELECT * FROM foto GROUP BY FotoID";
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
    <title>Galeri Foto</title>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 15px;
        }
        main {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e0f7fa;
        }
        img {
            max-width: 100px; /* Batasi ukuran gambar */
            height: auto;
        }
        .btn {
            padding: 8px 15px;
            margin: 5px;
            text-decoration: none;
            color: white;
            background-color: blue;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: darkblue;
        }
        .btn-delete {
            background-color: red;
        }
        .btn-delete:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <header>
        <h1>Selamat datang <?= htmlspecialchars($_SESSION['UserName'] ?? '') ?>!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Foto</a></li>
                <li><a href="tambah_foto.php">Tambah Foto</a></li>
                <?php if ($userLevel === 'Admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div>
            <!-- Form Pencarian -->
            <form method="POST" action="">
                <input type="text" name="search" placeholder="Cari foto atau Foto ID..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="foto-list">
            <h2>Semua Foto</h2>
            <table>
                <thead>
                    <tr>
                        <th>Foto ID</th>
                        <th>Judul Foto</th>
                        <th>Deskripsi</th>
                        <th>Gambar</th> <!-- Tambahkan kolom gambar -->
                        <th>Tanggal Unggah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['FotoID']) ?></td>
                                <td><?= htmlspecialchars($row['JudulFoto']) ?></td>
                                <td><?= htmlspecialchars($row['DeskripsiFoto']) ?></td>
                                <td>
                                <img src="<?= htmlspecialchars($row['LokasiFile']) ?>" alt="<?= htmlspecialchars($row['JudulFoto']) ?>" width="200">
                                </td> <!-- Menampilkan gambar dari folder images/ -->
                                <td><?= htmlspecialchars($row['TanggalUnggah']) ?></td>
                                <td>
                                    <a class="btn" href="edit_foto.php?id=<?= htmlspecialchars($row['FotoID']) ?>">Edit Foto</a>
                                    <?php if ($userLevel == 'Admin'): ?>
                                        <a class="btn btn-delete" href="delete_foto.php?id=<?= htmlspecialchars($row['FotoID']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?');">Hapus Foto</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Tidak ada foto yang ditemukan. Anda bisa <a href="tambah_foto.php">menambahkan foto</a>.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

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
    // Pencarian berdasarkan judul foto, deskripsi, atau FotoID dari semua foto
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
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin-right: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #ffdd57;
        }

        /* Main content styles */
        main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        main h2 {
            margin-top: 0;
            font-size: 24px;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        /* Form search styles */
        form {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        form input[type="text"] {
            width: 300px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
        }

        form button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
        }

        th {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e0f7fa;
        }

        /* Image styles */
        img {
            max-width: 150px;
            height: auto;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: red;
        }

        .btn-delete:hover {
            background-color: darkred;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            header, main {
                padding: 15px;
            }

            nav ul li {
                display: block;
                margin: 10px 0;
            }

            form input[type="text"] {
                width: 100%;
                margin-bottom: 10px;
            }

            form button {
                width: 100%;
            }

            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Selamat datang <?= htmlspecialchars($_SESSION['UserName'] ?? '') ?>!</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
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
                        <th>Tanggal Unggah</th>
                        <th>Gambar</th>
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
                                <td><?= htmlspecialchars($row['TanggalUnggah']) ?></td>
                                <td><img src="<?= htmlspecialchars($row['LokasiFile']) ?>" alt="Foto"></td>
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

<?php
include 'config.php'; // Koneksi database

$error_message = ""; // Variabel untuk menyimpan pesan kesalahan

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $level = $_POST['level']; // Get the role (User or Admin) from the form

    // Cek apakah username atau email sudah ada
    $checkQuery = "SELECT * FROM user WHERE Username = ? OR Email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Jika ada username atau email yang sama
        $error_message = "Username or email already exists. Please choose another.";
    } else {
        // Gunakan prepared statement untuk mencegah SQL Injection
        $sql = "INSERT INTO user (Username, Password, Email, NamaLengkap, Alamat, Level) 
                VALUES (?, ?, ?, ?, ?, ?)"; // Pastikan 'Level' digunakan sesuai struktur database

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameter ke prepared statement
            $stmt->bind_param("ssssss", $username, $password, $email, $nama_lengkap, $alamat, $level);

            // Eksekusi statement
            if ($stmt->execute()) {
                // Redirect ke halaman login setelah registrasi berhasil
                header("Location: login.php");
                exit;
            } else {
                $error_message = "Error: " . $stmt->error;
            }

            // Tutup statement
            $stmt->close();
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }

    // Tutup statement pemeriksaan
    $checkStmt->close();
    // Tutup koneksi
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <form method="POST" action="">
        <h2>Registrasi</h2><br>

        <!-- Tampilkan pesan kesalahan jika ada -->
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required><br><br>
        <textarea name="alamat" placeholder="Alamat" required></textarea><br><br>
        
        <!-- Container for password and toggle icon -->
        <div class="password-container">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div><br>

        <!-- Role selection dropdown -->
        <label for="level">Register as:</label>
        <select name="level" id="level" required>
            <option value="User">User</option>
            <option value="Admin">Admin</option>
        </select><br><br>

        <button type="submit">Register</button><br>
        <a href="login.php" class="button">Login</a>
    </form>

    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var toggleIcon = document.querySelector(".toggle-password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.textContent = "🙈"; // Change icon to closed eye
            } else {
                passwordField.type = "password";
                toggleIcon.textContent = "👁️"; // Change icon back to open eye
            }
        }
    </script>
</body>
</html>

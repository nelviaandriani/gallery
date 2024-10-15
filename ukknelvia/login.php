<?php
session_start();
include 'config.php'; // Include your database configuration file

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to retrieve user data based on username
    $query = "SELECT * FROM user WHERE Username = ?";
    $stmt = $conn->prepare($query);

    // Check if prepare was successful
    if ($stmt === false) {
        die("MySQL prepare error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user was found
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify the password using password_verify
        if (password_verify($password, $row['Password'])) {
            // Successful login, save session
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Level'] = $row['Level']; // Store user Level (User or Admin)

            // Redirect to index.php for both Admin and User
            header('Location: index.php');
            exit(); // Ensure no further code is executed after redirection
        } else {
            $error_message = "Invalid Username or Password!";
        }
    } else {
        $error_message = "Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <form method="POST" action="">
        <h2>Login</h2><br>
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        
        <button type="submit" name="login">Login</button><br>
        <a href="register.php" class="button">Register</a>
        
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
    </form>
</body>
</html>

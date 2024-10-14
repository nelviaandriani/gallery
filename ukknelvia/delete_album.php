<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['Level'])) {
    header('Location: login.php');
    exit();
}

// Check if the album ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $albumID = $_GET['id'];

    // Delete the album from the database
    $query = "DELETE FROM album WHERE AlbumID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $albumID);
    
    if ($stmt->execute()) {
        header('Location: index.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid album ID.";
    exit();
}
?>

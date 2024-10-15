<?php
session_start();
include 'config.php';

if ($_SESSION['Level'] != 'Admin') {
    header('Location: login.php');
    exit();
}

$komentarID = $_GET['id'];

$query = "DELETE FROM komentarfoto WHERE KomentarID = '$komentarID'";
if (mysqli_query($conn, $query)) {
    header('Location: komentar_admin.php');
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
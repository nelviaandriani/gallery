<?php
include"config.php";
session_start();

$FotoID = $_GET["FotoID"];

$sql = mysqli_query($conn,"DELETE FROM foto WHERE FotoID='$FotoID'");

header("location:admin_foto.php");
?>
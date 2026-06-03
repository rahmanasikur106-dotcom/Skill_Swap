<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "skillswap";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
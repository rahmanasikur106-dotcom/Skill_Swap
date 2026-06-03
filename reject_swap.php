<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$me      = intval($_SESSION['user_id']);
$swap_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($swap_id <= 0) { header("Location: swaps.php"); exit(); }

// Only receiver can reject, or sender can cancel their own pending request
$stmt = $conn->prepare("
    DELETE FROM swaps WHERE id = ? AND status = 'pending'
    AND (receiver_id = ? OR sender_id = ?)
");
$stmt->bind_param("iii", $swap_id, $me, $me);
$stmt->execute();

header("Location: swaps.php");
exit();
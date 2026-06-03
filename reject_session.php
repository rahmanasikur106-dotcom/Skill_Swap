<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id    = $_SESSION['user_id'];
$session_id = intval($_GET['id'] ?? 0);

// Only the invited person (user2) can reject, and only if Pending
$stmt = $conn->prepare("UPDATE sessions SET status='Rejected' WHERE session_id=? AND user2_id=? AND status='Pending'");
$stmt->bind_param("ii", $session_id, $user_id);
$stmt->execute();

header("Location: session_list.php");
exit();
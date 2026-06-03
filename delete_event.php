<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (empty($_GET['event_id'])) { header("Location: dashboard.php"); exit(); }

$user_id  = $_SESSION['user_id'];
$event_id = (int)$_GET['event_id'];

// Only the creator can delete
$check = $conn->prepare("SELECT event_id FROM events WHERE event_id = ? AND creator_id = ?");
$check->bind_param("ii", $event_id, $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Participants deleted automatically via ON DELETE CASCADE
    $del = $conn->prepare("DELETE FROM events WHERE event_id = ? AND creator_id = ?");
    $del->bind_param("ii", $event_id, $user_id);
    $del->execute();
}

header("Location: dashboard.php");
exit();
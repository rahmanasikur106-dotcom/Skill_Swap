<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (empty($_GET['event_id'])) { header("Location: dashboard.php"); exit(); }

$user_id  = $_SESSION['user_id'];
$event_id = (int)$_GET['event_id'];

$check = $conn->prepare("SELECT id FROM event_participant WHERE user_id = ? AND event_id = ?");
$check->bind_param("ii", $user_id, $event_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $ins = $conn->prepare("INSERT INTO event_participant (user_id, event_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $event_id);
    $ins->execute();
}

header("Location: dashboard.php");
exit();
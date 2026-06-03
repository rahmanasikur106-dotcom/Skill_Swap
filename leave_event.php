<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (empty($_GET['event_id'])) { header("Location: dashboard.php"); exit(); }

$user_id  = $_SESSION['user_id'];
$event_id = (int)$_GET['event_id'];

$del = $conn->prepare("DELETE FROM event_participant WHERE user_id = ? AND event_id = ?");
$del->bind_param("ii", $user_id, $event_id);
$del->execute();

header("Location: dashboard.php");
exit();
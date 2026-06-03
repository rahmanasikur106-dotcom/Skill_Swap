<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$me       = intval($_SESSION['user_id']);
$other_id = isset($_GET['to']) ? intval($_GET['to']) : 0;

if ($other_id <= 0 || $other_id === $me) {
    header("Location: matchmaking.php");
    exit();
}

// Verify reciprocal skill match exists
$verify = $conn->prepare("
    SELECT COUNT(*) AS cnt
    FROM users u
    JOIN user_skills us1 ON u.user_id = us1.user_id AND us1.type_name = 'Teach'
    JOIN user_skills us2 ON u.user_id = us2.user_id AND us2.type_name = 'Learn'
    WHERE u.user_id = ?
    AND us1.skill_id IN (SELECT skill_id FROM user_skills WHERE user_id = ? AND type_name = 'Learn')
    AND us2.skill_id IN (SELECT skill_id FROM user_skills WHERE user_id = ? AND type_name = 'Teach')
");
$verify->bind_param("iii", $other_id, $me, $me);
$verify->execute();
$valid = $verify->get_result()->fetch_assoc()['cnt'] ?? 0;

if (!$valid) {
    header("Location: matchmaking.php?error=not_a_match");
    exit();
}

// Check if request already exists in either direction
$check = $conn->prepare("
    SELECT id, status FROM swaps
    WHERE (sender_id = ? AND receiver_id = ?)
       OR (sender_id = ? AND receiver_id = ?)
");
$check->bind_param("iiii", $me, $other_id, $other_id, $me);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if (!$existing) {
    // Insert new swap request
    $ins = $conn->prepare("INSERT INTO swaps (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
    $ins->bind_param("ii", $me, $other_id);
    $ins->execute();

    // Notify the receiver
    $nm = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = ?");
    $nm->bind_param("i", $me);
    $nm->execute();
    $my_name = $nm->get_result()->fetch_assoc()['name'] ?? 'Someone';

$notif = $conn->prepare("INSERT INTO notification (user_id, message_text, type) VALUES (?, ?, 'swap_request')");
    $msg   = $my_name . " sent you a swap request!";
    $notif->bind_param("is", $other_id, $msg);
    $notif->execute();
}

header("Location: swaps.php?sent=1");
exit();
<?php
include 'db.php';
require_once 'MessageMiddle.php';

// ── Session check ──────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ── Only accept POST ───────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$sender_id   = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$message_text = trim($_POST['message'] ?? '');

// Basic sanity check before hitting the middleware
if ($receiver_id <= 0) {
    header("Location: chat.php?error=" . urlencode("Invalid recipient."));
    exit();
}

$middle = new MessageMiddle($conn);
$result = $middle->sendMessage($sender_id, $receiver_id, $message_text);

if ($result['success']) {
    header("Location: chat.php?user_id=" . $receiver_id);
} else {
    header("Location: chat.php?user_id=" . $receiver_id . "&error=" . urlencode($result['error']));
}
exit();
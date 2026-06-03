<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$me      = intval($_SESSION['user_id']);
$swap_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($swap_id <= 0) { header("Location: swaps.php"); exit(); }

// Only the receiver can accept
$stmt = $conn->prepare("UPDATE swaps SET status = 'accepted' WHERE id = ? AND receiver_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $swap_id, $me);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Also create a match record so the match gate works
    $get = $conn->prepare("SELECT sender_id FROM swaps WHERE id = ?");
    $get->bind_param("i", $swap_id);
    $get->execute();
    $sender_id = $get->get_result()->fetch_assoc()['sender_id'] ?? 0;

    if ($sender_id > 0) {
        $chk = $conn->prepare("SELECT match_id FROM matches WHERE (user1_id=? AND user2_id=?) OR (user1_id=? AND user2_id=?)");
        $chk->bind_param("iiii", $me, $sender_id, $sender_id, $me);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO matches (user1_id, user2_id, type) VALUES (?, ?, 'skill_swap')");
            $ins->bind_param("ii", $me, $sender_id);
            $ins->execute();
        }

        // Notify the sender
        $nm = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = ?");
        $nm->bind_param("i", $me);
        $nm->execute();
        $my_name = $nm->get_result()->fetch_assoc()['name'] ?? 'Someone';
$notif = $conn->prepare("INSERT INTO notification (user_id, message_text, type) VALUES (?, ?, 'swap_request')");
        $msg   = $my_name . " accepted your swap request! You can now chat.";
        $notif->bind_param("is", $sender_id, $msg);
        $notif->execute();
    }
}

header("Location: swaps.php?accepted=1");
exit();
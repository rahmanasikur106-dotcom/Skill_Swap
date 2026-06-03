<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$me       = intval($_SESSION['user_id']);
$other_id = isset($_GET['other_id']) ? intval($_GET['other_id']) : 0;

if ($other_id <= 0 || $other_id === $me) {
    header("Location: matchmaking.php");
    exit();
}

// Verify this is a legitimate reciprocal match before allowing connect
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
    // Not a real reciprocal match — block it
    header("Location: matchmaking.php?error=not_a_match");
    exit();
}

// Check if match already exists
$check = $conn->prepare("
    SELECT match_id FROM matches
    WHERE (user1_id = ? AND user2_id = ?)
       OR (user1_id = ? AND user2_id = ?)
");
$check->bind_param("iiii", $me, $other_id, $other_id, $me);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    // Create the match
    $ins = $conn->prepare("INSERT INTO matches (user1_id, user2_id, type) VALUES (?, ?, 'skill_swap')");
    $ins->bind_param("ii", $me, $other_id);
    $ins->execute();

    // Fetch my name for notification
    $nm = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = ?");
    $nm->bind_param("i", $me);
    $nm->execute();
    $my_name = $nm->get_result()->fetch_assoc()['name'] ?? 'Someone';

    // Notify the other user
    $notif = $conn->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
    $msg   = $my_name . " connected with you for a skill swap!";
    $notif->bind_param("is", $other_id, $msg);
    $notif->execute();
}

// Redirect to chat with this user
header("Location: chat.php?user_id=" . $other_id);
exit();
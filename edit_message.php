<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_id = intval($_POST['message_id']);
    $receiver_id = intval($_POST['receiver_id']);
    $new_text = trim($_POST['new_message']);
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($new_text)) {
        header("Location: chat.php?user_id=" . $receiver_id . "&error=Message cannot be empty");
        exit();
    }

    // Only allow sender to edit their own message
    $check = $conn->prepare("SELECT MessageID FROM messages WHERE MessageID = ? AND sender_id = ?");
    $check->bind_param("ii", $message_id, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 1) {
        // Update the message
        $update = $conn->prepare("UPDATE messages SET MessageText = ?, IsEdited = 1 WHERE MessageID = ?");
        $update->bind_param("si", $new_text, $message_id);
        $update->execute();
    }

    header("Location: chat.php?user_id=" . $receiver_id);
    exit();
} else {
    header("Location: chat.php");
    exit();
}
?>
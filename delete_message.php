<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_id = intval($_POST['message_id']);
    $receiver_id = intval($_POST['receiver_id']);
    $user_id = $_SESSION['user_id'];

    // Only allow the sender to delete their own message
    $check = $conn->prepare("SELECT MessageID FROM messages WHERE MessageID = ? AND sender_id = ?");
    $check->bind_param("ii", $message_id, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 1) {
        // Delete the message permanently
        $delete = $conn->prepare("DELETE FROM messages WHERE MessageID = ?");
        $delete->bind_param("i", $message_id);
        $delete->execute();
    }

    header("Location: chat.php?user_id=" . $receiver_id);
    exit();
} else {
    header("Location: chat.php");
    exit();
}
?>
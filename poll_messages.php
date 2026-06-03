<?php
/**
 * poll_messages.php  (updated — includes file metadata)
 * ───────────────────────────────────────────────────────
 * Called by chat.php every 3 seconds via fetch().
 * Returns any new messages since `last_id` as JSON,
 * including file_path / file_name / file_type / file_size.
 *
 * GET params:
 *   receiver_id  – the other user in the conversation
 *   last_id      – the highest MessageID the client already has
 */

include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$sender_id   = intval($_SESSION['user_id']);
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
$last_id     = isset($_GET['last_id'])     ? intval($_GET['last_id'])     : 0;

if ($receiver_id <= 0) {
    echo json_encode(['messages' => []]);
    exit();
}

// ── Mark incoming messages as read ─────────────────────────────
$mr = $conn->prepare("
    UPDATE messages SET IsRead = 1
    WHERE sender_id = ? AND receiver_id = ? AND IsRead = 0
");
$mr->bind_param("ii", $receiver_id, $sender_id);
$mr->execute();

// ── Fetch only messages newer than last_id ─────────────────────
$stmt = $conn->prepare("
    SELECT MessageID, sender_id, receiver_id, MessageText,
           IsRead, IsEdited, Timestamp,
           file_path, file_name, file_type, file_size
    FROM messages
    WHERE ((sender_id = ? AND receiver_id = ?)
        OR (sender_id = ? AND receiver_id = ?))
      AND MessageID > ?
    ORDER BY Timestamp ASC
");
$stmt->bind_param("iiiii", $sender_id, $receiver_id, $receiver_id, $sender_id, $last_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['messages' => $rows]);
exit();
<?php
/**
 * get_messages.php
 * ────────────────
 * Called by the long-poll in chat.php every 3 seconds.
 * Returns the message-row HTML fragment for the active conversation.
 * No full page wrapper — just the .msg-row divs.
 */

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$sender_id   = intval($_SESSION['user_id']);
$receiver_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($receiver_id <= 0) {
    http_response_code(400);
    exit();
}

// Mark incoming messages as read
$up = $conn->prepare("UPDATE messages SET IsRead = 1 WHERE sender_id = ? AND receiver_id = ?");
$up->bind_param("ii", $receiver_id, $sender_id);
$up->execute();
$up->close();

// Fetch conversation history
$sql = "
    SELECT sender_id, receiver_id, MessageID, MessageText,
           file_path, file_name, Timestamp
    FROM messages
    WHERE (sender_id = ? AND receiver_id = ?)
       OR (sender_id = ? AND receiver_id = ?)
    ORDER BY Timestamp ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Output message rows (same markup as chat.php so the DOM count comparison works)
foreach ($messages as $m):
    $isMe    = ($m['sender_id'] == $sender_id);
    $msgTime = date('H:i', strtotime($m['Timestamp']));
?>
<div class="msg-row <?= $isMe ? 'sent' : 'received' ?>"
     <?php if ($isMe): ?>
     data-msg-id="<?= $m['MessageID'] ?>"
     data-msg-text="<?= htmlspecialchars($m['MessageText'] ?? '', ENT_QUOTES) ?>"
     data-receiver="<?= $receiver_id ?>"
     <?php endif; ?>>
    <div class="msg-bubble">
        <?php if ($isMe && empty($m['file_path'])): ?>
            <div class="msg-actions-hint" style="font-size:0.65rem;color:rgba(255,255,255,0.35);text-align:right;margin-bottom:2px;">hold to edit</div>
        <?php endif; ?>

        <?php if (!empty($m['MessageText'])): ?>
            <div><?= nl2br(htmlspecialchars($m['MessageText'])) ?></div>
        <?php endif; ?>

        <?php if (!empty($m['file_name'])): ?>
            <a href="uploads/<?= htmlspecialchars($m['file_name']) ?>" target="_blank" class="file-attachment">
                📁 Download Attachment
            </a>
        <?php endif; ?>

        <span class="msg-meta"><?= $msgTime ?></span>
    </div>
</div>
<?php endforeach; ?>
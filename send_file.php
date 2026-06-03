<?php
/**
 * send_file.php
 * ─────────────
 * Handles file uploads in the chat.
 * Saves the file to /uploads/chat_files/ and inserts a message row
 * with the file metadata. No size limit enforced server-side.
 */

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$sender_id   = intval($_SESSION['user_id']);
$receiver_id = intval($_POST['receiver_id'] ?? 0);

if ($receiver_id <= 0) {
    header("Location: chat.php?error=" . urlencode("Invalid recipient."));
    exit();
}

if (!isset($_FILES['chat_file']) || $_FILES['chat_file']['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'File too large (server limit).',
        UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
        UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file selected.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by extension.',
    ];
    $code = $_FILES['chat_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $msg  = $upload_errors[$code] ?? 'Unknown upload error.';
    header("Location: chat.php?user_id={$receiver_id}&error=" . urlencode($msg));
    exit();
}

// ── Create upload directory if missing ─────────────────────
$upload_dir = __DIR__ . '/uploads/chat_files/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// ── Sanitise file name & build unique storage name ─────────
$original_name = basename($_FILES['chat_file']['name']);
// Strip any path traversal characters
$original_name = preg_replace('/[^a-zA-Z0-9._\- ]/', '_', $original_name);
$ext           = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
$stored_name   = uniqid('cf_', true) . '.' . $ext;
$dest_path     = $upload_dir . $stored_name;
$relative_path = 'uploads/chat_files/' . $stored_name;

$file_type = $_FILES['chat_file']['type'];
$file_size = $_FILES['chat_file']['size'];

// ── Move uploaded file ──────────────────────────────────────
if (!move_uploaded_file($_FILES['chat_file']['tmp_name'], $dest_path)) {
    header("Location: chat.php?user_id={$receiver_id}&error=" . urlencode("Failed to save file. Check server permissions."));
    exit();
}

// ── Insert message row (MessageText is the original filename) ─
$caption = trim($_POST['caption'] ?? '');
$msg_text = $caption !== '' ? $caption : $original_name;

$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, MessageText, file_path, file_name, file_type, file_size)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "iissssi",
    $sender_id,
    $receiver_id,
    $msg_text,
    $relative_path,
    $original_name,
    $file_type,
    $file_size
);

if ($stmt->execute()) {
    header("Location: chat.php?user_id=" . $receiver_id);
} else {
    // Clean up orphaned file
    @unlink($dest_path);
    header("Location: chat.php?user_id={$receiver_id}&error=" . urlencode("Database error while saving file."));
}
exit();
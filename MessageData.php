<?php
/**
 * MessageData.php
 * ───────────────
 * Data-access layer for the messages table.
 * Called exclusively by MessageMiddle.php — no direct use elsewhere.
 */

class MessageData {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Insert a new text message.
     * Returns true on success, false on failure.
     */
    public function addMessage(int $sender_id, int $receiver_id, string $message_text): bool {
        $stmt = $this->conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, MessageText)
            VALUES (?, ?, ?)
        ");
        if (!$stmt) return false;
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);
        return $stmt->execute();
    }

    /**
     * Get all messages involving a user (sent or received).
     * Returns array of rows.
     */
    public function listMessages(int $user_id): array {
        $stmt = $this->conn->prepare("
            SELECT MessageID, sender_id, receiver_id, MessageText,
                   IsRead, IsEdited, Timestamp,
                   file_path, file_name, file_type, file_size
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            ORDER BY Timestamp ASC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Find a single message by its ID.
     * Returns the row array or null.
     */
    public function findMessage(int $message_id): ?array {
        $stmt = $this->conn->prepare("
            SELECT MessageID, sender_id, receiver_id, MessageText,
                   IsRead, IsEdited, Timestamp,
                   file_path, file_name, file_type, file_size
            FROM messages
            WHERE MessageID = ?
            LIMIT 1
        ");
        if (!$stmt) return null;
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Filter messages received by a user by read status.
     * $is_read: 0 = unread, 1 = read
     * Returns array of rows.
     */
    public function filterMessages(int $user_id, int $is_read): array {
        $stmt = $this->conn->prepare("
            SELECT MessageID, sender_id, receiver_id, MessageText,
                   IsRead, IsEdited, Timestamp,
                   file_path, file_name, file_type, file_size
            FROM messages
            WHERE receiver_id = ? AND IsRead = ?
            ORDER BY Timestamp DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("ii", $user_id, $is_read);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
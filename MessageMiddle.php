<?php
require_once 'MessageData.php';

class MessageMiddle {
    private $data;

    public function __construct($conn) {
        $this->data = new MessageData($conn);
    }

    // Validate and send message
    public function sendMessage($sender_id, $receiver_id, $message_text) {
        // Validation
        if (empty(trim($message_text))) {
            return ["success" => false, "error" => "Message cannot be empty"];
        }
        if (!is_numeric($sender_id) || !is_numeric($receiver_id)) {
            return ["success" => false, "error" => "Invalid user ID"];
        }
        if ($sender_id == $receiver_id) {
            return ["success" => false, "error" => "Cannot send message to yourself"];
        }

        $result = $this->data->addMessage($sender_id, $receiver_id, $message_text);
        return ["success" => $result];
    }

    // Get all messages for a user
    public function getMessages($user_id) {
        return $this->data->listMessages($user_id);
    }

    // Find one message by ID
    public function getMessage($message_id) {
        return $this->data->findMessage($message_id);
    }

    // Filter messages by IsRead status (0=unread, 1=read)
    public function filterMessages($user_id, $is_read) {
        if (!in_array($is_read, [0, 1])) {
            return ["success" => false, "error" => "Invalid filter value"];
        }
        return $this->data->filterMessages($user_id, $is_read);
    }
}
?>
<?php
include 'db.php';
require_once 'MessageMiddle.php';

$middle = new MessageMiddle($conn);

// Test 1 — Send a message
echo "<h3>Test 1: Send a Message</h3>";
$result = $middle->sendMessage(1, 2, "Hello this is a test message!");
if ($result['success']) {
    echo "<p style='color:green'>✅ Message sent successfully!</p>";
} else {
    echo "<p style='color:red'>❌ Error: " . $result['error'] . "</p>";
}

// Test 2 — Send empty message (should fail)
echo "<h3>Test 2: Send Empty Message</h3>";
$result2 = $middle->sendMessage(1, 2, "");
if (!$result2['success']) {
    echo "<p style='color:green'>✅ Empty message correctly blocked: " . $result2['error'] . "</p>";
} else {
    echo "<p style='color:red'>❌ Empty message was not blocked!</p>";
}

// Test 3 — List all messages
echo "<h3>Test 3: List Messages for User 1</h3>";
$messages = $middle->getMessages(1);
if (count($messages) > 0) {
    echo "<p style='color:green'>✅ " . count($messages) . " messages found</p>";
    foreach ($messages as $msg) {
        echo "<p>— " . htmlspecialchars($msg['MessageText']) . " (IsRead: " . $msg['IsRead'] . ")</p>";
    }
} else {
    echo "<p style='color:orange'>⚠️ No messages found</p>";
}

// Test 4 — Filter unread messages
echo "<h3>Test 4: Filter Unread Messages for User 2</h3>";
$unread = $middle->filterMessages(2, 0);
if (is_array($unread) && count($unread) > 0) {
    echo "<p style='color:green'>✅ " . count($unread) . " unread messages found</p>";
} else {
    echo "<p style='color:orange'>⚠️ No unread messages found</p>";
}

echo "<hr><p><strong>All tests complete!</strong></p>";
?>
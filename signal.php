<?php
/**
 * signal.php
 * ──────────
 * Lightweight WebRTC signaling endpoint.
 * Uses long-polling to push signals to the other peer.
 *
 * POST  → store a signal   (offer / answer / ice / hangup / ring)
 * GET   → poll for signals since ?since=<id>
 */

include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = intval($_SESSION['user_id']);

// ── POST: store signal ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $room_id  = preg_replace('/[^a-zA-Z0-9_\-]/', '', $body['room_id']  ?? '');
    $to_user  = intval($body['to_user']  ?? 0);
    $type     = $body['type']    ?? '';
    $payload  = json_encode($body['payload'] ?? []);

    $allowed_types = ['offer','answer','ice','hangup','ring'];
    if (!$room_id || !$to_user || !in_array($type, $allowed_types)) {
        echo json_encode(['error' => 'Invalid signal']);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO call_signals (room_id, from_user, to_user, type, payload)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("siiss", $room_id, $user_id, $to_user, $type, $payload);
    $stmt->execute();

    echo json_encode(['ok' => true, 'id' => $conn->insert_id]);
    exit();
}

// ── GET: poll signals ──────────────────────────────────────
$room_id  = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['room_id'] ?? '');
$since_id = intval($_GET['since'] ?? 0);

if (!$room_id) {
    echo json_encode(['signals' => []]);
    exit();
}

// Auto-clean old signals (> 60s)
$conn->query("DELETE FROM call_signals WHERE created_at < NOW() - INTERVAL 60 SECOND");

$stmt = $conn->prepare("
    SELECT id, from_user, to_user, type, payload, created_at
    FROM call_signals
    WHERE room_id = ? AND to_user = ? AND id > ?
    ORDER BY id ASC
    LIMIT 20
");
$stmt->bind_param("sii", $room_id, $user_id, $since_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Decode payloads
foreach ($rows as &$r) {
    $r['payload'] = json_decode($r['payload'], true);
}

echo json_encode(['signals' => $rows]);
exit();
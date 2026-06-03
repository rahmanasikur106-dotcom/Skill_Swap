<?php
include 'db.php';
require_once 'MessageMiddle.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$middle  = new MessageMiddle($conn);

// 0 = unread, 1 = read
$filter   = isset($_GET['filter']) ? intval($_GET['filter']) : 0;
$messages = $middle->filterMessages($user_id, $filter);

include 'includes/header.php';
?>

<style>
body { background: #0f0f13; color: #d0d0e8; }
.fm-wrap { max-width: 700px; margin: 0 auto; padding: 50px 20px 80px; }
.fm-title { font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 6px; }
.fm-sub   { color: #505060; font-size: 0.9rem; margin-bottom: 28px; }

.fm-tabs  { display: flex; gap: 10px; margin-bottom: 28px; }
.fm-tab   {
    padding: 9px 22px; border-radius: 50px; text-decoration: none;
    font-weight: 600; font-size: 0.875rem; border: 1px solid #2a2a35;
    color: #a0a0b0; background: #16161d; transition: all 0.2s;
}
.fm-tab:hover  { border-color: #6366f1; color: #fff; }
.fm-tab.active { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border-color: transparent; }

.fm-card {
    background: #16161d;
    border: 1px solid #2a2a35;
    border-radius: 16px;
    overflow: hidden;
}
.fm-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 22px;
    border-bottom: 1px solid #1e1e28;
    transition: background 0.15s;
}
.fm-row:last-child { border-bottom: none; }
.fm-row:hover { background: #1a1a24; }
.fm-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.fm-dot.unread { background: #6366f1; }
.fm-dot.read   { background: #2a2a35; }
.fm-body { flex: 1; min-width: 0; }
.fm-text {
    color: #e0e0f0;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fm-text.unread { font-weight: 600; }
.fm-date { font-size: 0.75rem; color: #404050; margin-top: 4px; }
.fm-badge {
    padding: 3px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
}
.fm-badge.unread { background: #1e1e3a; color: #818cf8; }
.fm-badge.read   { background: #0e2a1a; color: #4ade80; }

.fm-empty {
    padding: 60px 20px;
    text-align: center;
    color: #404050;
}
.fm-empty .icon { font-size: 2.5rem; margin-bottom: 12px; }
.fm-empty p { font-size: 0.9rem; line-height: 1.6; }

.fm-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 22px;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
}
.fm-back:hover { color: #818cf8; }
.fm-count { margin-top: 14px; color: #404050; font-size: 0.8rem; }
</style>

<div class="fm-wrap">
    <h2 class="fm-title">📬 Filter Messages</h2>
    <p class="fm-sub">View your received messages by read/unread status.</p>

    <!-- Tabs -->
    <div class="fm-tabs">
        <a href="filter_messages.php?filter=0" class="fm-tab <?= $filter == 0 ? 'active' : '' ?>">
            🔵 Unread
        </a>
        <a href="filter_messages.php?filter=1" class="fm-tab <?= $filter == 1 ? 'active' : '' ?>">
            ✅ Read
        </a>
    </div>

    <!-- Message List -->
    <div class="fm-card">
        <?php if (is_array($messages) && count($messages) > 0): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="fm-row">
                    <div class="fm-dot <?= $msg['IsRead'] == 0 ? 'unread' : 'read' ?>"></div>
                    <div class="fm-body">
                        <div class="fm-text <?= $msg['IsRead'] == 0 ? 'unread' : '' ?>">
                            <?= htmlspecialchars($msg['MessageText']) ?>
                        </div>
                        <div class="fm-date">
                            <?= date('D d M Y, H:i', strtotime($msg['Timestamp'])) ?>
                        </div>
                    </div>
                    <span class="fm-badge <?= $msg['IsRead'] == 0 ? 'unread' : 'read' ?>">
                        <?= $msg['IsRead'] == 0 ? 'Unread' : 'Read' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="fm-empty">
                <div class="icon"><?= $filter == 0 ? '✅' : '📭' ?></div>
                <p>
                    <?= $filter == 0
                        ? 'No unread messages — you\'re all caught up!'
                        : 'No read messages yet.' ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <p class="fm-count">
        Showing <?= is_array($messages) ? count($messages) : 0 ?>
        <?= $filter == 0 ? 'unread' : 'read' ?> message(s)
    </p>

    <!-- Fixed back link — goes to chat.php not swaps.php -->
    <a class="fm-back" href="chat.php">← Back to Messages</a>
</div>

<?php include 'includes/footer.php'; ?>
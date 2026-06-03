<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// Handle Accept / Reject / Cancel actions
if (isset($_GET['action'], $_GET['id'])) {
    $session_id = (int)$_GET['id'];
    $action     = $_GET['action'];

    $check = $conn->prepare("SELECT session_id, user1_id, user2_id, status FROM sessions WHERE session_id = ? AND (user1_id = ? OR user2_id = ?) LIMIT 1");
    $check->bind_param("iii", $session_id, $user_id, $user_id);
    $check->execute();
    $sess = $check->get_result()->fetch_assoc();

    if ($sess) {
        if ($action === 'accept' && $sess['user2_id'] == $user_id && $sess['status'] === 'Pending') {
            $upd = $conn->prepare("UPDATE sessions SET status='Accepted' WHERE session_id=?");
            $upd->bind_param("i", $session_id);
            $upd->execute();

        } elseif ($action === 'reject' && $sess['user2_id'] == $user_id && $sess['status'] === 'Pending') {
            $upd = $conn->prepare("UPDATE sessions SET status='Rejected' WHERE session_id=?");
            $upd->bind_param("i", $session_id);
            $upd->execute();

        } elseif ($action === 'cancel' && $sess['user1_id'] == $user_id && $sess['status'] === 'Pending') {
            $upd = $conn->prepare("DELETE FROM sessions WHERE session_id=? AND user1_id=?");
            $upd->bind_param("ii", $session_id, $user_id);
            $upd->execute();
        }
    }

    header("Location: session_list.php" . (isset($_GET['filter']) ? "?filter=" . $_GET['filter'] : ""));
    exit();
}

// Filter
$filter  = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$allowed = ['all', 'Pending', 'Accepted', 'Rejected'];
if (!in_array($filter, $allowed)) $filter = 'all';

// Fetch sessions
$where = $filter === 'all' ? "" : "AND s.status = '" . $conn->real_escape_string($filter) . "'";
$stmt = $conn->prepare("
    SELECT s.*,
           CONCAT(u1.first_name, ' ', u1.last_name) AS creator_name,
           CONCAT(u2.first_name, ' ', u2.last_name) AS partner_name
    FROM sessions s
    JOIN users u1 ON u1.user_id = s.user1_id
    JOIN users u2 ON u2.user_id = s.user2_id
    WHERE (s.user1_id = ? OR s.user2_id = ?) $where
    ORDER BY s.date_time ASC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Counts for tab badges
$counts_stmt = $conn->prepare("
    SELECT status, COUNT(*) AS cnt FROM sessions
    WHERE user1_id = ? OR user2_id = ?
    GROUP BY status
");
$counts_stmt->bind_param("ii", $user_id, $user_id);
$counts_stmt->execute();
$counts_raw = $counts_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$counts = ['all' => 0, 'Pending' => 0, 'Accepted' => 0, 'Rejected' => 0];
foreach ($counts_raw as $r) {
    $counts[$r['status']] = (int)$r['cnt'];
    $counts['all'] += (int)$r['cnt'];
}

include 'includes/header.php';
?>

<style>
.sl-wrap { max-width: 860px; margin: 40px auto; padding: 0 20px 60px; }
.sl-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 14px; }
.sl-title  { font-size: 1.6rem; font-weight: 800; color: #e0e0f0; }
.btn-create {
    padding: 10px 22px; border-radius: 12px; font-weight: 700; font-size: 0.88rem;
    background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff;
    text-decoration: none; transition: opacity 0.2s;
}
.btn-create:hover { opacity: 0.85; }

/* Tabs */
.sl-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.sl-tab {
    padding: 8px 18px; border-radius: 99px; font-size: 0.85rem; font-weight: 700;
    color: #505060; background: #16161d; border: 1px solid #2a2a35;
    text-decoration: none; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
}
.sl-tab:hover { color: #e0e0f0; border-color: #6366f1; }
.sl-tab.active { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; border-color: transparent; }
.sl-tab-count {
    background: rgba(255,255,255,0.2); color: inherit;
    font-size: 0.75rem; padding: 1px 7px; border-radius: 99px;
}
.sl-tab:not(.active) .sl-tab-count { background: #2a2a35; color: #808090; }

/* Session card */
.sess-card {
    background: #16161d; border: 1px solid #2a2a35; border-radius: 18px;
    padding: 24px 28px; margin-bottom: 16px;
    display: flex; gap: 24px; align-items: flex-start;
    transition: border-color 0.2s, transform 0.15s;
}
.sess-card:hover { border-color: #6366f1; transform: translateY(-2px); }

.sess-date-block {
    min-width: 70px; text-align: center;
    background: #0f0f13; border: 1px solid #2a2a35;
    border-radius: 14px; padding: 12px 10px;
    flex-shrink: 0;
}
.sess-date-day   { font-size: 1.6rem; font-weight: 800; color: #6366f1; line-height: 1; }
.sess-date-month { font-size: 0.72rem; font-weight: 700; color: #505060; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }
.sess-date-time  { font-size: 0.72rem; color: #505060; margin-top: 4px; }

.sess-body  { flex: 1; min-width: 0; }
.sess-people { font-size: 0.82rem; color: #505060; margin-bottom: 8px; }
.sess-people strong { color: #a0a0b0; }
.sess-skills { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.sess-skill {
    padding: 5px 12px; border-radius: 99px; font-size: 0.78rem; font-weight: 700;
}
.skill-teach { background: rgba(99,102,241,0.12); color: #818cf8; border: 1px solid rgba(99,102,241,0.25); }
.skill-learn { background: rgba(16,185,129,0.1); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.2); }

.sess-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.sa-btn {
    padding: 7px 16px; border-radius: 9px; font-size: 0.8rem; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer; transition: opacity 0.2s;
    display: inline-block;
}
.sa-accept  { background: linear-gradient(135deg,#10b981,#06b6d4); color: #fff; }
.sa-reject  { background: #1e1e28; color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
.sa-cancel  { background: #1e1e28; color: #808090; border: 1px solid #2a2a35; }
.sa-chat    { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; }
.sa-btn:hover { opacity: 0.82; }

/* Status badge */
.status-badge { padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 700; margin-left: auto; flex-shrink: 0; }
.st-pending  { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
.st-accepted { background: rgba(16,185,129,0.1);  color: #6ee7b7; border: 1px solid rgba(16,185,129,0.2); }
.st-rejected { background: rgba(239,68,68,0.1);   color: #fca5a5; border: 1px solid rgba(239,68,68,0.2); }

.sl-empty { text-align: center; padding: 60px 20px; color: #404055; }
.sl-empty .icon { font-size: 2.5rem; margin-bottom: 12px; }
</style>

<div class="sl-wrap">
    <div class="sl-header">
        <div class="sl-title">📅 My Sessions</div>
        <a href="create_session.php" class="btn-create">+ Schedule Session</a>
    </div>

    <div class="sl-tabs">
        <?php
        $tabs = ['all' => 'All', 'Pending' => 'Pending', 'Accepted' => 'Accepted', 'Rejected' => 'Rejected'];
        foreach ($tabs as $key => $label):
            $active = ($filter === $key) ? 'active' : '';
        ?>
            <a href="session_list.php?filter=<?= $key ?>" class="sl-tab <?= $active ?>">
                <?= $label ?>
                <span class="sl-tab-count"><?= $counts[$key] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($sessions)): ?>
        <div class="sl-empty">
            <div class="icon">📭</div>
            <p>No sessions found. <a href="create_session.php" style="color:#6366f1;font-weight:700;">Schedule one →</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($sessions as $s):
            $is_creator = ($s['user1_id'] == $user_id);
            $other_name = $is_creator ? $s['partner_name'] : $s['creator_name'];
            $other_id   = $is_creator ? $s['user2_id'] : $s['user1_id'];
            $dt         = new DateTime($s['date_time']);
            $status_cls = ['Pending' => 'st-pending', 'Accepted' => 'st-accepted', 'Rejected' => 'st-rejected'][$s['status']] ?? 'st-pending';
        ?>
        <div class="sess-card">
            <div class="sess-date-block">
                <div class="sess-date-day"><?= $dt->format('d') ?></div>
                <div class="sess-date-month"><?= $dt->format('M Y') ?></div>
                <div class="sess-date-time"><?= $dt->format('H:i') ?></div>
            </div>

            <div class="sess-body">
                <div class="sess-people">
                    <?= $is_creator ? 'You invited' : 'Invited by' ?>
                    <strong><?= htmlspecialchars($other_name) ?></strong>
                </div>

                <div class="sess-skills">
                    <span class="sess-skill skill-teach">🎓 Teach: <?= htmlspecialchars($s['skill_offered']) ?></span>
                    <span class="sess-skill skill-learn">📖 Learn: <?= htmlspecialchars($s['skill_requested']) ?></span>
                </div>

                <div class="sess-actions">
                    <?php if ($s['status'] === 'Pending'): ?>
                        <?php if (!$is_creator): ?>
                            <a href="session_list.php?action=accept&id=<?= $s['session_id'] ?>&filter=<?= $filter ?>" class="sa-btn sa-accept">✅ Accept</a>
                            <a href="session_list.php?action=reject&id=<?= $s['session_id'] ?>&filter=<?= $filter ?>"
                               class="sa-btn sa-reject"
                               onclick="return confirm('Reject this session?')">❌ Reject</a>
                        <?php else: ?>
                            <span style="font-size:0.8rem; color:#505060; padding:7px 0;">Waiting for response…</span>
                            <a href="session_list.php?action=cancel&id=<?= $s['session_id'] ?>&filter=<?= $filter ?>"
                               class="sa-btn sa-cancel"
                               onclick="return confirm('Cancel this session request?')">🗑 Cancel</a>
                        <?php endif; ?>
                    <?php elseif ($s['status'] === 'Accepted'): ?>
                        <a href="chat.php?user_id=<?= $other_id ?>" class="sa-btn sa-chat">💬 Open Chat</a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <span class="status-badge <?= $status_cls ?>"><?= $s['status'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
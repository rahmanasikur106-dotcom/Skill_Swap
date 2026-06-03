<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// ── Incoming pending requests (others sent to me) ──────────────
$inc_stmt = $conn->prepare("
    SELECT s.id AS swap_id, s.created_at,
           CONCAT(u.first_name, ' ', u.last_name) AS name,
           u.user_id, u.bio
    FROM swaps s
    JOIN users u ON u.user_id = s.sender_id
    WHERE s.receiver_id = ? AND s.status = 'pending'
    ORDER BY s.created_at DESC
");
$inc_stmt->bind_param("i", $user_id);
$inc_stmt->execute();
$incoming = $inc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Outgoing pending requests (I sent, not yet accepted) ────────
$out_stmt = $conn->prepare("
    SELECT s.id AS swap_id, s.created_at,
           CONCAT(u.first_name, ' ', u.last_name) AS name,
           u.user_id, u.bio
    FROM swaps s
    JOIN users u ON u.user_id = s.receiver_id
    WHERE s.sender_id = ? AND s.status = 'pending'
    ORDER BY s.created_at DESC
");
$out_stmt->bind_param("i", $user_id);
$out_stmt->execute();
$outgoing = $out_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Accepted conversations ──────────────────────────────────────
$conv_stmt = $conn->prepare("
    SELECT
        u.user_id AS id,
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        latest.MessageText,
        latest.Timestamp,
        latest.sender_id,
        COALESCE(unread.unread_count, 0) AS unread_count
    FROM swaps s
    JOIN users u ON u.user_id = CASE WHEN s.sender_id = ? THEN s.receiver_id ELSE s.sender_id END
    LEFT JOIN (
        SELECT
            CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_id,
            MAX(Timestamp) AS last_time
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY other_id
    ) convo ON convo.other_id = u.user_id
    LEFT JOIN messages latest
        ON ((latest.sender_id = ? AND latest.receiver_id = u.user_id)
            OR (latest.sender_id = u.user_id AND latest.receiver_id = ?))
        AND latest.Timestamp = convo.last_time
    LEFT JOIN (
        SELECT sender_id, COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = ? AND IsRead = 0
        GROUP BY sender_id
    ) unread ON unread.sender_id = u.user_id
    WHERE (s.sender_id = ? OR s.receiver_id = ?) AND s.status = 'accepted'
    ORDER BY COALESCE(latest.Timestamp, s.created_at) DESC
");
$conv_stmt->bind_param("iiiiiiiii",
    $user_id, $user_id, $user_id, $user_id,
    $user_id, $user_id, $user_id, $user_id, $user_id
);
$conv_stmt->execute();
$conversations = $conv_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$colors = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444','#14b8a6'];
function swColor($name, $colors) {
    return $colors[ord($name[0] ?? 'A') % count($colors)];
}

$total_unread  = array_sum(array_column($conversations, 'unread_count'));
$total_incoming = count($incoming);

include 'includes/header.php';
?>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
footer { display: none !important; }

.sw-shell { display:flex; height:calc(100vh - 73px); font-family:'Segoe UI',system-ui,sans-serif; background:#0f0f13; overflow:hidden; }

/* ── Sidebar ── */
.sw-sidebar { width:380px; flex-shrink:0; background:#16161d; display:flex; flex-direction:column; border-right:1px solid #2a2a35; overflow-y:auto; }
.sw-sidebar-top { padding:20px 16px 12px; border-bottom:1px solid #2a2a35; }
.sw-title { font-size:1.3rem; font-weight:800; color:#fff; margin-bottom:12px; }
.sw-search { width:100%; background:#1e1e28; border:1px solid #2a2a35; border-radius:10px; padding:9px 14px; color:#a0a0b0; font-size:0.875rem; outline:none; }
.sw-search:focus { border-color:#6366f1; color:#fff; }

/* Stats bar */
.sw-stats { display:flex; gap:0; border-bottom:1px solid #2a2a35; background:#13131a; }
.sw-stat { text-align:center; flex:1; padding:12px 8px; border-right:1px solid #2a2a35; }
.sw-stat:last-child { border-right:none; }
.sw-stat-val { font-size:1.2rem; font-weight:800; color:#6366f1; }
.sw-stat-label { font-size:0.68rem; color:#505060; margin-top:2px; }

/* Section headers */
.sw-section-title {
    padding:14px 16px 6px;
    font-size:0.7rem; font-weight:700; color:#505060;
    text-transform:uppercase; letter-spacing:0.08em;
    display:flex; justify-content:space-between; align-items:center;
}
.sw-section-badge { background:#ec4899; color:#fff; font-size:0.65rem; font-weight:700; padding:2px 7px; border-radius:99px; }

/* Pending request card */
.sw-request-card {
    margin:6px 10px; background:#1a1a24;
    border:1px solid #2a2a35; border-radius:12px; padding:14px;
}
.sw-req-header { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.sw-req-av { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:800; color:#fff; flex-shrink:0; }
.sw-req-name { font-size:0.88rem; font-weight:700; color:#e0e0f0; }
.sw-req-time { font-size:0.72rem; color:#404050; margin-top:2px; }
.sw-req-actions { display:flex; gap:7px; }
.sw-btn { padding:7px 14px; border-radius:8px; font-size:0.78rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; text-align:center; flex:1; transition:opacity 0.15s; display:inline-block; }
.sw-btn:hover { opacity:0.85; }
.sw-btn-accept { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.sw-btn-reject { background:#1e1e28; color:#ef4444; border:1px solid #3f1010; }
.sw-btn-cancel { background:#1e1e28; color:#a0a0b0; border:1px solid #2a2a35; }

/* Outgoing pending */
.sw-outgoing-card {
    margin:4px 10px; padding:10px 14px;
    background:#13131a; border:1px solid #2a2a35; border-radius:10px;
    display:flex; align-items:center; gap:10px;
}
.sw-outgoing-av { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:800; color:#fff; flex-shrink:0; }
.sw-outgoing-body { flex:1; min-width:0; }
.sw-outgoing-name { font-size:0.85rem; font-weight:700; color:#a0a0b0; }
.sw-outgoing-status { font-size:0.72rem; color:#404050; margin-top:1px; }

/* Conversations */
.sw-item { display:flex; align-items:center; gap:12px; padding:12px 10px; border-radius:12px; text-decoration:none; color:inherit; transition:background 0.15s; margin:2px 8px; }
.sw-item:hover { background:#1e1e28; }
.sw-av { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:800; color:#fff; flex-shrink:0; }
.sw-item-body { flex:1; min-width:0; }
.sw-item-name { font-size:0.88rem; font-weight:700; color:#e0e0f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sw-item-preview { font-size:0.76rem; color:#505060; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.sw-item-preview.unread { color:#a0a0c0; font-weight:600; }
.sw-msg-badge { background:#6366f1; color:#fff; border-radius:20px; font-size:0.65rem; font-weight:700; padding:2px 7px; flex-shrink:0; }
.sw-time { font-size:0.65rem; color:#404050; }

/* Right panel */
.sw-right { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#0c0c0f; gap:14px; padding:40px; text-align:center; }
.sw-empty-icon { width:72px; height:72px; border-radius:20px; background:#1e1e28; border:1px solid #2a2a35; display:flex; align-items:center; justify-content:center; font-size:1.8rem; }

/* Toast */
.sw-toast { position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#10b981; color:#fff; padding:10px 24px; border-radius:10px; font-weight:700; font-size:0.88rem; z-index:9999; display:none; }
</style>

<!-- Toast notifications -->
<div class="sw-toast" id="swToast"></div>

<div class="sw-shell">
    <aside class="sw-sidebar">
        <div class="sw-sidebar-top">
            <div class="sw-title">🤝 Swap Requests</div>
            <input class="sw-search" id="swSearch" type="text" placeholder="Search conversations...">
        </div>

        <div class="sw-stats">
            <div class="sw-stat">
                <div class="sw-stat-val"><?= $total_incoming ?></div>
                <div class="sw-stat-label">Incoming</div>
            </div>
            <div class="sw-stat">
                <div class="sw-stat-val"><?= count($outgoing) ?></div>
                <div class="sw-stat-label">Sent</div>
            </div>
            <div class="sw-stat">
                <div class="sw-stat-val"><?= count($conversations) ?></div>
                <div class="sw-stat-label">Active</div>
            </div>
            <div class="sw-stat">
                <div class="sw-stat-val"><?= $total_unread ?></div>
                <div class="sw-stat-label">Unread</div>
            </div>
        </div>

        <!-- ── Incoming Requests ── -->
        <?php if (!empty($incoming)): ?>
            <div class="sw-section-title">
                Incoming Requests
                <span class="sw-section-badge"><?= count($incoming) ?></span>
            </div>
            <?php foreach ($incoming as $r):
                $col = swColor($r['name'], $colors);
                $ago = time() - strtotime($r['created_at']);
                $tstr = $ago < 3600 ? floor($ago/60).'m ago' : ($ago < 86400 ? floor($ago/3600).'h ago' : date('d M', strtotime($r['created_at'])));
            ?>
            <div class="sw-request-card">
                <div class="sw-req-header">
                    <div class="sw-req-av" style="background:<?= $col ?>"><?= strtoupper(substr($r['name'],0,1)) ?></div>
                    <div>
                        <div class="sw-req-name"><?= htmlspecialchars($r['name']) ?></div>
                        <div class="sw-req-time">Sent <?= $tstr ?></div>
                    </div>
                </div>
                <div class="sw-req-actions">
                    <a href="accept_swap.php?id=<?= $r['swap_id'] ?>" class="sw-btn sw-btn-accept">✅ Accept</a>
                    <a href="reject_swap.php?id=<?= $r['swap_id'] ?>" class="sw-btn sw-btn-reject">✕ Decline</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ── Outgoing Pending ── -->
        <?php if (!empty($outgoing)): ?>
            <div class="sw-section-title">Sent — Awaiting Response</div>
            <?php foreach ($outgoing as $r):
                $col = swColor($r['name'], $colors);
            ?>
            <div class="sw-outgoing-card">
                <div class="sw-outgoing-av" style="background:<?= $col ?>"><?= strtoupper(substr($r['name'],0,1)) ?></div>
                <div class="sw-outgoing-body">
                    <div class="sw-outgoing-name"><?= htmlspecialchars($r['name']) ?></div>
                    <div class="sw-outgoing-status">⏳ Pending their response</div>
                </div>
                <a href="reject_swap.php?id=<?= $r['swap_id'] ?>" class="sw-btn sw-btn-cancel" style="flex:0;padding:6px 10px;">Cancel</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ── Active Conversations ── -->
        <?php if (!empty($conversations)): ?>
            <div class="sw-section-title">Active Conversations</div>
            <?php foreach ($conversations as $c):
                $col = swColor($c['name'], $colors);
                $has_unread = intval($c['unread_count']) > 0;
                $preview = ($c['sender_id'] == $user_id ? 'You: ' : '') . ($c['MessageText'] ?? 'Say hello 👋');
                $tstr = '';
                if (!empty($c['Timestamp'])) {
                    $ts = strtotime($c['Timestamp']);
                    $diff = time() - $ts;
                    $tstr = $diff < 60 ? 'Now' : ($diff < 3600 ? floor($diff/60).'m ago' : ($diff < 86400 ? date('H:i',$ts) : date('d M',$ts)));
                }
            ?>
            <a class="sw-item" href="chat.php?user_id=<?= $c['id'] ?>" data-name="<?= strtolower(htmlspecialchars($c['name'])) ?>">
                <div class="sw-av" style="background:<?= $col ?>"><?= strtoupper(substr($c['name'],0,1)) ?></div>
                <div class="sw-item-body">
                    <div class="sw-item-name"><?= htmlspecialchars($c['name']) ?></div>
                    <div class="sw-item-preview <?= $has_unread ? 'unread' : '' ?>"><?= htmlspecialchars(mb_strimwidth($preview,0,38,'...')) ?></div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                    <span class="sw-time"><?= $tstr ?></span>
                    <?php if ($has_unread): ?><span class="sw-msg-badge"><?= $c['unread_count'] ?></span><?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($incoming) && empty($outgoing) && empty($conversations)): ?>
            <div style="padding:40px 16px;text-align:center;color:#404050;font-size:0.82rem;line-height:1.8;">
                No swap requests yet.<br>
                <a href="matchmaking.php" style="color:#6366f1;text-decoration:none;font-weight:600;">Find your skill matches →</a>
            </div>
        <?php endif; ?>
    </aside>

    <div class="sw-right">
        <div class="sw-empty-icon">🤝</div>
        <h2 style="color:#e0e0f0;font-size:1.15rem;font-weight:700;">Your Skill Swaps</h2>
        <p style="color:#505060;font-size:0.85rem;max-width:260px;line-height:1.6;">
            Accept a swap request to unlock messaging, or find new reciprocal matches.
        </p>
        <a href="matchmaking.php" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:0.875rem;">
            Find Matches →
        </a>
    </div>
</div>

<script>
// Search filter
document.getElementById('swSearch')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.sw-item').forEach(el => {
        el.style.display = el.dataset.name?.includes(q) ? '' : 'none';
    });
});

// Toast on redirect
const params = new URLSearchParams(location.search);
const toast  = document.getElementById('swToast');
if (params.get('sent') === '1') {
    toast.textContent = '✅ Swap request sent!';
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 3000);
}
if (params.get('accepted') === '1') {
    toast.textContent = '🎉 Request accepted! You can now chat.';
    toast.style.background = '#6366f1';
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 3500);
}
</script>

<?php include 'includes/footer.php'; ?>
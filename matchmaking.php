<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$me = intval($_SESSION['user_id']);

// Perfect reciprocal matches with swap request status
$sql = "
    SELECT DISTINCT
        u.user_id,
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        u.user_location AS location,
        u.bio,
        -- swap status
        (SELECT status FROM swaps
         WHERE (sender_id = ? AND receiver_id = u.user_id)
            OR (sender_id = u.user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1
        ) AS swap_status,
        (SELECT id FROM swaps
         WHERE sender_id = ? AND receiver_id = u.user_id
           AND status = 'pending'
         LIMIT 1
        ) AS my_pending_id
    FROM users u
    JOIN user_skills us1 ON u.user_id = us1.user_id AND us1.type_name = 'Teach'
    JOIN user_skills us2 ON u.user_id = us2.user_id AND us2.type_name = 'Learn'
    WHERE us1.skill_id IN (SELECT skill_id FROM user_skills WHERE user_id = ? AND type_name = 'Learn')
    AND   us2.skill_id IN (SELECT skill_id FROM user_skills WHERE user_id = ? AND type_name = 'Teach')
    AND   u.user_id != ?
    AND   u.account_status = 'active'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $me, $me, $me, $me, $me, $me);
$stmt->execute();
$result = $stmt->get_result();

$grads = [
    'linear-gradient(135deg,#7c3aed,#ec4899)',
    'linear-gradient(135deg,#06b6d4,#7c3aed)',
    'linear-gradient(135deg,#f59e0b,#ec4899)',
    'linear-gradient(135deg,#10b981,#06b6d4)',
];
?>

<style>
.mm-wrap { max-width: 960px; margin: 40px auto; padding: 0 20px 60px; }
.mm-title { font-size: 1.75rem; font-weight: 800; color: #e0e0f0; margin-bottom: 6px; }
.mm-subtitle { color: #505060; font-size: 0.88rem; margin-bottom: 32px; line-height: 1.6; }
.mm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.mm-card { background: #16161d; border: 1px solid #2a2a35; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; gap: 10px; transition: transform 0.2s, border-color 0.2s; }
.mm-card:hover { transform: translateY(-2px); border-color: #6366f1; }
.mm-avatar { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg,#6366f1,#8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800; color: #fff; }
.mm-name { font-size: 1.05rem; font-weight: 700; color: #e0e0f0; }
.mm-location { font-size: 0.8rem; color: #505060; }
.mm-bio { font-size: 0.8rem; color: #606070; line-height: 1.5; }
.mm-badge { padding: 4px 10px; border-radius: 99px; font-size: 0.73rem; font-weight: 700; display: inline-block; }
.badge-match   { background: rgba(99,102,241,0.1); color: #818cf8; border: 1px solid rgba(99,102,241,0.2); }
.badge-pending { background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); }
.badge-active  { background: rgba(16,185,129,0.1); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
.mm-actions { display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap; }
.btn-send    { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; text-decoration: none; padding: 9px 16px; border-radius: 9px; font-weight: 700; font-size: 0.8rem; flex: 1; text-align: center; transition: opacity 0.2s; }
.btn-send:hover { opacity: 0.88; }
.btn-pending { background: #1e1e28; color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); padding: 9px 16px; border-radius: 9px; font-weight: 700; font-size: 0.8rem; flex: 1; text-align: center; text-decoration: none; }
.btn-message { background: linear-gradient(135deg,#10b981,#059669); color: #fff; text-decoration: none; padding: 9px 16px; border-radius: 9px; font-weight: 700; font-size: 0.8rem; flex: 1; text-align: center; transition: opacity 0.2s; }
.btn-message:hover { opacity: 0.88; }
.btn-profile { background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35; text-decoration: none; padding: 9px 16px; border-radius: 9px; font-weight: 700; font-size: 0.8rem; flex: 1; text-align: center; transition: background 0.2s; }
.btn-profile:hover { background: #252532; color: #e0e0f0; }
.mm-empty { background: #0f0f13; border: 1px solid #2a2a35; border-radius: 16px; padding: 50px 30px; text-align: center; color: #a0a0b0; grid-column: 1/-1; }
</style>

<div class="mm-wrap">
    <div class="mm-title">🤝 Your Skill Match Suggestions</div>
    <div class="mm-subtitle">
        Only <strong style="color:#6366f1;">perfect reciprocal matches</strong> are shown —
        people who teach what you want to learn and want to learn what you teach.
        Send a swap request to connect. Messaging unlocks after they accept.
    </div>

    <div class="mm-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()):
                $initial    = strtoupper(substr($row['name'], 0, 1));
                $grad       = $grads[$row['user_id'] % count($grads)];
                $status     = $row['swap_status'];
                $pending_id = $row['my_pending_id'];
            ?>
            <div class="mm-card">
                <div class="mm-avatar" style="background:<?= $grad ?>"><?= $initial ?></div>
                <div class="mm-name"><?= htmlspecialchars($row['name']) ?></div>
                <?php if (!empty($row['location'])): ?>
                    <div class="mm-location">📍 <?= htmlspecialchars($row['location']) ?></div>
                <?php endif; ?>
                <?php if (!empty($row['bio'])): ?>
                    <div class="mm-bio"><?= htmlspecialchars(mb_strimwidth($row['bio'], 0, 80, '...')) ?></div>
                <?php endif; ?>

                <!-- Status badge -->
                <?php if ($status === 'accepted'): ?>
                    <span class="mm-badge badge-active">✅ Connected</span>
                <?php elseif ($status === 'pending'): ?>
                    <span class="mm-badge badge-pending">⏳ Request Pending</span>
                <?php else: ?>
                    <span class="mm-badge badge-match">🔁 Reciprocal Match</span>
                <?php endif; ?>

                <div class="mm-actions">
                    <?php if ($status === 'accepted'): ?>
                        <a href="chat.php?user_id=<?= $row['user_id'] ?>" class="btn-message">💬 Message</a>
                    <?php elseif ($pending_id): ?>
                        <a href="reject_swap.php?id=<?= $pending_id ?>" class="btn-pending">⏳ Cancel Request</a>
                    <?php else: ?>
                        <a href="send_swap_request.php?to=<?= $row['user_id'] ?>" class="btn-send">➕ Send Request</a>
                    <?php endif; ?>
                    <a href="view_profile.php?id=<?= $row['user_id'] ?>" class="btn-profile">Profile</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="mm-empty">
                <div style="font-size:2.5rem;margin-bottom:14px;">🔍</div>
                <p style="font-size:1.05rem;font-weight:700;color:#e0e0f0;margin-bottom:8px;">No reciprocal matches yet</p>
                <p style="font-size:0.88rem;margin:0 0 20px;">Add more skills to your profile to find matches.</p>
                <a href="profile.php" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:0.88rem;">Update My Skills →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
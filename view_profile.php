<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';
include 'includes/header.php';

$user_id_to_view = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id_to_view === 0) {
    die("Error: No user specified.");
}

// ── FETCH USER ──────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT user_id AS id,
           CONCAT(first_name, ' ', last_name) AS name,
           bio, user_location AS location,
           rating_average, total_reviews
    FROM users WHERE user_id = ?
");
$stmt->bind_param("i", $user_id_to_view);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Error: User not found.");
}

// ── FETCH SKILLS (joins user_skills → skills table) ─────────
$skills_teach = [];
$skills_learn  = [];

$sk = $conn->prepare("
    SELECT us.type_name AS skill_direction, s.skill_name
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = ?
");
$sk->bind_param("i", $user_id_to_view);
$sk->execute();
$sk_result = $sk->get_result();

while ($row = $sk_result->fetch_assoc()) {
    $direction = strtolower(trim($row['skill_direction']));
    if ($direction === 'teach') {
        $skills_teach[] = $row['skill_name'];
    } elseif ($direction === 'learn') {
        $skills_learn[] = $row['skill_name'];
    }
}

// ── AVATAR ──────────────────────────────────────────────────
$grads = [
    'linear-gradient(135deg,#7c3aed,#ec4899)',
    'linear-gradient(135deg,#06b6d4,#7c3aed)',
    'linear-gradient(135deg,#f59e0b,#ec4899)',
    'linear-gradient(135deg,#10b981,#06b6d4)',
];
$grad    = $grads[$user_id_to_view % count($grads)];
$initial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
?>

<style>
.vp-wrap { max-width: 800px; margin: 40px auto; padding: 0 20px 60px; }
.vp-card { background: #16161d; border: 1px solid #2a2a35; border-radius: 20px; padding: 32px; margin-bottom: 20px; }
.vp-header { display: flex; align-items: center; gap: 20px; margin-bottom: 28px; flex-wrap: wrap; }
.vp-av { width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #fff; flex-shrink: 0; }
.vp-name { font-size: 1.6rem; font-weight: 800; color: #e0e0f0; margin-bottom: 4px; }
.vp-meta-row { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
.vp-location, .vp-rating { color: #505060; font-size: 0.875rem; display: flex; align-items: center; gap: 4px; }
.vp-rating-num { color: #f59e0b; font-weight: 700; }
.vp-section-title { font-size: 0.75rem; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; }
.vp-bio { color: #a0a0b0; line-height: 1.7; background: #0f0f13; padding: 18px; border-radius: 12px; border: 1px solid #2a2a35; font-size: 0.9rem; }
.skill-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.skill-pill { padding: 6px 14px; border-radius: 99px; font-size: 0.8rem; font-weight: 600; }
.skill-teach { background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); }
.skill-learn  { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
.vp-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
.btn { padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.9rem; display: inline-block; text-align: center; border: none; cursor: pointer; }
.btn-primary   { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; }
.btn-secondary { background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35; }
.empty-skill { color: #404050; font-size: 0.85rem; font-style: italic; }
</style>

<?php if (isset($_GET['updated'])): ?>
<div style="max-width:800px;margin:24px auto 0;padding:0 20px;">
    <div style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:10px;color:#34d399;font-weight:600;font-size:.9rem;">
        ✅ Your profile has been updated successfully!
    </div>
</div>
<?php endif; ?>

<div class="vp-wrap">
    <div class="vp-card">

        <div class="vp-header">
            <div class="vp-av" style="background:<?= $grad ?>"><?= $initial ?></div>
            <div style="flex:1;">
                <div class="vp-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="vp-meta-row">
                    <?php if (!empty($user['location'])): ?>
                        <div class="vp-location">📍 <?= htmlspecialchars($user['location']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($user['total_reviews']) && $user['total_reviews'] > 0): ?>
                        <div class="vp-rating">
                            ⭐ <span class="vp-rating-num"><?= number_format($user['rating_average'], 1) ?></span>
                            (<?= $user['total_reviews'] ?> <?= $user['total_reviews'] == 1 ? 'review' : 'reviews' ?>)
                        </div>
                    <?php else: ?>
                        <div class="vp-rating" style="opacity:0.5;">⭐ No reviews yet</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (isset($_SESSION['user_id']) && $user['id'] == $_SESSION['user_id']): ?>
                <a href="profile.php" class="btn btn-secondary">✏️ Edit Profile</a>
            <?php endif; ?>
        </div>

        <div style="margin-bottom:24px;">
            <div class="vp-section-title">About</div>
            <div class="vp-bio">
                <?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : "This user hasn't added a bio yet." ?>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <div>
                <div class="vp-section-title">🎓 Can Teach</div>
                <div class="skill-grid">
                    <?php if (!empty($skills_teach)): ?>
                        <?php foreach ($skills_teach as $skill): ?>
                            <span class="skill-pill skill-teach"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="empty-skill">No teach skills listed yet</span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <div class="vp-section-title">🎯 Wants to Learn</div>
                <div class="skill-grid">
                    <?php if (!empty($skills_learn)): ?>
                        <?php foreach ($skills_learn as $skill): ?>
                            <span class="skill-pill skill-learn"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="empty-skill">No learn skills listed yet</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

       <?php if (isset($_SESSION['user_id']) && $user['id'] != $_SESSION['user_id']): ?>
    <div class="vp-actions">
        <a href="chat.php?user_id=<?= $user['id'] ?>" class="btn btn-primary" style="flex:1;">
            💬 Message to Swap
        </a>
        <a href="add_rating.php?reviewed_id=<?= $user['id'] ?>" class="btn btn-secondary" style="flex:1; text-align:center;">
            ⭐ Rate User
        </a>
    </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <div style="text-align:center;padding:16px;background:#1e1e28;border-radius:10px;border:1px solid #2a2a35;">
                <p style="color:#a0a0b0;margin:0;">Please <a href="login.php" style="color:#6366f1;font-weight:700;">log in</a> to message this user.</p>
            </div>
        <?php endif; ?>

    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="discovery.php" class="btn btn-secondary">← Back to Discover</a>
        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
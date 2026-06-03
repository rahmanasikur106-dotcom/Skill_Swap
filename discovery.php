<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$current_user = $_SESSION['user_id'];
$search_term  = isset($_GET['search']) ? trim($_GET['search']) : '';

// Only show users who are matched with the current user
$query = "
    SELECT u.user_id AS id,
           CONCAT(u.first_name, ' ', u.last_name) AS name,
           u.bio AS skills_offered
    FROM users u
    INNER JOIN swaps s
        ON (s.sender_id = ? AND s.receiver_id = u.user_id)
        OR (s.receiver_id = ? AND s.sender_id = u.user_id)
    WHERE u.user_id != ? AND u.account_status = 'active'
    AND s.status = 'accepted'
";

if (!empty($search_term)) {
    $query .= " AND (u.bio LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
}

$stmt = $conn->prepare($query);
if (!empty($search_term)) {
    $like = '%' . $search_term . '%';
    $stmt->bind_param("iiiss", $current_user, $current_user, $current_user, $like, $like);
} else {
    $stmt->bind_param("iii", $current_user, $current_user, $current_user);
}
$stmt->execute();
$result = $stmt->get_result();

$av_grads = [
    'linear-gradient(135deg,#7c3aed,#ec4899)',
    'linear-gradient(135deg,#06b6d4,#7c3aed)',
    'linear-gradient(135deg,#f59e0b,#ec4899)',
    'linear-gradient(135deg,#10b981,#06b6d4)',
    'linear-gradient(135deg,#ef4444,#f59e0b)',
    'linear-gradient(135deg,#8b5cf6,#06b6d4)',
];
function avGrad($name, $grads) {
    return $grads[ord($name[0] ?? 'A') % count($grads)];
}

include 'includes/header.php';
?>

<style>
.disco-hero { text-align:center; padding:50px 20px 30px; background:linear-gradient(135deg,#1a1a2e,#16213e); color:#fff; }
.disco-hero h1 { font-size:2rem; font-weight:800; margin-bottom:10px; }
.disco-hero h1 span { background:linear-gradient(135deg,#6366f1,#8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.disco-count { color:#a0a0b0; font-size:0.9rem; margin-bottom:20px; }
.search-bar { display:flex; gap:8px; max-width:500px; margin:0 auto 16px; }
.search-bar input { flex:1; padding:12px 16px; border-radius:10px; border:1px solid #2a2a35; background:#1e1e28; color:#fff; font-size:0.9rem; outline:none; }
.search-bar input:focus { border-color:#6366f1; }
.search-bar button { padding:12px 22px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer; }
.disco-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:20px; padding:30px 5%; background:#0f0f13; min-height:60vh; }
.user-card { background:#16161d; border:1px solid #2a2a35; border-radius:16px; padding:24px 20px; display:flex; flex-direction:column; gap:12px; transition:border-color 0.2s,transform 0.15s; }
.user-card:hover { border-color:#6366f1; transform:translateY(-2px); }
.user-card-avatar { width:60px; height:60px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:800; color:#fff; margin:0 auto; }
.user-card-name { text-align:center; font-weight:700; color:#e0e0f0; font-size:1rem; }
.user-card-skills p { font-size:0.72rem; color:#505060; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px; font-weight:600; }
.skill-tag { display:inline-block; padding:4px 12px; border-radius:99px; font-size:0.8rem; font-weight:600; }
.skill-offer { background:rgba(99,102,241,0.15); color:#818cf8; border:1px solid rgba(99,102,241,0.25); }
.user-card-actions { display:flex; gap:8px; margin-top:4px; }
.btn { padding:9px 16px; border-radius:9px; text-decoration:none; font-weight:600; font-size:0.82rem; cursor:pointer; border:none; display:inline-block; }
.btn-primary { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; }
.btn-secondary { background:#1e1e28; color:#a0a0b0; border:1px solid #2a2a35; }
.btn-sm { padding:7px 12px; font-size:0.78rem; }
.no-match-box { grid-column:1/-1; text-align:center; padding:60px 20px; color:#a0a0b0; }
.no-match-box .icon { font-size:3rem; margin-bottom:16px; }
</style>

<div class="disco-hero">
    <h1>
        <?= $search_term
            ? 'Results for "<span>' . htmlspecialchars($search_term) . '</span>"'
            : 'Your <span>Matched Connections</span>' ?>
    </h1>
    <p class="disco-count"><?= $result->num_rows ?> matched member<?= $result->num_rows != 1 ? 's' : '' ?> found</p>

    <form action="discovery.php" method="GET">
        <div class="search-bar">
            <input type="text" name="search"
                   value="<?= htmlspecialchars($search_term) ?>"
                   placeholder="Search your matches…">
            <button type="submit">Search</button>
        </div>
    </form>
</div>

<div class="disco-grid">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
            $grad = avGrad($row['name'], $av_grads);
        ?>
            <div class="user-card">
                <div class="user-card-avatar" style="background:<?= $grad ?>">
                    <?= strtoupper(substr($row['name'], 0, 1)) ?>
                </div>
                <div class="user-card-name"><?= htmlspecialchars($row['name']) ?></div>
                <div class="user-card-skills">
                    <p>About / Skills</p>
                    <span class="skill-tag skill-offer">
                        <?= htmlspecialchars($row['skills_offered'] ?: 'No bio yet') ?>
                    </span>
                </div>
                <div class="user-card-actions">
                    <a href="view_profile.php?id=<?= $row['id'] ?>"
                       class="btn btn-secondary btn-sm" style="flex:1;text-align:center;">Profile</a>
                    <a href="chat.php?user_id=<?= $row['id'] ?>"
                       class="btn btn-primary btn-sm" style="flex:2;text-align:center;">Message →</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-match-box">
            <div class="icon">🔒</div>
            <h3 style="color:#e0e0f0;margin-bottom:10px;">No matches yet</h3>
            <p style="margin-bottom:24px;max-width:360px;margin-left:auto;margin-right:auto;">
                You can only connect with users who match your skills. Find your reciprocal matches first.
            </p>
            <a href="matchmaking.php" class="btn btn-primary">Find My Matches →</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
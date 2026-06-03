<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$errors  = [];
$success = false;

// Only let users create sessions with people they have an accepted swap with
$partners_stmt = $conn->prepare("
    SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) AS name
    FROM users u
    INNER JOIN swaps s
        ON (s.sender_id = ? AND s.receiver_id = u.user_id)
        OR (s.receiver_id = ? AND s.sender_id = u.user_id)
    WHERE s.status = 'accepted' AND u.user_id != ?
    ORDER BY name ASC
");
$partners_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$partners_stmt->execute();
$partners = $partners_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch skills for dropdowns
$skills_res = $conn->query("SELECT skill_id, skill_name FROM skills ORDER BY skill_name ASC");
$skills = $skills_res ? $skills_res->fetch_all(MYSQLI_ASSOC) : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user2_id        = (int)($_POST['user2_id'] ?? 0);
    $skill_offered   = trim($_POST['skill_offered'] ?? '');
    $skill_requested = trim($_POST['skill_requested'] ?? '');
    $date_time       = trim($_POST['date_time'] ?? '');

    if (!$user2_id)          $errors[] = "Please select a partner.";
    if (!$skill_offered)     $errors[] = "Please enter the skill you will teach.";
    if (!$skill_requested)   $errors[] = "Please enter the skill you want to learn.";
    if (!$date_time)         $errors[] = "Please pick a date and time.";

    // Verify this partner has an accepted swap with current user
    if ($user2_id) {
        $verify = $conn->prepare("
            SELECT id FROM swaps
            WHERE status = 'accepted'
            AND ((sender_id = ? AND receiver_id = ?) OR (receiver_id = ? AND sender_id = ?))
            LIMIT 1
        ");
        $verify->bind_param("iiii", $user_id, $user2_id, $user_id, $user2_id);
        $verify->execute();
        if ($verify->get_result()->num_rows === 0) $errors[] = "Invalid partner selected.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO sessions (user1_id, user2_id, skill_offered, skill_requested, date_time, status)
            VALUES (?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->bind_param("iisss", $user_id, $user2_id, $skill_offered, $skill_requested, $date_time);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<style>
.sess-wrap { max-width: 680px; margin: 40px auto; padding: 0 20px 60px; }
.sess-card { background: #16161d; border: 1px solid #2a2a35; border-radius: 24px; padding: 40px; }
.sess-title { font-size: 1.6rem; font-weight: 800; color: #e0e0f0; margin-bottom: 6px; }
.sess-sub   { color: #505060; font-size: 0.9rem; margin-bottom: 32px; }
.form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 22px; }
.form-label { font-size: 0.78rem; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 0.06em; }
.form-input, .form-select {
    background: #0f0f13; border: 1px solid #2a2a35; border-radius: 12px;
    padding: 13px 16px; color: #e0e0f0; font-size: 0.95rem; font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s; width: 100%; outline: none;
}
.form-input:focus, .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
.form-select option { background: #1e1e28; }
.alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 24px; font-size: 0.9rem; }
.alert-error   { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5; }
.alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); color: #6ee7b7; }
.btn-row { display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
.btn-primary {
    padding: 13px 28px; border-radius: 12px; font-weight: 700; font-size: 0.95rem;
    background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; border: none;
    cursor: pointer; transition: opacity 0.2s, transform 0.15s;
}
.btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }
.btn-secondary {
    padding: 13px 28px; border-radius: 12px; font-weight: 700; font-size: 0.95rem;
    background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35;
    text-decoration: none; display: inline-flex; align-items: center;
    transition: background 0.2s, color 0.2s;
}
.btn-secondary:hover { background: #252532; color: #e0e0f0; }
.no-partners { background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2);
    border-radius: 14px; padding: 28px; text-align: center; color: #6060a0; }
.no-partners a { color: #6366f1; font-weight: 700; }
</style>

<div class="sess-wrap">
    <div class="sess-card">
        <div class="sess-title">📅 Schedule a Session</div>
        <div class="sess-sub">Book a time to teach or learn with one of your swap partners.</div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ Session created successfully! Your partner will see it in their session list.
            </div>
            <div class="btn-row">
                <a href="session_list.php" class="btn-secondary">View My Sessions →</a>
                <a href="create_session.php" class="btn-secondary">Create Another</a>
            </div>
        <?php elseif (empty($partners)): ?>
            <div class="no-partners">
                <div style="font-size:2rem; margin-bottom:10px;">🤝</div>
                <div style="font-weight:700; color:#a0a0b0; margin-bottom:6px;">No swap partners yet</div>
                <div style="font-size:0.88rem;">You can only schedule sessions with accepted swap partners.<br>
                <a href="discovery.php">Find people to swap with →</a></div>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?>
                        <div>❌ <?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Swap Partner</label>
                    <select name="user2_id" class="form-select" required>
                        <option value="">— Select a partner —</option>
                        <?php foreach ($partners as $p): ?>
                            <option value="<?= $p['user_id'] ?>"
                                <?= (isset($_POST['user2_id']) && $_POST['user2_id'] == $p['user_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Skill You Will Teach</label>
                    <input type="text" name="skill_offered" class="form-input"
                           placeholder="e.g. Python, Guitar, Photography"
                           value="<?= htmlspecialchars($_POST['skill_offered'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Skill You Want to Learn</label>
                    <input type="text" name="skill_requested" class="form-input"
                           placeholder="e.g. UI Design, Spanish, MySQL"
                           value="<?= htmlspecialchars($_POST['skill_requested'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" name="date_time" class="form-input"
                           min="<?= date('Y-m-d\TH:i') ?>"
                           value="<?= htmlspecialchars($_POST['date_time'] ?? '') ?>" required>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn-primary">📅 Create Session</button>
                    <a href="session_list.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

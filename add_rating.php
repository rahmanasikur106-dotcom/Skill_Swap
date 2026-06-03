<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reviewer_id = $_SESSION['user_id'];
$message = "";

// Check if we are rating a specific user from their profile link
$url_reviewed_id = isset($_GET['reviewed_id']) ? (int)$_GET['reviewed_id'] : 0;
$locked_user_name = "";

if ($url_reviewed_id > 0) {
    $user_stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = ? AND user_id != ?");
    $user_stmt->bind_param("ii", $url_reviewed_id, $reviewer_id);
    $user_stmt->execute();
    $user_res = $user_stmt->get_result();
    if ($row = $user_res->fetch_assoc()) {
        $locked_user_name = $row['name'];
    } else {
        $url_reviewed_id = 0;
    }
}

// Handle form submit
if (isset($_POST['submit'])) {
    $reviewed_id = ($url_reviewed_id > 0) ? $url_reviewed_id : (int)($_POST['reviewed_id'] ?? 0);
    $stars = (int)$_POST['stars'];

    if ($reviewed_id == $reviewer_id) {
        $message = "❌ You cannot rate yourself.";
    } elseif ($reviewed_id <= 0) {
        $message = "❌ Please select a valid user to rate.";
    } elseif ($stars < 1 || $stars > 5) {
        $message = "❌ Invalid rating value.";
    } else {
        $check_stmt = $conn->prepare("SELECT rating_id FROM rating WHERE reviewer_id = ? AND reviewed_id = ?");
        $check_stmt->bind_param("ii", $reviewer_id, $reviewed_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $update_stmt = $conn->prepare("UPDATE rating SET stars = ? WHERE reviewer_id = ? AND reviewed_id = ?");
            $update_stmt->bind_param("iii", $stars, $reviewer_id, $reviewed_id);
            $update_stmt->execute();
            $message = "✅ Rating updated successfully!";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO rating (reviewer_id, reviewed_id, stars) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $reviewer_id, $reviewed_id, $stars);
            $insert_stmt->execute();
            $message = "✅ Rating submitted successfully!";
        }

        // Recalculate and sync stats back to users table
        $calc_stmt = $conn->prepare("SELECT COUNT(*) as total_reviews, AVG(stars) as avg_rating FROM rating WHERE reviewed_id = ?");
        $calc_stmt->bind_param("i", $reviewed_id);
        $calc_stmt->execute();
        $stats = $calc_stmt->get_result()->fetch_assoc();

        $total_reviews  = (int)$stats['total_reviews'];
        $rating_average = (float)$stats['avg_rating'];

        $sync_stmt = $conn->prepare("UPDATE users SET rating_average = ?, total_reviews = ? WHERE user_id = ?");
        $sync_stmt->bind_param("dii", $rating_average, $total_reviews, $reviewed_id);
        $sync_stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate a User | SkillSwap</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f0f13; color: #a0a0b0; padding: 40px; }
        .rate-card { max-width: 450px; margin: 0 auto; background: #16161d; border: 1px solid #2a2a35; border-radius: 20px; padding: 32px; }
        h2 { margin-top: 0; color: #e0e0f0; font-weight: 800; margin-bottom: 24px; }
        label { font-size: 0.75rem; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 8px; }
        select, button, .locked-input { width: 100%; padding: 12px; border-radius: 12px; font-size: 0.95rem; margin-bottom: 20px; box-sizing: border-box; }
        select { background: #0f0f13; border: 1px solid #2a2a35; color: #e0e0f0; }
        .locked-input { background: #1e1e28; border: 1px solid #2a2a35; color: #818cf8; font-weight: 600; display: flex; align-items: center; }
        button { background: linear-gradient(135deg,#6366f1,#8b5cf6); border: none; color: white; font-weight: 700; cursor: pointer; transition: opacity 0.2s; }
        button:hover { opacity: 0.9; }
        .msg { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.2); text-align: center; font-weight: 600; color: #e0e0f0; }
        a { color: #6366f1; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-top: 10px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="rate-card">
    <h2>🌟 Rate a Member</h2>

    <?php if (!empty($message)): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Reviewing User:</label>

        <?php if ($url_reviewed_id > 0 && !empty($locked_user_name)): ?>
            <div class="locked-input">👤 <?= htmlspecialchars($locked_user_name) ?></div>
            <input type="hidden" name="reviewed_id" value="<?= $url_reviewed_id ?>">
        <?php else: ?>
            <select name="reviewed_id" required>
                <option value="">-- Select User --</option>
                <?php
                $u_stmt = $conn->prepare("SELECT user_id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id != ?");
                $u_stmt->bind_param("i", $reviewer_id);
                $u_stmt->execute();
                $users = $u_stmt->get_result();
                while ($u = $users->fetch_assoc()) {
                    echo "<option value='{$u['user_id']}'>" . htmlspecialchars($u['name']) . "</option>";
                }
                ?>
            </select>
        <?php endif; ?>

        <label>Select Rating:</label>
        <select name="stars" required>
            <option value="">-- Select Score --</option>
            <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
            <option value="4">⭐⭐⭐⭐ (4/5)</option>
            <option value="3">⭐⭐⭐ (3/5)</option>
            <option value="2">⭐⭐ (2/5)</option>
            <option value="1">⭐ (1/5)</option>
        </select>

        <button name="submit" type="submit">Submit Rating</button>
    </form>

    <div style="text-align:center; margin-top:10px;">
        <?php if ($url_reviewed_id > 0): ?>
            <a href="view_profile.php?id=<?= $url_reviewed_id ?>">← Back to Profile</a>
        <?php else: ?>
            <a href="dashboard.php">← Back to Dashboard</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
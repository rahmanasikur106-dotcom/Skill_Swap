<?php
include 'db.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$msg = '';
$msg_type = '';
$valid_token = false;
$user_id = null;

// Validate token
if (!empty($token)) {
    $stmt = $conn->prepare(
        "SELECT pr.user_id, pr.expires_at
         FROM password_resets pr
         JOIN users u ON u.user_id = pr.user_id
         WHERE pr.token = ? AND u.account_status = 'active'"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (strtotime($row['expires_at']) > time()) {
            $valid_token = true;
            $user_id = $row['user_id'];
        } else {
            $msg = "This reset link has expired. Please request a new one.";
            $msg_type = 'error';
        }
    } else {
        $msg = "This reset link is invalid or has already been used.";
        $msg_type = 'error';
    }
} else {
    $msg = "No reset token provided.";
    $msg_type = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password  = isset($_POST['password'])  ? $_POST['password']  : '';
    $confirm_password = isset($_POST['confirm']) ? $_POST['confirm'] : '';

    if (strlen($new_password) < 8) {
        $msg = "Password must be at least 8 characters.";
        $msg_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $msg = "Passwords do not match.";
        $msg_type = 'error';
    } else {
        $hash = password_hash($new_password, PASSWORD_BCRYPT);

        $upd = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $upd->bind_param("si", $hash, $user_id);

        if ($upd->execute()) {
            // Delete used token
            $del = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->bind_param("i", $user_id);
            $del->execute();

            // Redirect to login with success message
            header("Location: login.php?success=" . urlencode("Password updated! You can now log in."));
            exit();
        } else {
            $msg = "Something went wrong. Please try again.";
            $msg_type = 'error';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-text">⚡ SkillSwap</div>
        </div>

        <h1 class="auth-title">Set new password</h1>
        <p class="auth-subtitle">Choose a strong password for your account.</p>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?= $msg_type === 'error' ? 'error' : 'success' ?>">
                <?= $msg_type === 'error' ? '❌' : '✅' ?> <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($valid_token && $msg_type !== 'success'): ?>
        <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" placeholder="At least 8 characters" required minlength="8">
                <small style="color:var(--text3);font-size:.8rem;margin-top:4px;display:block;">Minimum 8 characters</small>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm" placeholder="Repeat your new password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
                Update Password →
            </button>
        </form>
        <?php elseif (!$valid_token): ?>
            <div style="text-align:center;margin-top:16px;">
                <a href="forgot_password.php" class="btn btn-primary btn-lg">Request a new link</a>
            </div>
        <?php endif; ?>

        <div class="auth-divider">
            <a href="login.php">Back to log in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

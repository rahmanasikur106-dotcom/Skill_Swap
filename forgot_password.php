<?php
include 'db.php';

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $msg = "Please enter your email address.";
        $msg_type = 'error';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, first_name FROM users WHERE email = ? AND account_status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Always show the same success message to prevent email enumeration
        $msg = "If that email is registered, you'll receive a password reset link shortly. Check your inbox (and spam folder).";
        $msg_type = 'success';

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Delete any existing tokens for this user
            $del = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->bind_param("i", $user['user_id']);
            $del->execute();

            // Insert new token
            $ins = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param("iss", $user['user_id'], $token, $expires_at);
            $ins->execute();

            // Build reset link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host     = $_SERVER['HTTP_HOST'];
            $dir      = dirname($_SERVER['SCRIPT_NAME']);
            $reset_link = $protocol . '://' . $host . rtrim($dir, '/') . '/reset_password.php?token=' . $token;

            // Send email using PHP mail()
            $to      = $email;
            $subject = "SkillSwap – Reset your password";
            $name    = htmlspecialchars($user['first_name']);

            $body  = "Hi {$name},\r\n\r\n";
            $body .= "We received a request to reset your SkillSwap password.\r\n\r\n";
            $body .= "Click the link below to set a new password (valid for 1 hour):\r\n";
            $body .= $reset_link . "\r\n\r\n";
            $body .= "If you did not request a password reset, you can safely ignore this email.\r\n\r\n";
            $body .= "— The SkillSwap Team";

            $headers  = "From: no-reply@skillswap.local\r\n";
            $headers .= "Reply-To: no-reply@skillswap.local\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($to, $subject, $body, $headers);
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

        <h1 class="auth-title">Forgot password?</h1>
        <p class="auth-subtitle">Enter your email and we'll send you a reset link.</p>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?= $msg_type === 'error' ? 'error' : 'success' ?>">
                <?= $msg_type === 'error' ? '❌' : '✅' ?> <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg_type !== 'success'): ?>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
                Send Reset Link →
            </button>
        </form>
        <?php endif; ?>

        <div class="auth-divider">
            Remember your password? <a href="login.php">Log in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php 
include 'db.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_msg = "";

// ── 1. PROCESSING LOGIC ON SELF-SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in both email and password.";
    } else {
        // Query the 'users' table safely
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password_hash FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Checks encrypted hashes or falls back to plain-text check for easy development testing
                if (password_verify($password, $user['password_hash']) || $password === $user['password_hash']) {
                    
                    // Set up session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    // Send to your dashboard landing page
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_msg = "Invalid password credential.";
                }
            } else {
                $error_msg = "No account associated with that email address.";
            }
        } else {
            $error_msg = "Database error: " . $conn->error;
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

        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-subtitle">Continue your swapping journey.</p>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <?php if (empty($error_msg) && isset($_GET['error'])): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label" style="display:flex;justify-content:space-between;align-items:center;">
                    Password
                    <a href="forgot_password.php" style="font-size:0.82rem;font-weight:500;color:var(--violet-l);text-transform:none;letter-spacing:0;">Forgot password?</a>
                </label>
                <input type="password" name="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
                Log In →
            </button>
        </form>

        <div class="auth-divider">
            Don't have an account? <a href="register.php">Join free</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
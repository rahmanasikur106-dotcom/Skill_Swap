<?php include 'db.php'; include 'includes/header.php'; ?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-text">⚡ SkillSwap</div>
        </div>

        <h1 class="auth-title">Join SkillSwap</h1>
        <p class="auth-subtitle">Start trading your talents today — it's free.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($_GET['error']) ?></div>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; font-weight: 600;">
                🎉 <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <form action="register_process.php" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" placeholder="johndoe123" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">City (Optional)</label>
                <input type="text" name="city" placeholder="Copenhagen">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" placeholder="Min. 6 characters" required>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px;">
                <span class="badge badge-violet">💻 Coding</span>
                <span class="badge badge-pink">🎨 Design</span>
                <span class="badge badge-cyan">🎸 Guitar</span>
                <span class="badge badge-amber">🍳 Cooking</span>
            </div>

            <button type="submit" name="register" class="btn btn-primary btn-full btn-lg">
                Create Account →
            </button>
        </form>

        <div class="auth-divider">
            Already a member? <a href="login.php">Log in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
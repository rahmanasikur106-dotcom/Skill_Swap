<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillSwap</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<nav class="sk-nav">
    <a href="index.php" class="sk-nav-logo">
    <img src="images/logo.png" alt="SkillSwap Logo">
</a>

    <div class="sk-nav-links">
        <a href="index.php"        <?= basename($_SERVER['PHP_SELF']) === 'index.php'        ? 'class="active"' : '' ?>>Home</a>
        <a href="discovery.php"    <?= basename($_SERVER['PHP_SELF']) === 'discovery.php'    ? 'class="active"' : '' ?>>Discover</a>
        <a href="profile.php"      <?= basename($_SERVER['PHP_SELF']) === 'profile.php'      ? 'class="active"' : '' ?>>Profile</a>
        <a href="chat.php"         <?= in_array(basename($_SERVER['PHP_SELF']), ['chat.php','swaps.php']) ? 'class="active"' : '' ?>>Messages</a>
        <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="notification.php" <?= basename($_SERVER['PHP_SELF']) === 'notification.php' ? 'class="active"' : '' ?>>🔔 Notifications</a>
        <?php endif; ?>
    </div>

    <div class="sk-nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="swaps.php" class="btn btn-sm" style="background:var(--grad-main);color:#fff;">
                💬 Swaps
            </a>
            <a href="dashboard.php" class="btn btn-sm btn-secondary">Dashboard</a>
        <?php else: ?>
            <a href="login.php"    class="btn btn-sm btn-secondary">Log in</a>
            <a href="register.php" class="btn btn-sm btn-primary">Join Free</a>
        <?php endif; ?>

        <button class="theme-toggle" id="themeBtn" aria-label="Toggle theme" title="Toggle light/dark">🌙</button>
    </div>
</nav>

<script>
(function(){
    const html = document.documentElement;
    const btn  = document.getElementById('themeBtn');
    const saved = localStorage.getItem('sk-theme') || 'dark';
    html.setAttribute('data-theme', saved);
    btn.textContent = saved === 'dark' ? '🌙' : '☀️';
    btn.addEventListener('click', function(){
        const cur  = html.getAttribute('data-theme');
        const next = cur === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('sk-theme', next);
        btn.textContent = next === 'dark' ? '🌙' : '☀️';
    });
})();
</script>
<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$status = '';

if (isset($_POST['create'])) {

    $location = $_POST['location'];
    $date_time = $_POST['date_time'];

    // SAFE SQL (correct syntax)
    $stmt = $conn->prepare("
        INSERT INTO events (creator_id, location, date_time)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iss", $user_id, $location, $date_time);

    if ($stmt->execute()) {
        $message = "🎉 Event created successfully!";
        $status = "success";
    } else {
        $message = "❌ Error creating event.";
        $status = "error";
    }
}

include 'includes/header.php';
?>

<style>
/* Scoped styling to blend seamlessly with the SkillSwap interface */
.ev-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 100px);
    background: #0f0f13;
    font-family: 'Segoe UI', system-ui, sans-serif;
    padding: 20px;
}

.ev-card {
    background: #16161d;
    border: 1px solid #2a2a35;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    padding: 35px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.ev-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.5px;
    margin-bottom: 8px;
    text-align: center;
}

.ev-subtitle {
    font-size: 0.88rem;
    color: #8a8a9e;
    text-align: center;
    margin-bottom: 25px;
}

.ev-alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.88rem;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 600;
}
.ev-alert.success { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
.ev-alert.error { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

.ev-group {
    margin-bottom: 20px;
}

.ev-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: #a0a0b0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}

.ev-input {
    width: 100%;
    background: #1e1e28;
    border: 1px solid #2a2a35;
    border-radius: 12px;
    padding: 12px 16px;
    color: #fff;
    font-size: 0.95rem;
    outline: none;
    transition: all 0.2s ease;
}

.ev-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.ev-input::-webkit-calendar-picker-indicator {
    filter: invert(1); /* Makes the calendar clock icon white in dark mode */
    cursor: pointer;
}

.ev-btn-submit {
    width: 100%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: transform 0.1s, opacity 0.2s;
    margin-top: 10px;
}

.ev-btn-submit:hover {
    opacity: 0.95;
}

.ev-btn-submit:active {
    transform: scale(0.98);
}

.ev-back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 0.88rem;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.ev-back-link:hover {
    color: #8b5cf6;
}
</style>

<div class="ev-container">
    <div class="ev-card">
        <div class="ev-title">📅 Create Event</div>
        <div class="ev-subtitle">Host a session to share knowledge and swap skills</div>

        <?php if (!empty($message)): ?>
            <div class="ev-alert <?= $status ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="ev-group">
                <label class="ev-label">📍 Location / Platform</label>
                <input class="ev-input" name="location" placeholder="e.g., Zoom, Discord, Room 402..." required>
            </div>

            <div class="ev-group">
                <label class="ev-label">⏰ Date &amp; Time</label>
                <input class="ev-input" name="date_time" type="datetime-local" required>
            </div>

            <button class="ev-btn-submit" name="create">Create Event</button>
        </form>

        <a href="dashboard.php" class="ev-back-link">← Back</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
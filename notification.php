<?php 
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| MARK NOTIFICATIONS AS READ
|--------------------------------------------------------------------------
*/
$update_read = $conn->prepare("
    UPDATE notification
    SET is_read = 1
    WHERE user_id = ? AND is_read = 0
");

$update_read->bind_param("i", $user_id);
$update_read->execute();
$update_read->close();

include 'includes/header.php'; 
?>

<style>
/* Scoped styling to blend seamlessly with the SkillSwap interface */

.notif-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 100px);
    background: #0f0f13;
    font-family: 'Segoe UI', system-ui, sans-serif;
    padding: 20px;
}

.notif-card {
    background: #16161d;
    border: 1px solid #2a2a35;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    padding: 35px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.notif-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.5px;
    margin-bottom: 8px;
    text-align: center;
}

.notif-subtitle {
    font-size: 0.88rem;
    color: #8a8a9e;
    text-align: center;
    margin-bottom: 25px;
}

.notif-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.notif-item {
    background: #1e1e28;
    border: 1px solid #2a2a35;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    transition: transform 0.2s ease, border-color 0.2s ease;
}

.notif-item:hover {
    border-color: #6366f1;
    transform: translateY(-2px);
}

.notif-icon {
    font-size: 1.2rem;
    background: rgba(99, 102, 241, 0.1);
    padding: 8px;
    border-radius: 10px;
    color: #6366f1;
    flex-shrink: 0;
}

.notif-content {
    flex-grow: 1;
}

.notif-text {
    color: #fff;
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 4px;
}

.notif-time {
    display: block;
    font-size: 0.78rem;
    color: #8a8a9e;
}

.notif-empty {
    padding: 40px 20px;
    text-align: center;
    color: #8a8a9e;
    font-size: 0.95rem;
}

.notif-back-link {
    display: block;
    text-align: center;
    margin-top: 10px;
    font-size: 0.88rem;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.notif-back-link:hover {
    color: #8b5cf6;
}
</style>

<div class="notif-container">
    <div class="notif-card">

        <div class="notif-title">🔔 My Notifications</div>

        <div class="notif-subtitle">
            Stay updated on your latest skill swaps and updates
        </div>

        <div class="notif-list">

            <?php

            /*
            |--------------------------------------------------------------------------
            | FETCH NOTIFICATIONS
            |--------------------------------------------------------------------------
            */

            $sql = "
    SELECT
        notification_id,
        message_text AS message,
        created_at,
        type,
        is_read
    FROM notification
    WHERE user_id = ?
    ORDER BY created_at DESC
";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $message = htmlspecialchars($row['message']);
                    $created_at = htmlspecialchars($row['created_at']);
                    $type = $row['type'];
            ?>

                    <div class="notif-item">

                        <div class="notif-icon">

                            <?php
                           switch ($type) {
    case 'swap_request':    echo '🤝'; break;
    case 'message':         echo '💬'; break;
    case 'session_invite':  echo '📅'; break;
    case 'session_accepted':echo '✅'; break;
    case 'session_rejected':echo '❌'; break;
    default:                echo '📩';
}
                            ?>

                        </div>

                        <div class="notif-content">

                            <div class="notif-text">
                                <?= $message ?>
                            </div>

                            <span class="notif-time">
                                <?= $created_at ?>
                            </span>

                        </div>

                    </div>

            <?php
                }

            } else {

                echo "
                    <div class='notif-empty'>
                        👋 No notifications yet.
                    </div>
                ";
            }

            $stmt->close();
            ?>

        </div>

        <a href="dashboard.php" class="notif-back-link">
            ← Back to Dashboard
        </a>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
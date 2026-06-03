<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (empty($_GET['event_id'])) { header("Location: dashboard.php"); exit(); }

$event_id = (int)$_GET['event_id'];

// Fetch event info
$ev = $conn->prepare("SELECT location, date_time FROM events WHERE event_id = ?");
$ev->bind_param("i", $event_id);
$ev->execute();
$event = $ev->get_result()->fetch_assoc();

// Fetch participants
$stmt = $conn->prepare("
    SELECT CONCAT(u.first_name, ' ', u.last_name) AS name, u.user_location
    FROM event_participant ep
    JOIN users u ON u.user_id = ep.user_id
    WHERE ep.event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>
<style>
.part-container { display:flex; justify-content:center; align-items:center; min-height:calc(100vh - 100px); background:#0f0f13; font-family:'Segoe UI',system-ui,sans-serif; padding:20px; }
.part-card { background:#16161d; border:1px solid #2a2a35; border-radius:20px; width:100%; max-width:500px; padding:35px; box-shadow:0 10px 30px rgba(0,0,0,0.5); }
.part-title { font-size:1.6rem; font-weight:800; color:#fff; margin-bottom:6px; text-align:center; }
.part-subtitle { font-size:0.88rem; color:#8a8a9e; text-align:center; margin-bottom:25px; }
.part-event-info { background:#1e1e28; border:1px solid #2a2a35; border-radius:10px; padding:12px 16px; margin-bottom:20px; font-size:0.85rem; color:#a0a0b0; }
.part-list { background:#1e1e28; border:1px solid #2a2a35; border-radius:12px; padding:10px; margin-bottom:20px; max-height:300px; overflow-y:auto; }
.part-item { display:flex; align-items:center; gap:12px; padding:12px 16px; color:#fff; font-size:0.95rem; border-bottom:1px solid #2a2a35; }
.part-item:last-child { border-bottom:none; }
.part-avatar { width:36px; height:36px; background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:50%; display:flex; justify-content:center; align-items:center; font-weight:700; font-size:0.9rem; color:#fff; flex-shrink:0; }
.part-empty { padding:30px 20px; text-align:center; color:#8a8a9e; font-size:0.95rem; }
.part-back-link { display:block; text-align:center; margin-top:10px; font-size:0.88rem; color:#6366f1; text-decoration:none; font-weight:600; }
.part-back-link:hover { color:#8b5cf6; }
</style>

<div class="part-container">
    <div class="part-card">
        <div class="part-title">👥 Event Participants</div>
        <div class="part-subtitle">Members attending this skill swap session</div>

        <?php if ($event): ?>
        <div class="part-event-info">
            📍 <?= htmlspecialchars($event['location']) ?> &nbsp;·&nbsp;
            ⏰ <?= date('D d M Y, H:i', strtotime($event['date_time'])) ?>
        </div>
        <?php endif; ?>

        <div class="part-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="part-item">
                        <div class="part-avatar"><?= strtoupper(substr($row['name'], 0, 1)) ?></div>
                        <div>
                            <div style="font-weight:600;"><?= htmlspecialchars($row['name']) ?></div>
                            <?php if (!empty($row['user_location'])): ?>
                                <div style="font-size:0.78rem;color:#505060;">📍 <?= htmlspecialchars($row['user_location']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="part-empty">👋 No participants joined yet.</div>
            <?php endif; ?>
        </div>

        <a href="dashboard.php" class="part-back-link">← Back to Dashboard</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
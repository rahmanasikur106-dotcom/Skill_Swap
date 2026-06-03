<?php
session_start();
include "db.php";

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    die("Error: Event ID is missing. Please go back to dashboard and click a valid event.");
}

$event_id = (int)$_GET['event_id'];

$sql = "
SELECT users.name 
FROM event_participant
JOIN users ON users.user_id = event_participant.user_id
WHERE event_participant.event_id = $event_id
";

$result = $conn->query($sql);

include 'includes/header.php';
?>

<style>
/* Scoped styling to blend seamlessly with the SkillSwap interface */
.part-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 100px);
    background: #0f0f13;
    font-family: 'Segoe UI', system-ui, sans-serif;
    padding: 20px;
}

.part-card {
    background: #16161d;
    border: 1px solid #2a2a35;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    padding: 35px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.part-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.5px;
    margin-bottom: 8px;
    text-align: center;
}

.part-subtitle {
    font-size: 0.88rem;
    color: #8a8a9e;
    text-align: center;
    margin-bottom: 25px;
}

.part-list {
    background: #1e1e28;
    border: 1px solid #2a2a35;
    border-radius: 12px;
    padding: 10px;
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
}

.part-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #fff;
    font-size: 0.95rem;
    border-bottom: 1px solid #2a2a35;
}

.part-item:last-child {
    border-bottom: none;
}

.part-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: 700;
    font-size: 0.85rem;
    color: #fff;
}

.part-empty {
    padding: 30px 20px;
    text-align: center;
    color: #8a8a9e;
    font-size: 0.95rem;
}

.part-back-link {
    display: block;
    text-align: center;
    margin-top: 10px;
    font-size: 0.88rem;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.part-back-link:hover {
    color: #8b5cf6;
}
</style>

<div class="part-container">
    <div class="part-card">
        <div class="part-title">👥 Event Participants</div>
        <div class="part-subtitle">Members attending this skill swap session</div>

        <div class="part-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Extract the first letter of the name for a dynamic clean avatar
                    $first_letter = strtoupper(substr($row['name'], 0, 1));
                    echo "<div class='part-item'>";
                    echo "  <div class='part-avatar'>" . $first_letter . "</div>";
                    echo "  <span>" . htmlspecialchars($row['name']) . "</span>";
                    echo "</div>";
                }
            } else {
                echo "<div class='part-empty'>👋 No participants joined yet.</div>";
            }
            ?>
        </div>

        <a href="dashboard.php" class="part-back-link">← Back</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php 
include 'db.php'; 
include 'includes/header.php';

$typeFilter = "";
if (isset($_GET['filter_type']) && $_GET['filter_type'] != "") {
    $type = $_GET['filter_type'];
    if ($type == "Teach") {
        $typeFilter = " AND us1.type_name = 'Teach' AND us2.type_name = 'Learn'";
    }
    if ($type == "Learn") {
        $typeFilter = " AND us1.type_name = 'Learn' AND us2.type_name = 'Teach'";
    }
}

// ── FIXED SQL QUERY: Mapped smoothly to skill_types, type_id, and type_name
$sql = "
SELECT 
    CONCAT(u1.first_name, ' ', u1.last_name) AS teacher,
    CONCAT(u2.first_name, ' ', u2.last_name) AS learner,
    st.type_name AS skill_name,
    us1.level_name AS skill_level,
    (
        40 +
        CASE 
            WHEN (us1.type_name = 'Teach' AND us2.type_name = 'Learn')
              OR (us1.type_name = 'Learn' AND us2.type_name = 'Teach')
            THEN 30 ELSE 0
        END +
        CASE 
            WHEN us1.level_name = us2.level_name THEN 20
            ELSE 10
        END
    ) AS match_score
FROM user_skills us1
JOIN user_skills us2 
    ON us1.skill_id = us2.skill_id
    AND us1.user_id < us2.user_id
JOIN users u1 ON us1.user_id = u1.user_id
JOIN users u2 ON us2.user_id = u2.user_id
JOIN skill_types st ON us1.skill_id = st.type_id
WHERE 1=1 $typeFilter
ORDER BY match_score DESC";

$result = $conn->query($sql);
?>

<style>
.tbl-wrap { max-width: 1000px; margin: 40px auto; padding: 0 20px 60px; }
.filter-bar { background: #16161d; border: 1px solid #2a2a35; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
.filter-bar label { font-size: 0.85rem; font-weight: 700; color: #6366f1; text-transform: uppercase; }
.filter-select { background: #0f0f13; border: 1px solid #2a2a35; color: #e0e0f0; border-radius: 8px; padding: 8px 12px; font-size: 0.9rem; }
.btn-filter { background: #1e1e28; color: #a0a0b0; border: 1px solid #2a2a35; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem; }
.btn-filter:hover { background: #252532; color: #e0e0f0; }
.match-table { width: 100%; border-collapse: collapse; background: #16161d; border: 1px solid #2a2a35; border-radius: 14px; overflow: hidden; }
.match-table th { background: #0f0f13; color: #6366f1; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; font-weight: 700; text-align: left; padding: 16px; border-bottom: 1px solid #2a2a35; }
.match-table td { padding: 16px; color: #a0a0b0; font-size: 0.9rem; border-bottom: 1px solid #2a2a35; }
.match-table tr:last-child td { border-bottom: none; }
.score-badge { background: rgba(16,185,129,0.12); color: #34d399; font-weight: 700; padding: 4px 8px; border-radius: 6px; display: inline-block; }
</style>

<div class="tbl-wrap">
    <h2 style="color: #e0e0f0; margin-bottom: 24px; font-weight: 800;">🌐 Global Community Matches</h2>

    <form method="GET" class="filter-bar">
        <label>Filter Direction:</label>
        <select name="filter_type" class="filter-select">
            <option value="">All Connections</option>
            <option value="Teach" <?= isset($_GET['filter_type']) && $_GET['filter_type'] == 'Teach' ? 'selected' : '' ?>>Teach matches</option>
            <option value="Learn" <?= isset($_GET['filter_type']) && $_GET['filter_type'] == 'Learn' ? 'selected' : '' ?>>Learn matches</option>
        </select>
        <button type="submit" class="btn-filter">Apply Filter</button>
    </form>

    <table class="match-table">
        <thead>
            <tr>
                <th>Teacher</th>
                <th>Learner</th>
                <th>Exchanged Skill</th>
                <th>Experience Level</th>
                <th>Match Score</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#e0e0f0; font-weight:600;"><?= htmlspecialchars($row['teacher']) ?></td>
                        <td><?= htmlspecialchars($row['learner']) ?></td>
                        <td><span style="color:#818cf8; font-weight:600;"><?= htmlspecialchars($row['skill_name']) ?></span></td>
                        <td><?= htmlspecialchars($row['skill_level']) ?></td>
                        <td><span class="score-badge"><?= htmlspecialchars($row['match_score']) ?> pts</span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding:32px; color:#404050; font-style:italic;">No cross-profile matches exist in the system registry yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
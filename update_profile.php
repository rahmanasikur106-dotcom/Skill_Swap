<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$bio      = trim($_POST['bio']      ?? '');
$location = trim($_POST['location'] ?? '');

// 1. Update bio and location
$stmt = $conn->prepare("UPDATE users SET bio = ?, user_location = ? WHERE user_id = ?");
$stmt->bind_param("ssi", $bio, $location, $user_id);
$ok = $stmt->execute();

if (!$ok) {
    header("Location: profile.php?error=1");
    exit();
}

// 2. Wipe existing skills and re-insert from checkboxes
$del = $conn->prepare("DELETE FROM user_skills WHERE user_id = ?");
$del->bind_param("i", $user_id);
$del->execute();

$insert = $conn->prepare("INSERT INTO user_skills (user_id, skill_id, level_name, type_name) VALUES (?, ?, 'Beginner', ?)");

$teach_ids = isset($_POST['teach_skills']) && is_array($_POST['teach_skills']) ? $_POST['teach_skills'] : [];
$learn_ids = isset($_POST['learn_skills']) && is_array($_POST['learn_skills']) ? $_POST['learn_skills'] : [];

foreach ($teach_ids as $sid) {
    $sid = (int)$sid;
    $t   = 'Teach';
    $insert->bind_param("iis", $user_id, $sid, $t);
    $insert->execute();
}
foreach ($learn_ids as $sid) {
    $sid = (int)$sid;
    $t   = 'Learn';
    $insert->bind_param("iis", $user_id, $sid, $t);
    $insert->execute();
}

// 3. Handle custom (typed) skills — insert into skills table if new, then link
$custom_map = [
    'Teach' => isset($_POST['custom_teach_skills']) && is_array($_POST['custom_teach_skills'])
                ? $_POST['custom_teach_skills'] : [],
    'Learn' => isset($_POST['custom_learn_skills']) && is_array($_POST['custom_learn_skills'])
                ? $_POST['custom_learn_skills'] : [],
];

$find_skill = $conn->prepare("SELECT skill_id FROM skills WHERE LOWER(skill_name) = LOWER(?)");
$new_skill  = $conn->prepare("INSERT INTO skills (skill_name) VALUES (?)");
$dup_check  = $conn->prepare("SELECT 1 FROM user_skills WHERE user_id=? AND skill_id=? AND type_name=?");
$link_skill = $conn->prepare("INSERT INTO user_skills (user_id, skill_id, level_name, type_name) VALUES (?, ?, 'Beginner', ?)");

foreach ($custom_map as $type => $names) {
    foreach ($names as $raw) {
        $name = trim($raw);
        if ($name === '' || mb_strlen($name) > 100) continue;

        // Find or create the skill row
        $find_skill->bind_param("s", $name);
        $find_skill->execute();
        $row = $find_skill->get_result()->fetch_assoc();

        if ($row) {
            $skill_id = (int)$row['skill_id'];
        } else {
            $new_skill->bind_param("s", $name);
            $new_skill->execute();
            $skill_id = (int)$conn->insert_id;
        }

        // Only link if not already linked by a checkbox
        $dup_check->bind_param("iis", $user_id, $skill_id, $type);
        $dup_check->execute();
        if ($dup_check->get_result()->num_rows === 0) {
            $link_skill->bind_param("iis", $user_id, $skill_id, $type);
            $link_skill->execute();
        }
    }
}

// 4. Clear custom skills from session — they are now in the DB
unset($_SESSION['custom_skills']);

// 5. Redirect to view_profile so the user sees the updated result immediately
header("Location: view_profile.php?id=" . $user_id . "&updated=1");
exit();
?>
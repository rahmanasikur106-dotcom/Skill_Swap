<?php
include 'db.php';

$user_id = $_POST['user_id'];
$skill_id = $_POST['skill_id'];
$level_name = $_POST['level_name'];
$type_name = $_POST['type_name'];

if (empty($user_id) || empty($skill_id) || empty($level_name) || empty($type_name)) {
    header("Location: index.php?msg=All fields are required&type=error");
    exit;
}

$check = $conn->prepare("SELECT * FROM user_skills WHERE user_id=? AND skill_id=? AND type_name=?");
$check->bind_param("iis", $user_id, $skill_id, $type_name);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header("Location: index.php?msg=Skill already exists&type=error");
    exit;
}

$stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id, level_name, type_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $user_id, $skill_id, $level_name, $type_name);

if ($stmt->execute()) {
    header("Location: index.php?msg=Skill added successfully&type=success");
} else {
    header("Location: index.php?msg=Error adding skill&type=error");
}
exit;
?>
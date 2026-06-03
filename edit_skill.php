<?php
include 'db.php';

$user_skill_id = $_POST['user_skill_id'];
$level_name = $_POST['level_name'];
$type_name = $_POST['type_name'];

if (empty($user_skill_id)) {
    header("Location: index.php?msg=ID is required&type=error");
    exit;
}

$stmt = $conn->prepare("UPDATE user_skills SET level_name=?, type_name=? WHERE user_skill_id=?");
$stmt->bind_param("ssi", $level_name, $type_name, $user_skill_id);

if ($stmt->execute()) {
    header("Location: index.php?msg=Skill updated successfully&type=success");
} else {
    header("Location: index.php?msg=Error updating skill&type=error");
}
exit;
?>
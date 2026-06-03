<?php
include 'db.php';

$user_skill_id = $_POST['user_skill_id'];

if (empty($user_skill_id)) {
    header("Location: index.php?msg=ID is required&type=error");
    exit;
}

$stmt = $conn->prepare("DELETE FROM user_skills WHERE user_skill_id = ?");
$stmt->bind_param("i", $user_skill_id);

if ($stmt->execute()) {
    header("Location: index.php?msg=Skill deleted successfully&type=success");
} else {
    header("Location: index.php?msg=Error deleting skill&type=error");
}
exit;
?>
<?php
include 'db.php';

// TEMP: static user (replace later with session)
$user_id = 1;

// Prepare SQL query - Removed the joins for level and type since names are now in user_skills
$sql = "SELECT 
            us.user_skill_id, 
            s.skill_name, 
            us.level_name, 
            us.type_name
        FROM user_skills us
        JOIN skills s ON us.skill_id = s.skill_id
        WHERE us.user_id = ?";

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// This file is used inside index.php
?>
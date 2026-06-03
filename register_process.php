<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $password = $_POST['password'];

    // Validations
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
        header("Location: register.php?error=" . urlencode("All required fields must be filled."));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=" . urlencode("Invalid email format."));
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: register.php?error=" . urlencode("Password must be at least 6 characters long."));
        exit();
    }

    // Check if username or email already exists in users table
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: register.php?error=" . urlencode("Username or Email is already taken."));
        exit();
    }
    $stmt->close();

    // Securely hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into your exact database layout
    $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, city) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssss", $username, $email, $password_hash, $first_name, $last_name, $city);

    if ($insert_stmt->execute()) {
        header("Location: register.php?success=" . urlencode("Account created! Please log in below."));
        exit();
    } else {
        header("Location: register.php?error=" . urlencode("Registration failed. Please try again."));
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
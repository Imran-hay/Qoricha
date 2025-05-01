<?php
session_start();
require 'config.php'; // Include database connection

// Check if the user is logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
   // header("Location: login.php");
    exit();
}

// Predefined Admin accounts
$admins = [
    ['fullname' => 'Fekadu Abebe', 'email' => 'Fekaduabebe90@gmail.com', 'password' => 'Feke1234'],
    ['fullname' => 'Admin Two', 'email' => 'admin2@example.com', 'password' => 'adminpassword2'],
];

foreach ($admins as $admin) {
    // Hash the password
    $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkEmail->execute([$admin['email']]);
    if ($checkEmail->fetchColumn() == 0) {
        // Insert new Admin record
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, role, password) VALUES (?, ?, 'admin', ?)");
        $stmt->execute([$admin['fullname'], $admin['email'], $hashed_password]);
    }
}

// Redirect to the Admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
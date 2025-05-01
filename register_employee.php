<?php
session_start();

// Include database configuration
require 'config.php'; // Ensure this path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $region = $_POST['region'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $tin = $_POST['tin'];
    $joining_date = $_POST['joining_date'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='register_employee.php';</script>";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetchColumn() > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='register_employee.php';</script>";
        exit;
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, region, address, phone, tin, joining_date, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bindParam(1, $fullname);
    $stmt->bindParam(2, $email);
    $stmt->bindParam(3, $region);
    $stmt->bindParam(4, $address);
    $stmt->bindParam(5, $phone);
    $stmt->bindParam(6, $tin);
    $stmt->bindParam(7, $joining_date);
    $stmt->bindParam(8, $role);
    $stmt->bindParam(9, $hashed_password);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('User registration successful!'); window.location.href='login.php';</script>";
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "<script>alert('Registration failed: " . $errorInfo[2] . "'); window.location.href='register_employee.php';</script>";
    }
}
?>
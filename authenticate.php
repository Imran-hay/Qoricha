<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php'; 
try {
    $pdo = new PDO("mysql:host=localhost;dbname=qorichadb", "root", "");
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

   
    if ($user && password_verify($password, $user['password'])) {
       
        $_SESSION['user_id'] = $user['user name'];
        $_SESSION['role'] = $user['role']; 

        
        switch ($user['role']) {
            case 'agent':
                header("Location: agent_dashboard.php");
                break;
            case 'cashier':
                header("Location: cashier_dashboard.php");
                break;
            case 'storeman':
                header("Location: storeman_dashboard.php");
                break;
            case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
            default:
                header("Location: default_dashboard.php"); // Fallback
                break;
        }
        exit();
    } else {
        echo "<script>alert('Invalid email or password!'); window.location.href='login.php';</script>";
        exit();
    }
}
?>
<?php
/**
 * Simple Login Handler - Traditional PHP Form Processing
 */

// Start session
session_start();

// Database connection
$host = 'localhost';
$dbname = 'edumind';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed");
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($user) || empty($pass)) {
        $_SESSION['error'] = 'Please enter username and password';
        header('Location: ../Views/front-office/login.php');
        exit;
    }
    
    // Determine table
    if ($role === 'teacher') {
        $table = 'teachers';
        $redirect = '../Views/teacher-back-office/dashboard.php';
    } elseif ($role === 'admin') {
        $table = 'admins';
        $redirect = '../Views/admin-back-office/dashboard.php';
    } else {
        $table = 'students';
        $redirect = '../Views/front-office/dashboard.php';
    }
    
    // Query user
    $sql = "SELECT * FROM $table WHERE username = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Check password
        $valid = false;
        if (password_verify($pass, $result['password'])) {
            $valid = true;
        } elseif ($pass === $result['password']) {
            $valid = true;
        }
        
        if ($valid) {
            // Update last login
            $update = "UPDATE $table SET lastLoginAt = NOW() WHERE id = ?";
            $stmt2 = $pdo->prepare($update);
            $stmt2->execute([$result['id']]);
            
            // Set session
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['role'] = $role;
            $_SESSION['full_name'] = $result['fullName'] ?? $result['name'] ?? $result['username'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            header('Location: ' . $redirect);
            exit;
        }
    }
    
    $_SESSION['error'] = 'Invalid username or password';
    header('Location: ../Views/front-office/login.php');
    exit;
}

header('Location: ../Views/front-office/login.php');
exit;
?>

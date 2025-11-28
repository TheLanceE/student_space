<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/teacher-back-office/login.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ../Views/teacher-back-office/login.php?error=empty');
    exit;
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=edumind;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query teacher
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($teacher) {
        // Check password
        $passwordValid = password_verify($password, $teacher['password']) || $password === $teacher['password'];
        
        if ($passwordValid) {
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE teachers SET lastLoginAt = NOW() WHERE id = ?");
            $updateStmt->execute([$teacher['id']]);
            
            // Set session
            $_SESSION['user_id'] = $teacher['id'];
            $_SESSION['username'] = $teacher['username'];
            $_SESSION['role'] = 'teacher';
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            header('Location: ../Views/teacher-back-office/dashboard.php');
            exit;
        }
    }
    
    // Invalid credentials
    header('Location: ../Views/teacher-back-office/login.php?error=invalid');
    exit;
    
} catch (PDOException $e) {
    header('Location: ../Views/teacher-back-office/login.php?error=db');
    exit;
}
?>

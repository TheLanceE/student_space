<?php
require_once __DIR__ . '/config.php';

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

// Database connection already available as $db_connection from config.php
try {
    // Query teacher
    $stmt = $db_connection->prepare("SELECT * FROM teachers WHERE username = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($teacher) {
        // Check password
        $passwordValid = password_verify($password, $teacher['password']) || $password === $teacher['password'];
        
        if ($passwordValid) {
            // Update last login
            $updateStmt = $db_connection->prepare("UPDATE teachers SET lastLoginAt = NOW() WHERE id = ?");
            $updateStmt->execute([$teacher['id']]);
            
            // Set session
            $_SESSION['user_id'] = $teacher['id'];
            $_SESSION['username'] = $teacher['username'];
            $_SESSION['role'] = 'teacher';
            $_SESSION['email'] = $teacher['email'] ?? '';
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

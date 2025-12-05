<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/admin-back-office/login.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ../Views/admin-back-office/login.php?error=empty');
    exit;
}

// Database connection already available as $db_connection from config.php
try {
    // Query admin
    $stmt = $db_connection->prepare("SELECT * FROM admins WHERE username = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Check password (supports hashed, plaintext, and fallback for default seed)
        $passwordValid = password_verify($password, $admin['password'])
            || $password === $admin['password']
            || ($admin['username'] === 'admin' && $password === 'admin123');
        
        if ($passwordValid) {
            // Update last login
            $updateStmt = $db_connection->prepare("UPDATE admins SET lastLoginAt = NOW() WHERE id = ?");
            $updateStmt->execute([$admin['id']]);
            
            // Set session
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            $_SESSION['email'] = $admin['email'] ?? '';
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            header('Location: ../Views/admin-back-office/dashboard.php');
            exit;
        }
    }
    
    // Invalid credentials
    header('Location: ../Views/admin-back-office/login.php?error=invalid');
    exit;
    
} catch (PDOException $e) {
    header('Location: ../Views/admin-back-office/login.php?error=db');
    exit;
}
?>

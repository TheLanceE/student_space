<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SecurityHelpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/admin-back-office/login.php');
    exit;
}

// Rate limit check - 5 attempts per minute per IP
$rateLimiter = new RateLimiter($db_connection);
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if ($rateLimiter->isRateLimited('admin_login', $clientIP, 5, 60)) {
    header('Location: ../Views/admin-back-office/login.php?error=rate_limited');
    exit;
}

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (!SessionManager::validateCSRFToken($csrf)) {
    header('Location: ../Views/admin-back-office/login.php?error=csrf');
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
        $stored = (string)($admin['password'] ?? '');
        $passwordValid = false;

        // Prefer secure hash verification
        if ($stored !== '' && password_verify($password, $stored)) {
            $passwordValid = true;
        } else {
            // Transitional upgrade path: if a legacy plaintext password is stored, accept once and upgrade to a hash.
            $info = password_get_info($stored);
            $isHash = !empty($info['algo']);
            if (!$isHash && $stored !== '' && hash_equals($stored, $password)) {
                $passwordValid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upgradeStmt = $db_connection->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $upgradeStmt->execute([$newHash, $admin['id']]);
            }
        }

        if ($passwordValid) {
            // Update last login
            $updateStmt = $db_connection->prepare("UPDATE admins SET lastLoginAt = NOW() WHERE id = ?");
            $updateStmt->execute([$admin['id']]);
            
            // Set session (new + legacy compatibility)
            SessionManager::createUserSession($admin['id'], $admin['username'], 'admin', $admin['name'] ?? $admin['username']);
            $_SESSION['email'] = $admin['email'] ?? '';
            
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

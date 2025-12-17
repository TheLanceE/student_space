<?php
/**
 * Simple Login Handler - Traditional PHP Form Processing
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SecurityHelpers.php';

// Database connection already available as $db_connection from config.php
// Initialize rate limiter
$rateLimiter = new RateLimiter($db_connection);

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    // Rate limit check - 5 attempts per minute per IP
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if ($rateLimiter->isRateLimited('login', $clientIP, 5, 60)) {
        $_SESSION['error'] = 'Too many login attempts. Please wait a minute before trying again.';
        if ($role === 'teacher') {
            header('Location: ../Views/teacher-back-office/login.php?error=rate_limited');
        } elseif ($role === 'admin') {
            header('Location: ../Views/admin-back-office/login.php?error=rate_limited');
        } else {
            header('Location: ../Views/front-office/login.php?error=rate_limited');
        }
        exit;
    }

    // CSRF validation
    $csrf = $_POST['csrf_token'] ?? '';
    if (!SessionManager::validateCSRFToken($csrf)) {
        $_SESSION['error'] = 'Invalid session token. Please try again.';
        if ($role === 'teacher') {
            header('Location: ../Views/teacher-back-office/login.php?error=csrf');
        } elseif ($role === 'admin') {
            header('Location: ../Views/admin-back-office/login.php?error=csrf');
        } else {
            header('Location: ../Views/front-office/login.php?error=csrf');
        }
        exit;
    }
    
    if (empty($user) || empty($pass)) {
        $_SESSION['error'] = 'Please enter username and password';
        header('Location: ../Views/front-office/login.php');
        exit;
    }
    
    // Explicit table name whitelist (prevents SQL injection)
    $roleConfig = [
        'teacher' => [
            'table' => 'teachers',
            'redirect' => '../Views/teacher-back-office/dashboard.php',
            'loginRedirect' => '../Views/teacher-back-office/login.php'
        ],
        'admin' => [
            'table' => 'admins',
            'redirect' => '../Views/admin-back-office/dashboard.php',
            'loginRedirect' => '../Views/admin-back-office/login.php'
        ],
        'student' => [
            'table' => 'students',
            'redirect' => '../Views/front-office/dashboard.php',
            'loginRedirect' => '../Views/front-office/login.php'
        ]
    ];
    
    // Default to student if invalid role provided
    $config = $roleConfig[$role] ?? $roleConfig['student'];
    $role = array_key_exists($role, $roleConfig) ? $role : 'student';
    $table = $config['table'];
    $redirect = $config['redirect'];
    $loginRedirect = $config['loginRedirect'];
    
    // Query user
    $sql = "SELECT * FROM $table WHERE username = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00') LIMIT 1";
    $stmt = $db_connection->prepare($sql);
    $stmt->execute([$user]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Check password
        $valid = false;
        if (password_verify($pass, (string)($result['password'] ?? ''))) {
            $valid = true;
        }
        
        if ($valid) {
            // Update last login
            $update = "UPDATE $table SET lastLoginAt = NOW() WHERE id = ?";
            $stmt2 = $db_connection->prepare($update);
            $stmt2->execute([$result['id']]);
            
            // Create secure session (new shape) and also set legacy keys (old shape)
            $fullName = $result['fullName'] ?? $result['name'] ?? $result['username'];
            SessionManager::createUserSession($result['id'], $result['username'], $role, $fullName);

            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['role'] = $role;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $result['email'] ?? '';
            // Ensure CSRF token exists for subsequent POSTs
            SessionManager::generateCSRFToken();
            
            header('Location: ' . $redirect);
            exit;
        }
    }
    
    $_SESSION['error'] = 'Invalid username or password';
    header('Location: ' . $loginRedirect);
    exit;
}

header('Location: ../Views/front-office/login.php');
exit;


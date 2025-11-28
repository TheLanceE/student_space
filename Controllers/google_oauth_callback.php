<?php
/**
 * Google OAuth Callback Handler
 * Processes OAuth response and creates/logs in user
 */

require_once __DIR__ . '/GoogleOAuthHandler.php';

session_start();

// Validate state to prevent CSRF
if (!isset($_GET['code'])) {
    header('Location: ../Views/front-office/login.php?error=oauth_failed');
    exit;
}

$code = $_GET['code'];
$role = $_SESSION['oauth_role'] ?? 'student'; // Default to student

try {
    // Initialize OAuth handler
    $oauth = new GoogleOAuthHandler(
        $db_connection,
        GOOGLE_CLIENT_ID,
        GOOGLE_CLIENT_SECRET,
        GOOGLE_REDIRECT_URI
    );
    
    // Exchange code for token
    $token_data = $oauth->getAccessToken($code);
    
    if (!$token_data || !isset($token_data['access_token'])) {
        throw new Exception('Failed to get access token');
    }
    
    // Get user info from Google
    $google_user = $oauth->getUserInfo($token_data['access_token']);
    
    if (!$google_user) {
        throw new Exception('Failed to get user info');
    }
    
    // Find or create user
    $user = $oauth->findOrCreateUser($google_user, $role);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['auth_method'] = 'google';
    $_SESSION['last_activity'] = time();
    
    // Update last login
    $stmt = $db_connection->prepare("UPDATE {$role}s SET lastLoginAt = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Redirect based on role
    if ($role === 'student') {
        header('Location: ../Views/front-office/dashboard.php');
    } elseif ($role === 'teacher') {
        header('Location: ../Views/teacher-back-office/dashboard.php');
    } else {
        header('Location: ../Views/admin-back-office/dashboard.php');
    }
    exit;
    
} catch (Exception $e) {
    error_log('OAuth error: ' . $e->getMessage());
    
    if ($role === 'student') {
        header('Location: ../Views/front-office/login.php?error=oauth_failed');
    } elseif ($role === 'teacher') {
        header('Location: ../Views/teacher-back-office/login.php?error=oauth_failed');
    } else {
        header('Location: ../Views/admin-back-office/login.php?error=oauth_failed');
    }
    exit;
}
?>

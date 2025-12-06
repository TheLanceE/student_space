<?php
/**
 * Google OAuth Callback Handler
 * Processes OAuth response and creates/logs in user
 */

require_once __DIR__ . '/GoogleOAuthHandler.php';
require_once __DIR__ . '/config.php';

// Session already started by config.php via SessionManager

// Validate state to prevent CSRF
if (!isset($_GET['code'])) {
    error_log('[OAuth Callback] Missing code. Query: ' . json_encode($_GET));
    header('Location: ../Views/front-office/login.php?error=oauth_failed');
    exit;
}

$code = $_GET['code'];
$statePayload = null;
if (!empty($_GET['state'])) {
    $decoded = json_decode(base64_decode($_GET['state']), true);
    if (is_array($decoded)) {
        $statePayload = $decoded;
    }
}

$role = $_SESSION['oauth_role'] ?? ($statePayload['role'] ?? 'student'); // Default to student
$stateNonce = $_SESSION['oauth_state_nonce'] ?? null;
if ($statePayload && isset($statePayload['nonce']) && $stateNonce && !hash_equals($stateNonce, $statePayload['nonce'])) {
    error_log('[OAuth Callback] State nonce mismatch');
}

try {
    // Initialize OAuth handler with DB connection from config.php
    $oauth = new GoogleOAuthHandler(
        $db_connection,
        GOOGLE_CLIENT_ID,
        GOOGLE_CLIENT_SECRET,
        GOOGLE_REDIRECT_URI
    );
    
    // Exchange code for token
    $token_data = $oauth->getAccessToken($code);
    
    if (!$token_data || !isset($token_data['access_token'])) {
        error_log('[OAuth Callback] Token exchange failed. Response: ' . json_encode($token_data));
        throw new Exception('Failed to get access token');
    }
    
    // Get user info from Google
    $google_user = $oauth->getUserInfo($token_data['access_token']);
    
    if (!$google_user) {
        error_log('[OAuth Callback] Failed to fetch user info with access_token');
        throw new Exception('Failed to get user info');
    }
    
    // Find or create user, detect onboarding
    $result = $oauth->findOrCreateUser($google_user, $role);
    $user = $result['user'];
    $created = $result['created'];

    $sessionUsername = $user['username'] ?? ($google_user['email'] ? strtok($google_user['email'], '@') : 'user');
    $sessionFullName = $user['fullName'] ?? ($google_user['name'] ?? $sessionUsername);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $sessionUsername;
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['auth_method'] = 'google';
    $_SESSION['last_activity'] = time();
    $_SESSION['email'] = $user['email'] ?? $google_user['email'] ?? '';
    $_SESSION['google_name'] = $sessionFullName;
    $_SESSION['full_name'] = $sessionFullName;
    
    error_log('[OAuth Callback] Session set. user_id=' . $user['id'] . ', role=' . $role . ', created=' . ($created ? 'yes' : 'no'));
    
    // Update last login
    $stmt = $db_connection->prepare("UPDATE {$role}s SET lastLoginAt = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Redirect: onboarding for newly created, else dashboard
    if ($created) {
        if ($role === 'student') {
            header('Location: ../Views/front-office/onboard.php');
        } elseif ($role === 'teacher') {
            header('Location: ../Views/teacher-back-office/onboard.php');
        } else {
            // Admins not using Google; fallback to admin login
            header('Location: ../Views/admin-back-office/login.php?error=oauth_admin_disabled');
        }
    } else {
        if ($role === 'student') {
            header('Location: ../Views/front-office/dashboard.php');
        } elseif ($role === 'teacher') {
            header('Location: ../Views/teacher-back-office/dashboard.php');
        } else {
            header('Location: ../Views/admin-back-office/dashboard.php');
        }
    }
    exit;
    
} catch (Exception $e) {
    error_log('[OAuth Callback] Error: ' . $e->getMessage());
    
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

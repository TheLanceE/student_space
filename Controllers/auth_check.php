<?php
/**
 * Authentication Check - Include this at top of protected pages
 */

require_once __DIR__ . '/config.php';
// Session already initialized by config.php via SessionManager

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Refresh login time
$_SESSION['login_time'] = time();

// Initialize CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
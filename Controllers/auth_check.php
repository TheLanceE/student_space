<?php
/**
 * Authentication Check - Include this at top of protected pages
 */

require_once __DIR__ . '/config.php';
// Session already initialized by config.php via SessionManager

function _authcheck_is_json_request() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return (stripos($accept, 'application/json') !== false)
        || (strtolower($xrw) === 'xmlhttprequest')
        || (strpos($uri, 'Controller') !== false);
}

// Check if user is logged in (supports legacy + SessionManager session shapes)
if (!SessionManager::isLoggedIn()) {
    if (_authcheck_is_json_request()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }
    header('Location: login.php');
    exit;
}

// Ensure CSRF token exists for subsequent POSTs
SessionManager::generateCSRFToken();
?>
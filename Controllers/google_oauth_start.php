<?php
/**
 * Google OAuth Start
 * Redirects to Google for authentication
 */

require_once __DIR__ . '/GoogleOAuthHandler.php';
// Session already initialized by GoogleOAuthHandler (via config.php)

// Check if Google OAuth is configured
if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET) || empty(GOOGLE_REDIRECT_URI)) {
    error_log('[OAuth Start] Missing config: CLIENT_ID=' . (GOOGLE_CLIENT_ID ?: 'NOT SET') . ', SECRET=' . (GOOGLE_CLIENT_SECRET ? 'SET' : 'NOT SET') . ', REDIRECT=' . (GOOGLE_REDIRECT_URI ?: 'NOT SET'));
    header('Location: ../Views/front-office/login.php?error=oauth_config');
    exit;
}

// Get OAuth URL
$auth_url = get_google_oauth_url();
error_log('[OAuth Start] Redirecting to Google. Redirect URI=' . GOOGLE_REDIRECT_URI . ' | URL=' . $auth_url);

if (!$auth_url) {
    die('Failed to generate OAuth URL');
}

// Redirect to Google
header('Location: ' . $auth_url);
exit;
?>

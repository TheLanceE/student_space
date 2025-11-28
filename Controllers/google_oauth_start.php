<?php
/**
 * Google OAuth Start
 * Redirects to Google for authentication
 */

require_once __DIR__ . '/GoogleOAuthHandler.php';

session_start();

// Check if Google OAuth is configured
if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    die('Google OAuth is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in GoogleOAuthHandler.php');
}

// Get OAuth URL
$auth_url = get_google_oauth_url();

if (!$auth_url) {
    die('Failed to generate OAuth URL');
}

// Redirect to Google
header('Location: ' . $auth_url);
exit;
?>

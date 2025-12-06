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

$role = $_SESSION['oauth_role'] ?? ($_GET['role'] ?? 'student');
$_SESSION['oauth_role'] = $role;
$stateNonce = bin2hex(random_bytes(8));
$_SESSION['oauth_state_nonce'] = $stateNonce;

$state = base64_encode(json_encode([
    'nonce' => $stateNonce,
    'role' => $role
]));

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
    'state' => $state
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
error_log('[OAuth Start] Redirecting to Google. role=' . $role . ' stateNonce=' . $stateNonce . ' Redirect URI=' . GOOGLE_REDIRECT_URI);

// Redirect to Google
header('Location: ' . $auth_url);
exit;
?>

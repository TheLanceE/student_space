<?php
/**
 * Google OAuth Integration Handler
 * Handles Google Sign-In authentication flow
 */

require_once __DIR__ . '/config.php';

// Load local OAuth config if it exists
$local_config = __DIR__ . '/oauth_config.local.php';
if (file_exists($local_config)) {
    require_once $local_config;
}

// Google OAuth Configuration (read from environment or local config)
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
}
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/edumind/Controllers/google_oauth_callback.php');
}

class GoogleOAuthHandler {
    private $pdo;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    
    public function __construct($pdo, $client_id, $client_secret, $redirect_uri) {
        $this->pdo = $pdo;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
    }
    
    /**
     * Generate Google OAuth URL
     */
    public function getAuthUrl() {
        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];
        
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        error_log('[OAuth Handler] Auth URL generated. Redirect URI=' . $this->redirect_uri);
        return $url;
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken($code) {
        $token_url = 'https://oauth2.googleapis.com/token';
        
        $params = [
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            error_log('[OAuth Handler] Token exchange HTTP ' . $http_code . ' Response: ' . $response);
            return false;
        }
        
        $decoded = json_decode($response, true);
        if (!$decoded) {
            error_log('[OAuth Handler] Token exchange JSON parse failed: ' . $response);
        }
        return $decoded;
    }
    
    /**
     * Get user info from Google
     */
    public function getUserInfo($access_token) {
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($access_token);
        
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            error_log('[OAuth Handler] User info HTTP ' . $http_code . ' Response: ' . $response);
            return false;
        }
        
        $decoded = json_decode($response, true);
        if (!$decoded) {
            error_log('[OAuth Handler] User info JSON parse failed: ' . $response);
        }
        return $decoded;
    }
    
    /**
     * Find or create user from Google data
     */
    public function findOrCreateUser($google_user, $role = 'student') {
        $email = $google_user['email'] ?? null;
        $google_id = $google_user['id'] ?? null;
        $name = $google_user['name'] ?? null;

        if (!$email) {
            throw new Exception('Google user missing email');
        }

        $generatedUsername = $this->buildUsername($name, $email, $google_id, $role);
        
        // Check if user exists by email
        $stmt = $this->pdo->prepare("SELECT * FROM {$role}s WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            // Update Google ID if not set
            if (empty($existing_user['google_id'])) {
                $update_stmt = $this->pdo->prepare("UPDATE {$role}s SET google_id = ? WHERE id = ?");
                $update_stmt->execute([$google_id, $existing_user['id']]);
            }
            // Backfill username/fullName if missing
            $needsUpdate = false;
            $newUsername = $existing_user['username'] ?: $generatedUsername;
            $newFullName = $existing_user['fullName'] ?: ($name ?: $generatedUsername);
            if (empty($existing_user['username'])) {
                $needsUpdate = true;
            }
            if (empty($existing_user['fullName'])) {
                $needsUpdate = true;
            }
            if ($needsUpdate) {
                $update_stmt = $this->pdo->prepare("UPDATE {$role}s SET username = ?, fullName = ? WHERE id = ?");
                $update_stmt->execute([$newUsername, $newFullName, $existing_user['id']]);
                $existing_user['username'] = $newUsername;
                $existing_user['fullName'] = $newFullName;
            }
            return ['user' => $existing_user, 'created' => false];
        }
        
        // Create new user
        $user_id = uniqid($role[0] . '_');
        $username = $generatedUsername;
        
        try {
            if ($role === 'student') {
                $insert_stmt = $this->pdo->prepare("
                    INSERT INTO students (id, username, password, fullName, email, google_id, createdAt)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $insert_stmt->execute([
                    $user_id,
                    $username,
                    password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Random password
                    $name ?: $generatedUsername,
                    $email,
                    $google_id
                ]);
            } elseif ($role === 'teacher') {
                $insert_stmt = $this->pdo->prepare("
                    INSERT INTO teachers (id, username, password, fullName, email, google_id, createdAt)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $insert_stmt->execute([
                    $user_id,
                    $username,
                    password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    $name ?: $generatedUsername,
                    $email,
                    $google_id
                ]);
            }
        } catch (PDOException $e) {
            // Handle duplicate email by returning the existing user instead of failing the flow
            if ($e->getCode() === '23000') {
                $stmt = $this->pdo->prepare("SELECT * FROM {$role}s WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $existing_user = $stmt->fetch();
                if ($existing_user) {
                    return ['user' => $existing_user, 'created' => false];
                }
            }
            throw $e;
        }
        
        // Fetch newly created user
        $stmt = $this->pdo->prepare("SELECT * FROM {$role}s WHERE id = ?");
        $stmt->execute([$user_id]);
        $new_user = $stmt->fetch();
        return ['user' => $new_user, 'created' => true];
    }

    private function buildUsername($name, $email, $google_id, $role) {
        $base = $name ?: ($email ? strtok($email, '@') : $role);
        $base = strtolower(preg_replace('/[^a-z0-9]+/', '', $base));
        $suffix = $google_id ? substr($google_id, -4) : substr(uniqid(), -4);
        if (!$base) {
            $base = $role;
        }
        return $base . '_' . $suffix;
    }
}

// Export function for easy use
function get_google_oauth_url() {
    if (empty(GOOGLE_CLIENT_ID)) {
        return null;
    }
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}
?>

<?php
/**
 * Google OAuth Integration Handler
 * Handles Google Sign-In authentication flow
 */

require_once __DIR__ . '/config.php';

// Google OAuth Configuration
define('392955002198-0k8r5d2kuo47kbhnrmh3c8f32umgkcvi.apps.googleusercontent.com', ''); // TO BE CONFIGURED
define('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', ''); // TO BE CONFIGURED
define('GOOGLE_REDIRECT_URI', 'http://localhost/edumind/Controllers/google_oauth_callback.php');

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
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
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
            return false;
        }
        
        return json_decode($response, true);
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
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Find or create user from Google data
     */
    public function findOrCreateUser($google_user, $role = 'student') {
        $email = $google_user['email'];
        $google_id = $google_user['id'];
        $name = $google_user['name'];
        
        // Check if user exists by email
        $stmt = $this->pdo->prepare("SELECT * FROM {$role}s WHERE email = ?");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            // Update Google ID if not set
            if (empty($existing_user['google_id'])) {
                $update_stmt = $this->pdo->prepare("UPDATE {$role}s SET google_id = ? WHERE id = ?");
                $update_stmt->execute([$google_id, $existing_user['id']]);
            }
            return $existing_user;
        }
        
        // Create new user
        $user_id = uniqid($role[0] . '_');
        $username = strtolower(str_replace(' ', '', $name)) . '_' . substr($google_id, -4);
        
        if ($role === 'student') {
            $insert_stmt = $this->pdo->prepare("
                INSERT INTO students (id, username, password, fullName, email, google_id, createdAt)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $insert_stmt->execute([
                $user_id,
                $username,
                password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Random password
                $name,
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
                $name,
                $email,
                $google_id
            ]);
        }
        
        // Fetch newly created user
        $stmt = $this->pdo->prepare("SELECT * FROM {$role}s WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
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

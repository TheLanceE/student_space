<?php
/**
 * Security Helper Classes
 * CSRF, Rate Limiting, Input Validation, Audit Logging, Secure ID Generation
 */

/**
 * Generate cryptographically secure random ID
 * @param string $prefix Optional prefix for the ID
 * @param int $length Length of random bytes (default 8 = 16 hex chars)
 * @return string Secure random ID
 */
function generateSecureId(string $prefix = '', int $length = 8): string {
    return $prefix . bin2hex(random_bytes($length));
}

/**
 * CSRF Token Manager
 * Generates and validates CSRF tokens for form protection
 */

class CSRFProtection {
    /**
     * Generate CSRF token and store in session
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get current token
     */
    public static function getToken() {
        return self::generateToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate hidden input field for forms
     */
    public static function inputField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Get token for JavaScript/AJAX requests
     */
    public static function getTokenForAjax() {
        return self::getToken();
    }
    
    /**
     * Validate token from request (POST or headers)
     */
    public static function validateRequest() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!self::validateToken($token)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Invalid CSRF token']));
        }
        
        return true;
    }
}

/**
 * Input Validation and Sanitization
 */
class InputValidator {
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate username (alphanumeric + underscore, 3-20 chars)
     */
    public static function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
    
    /**
     * Sanitize HTML (remove scripts)
     */
    public static function sanitizeHTML($html) {
        return strip_tags($html, '<p><br><b><i><u><strong><em>');
    }
    
    /**
     * Validate integer
     */
    public static function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate array of IDs
     */
    public static function validateIdArray($ids) {
        if (!is_array($ids)) {
            return false;
        }
        
        foreach ($ids as $id) {
            if (!is_string($id) || empty($id)) {
                return false;
            }
        }
        
        return true;
    }
}

/**
 * Rate Limiting
 */
class RateLimiter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check if action is rate limited
     * @param string $action - Action identifier (e.g., 'login', 'api_call')
     * @param string $identifier - User IP or ID
     * @param int $max_attempts - Maximum attempts allowed
     * @param int $window_seconds - Time window in seconds
     */
    public function isRateLimited($action, $identifier, $max_attempts = 10, $window_seconds = 60) {
        // Create rate limit table if not exists
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id VARCHAR(100) PRIMARY KEY,
                action VARCHAR(50) NOT NULL,
                identifier VARCHAR(255) NOT NULL,
                attempts INT DEFAULT 1,
                first_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_action_identifier (action, identifier),
                INDEX idx_last_attempt (last_attempt)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        $stmt = $this->pdo->prepare("
            SELECT attempts, first_attempt 
            FROM rate_limits 
            WHERE action = ? AND identifier = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$action, $identifier, $window_seconds]);
        $record = $stmt->fetch();
        
        if (!$record) {
            // No recent attempts, create new record
            $id = 'rl_' . bin2hex(random_bytes(8));
            $insert = $this->pdo->prepare("
                INSERT INTO rate_limits (id, action, identifier, attempts) 
                VALUES (?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()
            ");
            $insert->execute([$id, $action, $identifier]);
            return false;
        }
        
        if ($record['attempts'] >= $max_attempts) {
            return true; // Rate limited
        }
        
        // Increment attempt count
        $update = $this->pdo->prepare("
            UPDATE rate_limits 
            SET attempts = attempts + 1, last_attempt = NOW() 
            WHERE action = ? AND identifier = ?
        ");
        $update->execute([$action, $identifier]);
        
        return false;
    }
    
    /**
     * Clear rate limit for identifier
     */
    public function clearLimit($action, $identifier) {
        $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE action = ? AND identifier = ?");
        $stmt->execute([$action, $identifier]);
    }
}

/**
 * Admin Audit Logger
 */
class AuditLogger {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log admin action
     */
    public function log($admin_id, $action, $target_type = null, $target_id = null, $details = []) {
        $id = 'audit_' . bin2hex(random_bytes(8));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $this->pdo->prepare("
            INSERT INTO admin_audit_log (id, admin_id, action, target_type, target_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $id,
            $admin_id,
            $action,
            $target_type,
            $target_id,
            json_encode($details),
            $ip,
            $user_agent
        ]);
    }
}
?>

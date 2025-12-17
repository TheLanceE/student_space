<?php
/**
 * SessionManager - Secure session management with CSRF protection
 * Handles session creation, validation, timeout, and security features
 */

class SessionManager {
    private const SESSION_LIFETIME = 3600; // 1 hour
    private const CSRF_TOKEN_NAME = 'csrf_token';
    private const MAX_IDLE_TIME = 1800; // 30 minutes
    
    /**
     * Initialize secure session configuration
     */
    public static function init() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        // Lax allows session to persist across Google OAuth redirect back
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', 1);
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => self::SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_start();
        
        // Set security headers
        self::setSecurityHeaders();
        
        // Initialize session security on first start
        if (!isset($_SESSION['initialized'])) {
            self::regenerateId();
            $_SESSION['initialized'] = true;
            $_SESSION['created_at'] = time();
            $_SESSION['user_ip'] = self::getClientIP();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        
        // Check session timeout
        self::checkTimeout();
        
        // Validate session security
        self::validateSession();

        // Bridge legacy session shape (older code uses top-level keys)
        self::hydrateLegacySession();
    }
    
    /**
     * Create user session after successful login
     */
    public static function createUserSession($userId, $username, $role, $fullName = null) {
        self::regenerateId();
        
        $_SESSION['user'] = [
            'id' => $userId,
            'username' => $username,
            'role' => $role,
            'fullName' => $fullName ?? $username,
            'logged_in_at' => time()
        ];
        
        $_SESSION['last_activity'] = time();
        $_SESSION['user_ip'] = self::getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Legacy compatibility keys used by older pages/controllers
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['full_name'] = $fullName ?? $username;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    /**
     * Get current logged-in user
     */
    public static function getCurrentUser() {
        self::init();
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::init();
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            return true;
        }
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        $user = self::getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Require authentication (redirect if not logged in)
     */
    public static function requireAuth($requiredRole = null) {
        self::init();

        if (!self::isLoggedIn()) {
            self::destroy();

            if (self::isJsonRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                exit;
            }

            header('Location: ' . self::getDefaultLoginPath());
            exit;
        }
        
        if ($requiredRole && !self::hasRole($requiredRole)) {
            self::destroy();
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
    }

    private static function hydrateLegacySession() {
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            return;
        }

        if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
            return;
        }

        $username = (string)($_SESSION['username'] ?? 'user');
        $role = (string)($_SESSION['role'] ?? 'student');
        $fullName = (string)($_SESSION['full_name'] ?? $_SESSION['google_name'] ?? $username);

        $_SESSION['user'] = [
            'id' => $_SESSION['user_id'],
            'username' => $username,
            'role' => $role,
            'fullName' => $fullName,
            'logged_in_at' => $_SESSION['login_time'] ?? time()
        ];

        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    private static function isJsonRequest() {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        if (stripos($accept, 'application/json') !== false) {
            return true;
        }
        if (strtolower($xrw) === 'xmlhttprequest') {
            return true;
        }
        return (strpos($uri, 'Controller') !== false);
    }

    private static function getDefaultLoginPath() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/Views/admin-back-office/') !== false) {
            return '/edumind/Views/admin-back-office/login.php';
        }
        if (strpos($uri, '/Views/teacher-back-office/') !== false) {
            return '/edumind/Views/teacher-back-office/login.php';
        }
        return '/edumind/Views/front-office/login.php';
    }
    
    /**
     * Destroy session (logout)
     */
    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            // Delete session cookie
            if (isset($_COOKIE[session_name()])) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            session_destroy();
        }
    }
    
    /**
     * Regenerate session ID (prevents session fixation)
     */
    public static function regenerateId() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        self::init();
        
        if (!isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            $_SESSION[self::CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[self::CSRF_TOKEN_NAME];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        self::init();
        
        if (!isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            return false;
        }
        
        return hash_equals($_SESSION[self::CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Get CSRF token for forms
     */
    public static function getCSRFToken() {
        return self::generateCSRFToken();
    }
    
    /**
     * Check session timeout
     */
    private static function checkTimeout() {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return;
        }
        
        $elapsed = time() - $_SESSION['last_activity'];
        
        // Session expired due to inactivity
        if ($elapsed > self::MAX_IDLE_TIME) {
            self::destroy();
            return;
        }
        
        // Session expired due to absolute timeout
        if (isset($_SESSION['created_at']) && (time() - $_SESSION['created_at']) > self::SESSION_LIFETIME) {
            self::destroy();
            return;
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Validate session security (IP and User-Agent)
     */
    private static function validateSession() {
        if (!isset($_SESSION['user'])) {
            return; // Not logged in, nothing to validate
        }
        
        // Check IP address (optional - can be disabled if users have dynamic IPs)
        if (isset($_SESSION['user_ip'])) {
            $currentIP = self::getClientIP();
            if ($_SESSION['user_ip'] !== $currentIP) {
                // IP changed - potential session hijacking
                self::destroy();
                return;
            }
        }
        
        // Check User-Agent
        if (isset($_SESSION['user_agent'])) {
            $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if ($_SESSION['user_agent'] !== $currentAgent) {
                // User-Agent changed - potential session hijacking
                self::destroy();
                return;
            }
        }
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Get first IP if multiple (X-Forwarded-For can have multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Set flash message (one-time message for next request)
     */
    public static function setFlash($key, $message) {
        self::init();
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Get and clear flash message
     */
    public static function getFlash($key) {
        self::init();
        
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        
        return null;
    }
    
    /**
     * Get session age in seconds
     */
    public static function getSessionAge() {
        self::init();
        
        if (isset($_SESSION['created_at'])) {
            return time() - $_SESSION['created_at'];
        }
        
        return 0;
    }
    
    /**
     * Get time remaining until session timeout
     */
    public static function getTimeRemaining() {
        self::init();
        
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            return max(0, self::MAX_IDLE_TIME - $elapsed);
        }
        
        return self::MAX_IDLE_TIME;
    }
    
    /**
     * Set security headers for all responses
     */
    private static function setSecurityHeaders() {
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // XSS protection (legacy browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        // Allow inline scripts/styles for Bootstrap and legacy code, but restrict sources
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://accounts.google.com https://apis.google.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "connect-src 'self' https://accounts.google.com",
            "frame-src 'self' https://accounts.google.com",
            "form-action 'self'",
            "base-uri 'self'"
        ];
        header('Content-Security-Policy: ' . implode('; ', $csp));
        
        // Permissions Policy (restrict sensitive features)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}
?>

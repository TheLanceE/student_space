<?php
/**
 * AuthController - Handles user authentication with secure session management
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        global $db_connection;
        $this->db = $db_connection;
        $this->userModel = new User($this->db);
    }

    /**
     * Register a new student
     */
    public function registerStudent($data) {
        return $this->userModel->create($data, 'student');
    }

    /**
     * Register a new teacher
     */
    public function registerTeacher($data) {
        return $this->userModel->create($data, 'teacher');
    }

    /**
     * Login - check credentials and create secure session
     */
    public function login($username, $password, $role = 'student') {
        try {
            $table = $role === 'teacher' ? 'teachers' : ($role === 'admin' ? 'admins' : 'students');
            
            $stmt = $this->db->prepare("
                SELECT * FROM $table 
                WHERE username = :username 
                AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
                LIMIT 1
            ");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check password using secure hash only
            $passwordValid = $user && password_verify($password, $user['password']);

            if ($user && $passwordValid) {
                // Update last login timestamp
                $updateStmt = $this->db->prepare("UPDATE $table SET lastLoginAt = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $user['id']]);

                // Create secure session using SessionManager
                $fullName = $user['fullName'] ?? $user['name'] ?? $user['username'];
                SessionManager::createUserSession($user['id'], $user['username'], $role, $fullName);

                // Return user data (without password)
                unset($user['password']);
                $user['role'] = $role;
                
                return [
                    'success' => true,
                    'user' => $user,
                    'csrf_token' => SessionManager::getCSRFToken()
                ];
            } else {
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $role = 'student') {
        return $this->userModel->usernameExists($username, $role);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $role = 'student') {
        return $this->userModel->emailExists($email, $role);
    }

    /**
     * Logout - destroy session securely
     */
    public function logout() {
        SessionManager::destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Get current logged-in user from session
     */
    public function getCurrentUser() {
        $user = SessionManager::getCurrentUser();
        
        if ($user) {
            return [
                'success' => true,
                'user' => $user,
                'csrf_token' => SessionManager::getCSRFToken()
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Not authenticated'
        ];
    }
    
    /**
     * Check authentication status
     */
    public function checkAuth() {
        return [
            'authenticated' => SessionManager::isLoggedIn(),
            'user' => SessionManager::getCurrentUser(),
            'session_time_remaining' => SessionManager::getTimeRemaining()
        ];
    }
}

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new AuthController();
    
    // Get JSON input for POST requests
    $input = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? [];
        // Fallback to $_POST if no JSON
        if (empty($input)) {
            $input = $_POST;
        }
    }
    
    $action = $input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'register_student':
            echo json_encode($auth->registerStudent($input['data'] ?? $input));
            break;
        
        case 'register_teacher':
            echo json_encode($auth->registerTeacher($input['data'] ?? $input));
            break;
        
        case 'login':
            echo json_encode($auth->login(
                $input['username'] ?? '',
                $input['password'] ?? '',
                $input['role'] ?? 'student'
            ));
            break;
        
        case 'check_username':
            $exists = $auth->usernameExists(
                $input['username'] ?? '',
                $input['role'] ?? 'student'
            );
            echo json_encode(['exists' => $exists]);
            break;
            
        case 'check_email':
            $exists = $auth->emailExists(
                $input['email'] ?? '',
                $input['role'] ?? 'student'
            );
            echo json_encode(['exists' => $exists]);
            break;
        
        case 'logout':
            echo json_encode($auth->logout());
            break;
        
        case 'current_user':
            echo json_encode($auth->getCurrentUser());
            break;
            
        case 'check_auth':
            echo json_encode($auth->checkAuth());
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>


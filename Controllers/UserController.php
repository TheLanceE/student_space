<?php
/**
 * UserController - Handles user management operations (Admin only)
 * Provides CRUD endpoints for managing students and teachers
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../Models/User.php';

class UserController {
    private $db;
    private $userModel;

    public function __construct() {
        global $db_connection;
        $this->db = $db_connection;
        $this->userModel = new User($this->db);
    }

    /**
     * Get all users of a specific role
     */
    public function getAll($role = 'student', $filters = []) {
        // Check authorization
        if (!SessionManager::hasRole('admin')) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ];
        }

        $users = $this->userModel->getAll($role, $filters);
        
        return [
            'success' => true,
            'users' => $users,
            'count' => count($users)
        ];
    }

    /**
     * Get single user by ID
     */
    public function getById($id, $role = null) {
        // Check authorization - admin can view all, users can view themselves
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }

        $isAdmin = SessionManager::hasRole('admin');
        $isSelf = ($currentUser['id'] === $id);

        if (!$isAdmin && !$isSelf) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Cannot view other users.'
            ];
        }

        $user = $this->userModel->getById($id, $role);
        
        if ($user) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'error' => 'User not found'];
        }
    }

    /**
     * Create new user (Admin only)
     */
    public function create($data, $role = 'student') {
        // Check authorization
        if (!SessionManager::hasRole('admin')) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ];
        }

        // Validate required fields
        $required = ['username', 'email', 'password'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: $field"
                ];
            }
        }

        // Check if username exists
        if ($this->userModel->usernameExists($data['username'], $role)) {
            return [
                'success' => false,
                'error' => 'Username already exists'
            ];
        }

        // Check if email exists
        if ($this->userModel->emailExists($data['email'], $role)) {
            return [
                'success' => false,
                'error' => 'Email already exists'
            ];
        }

        // Create user
        return $this->userModel->create($data, $role);
    }

    /**
     * Update user (Admin or self)
     */
    public function update($id, $data, $role = null) {
        // Check authorization
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }

        $isAdmin = SessionManager::hasRole('admin');
        $isSelf = ($currentUser['id'] === $id);

        if (!$isAdmin && !$isSelf) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Cannot update other users.'
            ];
        }

        // Non-admin users cannot change their role or certain fields
        if (!$isAdmin) {
            unset($data['role']);
            unset($data['gradeLevel']); // Students can't change their grade
            unset($data['specialty']); // Teachers can't change their specialty
        }

        return $this->userModel->update($id, $data, $role);
    }

    /**
     * Delete user (Soft delete by default)
     */
    public function delete($id, $role = null, $hard = false) {
        // Check authorization
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }

        $isAdmin = SessionManager::hasRole('admin');
        $isSelf = ($currentUser['id'] === $id);

        // Admin can delete anyone, students can delete themselves
        if (!$isAdmin && !$isSelf) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Cannot delete other users.'
            ];
        }

        // Only admin can do hard delete
        if ($hard && !$isAdmin) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Only admins can permanently delete users.'
            ];
        }

        // Prevent self-deletion for admins
        if ($isAdmin && $isSelf) {
            return [
                'success' => false,
                'error' => 'Cannot delete your own admin account.'
            ];
        }

        return $this->userModel->delete($id, $role, $hard);
    }

    /**
     * Search users
     */
    public function search($query, $role = 'student') {
        // Check authorization
        if (!SessionManager::hasRole('admin')) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ];
        }

        $filters = ['search' => $query];
        $users = $this->userModel->getAll($role, $filters);

        return [
            'success' => true,
            'users' => $users,
            'count' => count($users)
        ];
    }

    /**
     * Get user statistics
     */
    public function getStats() {
        // Check authorization
        if (!SessionManager::hasRole('admin')) {
            return [
                'success' => false,
                'error' => 'Unauthorized. Admin access required.'
            ];
        }

        $stats = [
            'students' => $this->userModel->getCount('student'),
            'teachers' => $this->userModel->getCount('teacher'),
            'admins' => $this->userModel->getCount('admin')
        ];

        return [
            'success' => true,
            'stats' => $stats
        ];
    }

    /**
     * Verify password for user (used before deletion or sensitive operations)
     */
    public function verifyPassword($id, $password, $role = null) {
        $currentUser = SessionManager::getCurrentUser();
        if (!$currentUser) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }

        // Only verify for self
        if ($currentUser['id'] !== $id) {
            return [
                'success' => false,
                'error' => 'Cannot verify password for other users.'
            ];
        }

        $isValid = $this->userModel->verifyPassword($id, $password, $role);

        return [
            'success' => true,
            'valid' => $isValid
        ];
    }
}

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    $controller = new UserController();
    
    // Get JSON input
    $input = [];
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? [];
        if (empty($input)) {
            $input = $_POST;
        }
    }
    
    // Get action from query string or input
    $action = $_GET['action'] ?? $input['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $role = $_GET['role'] ?? $input['role'] ?? 'student';
            $filters = [];
            if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
            if (isset($_GET['gradeLevel'])) $filters['gradeLevel'] = $_GET['gradeLevel'];
            if (isset($_GET['specialty'])) $filters['specialty'] = $_GET['specialty'];
            
            echo json_encode($controller->getAll($role, $filters));
            break;

        case 'getById':
            $id = $_GET['id'] ?? $input['id'] ?? '';
            $role = $_GET['role'] ?? $input['role'] ?? null;
            echo json_encode($controller->getById($id, $role));
            break;

        case 'create':
            $data = $input['data'] ?? $input;
            $role = $input['role'] ?? 'student';
            echo json_encode($controller->create($data, $role));
            break;

        case 'update':
            $id = $input['id'] ?? '';
            $data = $input['data'] ?? $input;
            $role = $input['role'] ?? null;
            unset($data['id']);
            unset($data['action']);
            unset($data['role']);
            echo json_encode($controller->update($id, $data, $role));
            break;

        case 'delete':
            $id = $_GET['id'] ?? $input['id'] ?? '';
            $role = $_GET['role'] ?? $input['role'] ?? null;
            $hard = isset($_GET['hard']) ? (bool)$_GET['hard'] : false;
            echo json_encode($controller->delete($id, $role, $hard));
            break;

        case 'search':
            $query = $_GET['query'] ?? $input['query'] ?? '';
            $role = $_GET['role'] ?? $input['role'] ?? 'student';
            echo json_encode($controller->search($query, $role));
            break;

        case 'stats':
            echo json_encode($controller->getStats());
            break;

        case 'verify_password':
            $id = $input['id'] ?? '';
            $password = $input['password'] ?? '';
            $role = $input['role'] ?? null;
            echo json_encode($controller->verifyPassword($id, $password, $role));
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action. Available: getAll, getById, create, update, delete, search, stats, verify_password'
            ]);
    }
    exit;
}
?>

<?php
/**
 * AuthController - Handles user authentication
 */
require_once __DIR__ . '/config.php';

class AuthController {
    private $db;

    public function __construct() {
        global $db_connection;
        $this->db = $db_connection;
    }

    /**
     * Register a new student
     */
    public function registerStudent($data) {
        try {
            $id = 'stu_' . uniqid();
            $stmt = $this->db->prepare("
                INSERT INTO students (id, username, password, fullName, email, mobile, address, gradeLevel, createdAt, lastLoginAt)
                VALUES (:id, :username, :password, :fullName, :email, :mobile, :address, :gradeLevel, NOW(), NOW())
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':fullName' => $data['fullName'],
                ':email' => $data['email'],
                ':mobile' => $data['mobile'] ?? '',
                ':address' => $data['address'] ?? '',
                ':gradeLevel' => $data['gradeLevel'] ?? 'Unassigned'
            ]);

            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Register a new teacher
     */
    public function registerTeacher($data) {
        try {
            $id = 'teach_' . uniqid();
            $stmt = $this->db->prepare("
                INSERT INTO teachers (id, username, password, fullName, email, mobile, address, specialty, nationalId, createdAt, lastLoginAt)
                VALUES (:id, :username, :password, :fullName, :email, :mobile, :address, :specialty, :nationalId, NOW(), NOW())
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':fullName' => $data['fullName'],
                ':email' => $data['email'],
                ':mobile' => $data['mobile'] ?? '',
                ':address' => $data['address'] ?? '',
                ':specialty' => $data['specialty'] ?? 'Unassigned',
                ':nationalId' => $data['nationalId'] ?? ''
            ]);

            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Login - check credentials and return user data
     */
    public function login($username, $password, $role = 'student') {
        try {
            $table = $role === 'teacher' ? 'teachers' : ($role === 'admin' ? 'admins' : 'students');
            
            $stmt = $this->db->prepare("SELECT * FROM $table WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $updateStmt = $this->db->prepare("UPDATE $table SET lastLoginAt = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $user['id']]);

                // Store in session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $role,
                    'fullName' => $user['fullName'] ?? $user['name'] ?? $user['username']
                ];

                unset($user['password']); // Don't send password to frontend
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $role = 'student') {
        try {
            $table = $role === 'teacher' ? 'teachers' : ($role === 'admin' ? 'admins' : 'students');
            
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM $table WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['count'] > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Logout - destroy session
     */
    public function logout() {
        session_destroy();
        return ['success' => true];
    }

    /**
     * Get current logged-in user
     */
    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
}

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $auth = new AuthController();
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'register_student':
            echo json_encode($auth->registerStudent($input['data']));
            break;
        
        case 'register_teacher':
            echo json_encode($auth->registerTeacher($input['data']));
            break;
        
        case 'login':
            echo json_encode($auth->login(
                $input['username'],
                $input['password'],
                $input['role'] ?? 'student'
            ));
            break;
        
        case 'check_username':
            $exists = $auth->usernameExists($input['username'], $input['role'] ?? 'student');
            echo json_encode(['exists' => $exists]);
            break;
        
        case 'logout':
            echo json_encode($auth->logout());
            break;
        
        case 'current_user':
            echo json_encode(['user' => $auth->getCurrentUser()]);
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>

<?php
/**
 * Project Controller for Debug Environment
 * Handles all project and task CRUD operations
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edumind');

class ProjectController {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $this->jsonResponse(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function getCurrentUserId() {
        return $_SESSION['user']['id'] ?? 'stu_debug';
    }

    public function getAllProjects() {
        try {
            $userId = $this->getCurrentUserId();
            
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    COUNT(t.id) as taskCount,
                    SUM(CASE WHEN t.isComplete = 1 THEN 1 ELSE 0 END) as completedTasks
                FROM projects p
                LEFT JOIN tasks t ON p.id = t.projectId
                WHERE p.createdBy = :userId OR p.assignedTo = :userId
                GROUP BY p.id
                ORDER BY p.createdAt DESC
            ");
            
            $stmt->execute([':userId' => $userId]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse(['success' => true, 'projects' => $projects]);
        } catch (PDOException $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getProject() {
        try {
            $projectId = $_POST['projectId'] ?? null;
            
            if (!$projectId) {
                throw new Exception('Project ID is required');
            }

            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = :id");
            $stmt->execute([':id' => $projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                throw new Exception('Project not found');
            }

            // Get tasks
            $stmt = $this->db->prepare("SELECT * FROM tasks WHERE projectId = :projectId ORDER BY createdAt DESC");
            $stmt->execute([':projectId' => $projectId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate completion percentage
            $totalTasks = count($tasks);
            $completedTasks = array_reduce($tasks, function($carry, $task) {
                return $carry + ($task['isComplete'] ? 1 : 0);
            }, 0);
            $project['completionPercentage'] = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $this->jsonResponse([
                'success' => true,
                'project' => $project,
                'tasks' => $tasks
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function createProject() {
        try {
            $data = $_POST['data'] ?? null;
            
            if (!$data || !isset($data['projectName'])) {
                throw new Exception('Project data is required');
            }

            $projectId = 'proj_' . bin2hex(random_bytes(8));
            $userId = $this->getCurrentUserId();

            $stmt = $this->db->prepare("
                INSERT INTO projects (id, projectName, description, createdBy, assignedTo, status, dueDate, createdAt)
                VALUES (:id, :name, :desc, :createdBy, :assignedTo, :status, :dueDate, NOW())
            ");

            $stmt->execute([
                ':id' => $projectId,
                ':name' => $data['projectName'],
                ':desc' => $data['description'] ?? '',
                ':createdBy' => $userId,
                ':assignedTo' => $userId,
                ':status' => $data['status'] ?? 'not_started',
                ':dueDate' => $data['dueDate'] ?? null
            ]);

            $this->jsonResponse([
                'success' => true,
                'projectId' => $projectId,
                'message' => 'Project created successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateProject() {
        try {
            $projectId = $_POST['projectId'] ?? null;
            $data = $_POST['data'] ?? null;
            
            if (!$projectId || !$data) {
                throw new Exception('Project ID and data are required');
            }

            $stmt = $this->db->prepare("
                UPDATE projects 
                SET projectName = :name, 
                    description = :desc, 
                    status = :status, 
                    dueDate = :dueDate,
                    updatedAt = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $projectId,
                ':name' => $data['projectName'],
                ':desc' => $data['description'] ?? '',
                ':status' => $data['status'] ?? 'not_started',
                ':dueDate' => $data['dueDate'] ?? null
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Project updated successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteProject() {
        try {
            $projectId = $_POST['projectId'] ?? null;
            
            if (!$projectId) {
                throw new Exception('Project ID is required');
            }

            // Delete tasks first
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE projectId = :projectId");
            $stmt->execute([':projectId' => $projectId]);

            // Delete project
            $stmt = $this->db->prepare("DELETE FROM projects WHERE id = :id");
            $stmt->execute([':id' => $projectId]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProjectController();
    
    $input = file_get_contents('php://input');
    $json = json_decode($input, true);
    
    // Merge JSON input with POST data
    $_POST = array_merge($_POST, $json ?? []);
    
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'get_all_projects':
            $controller->getAllProjects();
            break;
        case 'get_project':
            $controller->getProject();
            break;
        case 'create_project':
            $controller->createProject();
            break;
        case 'update_project':
            $controller->updateProject();
            break;
        case 'delete_project':
            $controller->deleteProject();
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // For GET requests, support testing
    $controller = new ProjectController();
    $action = $_GET['action'] ?? 'test';
    
    if ($action === 'test') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'ProjectController is working!',
            'user' => $controller->getCurrentUserId(),
            'database' => 'connected'
        ]);
    } elseif ($action === 'get_all_projects') {
        $controller->getAllProjects();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid GET action']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Only GET and POST requests are allowed']);
}

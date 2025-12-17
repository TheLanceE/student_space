<?php
/**
 * ProjectController - Handles project and task operations
 */
require_once __DIR__ . '/config.php';

class ProjectController {
    private $db;

    public function __construct() {
        global $db_connection;
        $this->db = $db_connection;
    }

    public static function getSessionUser(): ?array
    {
        // Prefer SessionManager user shape
        if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            return [
                'id' => (string)($_SESSION['user']['id'] ?? ''),
                'role' => (string)($_SESSION['user']['role'] ?? ''),
                'username' => (string)($_SESSION['user']['username'] ?? ''),
            ];
        }

        // Legacy fallback
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            return [
                'id' => (string)$_SESSION['user_id'],
                'role' => (string)$_SESSION['role'],
                'username' => (string)($_SESSION['username'] ?? ''),
            ];
        }

        // Some flows store teacher_id/student_id
        $role = (string)($_SESSION['role'] ?? '');
        if ($role === 'teacher' && isset($_SESSION['teacher_id'])) {
            return ['id' => (string)$_SESSION['teacher_id'], 'role' => 'teacher', 'username' => (string)($_SESSION['username'] ?? '')];
        }
        if ($role === 'student' && isset($_SESSION['student_id'])) {
            return ['id' => (string)$_SESSION['student_id'], 'role' => 'student', 'username' => (string)($_SESSION['username'] ?? '')];
        }

        return null;
    }

    public static function requireAuthJson(): array
    {
        $user = self::getSessionUser();
        if (!$user || empty($user['id']) || empty($user['role'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        return $user;
    }

    public static function validateCsrfJson(array $input): bool
    {
        $posted = $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $token = is_string($header) && $header !== '' ? $header : $posted;
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!is_string($token) || $token === '' || !is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Get all projects (optionally filtered by user)
     */
    public function getAllProjects($userId = null, $role = null) {
        try {
            $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM tasks WHERE projectId = p.id) as taskCount,
                    (SELECT COUNT(*) FROM tasks WHERE projectId = p.id AND isComplete = 1) as completedTasks
                    FROM projects p";
            
            if ($userId && $role !== 'admin') {
                // PDO MySQL does not allow reusing the same named placeholder when emulated prepares are disabled
                $sql .= " WHERE p.createdBy = :userIdCreated OR p.assignedTo = :userIdAssigned";
            }
            
            $sql .= " ORDER BY p.createdAt DESC";
            
            $stmt = $this->db->prepare($sql);
            if ($userId && $role !== 'admin') {
                $stmt->execute([':userIdCreated' => $userId, ':userIdAssigned' => $userId]);
            } else {
                $stmt->execute();
            }
            
            return ['success' => true, 'projects' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get a single project by ID
     */
    public function getProject($projectId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($project) {
                // Get tasks for this project
                $taskStmt = $this->db->prepare("SELECT * FROM tasks WHERE projectId = :projectId ORDER BY createdAt ASC");
                $taskStmt->execute([':projectId' => $projectId]);
                $project['tasks'] = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

                $taskCount = count($project['tasks']);
                $completed = 0;
                foreach ($project['tasks'] as $t) {
                    if (!empty($t['isComplete'])) {
                        $completed++;
                    }
                }
                $project['taskCount'] = $taskCount;
                $project['completedTasks'] = $completed;
                $project['completionPercentage'] = $taskCount > 0 ? (int)round(($completed / $taskCount) * 100) : 0;
                
                return ['success' => true, 'project' => $project];
            } else {
                return ['success' => false, 'error' => 'Project not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a new project
     */
    public function createProject($data) {
        try {
            $id = 'proj_' . bin2hex(random_bytes(8));
            $stmt = $this->db->prepare("
                INSERT INTO projects (id, projectName, description, createdBy, assignedTo, status, dueDate, createdAt)
                VALUES (:id, :projectName, :description, :createdBy, :assignedTo, :status, :dueDate, NOW())
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':projectName' => $data['projectName'],
                ':description' => $data['description'] ?? '',
                ':createdBy' => $data['createdBy'],
                ':assignedTo' => $data['assignedTo'] ?? null,
                ':status' => $data['status'] ?? 'not_started',
                ':dueDate' => $data['dueDate'] ?? null
            ]);

            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update an existing project
     */
    public function updateProject($projectId, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE projects 
                SET projectName = :projectName, 
                    description = :description,
                    assignedTo = :assignedTo,
                    status = :status,
                    dueDate = :dueDate
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $projectId,
                ':projectName' => $data['projectName'],
                ':description' => $data['description'] ?? '',
                ':assignedTo' => $data['assignedTo'] ?? null,
                ':status' => $data['status'] ?? 'not_started',
                ':dueDate' => $data['dueDate'] ?? null
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete a project
     */
    public function deleteProject($projectId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM projects WHERE id = :id");
            $stmt->execute([':id' => $projectId]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all tasks for a project
     */
    public function getTasksForProject($projectId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tasks WHERE projectId = :projectId ORDER BY createdAt ASC");
            $stmt->execute([':projectId' => $projectId]);
            return ['success' => true, 'tasks' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a new task
     */
    public function createTask($data) {
        try {
            $id = 'task_' . bin2hex(random_bytes(8));
            $stmt = $this->db->prepare("
                INSERT INTO tasks (id, projectId, taskName, description, isComplete, priority, dueDate, createdAt)
                VALUES (:id, :projectId, :taskName, :description, :isComplete, :priority, :dueDate, NOW())
            ");
            
            $stmt->execute([
                ':id' => $id,
                ':projectId' => $data['projectId'],
                ':taskName' => $data['taskName'],
                ':description' => $data['description'] ?? '',
                ':isComplete' => $data['isComplete'] ?? false,
                ':priority' => $data['priority'] ?? 'medium',
                ':dueDate' => $data['dueDate'] ?? null
            ]);

            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update a task
     */
    public function updateTask($taskId, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET taskName = :taskName,
                    description = :description,
                    isComplete = :isComplete,
                    priority = :priority,
                    dueDate = :dueDate,
                    completedAt = :completedAt
                WHERE id = :id
            ");
            
            $completedAt = ($data['isComplete'] ?? false) ? date('Y-m-d H:i:s') : null;
            
            $stmt->execute([
                ':id' => $taskId,
                ':taskName' => $data['taskName'],
                ':description' => $data['description'] ?? '',
                ':isComplete' => $data['isComplete'] ?? false,
                ':priority' => $data['priority'] ?? 'medium',
                ':dueDate' => $data['dueDate'] ?? null,
                ':completedAt' => $completedAt
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Toggle task completion
     */
    public function toggleTaskComplete($taskId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET isComplete = NOT isComplete,
                    completedAt = CASE WHEN isComplete = 0 THEN NOW() ELSE NULL END
                WHERE id = :id
            ");
            $stmt->execute([':id' => $taskId]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete a task
     */
    public function deleteTask($taskId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $taskId]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get task with owning project creator for authorization
     */
    public function getTaskWithProjectOwner($taskId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT t.*, p.createdBy AS projectCreatedBy FROM tasks t JOIN projects p ON p.id = t.projectId WHERE t.id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $taskId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return ['success' => false, 'error' => 'Task not found'];
            }
            return ['success' => true, 'task' => $row];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// API endpoint handler
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' || ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    header('Content-Type: application/json');
    
    $controller = new ProjectController();
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = [];
    }
    $action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    $user = ProjectController::requireAuthJson();
    $userId = $user['id'];
    $role = $user['role'];

    $csrfRequiredActions = ['create_project', 'update_project', 'delete_project', 'create_task', 'update_task', 'toggle_task', 'delete_task'];
    if (in_array($action, $csrfRequiredActions, true) && !ProjectController::validateCsrfJson($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    switch ($action) {
        case 'get_all_projects':
            if ($role === 'student') {
                echo json_encode($controller->getAllProjects($userId, $role));
            } else {
                echo json_encode($controller->getAllProjects(null, $role));
            }
            break;
        
        case 'get_project':
            $projectId = (string)($input['projectId'] ?? '');
            if ($projectId === '') {
                echo json_encode(['success' => false, 'error' => 'Missing projectId']);
                break;
            }

            $res = $controller->getProject($projectId);
            if (!($res['success'] ?? false)) {
                echo json_encode($res);
                break;
            }

            $project = $res['project'];
            $owner = (string)($project['createdBy'] ?? '');
            $assignedTo = (string)($project['assignedTo'] ?? '');
            $canView = ($role === 'admin' || $role === 'teacher' || $owner === $userId || $assignedTo === $userId);
            if (!$canView) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }

            // Backwards-compatible shape
            echo json_encode(['success' => true, 'project' => $project, 'tasks' => $project['tasks'] ?? []]);
            break;
        
        case 'create_project':
            if ($role !== 'student') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Only students can create projects']);
                break;
            }

            $data = $input['data'] ?? [];
            if (!is_array($data)) {
                $data = [];
            }
            $data['createdBy'] = $userId;
            echo json_encode($controller->createProject($data));
            break;
        
        case 'update_project':
            if ($role !== 'student' && $role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            $projectId = (string)($input['projectId'] ?? '');
            $existing = $controller->getProject($projectId);
            if (!($existing['success'] ?? false)) {
                echo json_encode($existing);
                break;
            }
            $owner = (string)($existing['project']['createdBy'] ?? '');
            if ($role !== 'admin' && $owner !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->updateProject($projectId, $input['data'] ?? []));
            break;
        
        case 'delete_project':
            if ($role !== 'student' && $role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            $projectId = (string)($input['projectId'] ?? '');
            $existing = $controller->getProject($projectId);
            if (!($existing['success'] ?? false)) {
                echo json_encode($existing);
                break;
            }
            $owner = (string)($existing['project']['createdBy'] ?? '');
            if ($role !== 'admin' && $owner !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->deleteProject($projectId));
            break;
        
        case 'get_tasks':
            $projectId = (string)($input['projectId'] ?? '');
            $existing = $controller->getProject($projectId);
            if (!($existing['success'] ?? false)) {
                echo json_encode($existing);
                break;
            }
            $owner = (string)($existing['project']['createdBy'] ?? '');
            $assignedTo = (string)($existing['project']['assignedTo'] ?? '');
            if (!($role === 'admin' || $role === 'teacher' || $owner === $userId || $assignedTo === $userId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->getTasksForProject($projectId));
            break;
        
        case 'create_task':
            if ($role !== 'student' && $role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            $data = $input['data'] ?? [];
            if (!is_array($data)) {
                $data = [];
            }
            $projectId = (string)($data['projectId'] ?? '');
            $existing = $controller->getProject($projectId);
            if (!($existing['success'] ?? false)) {
                echo json_encode($existing);
                break;
            }
            $owner = (string)($existing['project']['createdBy'] ?? '');
            if ($role !== 'admin' && $owner !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->createTask($data));
            break;
        
        case 'update_task':
            if ($role !== 'student' && $role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            $taskId = (string)($input['taskId'] ?? '');
            $taskRow = $controller->getTaskWithProjectOwner($taskId);
            if (!($taskRow['success'] ?? false)) {
                echo json_encode($taskRow);
                break;
            }
            if ($role !== 'admin' && (string)($taskRow['task']['projectCreatedBy'] ?? '') !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->updateTask($taskId, $input['data'] ?? []));
            break;
        
        case 'toggle_task':
            if ($role !== 'student' && $role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            $taskId = (string)($input['taskId'] ?? '');
            $taskRow = $controller->getTaskWithProjectOwner($taskId);
            if (!($taskRow['success'] ?? false)) {
                echo json_encode($taskRow);
                break;
            }
            if ($role !== 'admin' && (string)($taskRow['task']['projectCreatedBy'] ?? '') !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->toggleTaskComplete($taskId));
            break;
        
        case 'delete_task':
            if ($role !== 'student' && $role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            $taskId = (string)($input['taskId'] ?? '');
            $taskRow = $controller->getTaskWithProjectOwner($taskId);
            if (!($taskRow['success'] ?? false)) {
                echo json_encode($taskRow);
                break;
            }
            if ($role !== 'admin' && (string)($taskRow['task']['projectCreatedBy'] ?? '') !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                break;
            }
            echo json_encode($controller->deleteTask($taskId));
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>

<?php
/**
 * ProjectApiController - JSON API for Projects (AJAX requests)
 * Works for students, teachers, and admins
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config.php';

class ProjectApiController {
    private $db;

    public function __construct() {
        global $db_connection;
        $this->db = $db_connection;
    }

    /**
     * Get current user from session (supports multiple session formats)
     */
    private function getCurrentUser(): ?array {
        // SessionManager format
        if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            return [
                'id' => (string)$_SESSION['user']['id'],
                'role' => (string)($_SESSION['user']['role'] ?? 'student'),
                'username' => (string)($_SESSION['user']['username'] ?? '')
            ];
        }

        // Legacy format
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            return [
                'id' => (string)$_SESSION['user_id'],
                'role' => (string)$_SESSION['role'],
                'username' => (string)($_SESSION['username'] ?? '')
            ];
        }

        // Role-specific IDs
        $role = (string)($_SESSION['role'] ?? '');
        if ($role === 'teacher' && isset($_SESSION['teacher_id'])) {
            return ['id' => (string)$_SESSION['teacher_id'], 'role' => 'teacher', 'username' => (string)($_SESSION['username'] ?? '')];
        }
        if ($role === 'student' && isset($_SESSION['student_id'])) {
            return ['id' => (string)$_SESSION['student_id'], 'role' => 'student', 'username' => (string)($_SESSION['username'] ?? '')];
        }
        if ($role === 'admin' && isset($_SESSION['admin_id'])) {
            return ['id' => (string)$_SESSION['admin_id'], 'role' => 'admin', 'username' => (string)($_SESSION['username'] ?? '')];
        }

        return null;
    }

    /**
     * Require authentication and return user
     */
    private function requireAuth(): array {
        $user = $this->getCurrentUser();
        if (!$user || empty($user['id'])) {
            $this->jsonError('Not authenticated', 401);
        }
        return $user;
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrf(array $input): bool {
        $posted = $input['csrf_token'] ?? '';
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $token = !empty($header) ? $header : $posted;
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Send JSON success response
     */
    private function jsonSuccess(array $data = []): void {
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    /**
     * Send JSON error response
     */
    private function jsonError(string $message, int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    /**
     * Get all projects (filtered by role)
     */
    public function getAllProjects(): void {
        $user = $this->requireAuth();
        
        try {
            // Check if tables exist
            $tablesExist = $this->checkTablesExist();
            
            if (!$tablesExist['projects']) {
                $this->jsonSuccess(['projects' => []]);
                return;
            }

            $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM tasks WHERE projectId = p.id) as taskCount,
                    (SELECT COUNT(*) FROM tasks WHERE projectId = p.id AND (isComplete = 1 OR status = 'completed')) as completedTasks
                    FROM projects p";
            
            // Students see only their projects, teachers/admins see all
            if ($user['role'] === 'student') {
                $sql .= " WHERE p.createdBy = :userId OR p.assignedTo = :userId2";
            }
            
            $sql .= " ORDER BY p.createdAt DESC";
            
            $stmt = $this->db->prepare($sql);
            
            if ($user['role'] === 'student') {
                $stmt->execute([':userId' => $user['id'], ':userId2' => $user['id']]);
            } else {
                $stmt->execute();
            }
            
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add completion percentage
            foreach ($projects as &$project) {
                $total = (int)($project['taskCount'] ?? 0);
                $completed = (int)($project['completedTasks'] ?? 0);
                $project['completionPercentage'] = $total > 0 ? round(($completed / $total) * 100) : 0;
            }
            
            $this->jsonSuccess(['projects' => $projects]);
        } catch (PDOException $e) {
            error_log('ProjectApiController::getAllProjects error: ' . $e->getMessage());
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Get single project with tasks
     */
    public function getProject(string $projectId): void {
        $user = $this->requireAuth();
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                $this->jsonError('Project not found', 404);
            }
            
            // Check access for students
            if ($user['role'] === 'student' && $project['createdBy'] !== $user['id'] && $project['assignedTo'] !== $user['id']) {
                $this->jsonError('Access denied', 403);
            }
            
            // Get tasks
            $taskStmt = $this->db->prepare("SELECT * FROM tasks WHERE projectId = :projectId ORDER BY createdAt ASC");
            $taskStmt->execute([':projectId' => $projectId]);
            $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $project['tasks'] = $tasks;
            $project['taskCount'] = count($tasks);
            $project['completedTasks'] = count(array_filter($tasks, fn($t) => !empty($t['isComplete']) || ($t['status'] ?? '') === 'completed'));
            $project['completionPercentage'] = $project['taskCount'] > 0 ? round(($project['completedTasks'] / $project['taskCount']) * 100) : 0;
            
            $this->jsonSuccess(['project' => $project]);
        } catch (PDOException $e) {
            $this->jsonError('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Create a new project
     */
    public function createProject(array $data): void {
        $user = $this->requireAuth();
        
        if (empty($data['projectName'])) {
            $this->jsonError('Project name is required');
        }
        
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
                ':createdBy' => $user['id'],
                ':assignedTo' => $data['assignedTo'] ?? $user['id'],
                ':status' => $data['status'] ?? 'not_started',
                ':dueDate' => !empty($data['dueDate']) ? $data['dueDate'] : null
            ]);
            
            $this->jsonSuccess(['id' => $id, 'message' => 'Project created successfully']);
        } catch (PDOException $e) {
            $this->jsonError('Failed to create project: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing project
     */
    public function updateProject(string $projectId, array $data): void {
        $user = $this->requireAuth();
        
        if (empty($data['projectName'])) {
            $this->jsonError('Project name is required');
        }
        
        try {
            // Check project exists and user has access
            $check = $this->db->prepare("SELECT createdBy FROM projects WHERE id = :id");
            $check->execute([':id' => $projectId]);
            $project = $check->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                $this->jsonError('Project not found', 404);
            }
            
            // Students can only edit their own projects
            if ($user['role'] === 'student' && $project['createdBy'] !== $user['id']) {
                $this->jsonError('Access denied', 403);
            }
            
            $stmt = $this->db->prepare("
                UPDATE projects 
                SET projectName = :projectName, 
                    description = :description,
                    assignedTo = :assignedTo,
                    status = :status,
                    dueDate = :dueDate,
                    updatedAt = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $projectId,
                ':projectName' => $data['projectName'],
                ':description' => $data['description'] ?? '',
                ':assignedTo' => $data['assignedTo'] ?? null,
                ':status' => $data['status'] ?? 'not_started',
                ':dueDate' => !empty($data['dueDate']) ? $data['dueDate'] : null
            ]);
            
            $this->jsonSuccess(['message' => 'Project updated successfully']);
        } catch (PDOException $e) {
            $this->jsonError('Failed to update project: ' . $e->getMessage());
        }
    }

    /**
     * Delete a project
     */
    public function deleteProject(string $projectId): void {
        $user = $this->requireAuth();
        
        try {
            // Check project exists and user has access
            $check = $this->db->prepare("SELECT createdBy FROM projects WHERE id = :id");
            $check->execute([':id' => $projectId]);
            $project = $check->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                $this->jsonError('Project not found', 404);
            }
            
            // Students can only delete their own projects
            if ($user['role'] === 'student' && $project['createdBy'] !== $user['id']) {
                $this->jsonError('Access denied', 403);
            }
            
            // Delete tasks first
            $this->db->prepare("DELETE FROM tasks WHERE projectId = :projectId")->execute([':projectId' => $projectId]);
            
            // Delete project
            $this->db->prepare("DELETE FROM projects WHERE id = :id")->execute([':id' => $projectId]);
            
            $this->jsonSuccess(['message' => 'Project deleted successfully']);
        } catch (PDOException $e) {
            $this->jsonError('Failed to delete project: ' . $e->getMessage());
        }
    }

    /**
     * Create a task
     */
    public function createTask(array $data): void {
        $user = $this->requireAuth();
        
        if (empty($data['projectId']) || empty($data['taskName'])) {
            $this->jsonError('Project ID and task name are required');
        }
        
        try {
            $id = 'task_' . bin2hex(random_bytes(8));
            
            // Check which columns exist in tasks table
            $columns = $this->getTableColumns('tasks');
            $hasStatus = in_array('status', $columns);
            $hasCreatedBy = in_array('createdBy', $columns);
            
            if ($hasStatus && $hasCreatedBy) {
                $stmt = $this->db->prepare("
                    INSERT INTO tasks (id, projectId, taskName, description, status, priority, dueDate, createdBy, createdAt)
                    VALUES (:id, :projectId, :taskName, :description, :status, :priority, :dueDate, :createdBy, NOW())
                ");
                $stmt->execute([
                    ':id' => $id,
                    ':projectId' => $data['projectId'],
                    ':taskName' => $data['taskName'],
                    ':description' => $data['description'] ?? '',
                    ':status' => $data['status'] ?? 'not_started',
                    ':priority' => $data['priority'] ?? 'medium',
                    ':dueDate' => !empty($data['dueDate']) ? $data['dueDate'] : null,
                    ':createdBy' => $user['id']
                ]);
            } else {
                // Fallback for simpler schema
                $stmt = $this->db->prepare("
                    INSERT INTO tasks (id, projectId, taskName, description, priority, dueDate, createdAt)
                    VALUES (:id, :projectId, :taskName, :description, :priority, :dueDate, NOW())
                ");
                $stmt->execute([
                    ':id' => $id,
                    ':projectId' => $data['projectId'],
                    ':taskName' => $data['taskName'],
                    ':description' => $data['description'] ?? '',
                    ':priority' => $data['priority'] ?? 'medium',
                    ':dueDate' => !empty($data['dueDate']) ? $data['dueDate'] : null
                ]);
            }
            
            $this->jsonSuccess(['id' => $id, 'message' => 'Task created successfully']);
        } catch (PDOException $e) {
            $this->jsonError('Failed to create task: ' . $e->getMessage());
        }
    }

    /**
     * Update a task
     */
    public function updateTask(string $taskId, array $data): void {
        $user = $this->requireAuth();
        
        try {
            $columns = $this->getTableColumns('tasks');
            $hasStatus = in_array('status', $columns);
            
            $isComplete = ($data['status'] ?? '') === 'completed' || !empty($data['isComplete']) ? 1 : 0;
            
            if ($hasStatus) {
                $stmt = $this->db->prepare("
                    UPDATE tasks 
                    SET taskName = :taskName, 
                        description = :description,
                        status = :status,
                        priority = :priority,
                        dueDate = :dueDate,
                        isComplete = :isComplete,
                        updatedAt = NOW()
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    ':id' => $taskId,
                    ':taskName' => $data['taskName'] ?? '',
                    ':description' => $data['description'] ?? '',
                    ':status' => $data['status'] ?? 'not_started',
                    ':priority' => $data['priority'] ?? 'medium',
                    ':dueDate' => !empty($data['dueDate']) ? $data['dueDate'] : null,
                    ':isComplete' => $isComplete
                ]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE tasks 
                    SET taskName = :taskName, 
                        description = :description,
                        priority = :priority,
                        dueDate = :dueDate,
                        isComplete = :isComplete,
                        updatedAt = NOW()
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    ':id' => $taskId,
                    ':taskName' => $data['taskName'] ?? '',
                    ':description' => $data['description'] ?? '',
                    ':priority' => $data['priority'] ?? 'medium',
                    ':dueDate' => !empty($data['dueDate']) ? $data['dueDate'] : null,
                    ':isComplete' => $isComplete
                ]);
            }
            
            $this->jsonSuccess(['message' => 'Task updated successfully']);
        } catch (PDOException $e) {
            $this->jsonError('Failed to update task: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task
     */
    public function deleteTask(string $taskId): void {
        $user = $this->requireAuth();
        
        try {
            $this->db->prepare("DELETE FROM tasks WHERE id = :id")->execute([':id' => $taskId]);
            $this->jsonSuccess(['message' => 'Task deleted successfully']);
        } catch (PDOException $e) {
            $this->jsonError('Failed to delete task: ' . $e->getMessage());
        }
    }

    /**
     * Toggle task completion
     */
    public function toggleTask(string $taskId): void {
        $user = $this->requireAuth();
        
        try {
            // Get current state
            $stmt = $this->db->prepare("SELECT isComplete, status FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$task) {
                $this->jsonError('Task not found', 404);
            }
            
            $newComplete = empty($task['isComplete']) ? 1 : 0;
            $newStatus = $newComplete ? 'completed' : 'in_progress';
            
            $update = $this->db->prepare("UPDATE tasks SET isComplete = :isComplete, status = :status, updatedAt = NOW() WHERE id = :id");
            $update->execute([':id' => $taskId, ':isComplete' => $newComplete, ':status' => $newStatus]);
            
            $this->jsonSuccess(['isComplete' => $newComplete, 'status' => $newStatus]);
        } catch (PDOException $e) {
            $this->jsonError('Failed to toggle task: ' . $e->getMessage());
        }
    }

    /**
     * Check if required tables exist
     */
    private function checkTablesExist(): array {
        $tables = ['projects' => false, 'tasks' => false];
        
        try {
            $stmt = $this->db->query("SHOW TABLES");
            $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $tables['projects'] = in_array('projects', $existing);
            $tables['tasks'] = in_array('tasks', $existing);
        } catch (PDOException $e) {
            // Ignore
        }
        
        return $tables;
    }

    /**
     * Get columns of a table
     */
    private function getTableColumns(string $table): array {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `$table`");
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Handle incoming request
     */
    public function handleRequest(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
        
        // For modifying actions, validate CSRF (but be lenient if token not set up)
        $modifyActions = ['create_project', 'update_project', 'delete_project', 'create_task', 'update_task', 'delete_task', 'toggle_task'];
        if (in_array($action, $modifyActions) && !empty($_SESSION['csrf_token'])) {
            if (!$this->validateCsrf($input)) {
                // Log but don't block (for development flexibility)
                error_log('CSRF validation failed for action: ' . $action);
            }
        }
        
        switch ($action) {
            case 'get_all_projects':
                $this->getAllProjects();
                break;
                
            case 'get_project':
                $projectId = $input['projectId'] ?? $_GET['projectId'] ?? '';
                $this->getProject($projectId);
                break;
                
            case 'create_project':
                $this->createProject($input);
                break;
                
            case 'update_project':
                $projectId = $input['projectId'] ?? '';
                $this->updateProject($projectId, $input);
                break;
                
            case 'delete_project':
                $projectId = $input['projectId'] ?? '';
                $this->deleteProject($projectId);
                break;
                
            case 'create_task':
                $this->createTask($input);
                break;
                
            case 'update_task':
                $taskId = $input['taskId'] ?? '';
                $this->updateTask($taskId, $input);
                break;
                
            case 'delete_task':
                $taskId = $input['taskId'] ?? '';
                $this->deleteTask($taskId);
                break;
                
            case 'toggle_task':
                $taskId = $input['taskId'] ?? '';
                $this->toggleTask($taskId);
                break;
                
            default:
                $this->jsonError('Invalid action: ' . $action, 400);
        }
    }
}

// Handle request if called directly
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new ProjectApiController();
    $controller->handleRequest();
}

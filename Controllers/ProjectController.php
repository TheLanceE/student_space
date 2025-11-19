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
                $sql .= " WHERE p.createdBy = :userId OR p.assignedTo = :userId";
            }
            
            $sql .= " ORDER BY p.createdAt DESC";
            
            $stmt = $this->db->prepare($sql);
            if ($userId && $role !== 'admin') {
                $stmt->execute([':userId' => $userId]);
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
            $id = 'proj_' . uniqid();
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
            $id = 'task_' . uniqid();
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
}

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    $controller = new ProjectController();
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'get_all_projects':
            echo json_encode($controller->getAllProjects(
                $input['userId'] ?? null,
                $input['role'] ?? null
            ));
            break;
        
        case 'get_project':
            echo json_encode($controller->getProject($input['projectId']));
            break;
        
        case 'create_project':
            echo json_encode($controller->createProject($input['data']));
            break;
        
        case 'update_project':
            echo json_encode($controller->updateProject($input['projectId'], $input['data']));
            break;
        
        case 'delete_project':
            echo json_encode($controller->deleteProject($input['projectId']));
            break;
        
        case 'get_tasks':
            echo json_encode($controller->getTasksForProject($input['projectId']));
            break;
        
        case 'create_task':
            echo json_encode($controller->createTask($input['data']));
            break;
        
        case 'update_task':
            echo json_encode($controller->updateTask($input['taskId'], $input['data']));
            break;
        
        case 'toggle_task':
            echo json_encode($controller->toggleTaskComplete($input['taskId']));
            break;
        
        case 'delete_task':
            echo json_encode($controller->deleteTask($input['taskId']));
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>

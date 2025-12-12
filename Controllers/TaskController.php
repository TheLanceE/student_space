<?php
/**
 * TaskController - CRUD for tasks (Book-style)
 * Uses config::getConnexion(), handles only form POST actions (no JSON)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include_once(__DIR__ . '/../config.php');

class TaskController {
    private $db;
    private $defaultRedirect;

    public function __construct(){
        $this->db = config::getConnexion();
        $this->defaultRedirect = $_SERVER['HTTP_REFERER'] ?? '../Views/FrontOffice/taskList.php';
    }

    private function respond($success, $message = '', $redirect = null){
        if ($success) $_SESSION['flash_success'] = $message ?: 'Operation completed';
        else $_SESSION['flash_error'] = $message ?: 'Operation failed';
        $target = $redirect ?? $this->defaultRedirect;
        if ($target) { header('Location: '.$target); exit; }
        echo $success ? '<p>'.htmlspecialchars($message).'</p>' : '<p>Error: '.htmlspecialchars($message).'</p>';
    }

    public function getCurrentUserId(){ return $_SESSION['user']['id'] ?? 'stu_debug'; }

    private function projectExists($projectId){
        if (!$projectId) return false;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE id=:id");
        $stmt->execute([':id'=>$projectId]);
        return $stmt->fetchColumn() > 0;
    }

    private function handleFileUpload($taskId){
        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // no file uploaded
        }
        
        if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $_FILES['attachment']['error']);
        }
        
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($_FILES['attachment']['size'] > $maxSize) {
            throw new Exception('File too large (max 10MB)');
        }
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'];
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            throw new Exception('File type not allowed');
        }
        
        $uploadDir = __DIR__ . '/../uploads/task_attachments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = $taskId . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $filepath)) {
            throw new Exception('Failed to save file');
        }
        
        return 'uploads/task_attachments/' . $filename;
    }

    public function listTasks($projectId = null){
        try{
            $user = $this->getCurrentUserId();
            $role = $_SESSION['user']['role'] ?? 'student';
            // Teachers/admins see all tasks; students see tasks for projects they own or assigned to them
            if (in_array($role, ['teacher','admin'])) {
                if ($projectId) {
                    $stmt = $this->db->prepare("SELECT t.*, p.projectName FROM tasks t LEFT JOIN projects p ON t.projectId = p.id WHERE t.projectId = :projectId ORDER BY t.createdAt DESC");
                    $stmt->execute([':projectId'=>$projectId]);
                } else {
                    $stmt = $this->db->prepare("SELECT t.*, p.projectName FROM tasks t LEFT JOIN projects p ON t.projectId = p.id ORDER BY t.createdAt DESC");
                    $stmt->execute();
                }
            } else {
                if ($projectId) {
                    $stmt = $this->db->prepare("SELECT t.*, p.projectName FROM tasks t LEFT JOIN projects p ON t.projectId = p.id WHERE t.projectId = :projectId AND (p.createdBy = :user OR p.assignedTo = :user) ORDER BY t.createdAt DESC");
                    $stmt->execute([':projectId'=>$projectId, ':user'=>$user]);
                } else {
                    $stmt = $this->db->prepare("SELECT t.*, p.projectName FROM tasks t LEFT JOIN projects p ON t.projectId = p.id WHERE p.createdBy = :user OR p.assignedTo = :user ORDER BY t.createdAt DESC");
                    $stmt->execute([':user'=>$user]);
                }
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){ die('Error: '.$e->getMessage()); }
    }

    public function showTask($id){
        if (!$id) throw new Exception('Task ID required');
        $stmt = $this->db->prepare("SELECT t.*, p.projectName FROM tasks t LEFT JOIN projects p ON t.projectId=p.id WHERE t.id=:id");
        $stmt->execute([':id'=>$id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$task) throw new Exception('Task not found');
        return ['task'=>$task];
    }

    public function addTask($data){
        if (!$data || !isset($data['taskName'])) throw new Exception('Task data required');
            $name = trim($data['taskName']);
            // require at least 4 letters (Unicode aware)
            $letterCount = 0;
            if (preg_match_all('/\p{L}/u', $name, $m)) { $letterCount = count($m[0]); }
            if ($letterCount < 4) throw new Exception('Task name must contain at least 4 letters');
            // due date validation: if provided, cannot be before today
            if (!empty($data['dueDate'])){
                $today = date('Y-m-d');
                if ($data['dueDate'] < $today) throw new Exception('Due date must be today or later');
            }
        $projectId = $data['projectId'] ?? null;
        if (!$this->projectExists($projectId)) throw new Exception('Invalid project selected');

        // Gate: prevent creating beyond expectedTaskCount
        try {
            $stmtProj = $this->db->prepare('SELECT expectedTaskCount FROM projects WHERE id = :id');
            $stmtProj->execute([':id' => $projectId]);
            $proj = $stmtProj->fetch(PDO::FETCH_ASSOC);
            if ($proj && (int)$proj['expectedTaskCount'] > 0) {
                $expected = (int)$proj['expectedTaskCount'];
                $stmtCnt = $this->db->prepare('SELECT COUNT(*) AS c FROM tasks WHERE projectId = :pid');
                $stmtCnt->execute([':pid' => $projectId]);
                $created = (int)$stmtCnt->fetch(PDO::FETCH_ASSOC)['c'];
                if ($created >= $expected) {
                    throw new Exception('You reached the expected number of tasks. Increase it to add more.');
                }
            }
        } catch (Exception $e) {
            if ($e instanceof Exception) { throw $e; }
        }

        $id = 'task_'.bin2hex(random_bytes(8));
        
        // Handle file upload
        $attachmentPath = null;
        try {
            $attachmentPath = $this->handleFileUpload($id);
        } catch (Exception $e) {
            throw new Exception('Attachment error: ' . $e->getMessage());
        }
        
        // build columns dynamically to allow optional assignedTo if supplied (and only used when column exists in DB)
        $columns = ['id','projectId','taskName','description','priority','isComplete','dueDate','createdAt'];
        $placeholders = [':id',':projectId',':name',':desc',':priority',':isComplete',':dueDate','NOW()'];
        $bindings = [
            ':id'=>$id,
            ':projectId'=>$projectId,
                ':name'=>$name,
            ':desc'=>$data['description'] ?? '',
            ':priority'=>$data['priority'] ?? 'medium',
            ':isComplete'=>!empty($data['isComplete'])?1:0,
            ':dueDate'=>$data['dueDate'] ?? null
        ];

        if ($attachmentPath) {
            $columns[] = 'attachmentPath';
            $placeholders[] = ':attachmentPath';
            $bindings[':attachmentPath'] = $attachmentPath;
        }

        if (isset($data['assignedTo']) && $data['assignedTo'] !== ''){
            // include assignedTo only if the caller provided it; DB migration is expected to add this column
            $columns[] = 'assignedTo';
            $placeholders[] = ':assignedTo';
            $bindings[':assignedTo'] = $data['assignedTo'];
        }

        $sql = "INSERT INTO tasks (".implode(',', $columns).") VALUES (".implode(',', $placeholders).")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $id;
    }

    public function updateExistingTask($id,$data){
        if (!$id || !$data) throw new Exception('Task ID & data required');
        $projectId = $data['projectId'] ?? null;
        if ($projectId && !$this->projectExists($projectId)) throw new Exception('Invalid project selected');

        $sets = [];
        $bindings = [':id'=>$id];

        if (isset($data['taskName'])) {
            $name = trim($data['taskName']);
            // require at least 4 letters on update as well
            $letterCount = 0;
            if (preg_match_all('/\p{L}/u', $name, $m)) { $letterCount = count($m[0]); }
            if ($letterCount < 4) throw new Exception('Task name must contain at least 4 letters');
            $sets[] = 'taskName=:name'; $bindings[':name'] = $name;
        }
        $sets[] = 'description=:desc'; $bindings[':desc'] = $data['description'] ?? '';
        // Only update priority/isComplete if provided in the payload; we removed these from student forms
        if (array_key_exists('priority', $data)) { $sets[] = 'priority=:priority'; $bindings[':priority'] = $data['priority'] ?? 'medium'; }
        if (array_key_exists('isComplete', $data)) { $sets[] = 'isComplete=:isComplete'; $bindings[':isComplete'] = !empty($data['isComplete'])?1:0; }
        $sets[] = 'dueDate=:dueDate'; $bindings[':dueDate'] = $data['dueDate'] ?? null;

        // due date validation
        if (!empty($data['dueDate'])){
            $today = date('Y-m-d');
            if ($data['dueDate'] < $today) throw new Exception('Due date must be today or later');
        }

        // Handle file upload if provided
        try {
            $attachmentPath = $this->handleFileUpload($id);
            if ($attachmentPath) {
                $sets[] = 'attachmentPath=:attachmentPath';
                $bindings[':attachmentPath'] = $attachmentPath;
            }
        } catch (Exception $e) {
            throw new Exception('Attachment error: ' . $e->getMessage());
        }

        if (!empty($projectId)) { $sets[] = 'projectId=:projectId'; $bindings[':projectId'] = $projectId; }
        if (isset($data['assignedTo']) && $data['assignedTo'] !== ''){ $sets[] = 'assignedTo=:assignedTo'; $bindings[':assignedTo'] = $data['assignedTo']; }

        $sql = "UPDATE tasks SET ".implode(',', $sets).", updatedAt=NOW() WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return true;
    }

    public function deleteExistingTask($id){
        if (!$id) throw new Exception('Task ID required');
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        return true;
    }

    // handlers for form POST
    public function handleCreate(){
        try {
            $this->addTask($_POST['data'] ?? null);
            $redirect = $_POST['redirect'] ?? null;
            $this->respond(true,'Task created',$redirect);
        } catch (Exception $e) {
            $redirect = $_POST['redirect'] ?? null;
            $this->respond(false, $e->getMessage(), $redirect);
        }
    }
    public function handleUpdate(){
        $this->updateExistingTask($_POST['taskId'] ?? null, $_POST['data'] ?? null);
        $redirect = $_POST['redirect'] ?? null;
        $this->respond(true,'Task updated',$redirect);
    }
    public function handleDelete(){
        $this->deleteExistingTask($_POST['taskId'] ?? null);
        $redirect = $_POST['redirect'] ?? null;
        $this->respond(true,'Task deleted',$redirect);
    }

}

// POST/Direct entrypoint: only run this block when this file is requested directly
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $c = new TaskController(); $action = $_POST['action'] ?? '';
        switch($action){
            case 'create_task': $c->handleCreate(); break;
            case 'update_task': $c->handleUpdate(); break;
            case 'delete_task': $c->handleDelete(); break;
            default: $_SESSION['flash_error'] = 'Invalid action: '.htmlspecialchars($action); header('Location:'.($_SERVER['HTTP_REFERER']??'../Views/FrontOffice/taskList.php')); break;
        }
    } elseif (!empty($_GET['action']) && $_GET['action']==='test'){
        echo '<p>TaskController is working. User: '.htmlspecialchars((new TaskController())->getCurrentUserId()).'</p>';
    }
}

<?php
/**
 * ProjectController - FINAL (single clean implementation)
 * Uses config::getConnexion(), handles only form POST actions (no JSON)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include_once(__DIR__ . '/../config.php');

class ProjectController {
    private $db;
    private $defaultRedirect;

    public function __construct() {
        $this->db = config::getConnexion();
        $this->defaultRedirect = $_SERVER['HTTP_REFERER'] ?? '../Views/projects_student.php';
    }

    private function respond($success, $message = '', $redirect = null) {
        if ($success) $_SESSION['flash_success'] = $message ?: 'Operation completed';
        else $_SESSION['flash_error'] = $message ?: 'Operation failed';

        $target = $redirect ?? $this->defaultRedirect;
        if ($target) {
            header('Location: ' . $target);
            exit;
        }

        echo $success ? '<p>' . htmlspecialchars($message) . '</p>' : '<p>Error: ' . htmlspecialchars($message) . '</p>';
    }

    public function getCurrentUserId() { return $_SESSION['user']['id'] ?? 'stu_debug'; }

    private function requireTeacher() {
        $role = $_SESSION['user']['role'] ?? '';
        if ($role !== 'teacher') {
            throw new Exception('Only teachers can react');
        }
    }

    public function listProjectMembers($projectId){
        if (!$projectId) return [];
        try{
            // ensure mapping table exists before querying
            $check = $this->db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=:tbl LIMIT 1");
            $check->execute([':tbl' => 'project_members']);
            if ($check->fetch() === false) return [];

            $stmt = $this->db->prepare("SELECT pm.userId, pm.role, pm.addedAt,
                (SELECT COUNT(*) FROM tasks t WHERE t.projectId=pm.projectId AND t.assignedTo=pm.userId AND t.isComplete=1) as submittedCount
                FROM project_members pm WHERE pm.projectId=:projectId");
            $stmt->execute([':projectId'=>$projectId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r){ $r['submitted'] = ((int)($r['submittedCount'] ?? 0)) > 0; }
            return $rows;
        } catch (PDOException $e) {
            // table may not exist; return empty member list so UI can fall back
            return [];
        }
    }

    public function listProjects() {
        try {
            $userId = $this->getCurrentUserId();
            $role = $_SESSION['user']['role'] ?? 'student';

            // Teachers/admins see all projects; students see only theirs
            $baseSql = "SELECT p.*, COUNT(t.id) as taskCount, SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completedTasks,
                (SELECT r.type FROM reactions r JOIN users u ON u.id = r.userId WHERE r.projectId = p.id AND r.userId = :userId AND u.role = 'teacher' ORDER BY r.updatedAt DESC LIMIT 1) AS myReaction,
                (SELECT r.type FROM reactions r JOIN users u ON u.id = r.userId WHERE r.projectId = p.id AND u.role = 'teacher' ORDER BY r.updatedAt DESC LIMIT 1) AS latestReaction
                FROM projects p LEFT JOIN tasks t ON p.id=t.projectId";

            if (in_array($role, ['teacher','admin'])) {
                $sql = $baseSql . " GROUP BY p.id ORDER BY p.createdAt DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':userId' => $userId]);
            } else {
                $sql = $baseSql . " WHERE p.createdBy=:userId OR p.assignedTo=:userId GROUP BY p.id ORDER BY p.createdAt DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':userId' => $userId]);
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { die('Error: ' . $e->getMessage()); }
    }

    public function showProject($id) {
        if (!$id) throw new Exception('Project ID is required');
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id=:id"); $stmt->execute([':id'=>$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC); if (!$project) throw new Exception('Not found');
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE projectId=:projectId ORDER BY createdAt DESC"); $stmt->execute([':projectId'=>$id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute progress based on task status
        $expected = (int)($project['expectedTaskCount'] ?? 0);
        $createdCount = count($tasks);
        $completedCount = 0;
        foreach ($tasks as $t) { if (($t['status'] ?? 'not_started') === 'completed') { $completedCount++; } }
        $totalForProgress = $expected > 0 ? $expected : ($createdCount > 0 ? $createdCount : 0);
        $project['completionPercentage'] = $totalForProgress>0 ? min(100, round(($completedCount/$totalForProgress)*100)) : 0;
        $project['memberCount'] = 0;
        $project['completedMemberCount'] = $completedCount;
        // attach reaction info: latest and current user's reaction
        try {
            $stmtR = $this->db->prepare("SELECT r.type, r.userId FROM reactions r JOIN users u ON u.id = r.userId WHERE r.projectId = :pid AND u.role = 'teacher' ORDER BY r.updatedAt DESC LIMIT 1");
            $stmtR->execute([':pid' => $id]);
            $rowR = $stmtR->fetch(PDO::FETCH_ASSOC);
            $project['latestReaction'] = $rowR['type'] ?? null;
            $project['latestReactionBy'] = $rowR['userId'] ?? null;
            $uid = $this->getCurrentUserId();
            $stmtUR = $this->db->prepare("SELECT r.type FROM reactions r JOIN users u ON u.id = r.userId WHERE r.projectId = :pid AND r.userId = :uid AND u.role = 'teacher' ORDER BY r.updatedAt DESC LIMIT 1");
            $stmtUR->execute([':pid' => $id, ':uid' => $uid]);
            $project['myReaction'] = $stmtUR->fetchColumn() ?: null;
        } catch (Exception $e) {
            // ignore reaction fetch errors
        }
        return ['project'=>$project,'tasks'=>$tasks];
    }

    public function addProject($data) {
        if (!$data || !isset($data['projectName'])) throw new Exception('Project data required');
        $name = trim($data['projectName']);
        // require at least 4 letters (Unicode-aware)
        $letterCount = 0;
        if (preg_match_all('/\p{L}/u', $name, $m)) { $letterCount = count($m[0]); }
        if ($letterCount < 4) throw new Exception('Project name must contain at least 4 letters');

        // description validation (optional but recommended)
        $desc = trim($data['description'] ?? '');
        if (strlen($desc) > 0 && mb_strlen($desc) < 10) throw new Exception('Description must be at least 10 characters');

        // due date validation
        if (!empty($data['dueDate'])){
            $today = date('Y-m-d');
            if ($data['dueDate'] < $today) throw new Exception('Due date must be today or later');
        }

        $id = 'proj_'.bin2hex(random_bytes(8)); $user = $this->getCurrentUserId();
        $expectedTaskCount = (int)($data['expectedTaskCount'] ?? 0);
        if ($expectedTaskCount < 1) throw new Exception('Expected task count must be at least 1');
        $stmt = $this->db->prepare("INSERT INTO projects (id,projectName,description,createdBy,assignedTo,status,dueDate,expectedTaskCount,createdAt) VALUES (:id,:name,:desc,:createdBy,:assignedTo,:status,:dueDate,:expectedTaskCount,NOW())");
        $stmt->execute([':id'=>$id,':name'=>$name,':desc'=>$desc,':createdBy'=>$user,':assignedTo'=>$user,':status'=>$data['status']??'not_started',':dueDate'=>$data['dueDate']??null,':expectedTaskCount'=>$expectedTaskCount]);
        return $id;
    }

    public function updateExistingProject($id,$data) {
        if (!$id || !$data) throw new Exception('Project ID & data required');
        $expectedTaskCount = (int)($data['expectedTaskCount'] ?? null);
        if ($expectedTaskCount !== null && $expectedTaskCount < 1) throw new Exception('Expected task count must be at least 1');
        
        if ($expectedTaskCount !== null) {
            $stmt = $this->db->prepare("UPDATE projects SET projectName=:name,description=:desc,status=:status,dueDate=:dueDate,expectedTaskCount=:expectedTaskCount,updatedAt=NOW() WHERE id=:id");
            $stmt->execute([':id'=>$id,':name'=>$data['projectName'],':desc'=>$data['description']??'',':status'=>$data['status']??'not_started',':dueDate'=>$data['dueDate']??null,':expectedTaskCount'=>$expectedTaskCount]);
        } else {
            $stmt = $this->db->prepare("UPDATE projects SET projectName=:name,description=:desc,status=:status,dueDate=:dueDate,updatedAt=NOW() WHERE id=:id");
            $stmt->execute([':id'=>$id,':name'=>$data['projectName'],':desc'=>$data['description']??'',':status'=>$data['status']??'not_started',':dueDate'=>$data['dueDate']??null]);
        }
        return true;
    }

    public function deleteExistingProject($id){
        if (!$id) throw new Exception('Project ID required');
        // delete reactions linked to this project if the table exists
        try {
            $stmt = $this->db->prepare("DELETE FROM reactions WHERE projectId=:projectId");
            $stmt->execute([':projectId'=>$id]);
        } catch (Exception $e) {}
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE projectId=:projectId"); $stmt->execute([':projectId'=>$id]);
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id=:id"); $stmt->execute([':id'=>$id]);
        return true;
    }

    public function updateReaction($projectId, $reaction) {
        if (!$projectId) throw new Exception('Project ID required');
        $this->requireTeacher();
        $userId = $this->getCurrentUserId();

        // remove reaction for current user
        if (empty($reaction)) {
            $stmt = $this->db->prepare("DELETE FROM reactions WHERE projectId = :pid AND userId = :uid");
            $stmt->execute([':pid' => $projectId, ':uid' => $userId]);
            return 'removed';
        }

        // check if reaction exists to tailor message
        $stmtCheck = $this->db->prepare("SELECT 1 FROM reactions WHERE projectId = :pid AND userId = :uid LIMIT 1");
        $stmtCheck->execute([':pid' => $projectId, ':uid' => $userId]);
        $exists = (bool)$stmtCheck->fetchColumn();

        // upsert reaction (prevent duplicates per user/project)
        $id = 'react_' . bin2hex(random_bytes(8));
        $sql = "INSERT INTO reactions (id, projectId, userId, type, createdAt, updatedAt)
                VALUES (:id, :pid, :uid, :type, NOW(), NOW())
                ON DUPLICATE KEY UPDATE type = VALUES(type), updatedAt = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':pid' => $projectId, ':uid' => $userId, ':type' => $reaction]);
        return $exists ? 'updated' : 'created';
    }

    // handlers
    public function handleCreate(){
        $this->addProject($_POST['data'] ?? null);
        $redirect = $_POST['redirect'] ?? null;
        $this->respond(true,'Project created',$redirect);
    }
    public function handleUpdate(){
        $this->updateExistingProject($_POST['projectId'] ?? null, $_POST['data'] ?? null);
        $redirect = $_POST['redirect'] ?? null;
        $this->respond(true,'Project updated',$redirect);
    }
    public function handleDelete(){
        $this->deleteExistingProject($_POST['projectId'] ?? null);
        $redirect = $_POST['redirect'] ?? null;
        $this->respond(true,'Project deleted',$redirect);
    }
    public function handleReaction(){
        try {
            $projectId = $_POST['projectId'] ?? null;
            $reaction = $_POST['reaction'] ?? null;
            $result = $this->updateReaction($projectId, $reaction);
            $msg = 'Reaction saved';
            if ($result === 'removed') $msg = 'Reaction removed';
            elseif ($result === 'updated') $msg = 'Reaction updated';
            elseif ($result === 'created') $msg = 'Reaction saved';
            $this->respond(true, $msg);
        } catch (Exception $e) {
            $this->respond(false, $e->getMessage());
        }
    }

}

// POST/Direct entrypoint: only run this block when this file is requested directly
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $c = new ProjectController(); $action = $_POST['action'] ?? '';
        switch($action){
            case 'create_project': $c->handleCreate(); break;
            case 'update_project': $c->handleUpdate(); break;
            case 'delete_project': $c->handleDelete(); break;
            case 'update_reaction': $c->handleReaction(); break;
            default: $_SESSION['flash_error']='Invalid action: '.htmlspecialchars($action); header('Location:'.($_SERVER['HTTP_REFERER']??'../Views/projects_student.php')); break;
        }
    } elseif (!empty($_GET['action']) && $_GET['action']==='test'){
        echo '<p>ProjectController is working. User: '.htmlspecialchars((new ProjectController())->getCurrentUserId()).'</p>';
    }
}

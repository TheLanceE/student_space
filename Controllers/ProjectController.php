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
            $stmt = $this->db->prepare("SELECT p.*, COUNT(t.id) as taskCount, SUM(CASE WHEN t.isComplete = 1 THEN 1 ELSE 0 END) as completedTasks FROM projects p LEFT JOIN tasks t ON p.id=t.projectId WHERE p.createdBy=:userId OR p.assignedTo=:userId GROUP BY p.id ORDER BY p.createdAt DESC");
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { die('Error: ' . $e->getMessage()); }
    }

    public function showProject($id) {
        if (!$id) throw new Exception('Project ID is required');
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id=:id"); $stmt->execute([':id'=>$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC); if (!$project) throw new Exception('Not found');
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE projectId=:projectId ORDER BY createdAt DESC"); $stmt->execute([':projectId'=>$id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Try to compute completion based on project members when mapping exists
        try {
            // Count project members
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM project_members WHERE projectId=:projectId");
            $stmt->execute([':projectId'=>$id]);
            $memberCount = (int)$stmt->fetchColumn();

            if ($memberCount > 0) {
                // Count distinct members who have at least one completed task assigned to them for this project
                $stmt = $this->db->prepare("SELECT COUNT(DISTINCT assignedTo) FROM tasks WHERE projectId=:projectId AND isComplete=1 AND assignedTo IS NOT NULL");
                $stmt->execute([':projectId'=>$id]);
                $completedMembers = (int)$stmt->fetchColumn();
                $project['completionPercentage'] = round(($completedMembers / $memberCount) * 100);
                $project['memberCount'] = $memberCount;
                $project['completedMemberCount'] = $completedMembers;
            } else {
                // Progress based on tasks created vs expected count
                $expected = (int)($project['expectedTaskCount'] ?? 0);
                $createdCount = count($tasks);
                $totalForProgress = $expected > 0 ? $expected : $createdCount;
                $project['completionPercentage'] = $totalForProgress>0?round(($createdCount/$totalForProgress)*100):0;
                $project['memberCount'] = 0;
                $project['completedMemberCount'] = array_reduce($tasks,function($c,$t){return $c+(!empty($t['isComplete'])?1:0);},0);
            }
        } catch (PDOException $e) {
            // project_members table likely missing; fallback to task-created based progress
            $expected = (int)($project['expectedTaskCount'] ?? 0);
            $createdCount = count($tasks);
            $totalForProgress = $expected > 0 ? $expected : $createdCount;
            $project['completionPercentage'] = $totalForProgress>0?round(($createdCount/$totalForProgress)*100):0;
            $project['memberCount'] = 0;
            $project['completedMemberCount'] = array_reduce($tasks,function($c,$t){return $c+(!empty($t['isComplete'])?1:0);},0);
        }
        return ['project'=>$project,'tasks'=>$tasks];
    }

    public function addProject($data) {
        if (!$data || !isset($data['projectName'])) throw new Exception('Project data required');
        $id = 'proj_'.bin2hex(random_bytes(8)); $user = $this->getCurrentUserId();
        $expectedTaskCount = (int)($data['expectedTaskCount'] ?? 0);
        if ($expectedTaskCount < 1) throw new Exception('Expected task count must be at least 1');
        $stmt = $this->db->prepare("INSERT INTO projects (id,projectName,description,createdBy,assignedTo,status,dueDate,expectedTaskCount,createdAt) VALUES (:id,:name,:desc,:createdBy,:assignedTo,:status,:dueDate,:expectedTaskCount,NOW())");
        $stmt->execute([':id'=>$id,':name'=>$data['projectName'],':desc'=>$data['description']??'',':createdBy'=>$user,':assignedTo'=>$user,':status'=>$data['status']??'not_started',':dueDate'=>$data['dueDate']??null,':expectedTaskCount'=>$expectedTaskCount]);
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
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE projectId=:projectId"); $stmt->execute([':projectId'=>$id]);
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id=:id"); $stmt->execute([':id'=>$id]);
        return true;
    }

    public function updateReaction($projectId, $reaction) {
        if (!$projectId) throw new Exception('Project ID required');
        $userId = $this->getCurrentUserId();
        // reaction can be null to remove it, or a string (emoji)
        // reactedBy stores the user ID who reacted
        if (empty($reaction)) {
            $stmt = $this->db->prepare("UPDATE projects SET reaction=NULL, reactedBy=NULL, updatedAt=NOW() WHERE id=:id");
            $stmt->execute([':id'=>$projectId]);
        } else {
            $stmt = $this->db->prepare("UPDATE projects SET reaction=:reaction, reactedBy=:reactedBy, updatedAt=NOW() WHERE id=:id");
            $stmt->execute([':id'=>$projectId, ':reaction'=>$reaction, ':reactedBy'=>$userId]);
        }
        return true;
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
        $projectId = $_POST['projectId'] ?? null;
        $reaction = $_POST['reaction'] ?? null;
        $this->updateReaction($projectId, $reaction);
        $this->respond(true,'Reaction updated');
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

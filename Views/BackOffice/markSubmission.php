<?php
// Mark submission for a project member (BackOffice)
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
include(__DIR__ . '/../../Controllers/TaskController.php');
$tctrl = new TaskController();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $projectId = $_POST['projectId'] ?? null;
  $userId = $_POST['userId'] ?? null;
  $redirect = $_POST['redirect'] ?? 'projectList.php';
  if (!$projectId || !$userId){ $_SESSION['flash_error']='Missing project or user'; header('Location: '.$redirect); exit; }

  try{
    // use config::getConnexion() instead of accessing controller internals
    include_once(__DIR__ . '/../../config.php');
    $db = config::getConnexion();
    $stmt = $db->prepare("SELECT id FROM tasks WHERE projectId=:projectId AND assignedTo=:userId ORDER BY createdAt DESC LIMIT 1");
    $stmt->execute([':projectId'=>$projectId,':userId'=>$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['id'])){
      // mark it complete
      $tctrl->updateExistingTask($row['id'], ['isComplete'=>1]);
    } else {
      // create a placeholder completed task assigned to the user
      $tctrl->addTask(['taskName'=>'Submission by '.$userId,'projectId'=>$projectId,'description'=>'Submitted by teacher/admin','dueDate'=>date('Y-m-d'),'assignedTo'=>$userId,'isComplete'=>1]);
    }
    $_SESSION['flash_success']='Member marked as submitted';
  }catch(Exception $e){ $_SESSION['flash_error']='Mark failed: '.$e->getMessage(); }

  header('Location: '.$redirect); exit;
}

header('Location: projectList.php'); exit;

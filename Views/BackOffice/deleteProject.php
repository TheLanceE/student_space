<?php
// BackOffice deleteProject - calls ProjectController::deleteExistingProject()
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();
$id = $_GET['projectId'] ?? null;
if ($id) {
  try{
    $pctrl->deleteExistingProject($id);
    $_SESSION['flash_success'] = 'Project deleted';
  }catch(Exception $e){
    $_SESSION['flash_error'] = 'Delete failed: '.$e->getMessage();
  }
}
header('Location: projectList.php');
exit;

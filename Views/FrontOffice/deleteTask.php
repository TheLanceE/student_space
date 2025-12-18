<?php
// FrontOffice deleteTask - POST endpoint that forwards to TaskController
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
include_once(__DIR__ . '/../../Controllers/TaskController.php');
$tctrl = new TaskController();
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $id = $_POST['taskId'] ?? null;
  $redirect = $_POST['redirect'] ?? 'taskList.php';
  if ($id){
    try{ $tctrl->deleteExistingTask($id); $_SESSION['flash_success']='Task deleted'; }catch(Exception $e){ $_SESSION['flash_error']='Delete failed: '.$e->getMessage(); }
  }
  header('Location: '.$redirect); exit;
}
header('Location: taskList.php'); exit;

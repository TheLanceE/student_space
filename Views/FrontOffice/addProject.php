<?php
// FrontOffice addProject - uses ProjectController::addProject()
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    $id = $pctrl->addProject($_POST['data'] ?? null);
    $_SESSION['flash_success'] = 'Project created';
  }catch(Exception $e){
    $_SESSION['flash_error'] = 'Create failed: ' . $e->getMessage();
  }
  header('Location: projectList.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Project - FrontOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3>Add Project (FrontOffice)</h3>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Project Name</label>
        <input class="form-control" name="data[projectName]" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="data[description]"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="data[status]">
          <option value="not_started">Not Started</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="on_hold">On Hold</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]">
      </div>
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn btn-secondary" href="projectList.php">Cancel</a>
    </form>
  </div>
</body>
</html>

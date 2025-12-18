<?php
// Update form for FrontOffice
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();
$projectId = $_GET['projectId'] ?? null;
if (!$projectId) { header('Location: projectList.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    $pctrl->updateExistingProject($_POST['projectId'] ?? null, $_POST['data'] ?? null);
    $_SESSION['flash_success'] = 'Project updated';
  }catch(Exception $e){ $_SESSION['flash_error'] = 'Update failed: '.$e->getMessage(); }
  header('Location: projectList.php'); exit;
}

$show = $pctrl->showProject($projectId);
$proj = $show['project'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Project</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3>Edit Project</h3>
    <form method="POST">
      <input type="hidden" name="projectId" value="<?= htmlspecialchars($proj['id']) ?>">
      <div class="mb-3">
        <label class="form-label">Project Name</label>
        <input class="form-control" name="data[projectName]" value="<?= htmlspecialchars($proj['projectName']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="data[description]"><?= htmlspecialchars($proj['description'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="data[status]">
          <option value="not_started" <?= ($proj['status'] === 'not_started' ? 'selected' : '') ?>>Not Started</option>
          <option value="in_progress" <?= ($proj['status'] === 'in_progress' ? 'selected' : '') ?>>In Progress</option>
          <option value="completed" <?= ($proj['status'] === 'completed' ? 'selected' : '') ?>>Completed</option>
          <option value="on_hold" <?= ($proj['status'] === 'on_hold' ? 'selected' : '') ?>>On Hold</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]" value="<?= htmlspecialchars($proj['dueDate'] ?? '') ?>">
      </div>
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn btn-secondary" href="projectList.php">Cancel</a>
    </form>
  </div>
</body>
</html>

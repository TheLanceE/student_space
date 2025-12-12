<?php
// BackOffice addTask (posts to TaskController)
session_start();
include(__DIR__ . '/../../Controllers/TaskController.php');
include(__DIR__ . '/../../Controllers/ProjectController.php');

// Capture the projectId from URL parameter
$projectId = $_GET['projectId'] ?? null;

$tctrl = new TaskController();
$pctrl = new ProjectController();
$projects = $pctrl->listProjects();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{ $tctrl->addTask($_POST['data'] ?? null); $_SESSION['flash_success']='Task created'; }catch(Exception $e){ $_SESSION['flash_error']='Create failed: '.$e->getMessage(); }
  $redirect = $projectId ? 'taskList.php?projectId=' . urlencode($projectId) : 'taskList.php';
  header('Location: ' . $redirect); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Task - BackOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3>New Task (BackOffice)</h3>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Task Name</label>
        <input class="form-control" name="data[taskName]" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Project</label>
        <select class="form-select" name="data[projectId]" required>
          <?php if (empty($projects)): ?>
            <option value="">(no projects available)</option>
          <?php else: ?>
            <?php foreach ($projects as $i => $proj): ?>
              <option value="<?= htmlspecialchars($proj['id']) ?>" <?= ($i===0 ? 'selected' : '') ?>><?= htmlspecialchars($proj['projectName']) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="data[description]"></textarea>
      </div>
      <div class="mb-3 form-check">
        <input class="form-check-input" type="checkbox" id="isComplete" name="data[isComplete]" value="1">
        <label class="form-check-label" for="isComplete">Completed</label>
      </div>
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]">
      </div>
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">Cancel</a>
    </form>
  </div>
</body>
</html>

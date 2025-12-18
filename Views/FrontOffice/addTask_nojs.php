<?php
// No-JS copy of addTask for debugging/input testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sessionStarted = session_status() === PHP_SESSION_ACTIVE ? true : session_start();
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');

try {
  $tctrl = new TaskController();
} catch (Throwable $e) {
  echo '<div class="container py-4"><div class="alert alert-danger">TaskController init error: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}
try {
  $pctrl = new ProjectController();
} catch (Throwable $e) {
  echo '<div class="container py-4"><div class="alert alert-danger">ProjectController init error: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}
try {
  $projects = $pctrl->listProjects();
} catch (Exception $e) {
  echo '<div class="container py-4"><div class="alert alert-danger">Error loading projects: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{ $tctrl->addTask($_POST['data'] ?? null); $_SESSION['flash_success']='Task created'; }catch(Exception $e){ $_SESSION['flash_error']='Create failed: '.$e->getMessage(); }
  header('Location: taskList.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Task (no JS)</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3>New Task (no-JS)</h3>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Task Name</label>
        <input class="form-control" name="data[taskName]" required minlength="4">
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
        <textarea class="form-control" name="data[description]" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]" min="<?= date('Y-m-d') ?>">
      </div>
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn btn-secondary" href="taskList.php">Cancel</a>
    </form>
  </div>
</body>
</html>
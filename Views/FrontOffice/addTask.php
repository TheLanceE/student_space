<?php
// FrontOffice addTask (posts to TaskController)
// Temporarily enable error display for debugging this view
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sessionStarted = session_status() === PHP_SESSION_ACTIVE ? true : session_start();
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');

// Capture the projectId from URL parameter
$projectId = $_GET['projectId'] ?? null;

// instantiate controllers with graceful error reporting so we don't render a blank page
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
  // show the exception message so the dev can see why the page 500s
  echo '<div class="container py-4"><div class="alert alert-danger">Error loading projects: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}
$firstProjectId = $projects[0]['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  // Use controller handler via POST endpoint to keep behavior consistent
  // But we can also call addTask directly; here we'll forward to controller by calling addTask()
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
  <title>Add Task</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3>New Task</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Task Name</label>
        <input class="form-control" id="taskName" name="data[taskName]">
        <div id="taskNameError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Project</label>
        <select class="form-select" id="taskProject" name="data[projectId]">
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
        <textarea class="form-control" id="taskDescription" name="data[description]"></textarea>
        <div id="taskDescError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
      </div>
      <!-- Priority and completed flag removed for student task creation -->
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Attachment (Optional)</label>
        <input type="file" class="form-control" name="attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
        <small class="form-text text-muted">Allowed: images, PDF, Office docs, ZIP (max 10MB)</small>
      </div>
      <button id="submitBtn" class="btn btn-primary" type="submit">Create</button>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">Cancel</a>
    </form>
  </div>
  <script src="../../assets/js/taskNameValidator.js"></script>
  <script src="../../assets/js/descriptionValidator.js"></script>
</body>
</html>

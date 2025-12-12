<?php
// FrontOffice updateTask
$sessionStarted = session_status() === PHP_SESSION_ACTIVE ? true : session_start();
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$tctrl = new TaskController();
$pctrl = new ProjectController();
$taskId = $_GET['taskId'] ?? null;
$projectId = $_GET['projectId'] ?? null;
if (!$taskId) { header('Location: taskList.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    $tctrl->updateExistingTask($_POST['taskId'] ?? null, $_POST['data'] ?? null);
    $_SESSION['flash_success']='Task updated';
  }catch(Exception $e){ $_SESSION['flash_error']='Update failed: '.$e->getMessage(); }
  $redirect = $projectId ? 'taskList.php?projectId=' . urlencode($projectId) : 'taskList.php';
  header('Location: ' . $redirect); exit;
}

$res = $tctrl->showTask($taskId);
$task = $res['task'];
try {
  $projects = $pctrl->listProjects();
} catch (Exception $e) { $projects = []; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Task</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3>Edit Task</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="taskId" value="<?= htmlspecialchars($task['id']) ?>">
      <div class="mb-3">
        <label class="form-label">Task Name</label>
        <input class="form-control" id="taskName" name="data[taskName]" value="<?= htmlspecialchars($task['taskName']) ?>">
        <div id="taskNameError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Project</label>
        <select class="form-select" id="taskProject" name="data[projectId]">
          <?php if (empty($projects)): ?>
            <option value="">(no projects)</option>
          <?php else: ?>
            <?php foreach ($projects as $i => $proj): ?>
              <option value="<?= htmlspecialchars($proj['id']) ?>" <?= ($proj['id']===$task['projectId']?'selected':'') ?>><?= htmlspecialchars($proj['projectName']) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" id="taskDescription" name="data[description]"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
        <div id="taskDescError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
      </div>
      <!-- Priority and completed controls removed for students -->
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]" value="<?= htmlspecialchars($task['dueDate'] ?? '') ?>" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Attachment (Optional)</label>
        <input type="file" class="form-control" name="attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
        <small class="form-text text-muted">Allowed: images, PDF, Office docs, ZIP (max 10MB)</small>
        <?php if (!empty($task['attachmentPath'])): ?>
          <p class="mt-2"><strong>Current attachment:</strong> <a href="../../<?= htmlspecialchars($task['attachmentPath']) ?>" target="_blank">View File</a></p>
        <?php endif; ?>
      </div>
      <button id="submitBtn" class="btn btn-primary" type="submit">Save</button>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">Cancel</a>
    </form>
  </div>
  <script src="../../assets/js/taskNameValidator.js"></script>
  <script src="../../assets/js/descriptionValidator.js"></script>
</body>
</html>

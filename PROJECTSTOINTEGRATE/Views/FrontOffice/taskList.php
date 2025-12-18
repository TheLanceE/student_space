<?php
// FrontOffice Task list
session_start();
// Set default student session if not already set
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['id' => 'stu_debug', 'username' => 'debug_student', 'role' => 'student'];
}
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$tctrl = new TaskController();
$pctrl = new ProjectController();
$projectId = $_GET['projectId'] ?? null;
$tasks = $tctrl->listTasks($projectId);
$projectName = null;
$expectedTaskCount = null;
$createdCount = count($tasks);
if ($projectId) {
    try {
        $projectData = $pctrl->showProject($projectId);
        $projectName = $projectData['project']['projectName'] ?? null;
    $expectedTaskCount = $projectData['project']['expectedTaskCount'] ?? null;
    } catch (Exception $e) {
        $projectName = null;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tasks - FrontOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3>My Tasks<?= $projectName ? ' - ' . htmlspecialchars($projectName) : '' ?></h3>
      </div>
      <div>
        <a class="btn btn-outline-secondary me-2" href="projects_student.php">← Back to Projects</a>
        <?php
          $atLimit = ($projectId && $expectedTaskCount !== null && (int)$expectedTaskCount > 0 && $createdCount >= (int)$expectedTaskCount);
        ?>
        <?php if ($atLimit): ?>
          <button class="btn btn-primary" type="button" disabled title="Increase expected tasks to add more">New Task</button>
        <?php else: ?>
          <a class="btn btn-primary" href="addTask.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">New Task</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if (($projectId && $expectedTaskCount !== null && (int)$expectedTaskCount > 0) && $createdCount >= (int)$expectedTaskCount): ?>
      <div class="alert alert-success">🎉 Fantastic! You've created all <?= htmlspecialchars((string)$expectedTaskCount) ?> tasks you planned.</div>
    <?php endif; ?>

    <?php if (empty($tasks)): ?>
      <p>No tasks yet.</p>
    <?php else: ?>
      <div class="list-group">
        <?php foreach ($tasks as $task): ?>
          <div class="list-group-item">
            <div class="d-flex justify-content-between">
              <div>
                <?php
                  $status = $task['status'] ?? 'not_started';
                  $labelMap = [
                    'not_started' => 'not started',
                    'in_progress' => 'in progress',
                    'completed' => 'completed',
                    'on_hold' => 'on hold'
                  ];
                  $classMap = [
                    'not_started' => 'secondary',
                    'in_progress' => 'primary',
                    'completed' => 'success',
                    'on_hold' => 'warning'
                  ];
                  $badgeClass = $classMap[$status] ?? 'secondary';
                  $badgeLabel = $labelMap[$status] ?? htmlspecialchars(ucfirst(str_replace('_',' ',$status)));
                ?>
                <h6>
                  <?= htmlspecialchars($task['taskName']) ?>
                  <span class="badge rounded-pill bg-<?= $badgeClass ?> align-middle" style="vertical-align: middle;"><?= $badgeLabel ?></span>
                  <small class="text-muted">(<?= htmlspecialchars($task['projectName'] ?? 'No Project') ?>)</small>
                </h6>
                <p class="mb-1 text-muted"><?= htmlspecialchars($task['description'] ?? '') ?></p>
              </div>
              <div class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="showTask.php?taskId=<?= urlencode($task['id']) ?><?= $projectId ? '&projectId=' . urlencode($projectId) : '' ?>">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="updateTask.php?taskId=<?= urlencode($task['id']) ?><?= $projectId ? '&projectId=' . urlencode($projectId) : '' ?>">Edit</a>
                <form method="POST" action="deleteTask.php" class="d-inline" onsubmit="return confirm('Delete task?');">
                  <input type="hidden" name="action" value="delete_task">
                  <input type="hidden" name="taskId" value="<?= htmlspecialchars($task['id']) ?>">
                  <input type="hidden" name="redirect" value="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

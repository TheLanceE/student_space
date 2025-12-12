<?php
// FrontOffice showTask
session_start();
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$tctrl = new TaskController();
$pctrl = new ProjectController();
$taskId = $_GET['taskId'] ?? null;
$projectId = $_GET['projectId'] ?? null;
$fromModal = $_GET['fromModal'] ?? null;
if (!$taskId) { header('Location: taskList.php'); exit; }
$res = $tctrl->showTask($taskId);
$task = $res['task'];
// If projectId not in URL, get it from the task
if (!$projectId && !empty($task['projectId'])) {
    $projectId = $task['projectId'];
}
// Get project details to show project due date
$project = null;
if ($projectId) {
    try {
        $projectData = $pctrl->showProject($projectId);
        $project = $projectData['project'];
    } catch (Exception $e) {
        $project = null;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Task</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3><?= htmlspecialchars($task['taskName']) ?></h3>
    <p><?= htmlspecialchars($task['description'] ?? '') ?></p>
    <p><strong>Project:</strong> <?= htmlspecialchars($task['projectName'] ?? ($task['projectId'] ?? 'N/A')) ?></p>
    <?php if ($project && !empty($project['dueDate'])): ?>
      <p><strong>Project Due Date:</strong> <?= htmlspecialchars(date('M j, Y', strtotime($project['dueDate']))) ?></p>
    <?php endif; ?>
    <?php if (!empty($task['dueDate'])): ?>
      <p><strong>Task Due Date:</strong> <?= htmlspecialchars(date('M j, Y', strtotime($task['dueDate']))) ?></p>
    <?php endif; ?>
    <?php if (!empty($task['attachmentPath'])): ?>
      <p><strong>Attachment:</strong> <a href="../../<?= htmlspecialchars($task['attachmentPath']) ?>" class="btn btn-sm btn-outline-primary" download>Download File</a></p>
    <?php endif; ?>
    <?php if ($fromModal): ?>
      <a class="btn btn-secondary" href="projects_student.php?action=show&projectId=<?= urlencode($projectId) ?>">← Back to Project</a>
    <?php else: ?>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">← Back to Tasks</a>
    <?php endif; ?>
  </div>
</body>
</html>

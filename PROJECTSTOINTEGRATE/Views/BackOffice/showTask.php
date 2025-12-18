<?php
// BackOffice showTask
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
// Get project details to show project name and due date
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
  <title>View Task - BackOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3><?= htmlspecialchars($task['taskName']) ?></h3>
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
    <p><strong>Status:</strong> <span class="badge rounded-pill bg-<?= $badgeClass ?>"><?= $badgeLabel ?></span></p>
    <p><?= htmlspecialchars($task['description'] ?? '') ?></p>
    <?php if ($project): ?>
      <p><strong>Project:</strong> <?= htmlspecialchars($project['projectName']) ?></p>
      <?php if (!empty($project['dueDate'])): ?>
        <p><strong>Project Due Date:</strong> <?= htmlspecialchars(date('M j, Y', strtotime($project['dueDate']))) ?></p>
      <?php endif; ?>
    <?php else: ?>
      <p><strong>Project:</strong> N/A</p>
    <?php endif; ?>
    <?php if (!empty($task['dueDate'])): ?>
      <p><strong>Task Due Date:</strong> <?= htmlspecialchars(date('M j, Y', strtotime($task['dueDate']))) ?></p>
    <?php endif; ?>
    <?php if (!empty($task['attachmentPath'])): ?>
      <p><strong>Attachment:</strong> <a href="../../<?= htmlspecialchars($task['attachmentPath']) ?>" class="btn btn-sm btn-outline-primary" download>Download File</a></p>
    <?php endif; ?>
    <?php if ($fromModal): ?>
      <?php
      $role = $_SESSION['user']['role'] ?? 'student';
      $backPage = ($role === 'teacher') ? 'projects_teacher.php' : (($role === 'admin') ? 'projects_admin.php' : 'projectList.php');
      ?>
      <a class="btn btn-secondary" href="<?= $backPage ?>?action=show&projectId=<?= urlencode($projectId) ?>">← Back to Project</a>
    <?php else: ?>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">← Back to Tasks</a>
    <?php endif; ?>
  </div>
</body>
</html>

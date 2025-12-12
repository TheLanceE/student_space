<?php
// Show a single project (FrontOffice)
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();
$projectId = $_GET['projectId'] ?? null;
if (!$projectId) {
  header('Location: projectList.php');
  exit;
}
$show = $pctrl->showProject($projectId);
$proj = $show['project'];
$tasks = $show['tasks'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Project</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3><?= htmlspecialchars($proj['projectName']) ?></h3>
    <p><?= htmlspecialchars($proj['description'] ?? '') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($proj['status'] ?? 'N/A') ?></p>
    <p><strong>Progress:</strong> <?= htmlspecialchars($proj['completionPercentage'] ?? 0) ?>%</p>
    <a class="btn btn-secondary" href="projectList.php">Back</a>
  </div>
</body>
</html>

<?php
// Show project details (BackOffice)
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();
$projectId = $_GET['projectId'] ?? null;
if (!$projectId) { header('Location: projectList.php'); exit; }
$show = $pctrl->showProject($projectId);
$proj = $show['project'];
$tasks = $show['tasks'];
// load project members for BackOffice
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$members = [];
try{ $members = (new ProjectController())->listProjectMembers($projectId); }catch(Exception $e){ $members = []; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Project - BackOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h3><?= htmlspecialchars($proj['projectName']) ?></h3>
    <p><?= htmlspecialchars($proj['description'] ?? '') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($proj['status'] ?? 'N/A') ?></p>
    <p><strong>Progress:</strong> <?= htmlspecialchars($proj['completionPercentage'] ?? 0) ?>%</p>
    <h5 class="mt-4">Project Members</h5>
    <?php if (empty($members)): ?>
      <p class="text-muted">No members assigned to this project.</p>
    <?php else: ?>
      <div class="list-group mb-3">
        <?php foreach ($members as $m): ?>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong><?= htmlspecialchars($m['userId']) ?></strong>
              <div class="small text-muted">Role: <?= htmlspecialchars($m['role'] ?? 'student') ?> • Added: <?= htmlspecialchars($m['addedAt'] ?? '') ?></div>
            </div>
            <div class="text-end">
              <?php if ($m['submitted']): ?>
                <span class="badge bg-success">Submitted</span>
              <?php else: ?>
                <form method="POST" action="addTask.php" class="d-inline me-2">
                  <input type="hidden" name="data[projectId]" value="<?= htmlspecialchars($projectId) ?>">
                  <input type="hidden" name="data[assignedTo]" value="<?= htmlspecialchars($m['userId']) ?>">
                  <input type="hidden" name="data[taskName]" value="Task for <?= htmlspecialchars($m['userId']) ?>">
                  <input type="hidden" name="data[description]" value="Assigned by teacher/admin">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Add Task</button>
                </form>
                <form method="POST" action="markSubmission.php" class="d-inline">
                  <input type="hidden" name="projectId" value="<?= htmlspecialchars($projectId) ?>">
                  <input type="hidden" name="userId" value="<?= htmlspecialchars($m['userId']) ?>">
                  <input type="hidden" name="redirect" value="showProject.php?projectId=<?= urlencode($projectId) ?>">
                  <button class="btn btn-sm btn-primary" type="submit">Mark Submitted</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <a class="btn btn-secondary" href="projectList.php">Back</a>
  </div>
</body>
</html>

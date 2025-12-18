<?php
// FrontOffice project list page (single responsibility)
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();
$projects = $pctrl->listProjects();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projects - FrontOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
      <h3>My Projects (Front)</h3>
      <a class="btn btn-primary" href="addProject.php">New Project</a>
    </div>

    <?php if (empty($projects)): ?>
      <p>No projects yet.</p>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($projects as $proj): ?>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="card-title mb-0"><?= htmlspecialchars($proj['projectName']) ?></h5>
                  <a class="btn btn-sm btn-outline-secondary" href="taskList.php?projectId=<?= urlencode($proj['id']) ?>"><i class="bi bi-list-task"></i> Tasks</a>
                </div>
                <p class="card-text"><?= htmlspecialchars($proj['description'] ?? '') ?></p>
                <a class="btn btn-sm btn-outline-primary" href="showProject.php?projectId=<?= urlencode($proj['id']) ?>">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="updateProject.php?projectId=<?= urlencode($proj['id']) ?>">Edit</a>
                <form method="POST" action="../../Controllers/ProjectController.php" class="d-inline" onsubmit="return confirm('Delete project?');">
                  <input type="hidden" name="action" value="delete_project">
                  <input type="hidden" name="projectId" value="<?= htmlspecialchars($proj['id']) ?>">
                  <input type="hidden" name="redirect" value="projectList.php">
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

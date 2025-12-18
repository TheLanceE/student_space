<?php
// BackOffice Task list
session_start();
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$tctrl = new TaskController();
$pctrl = new ProjectController();
$projectId = $_GET['projectId'] ?? null;
$tasks = $tctrl->listTasks($projectId);
$projectName = null;
if ($projectId) {
    try {
        $projectData = $pctrl->showProject($projectId);
        $projectName = $projectData['project']['projectName'] ?? null;
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
  <title>Tasks - BackOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3>All Tasks<?= $projectName ? ' - ' . htmlspecialchars($projectName) : '' ?></h3>
      </div>
      <div>
        <?php
        // Determine which projects page to return to based on role or default to projectList
        $role = $_SESSION['user']['role'] ?? null;
        if ($role === 'teacher') {
            $backUrl = 'projects_teacher.php';
        } elseif ($role === 'admin') {
            $backUrl = 'projects_admin.php';
        } else {
            $backUrl = 'projectList.php';
        }
        ?>
        <a class="btn btn-outline-secondary" href="<?= $backUrl ?>">← Back to Projects</a>
      </div>
    </div>

    <!-- Search Control -->
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" id="searchInput" class="form-control" placeholder="Search tasks by name or description...">
        </div>
      </div>
      <div class="col-md-3">
        <select id="statusFilter" class="form-select">
          <option value="">All Statuses</option>
          <option value="not_started">Not Started</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="on_hold">On Hold</option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100" id="clearFilters"><i class="bi bi-x-circle"></i> Clear Filters</button>
      </div>
    </div>

    <?php if (empty($tasks)): ?>
      <p>No tasks yet.</p>
    <?php else: ?>
      <div class="list-group" id="tasksList">
        <?php foreach ($tasks as $task): ?>
          <?php $status = $task['status'] ?? 'not_started'; ?>
          <div class="list-group-item" data-status="<?= htmlspecialchars($status) ?>">
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <script>
    // Client-side task search and filter
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const statusFilter = document.getElementById('statusFilter');
      const clearBtn = document.getElementById('clearFilters');
      const taskItems = document.querySelectorAll('#tasksList .list-group-item');

      function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;

        taskItems.forEach(item => {
          const name = item.querySelector('h6').textContent.toLowerCase();
          const desc = item.querySelector('p').textContent.toLowerCase();
          const status = item.getAttribute('data-status');

          const matchesSearch = name.includes(searchTerm) || desc.includes(searchTerm);
          const matchesStatus = !selectedStatus || status === selectedStatus;

          if (matchesSearch && matchesStatus) {
            item.style.display = '';
            visibleCount++;
          } else {
            item.style.display = 'none';
          }
        });

        // Show/hide no-results message
        const listGroup = document.querySelector('#tasksList');
        let noResultsMsg = document.getElementById('noResultsMsg');
        if (!noResultsMsg && listGroup) {
          noResultsMsg = document.createElement('div');
          noResultsMsg.id = 'noResultsMsg';
          noResultsMsg.className = 'text-center py-5 text-muted';
          noResultsMsg.innerHTML = '<i class="bi bi-search" style="font-size: 3rem;"></i><h5 class="mt-3">No tasks found</h5><p>Try adjusting your search or filters</p>';
          listGroup.parentNode.insertBefore(noResultsMsg, listGroup.nextSibling);
        }
        if (noResultsMsg) {
          noResultsMsg.style.display = visibleCount === 0 && taskItems.length > 0 ? 'block' : 'none';
        }
      }

      if (searchInput && statusFilter && clearBtn) {
        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        clearBtn.addEventListener('click', function() {
          searchInput.value = '';
          statusFilter.value = '';
          applyFilters();
        });
      }
    });
  </script>
</body>
</html>

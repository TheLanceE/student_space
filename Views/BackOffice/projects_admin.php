<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>All Projects | EduMind+</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../assets/css/debug.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    /* Hide edit button for admins, keep delete */
    .btn-edit { display: none !important; }
  </style>
</head>
<body data-page="admin-projects">
<?php
session_start();
// Force debug admin identity for this page
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
  $_SESSION['user'] = ['id' => 'admin_debug', 'username' => 'debug_admin', 'role' => 'admin'];
} else {
  $_SESSION['user']['id'] = 'admin_debug';
  $_SESSION['user']['username'] = 'debug_admin';
  $_SESSION['user']['role'] = 'admin';
}
if (!empty($_SESSION['flash_success']) || !empty($_SESSION['flash_error'])) {
  echo '<div class="container mt-3" id="flashContainer">';
  if (!empty($_SESSION['flash_success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show flash-alert" role="alert">' . htmlspecialchars($_SESSION['flash_success']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['flash_success']);
  }
  if (!empty($_SESSION['flash_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show flash-alert" role="alert">' . htmlspecialchars($_SESSION['flash_error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['flash_error']);
  }
  echo '</div>';
  echo '<script>document.addEventListener("DOMContentLoaded",function(){const alerts=document.querySelectorAll(".flash-alert");alerts.forEach(a=>setTimeout(()=>{try{bootstrap.Alert.getOrCreateInstance(a).close()}catch(e){a.remove()}},3000));});</script>';
}
?>
  <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ [ADMIN]</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="projects_admin.php">Projects</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">All Projects</h1>
    </div>

    <!-- Search and Filter Controls -->
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" id="searchInput" class="form-control" placeholder="Search projects by name or description...">
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

    <?php
    include_once(__DIR__ . '/../../Controllers/ProjectController.php');
    $pctrl = new ProjectController();
    $projects = $pctrl->listProjects();
    ?>
    <div id="projectsList" class="row g-4">
      <?php if (empty($projects)) : ?>
        <div class="col-12 empty-state">
          <div class="mb-3">📋</div>
          <h4>No projects yet</h4>
          <p class="text-muted">There are no projects available.</p>
        </div>
      <?php else: ?>
        <?php foreach ($projects as $proj): ?>
          <div class="col-md-6 col-lg-4" data-project-id="<?= htmlspecialchars($proj['id']) ?>">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0"><?= htmlspecialchars($proj['projectName']) ?></h5>
                  </div>
                  <div>
                    <a class="btn btn-sm btn-outline-secondary me-1" href="taskList.php?projectId=<?= urlencode($proj['id']) ?>"><i class="bi bi-list-task"></i> Tasks</a>
                    <span class="badge bg-<?= ($proj['status'] === 'completed' ? 'success' : ($proj['status'] === 'in_progress' ? 'primary' : ($proj['status'] === 'on_hold' ? 'warning' : 'secondary'))) ?>"><?= htmlspecialchars(str_replace('_', ' ', $proj['status'])) ?></span>
                  </div>
                </div>
                <p class="card-text text-muted small mt-2"><?= htmlspecialchars($proj['description'] ?? 'No description') ?></p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <small class="text-muted">
                    <i class="bi bi-check2-square"></i> <?= htmlspecialchars($proj['completedTasks'] ?? 0) ?>/<?= htmlspecialchars($proj['expectedTaskCount'] ?? 0) ?> tasks
                  </small>
                  <?= $proj['dueDate'] ? '<small class="text-muted"><i class="bi bi-calendar"></i> ' . htmlspecialchars(date('M j, Y', strtotime($proj['dueDate']))) . '</small>' : '' ?>
                </div>
              </div>
              <div class="card-footer bg-transparent">
                <a class="btn btn-sm btn-outline-primary" href="?action=show&projectId=<?= urlencode($proj['id']) ?>"> <i class="bi bi-eye"></i> View</a>
                <form method="POST" action="../../Controllers/ProjectController.php" class="d-inline" onsubmit="return confirm('Delete this project and all its tasks?');">
                  <input type="hidden" name="action" value="delete_project">
                  <input type="hidden" name="projectId" value="<?= htmlspecialchars($proj['id']) ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <!-- Project Detail Modal -->
  <div class="modal fade" id="projectDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="projectDetailTitle">Project Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="projectDetailBody">
                <!-- Project details will be loaded here -->
                <?php
                if (!empty($_GET['action']) && $_GET['action'] === 'show' && !empty($_GET['projectId'])) {
                    $show = $pctrl->showProject($_GET['projectId']);
                    $proj = $show['project'];
                    $tasks = $show['tasks'];
                    ob_start();
                ?>
                <div class="row">
                  <div class="col-md-8">
                    <h6 class="text-muted">Description</h6>
                    <p><?= htmlspecialchars($proj['description'] ?? 'No description') ?></p>
                    <h6 class="text-muted mt-4">Tasks (<?= count($tasks) ?>)</h6>
                    <div class="list-group">
                      <?php if (count($tasks) === 0): ?>
                        <p class="text-muted">No tasks yet</p>
                      <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                          <div class="list-group-item task-item <?= !empty($task['isComplete']) ? 'task-completed' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start">
                              <div class="flex-grow-1">
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
                                <h6 class="mb-1">
                                  <?= htmlspecialchars($task['taskName']) ?>
                                  <span class="badge rounded-pill bg-<?= $badgeClass ?> align-middle" style="vertical-align: middle;">
                                    <?= $badgeLabel ?>
                                  </span>
                                </h6>
                                <small class="text-muted"><?= htmlspecialchars($task['description'] ?? 'No description') ?></small>
                              </div>
                              <div class="d-flex gap-2 align-items-start">
                                <a href="showTask.php?taskId=<?= urlencode($task['id']) ?><?= !empty($proj['id']) ? '&projectId=' . urlencode($proj['id']) : '' ?>&fromModal=1" class="btn btn-sm btn-outline-primary">View</a>
                                <form method="POST" action="deleteTask.php" class="d-inline" onsubmit="return confirm('Delete this task?');">
                                  <input type="hidden" name="action" value="delete_task">
                                  <input type="hidden" name="taskId" value="<?= htmlspecialchars($task['id']) ?>">
                                  <input type="hidden" name="redirect" value="projects_admin.php?action=show&projectId=<?= urlencode($proj['id']) ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">Project Info</h6>
                        <?php if (!empty($proj['latestReaction'])): ?>
                          <div class="mb-3">
                            <small class="text-muted d-block"><i class="bi bi-person-check"></i> Teacher's reaction</small>
                            <span style="font-size: 1.25rem;" class="me-2"><?= htmlspecialchars($proj['latestReaction']) ?></span>
                            <?php if (!empty($proj['latestReactionBy'])): ?>
                              <small class="text-muted">by <?= htmlspecialchars($proj['latestReactionBy']) ?></small>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                        <div class="mb-3">
                          <small class="text-muted d-block">Status</small>
                          <?php
                            $st = $proj['status'] ?? '';
                            $stClass = ($st === 'completed' ? 'success' : ($st === 'in_progress' ? 'primary' : ($st === 'on_hold' ? 'warning' : 'secondary')));
                            $stLabel = $st ? str_replace('_',' ', $st) : 'Not set';
                          ?>
                          <span class="badge bg-<?= htmlspecialchars($stClass) ?>"><?= htmlspecialchars($stLabel) ?></span>
                        </div>
                        <?php if (!empty($proj['dueDate'])): ?>
                          <div class="mb-3">
                            <small class="text-muted d-block">Due Date</small>
                            <strong><?= htmlspecialchars(date('M j, Y', strtotime($proj['dueDate']))) ?></strong>
                          </div>
                        <?php endif; ?>
                        <div class="mb-3">
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted d-block">Progress</small>
                            <strong><?= htmlspecialchars($proj['completionPercentage'] ?? 0) ?>%</strong>
                          </div>
                          <div class="progress" style="height: 20px;">
                            <div class="progress-bar" style="width: <?= htmlspecialchars($proj['completionPercentage'] ?? 0) ?>%; min-width: 2rem;">
                              <?= htmlspecialchars($proj['completionPercentage'] ?? 0) ?>%
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php
                    $html = ob_get_clean();
                    echo $html;
                    echo "<script>document.addEventListener('DOMContentLoaded', function(){ new bootstrap.Modal(document.getElementById('projectDetailModal')).show(); try{ history.replaceState(null,'', window.location.pathname); }catch(e){} });</script>";
                }
                ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Debug Badge -->
  <div class="debug-badge" style="background-color: #dc3545;">
    🔒 ADMIN MODE
  </div>

  <script src="../../assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/projects.js?v=20251126"></script>
  <script>
    // Client-side search and filter
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const statusFilter = document.getElementById('statusFilter');
      const clearBtn = document.getElementById('clearFilters');
      const projectCards = document.querySelectorAll('#projectsList > div[data-project-id]');

      function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;

        projectCards.forEach(card => {
          const name = card.querySelector('.card-title').textContent.toLowerCase();
          const desc = card.querySelector('.card-text').textContent.toLowerCase();
          const statusBadge = card.querySelector('.badge');
          const status = statusBadge ? statusBadge.textContent.toLowerCase().replace(/ /g, '_') : '';

          const matchesSearch = name.includes(searchTerm) || desc.includes(searchTerm);
          const matchesStatus = !selectedStatus || status === selectedStatus;

          if (matchesSearch && matchesStatus) {
            card.style.display = '';
            visibleCount++;
          } else {
            card.style.display = 'none';
          }
        });

        // Show/hide no-results message
        let noResultsMsg = document.getElementById('noResultsMsg');
        if (!noResultsMsg) {
          noResultsMsg = document.createElement('div');
          noResultsMsg.id = 'noResultsMsg';
          noResultsMsg.className = 'col-12 text-center py-5';
          noResultsMsg.innerHTML = '<div class="text-muted"><i class="bi bi-search" style="font-size: 3rem;"></i><h5 class="mt-3">No projects found</h5><p>Try adjusting your search or filters</p></div>';
          document.getElementById('projectsList').appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = visibleCount === 0 && projectCards.length > 0 ? '' : 'none';
      }

      searchInput.addEventListener('input', applyFilters);
      statusFilter.addEventListener('change', applyFilters);
      clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        applyFilters();
      });
    });
  </script>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Projects Debug | EduMind+</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../assets/css/debug.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    /* Simple celebratory confetti */
    .confetti-container { position: fixed; inset: 0; pointer-events: none; z-index: 1060; }
    .confetti { position: absolute; width: 10px; height: 10px; border-radius: 50%; opacity: 0.9; animation: fall 2.5s ease-in forwards; }
    @keyframes fall { to { transform: translateY(100vh) rotate(720deg); opacity: 0.8; } }
    .celebrate-banner { position: fixed; top: 20%; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.95); padding: 16px 24px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 1061; }
  </style>
</head>
<body data-page="front-projects">
<?php
session_start();
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
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ [DEBUG]</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="projects_student.php">Projects</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <?php
  include_once(__DIR__ . '/../../Controllers/ProjectController.php');
  $pctrl = new ProjectController();
  $projects = $pctrl->listProjects();
  ?>

  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">My Projects</h1>
      <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
          <i class="bi bi-plus"></i> New Project
        </button>
      </div>
    </div>

    <div id="projectsList" class="row g-4">
      <?php if (empty($projects)) : ?>
        <div class="col-12 empty-state">
          <div class="mb-3">📋</div>
          <h4>No projects yet</h4>
          <p class="text-muted">Create your first project to get started!</p>
        </div>
      <?php else: ?>
        <?php foreach ($projects as $proj): ?>
          <div class="col-md-6 col-lg-4" data-project-id="<?= htmlspecialchars($proj['id']) ?>"
               data-project-name="<?= htmlspecialchars($proj['projectName']) ?>"
               data-project-desc="<?= htmlspecialchars($proj['description'] ?? '') ?>"
               data-project-status="<?= htmlspecialchars($proj['status']) ?>"
               data-project-due="<?= htmlspecialchars($proj['dueDate'] ?? '') ?>"
               >
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
                <button class="btn btn-sm btn-outline-secondary btn-edit" onclick="ProjectDebug.openEditForm('<?= htmlspecialchars($proj['id']) ?>')">
                  <i class="bi bi-pencil"></i> Edit
                </button>

                <form method="POST" action="../../Controllers/ProjectController.php" class="d-inline" onsubmit="return confirm('Delete this project and all its tasks?');">
                  <input type="hidden" name="action" value="delete_project">
                  <input type="hidden" name="projectId" value="<?= htmlspecialchars($proj['id']) ?>">
                  <input type="hidden" name="redirect" value="../Views/FrontOffice/projects_student.php">
                  <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i> Delete</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <!-- Project Modal -->
  <div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="projectModalTitle">New Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="projectForm" method="POST" action="../../Controllers/ProjectController.php">
            <input type="hidden" name="action" id="projectAction" value="create_project">
            <input type="hidden" name="redirect" value="../Views/FrontOffice/projects_student.php">
            <input type="hidden" id="projectId" name="projectId">
            <div class="mb-3">
              <label for="projectName" class="form-label">Project Name</label>
              <input type="text" class="form-control" id="projectName" name="data[projectName]">
            </div>
            <div class="mb-3">
              <label for="projectDesc" class="form-label">Description</label>
              <div class="input-group">
                <textarea class="form-control" id="projectDesc" name="data[description]" rows="3"></textarea>
                <button type="button" class="btn btn-outline-secondary" id="projectDescVoiceBtn" title="Voice input">
                  <i class="bi bi-mic-fill"></i>
                </button>
              </div>
              <div id="projectDescError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
              <small class="text-muted" id="projectDescVoiceStatus" style="display: none;"></small>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="projectStatus" class="form-label">Status</label>
                <select class="form-select" id="projectStatus" name="data[status]">
                  <option value="not_started">Not Started</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="on_hold">On Hold</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="projectDueDate" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="projectDueDate" name="data[dueDate]">
              </div>
            </div>
            <div class="mb-3">
              <label for="expectedTaskCount" class="form-label">Expected Number of Tasks</label>
              <input type="number" class="form-control" id="expectedTaskCount" name="data[expectedTaskCount]" min="1" placeholder="e.g., 6">
              <div id="expectedTaskCountError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
              <small class="form-text text-muted">How many tasks do you plan to complete for this project?</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="projectForm" class="btn btn-primary">Save Project</button>
        </div>
      </div>
    </div>
  </div>

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
                                  <span class="badge rounded-pill bg-<?= $badgeClass ?> align-middle" style="vertical-align: middle;"><?= $badgeLabel ?></span>
                                </h6>
                                <small class="text-muted"><?= htmlspecialchars($task['description'] ?? 'No description') ?></small>
                                <?= !empty($task['dueDate']) ? '<small class="text-muted d-block mt-2"><i class="bi bi-calendar"></i> ' . htmlspecialchars(date('M j, Y', strtotime($task['dueDate']))) . '</small>' : '' ?>
                              </div>
                              <div class="d-flex gap-2 align-items-start">
                                <a href="showTask.php?taskId=<?= urlencode($task['id']) ?><?= !empty($proj['id']) ? '&projectId=' . urlencode($proj['id']) : '' ?>&fromModal=1" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="updateTask.php?taskId=<?= urlencode($task['id']) ?><?= !empty($proj['id']) ? '&projectId=' . urlencode($proj['id']) : '' ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="deleteTask.php" class="d-inline" onsubmit="return confirm('Delete this task?');">
                                  <input type="hidden" name="action" value="delete_task">
                                  <input type="hidden" name="taskId" value="<?= htmlspecialchars($task['id']) ?>">
                                  <input type="hidden" name="redirect" value="projects_student.php?action=show&projectId=<?= urlencode($proj['id']) ?>">
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
                          <?php
                            $progress = (int)($proj['completionPercentage'] ?? 0);
                            $encouragement = '';
                            if ($progress === 0) {
                              $encouragement = "🚀 Ready to start? Your journey begins with the first step!";
                            } elseif ($progress > 0 && $progress < 25) {
                              $encouragement = "💪 Great start! Keep the momentum going!";
                            } elseif ($progress >= 25 && $progress < 50) {
                              $encouragement = "🌟 You're making progress! Quarter way there!";
                            } elseif ($progress >= 50 && $progress < 75) {
                              $encouragement = "🔥 Halfway done! You're doing amazing!";
                            } elseif ($progress >= 75 && $progress < 100) {
                              $encouragement = "⚡ Almost there! The finish line is in sight!";
                            } elseif ($progress === 100) {
                              $encouragement = "🎉 Perfect! You've completed everything!";
                            }
                          ?>
                          <small class="text-muted d-block mt-2 fst-italic"><?= htmlspecialchars($encouragement) ?></small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php
                    $html = ob_get_clean();
                    echo $html;
                    $percent = (int)($proj['completionPercentage'] ?? 0);
                    $celebrateJs = "<script>document.addEventListener('DOMContentLoaded', function(){ new bootstrap.Modal(document.getElementById('projectDetailModal')).show(); try{ history.replaceState(null,'', window.location.pathname); }catch(e){} if($percent===100){ const cont=document.createElement('div'); cont.className='confetti-container'; document.body.appendChild(cont); const colors=['#ff6b6b','#ffd93d','#6bcB77','#4d96ff','#f06595']; for(let i=0;i<120;i++){ const s=document.createElement('div'); s.className='confetti'; s.style.left=Math.random()*100+'vw'; s.style.top='-20px'; s.style.background=colors[Math.floor(Math.random()*colors.length)]; s.style.animationDelay=(Math.random()*0.8)+'s'; s.style.transform='translateY(-20px)'; cont.appendChild(s);} const banner=document.createElement('div'); banner.className='celebrate-banner'; banner.innerHTML='🎉 Amazing work! You hit 100% — keep shining!'; document.body.appendChild(banner); setTimeout(()=>{ banner.remove(); cont.remove(); }, 3500); } });</script>";
                    echo $celebrateJs;
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
  <div class="debug-badge">
    🐛 DEBUG MODE
  </div>

  <script src="../../assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/projectNameValidator.js"></script>
  <script src="../../assets/js/dueDateValidator.js"></script>
  <script src="../../assets/js/descriptionValidator.js"></script>
  <script src="../../assets/js/projects.js?v=20251126"></script>
  <script>
    // Validate expected task count
    document.addEventListener('DOMContentLoaded', function() {
      const projectForm = document.getElementById('projectForm');
      const expectedTaskCount = document.getElementById('expectedTaskCount');
      const expectedTaskCountError = document.getElementById('expectedTaskCountError');
      
      if (projectForm && expectedTaskCount && expectedTaskCountError) {
        projectForm.addEventListener('submit', function(e) {
          const value = parseInt(expectedTaskCount.value);
          if (!value || value < 1) {
            e.preventDefault();
            expectedTaskCountError.textContent = 'Expected task count must be at least 1';
            expectedTaskCountError.style.display = 'block';
            expectedTaskCount.classList.add('is-invalid');
            expectedTaskCount.focus();
            return false;
          } else {
            expectedTaskCountError.style.display = 'none';
            expectedTaskCount.classList.remove('is-invalid');
          }
        });
        
        expectedTaskCount.addEventListener('input', function() {
          const value = parseInt(expectedTaskCount.value);
          if (value && value >= 1) {
            expectedTaskCountError.style.display = 'none';
            expectedTaskCount.classList.remove('is-invalid');
          }
        });
      }

      // Speech-to-text for project description
      const projectDescVoiceBtn = document.getElementById('projectDescVoiceBtn');
      const projectDescTextarea = document.getElementById('projectDesc');
      const projectDescVoiceStatus = document.getElementById('projectDescVoiceStatus');

      if (projectDescVoiceBtn && projectDescTextarea && 'webkitSpeechRecognition' in window) {
        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        let isListening = false;

        projectDescVoiceBtn.addEventListener('click', function() {
          if (isListening) {
            recognition.stop();
            return;
          }

          recognition.start();
          isListening = true;
          projectDescVoiceBtn.classList.add('btn-danger');
          projectDescVoiceBtn.classList.remove('btn-outline-secondary');
          projectDescVoiceBtn.innerHTML = '<i class="bi bi-mic-fill"></i> Listening...';
          projectDescVoiceStatus.textContent = '🎤 Listening... Speak now';
          projectDescVoiceStatus.style.display = 'block';
        });

        recognition.onresult = function(event) {
          const transcript = event.results[0][0].transcript;
          const currentText = projectDescTextarea.value;
          projectDescTextarea.value = currentText ? currentText + ' ' + transcript : transcript;
          projectDescVoiceStatus.textContent = '✓ Voice input captured';
          setTimeout(() => projectDescVoiceStatus.style.display = 'none', 2000);
        };

        recognition.onend = function() {
          isListening = false;
          projectDescVoiceBtn.classList.remove('btn-danger');
          projectDescVoiceBtn.classList.add('btn-outline-secondary');
          projectDescVoiceBtn.innerHTML = '<i class="bi bi-mic-fill"></i>';
        };

        recognition.onerror = function(event) {
          isListening = false;
          projectDescVoiceBtn.classList.remove('btn-danger');
          projectDescVoiceBtn.classList.add('btn-outline-secondary');
          projectDescVoiceBtn.innerHTML = '<i class="bi bi-mic-fill"></i>';
          projectDescVoiceStatus.textContent = '⚠ Voice input error: ' + event.error;
          projectDescVoiceStatus.style.display = 'block';
          setTimeout(() => projectDescVoiceStatus.style.display = 'none', 3000);
        };
      } else if (projectDescVoiceBtn && !('webkitSpeechRecognition' in window)) {
        projectDescVoiceBtn.disabled = true;
        projectDescVoiceBtn.title = 'Voice input not supported in this browser';
      }

      // Clean up modal backdrop and reset form on close
      const projectModal = document.getElementById('projectModal');
      if (projectModal) {
        projectModal.addEventListener('hidden.bs.modal', function() {
          // Remove any lingering backdrops
          const backdrops = document.querySelectorAll('.modal-backdrop');
          backdrops.forEach(backdrop => backdrop.remove());
          
          // Remove modal-open class from body
          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
          document.body.style.paddingRight = '';
          
          // Reset form
          if (projectForm) {
            projectForm.reset();
          }
        });
      }
    });
  </script>
</body>
</html>

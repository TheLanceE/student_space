<?php
// FrontOffice addTask (posts to TaskController)
// Temporarily enable error display for debugging this view
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sessionStarted = session_status() === PHP_SESSION_ACTIVE ? true : session_start();
// Set default student session if not already set
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['id' => 'stu_debug', 'username' => 'debug_student', 'role' => 'student'];
}
include_once(__DIR__ . '/../../Controllers/TaskController.php');
include_once(__DIR__ . '/../../Controllers/ProjectController.php');

// Capture the projectId from URL parameter
$projectId = $_GET['projectId'] ?? null;

// instantiate controllers with graceful error reporting so we don't render a blank page
try {
  $tctrl = new TaskController();
} catch (Throwable $e) {
  echo '<div class="container py-4"><div class="alert alert-danger">TaskController init error: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}
try {
  $pctrl = new ProjectController();
} catch (Throwable $e) {
  echo '<div class="container py-4"><div class="alert alert-danger">ProjectController init error: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}
try {
  $projects = $pctrl->listProjects();
} catch (Exception $e) {
  // show the exception message so the dev can see why the page 500s
  echo '<div class="container py-4"><div class="alert alert-danger">Error loading projects: '.htmlspecialchars($e->getMessage()).'</div></div>';
  exit;
}
$firstProjectId = $projects[0]['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  // Use controller handler via POST endpoint to keep behavior consistent
  // But we can also call addTask directly; here we'll forward to controller by calling addTask()
  try{ 
    error_log("DEBUG addTask: POST data = " . print_r($_POST, true));
    error_log("DEBUG addTask: FILES data = " . print_r($_FILES, true));
    $tctrl->addTask($_POST['data'] ?? null); 
    $_SESSION['flash_success']='Task created'; 
    error_log("DEBUG addTask: Task created successfully");
  }catch(Exception $e){ 
    $_SESSION['flash_error']='Create failed: '.$e->getMessage(); 
    error_log("DEBUG addTask ERROR: " . $e->getMessage());
  }
  $redirect = $projectId ? 'taskList.php?projectId=' . urlencode($projectId) : 'taskList.php';
  error_log("DEBUG addTask: Redirecting to " . $redirect);
  header('Location: ' . $redirect); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Task</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
  <div class="container py-4">
    <h3>New Task</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Task Name</label>
        <input class="form-control" id="taskName" name="data[taskName]">
        <div id="taskNameError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Project</label>
        <select class="form-select" id="taskProject" name="data[projectId]">
          <?php if (empty($projects)): ?>
            <option value="">(no projects available)</option>
          <?php else: ?>
            <?php foreach ($projects as $i => $proj): ?>
              <?php $isSelected = $projectId ? ($proj['id'] === $projectId) : ($i === 0); ?>
              <option value="<?= htmlspecialchars($proj['id']) ?>" <?= ($isSelected ? 'selected' : '') ?>><?= htmlspecialchars($proj['projectName']) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <div class="input-group">
          <textarea class="form-control" id="taskDescription" name="data[description]"></textarea>
          <button class="btn btn-outline-primary" type="button" id="taskDescVoiceBtn" title="Voice input">
            <i class="bi bi-mic-fill"></i>
          </button>
        </div>
        <div id="taskDescVoiceStatus" class="form-text" style="display: none;"></div>
        <div id="taskDescError" style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="data[status]">
          <option value="not_started" selected>Not Started</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="on_hold">On Hold</option>
        </select>
      </div>
      <!-- Priority and completed flag removed for student task creation -->
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Upload Attachment (Optional)</label>
        <input type="file" class="form-control" name="attachment" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
        <small class="form-text text-muted">Allowed: images, PDF, Office docs, ZIP (max 10MB)</small>
      </div>
      <button id="submitBtn" class="btn btn-primary" type="submit">Create</button>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">Cancel</a>
    </form>
  </div>
  <script src="../../assets/js/taskNameValidator.js"></script>
  <script src="../../assets/js/descriptionValidator.js"></script>
  <script>
    // Speech-to-text for task description
    (function() {
      const voiceBtn = document.getElementById('taskDescVoiceBtn');
      const textarea = document.getElementById('taskDescription');
      const statusDiv = document.getElementById('taskDescVoiceStatus');
      
      if (!('webkitSpeechRecognition' in window)) {
        voiceBtn.disabled = true;
        voiceBtn.title = 'Voice input not supported in this browser';
        return;
      }
      
      const recognition = new webkitSpeechRecognition();
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.lang = 'en-US';
      
      let isListening = false;
      
      voiceBtn.addEventListener('click', function() {
        if (isListening) {
          recognition.stop();
          return;
        }
        
        try {
          recognition.start();
          isListening = true;
          voiceBtn.classList.remove('btn-outline-primary');
          voiceBtn.classList.add('btn-danger');
          statusDiv.textContent = '🎤 Listening...';
          statusDiv.style.display = 'block';
        } catch (e) {
          console.error('Speech recognition error:', e);
        }
      });
      
      recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        const currentText = textarea.value;
        textarea.value = currentText ? currentText + ' ' + transcript : transcript;
        
        statusDiv.textContent = '✓ Voice input captured';
        setTimeout(() => {
          statusDiv.style.display = 'none';
        }, 2000);
      };
      
      recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
        statusDiv.textContent = '⚠ Voice input error: ' + event.error;
        setTimeout(() => {
          statusDiv.style.display = 'none';
        }, 3000);
      };
      
      recognition.onend = function() {
        isListening = false;
        voiceBtn.classList.remove('btn-danger');
        voiceBtn.classList.add('btn-outline-primary');
      };
    })();
  </script>
</body>
</html>

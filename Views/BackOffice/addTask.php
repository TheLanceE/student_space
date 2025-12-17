<?php
// BackOffice addTask (posts to TaskController)
session_start();
include(__DIR__ . '/../../Controllers/TaskController.php');
include(__DIR__ . '/../../Controllers/ProjectController.php');

// Capture the projectId from URL parameter
$projectId = $_GET['projectId'] ?? null;

$tctrl = new TaskController();
$pctrl = new ProjectController();
$projects = $pctrl->listProjects();

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{ $tctrl->addTask($_POST['data'] ?? null); $_SESSION['flash_success']='Task created'; }catch(Exception $e){ $_SESSION['flash_error']='Create failed: '.$e->getMessage(); }
  $redirect = $projectId ? 'taskList.php?projectId=' . urlencode($projectId) : 'taskList.php';
  header('Location: ' . $redirect); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Task - BackOffice</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
  <div class="container py-4">
    <h3>New Task (BackOffice)</h3>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Task Name</label>
        <input class="form-control" name="data[taskName]" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Project</label>
        <select class="form-select" name="data[projectId]" required>
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
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]">
      </div>
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn btn-secondary" href="taskList.php<?= $projectId ? '?projectId=' . urlencode($projectId) : '' ?>">Cancel</a>
    </form>
  </div>
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

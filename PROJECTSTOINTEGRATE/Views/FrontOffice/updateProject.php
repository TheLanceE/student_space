<?php
// Update form for FrontOffice
session_start();
include_once(__DIR__ . '/../../Controllers/ProjectController.php');
$pctrl = new ProjectController();
$projectId = $_GET['projectId'] ?? null;
if (!$projectId) { header('Location: projectList.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    $pctrl->updateExistingProject($_POST['projectId'] ?? null, $_POST['data'] ?? null);
    $_SESSION['flash_success'] = 'Project updated';
  }catch(Exception $e){ $_SESSION['flash_error'] = 'Update failed: '.$e->getMessage(); }
  header('Location: projectList.php'); exit;
}

$show = $pctrl->showProject($projectId);
$proj = $show['project'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Project</title>
  <link href="../../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
  <div class="container py-4">
    <h3>Edit Project</h3>
    <form method="POST">
      <input type="hidden" name="projectId" value="<?= htmlspecialchars($proj['id']) ?>">
      <div class="mb-3">
        <label class="form-label">Project Name</label>
        <input class="form-control" name="data[projectName]" value="<?= htmlspecialchars($proj['projectName']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <div class="input-group">
          <textarea class="form-control" id="projectDescription" name="data[description]"><?= htmlspecialchars($proj['description'] ?? '') ?></textarea>
          <button class="btn btn-outline-primary" type="button" id="projectDescVoiceBtn" title="Voice input"><i class="bi bi-mic-fill"></i></button>
        </div>
        <div id="projectDescVoiceStatus" class="form-text" style="display:none;"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Expected Task Count</label>
        <input type="number" min="1" class="form-control" name="data[expectedTaskCount]" value="<?= htmlspecialchars($proj['expectedTaskCount'] ?? 1) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="data[status]">
          <option value="not_started" <?= ($proj['status'] === 'not_started' ? 'selected' : '') ?>>Not Started</option>
          <option value="in_progress" <?= ($proj['status'] === 'in_progress' ? 'selected' : '') ?>>In Progress</option>
          <option value="completed" <?= ($proj['status'] === 'completed' ? 'selected' : '') ?>>Completed</option>
          <option value="on_hold" <?= ($proj['status'] === 'on_hold' ? 'selected' : '') ?>>On Hold</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="date" class="form-control" name="data[dueDate]" value="<?= htmlspecialchars($proj['dueDate'] ?? '') ?>">
      </div>
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn btn-secondary" href="projectList.php">Cancel</a>
    </form>
  </div>
  <script src="../../assets/vendor/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      const voiceBtn = document.getElementById('projectDescVoiceBtn');
      const textarea = document.getElementById('projectDescription');
      const statusDiv = document.getElementById('projectDescVoiceStatus');
      if (!voiceBtn || !('webkitSpeechRecognition' in window)) { if (voiceBtn) { voiceBtn.disabled = true; voiceBtn.title = 'Voice input not supported in this browser'; } return; }
      const recognition = new webkitSpeechRecognition(); recognition.continuous=false; recognition.interimResults=false; recognition.lang='en-US'; let isListening=false;
      voiceBtn.addEventListener('click', function(){ if (isListening){ recognition.stop(); return;} try{ recognition.start(); isListening=true; voiceBtn.classList.remove('btn-outline-primary'); voiceBtn.classList.add('btn-danger'); statusDiv.textContent='🎤 Listening...'; statusDiv.style.display='block'; }catch(e){ console.error(e); } });
      recognition.onresult=function(event){ const transcript = event.results[0][0].transcript; const cur = textarea.value; textarea.value = cur ? cur + ' ' + transcript : transcript; statusDiv.textContent='✓ Voice input captured'; setTimeout(()=>{ statusDiv.style.display='none'; },2000); };
      recognition.onerror=function(ev){ statusDiv.textContent='⚠ Voice input error: ' + ev.error; setTimeout(()=>{ statusDiv.style.display='none'; },3000); };
      recognition.onend=function(){ isListening=false; voiceBtn.classList.remove('btn-danger'); voiceBtn.classList.add('btn-outline-primary'); };
    })();
  </script>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Challenges.php';
$teacherID = $_SESSION['userID'] ?? 1;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Challenges | Teacher</title>
  <link rel="stylesheet" href="../shared-assets/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="../shared-assets/css/global.css">
  <link rel="stylesheet" href="../shared-assets/css/styles.css">

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <style>
    #toast-container { position: fixed; top: 10px; right: 10px; z-index: 9999; }
    .toast { background: #4caf50; color: white; padding: 10px 15px; margin-bottom: 5px; border-radius: 5px; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
    @keyframes fadein { from {opacity:0;} to {opacity:1;} }
    @keyframes fadeout { from {opacity:1;} to {opacity:0;} }
  </style>
</head>
<body data-page="teacher-challenges">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Teacher</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="challenges.php">📝 Challenges</a></li>
        </ul>
        <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    <h1>📚 My Challenges</h1>
    <button class="btn btn-primary mb-3" id="createChallengeBtn">➕ Create Challenge</button>
    <div id="challengeTable">
      <?php
      $challenges = Challenges::getByCreator($pdo, $teacherID);
      // Enhancement: Add sample challenges if DB is empty (for demo, non-destructive)
      if (!$challenges) {
          $challenges = [
              ['id' => 1, 'title' => 'Quiz Mastery', 'type' => 'Quiz', 'points' => 50, 'status' => 'Active'],
              ['id' => 2, 'title' => 'Course Completion', 'type' => 'Course', 'points' => 75, 'status' => 'Active'],
              ['id' => 3, 'title' => 'Time Study', 'type' => 'Time', 'points' => 30, 'status' => 'Active'],
              ['id' => 4, 'title' => 'Social Share', 'type' => 'Social', 'points' => 20, 'status' => 'Active'],
              ['id' => 5, 'title' => 'Group Project', 'type' => 'Social', 'points' => 40, 'status' => 'Active'],
              ['id' => 6, 'title' => 'Reflection Journal', 'type' => 'Reading', 'points' => 25, 'status' => 'Active'],
          ];
      }
      if ($challenges) {
        echo '<table class="table"><thead><tr><th>Title</th><th>Type</th><th>Points</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        foreach ($challenges as $c) {
          echo "<tr><td>{$c['title']}</td><td>{$c['type']}</td><td>{$c['points']}</td><td>{$c['status']}</td><td><button class='btn btn-sm btn-warning edit-btn' data-id='{$c['id']}'>✏️ Edit</button> <button class='btn btn-sm btn-danger delete-btn' data-id='{$c['id']}'>🗑️ Delete</button></td></tr>";
        }
        echo '</tbody></table>';
      }
      ?>
    </div>
    <div id="challengeForm" style="display:none;">
      <form id="challengeFormData">
        <input type="hidden" id="challengeId">
        <div class="mb-3"><label>Title</label><input type="text" class="form-control" id="title" required></div>
        <div class="mb-3"><label>Description</label><textarea class="form-control" id="description"></textarea></div>
        <div class="mb-3"><label>Type</label><select class="form-select" id="type"><option>Quiz</option><option>Course</option><option>Time</option><option>Social</option></select></div>
        <div class="mb-3"><label>Points Award</label><input type="number" class="form-control" id="pointsAward" required></div>
        <div class="mb-3"><label>Criteria</label><input type="text" class="form-control" id="criteria" placeholder="e.g., Score 80% on quiz"></div>
        <div class="mb-3"><label>Status</label><select class="form-select" id="status"><option>Active</option><option>Inactive</option></select></div>
        <button type="submit" class="btn btn-success">💾 Save</button>
        <button type="button" class="btn btn-secondary" onclick="hideForm()">❌ Cancel</button>
      </form>
    </div>
  </main>
  <div id="toast-container"></div>
  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script>
    function showToast(message) {
      const toast = document.createElement('div');
      toast.classList.add('toast');
      toast.textContent = message;
      document.getElementById('toast-container').appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }

    function hideForm() {
      document.getElementById('challengeForm').style.display = 'none';
    }

    document.getElementById('createChallengeBtn').addEventListener('click', () => {
      document.getElementById('challengeId').value = '';
      document.getElementById('title').value = '';
      document.getElementById('description').value = '';
      document.getElementById('type').value = 'Quiz';
      document.getElementById('pointsAward').value = '';
      document.getElementById('criteria').value = '';
      document.getElementById('status').value = 'Active';
      document.getElementById('challengeForm').style.display = 'block';
    });

    document.getElementById('challengeFormData').addEventListener('submit', function(e) {
      e.preventDefault();
      const id = document.getElementById('challengeId').value;
      const action = id ? 'update' : 'create';
      const formData = new FormData();
      formData.append('title', document.getElementById('title').value);
      formData.append('description', document.getElementById('description').value);
      formData.append('type', document.getElementById('type').value);
      formData.append('points', parseInt(document.getElementById('pointsAward').value));
      formData.append('criteria', document.getElementById('criteria').value);
      formData.append('status', document.getElementById('status').value);
      if (!id) formData.append('createdBy', 1);

      fetch('../../Controllers/ChallengesController.php?action=' + action + (id ? '&id=' + id : ''), {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast(action === 'create' ? '🎉 Challenge created!' : '✨ Challenge updated!');
          location.reload();
        } else {
          showToast('⚠️ Error: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('⚠️ Error saving challenge.');
      });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch('../../Controllers/ChallengesController.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const c = data.challenge;
            document.getElementById('challengeId').value = c.id;
            document.getElementById('title').value = c.title;
            document.getElementById('description').value = c.description;
            document.getElementById('type').value = c.type;
            document.getElementById('pointsAward').value = c.points;
            document.getElementById('criteria').value = c.criteria;
            document.getElementById('status').value = c.status;
            document.getElementById('challengeForm').style.display = 'block';
          } else {
            showToast('⚠️ Error loading challenge.');
          }
        })
        .catch(() => showToast('⚠️ Error fetching challenge.'));
      });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (confirm('Delete this challenge?')) {
          const id = btn.dataset.id;
          fetch('../../Controllers/ChallengesController.php?action=delete&id=' + id)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showToast('🗑️ Challenge deleted!');
              location.reload();
            } else {
              showToast('⚠️ Error deleting challenge');
            }
          })
          .catch(() => showToast('⚠️ Error deleting challenge.'));
        }
      });
    });
  </script>
</body>
</html>

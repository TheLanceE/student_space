<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Challenges.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Challenges | Admin</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet"> <!-- Merged CSS link -->
</head>
<body data-page="admin-challenges">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="challenges.php">📝 Challenges</a></li>
          <li class="nav-item"><a class="nav-link" href="rewards.php">🎁 Rewards</a></li>
        </ul>
        <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h5">📚 Challenge Management</h1>
        <button class="btn btn-primary mb-3" id="createChallengeBtn">➕ Create Challenge</button>
        <div id="challengeTable">
          <?php
          $challenges = Challenges::getAll($pdo);
          // Enhancement: Add sample challenges if DB is empty (for demo, non-destructive)
          if (!$challenges) {
              $challenges = [
                  ['id' => 1, 'title' => 'Advanced Quiz', 'type' => 'Quiz', 'points' => 100, 'status' => 'Active'],
                  ['id' => 2, 'title' => 'Full Course', 'type' => 'Course', 'points' => 150, 'status' => 'Active'],
                  ['id' => 3, 'title' => 'Extended Study', 'type' => 'Time', 'points' => 60, 'status' => 'Active'],
                  ['id' => 4, 'title' => 'Community Post', 'type' => 'Social', 'points' => 40, 'status' => 'Active'],
              ];
          }
          if ($challenges) {
            echo '<table class="table"><thead><tr><th>Title</th><th>Type</th><th>Points</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            foreach ($challenges as $c) {
              $badgeClass = 'badge-' . strtolower($c['type']);
              echo "<tr><td>{$c['title']}</td><td><span class='badge $badgeClass'>{$c['type']}</span></td><td>{$c['points']}</td><td>{$c['status']}</td><td><button class='btn btn-sm btn-warning edit-btn' data-id='{$c['id']}'>✏️ Edit</button> <button class='btn btn-sm btn-danger delete-btn' data-id='{$c['id']}'>🗑️ Delete</button></td></tr>";
            }
            echo '</tbody></table>';
          } else {
            echo '<p>No challenges found.</p>';
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
      </div>
    </div>
  </main>
  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script>
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

      fetch('../Controllers/ChallengesController.php?action=' + action + (id ? '&id=' + id : ''), {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(action === 'create' ? '🎉 Challenge created!' : '✨ Challenge updated!');
          location.reload();
        } else {
          alert('⚠️ Error: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('⚠️ Error saving challenge.');
      });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch('../Controllers/ChallengesController.php?action=get&id=' + id)
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
            alert('⚠️ Error loading challenge.');
          }
        })
        .catch(() => alert('⚠️ Error fetching challenge.'));
      });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (confirm('Delete this challenge?')) {
          const id = btn.dataset.id;
          fetch('../Controllers/ChallengesController.php?action=delete&id=' + id)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('🗑️ Challenge deleted!');
              location.reload();
            } else {
              alert('⚠️ Error deleting challenge');
            }
          })
          .catch(() => alert('⚠️ Error deleting challenge.'));
        }
      });
    });
  </script>
</body>
</html>
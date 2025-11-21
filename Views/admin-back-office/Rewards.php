<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Rewards.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rewards | Admin</title>
  <link rel="stylesheet" href="../shared-assets/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="../shared-assets/css/global.css">
  <link rel="stylesheet" href="../shared-assets/css/styles.css">

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</head>
<body data-page="admin-rewards">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="challenges.php">📝 Challenges</a></li>
          <li class="nav-item"><a class="nav-link active" href="rewards.php">🎁 Rewards</a></li>
        </ul>
        <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h5">🏅 Reward Management</h1>
        <button class="btn btn-primary mb-3" id="createRewardBtn">➕ Create Reward</button>
        <div id="rewardTable">
          <?php
          $rewards = Rewards::getAll($pdo);
          // Enhancement: Add sample rewards if DB is empty (for demo, non-destructive)
          if (!$rewards) {
              $rewards = [
                  ['id' => 1, 'title' => 'Platinum Badge', 'type' => 'Badge', 'pointsCost' => 200, 'availability' => 10],
                  ['id' => 2, 'title' => 'Diploma Certificate', 'type' => 'Certificate', 'pointsCost' => 300, 'availability' => 5],
                  ['id' => 3, 'title' => '20% Discount Code', 'type' => 'Discount', 'pointsCost' => 250, 'availability' => 20],
                  ['id' => 4, 'title' => 'Bonus Break Time', 'type' => 'Perk', 'pointsCost' => 100, 'availability' => 50],
                  ['id' => 5, 'title' => 'Study Kit', 'type' => 'Perk', 'pointsCost' => 75, 'availability' => 30],
                  ['id' => 6, 'title' => 'Extra Credit', 'type' => 'Perk', 'pointsCost' => 150, 'availability' => 15],
              ];
          }
          if ($rewards) {
            echo '<table class="table"><thead><tr><th>Title</th><th>Type</th><th>Cost</th><th>Availability</th><th>Actions</th></tr></thead><tbody>';
            foreach ($rewards as $r) {
              echo "<tr><td>{$r['title']}</td><td>{$r['type']}</td><td>{$r['pointsCost']}</td><td>{$r['availability']}</td><td><button class='btn btn-sm btn-warning edit-btn' data-id='{$r['id']}'>✏️ Edit</button> <button class='btn btn-sm btn-danger delete-btn' data-id='{$r['id']}'>🗑️ Delete</button></td></tr>";
            }
            echo '</tbody></table>';
          }
          ?>
        </div>
        <div id="rewardForm" style="display:none;">
          <form id="rewardFormData">
            <input type="hidden" id="rewardId">
            <div class="mb-3"><label>Title</label><input type="text" class="form-control" id="title" required></div>
            <div class="mb-3"><label>Description</label><textarea class="form-control" id="description"></textarea></div>
            <div class="mb-3"><label>Type</label><select class="form-select" id="type"><option>Badge</option><option>Certificate</option><option>Discount</option><option>Perk</option></select></div>
            <div class="mb-3"><label>Points Cost</label><input type="number" class="form-control" id="pointsCost" required></div>
            <div class="mb-3"><label>Availability</label><input type="number" class="form-control" id="availability"></div>
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
      document.getElementById('rewardForm').style.display = 'none';
    }

    document.getElementById('createRewardBtn').addEventListener('click', () => {
      document.getElementById('rewardId').value = '';
      document.getElementById('title').value = '';
      document.getElementById('description').value = '';
      document.getElementById('type').value = 'Badge';
      document.getElementById('pointsCost').value = '';
      document.getElementById('availability').value = '';
      document.getElementById('status').value = 'Active';
      document.getElementById('rewardForm').style.display = 'block';
    });

    document.getElementById('rewardFormData').addEventListener('submit', function(e) {
      e.preventDefault();
      const id = document.getElementById('rewardId').value;
      const action = id ? 'update' : 'create';
      const formData = new FormData();
      formData.append('title', document.getElementById('title').value);
      formData.append('description', document.getElementById('description').value);
      formData.append('type', document.getElementById('type').value);
      formData.append('pointsCost', parseInt(document.getElementById('pointsCost').value));
      formData.append('availability', parseInt(document.getElementById('availability').value));
      formData.append('status', document.getElementById('status').value);

      fetch('../Controllers/RewardsController.php?action=' + action + (id ? '&id=' + id : ''), {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(action === 'create' ? '🎉 Reward created!' : '✨ Reward updated!');
          location.reload();
        } else {
          alert('⚠️ Error: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('⚠️ Error saving reward.');
      });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch('../Controllers/RewardsController.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const r = data.reward;
            document.getElementById('rewardId').value = r.id;
            document.getElementById('title').value = r.title;
            document.getElementById('description').value = r.description;
            document.getElementById('type').value = r.type;
            document.getElementById('pointsCost').value = r.pointsCost;
            document.getElementById('availability').value = r.availability;
            document.getElementById('status').value = r.status;
            document.getElementById('rewardForm').style.display = 'block';
          } else {
            alert('⚠️ Error loading reward.');
          }
        })
        .catch(() => alert('⚠️ Error fetching reward.'));
      });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (confirm('Delete this reward?')) {
          const id = btn.dataset.id;
          fetch('../Controllers/RewardsController.php?action=delete&id=' + id)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('🗑️ Reward deleted!');
              location.reload();
            } else {
              alert('⚠️ Error deleting reward');
            }
          })
          .catch(() => alert('⚠️ Error deleting reward.'));
        }
      });
    });
  </script>
</body>
</html>

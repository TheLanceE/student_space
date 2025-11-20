<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Students.php';
require_once __DIR__ . '/../../Models/Challenges.php';
$studentID = $_SESSION['userID'] ?? 1;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Challenges | Student</title>
<link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
<link href="../shared-assets/css/global.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet"> <!-- Merged CSS link -->
<style>
#toast-container { position: fixed; top: 10px; right: 10px; z-index: 9999; }
.toast { background: #4caf50; color: white; padding: 10px 15px; margin-bottom: 5px; border-radius: 5px; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
@keyframes fadein { from {opacity:0;} to {opacity:1;} }
@keyframes fadeout { from {opacity:1;} to {opacity:0;} }
</style>
</head>
<body data-page="student-challenges">
<main class="container py-4">
<h1>📝 Challenges</h1>
<h3>🏆 Leaderboard</h3>
<ol id="leaderboard">
<?php
$students = Students::getAll($pdo);
foreach ($students as $s) {
    echo "<li>{$s['name']} - {$s['points']} pts</li>";
}
?>
</ol>
<div id="challengesList">
<?php
$challenges = Challenges::getAll($pdo);
// Enhancement: Add sample challenges if DB is empty (for demo, non-destructive)
if (!$challenges) {
    $challenges = [
        ['id' => 1, 'title' => 'Complete Quiz on Algebra', 'description' => 'Score 80% or higher!', 'points' => 50],
        ['id' => 2, 'title' => 'Finish Course Module', 'description' => 'Watch all videos and pass the test.', 'points' => 75],
        ['id' => 3, 'title' => 'Study for 2 Hours', 'description' => 'Dedicate time to focused learning.', 'points' => 30],
        ['id' => 4, 'title' => 'Share a Study Tip', 'description' => 'Post something helpful in the forum.', 'points' => 20],
    ];
}
foreach ($challenges as $c) {
    $completed = Challenges::isCompleted($pdo, $studentID, $c['id']) ? 100 : 0;
    echo "<div class='card mb-3 challenge-card'>
            <div class='card-body'>
              <h5>📚 {$c['title']}</h5>
              <p>{$c['description']}</p>
              <div class='progress mb-2'>
                <div class='progress-bar bg-success' style='width: {$completed}%;'></div>
              </div>
              <button class='btn btn-success complete-btn' data-id='{$c['id']}'>✅ Complete</button>
            </div>
          </div>";
}
?>
</div>
</main>
<div id="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>
function showToast(message) {
  const toast = document.createElement('div');
  toast.classList.add('toast');
  toast.textContent = message;
  document.getElementById('toast-container').appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
function showConfetti() {
  confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
}
document.querySelectorAll('.complete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const challengeID = btn.dataset.id;
    fetch('../../Controllers/ChallengesController.php?action=complete&id=' + challengeID)
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          showToast(`🎉 ${data.points} points awarded!`);
          showConfetti();
          btn.closest('.card-body').querySelector('.progress-bar').style.width = '100%';
        } else {
          showToast('⚠️ ' + data.message);
        }
      })
      .catch(() => showToast('⚠️ Error completing challenge.'));
  });
});
</script>
</body>
</html>
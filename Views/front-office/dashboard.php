<?php
require_once '../../Controllers/config.php';
// auth_check already included by config.php

if (empty($_SESSION['user_id'])) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

// Fetch student data
$user_id = $_SESSION['user_id'] ?? null;
$student = null;
$recentScores = [];
$upcomingEvents = [];
$isAccountBirthday = false;
$createdAt = null;

if ($user_id) {
    try {
        // Get student info
        $stmt = $db_connection->prepare("SELECT * FROM students WHERE id = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
        $stmt->execute([$user_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $createdAt = $student['createdAt'] ?? null;
        
        // Get recent scores (last 5)
        $stmt = $db_connection->prepare("
            SELECT s.*, q.title as quiz_title, c.title as course_title, s.createdAt as attempt_date
            FROM scores s
            LEFT JOIN quizzes q ON s.quiz_id = q.id
            LEFT JOIN courses c ON q.courseId = c.id
            WHERE s.student_id = ?
            ORDER BY s.createdAt DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recentScores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get upcoming events (next 3)
        $stmt = $db_connection->prepare("
            SELECT * FROM events
            WHERE date >= CURDATE()
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY date ASC, startTime ASC
            LIMIT 3
        ");
        $stmt->execute();
        $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log('[Dashboard] Error loading data: ' . $e->getMessage());
    }
}

$sessionUsername = $_SESSION['username'] ?? ($_SESSION['google_name'] ?? 'Student');
$fullName = $student['fullName'] ?? ($_SESSION['full_name'] ?? $sessionUsername);
$gradeLevel = $student['gradeLevel'] ?? 'Not assigned';

if ($createdAt) {
    $isAccountBirthday = (date('md', strtotime($createdAt)) === date('md'));
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>EduMind+ | Student Dashboard</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="student-portal">
 <?php include __DIR__ . '/../partials/navbar_student.php'; ?>

 <main class="container py-4">
<?php if ($isAccountBirthday): ?>
 <div class="alert alert-success shadow-sm d-flex align-items-center gap-2 birthday-alert" role="alert">
     <span style="font-size: 1.4rem;">🎉</span>
     <div>
         <strong>Happy account birthday, <?php echo htmlspecialchars($fullName); ?>!</strong>
         <div class="small text-muted">Thanks for learning with us.</div>
     </div>
 </div>
<?php endif; ?>

 <div class="row g-4 stagger">
 <div class="col-12 col-lg-8">
 <div class="card shadow-sm mb-4 glass-panel">
 <div class="card-body">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h2 class="h5 mb-0">📊 My Performance</h2>
 <div class="d-flex gap-2">
 <a href="courses.php" class="btn btn-sm btn-primary">Take Quiz</a>
 <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#scoresModal">View Scores</button>
 </div>
 </div>
 <div style="height: 200px; position: relative;">
  <canvas id="progressChart"></canvas>
 </div>
 </div>
 </div>
 <div class="card shadow-sm glass-panel">
 <div class="card-body">
 <h2 class="h5 mb-3">📅 Upcoming Events</h2>
 <div id="upcomingEvents">
 <?php if (empty($upcomingEvents)): ?>
 <p class="text-muted">No upcoming events</p>
 <?php else: ?>
 <ul class="list-group list-group-flush">
 <?php foreach ($upcomingEvents as $event): ?>
 <li class="list-group-item">
 <div class="d-flex justify-content-between align-items-start">
 <div>
 <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
 <small class="text-muted">
 <i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($event['date'])); ?>
 <i class="bi bi-clock ms-2"></i> <?php echo htmlspecialchars($event['startTime']); ?>
 </small>
 </div>
 <span class="badge bg-primary"><?php echo htmlspecialchars($event['type'] ?? 'Event'); ?></span>
 </div>
 </li>
 <?php endforeach; ?>
 </ul>
 <?php endif; ?>
 </div>
 </div>
 </div>
 </div>
 <div class="col-12 col-lg-4">
 <div class="card shadow-sm profile-card mb-4">
 <div class="card-body text-center py-4">
 <img src="../../shared-assets/img/student-portal.jpg" alt="Student" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid white;">
 <h3 class="h5 mb-1"><?php echo htmlspecialchars($fullName); ?></h3>
 <p class="mb-0 small opacity-75"><?php echo htmlspecialchars($gradeLevel); ?></p>
 </div>
 </div>
 <div class="card shadow-sm glass-panel">
 <div class="card-body">
 <h2 class="h5 mb-3">💡 Continue Learning</h2>
 <p class="text-muted small">Personalized suggestions</p>
 <div class="d-flex flex-column gap-2">
  <a href="courses.php" class="pill-link">Browse All Courses <i class="bi bi-arrow-right"></i></a>
  <a href="quiz.php" class="pill-link">Take a Quiz <i class="bi bi-arrow-right"></i></a>
  <a href="projects.php" class="pill-link">View Projects <i class="bi bi-arrow-right"></i></a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="row g-4 mt-1">
 <div class="col-12">
 <div class="card shadow-sm">
 <div class="card-body">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <h2 class="h5 mb-0">Recent Results</h2>
 <a href="courses.php" class="btn btn-sm btn-outline-secondary">Take a Quiz</a>
 </div>
 <div id="recentResults" class="table-responsive">
 <?php if (empty($recentScores)): ?>
 <p class="text-muted">No quiz attempts yet. <a href="courses.php">Start learning!</a></p>
 <?php else: ?>
 <table class="table table-hover">
 <thead>
 <tr>
 <th>Quiz</th>
 <th>Course</th>
 <th>Score</th>
 <th>Date</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($recentScores as $score): ?>
 <tr>
 <td><?php echo htmlspecialchars($score['quiz_title'] ?? 'Unknown Quiz'); ?></td>
 <td><?php echo htmlspecialchars($score['course_title'] ?? 'N/A'); ?></td>
 <td>
 <span class="badge <?php echo ($score['score'] >= 70) ? 'bg-success' : (($score['score'] >= 50) ? 'bg-warning' : 'bg-danger'); ?>">
 <?php echo number_format($score['score'], 1); ?>%
 </span>
 </td>
 <td><?php echo date('M d, Y', strtotime($score['attempt_date'])); ?></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 <?php endif; ?>
 </div>
 </div>
 </div>
 </div>
 </div>
 </main>

 <div class="modal fade" id="scoresModal" tabindex="-1" aria-hidden="true" aria-labelledby="scoresModalLabel">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content">
 <div class="modal-header">
 <h1 class="modal-title fs-5" id="scoresModalLabel">📈 My Scores by Subject</h1>
 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 </div>
 <div class="modal-body">
 <p class="text-muted small">Average scores based on your quiz performance.</p>
 <ul class="list-group" id="scoresList">
  <?php if (empty($recentScores)): ?>
   <li class="list-group-item text-muted">No scores yet</li>
  <?php else: ?>
   <?php 
   $totalScore = 0;
   $count = count($recentScores);
   foreach ($recentScores as $score) {
    $totalScore += $score['score'];
   }
   $avgScore = $count > 0 ? $totalScore / $count : 0;
   ?>
   <li class="list-group-item d-flex justify-content-between align-items-center">
    <span>Average Score</span>
    <span class="badge <?php echo ($avgScore >= 70) ? 'bg-success' : (($avgScore >= 50) ? 'bg-warning' : 'bg-danger'); ?> rounded-pill">
     <?php echo number_format($avgScore, 1); ?>%
    </span>
   </li>
   <li class="list-group-item d-flex justify-content-between align-items-center">
    <span>Total Quizzes Taken</span>
    <span class="badge bg-primary rounded-pill"><?php echo $count; ?></span>
   </li>
  <?php endif; ?>
 </ul>
 </div>
 <div class="modal-footer">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
 </div>
 </div>
 </div>
 </div>

 <style>
 .birthday-alert {
     border-left: 4px solid #28a745;
 }
 .confetti-dot {
     position: fixed;
     width: 8px;
     height: 8px;
     border-radius: 50%;
     opacity: 0.9;
     animation: confetti-fall 1.8s ease-out forwards;
 }
 @keyframes confetti-fall {
     0% { transform: translateY(-20px) rotate(0deg); }
     100% { transform: translateY(120px) rotate(360deg); opacity: 0; }
 }
 </style>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/vendor/chart.umd.min.js"></script>
 <script>
 <?php if ($isAccountBirthday): ?>
 (function(){
     const colors = ['#ff6b6b', '#ffd93d', '#6bcb77', '#4d96ff', '#a26bfa'];
     for (let i = 0; i < 24; i++) {
         const dot = document.createElement('div');
         dot.className = 'confetti-dot';
         dot.style.background = colors[i % colors.length];
         dot.style.left = (10 + Math.random() * 80) + 'vw';
         dot.style.top = '-10px';
         dot.style.animationDelay = (Math.random() * 0.6) + 's';
         dot.style.transform = `translateY(-20px) rotate(${Math.random()*90}deg)`;
         document.body.appendChild(dot);
         setTimeout(() => dot.remove(), 2000);
     }
 })();
 <?php endif; ?>

 // Simple chart for progress (if canvas exists)
 document.addEventListener('DOMContentLoaded', function() {
 const canvas = document.getElementById('progressChart');
 if (canvas && typeof Chart !== 'undefined') {
 const ctx = canvas.getContext('2d');
 new Chart(ctx, {
 type: 'line',
 data: {
 labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
 datasets: [{
 label: 'Progress',
 data: [65, 70, 75, 80],
 borderColor: '#667eea',
 backgroundColor: 'rgba(102, 126, 234, 0.1)',
 tension: 0.4,
 fill: true
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: { display: false }
 },
 scales: {
 y: {
 beginAtZero: true,
 max: 100
 }
 }
 }
 });
 }
 });
 </script>
</body>
</html>



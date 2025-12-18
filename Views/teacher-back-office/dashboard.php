<?php
require_once '../../Controllers/config.php';
// auth_check already included by config.php

// Verify teacher role
$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'teacher') {
    header('Location: login.php?error=unauthorized');
    exit;
}

// Fetch teacher data
$user_id = $_SESSION['user_id'] ?? null;
$teacher = null;
$studentCount = 0;
$courseCount = 0;
$quizCount = 0;
$eventCount = 0;
$courseAverages = [];
$dailyAttempts = [];
$recentStudents = [];

if ($user_id) {
    try {
        // Get teacher info
        $stmt = $db_connection->prepare("SELECT * FROM teachers WHERE id = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
        $stmt->execute([$user_id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get course count for this teacher
        $stmt = $db_connection->prepare("SELECT COUNT(*) FROM courses WHERE teacherId = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
        $stmt->execute([$user_id]);
        $courseCount = $stmt->fetchColumn();
        
        // Get quiz count for this teacher's courses
        $stmt = $db_connection->prepare("SELECT COUNT(*) FROM quizzes q JOIN courses c ON q.courseId = c.id WHERE c.teacherId = ?");
        $stmt->execute([$user_id]);
        $quizCount = $stmt->fetchColumn();
        
        // Get event count for this teacher
        $stmt = $db_connection->prepare("SELECT COUNT(*) FROM events WHERE teacherId = ? AND date >= CURDATE()");
        $stmt->execute([$user_id]);
        $eventCount = $stmt->fetchColumn();
        
        // Get students who have attempted this teacher's quizzes
        $stmt = $db_connection->prepare("
            SELECT COUNT(DISTINCT sc.userId) 
            FROM scores sc 
            JOIN courses c ON sc.courseId = c.id 
            WHERE c.teacherId = ?
        ");
        $stmt->execute([$user_id]);
        $studentCount = $stmt->fetchColumn();
        
        // Get average scores by course for this teacher
        $stmt = $db_connection->prepare("
            SELECT c.id, c.title, 
                   ROUND(AVG((sc.score / NULLIF(sc.total, 0)) * 100), 0) as avgScore,
                   COUNT(sc.id) as attempts
            FROM courses c
            LEFT JOIN scores sc ON sc.courseId = c.id
            WHERE c.teacherId = ? AND (c.deleted_at IS NULL OR c.deleted_at = '0000-00-00 00:00:00')
            GROUP BY c.id, c.title
            ORDER BY c.title ASC
        ");
        $stmt->execute([$user_id]);
        $courseAverages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get daily attempts for this teacher's courses (last 14 days)
        $stmt = $db_connection->prepare("
            SELECT DATE(sc.timestamp) as day, COUNT(*) as attempts
            FROM scores sc
            JOIN courses c ON sc.courseId = c.id
            WHERE c.teacherId = ? AND sc.timestamp >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            GROUP BY DATE(sc.timestamp)
            ORDER BY day ASC
        ");
        $stmt->execute([$user_id]);
        $dailyAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent active students
        $stmt = $db_connection->prepare("
            SELECT s.id, s.username, s.fullName, 
                   COUNT(sc.id) as attempts,
                   ROUND(AVG((sc.score / NULLIF(sc.total, 0)) * 100), 0) as avgScore,
                   MAX(sc.timestamp) as lastAttempt
            FROM students s
            JOIN scores sc ON sc.userId = s.id
            JOIN courses c ON sc.courseId = c.id
            WHERE c.teacherId = ? AND (s.deleted_at IS NULL)
            GROUP BY s.id, s.username, s.fullName
            ORDER BY lastAttempt DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log('[Teacher Dashboard] Error loading data: ' . $e->getMessage());
    }
}

$fullName = $teacher['fullName'] ?? $_SESSION['username'] ?? 'Teacher';
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Teacher Dashboard | EduMind+</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
   body {
     background: var(--bg-body);
     min-height: 100vh;
   }
   
   /* Dark mode body background with subtle gradient */
   :root[data-theme="dark"] body {
     background: var(--bg-body) !important;
   }
   
   .card {
     border: 1px solid var(--border-color);
     border-radius: 1rem;
     box-shadow: var(--shadow-sm);
     transition: all 0.3s ease;
     animation: fadeInUp 0.6s ease-out;
     animation-fill-mode: both;
     background: var(--surface-1);
   }
   
   .card:nth-child(1) { animation-delay: 0.1s; }
   .card:nth-child(2) { animation-delay: 0.2s; }
   .card:nth-child(3) { animation-delay: 0.3s; }
   
   .card:hover {
     transform: translateY(-4px);
     box-shadow: var(--shadow-lg);
   }
   
   .card-body h2 {
     color: var(--text-primary);
     font-weight: 600;
   }
   
   .btn-outline-primary {
     border-radius: 8px;
     transition: all 0.3s ease;
   }
   
   .btn-outline-primary:hover {
     transform: scale(1.05);
     background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
     border-color: #11998e;
   }
   
   .btn-outline-secondary {
     border-radius: 8px;
     transition: all 0.3s ease;
   }
   
   .btn-outline-secondary:hover {
     transform: scale(1.05);
   }
   
   .btn-outline-light {
     border-radius: 8px;
     transition: all 0.3s ease;
     border: 2px solid white;
   }
   
   .btn-outline-light:hover {
     transform: scale(1.05);
     background: white;
     color: #11998e;
   }
   
   @keyframes fadeInUp {
     from {
       opacity: 0;
       transform: translateY(20px);
     }
     to {
       opacity: 1;
       transform: translateY(0);
     }
   }
   
   .stat {
     background: var(--surface-1);
     padding: 1.5rem;
     border-radius: 1rem;
     box-shadow: var(--shadow-sm);
     transition: all 0.3s ease;
     border: 1px solid var(--border-color);
     border-left: 4px solid var(--success-color);
   }
   
   .stat:hover {
     transform: translateY(-3px);
     box-shadow: var(--shadow-lg);
   }
   
   .stat .text-muted { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--text-secondary) !important; }
   .stat .h4 { font-weight: 700; color: var(--text-primary); font-size: 2rem; }
   
   .col-6:nth-child(1) .stat { border-left-color: var(--success-color); }
   .col-6:nth-child(2) .stat { border-left-color: #38ef7d; }
   .col-6:nth-child(3) .stat { border-left-color: var(--info-color); }
   .col-6:nth-child(4) .stat { border-left-color: var(--warning-color); }
 </style>
</head>
<body data-page="teacher-dashboard">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <!-- Welcome Header -->
 <div class="mb-4">
   <h1 class="h3">Welcome, <?php echo htmlspecialchars($fullName); ?>!</h1>
   <p class="text-muted">Here's your teaching overview.</p>
 </div>
 
 <!-- Stats Row -->
 <div class="row g-3 mb-4">
   <div class="col-6 col-lg-3">
     <div class="stat">
       <div class="text-muted small"><i class="bi bi-book-half me-1"></i>Courses</div>
       <div class="h4 mb-0"><?php echo (int)$courseCount; ?></div>
     </div>
   </div>
   <div class="col-6 col-lg-3">
     <div class="stat">
       <div class="text-muted small"><i class="bi bi-journal-check me-1"></i>Quizzes</div>
       <div class="h4 mb-0"><?php echo (int)$quizCount; ?></div>
     </div>
   </div>
   <div class="col-6 col-lg-3">
     <div class="stat">
       <div class="text-muted small"><i class="bi bi-people me-1"></i>Students</div>
       <div class="h4 mb-0"><?php echo (int)$studentCount; ?></div>
     </div>
   </div>
   <div class="col-6 col-lg-3">
     <div class="stat">
       <div class="text-muted small"><i class="bi bi-calendar-event me-1"></i>Upcoming Events</div>
       <div class="h4 mb-0"><?php echo (int)$eventCount; ?></div>
     </div>
   </div>
 </div>
 
 <!-- Charts Row -->
 <div class="row g-4 mb-4">
   <div class="col-12 col-lg-7">
     <div class="card shadow-sm">
       <div class="card-body p-4">
         <div class="d-flex justify-content-between align-items-center mb-3">
           <h2 class="h5 mb-0">
             <i class="bi bi-bar-chart-fill text-success me-2"></i>
             Average Scores by Course
           </h2>
           <a href="courses.php" class="btn btn-sm btn-outline-primary">
             <i class="bi bi-gear me-1"></i>Manage Courses
           </a>
         </div>
         <?php if (empty($courseAverages)): ?>
           <p class="text-muted">No course data yet. Create courses and quizzes to see statistics.</p>
         <?php else: ?>
           <canvas id="courseAverages" height="120"></canvas>
         <?php endif; ?>
       </div>
     </div>
   </div>
   <div class="col-12 col-lg-5">
     <div class="card shadow-sm">
       <div class="card-body p-4">
         <div class="d-flex justify-content-between align-items-center mb-3">
           <h2 class="h5 mb-0">
             <i class="bi bi-activity text-info me-2"></i>
             Activity (Attempts / Day)
           </h2>
           <a href="students.php" class="btn btn-sm btn-outline-secondary">
             <i class="bi bi-eye me-1"></i>View Students
           </a>
         </div>
         <?php if (empty($dailyAttempts)): ?>
           <p class="text-muted">No quiz activity in the last 14 days.</p>
         <?php else: ?>
           <canvas id="attemptsChart" height="120"></canvas>
         <?php endif; ?>
       </div>
     </div>
   </div>
 </div>
 
 <!-- Quick Actions & Recent Students -->
 <div class="row g-4">
   <div class="col-12 col-lg-4">
     <div class="card shadow-sm h-100">
       <div class="card-body">
         <h5 class="card-title mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Quick Actions</h5>
         <div class="d-flex flex-column gap-2">
           <a href="quiz-builder.php" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-pen me-2"></i>Create New Quiz</a>
           <a href="courses.php" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-book me-2"></i>Add Course</a>
           <a href="events.php" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-calendar-plus me-2"></i>Schedule Event</a>
           <a href="quiz-reports.php" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-graph-up me-2"></i>View Quiz Reports</a>
         </div>
       </div>
     </div>
   </div>
   
   <div class="col-12 col-lg-8">
     <div class="card shadow-sm h-100">
       <div class="card-body">
         <h5 class="card-title mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Recent Active Students</h5>
         <?php if (empty($recentStudents)): ?>
           <p class="text-muted">No student activity yet. Once students attempt your quizzes, they'll appear here.</p>
         <?php else: ?>
           <div class="table-responsive">
             <table class="table table-sm align-middle">
               <thead>
                 <tr>
                   <th>Student</th>
                   <th class="text-center">Attempts</th>
                   <th class="text-center">Avg Score</th>
                   <th>Last Active</th>
                 </tr>
               </thead>
               <tbody>
                 <?php foreach ($recentStudents as $stu): ?>
                   <tr>
                     <td><?php echo htmlspecialchars($stu['fullName'] ?: $stu['username']); ?></td>
                     <td class="text-center"><?php echo (int)$stu['attempts']; ?></td>
                     <td class="text-center"><?php echo $stu['avgScore'] !== null ? (int)$stu['avgScore'] . '%' : '—'; ?></td>
                     <td class="text-muted small"><?php echo $stu['lastAttempt'] ? date('M j, Y', strtotime($stu['lastAttempt'])) : '—'; ?></td>
                   </tr>
                 <?php endforeach; ?>
               </tbody>
             </table>
           </div>
           <a href="students.php" class="btn btn-sm btn-outline-secondary mt-2">View All Students</a>
         <?php endif; ?>
       </div>
     </div>
   </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/vendor/chart.umd.min.js"></script>
 <script>
 // Render charts with PHP data
 document.addEventListener('DOMContentLoaded', function() {
   <?php if (!empty($courseAverages)): ?>
   // Course Averages Chart
   const courseCtx = document.getElementById('courseAverages');
   if (courseCtx) {
     new Chart(courseCtx, {
       type: 'bar',
       data: {
         labels: <?php echo json_encode(array_column($courseAverages, 'title')); ?>,
         datasets: [{
           label: 'Avg %',
           data: <?php echo json_encode(array_map(fn($c) => (int)($c['avgScore'] ?? 0), $courseAverages)); ?>,
           backgroundColor: '#11998e'
         }]
       },
       options: {
         scales: { y: { beginAtZero: true, max: 100 } },
         plugins: { legend: { display: false } }
       }
     });
   }
   <?php endif; ?>
   
   <?php if (!empty($dailyAttempts)): ?>
   // Daily Attempts Chart
   const attemptsCtx = document.getElementById('attemptsChart');
   if (attemptsCtx) {
     new Chart(attemptsCtx, {
       type: 'line',
       data: {
         labels: <?php echo json_encode(array_column($dailyAttempts, 'day')); ?>,
         datasets: [{
           label: 'Attempts',
           data: <?php echo json_encode(array_column($dailyAttempts, 'attempts')); ?>,
           borderColor: '#22c55e',
           fill: false
         }]
       },
       options: {
         plugins: { legend: { display: false } }
       }
     });
   }
   <?php endif; ?>
 });
 </script>
</body>
</html>



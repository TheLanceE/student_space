<?php
require_once '../../Controllers/auth_check.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'admin') {
  http_response_code(403);
  die('Forbidden');
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Admin Dashboard | EduMind+</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
   body {
     background: #f8f9fa;
     min-height: 100vh;
   }
   
   .navbar {
     background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
     box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
     border-bottom: 3px solid #1e3c72;
   }
   
   .navbar-brand {
     font-weight: 700;
     font-size: 1.2rem;
     display: flex;
     align-items: center;
     gap: 0.5rem;
     letter-spacing: 0.5px;
   }
   
   .nav-link {
     position: relative;
     transition: all 0.2s ease;
     font-weight: 500;
   }
   
   .nav-link::after {
     content: '';
     position: absolute;
     bottom: 0;
     left: 50%;
     width: 0;
     height: 2px;
     background: white;
     transition: all 0.2s ease;
     transform: translateX(-50%);
   }
   
   .nav-link:hover::after,
   .nav-link.active::after {
     width: 70%;
   }
   
   .stat {
     background: white;
     padding: 1.5rem;
     border-radius: 10px;
     box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
     transition: all 0.3s ease;
     border-left: 4px solid #1e3c72;
     position: relative;
     overflow: hidden;
   }
   
   .stat::before {
     content: '';
     position: absolute;
     top: 0;
     right: 0;
     width: 60px;
     height: 60px;
     background: linear-gradient(135deg, rgba(30, 60, 114, 0.1), transparent);
     border-radius: 0 10px 0 100%;
   }
   
   .stat:hover {
     transform: translateY(-3px);
     box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
   }
   
   .col-6:nth-child(1) .stat { border-left-color: #007bff; animation: fadeInUp 0.4s ease-out; }
   .col-6:nth-child(2) .stat { border-left-color: #28a745; animation: fadeInUp 0.5s ease-out; }
   .col-6:nth-child(3) .stat { border-left-color: #ffc107; animation: fadeInUp 0.6s ease-out; }
   .col-6:nth-child(4) .stat { border-left-color: #dc3545; animation: fadeInUp 0.7s ease-out; }
   
   .stat .text-muted {
     font-size: 0.85rem;
     font-weight: 600;
     text-transform: uppercase;
     letter-spacing: 0.5px;
     margin-bottom: 0.5rem;
   }
   
   .stat .h4 {
     font-weight: 700;
     color: #2c3e50;
     font-size: 2rem;
   }
   
   .btn-outline-light {
     border-radius: 6px;
     transition: all 0.2s ease;
     border: 2px solid white;
     font-weight: 500;
   }
   
   .btn-outline-light:hover {
     transform: translateY(-1px);
     background: white;
     color: #1e3c72;
     box-shadow: 0 4px 10px rgba(255, 255, 255, 0.3);
   }
   
   @keyframes fadeInUp {
     from {
       opacity: 0;
       transform: translateY(15px);
     }
     to {
       opacity: 1;
       transform: translateY(0);
     }
   }
   
   .quick-links a {
     display: flex;
     align-items: center;
     padding: 0.75rem 1rem;
     background: white;
     border-radius: 8px;
     text-decoration: none;
     color: #2c3e50;
     border: 1px solid #e9ecef;
     transition: all 0.2s ease;
   }
   
   .quick-links a:hover {
     background: #f8f9fa;
     border-color: #1e3c72;
     transform: translateX(5px);
   }
   
   .quick-links .bi {
     font-size: 1.25rem;
     margin-right: 0.75rem;
     color: #1e3c72;
   }
   
   .activity-item {
     padding: 0.5rem 0;
     border-bottom: 1px solid #f0f0f0;
   }
   
   .activity-item:last-child {
     border-bottom: none;
   }
 </style>
</head>
<body data-page="admin-dashboard">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>
 
 <?php
 // Fetch real counts from database
 try {
     $studentCount = $db_connection->query("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL")->fetchColumn();
     $teacherCount = $db_connection->query("SELECT COUNT(*) FROM teachers WHERE deleted_at IS NULL")->fetchColumn();
     $courseCount = $db_connection->query("SELECT COUNT(*) FROM courses")->fetchColumn();
     $pendingCount = $db_connection->query("SELECT COUNT(*) FROM courses WHERE status = 'pending'")->fetchColumn();
     $quizCount = $db_connection->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
     $eventCount = $db_connection->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE()")->fetchColumn();
     
     // Recent activity
     $recentLogs = $db_connection->query("SELECT level, message, ts FROM logs ORDER BY ts DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
     
     // Top students by quiz scores
     $topStudents = $db_connection->query("
         SELECT s.username, s.fullName, COUNT(sc.id) as quizzes, AVG(sc.score/sc.total*100) as avgScore
         FROM students s 
         LEFT JOIN scores sc ON s.id = sc.userId 
         WHERE s.deleted_at IS NULL 
         GROUP BY s.id 
         ORDER BY avgScore DESC, quizzes DESC 
         LIMIT 5
     ")->fetchAll(PDO::FETCH_ASSOC);
 } catch (Exception $e) {
     $studentCount = $teacherCount = $courseCount = $pendingCount = $quizCount = $eventCount = 0;
     $recentLogs = [];
     $topStudents = [];
 }
 ?>

 <main class="container py-4">
 <!-- Stats Row -->
 <div class="row g-3 mb-4">
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-people-fill me-1"></i>Students
     </div>
     <div class="h4 mb-0"><?php echo $studentCount; ?></div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-person-workspace me-1"></i>Teachers
     </div>
     <div class="h4 mb-0"><?php echo $teacherCount; ?></div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-book-half me-1"></i>Courses
     </div>
     <div class="h4 mb-0"><?php echo $courseCount; ?></div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat">
     <div class="text-muted small">
       <i class="bi bi-hourglass-split me-1"></i>Pending Approvals
     </div>
     <div class="h4 mb-0"><?php echo $pendingCount; ?></div>
   </div>
 </div>
 </div>
 
 <!-- Second Stats Row -->
 <div class="row g-3 mb-4">
 <div class="col-6 col-lg-3">
   <div class="stat" style="border-left-color: #6f42c1;">
     <div class="text-muted small">
       <i class="bi bi-journal-check me-1"></i>Total Quizzes
     </div>
     <div class="h4 mb-0"><?php echo $quizCount; ?></div>
   </div>
 </div>
 <div class="col-6 col-lg-3">
   <div class="stat" style="border-left-color: #20c997;">
     <div class="text-muted small">
       <i class="bi bi-calendar-event me-1"></i>Upcoming Events
     </div>
     <div class="h4 mb-0"><?php echo $eventCount; ?></div>
   </div>
 </div>
 </div>
 
 <!-- Content Row -->
 <div class="row g-4">
   <!-- Quick Links -->
   <div class="col-12 col-lg-4">
     <div class="card shadow-sm h-100">
       <div class="card-body">
         <h5 class="card-title mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Quick Actions</h5>
         <div class="quick-links d-flex flex-column gap-2">
           <a href="users.php"><i class="bi bi-person-plus"></i>Manage Users</a>
           <a href="courses.php"><i class="bi bi-book"></i>Review Courses</a>
           <a href="quizzes.php"><i class="bi bi-journal-text"></i>View Quizzes</a>
           <a href="events.php"><i class="bi bi-calendar3"></i>Manage Events</a>
           <a href="reports.php"><i class="bi bi-graph-up"></i>Generate Reports</a>
           <a href="logs.php"><i class="bi bi-list-task"></i>System Logs</a>
         </div>
       </div>
     </div>
   </div>
   
   <!-- Recent Activity -->
   <div class="col-12 col-lg-4">
     <div class="card shadow-sm h-100">
       <div class="card-body">
         <h5 class="card-title mb-3"><i class="bi bi-activity text-primary me-2"></i>Recent Activity</h5>
         <?php if (empty($recentLogs)): ?>
           <p class="text-muted">No recent activity</p>
         <?php else: ?>
           <div class="activity-list">
             <?php foreach ($recentLogs as $log): ?>
               <div class="activity-item">
                 <span class="badge bg-<?php echo $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warn' ? 'warning' : 'secondary'); ?> me-2"><?php echo htmlspecialchars($log['level']); ?></span>
                 <small class="text-muted"><?php echo date('M j, g:i a', strtotime($log['ts'])); ?></small>
                 <div class="small"><?php echo htmlspecialchars(substr($log['message'], 0, 60)); ?><?php echo strlen($log['message']) > 60 ? '...' : ''; ?></div>
               </div>
             <?php endforeach; ?>
           </div>
           <a href="logs.php" class="btn btn-sm btn-outline-primary mt-3">View All Logs</a>
         <?php endif; ?>
       </div>
     </div>
   </div>
   
   <!-- Top Students -->
   <div class="col-12 col-lg-4">
     <div class="card shadow-sm h-100">
       <div class="card-body">
         <h5 class="card-title mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Students</h5>
         <?php if (empty($topStudents)): ?>
           <p class="text-muted">No student data yet</p>
         <?php else: ?>
           <ul class="list-group list-group-flush">
             <?php foreach ($topStudents as $idx => $stu): ?>
               <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                 <div>
                   <span class="badge bg-<?php echo $idx === 0 ? 'warning text-dark' : ($idx === 1 ? 'secondary' : 'light text-dark'); ?> me-2"><?php echo $idx + 1; ?></span>
                   <?php echo htmlspecialchars($stu['fullName'] ?: $stu['username']); ?>
                 </div>
                 <span class="text-muted small">
                   <?php echo $stu['quizzes']; ?> quiz<?php echo $stu['quizzes'] != 1 ? 'es' : ''; ?>
                   <?php if ($stu['avgScore']): ?>
                     · <?php echo round($stu['avgScore']); ?>%
                   <?php endif; ?>
                 </span>
               </li>
             <?php endforeach; ?>
           </ul>
           <a href="users.php" class="btn btn-sm btn-outline-primary mt-3">View All Students</a>
         <?php endif; ?>
       </div>
     </div>
   </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>
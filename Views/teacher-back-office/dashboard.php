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

if ($user_id) {
    try {
        // Get teacher info
        $stmt = $db_connection->prepare("SELECT * FROM teachers WHERE id = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
        $stmt->execute([$user_id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get student count
        $stmt = $db_connection->prepare("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL");
        $stmt->execute();
        $studentCount = $stmt->fetchColumn();
        
        // Get course count for this teacher
        $stmt = $db_connection->prepare("SELECT COUNT(*) FROM courses WHERE teacherId = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
        $stmt->execute([$user_id]);
        $courseCount = $stmt->fetchColumn();
        
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
     background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
     min-height: 100vh;
   }
   
   .navbar {
     background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
     box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
     animation: slideDown 0.6s ease-out;
   }
   
   .navbar-brand {
     font-weight: 700;
     font-size: 1.3rem;
     display: flex;
     align-items: center;
     gap: 0.5rem;
   }
   
   .nav-link {
     position: relative;
     transition: all 0.3s ease;
   }
   
   .nav-link::after {
     content: '';
     position: absolute;
     bottom: 0;
     left: 50%;
     width: 0;
     height: 2px;
     background: white;
     transition: all 0.3s ease;
     transform: translateX(-50%);
   }
   
   .nav-link:hover::after,
   .nav-link.active::after {
     width: 80%;
   }
   
   .card {
     border: none;
     border-radius: 15px;
     box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
     transition: all 0.3s ease;
     animation: fadeInUp 0.6s ease-out;
     animation-fill-mode: both;
     background: white;
   }
   
   .card:nth-child(1) { animation-delay: 0.1s; }
   .card:nth-child(2) { animation-delay: 0.2s; }
   .card:nth-child(3) { animation-delay: 0.3s; }
   
   .card:hover {
     transform: translateY(-5px);
     box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
   }
   
   .card-body h2 {
     color: #2c3e50;
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
     background: #6c757d;
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
   
   @keyframes slideDown {
     from {
       transform: translateY(-100%);
       opacity: 0;
     }
     to {
       transform: translateY(0);
       opacity: 1;
     }
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
 </style>
</head>
<body data-page="teacher-dashboard">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <div class="row g-4">
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
 <canvas id="courseAverages" height="120"></canvas>
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
 <canvas id="attemptsChart" height="120"></canvas>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/vendor/chart.umd.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/auth-teacher.js"></script>
 <script src="assets/js/data-teacher.js"></script>
 <script src="assets/js/charts-teacher.js"></script>
 <script src="assets/js/ui-teacher.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>



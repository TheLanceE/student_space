<?php 
// Check authentication FIRST before any output
require_once '../../Controllers/auth_check.php';

// Fetch all active courses from database
$courses = [];
try {
    $stmt = $db_connection->prepare("
        SELECT c.*, 
               t.fullName as teacherName,
               (SELECT COUNT(*) FROM quizzes q WHERE q.courseId = c.id) as quizCount
        FROM courses c
        LEFT JOIN teachers t ON c.teacherId = t.id
        WHERE c.status = 'active' AND (c.deleted_at IS NULL OR c.deleted_at = '0000-00-00 00:00:00')
        ORDER BY c.createdAt DESC
    ");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[Student Courses] Error loading courses: ' . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>EduMind+ | Courses</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="front-courses">
 <?php include __DIR__ . '/../partials/navbar_student.php'; ?>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="h4 mb-0"><i class="bi bi-book me-2"></i>Available Courses</h1>
   <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
 </div>
 
 <?php if (empty($courses)): ?>
   <div class="alert alert-info">
     <i class="bi bi-info-circle me-2"></i>No courses available at the moment. Check back later!
   </div>
 <?php else: ?>
   <div class="row g-4">
   <?php foreach ($courses as $course): ?>
     <div class="col-12 col-md-6 col-lg-4">
       <div class="card h-100 shadow-sm border-0">
         <div class="card-body d-flex flex-column">
           <div class="d-flex justify-content-between align-items-start mb-2">
             <h5 class="card-title mb-0"><?= htmlspecialchars($course['title']) ?></h5>
             <span class="badge bg-primary"><?= (int)$course['quizCount'] ?> quizzes</span>
           </div>
           <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($course['description'] ?? 'No description available.') ?></p>
           <?php if (!empty($course['teacherName'])): ?>
             <p class="small text-secondary mb-2">
               <i class="bi bi-person-badge me-1"></i>Instructor: <?= htmlspecialchars($course['teacherName']) ?>
             </p>
           <?php endif; ?>
           <div class="mt-auto">
             <a href="quiz.php?courseId=<?= htmlspecialchars($course['id']) ?>" class="btn btn-primary btn-sm w-100">
               <i class="bi bi-play-circle me-1"></i>Start Learning
             </a>
           </div>
         </div>
       </div>
     </div>
   <?php endforeach; ?>
   </div>
 <?php endif; ?>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>



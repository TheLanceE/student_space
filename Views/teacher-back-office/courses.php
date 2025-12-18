<?php 
require_once '../../Controllers/config.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'teacher') {
    http_response_code(403);
    die('Forbidden');
}

$teacherId = $_SESSION['user_id'] ?? null;
$message = null;
$error = null;
$courses = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($title)) {
                $error = 'Course title is required.';
            } else {
                try {
                    $courseId = 'course_' . bin2hex(random_bytes(8));
                    $stmt = $db_connection->prepare("INSERT INTO courses (id, title, description, teacherId, status, createdAt) VALUES (?, ?, ?, ?, 'active', NOW())");
                    $stmt->execute([$courseId, $title, $description, $teacherId]);
                    $message = 'Course created successfully!';
                } catch (Exception $e) {
                    error_log('[Teacher Courses] Error creating course: ' . $e->getMessage());
                    $error = 'Failed to create course.';
                }
            }
        } elseif ($action === 'delete') {
            $courseId = $_POST['course_id'] ?? '';
            if ($courseId) {
                try {
                    $stmt = $db_connection->prepare("UPDATE courses SET deleted_at = NOW() WHERE id = ? AND teacherId = ?");
                    $stmt->execute([$courseId, $teacherId]);
                    $message = 'Course deleted successfully.';
                } catch (Exception $e) {
                    error_log('[Teacher Courses] Error deleting course: ' . $e->getMessage());
                    $error = 'Failed to delete course.';
                }
            }
        }
    }
}

// Fetch teacher's courses
if ($teacherId) {
    try {
        $stmt = $db_connection->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM quizzes q WHERE q.courseId = c.id) as quizCount,
                   (SELECT COUNT(*) FROM scores s WHERE s.courseId = c.id) as attemptCount
            FROM courses c 
            WHERE c.teacherId = ? AND (c.deleted_at IS NULL OR c.deleted_at = '0000-00-00 00:00:00')
            ORDER BY c.createdAt DESC
        ");
        $stmt->execute([$teacherId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('[Teacher Courses] Error loading courses: ' . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Courses | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-courses">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <?php if ($message): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
     <?= htmlspecialchars($message) ?>
     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
 <?php endif; ?>
 <?php if ($error): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
     <?= htmlspecialchars($error) ?>
     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
 <?php endif; ?>
 
 <div class="row g-4">
 <div class="col-12 col-lg-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h2 class="h6"><i class="bi bi-plus-circle me-2"></i>Add Course</h2>
 <form method="post">
 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
 <input type="hidden" name="action" value="add">
 <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
 <div class="mb-2"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
 <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-check-circle me-1"></i>Add Course</button>
 </form>
 </div>
 </div>
 </div>
 <div class="col-12 col-lg-8">
 <div class="card shadow-sm">
 <div class="card-body">
 <h2 class="h6"><i class="bi bi-book-half me-2"></i>Your Courses (<?= count($courses) ?>)</h2>
 <?php if (empty($courses)): ?>
   <p class="text-muted">No courses yet. Create one to get started!</p>
 <?php else: ?>
   <div class="row g-3">
   <?php foreach ($courses as $c): ?>
     <div class="col-12 col-md-6">
       <div class="card h-100 border">
         <div class="card-body d-flex flex-column">
           <h5 class="card-title"><?= htmlspecialchars($c['title']) ?></h5>
           <p class="text-muted small"><?= htmlspecialchars($c['description'] ?? '') ?></p>
           <div class="d-flex gap-2 mb-2 small text-muted">
             <span><i class="bi bi-journal-text"></i> <?= (int)$c['quizCount'] ?> quizzes</span>
             <span><i class="bi bi-people"></i> <?= (int)$c['attemptCount'] ?> attempts</span>
           </div>
           <div class="mt-auto d-flex gap-2">
             <a href="quiz-builder.php?courseId=<?= htmlspecialchars($c['id']) ?>" class="btn btn-primary btn-sm">
               <i class="bi bi-pen me-1"></i>Build Quiz
             </a>
             <form method="post" class="d-inline" onsubmit="return confirm('Delete this course?');">
               <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
               <input type="hidden" name="action" value="delete">
               <input type="hidden" name="course_id" value="<?= htmlspecialchars($c['id']) ?>">
               <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
             </form>
           </div>
         </div>
       </div>
     </div>
   <?php endforeach; ?>
   </div>
 <?php endif; ?>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>



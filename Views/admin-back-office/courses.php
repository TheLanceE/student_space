<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';

if (($_SESSION['role'] ?? null) !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

$message = null;
$error = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        $courseId = $_POST['course_id'] ?? '';
        
        if ($action === 'approve' && $courseId) {
            try {
                $stmt = $db_connection->prepare("UPDATE courses SET status = 'active' WHERE id = ?");
                $stmt->execute([$courseId]);
                $message = 'Course approved successfully!';
            } catch (Exception $e) {
                error_log('[Admin Courses] Error approving course: ' . $e->getMessage());
                $error = 'Failed to approve course.';
            }
        } elseif ($action === 'delete' && $courseId) {
            try {
                $stmt = $db_connection->prepare("UPDATE courses SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$courseId]);
                $message = 'Course deleted successfully.';
            } catch (Exception $e) {
                error_log('[Admin Courses] Error deleting course: ' . $e->getMessage());
                $error = 'Failed to delete course.';
            }
        }
    }
}

// Fetch all courses
$courses = [];
try {
    $stmt = $db_connection->prepare("
        SELECT c.*, 
               t.fullName as teacherName,
               (SELECT COUNT(*) FROM quizzes q WHERE q.courseId = c.id) as quizCount,
               (SELECT COUNT(*) FROM scores s WHERE s.courseId = c.id) as attemptCount
        FROM courses c
        LEFT JOIN teachers t ON c.teacherId = t.id
        WHERE c.deleted_at IS NULL OR c.deleted_at = '0000-00-00 00:00:00'
        ORDER BY c.status ASC, c.createdAt DESC
    ");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[Admin Courses] Error loading courses: ' . $e->getMessage());
}

$pendingCount = count(array_filter($courses, fn($c) => ($c['status'] ?? '') === 'pending'));
$activeCount = count(array_filter($courses, fn($c) => ($c['status'] ?? '') === 'active'));
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Courses | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-courses">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

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
 
 <div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="h4 mb-0"><i class="bi bi-book me-2"></i>Course Management</h1>
   <div class="d-flex gap-2">
     <span class="badge bg-warning text-dark"><?= $pendingCount ?> Pending</span>
     <span class="badge bg-success"><?= $activeCount ?> Active</span>
   </div>
 </div>
 
 <div class="card shadow-sm">
   <div class="card-body">
   <?php if (empty($courses)): ?>
     <p class="text-muted mb-0">No courses found.</p>
   <?php else: ?>
     <div class="table-responsive">
       <table class="table table-hover align-middle">
         <thead>
           <tr>
             <th>Course</th>
             <th>Teacher</th>
             <th>Status</th>
             <th>Quizzes</th>
             <th>Attempts</th>
             <th class="text-end">Actions</th>
           </tr>
         </thead>
         <tbody>
         <?php foreach ($courses as $c): ?>
           <tr>
             <td>
               <div class="fw-semibold"><?= htmlspecialchars($c['title']) ?></div>
               <div class="text-muted small"><?= htmlspecialchars(substr($c['description'] ?? '', 0, 60)) ?>...</div>
             </td>
             <td><?= htmlspecialchars($c['teacherName'] ?? 'Unknown') ?></td>
             <td>
               <?php if (($c['status'] ?? '') === 'active'): ?>
                 <span class="badge bg-success">Active</span>
               <?php elseif (($c['status'] ?? '') === 'pending'): ?>
                 <span class="badge bg-warning text-dark">Pending</span>
               <?php else: ?>
                 <span class="badge bg-secondary"><?= htmlspecialchars($c['status'] ?? 'Unknown') ?></span>
               <?php endif; ?>
             </td>
             <td><?= (int)$c['quizCount'] ?></td>
             <td><?= (int)$c['attemptCount'] ?></td>
             <td class="text-end">
               <div class="d-flex gap-1 justify-content-end">
               <?php if (($c['status'] ?? '') === 'pending'): ?>
                 <form method="post" class="d-inline">
                   <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                   <input type="hidden" name="action" value="approve">
                   <input type="hidden" name="course_id" value="<?= htmlspecialchars($c['id']) ?>">
                   <button class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i></button>
                 </form>
               <?php endif; ?>
                 <form method="post" class="d-inline" onsubmit="return confirm('Delete this course?');">
                   <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                   <input type="hidden" name="action" value="delete">
                   <input type="hidden" name="course_id" value="<?= htmlspecialchars($c['id']) ?>">
                   <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                 </form>
               </div>
             </td>
           </tr>
         <?php endforeach; ?>
         </tbody>
       </table>
     </div>
   <?php endif; ?>
   </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>
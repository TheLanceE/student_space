<?php 
// Check authentication FIRST before any output
require_once '../../Controllers/auth_check.php';
$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'teacher') {
    http_response_code(403);
    die('Forbidden');
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
 <div class="row g-4">
 <div class="col-12 col-lg-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h2 class="h6">Add Course</h2>
 <form id="addCourseForm">
 <div class="mb-2"><label class="form-label">Title</label><input class="form-control" id="cTitle" required></div>
 <div class="mb-2"><label class="form-label">Description</label><textarea class="form-control" id="cDesc" rows="3"></textarea></div>
 <button class="btn btn-primary btn-sm" type="submit">Add</button>
 </form>
 </div>
 </div>
 </div>
 <div class="col-12 col-lg-8">
 <div class="card shadow-sm">
 <div class="card-body">
 <h2 class="h6">Your Courses</h2>
 <div class="row g-3" id="courseList"></div>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>



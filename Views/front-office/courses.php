<?php 
// Check authentication FIRST before any output
require_once '../../Controllers/auth_check.php';
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
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h1 class="h4 mb-0">Courses</h1>
 <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
 </div>
 <div id="courseList" class="row g-4"></div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="assets/js/auth.js"></script>
 <script src="assets/js/data.js"></script>
 <script src="assets/js/ui.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>



<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';

if (($_SESSION['role'] ?? null) !== 'teacher') {
	http_response_code(403);
	die('Forbidden');
}
?>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Projects | EduMind+</title>
 <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/projects.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
 /* Hide edit and delete buttons for teachers - READ ONLY */
 .btn-edit, .btn-delete { display: none !important; }
 #projectModal { display: none !important; }
 </style>
</head>
<body data-page="teacher-projects">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-4">
 <h1 class="h3">All Projects (Read Only)</h1>
 </div>

 <div id="projectsList" class="row g-4">
 <div class="col-12 text-center py-5">
 <div class="spinner-border text-success" role="status">
 <span class="visually-hidden">Loading...</span>
 </div>
 </div>
 </div>
 </main>

 <!-- Project Detail Modal -->
 <div class="modal fade" id="projectDetailModal" tabindex="-1">
 <div class="modal-dialog modal-xl">
 <div class="modal-content">
 <div class="modal-header">
 <h5 class="modal-title" id="projectDetailTitle">Project Details</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body" id="projectDetailBody">
 <!-- Project details will be loaded here -->
 </div>
 <div class="modal-footer">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
 </div>
 </div>
 </div>
 </div>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/projects.js"></script>
 <script src="assets/js/auth-teacher.js"></script>
</body>
</html>


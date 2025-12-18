<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';

// Allow students, teachers, and admins
$role = $_SESSION['role'] ?? null;
if (!in_array($role, ['student', 'teacher', 'admin'])) {
    http_response_code(403);
    die('Forbidden - Please log in');
}

// Determine page title based on role
$pageTitle = $role === 'student' ? 'My Projects' : 'All Projects';
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
</head>
<body data-page="front-projects">
 <?php 
 // Include appropriate navbar based on role
 if ($role === 'teacher') {
     include __DIR__ . '/../partials/navbar_teacher.php';
 } elseif ($role === 'admin') {
     include __DIR__ . '/../partials/navbar_admin.php';
 } else {
     include __DIR__ . '/../partials/navbar_student.php';
 }
 ?>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-4">
 <h1 class="h3"><?php echo htmlspecialchars($pageTitle); ?></h1>
 <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="ProjectDebug.openProjectModal()">
 <i class="bi bi-plus"></i> New Project
 </button>
 </div>

 <div id="projectsList" class="row g-4">
 <div class="col-12 text-center py-5">
 <div class="spinner-border text-primary" role="status">
 <span class="visually-hidden">Loading...</span>
 </div>
 </div>
 </div>
 </main>

 <!-- Project Modal -->
 <div class="modal fade" id="projectModal" tabindex="-1">
 <div class="modal-dialog modal-lg">
 <div class="modal-content">
 <div class="modal-header">
 <h5 class="modal-title" id="projectModalTitle">New Project</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body">
 <form id="projectForm">
 <input type="hidden" id="projectId">
 <div class="mb-3">
 <label for="projectName" class="form-label">Project Name</label>
 <input type="text" class="form-control" id="projectName" required>
 </div>
 <div class="mb-3">
 <label for="projectDesc" class="form-label">Description</label>
 <textarea class="form-control" id="projectDesc" rows="3"></textarea>
 </div>
 <div class="row">
 <div class="col-md-6 mb-3">
 <label for="projectStatus" class="form-label">Status</label>
 <select class="form-select" id="projectStatus">
 <option value="not_started">Not Started</option>
 <option value="in_progress">In Progress</option>
 <option value="completed">Completed</option>
 <option value="on_hold">On Hold</option>
 </select>
 </div>
 <div class="col-md-6 mb-3">
 <label for="projectDueDate" class="form-label">Due Date</label>
 <input type="date" class="form-control" id="projectDueDate">
 </div>
 </div>
 </form>
 </div>
 <div class="modal-footer">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
 <button type="button" class="btn btn-primary" onclick="ProjectDebug.saveProject()">Save Project</button>
 </div>
 </div>
 </div>
 </div>

 <!-- Task Modal -->
 <div class="modal fade" id="taskModal" tabindex="-1">
 <div class="modal-dialog">
 <div class="modal-content">
 <div class="modal-header">
 <h5 class="modal-title" id="taskModalTitle">New Task</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body">
 <form id="taskForm">
 <input type="hidden" id="taskProjectId">
 <input type="hidden" id="taskId">
 <div class="mb-3">
 <label for="taskName" class="form-label">Task Name</label>
 <input type="text" class="form-control" id="taskName" required>
 </div>
 <div class="mb-3">
 <label for="taskDesc" class="form-label">Description</label>
 <textarea class="form-control" id="taskDesc" rows="2"></textarea>
 </div>
 <div class="row">
 <div class="col-md-6 mb-3">
 <label for="taskPriority" class="form-label">Priority</label>
 <select class="form-select" id="taskPriority">
 <option value="low">Low</option>
 <option value="medium" selected>Medium</option>
 <option value="high">High</option>
 </select>
 </div>
 <div class="col-md-6 mb-3">
 <label for="taskDueDate" class="form-label">Due Date</label>
 <input type="date" class="form-control" id="taskDueDate">
 </div>
 </div>
 </form>
 </div>
 <div class="modal-footer">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
 <button type="button" class="btn btn-primary" onclick="ProjectDebug.saveTask()">Save Task</button>
 </div>
 </div>
 </div>
 </div>

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
 <script src="assets/js/auth.js"></script>
</body>
</html>

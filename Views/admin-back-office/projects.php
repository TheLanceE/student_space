<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Projects | EduMind+</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link href="../../shared-assets/css/projects.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
 /* Hide edit button for admins - DELETE ONLY */
 .btn-edit { display: none !important; }
 #projectModal { display: none !important; }
 </style>
</head>
<body data-page="admin-projects">
 <nav class="navbar navbar-expand-lg navbar-dark admin-nav">
 <div class="container-fluid">
 <a class="navbar-brand" href="dashboard.php"><i class="bi bi-shield-check"></i> EduMind+ Admin</a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php"><i class="bi bi-person-badge me-1"></i>Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php"><i class="bi bi-journal-text me-1"></i>Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
 </ul>
 <div class="d-flex"><a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></div>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-4">
 <h1 class="h3">All Projects (View & Delete)</h1>
 </div>

 <div id="projectsList" class="row g-4">
 <div class="col-12 text-center py-5">
 <div class="spinner-border text-danger" role="status">
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
 <script src="assets/js/auth-admin.js"></script>
</body>
</html>

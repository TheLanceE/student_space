<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Projects | EduMind+</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/projects.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <style>
 /* Hide edit button for admins - DELETE ONLY */
 .btn-edit { display: none !important; }
 #projectModal { display: none !important; }
 </style>
</head>
<body data-page="admin-projects">
 <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
 <div class="container-fluid">
 <a class="navbar-brand" href="#">EduMind+ Admin</a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="projects.php">Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php">Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php">Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
 </ul>
 <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
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

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Logs | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
</head>
<body data-page="admin-logs">
 <nav class="navbar navbar-expand-lg navbar-dark admin-nav">
 <div class="container-fluid">
 <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
  <i class="bi bi-shield-lock me-2"></i>EduMind+ Admin
 </a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php"><i class="bi bi-kanban me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php"><i class="bi bi-person-badge me-1"></i>Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php"><i class="bi bi-journal-check me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-clipboard-data me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="logs.php"><i class="bi bi-file-text me-1"></i>Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-graph-up me-1"></i>Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
 </ul>
 <a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h1 class="h5">System Logs</h1>
 <div id="logTable"></div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <!-- Deprecated client-side admin data scripts removed for consistency -->
</body>
</html>
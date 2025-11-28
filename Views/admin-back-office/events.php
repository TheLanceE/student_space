<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>EduMind+ | Events Administration</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-events" class="bg-light">
 <nav class="navbar navbar-expand-lg navbar-dark admin-nav">
 <div class="container-fluid">
 <a class="navbar-brand" href="dashboard.php"><i class="bi bi-shield-check"></i> EduMind+ Admin</a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php">Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php">Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
 </ul>
 <button id="logoutBtn" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
 </div>
 </div>
 </nav>

 <div class="container my-5">
 <div class="card shadow-sm">
 <div class="card-header d-flex justify-content-between align-items-center">
 <h1 class="h5 mb-0">All Events</h1>
 </div>
 <div class="card-body">
 <div id="eventsList"></div>
 </div>
 </div>
 </div>

 <script src="../../shared-assets/vendor/bootstrap.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/auth-admin.js"></script>
 <script src="assets/js/data-admin.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>

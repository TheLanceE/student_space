<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Settings | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="admin-settings">
 <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
 <div class="container-fluid">
 <a class="navbar-brand" href="#">EduMind+ Admin</a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php">Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php">Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php">Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="settings.php">Settings</a></li>
 </ul>
 <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h1 class="h5">Platform Settings (Demo)</h1>
 <form id="settingsForm" class="mt-3">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="form-label">Suggestion Engine: Inactivity Days</label>
 <input id="inactDays" type="number" min="1" value="7" class="form-control" />
 </div>
 <div class="col-md-6">
 <label class="form-label">Report Export Prefix</label>
 <input id="exportPrefix" type="text" value="edumind" class="form-control" />
 </div>
 </div>
 <div class="mt-3">
 <button class="btn btn-primary" type="submit">Save</button>
 </div>
 </form>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/data-admin.js"></script>
 <script src="assets/js/auth-admin.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Quiz Reports | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-quiz-reports">
 <nav class="navbar navbar-expand-lg navbar-dark teacher-nav">
 <div class="container-fluid">
 <a class="navbar-brand" href="dashboard.php"><i class="bi bi-mortarboard-fill"></i> EduMind+ Teacher</a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="students.php"><i class="bi bi-people me-1"></i>Students</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-builder.php"><i class="bi bi-pen me-1"></i>Quiz Builder</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 </ul>
 <button id="logoutBtn" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-header d-flex justify-content-between align-items-center">
 <h1 class="h5 mb-0">📝 Student Quiz Reports</h1>
 <span class="badge bg-warning text-dark" id="pendingCount">0 pending</span>
 </div>
 <div class="card-body">
 <div class="mb-3">
 <label class="form-label small">Filter by status:</label>
 <div class="btn-group btn-group-sm" role="group">
 <input type="radio" class="btn-check" name="statusFilter" id="filterAll" value="all" checked>
 <label class="btn btn-outline-primary" for="filterAll">All</label>
 <input type="radio" class="btn-check" name="statusFilter" id="filterPending" value="pending">
 <label class="btn btn-outline-warning" for="filterPending">Pending</label>
 <input type="radio" class="btn-check" name="statusFilter" id="filterReviewed" value="reviewed">
 <label class="btn btn-outline-info" for="filterReviewed">Reviewed</label>
 <input type="radio" class="btn-check" name="statusFilter" id="filterResolved" value="resolved">
 <label class="btn btn-outline-success" for="filterResolved">Resolved</label>
 </div>
 </div>
 <div id="reportsList"></div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/auth-teacher.js"></script>
 <script src="assets/js/data-teacher.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>




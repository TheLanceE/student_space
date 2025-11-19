<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Teacher Dashboard | EduMind+</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="teacher-dashboard">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Teacher</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>          <li class="nav-item"><a class="nav-link" href="projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
          <li class="nav-item"><a class="nav-link" href="quiz-reports.php">Quiz Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
        </ul>
        <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="row g-4">
      <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h5 mb-0">Average Scores by Course</h2><a href="courses.php" class="btn btn-sm btn-outline-primary">Manage Courses</a></div>
            <canvas id="courseAverages" height="120"></canvas>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h5 mb-0">Activity (Attempts / Day)</h2><a href="students.php" class="btn btn-sm btn-outline-secondary">View Students</a></div>
            <canvas id="attemptsChart" height="120"></canvas>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../shared-assets/vendor/chart.umd.min.js"></script>
  <script src="../../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth-teacher.js"></script>
  <script src="assets/js/data-teacher.js"></script>
  <script src="assets/js/charts-teacher.js"></script>
  <script src="assets/js/ui-teacher.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>



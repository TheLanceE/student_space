<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Courses | Teacher</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="teacher-courses">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Teacher</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>          <li class="nav-item"><a class="nav-link" href="projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link active" href="courses.php">Courses</a></li>
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
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h6">Add Course</h2>
            <form id="addCourseForm">
              <div class="mb-2"><label class="form-label">Title</label><input class="form-control" id="cTitle" required></div>
              <div class="mb-2"><label class="form-label">Description</label><textarea class="form-control" id="cDesc" rows="3"></textarea></div>
              <button class="btn btn-primary btn-sm" type="submit">Add</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h6">Your Courses</h2>
            <div class="row g-3" id="courseList"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth-teacher.js"></script>
  <script src="assets/js/data-teacher.js"></script>
  <script src="assets/js/ui-teacher.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>



<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Student Dashboard</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="front-dashboard">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample07"
        aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarsExample07">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="row g-4">
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h2 class="h5 mb-0">📊 My Performance</h2>
              <div class="d-flex gap-2">
                <a href="courses.php" class="btn btn-sm btn-primary">Take Quiz</a>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#scoresModal">View Scores</button>
              </div>
            </div>
            <canvas id="progressChart" height="100"></canvas>
          </div>
        </div>
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h5 mb-3">📅 Upcoming Events</h2>
            <div id="upcomingEvents"></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
          <div class="card-body text-center py-4">
            <img src="../shared-assets/img/student-portal.jpg" alt="Student" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid white;">
            <h3 class="h5 mb-1" id="studentName">Loading...</h3>
            <p class="mb-0 small opacity-75" id="studentGrade">Grade N/A</p>
          </div>
        </div>
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h5 mb-3">💡 Continue Learning</h2>
            <p class="text-muted small">Personalized suggestions</p>
            <ul id="suggestionsList" class="list-group list-group-flush"></ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h2 class="h5 mb-0">Recent Results</h2>
              <a href="courses.php" class="btn btn-sm btn-outline-secondary">Take a Quiz</a>
            </div>
            <div id="recentResults" class="table-responsive"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="scoresModal" tabindex="-1" aria-hidden="true" aria-labelledby="scoresModalLabel">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="scoresModalLabel">📈 My Scores by Subject</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Average scores based on your quiz performance.</p>
          <ul class="list-group" id="scoresList"></ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../shared-assets/vendor/chart.umd.min.js"></script>
  <script src="../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth.js"></script>
  <script src="assets/js/data.js"></script>
  <script src="assets/js/suggestionEngine.js"></script>
  <script src="assets/js/charts.js"></script>
  <script src="assets/js/ui.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>
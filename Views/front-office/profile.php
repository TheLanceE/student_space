<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Profile</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="front-profile">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample07"
        aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarsExample07">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>          <li class="nav-item"><a class="nav-link" href="projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="profile.php">Profile</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 mb-3">Student Profile</h2>
            <dl class="row mb-0" id="profileInfo"></dl>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h2 class="h5 mb-0">Score History</h2>
            </div>
            <canvas id="historyChart" height="120"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h2 class="h5 mb-0">All Results</h2>
            </div>
            <div id="allResults" class="table-responsive"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../shared-assets/vendor/chart.umd.min.js"></script>
  <script src="../../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth.js"></script>
  <script src="assets/js/data.js"></script>
  <script src="assets/js/charts.js"></script>
  <script src="assets/js/ui.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>



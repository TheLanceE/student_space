<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Events Administration</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="admin-events" class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="dashboard.php">EduMind+ Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link active" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="quiz-reports.php">Quiz Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
          <li class="nav-item"><button class="btn btn-link nav-link text-warning" id="logoutBtn">Logout</button></li>
        </ul>
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

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Quiz Reports | Admin</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="admin-quiz-reports">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="roles.php">Roles</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link active" href="quiz-reports.php">Quiz Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
        </ul>
        <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h1 class="h5 mb-0">📝 All Quiz Reports</h1>
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
  <script src="assets/js/auth-admin.js"></script>
  <script src="assets/js/data-admin.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>

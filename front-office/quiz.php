<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Quiz</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="front-quiz">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample07"
        aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarsExample07">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 id="quizTitle" class="h4 mb-0">Quiz</h1>
      <div>
        <span class="badge bg-dark badge-timer">Time left: <span id="timeLeft">--</span>s</span>
      </div>
    </div>

    <form id="quizForm" class="card shadow-sm">
      <div id="questions" class="card-body"></div>
      <div class="card-footer d-flex justify-content-between">
        <a class="btn btn-outline-secondary" href="courses.php">Back</a>
        <button id="submitBtn" type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>

    <div id="resultPanel" class="mt-4" style="display:none;"></div>
  </main>

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth.js"></script>
  <script src="assets/js/data.js"></script>
  <script src="assets/js/suggestionEngine.js"></script>
  <script src="assets/js/ui.js"></script>
  <script src="assets/js/quiz.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>
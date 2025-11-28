<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>EduMind+ | Courses</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="front-courses">
<?php 
// Check authentication
require_once '../../Controllers/auth_check.php';
?>
 <nav class="navbar navbar-expand-lg navbar-dark student-nav">
 <div class="container-fluid">
 <a class="navbar-brand" href="dashboard.php">
 	<i class="bi bi-mortarboard-fill"></i>
 	EduMind+
 </a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample07"
 aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="navbarsExample07">
 <ul class="navbar-nav me-auto mb-2 mb-lg-0">
 <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door-fill me-1"></i>Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz.php"><i class="bi bi-question-circle me-1"></i>Quiz</a></li>
 <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-1"></i>Profile</a></li>
 </ul>
 <div class="d-flex align-items-center gap-3">
 <span class="text-white welcome-text">
 	<i class="bi bi-person-badge"></i>
 	<?php echo htmlspecialchars($_SESSION['username']); ?>
 </span>
 <a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm">
 	<i class="bi bi-box-arrow-right me-1"></i>Logout
 </a>
 </div>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h1 class="h4 mb-0">Courses</h1>
 <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
 </div>
 <div id="courseList" class="row g-4"></div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="assets/js/auth.js"></script>
 <script src="assets/js/data.js"></script>
 <script src="assets/js/ui.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>



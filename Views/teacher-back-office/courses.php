<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Courses | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-courses">
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
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="students.php"><i class="bi bi-people me-1"></i>Students</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-builder.php"><i class="bi bi-pen me-1"></i>Quiz Builder</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 </ul>
 <a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
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
</body>
</html>



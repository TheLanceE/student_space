<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Quiz Builder | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-quiz-builder">
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
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="quiz-builder.php"><i class="bi bi-pen me-1"></i>Quiz Builder</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 </ul>
 <button id="logoutBtn" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <h1 class="h4 mb-3">Quiz Builder</h1>
 <form id="quizForm" class="card shadow-sm">
 <div class="card-body">
 <div class="row g-3">
 <div class="col-md-4">
 <label class="form-label">Course</label>
 <select id="courseId" class="form-select"></select>
 </div>
 <div class="col-md-5">
 <label class="form-label">Quiz Title</label>
 <input id="quizTitle" class="form-control" placeholder="e.g., Math Basics - Quiz 2" required />
 </div>
 <div class="col-md-3">
 <label class="form-label">Duration (sec)</label>
 <input id="duration" type="number" min="30" step="10" value="60" class="form-control" />
 </div>
 </div>
 <hr/>
 <div id="questions"></div>
 <button id="addQuestion" type="button" class="btn btn-outline-primary btn-sm mt-2">Add Question</button>
 </div>
 <div class="card-footer d-flex justify-content-end gap-2">
 <a href="courses.php" class="btn btn-outline-secondary">Back</a>
 <button class="btn btn-primary" type="submit">Save Quiz</button>
 </div>
 </form>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/auth-teacher.js"></script>
 <script src="assets/js/data-teacher.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>



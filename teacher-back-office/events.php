<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Events Management</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="teacher-events" class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="dashboard.php">EduMind+ Teacher</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link active" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
          <li class="nav-item"><button class="btn btn-link nav-link text-warning" id="logoutBtn">Logout</button></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container my-5">
    <div class="card mb-4 shadow-sm">
      <div class="card-header">
        <h1 class="h5 mb-0">Add New Event</h1>
      </div>
      <div class="card-body">
        <form id="addEventForm">
          <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="date" class="form-label">Event Date</label>
              <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="col-md-4">
              <label for="startTime" class="form-label">Start Time</label>
              <input type="time" class="form-control" id="startTime" name="startTime" required>
            </div>
            <div class="col-md-4">
              <label for="endTime" class="form-label">End Time</label>
              <input type="time" class="form-control" id="endTime" name="endTime" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="course" class="form-label">Course/Subject</label>
            <input type="text" class="form-control" id="course" name="course" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="type" class="form-label">Event Type</label>
              <select class="form-select" id="type" name="type" required>
                <option value="Lecture">Lecture</option>
                <option value="Quiz">Quiz</option>
                <option value="Webinar">Webinar</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="maxParticipants" class="form-label">Max Participants</label>
              <input type="number" class="form-control" id="maxParticipants" name="maxParticipants" value="30" min="1" required>
            </div>
          </div>

          <div class="mb-3" id="locationWrapper">
            <label for="location" class="form-label">Event Location</label>
            <input type="text" class="form-control" id="location" name="location">
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>

          <button type="submit" class="btn btn-success">Create Event</button>
        </form>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">My Events</h2>
      </div>
      <div class="card-body">
        <div id="eventsList"></div>
      </div>
    </div>
  </div>

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth-teacher.js"></script>
  <script src="assets/js/data-teacher.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>

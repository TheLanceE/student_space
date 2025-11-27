<?php
  require_once "../../../Controller/Events/eventsConfig.php";
  require_once "../../../Controller/Events/eventsController.php";
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Event Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<body class="bg-light p-4">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-mortarboard-fill"></i> EduMind</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="#"><i class="bi bi-house-door-fill"></i> Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-person-circle"></i> Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-warning" href="#"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

  <div class="card mb-4 shadow-sm">
    <div class="card-header">
      <span><i class="bi bi-plus-circle"></i> Add New Event</span>
    </div>
    <div class="card-body">
      <form id="addEventForm" method="POST" action="../../../Controller/Events/eventsController.php">
        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter event title" >
            <span id="errTitle"></span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label for="date" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="date" name="date">
            <span id="errDate"></span>
          </div>
          <div class="col-md-4">
            <label for="startTime" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="startTime" name="startTime">
            <span id="errStartTime"></span>
        </div>
          <div class="col-md-4">
            <label for="endTime" class="form-label">End Time</label>
            <input type="time" class="form-control" id="endTime" name="endTime">
            <span id="errEndTime"></span>
        </div>
        </div>
            <span id="errTimeDifference"></span>

        <div class="mb-3">
          <label for="course" class="form-label">Course/Subject</label>
          <input type="text" class="form-control" id="course" name="course" placeholder="Course name">
          <span id="errCourse"></span>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="type" class="form-label">Event Type</label>
            <select class="form-select" id="type" name="type">
              <option value="Lecture">Lecture</option>
              <option value="Quiz">Quiz</option>
              <option value="Webinar">Webinar</option>
              <option value="Other">Other</option>
            </select>
            <span id="errType"></span>
          </div>
     <div class="col-md-6">
            <label for="recurring" class="form-label">Recurring</label>
            <select class="form-select" id="recurring" name="recurring">
              <option value="None">None</option>
              <option value="Daily">Daily</option>
              <option value="Weekly">Weekly</option>
              <option value="Monthly">Monthly</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label for="location" id="locationLabel" class="form-label" style="display: block;">Event Location</label>
          <input type="text" class="form-control" id="location" name="location" style="display: block;" placeholder="Enter Event Location">
            <span id="errLocation"></span>
        </div>


        <div class="mb-3">
          <label for="maxParticipants" class="form-label">Max Participants</label>
          <input type="number" class="form-control" id="maxParticipants" name="maxParticipants">
          <span id="errParticipants"></span>
        </div>

        <div class="mb-3">
          <label for="links" class="form-label">Attachments / Links</label>
          <input type="text" class="form-control" id="links" name="links" placeholder="https://zoom.us/... , https://example.com">
        </div>

        <div class="mb-3">
          <label for="desc" class="form-label">Description</label>
          <textarea class="form-control" id="desc" name="desc" rows="3" placeholder="Event description"></textarea>
            <span id="errDesc"></span>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Event</button>
      </form>
    </div>
  </div>

  <h2 class="mb-3">Your Events</h2>
<style>
  details {
    cursor: pointer;
  }
</style>

<div class="container mt-4">
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Title</th>
          <th>Date</th>
          <th>Start</th>
          <th>End</th>
          <th>Max</th>
          <th>Participants</th>
          <th>Course</th>
          <th>Type</th>
          <th>Location</th>
          <th>Description</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $teacherID = 1;
          $events = getAllTeacherEvents($pdo, $teacherID);

          foreach($events as $event){
            echo "<tr>";

            echo "<td>{$event['title']}</td>";
            echo "<td>{$event['date']}</td>";
            echo "<td>{$event['startTime']}</td>";
            echo "<td>{$event['endTime']}</td>";
            echo "<td>{$event['maxParticipants']}</td>";
            echo "<td>{$event['nbrParticipants']}</td>";
            echo "<td>{$event['course']}</td>";
            echo "<td>{$event['type']}</td>";

            echo "<td><details><summary>Show</summary>" . htmlspecialchars($event['location']) . "</details></td>";
            echo "<td><details><summary>Show</summary>" . htmlspecialchars($event['description']) . "</details></td>";

            echo "<td class='text-center'>
                    <form method='post' action='../../../Controller/Events/eventsController.php'>
                      <input type='hidden' name='deleteID' value='{$event['eventID']}'>
                      <button class='btn btn-danger btn-sm'>
                        <i class='bi bi-trash'></i>
                      </button>
                    </form>
                  </td>";

            echo "</tr>";
          }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script src="assets/JavaScript/events.js"></script>

</body>
</html>

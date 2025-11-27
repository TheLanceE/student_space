<?php
 require_once "../../../Controller/Events/eventsConfig.php";
  require_once "../../../Controller/Events/eventsController.php";
?>









<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Events</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { background: #f8f9fa; }
  .event-card { transition: transform 0.3s, box-shadow 0.3s; border-radius: 12px; }
  .event-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.12); }
  .card-title { font-weight: 700; }
  .card-header-gradient { 
    background: linear-gradient(90deg, #4f46e5, #3b82f6);
    color: #fff; padding: 0.5rem 1rem; border-radius: 12px 12px 0 0; 
    font-weight: 600; display:flex; justify-content:space-between; align-items:center;
  }
  .register-btn { transition: 0.2s; }
  .register-btn:hover { transform: scale(1.05); }
</style>
</head>
<body class="p-4">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"></i> EduMind</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Courses</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Profile</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

  <!-- Filters
  <div class="row mb-4 g-2">
    <div class="col-md-4">
      <input type="text" id="searchInput" class="form-control" placeholder="Search by title or course">
    </div>
    <div class="col-md-3">
      <select id="typeFilter" class="form-select">
        <option value="">All Types</option>
        <option value="Lecture">Lecture</option>
        <option value="Quiz">Quiz</option>
        <option value="Webinar">Webinar</option>
        <option value="Other">Other</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="date" id="dateFilter" class="form-control">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-secondary w-100" id="resetFilters"><i class="bi bi-x-circle"></i> Reset</button>
    </div>
  </div>

  <div id="eventsList" class="row g-4"></div>
</div> -->

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
          $events = getAllEvents($pdo);

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
                      <input type='hidden' name='eventID' value='{$event['eventID']}'>
                       <input type='hidden' name='studentID' value='10'>
                    <button class='btn btn-success btn-sm'>
                        <i class='bi bi-plus-circle'></i> Join
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





</body>
</html>

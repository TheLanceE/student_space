<?php
  require_once "../../../Controller/Events/eventsConfig.php";
  require_once "../../../Controller/Events/eventsController.php";
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Event Panel</title>
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


      

  <h2 class="mb-3">Events</h2>
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
                      <input type='hidden' name='deleteID' value='{$event['eventID']}'>
                      <input type='hidden' name='admin' value='adminID'>
                        <button class='btn btn-danger btn-sm p-0' style='width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;'>
                          X
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

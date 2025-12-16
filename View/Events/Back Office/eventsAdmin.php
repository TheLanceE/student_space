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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { background: #f4f6f9; }
  table.table { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  table th, table td { vertical-align: middle; }
  details summary { cursor: pointer; font-weight: 500; }
  .chart-card { transition: 0.3s; }
  .chart-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
</style>
</head>
<body class="p-4">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-mortarboard-fill"></i> EduMind</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-house-door-fill"></i> Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-circle"></i> Profile</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="#"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

  <h2 class="mb-4 text-primary"><i class="bi bi-calendar-event-fill"></i> Events</h2>

  <!-- Table -->
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-hover align-middle shadow-sm">
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
          <th class="text-center"><i class="bi bi-tools"></i></th>
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
                      <button class='btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center' style='width:32px;height:32px;'>
                        <i class='bi bi-x-lg'></i>
                      </button>
                    </form>
                  </td>";
            echo "</tr>";
          }
        ?>
      </tbody>
    </table>
  </div>

  <?php
  $types = []; $participants = []; $eventTitles = [];
  foreach ($events as $event) {
      $types[] = $event['type'];
      $participants[] = $event['nbrParticipants'];
      $eventTitles[] = addslashes($event['title']);
  }
  $typeCounts = array_count_values($types);
  $typeLabels = json_encode(array_keys($typeCounts));
  $typeData = json_encode(array_values($typeCounts));
  $participantsLabels = json_encode($eventTitles);
  $participantsData = json_encode($participants);
  ?>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card shadow-sm chart-card">
        <div class="card-header bg-primary text-white">
          <i class="bi bi-pie-chart-fill"></i> Events per Type
        </div>
        <div class="card-body d-flex justify-content-center">
          <div style="max-width:500px; max-height:500px; width:100%;">
            <canvas id="eventsTypeChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card shadow-sm chart-card">
        <div class="card-header bg-primary text-white">
          <i class="bi bi-bar-chart-fill"></i> Participants per Event
        </div>
        <div class="card-body">
          <canvas id="participantsChart"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/JavaScript/events.js"></script>
<script>
const ctxType = document.getElementById('eventsTypeChart').getContext('2d');
new Chart(ctxType, {
    type: 'doughnut',
    data: {
        labels: <?= $typeLabels ?>,
        datasets: [{
            label: 'Number of Events',
            data: <?= $typeData ?>,
            backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6c757d'],
            borderWidth: 1
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio: false,
        plugins:{legend:{position:'bottom'}}
    }
});

const ctxParticipants = document.getElementById('participantsChart').getContext('2d');
new Chart(ctxParticipants, {
    type: 'bar',
    data: {
        labels: <?= $participantsLabels ?>,
        datasets: [{
            label: 'Participants',
            data: <?= $participantsData ?>,
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        responsive: true,
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true}}
    }
});
</script>

</body>
</html>

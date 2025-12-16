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
  body { background: #f0f2f5; }
  .event-card { transition: .25s; border-radius: 14px; overflow:hidden; }
  .event-card:hover { transform: translateY(-6px); box-shadow:0 12px 24px rgba(0,0,0,0.12); }
  .card-header-gradient { background: linear-gradient(90deg,#4f46e5,#3b82f6); color:#fff; padding:.75rem 1rem; font-weight:600; }
  details { cursor:pointer; }
  .register-btn { transition:.15s; }
  .register-btn:hover { transform:scale(1.05); }
</style>
</head>
<body class="p-4">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">EduMind</a>
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

<div class="container mt-3">
  <form method="get" class="mb-4" method='post' action='../../../Controller/Events/eventsController.php'>
  <div class="input-group">
    <span class="input-group-text"><i class="bi bi-search"></i></span>
    <input type="text" name="q" class="form-control" placeholder="Search events..." name='eventSearchBox'>
  </div>
</form>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">


<?php
$events = getAllEvents($pdo);
$userID = 899; //////////////////////////////////////////////////////////////   you should be able to just plug the userID here 
?>

<?php foreach($events as $event): ?>
  <div class="col">
    <div class="card shadow-sm event-card h-100">

      <div class="card-header-gradient d-flex justify-content-between">
        <span><?= $event['title'] ?></span>
        <i class="bi bi-calendar-week"></i>
      </div>

      <div class="card-body p-3">
        <p class="small mb-1"><i class="bi bi-calendar-event"></i> <strong><?= $event['date'] ?></strong></p>
        <p class="small mb-1"><i class="bi bi-clock"></i> <?= $event['startTime'] ?> - <?= $event['endTime'] ?></p>
        <p class="small mb-1"><i class="bi bi-people"></i> <?= $event['nbrParticipants'] ?>/<?= $event['maxParticipants'] ?></p>
        <p class="small mb-1"><i class="bi bi-journal-bookmark"></i> <?= $event['course'] ?></p>
        <p class="small mb-2"><i class="bi bi-tag"></i> <?= $event['type'] ?></p>

<?php 
      if (!empty($event['location']))
      { ?>
            <details class="small mb-2">
                <summary class="fw-semibold"><i class="bi bi-geo-alt"></i> Location</summary>
                <div class="ps-3"><?= htmlspecialchars($event['location']) ?></div>
            </details>
<?php
      }
?>
        <details class="small mb-3">
          <summary class="fw-semibold"><i class="bi bi-info-circle"></i> Description</summary>
          <div class="ps-3"><?= htmlspecialchars($event['description']) ?></div>
        </details>
<?php
      if(empty($event['location']))
      {
        echo '<br>';
      }
?>

        <form method="post" action="../../../Controller/Events/eventsController.php">
          <input type="hidden" name="eventID" value="<?= $event['eventID'] ?>">
          <input type="hidden" name="studentID" value="<?= $userID ?>">

<?php if(isUserInEvent($pdo,$userID,$event['eventID'])): ?>
          <input type="hidden" name="leave" value="1">
          <button class="btn btn-danger w-100 register-btn"><i class="bi bi-dash-circle"></i> Leave</button>
          <br><br>
          <details class="small mb-3">
              <summary class="fw-semibold"><i class="bi bi-geo-alt"></i> Comment</summary>
              <?= GetParticipationComment($pdo, $userID, $event['eventID']) ?>
          </details>
<?php else: ?>
  <?php if($event['nbrParticipants'] >= $event['maxParticipants']): ?>
          <button class="btn btn-secondary w-100 register-btn" disabled><i class="bi bi-x-circle"></i> Full</button>
  <?php else: ?>
          <button class="btn btn-success w-100 register-btn"><i class="bi bi-plus-circle"></i> Join</button>
          <br><br>
          <input type="text" class="form-control" id="participationComment" name="participationComment" placeholder="Enter comment">
  <?php endif; ?>
<?php endif; ?>

        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

  </div>
  <nav class="mt-4">
  <ul class="pagination justify-content-center" id="pagination"></ul>
</nav>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script> //i have no idea how this script works
document.addEventListener('DOMContentLoaded', () => {
  const cards = [...document.querySelectorAll('.row > .col')];
  const perPage = 6;
  let page = 1;

  const pager = document.getElementById('pagination');
  const searchInput = document.querySelector('input[name="q"]');

  function getVisibleCards() {
    return cards.filter(c => c.style.display !== 'none' || !c.dataset.hidden);
  }

  function renderPagination(visibleCards) {
    pager.innerHTML = '';
    const totalPages = Math.ceil(visibleCards.length / perPage) || 1;
    for (let i = 1; i <= totalPages; i++) {
      pager.innerHTML += `
        <li class="page-item ${i === page ? 'active' : ''}">
          <a class="page-link" href="#">${i}</a>
        </li>`;
    }
  }

  function showPage(visibleCards) {
    visibleCards.forEach((c, i) => {
      c.style.display = (i >= (page - 1) * perPage && i < page * perPage) ? '' : 'none';
    });
  }

  function update() {
    const q = searchInput.value.toLowerCase().trim();

    cards.forEach(c => {
      const text = c.textContent.toLowerCase();
      c.dataset.hidden = (!text.includes(q) && q) ? '1' : '0';
      c.style.display = c.dataset.hidden === '1' ? 'none' : '';
    });

    const visibleCards = cards.filter(c => c.dataset.hidden !== '1');
    page = 1;
    renderPagination(visibleCards);
    showPage(visibleCards);
  }

  pager.addEventListener('click', e => {
    if (!e.target.classList.contains('page-link')) return;
    e.preventDefault();
    page = +e.target.textContent;
    const visibleCards = cards.filter(c => c.dataset.hidden !== '1');
    showPage(visibleCards);


    [...pager.children].forEach(li => li.classList.remove('active'));
    e.target.parentElement.classList.add('active');
  });

  searchInput.addEventListener('input', update);

  update(); // initial render
});
</script>



</body>
</html>

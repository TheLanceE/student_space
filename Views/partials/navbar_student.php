<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$username = (string)($_SESSION['user']['username'] ?? $_SESSION['username'] ?? $_SESSION['google_name'] ?? 'Student');
?>
<nav class="navbar navbar-expand-lg navbar-dark student-nav">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-mortarboard-fill"></i> EduMind+</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" <?= $currentPage === 'dashboard.php' ? 'aria-current="page"' : '' ?> href="dashboard.php"><i class="bi bi-house-door-fill me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'projects.php' ? 'active' : '' ?>" <?= $currentPage === 'projects.php' ? 'aria-current="page"' : '' ?> href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'courses.php' ? 'active' : '' ?>" <?= $currentPage === 'courses.php' ? 'aria-current="page"' : '' ?> href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'quiz.php' ? 'active' : '' ?>" <?= $currentPage === 'quiz.php' ? 'aria-current="page"' : '' ?> href="quiz.php"><i class="bi bi-question-circle me-1"></i>Quiz</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'challenges.php' ? 'active' : '' ?>" <?= $currentPage === 'challenges.php' ? 'aria-current="page"' : '' ?> href="challenges.php"><i class="bi bi-trophy me-1"></i>Challenges</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'rewards.php' ? 'active' : '' ?>" <?= $currentPage === 'rewards.php' ? 'aria-current="page"' : '' ?> href="rewards.php"><i class="bi bi-gift me-1"></i>Rewards</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'ai.php' ? 'active' : '' ?>" <?= $currentPage === 'ai.php' ? 'aria-current="page"' : '' ?> href="ai.php"><i class="bi bi-cpu me-1"></i>AI</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" <?= $currentPage === 'profile.php' ? 'aria-current="page"' : '' ?> href="profile.php"><i class="bi bi-person-circle me-1"></i>Profile</a></li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <span class="text-white welcome-text"><i class="bi bi-person-badge"></i> <?= htmlspecialchars($username) ?></span>
        <?php if (isset($balance)): ?>
          <span class="badge bg-dark"><?= (int)$balance ?> pts</span>
        <?php endif; ?>
        <a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </div>
    </div>
  </div>
</nav>

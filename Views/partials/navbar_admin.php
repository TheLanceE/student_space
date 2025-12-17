<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$username = (string)($_SESSION['user']['username'] ?? $_SESSION['username'] ?? $_SESSION['google_name'] ?? 'Admin');
?>
<nav class="navbar navbar-expand-lg navbar-dark admin-nav">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-shield-check"></i> EduMind+ Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" <?= $currentPage === 'dashboard.php' ? 'aria-current="page"' : '' ?> href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'projects.php' ? 'active' : '' ?>" <?= $currentPage === 'projects.php' ? 'aria-current="page"' : '' ?> href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>" <?= $currentPage === 'users.php' ? 'aria-current="page"' : '' ?> href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'roles.php' ? 'active' : '' ?>" <?= $currentPage === 'roles.php' ? 'aria-current="page"' : '' ?> href="roles.php"><i class="bi bi-person-badge me-1"></i>Roles</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'courses.php' ? 'active' : '' ?>" <?= $currentPage === 'courses.php' ? 'aria-current="page"' : '' ?> href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'events.php' ? 'active' : '' ?>" <?= $currentPage === 'events.php' ? 'aria-current="page"' : '' ?> href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'quizzes.php' ? 'active' : '' ?>" <?= $currentPage === 'quizzes.php' ? 'aria-current="page"' : '' ?> href="quizzes.php"><i class="bi bi-question-circle me-1"></i>Quizzes</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'quiz-reports.php' ? 'active' : '' ?>" <?= $currentPage === 'quiz-reports.php' ? 'aria-current="page"' : '' ?> href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'challenges.php' ? 'active' : '' ?>" <?= $currentPage === 'challenges.php' ? 'aria-current="page"' : '' ?> href="challenges.php"><i class="bi bi-trophy me-1"></i>Challenges</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'rewards.php' ? 'active' : '' ?>" <?= $currentPage === 'rewards.php' ? 'aria-current="page"' : '' ?> href="rewards.php"><i class="bi bi-gift me-1"></i>Rewards</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'ai.php' ? 'active' : '' ?>" <?= $currentPage === 'ai.php' ? 'aria-current="page"' : '' ?> href="ai.php"><i class="bi bi-cpu me-1"></i>AI</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'logs.php' ? 'active' : '' ?>" <?= $currentPage === 'logs.php' ? 'aria-current="page"' : '' ?> href="logs.php"><i class="bi bi-journal-text me-1"></i>Logs</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>" <?= $currentPage === 'reports.php' ? 'aria-current="page"' : '' ?> href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>" <?= $currentPage === 'settings.php' ? 'aria-current="page"' : '' ?> href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <span class="text-white welcome-text"><i class="bi bi-person-badge"></i> <?= htmlspecialchars($username) ?></span>
        <a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </div>
    </div>
  </div>
</nav>

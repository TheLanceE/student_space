<?php
require_once __DIR__ . '/../../Controllers/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
	header('Location: login.php?error=unauthorized');
	exit;
}

$teacherId = $_SESSION['user_id'] ?? null;
$students = [];
$summary = ['count' => 0, 'attempts' => 0, 'avg_pct' => null];

if ($teacherId) {
	try {
		// Students who have quiz scores on this teacher's courses
		$sql = "SELECT s.id, s.username, s.fullName, s.email, s.gradeLevel, s.lastLoginAt,
									 COUNT(sc.id) AS attempts,
									 ROUND(AVG((sc.score / NULLIF(sc.total,0)) * 100),0) AS avg_pct
						FROM students s
						JOIN scores sc ON sc.userId = s.id
						JOIN courses c ON c.id = sc.courseId
						WHERE c.teacherId = ? AND (s.deleted_at IS NULL OR s.deleted_at = '0000-00-00 00:00:00')
						GROUP BY s.id, s.username, s.fullName, s.email, s.gradeLevel, s.lastLoginAt
						ORDER BY s.fullName ASC";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute([$teacherId]);
		$students = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

		if (count($students) === 0) {
			// Fallback: if no scores yet, show all active students
			$sql = "SELECT s.id, s.username, s.fullName, s.email, s.gradeLevel, s.lastLoginAt,
										 0 AS attempts, NULL AS avg_pct
							FROM students s
							WHERE s.deleted_at IS NULL OR s.deleted_at = '0000-00-00 00:00:00'
							ORDER BY s.fullName ASC";
			$stmt = $db_connection->query($sql);
			$students = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
		}

		$summary['count'] = count($students);
		$summary['attempts'] = array_sum(array_map(fn($s) => (int)($s['attempts'] ?? 0), $students));
		$withAvg = array_filter($students, fn($s) => $s['avg_pct'] !== null);
		if (count($withAvg) > 0) {
			$summary['avg_pct'] = round(array_sum(array_map(fn($s) => (int)$s['avg_pct'], $withAvg)) / count($withAvg));
		}
	} catch (Exception $e) {
		error_log('[Teacher Students] Error loading students: ' . $e->getMessage());
	}
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Students | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-students">
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
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="students.php"><i class="bi bi-people me-1"></i>Students</a></li>
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
 <div class="col-12 col-lg-7">
 <div class="card shadow-sm h-100">
 <div class="card-body">
 <h2 class="h6 d-flex align-items-center gap-2"><i class="bi bi-people"></i> Students Overview</h2>
 <?php if (empty($students)): ?>
	 <p class="placeholder-text mb-0">No students yet. Once learners attempt your quizzes, they will appear here.</p>
 <?php else: ?>
	 <div class="table-responsive mt-3">
		 <table class="table table-hover align-middle">
			 <thead>
				 <tr>
					 <th>Name</th>
					 <th>Email</th>
					 <th class="text-center">Attempts</th>
					 <th class="text-center">Avg Score</th>
					 <th class="text-nowrap">Last Login</th>
				 </tr>
			 </thead>
			 <tbody>
				 <?php foreach ($students as $s): ?>
					 <tr>
						 <td><?php echo htmlspecialchars($s['fullName'] ?: $s['username']); ?></td>
						 <td><?php echo htmlspecialchars($s['email'] ?? ''); ?></td>
						 <td class="text-center fw-semibold"><?php echo (int)($s['attempts'] ?? 0); ?></td>
						 <td class="text-center">
							 <?php echo $s['avg_pct'] !== null ? htmlspecialchars($s['avg_pct']) . '%' : '–'; ?>
						 </td>
						 <td class="text-nowrap small text-muted">
							 <?php echo $s['lastLoginAt'] ? htmlspecialchars($s['lastLoginAt']) : '—'; ?>
						 </td>
					 </tr>
				 <?php endforeach; ?>
			 </tbody>
		 </table>
	 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>
 <div class="col-12 col-lg-5">
 <div class="card shadow-sm h-100">
 <div class="card-body">
 <h2 class="h6 d-flex align-items-center gap-2"><i class="bi bi-graph-up"></i> Topline Insights</h2>
 <ul class="mb-0 list-unstyled mt-3">
	 <li class="mb-2"><strong>Total students:</strong> <?php echo (int)$summary['count']; ?></li>
	 <li class="mb-2"><strong>Total quiz attempts:</strong> <?php echo (int)$summary['attempts']; ?></li>
	 <li class="mb-0"><strong>Average score:</strong> <?php echo $summary['avg_pct'] !== null ? $summary['avg_pct'] . '%' : '—'; ?></li>
 </ul>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>



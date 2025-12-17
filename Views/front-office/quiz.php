<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Quiz.php';

// Student-only
$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'student') {
	http_response_code(403);
	die('Forbidden');
}

$studentId = (string)($_SESSION['user']['id'] ?? $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? '');
$username = (string)($_SESSION['user']['username'] ?? $_SESSION['username'] ?? '');

$quizId = (string)($_GET['quizId'] ?? '');
$quizRow = null;
$quizPayload = null;
$quizList = [];

if ($quizId !== '') {
	$quizRow = Quiz::getById($db_connection, $quizId);
	if ($quizRow) {
		$questions = $quizRow['questions_decoded'] ?? [];
		if (!is_array($questions)) {
			$questions = [];
		}

		// Do not send correctIndex to the client.
		$safeQuestions = [];
		foreach ($questions as $q) {
			$safeQuestions[] = [
				'id' => (string)($q['id'] ?? ''),
				'text' => (string)($q['text'] ?? ''),
				'options' => is_array($q['options'] ?? null) ? array_values($q['options']) : [],
			];
		}

		$quizPayload = [
			'id' => (string)($quizRow['id'] ?? ''),
			'courseId' => (string)($quizRow['courseId'] ?? ''),
			'title' => (string)($quizRow['title'] ?? ''),
			'durationSec' => (int)($quizRow['durationSec'] ?? 60),
			'questions' => $safeQuestions,
		];
	}
} else {
	// Quiz picker (no quizId specified)
	$sql = "
		SELECT q.id, q.title, q.durationSec, q.difficulty, q.courseId, c.title AS courseTitle
		FROM quizzes q
		JOIN courses c ON c.id = q.courseId
		WHERE c.status = 'active'
		ORDER BY c.title ASC, q.createdAt DESC
	";
	$stmt = $db_connection->query($sql);
	$quizList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>EduMind+ | Quiz</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
 <script>
   window.__QUIZ_CONTEXT__ = {
	 studentId: <?= json_encode($studentId) ?>,
	 username: <?= json_encode($username) ?>,
	 quiz: <?= json_encode($quizPayload) ?>
   };
 </script>
</head>
<body data-page="front-quiz">
 <?php include __DIR__ . '/../partials/navbar_student.php'; ?>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h1 id="quizTitle" class="h4 mb-0">Quiz</h1>
 <div>
 <span class="badge bg-dark badge-timer">Time left: <span id="timeLeft">--</span>s</span>
 </div>
 </div>

 <?php if ($quizId === ''): ?>
	 <div class="d-flex justify-content-between align-items-center mb-3">
		 <h2 class="h5 mb-0">Choose a quiz</h2>
		 <a class="btn btn-sm btn-outline-secondary" href="courses.php">Back to Courses</a>
	 </div>

	 <?php if (!$quizList): ?>
		 <div class="alert alert-info" role="alert">No quizzes available yet.</div>
	 <?php else: ?>
		 <div class="row g-3">
			 <?php foreach ($quizList as $q): ?>
				 <div class="col-12 col-lg-6">
					 <div class="card shadow-sm">
						 <div class="card-body">
							 <div class="d-flex justify-content-between align-items-start">
								 <div>
									 <div class="fw-semibold"><?= htmlspecialchars((string)$q['title']) ?></div>
									 <div class="text-muted small"><?= htmlspecialchars((string)$q['courseTitle']) ?></div>
								 </div>
								 <span class="badge bg-dark"><?= (int)($q['durationSec'] ?? 60) ?>s</span>
							 </div>

							 <?php if (!empty($q['difficulty'])): ?>
								 <div class="mt-2 text-muted small">Difficulty: <?= htmlspecialchars((string)$q['difficulty']) ?></div>
							 <?php endif; ?>

							 <div class="mt-3 d-flex justify-content-end">
								 <a class="btn btn-sm btn-primary" href="quiz.php?quizId=<?= urlencode((string)$q['id']) ?>">Start</a>
							 </div>
						 </div>
					 </div>
				 </div>
			 <?php endforeach; ?>
		 </div>
	 <?php endif; ?>

 <?php elseif (!$quizPayload): ?>
	 <div class="alert alert-warning" role="alert">Quiz not found.</div>
	 <a class="btn btn-outline-secondary" href="quiz.php">Back to Quiz List</a>
 <?php else: ?>
	 <form id="quizForm" class="card shadow-sm">
		 <div id="questions" class="card-body"></div>
		 <div class="card-footer d-flex justify-content-between">
			 <a class="btn btn-outline-secondary" href="quiz.php">Back</a>
			 <button id="submitBtn" type="submit" class="btn btn-primary">Submit</button>
		 </div>
	 </form>
 <?php endif; ?>

 <div id="resultPanel" class="mt-4" style="display:none;"></div>

 <!-- Report Issue Modal -->
 <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog">
 <div class="modal-content">
 <div class="modal-header">
 <h5 class="modal-title">Report Quiz Issue</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body">
 <form id="reportForm">
 <input type="hidden" id="reportQuizId">
 <input type="hidden" id="reportQuestionId">
 <div class="mb-3">
 <label for="reportType" class="form-label">Issue Type</label>
 <select id="reportType" class="form-select" required>
 <option value="">Select type...</option>
 <option value="incorrect_answer">Incorrect Answer</option>
 <option value="wrong_display">Display Issue</option>
 <option value="typo">Typo/Grammar</option>
 <option value="other">Other</option>
 </select>
 </div>
 <div class="mb-3">
 <label for="reportDescription" class="form-label">Description</label>
 <textarea id="reportDescription" class="form-control" rows="3" required placeholder="Please describe the issue in detail..."></textarea>
 </div>
 </form>
 </div>
 <div class="modal-footer">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
 <button type="button" class="btn btn-primary" id="submitReport">Submit Report</button>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="assets/js/quiz.js"></script>
</body>
</html>



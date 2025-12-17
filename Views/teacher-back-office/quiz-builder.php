<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Controllers/QuizController.php';

if (($_SESSION['role'] ?? null) !== 'teacher') {
	http_response_code(403);
	die('Forbidden');
}

$teacherId = (string)($_SESSION['teacher_id'] ?? $_SESSION['user_id'] ?? '');

// Courses owned by this teacher
$coursesStmt = $db_connection->prepare('SELECT id, title FROM courses WHERE teacherId = ? ORDER BY title ASC');
$coursesStmt->execute([$teacherId]);
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$qc = new QuizController($db_connection);
	$res = $qc->createFromPost($teacherId);
	if (($res['success'] ?? false) === true) {
		$message = 'Quiz created.';
	} else {
		$error = (string)($res['error'] ?? 'Failed to create quiz.');
	}
}
?>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Quiz Builder | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-quiz-builder">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <h1 class="h4 mb-3">Quiz Builder</h1>

 <?php if ($message): ?>
		 <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
 <?php endif; ?>
 <?php if ($error): ?>
		 <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <form method="post" class="card shadow-sm" id="quizForm">
		 <div class="card-body">
				 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

				 <div class="row g-3">
						 <div class="col-md-4">
								 <label class="form-label">Course</label>
								 <select name="courseId" class="form-select" required>
										 <option value="">Select course...</option>
										 <?php foreach ($courses as $c): ?>
												 <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['title']) ?></option>
										 <?php endforeach; ?>
								 </select>
						 </div>
						 <div class="col-md-5">
								 <label class="form-label">Quiz Title</label>
								 <input name="title" class="form-control" placeholder="e.g., Math Basics - Quiz 2" required />
						 </div>
						 <div class="col-md-3">
								 <label class="form-label">Duration (sec)</label>
								 <input name="durationSec" type="number" min="30" step="10" value="60" class="form-control" />
						 </div>
						 <div class="col-md-4">
								 <label class="form-label">Difficulty</label>
								 <select name="difficulty" class="form-select">
										 <option value="">(optional)</option>
										 <option value="beginner">beginner</option>
										 <option value="intermediate">intermediate</option>
										 <option value="advanced">advanced</option>
								 </select>
						 </div>
				 </div>

				 <hr />
				 <div id="questions"></div>
				 <button id="addQuestion" type="button" class="btn btn-outline-primary btn-sm mt-2">Add Question</button>
		 </div>

		 <div class="card-footer d-flex justify-content-end gap-2">
				 <a href="courses.php" class="btn btn-outline-secondary">Back</a>
				 <button class="btn btn-primary" type="submit">Save Quiz</button>
		 </div>
 </form>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>

 <script>
 (function(){
	 const container = document.getElementById('questions');
	 const addBtn = document.getElementById('addQuestion');
	 let qIndex = 0;

	 function addQuestion(){
		 const idx = qIndex++;
		 const el = document.createElement('div');
		 el.className = 'border rounded p-3 mb-3';
		 el.innerHTML = `
			 <div class="d-flex justify-content-between align-items-center mb-2">
				 <strong>Question ${idx+1}</strong>
				 <button type="button" class="btn btn-sm btn-outline-danger" data-remove>Remove</button>
			 </div>
			 <div class="mb-2">
				 <label class="form-label">Question text</label>
				 <input class="form-control" name="questions[${idx}][text]" required>
			 </div>
			 <div class="row g-2">
				 ${[0,1,2,3].map(i => `
					 <div class="col-md-6">
						 <label class="form-label">Option ${i+1}</label>
						 <input class="form-control" name="questions[${idx}][options][${i}]" required>
					 </div>
				 `).join('')}
			 </div>
			 <div class="mt-2">
				 <label class="form-label">Correct option</label>
				 <select class="form-select" name="questions[${idx}][correctIndex]">
					 <option value="0">Option 1</option>
					 <option value="1">Option 2</option>
					 <option value="2">Option 3</option>
					 <option value="3">Option 4</option>
				 </select>
			 </div>
		 `;

		 el.querySelector('[data-remove]').addEventListener('click', () => el.remove());
		 container.appendChild(el);
	 }

	 addBtn.addEventListener('click', addQuestion);
	 addQuestion();
 })();
 </script>
</body>
</html>



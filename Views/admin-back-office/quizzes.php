<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Quiz.php';

if (($_SESSION['role'] ?? null) !== 'admin') {
	http_response_code(403);
	die('Forbidden');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$posted = (string)($_POST['csrf_token'] ?? '');
	$sessionToken = (string)($_SESSION['csrf_token'] ?? '');
	if ($posted === '' || $sessionToken === '' || !hash_equals($sessionToken, $posted)) {
		$error = 'Invalid CSRF token';
	} else {
		$action = (string)($_POST['action'] ?? '');
		if ($action === 'delete') {
			$quizId = trim((string)($_POST['quizId'] ?? ''));
			if ($quizId !== '') {
				$ok = Quiz::delete($db_connection, $quizId);
				if (!$ok) {
					$error = 'Failed to delete quiz';
				}
			}
		}
	}
}

$quizzes = Quiz::listAll($db_connection);
$total = count($quizzes);
?>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Quiz Management | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-quizzes">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-4">
	 <h1 class="h3">Quiz Management</h1>
	 <div class="text-muted">Total: <?= (int)$total ?></div>
 </div>

 <?php if ($error): ?>
	 <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <div class="card shadow-sm">
	 <div class="card-body">
		 <h2 class="h6 mb-3">All Quizzes</h2>
		 <div class="table-responsive">
			 <table class="table table-hover align-middle">
				 <thead>
				 <tr>
					 <th>Title</th>
					 <th>Course</th>
					 <th>Teacher</th>
					 <th>Questions</th>
					 <th>Duration</th>
					 <th>Difficulty</th>
					 <th>Created</th>
					 <th class="text-end">Actions</th>
				 </tr>
				 </thead>
				 <tbody>
				 <?php if (!$quizzes): ?>
					 <tr><td colspan="8" class="text-center py-4 text-muted">No quizzes found</td></tr>
				 <?php else: ?>
					 <?php foreach ($quizzes as $q): ?>
						 <?php
						 $questionsCount = 0;
						 $decoded = json_decode((string)($q['questions'] ?? '[]'), true);
						 if (is_array($decoded)) {
							 $questionsCount = count($decoded);
						 }
						 ?>
						 <tr>
							 <td>
								 <div class="fw-semibold"><?= htmlspecialchars((string)($q['title'] ?? '')) ?></div>
								 <div class="text-muted small">ID: <?= htmlspecialchars((string)($q['id'] ?? '')) ?></div>
							 </td>
							 <td class="text-muted small"><?= htmlspecialchars((string)($q['courseTitle'] ?? $q['courseId'] ?? '')) ?></td>
							 <td class="text-muted small"><?= htmlspecialchars((string)($q['teacherName'] ?? $q['createdBy'] ?? '')) ?></td>
							 <td><?= (int)$questionsCount ?></td>
							 <td class="text-muted small"><?= (int)($q['durationSec'] ?? 60) ?>s</td>
							 <td class="text-muted small"><?= htmlspecialchars((string)($q['difficulty'] ?? '')) ?></td>
							 <td class="text-muted small"><?= htmlspecialchars((string)($q['createdAt'] ?? '')) ?></td>
							 <td class="text-end">
								 <form method="post" class="d-inline" onsubmit="return confirm('Delete this quiz? This may also remove related scores.');">
									 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
									 <input type="hidden" name="action" value="delete">
									 <input type="hidden" name="quizId" value="<?= htmlspecialchars((string)($q['id'] ?? '')) ?>">
									 <button class="btn btn-sm btn-danger" type="submit"><i class="bi bi-trash"></i> Delete</button>
								 </form>
							 </td>
						 </tr>
					 <?php endforeach; ?>
				 <?php endif; ?>
				 </tbody>
			 </table>
		 </div>
	 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Controllers/QuizReportController.php';

if (($_SESSION['role'] ?? null) !== 'teacher') {
	http_response_code(403);
	die('Forbidden');
}

$teacherId = (string)($_SESSION['teacher_id'] ?? $_SESSION['user_id'] ?? '');
$status = (string)($_GET['status'] ?? 'all');
$allowed = ['all', 'pending', 'reviewed', 'resolved', 'dismissed'];
if (!in_array($status, $allowed, true)) {
	$status = 'all';
}

$controller = new QuizReportController($db_connection);

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = (string)($_POST['action'] ?? '');
	if ($action === 'update_status') {
		$reportId = trim((string)($_POST['reportId'] ?? ''));
		$newStatus = trim((string)($_POST['newStatus'] ?? ''));

		$res = $controller->updateStatusForTeacher($teacherId, $reportId, $newStatus);
		if (($res['success'] ?? false) === true) {
			header('Location: quiz-reports.php?status=' . urlencode($status));
			exit;
		}
		$error = (string)($res['error'] ?? 'Failed to update status');
	}
}

$reports = $controller->listForTeacher($teacherId, $status);
$pendingCount = 0;
foreach ($reports as $r) {
	if (($r['status'] ?? '') === 'pending') {
		$pendingCount++;
	}
}

function qr_badge(string $st): string
{
	switch ($st) {
		case 'pending':
			return 'bg-warning text-dark';
		case 'reviewed':
			return 'bg-info text-dark';
		case 'resolved':
			return 'bg-success';
		case 'dismissed':
			return 'bg-secondary';
		default:
			return 'bg-light text-dark';
	}
}

function qr_preview(string $text, int $len = 120): string
{
	$t = trim($text);
	if (strlen($t) <= $len) {
		return $t;
	}
	return substr($t, 0, $len) . '…';
}
?>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Quiz Reports | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-quiz-reports">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <?php if ($message): ?>
	 <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
 <?php endif; ?>
 <?php if ($error): ?>
	 <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <div class="card shadow-sm">
	 <div class="card-header d-flex justify-content-between align-items-center">
		 <h1 class="h5 mb-0">📝 Student Quiz Reports</h1>
		 <span class="badge bg-warning text-dark"><?= (int)$pendingCount ?> pending</span>
	 </div>
	 <div class="card-body">
		 <div class="mb-3">
			 <label class="form-label small">Filter by status:</label>
			 <div class="btn-group btn-group-sm" role="group" aria-label="Status filter">
				 <a class="btn btn-outline-primary <?= $status === 'all' ? 'active' : '' ?>" href="quiz-reports.php?status=all">All</a>
				 <a class="btn btn-outline-warning <?= $status === 'pending' ? 'active' : '' ?>" href="quiz-reports.php?status=pending">Pending</a>
				 <a class="btn btn-outline-info <?= $status === 'reviewed' ? 'active' : '' ?>" href="quiz-reports.php?status=reviewed">Reviewed</a>
				 <a class="btn btn-outline-success <?= $status === 'resolved' ? 'active' : '' ?>" href="quiz-reports.php?status=resolved">Resolved</a>
				 <a class="btn btn-outline-secondary <?= $status === 'dismissed' ? 'active' : '' ?>" href="quiz-reports.php?status=dismissed">Dismissed</a>
			 </div>
		 </div>

		 <?php if (!$reports): ?>
			 <div class="text-muted">No reports found.</div>
		 <?php else: ?>
			 <div class="table-responsive">
				 <table class="table table-sm align-middle">
					 <thead>
					 <tr>
						 <th>Quiz</th>
						 <th>Question</th>
						 <th>Type</th>
						 <th>Description</th>
						 <th>Status</th>
						 <th class="text-end">Action</th>
					 </tr>
					 </thead>
					 <tbody>
					 <?php foreach ($reports as $r): ?>
						 <?php
						 $rid = (string)($r['id'] ?? '');
						 $st = (string)($r['status'] ?? 'pending');
						 $quizTitle = (string)($r['quizTitle'] ?? $r['quizId'] ?? '');
						 $questionId = (string)($r['questionId'] ?? '');
						 $reportType = (string)($r['reportType'] ?? 'other');
						 $desc = (string)($r['description'] ?? '');
						 ?>
						 <tr>
							 <td>
								 <div class="fw-semibold"><?= htmlspecialchars($quizTitle) ?></div>
								 <div class="text-muted small">ID: <?= htmlspecialchars((string)($r['quizId'] ?? '')) ?></div>
							 </td>
							 <td class="text-muted small"><?= htmlspecialchars($questionId) ?></td>
							 <td class="text-muted small"><?= htmlspecialchars($reportType) ?></td>
							 <td><?= htmlspecialchars(qr_preview($desc)) ?></td>
							 <td><span class="badge <?= qr_badge($st) ?>"><?= htmlspecialchars($st) ?></span></td>
							 <td class="text-end">
								 <form method="post" class="d-inline-flex gap-2">
									 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
									 <input type="hidden" name="action" value="update_status">
									 <input type="hidden" name="reportId" value="<?= htmlspecialchars($rid) ?>">
									 <select class="form-select form-select-sm" name="newStatus" aria-label="Update status">
										 <?php foreach (['pending', 'reviewed', 'resolved', 'dismissed'] as $opt): ?>
											 <option value="<?= htmlspecialchars($opt) ?>" <?= $opt === $st ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
										 <?php endforeach; ?>
									 </select>
									 <button class="btn btn-sm btn-primary" type="submit">Update</button>
								 </form>
							 </td>
						 </tr>
					 <?php endforeach; ?>
					 </tbody>
				 </table>
			 </div>
		 <?php endif; ?>
	 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>




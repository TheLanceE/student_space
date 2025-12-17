<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Controllers/ReportController.php';

if (($_SESSION['role'] ?? null) !== 'teacher') {
	http_response_code(403);
	die('Forbidden');
}

$controller = new ReportController($db_connection);
$role = (string)($_SESSION['role'] ?? '');
$userId = (string)($_SESSION['teacher_id'] ?? $_SESSION['user_id'] ?? '');
$status = (string)($_GET['status'] ?? 'All');
$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$res = $controller->createForRole($role, $userId);
		if ($res['ok']) {
			$message = 'Report created.';
		} else {
			$error = $res['error'] ?? 'Failed to create report.';
		}
	} elseif ($action === 'update') {
		$id = (int)($_POST['id'] ?? 0);
		$res = $controller->updateForRole($role, $userId, $id);
		if ($res['ok']) {
			$message = 'Report updated.';
			$editId = null;
		} else {
			$error = $res['error'] ?? 'Failed to update report.';
			$editId = $id;
		}
	} elseif ($action === 'status') {
		$id = (int)($_POST['id'] ?? 0);
		$newStatus = (string)($_POST['status'] ?? '');
		$res = $controller->updateStatusForRole($role, $userId, $id, $newStatus);
		if ($res['ok']) {
			$message = 'Status updated.';
		} else {
			$error = $res['error'] ?? 'Failed to update status.';
		}
	} elseif ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$res = $controller->deleteForRole($role, $userId, $id);
		if ($res['ok']) {
			$message = 'Report deleted.';
		} else {
			$error = $res['error'] ?? 'Failed to delete report.';
		}
	}
}

$editReport = null;
if ($editId !== null && $editId > 0) {
	$editReport = Report::getById($db_connection, $editId);
	if (!$editReport || ($editReport['created_by'] ?? '') !== $userId) {
		$editReport = null;
		$editId = null;
	}
}

$reports = $controller->listForRole($role, $userId, $status);
?>

<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Reports | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-reports">
 <?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

 <main class="container py-4">
 <?php if ($message): ?>
	 <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
 <?php endif; ?>
 <?php if ($error): ?>
	 <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <div class="card shadow-sm mb-3">
	 <div class="card-body">
		 <h1 class="h5 mb-3">Reports</h1>
		 <div class="btn-group" role="group" aria-label="Report filters">
			 <?php foreach (array_merge(['All'], ReportController::allowedStatuses()) as $s): ?>
				 <a class="btn btn-sm <?= ($status === $s ? 'btn-primary' : 'btn-outline-primary') ?>" href="?status=<?= urlencode($s) ?>">
					 <?= htmlspecialchars($s) ?>
				 </a>
			 <?php endforeach; ?>
		 </div>
	 </div>
 </div>

 <?php if ($editReport): ?>
 <div class="card shadow-sm mb-3">
	 <div class="card-header"><strong>Edit Report #<?= (int)$editReport['id'] ?></strong></div>
	 <div class="card-body">
		 <form method="post" class="row g-3">
			 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
			 <input type="hidden" name="action" value="update">
			 <input type="hidden" name="id" value="<?= (int)$editReport['id'] ?>">

			 <div class="col-md-4">
				 <label class="form-label">Student</label>
				 <input class="form-control" name="student" value="<?= htmlspecialchars($editReport['student'] ?? '') ?>" required>
			 </div>
			 <div class="col-md-4">
				 <label class="form-label">Quiz (optional)</label>
				 <input class="form-control" name="quiz" value="<?= htmlspecialchars($editReport['quiz'] ?? '') ?>">
			 </div>
			 <div class="col-md-4">
				 <label class="form-label">Type</label>
				 <select class="form-select" name="type" required>
					 <?php foreach (ReportController::allowedTypes() as $t): ?>
						 <option value="<?= htmlspecialchars($t) ?>" <?= (($editReport['type'] ?? '') === $t ? 'selected' : '') ?>><?= htmlspecialchars($t) ?></option>
					 <?php endforeach; ?>
				 </select>
			 </div>
			 <div class="col-md-4">
				 <label class="form-label">Status</label>
				 <select class="form-select" name="status" required>
					 <?php foreach (ReportController::allowedStatuses() as $s): ?>
						 <option value="<?= htmlspecialchars($s) ?>" <?= (($editReport['status'] ?? '') === $s ? 'selected' : '') ?>><?= htmlspecialchars($s) ?></option>
					 <?php endforeach; ?>
				 </select>
			 </div>
			 <div class="col-12">
				 <label class="form-label">Content</label>
				 <textarea class="form-control" name="content" rows="4" required><?= htmlspecialchars($editReport['content'] ?? '') ?></textarea>
			 </div>
			 <div class="col-12 d-flex gap-2">
				 <button class="btn btn-primary" type="submit">Update</button>
				 <a class="btn btn-outline-secondary" href="reports.php?status=<?= urlencode($status) ?>">Cancel</a>
			 </div>
		 </form>
	 </div>
 </div>
 <?php endif; ?>

 <div class="card shadow-sm mb-3">
	 <div class="card-header d-flex justify-content-between align-items-center">
		 <strong>My Reports</strong>
		 <small class="text-muted"><?= count($reports) ?> total</small>
	 </div>
	 <div class="card-body">
		 <?php if (empty($reports)): ?>
			 <p class="text-muted mb-0">No reports found.</p>
		 <?php else: ?>
			 <div class="table-responsive">
				 <table class="table table-hover align-middle">
					 <thead>
						 <tr>
							 <th>Student</th>
							 <th>Quiz</th>
							 <th>Type</th>
							 <th>Status</th>
							 <th>Created</th>
							 <th class="text-end">Actions</th>
						 </tr>
					 </thead>
					 <tbody>
						 <?php foreach ($reports as $r): ?>
							 <tr>
								 <td>
									 <div class="fw-semibold"><?= htmlspecialchars($r['student'] ?? '') ?></div>
									 <?php if (!empty($r['content'])): ?>
										 <?php
											 $preview = (string)$r['content'];
											 if (strlen($preview) > 90) {
												 $preview = substr($preview, 0, 90) . '...';
											 }
										 ?>
										 <div class="text-muted small"><?= htmlspecialchars($preview) ?></div>
									 <?php endif; ?>
								 </td>
								 <td><?= htmlspecialchars((string)($r['quiz'] ?? '')) ?></td>
								 <td><?= htmlspecialchars((string)($r['type'] ?? '')) ?></td>
								 <td><span class="badge text-bg-secondary"><?= htmlspecialchars((string)($r['status'] ?? '')) ?></span></td>
								 <td><?= htmlspecialchars((string)($r['created_date'] ?? '')) ?></td>
								 <td class="text-end">
									 <div class="d-inline-flex gap-1">
										 <a class="btn btn-sm btn-outline-primary" href="reports.php?status=<?= urlencode($status) ?>&edit_id=<?= (int)($r['id'] ?? 0) ?>">Edit</a>
										 <form method="post" class="d-inline">
											 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
											 <input type="hidden" name="action" value="status">
											 <input type="hidden" name="id" value="<?= (int)($r['id'] ?? 0) ?>">
											 <input type="hidden" name="status" value="Reviewed">
											 <button type="submit" class="btn btn-sm btn-outline-success">Review</button>
										 </form>
										 <form method="post" class="d-inline">
											 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
											 <input type="hidden" name="action" value="status">
											 <input type="hidden" name="id" value="<?= (int)($r['id'] ?? 0) ?>">
											 <input type="hidden" name="status" value="Kept">
											 <button type="submit" class="btn btn-sm btn-outline-warning">Keep</button>
										 </form>
										 <form method="post" class="d-inline" onsubmit="return confirm('Delete this report?');">
											 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
											 <input type="hidden" name="action" value="delete">
											 <input type="hidden" name="id" value="<?= (int)($r['id'] ?? 0) ?>">
											 <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
										 </form>
									 </div>
								 </td>
							 </tr>
						 <?php endforeach; ?>
					 </tbody>
				 </table>
			 </div>
		 <?php endif; ?>
	 </div>
 </div>

 <div class="card shadow-sm">
	 <div class="card-header"><strong>Create Report</strong></div>
	 <div class="card-body">
		 <form method="post" class="row g-3">
			 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
			 <input type="hidden" name="action" value="create">

			 <div class="col-md-4">
				 <label class="form-label">Student</label>
				 <input class="form-control" name="student" required>
			 </div>
			 <div class="col-md-4">
				 <label class="form-label">Quiz (optional)</label>
				 <input class="form-control" name="quiz">
			 </div>
			 <div class="col-md-4">
				 <label class="form-label">Type</label>
				 <select class="form-select" name="type" required>
					 <?php foreach (ReportController::allowedTypes() as $t): ?>
						 <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
					 <?php endforeach; ?>
				 </select>
			 </div>
			 <div class="col-md-4">
				 <label class="form-label">Status</label>
				 <select class="form-select" name="status" required>
					 <?php foreach (ReportController::allowedStatuses() as $s): ?>
						 <option value="<?= htmlspecialchars($s) ?>" <?= ($s === 'Pending' ? 'selected' : '') ?>><?= htmlspecialchars($s) ?></option>
					 <?php endforeach; ?>
				 </select>
			 </div>
			 <div class="col-12">
				 <label class="form-label">Content</label>
				 <textarea class="form-control" name="content" rows="4" required></textarea>
			 </div>
			 <div class="col-12">
				 <button class="btn btn-primary" type="submit">Add Report</button>
			 </div>
		 </form>
	 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>



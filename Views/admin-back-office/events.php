<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Controllers/EventController.php';

if (($_SESSION['role'] ?? null) !== 'admin') {
	http_response_code(403);
	die('Forbidden');
}

$controller = new EventController($db_connection);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'delete') {
		$ok = $controller->delete();
		if ($ok) {
			$message = 'Event deleted.';
		} else {
			$error = 'Could not delete event (check CSRF).';
		}
	}
}

$events = $controller->getAll();
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>EduMind+ | Events Administration</title>
	<link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
	<link href="../../shared-assets/css/global.css" rel="stylesheet">
	<link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-events" class="bg-light">
	<?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

	<div class="container my-5">
		<?php if ($message): ?>
			<div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<div class="card shadow-sm">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h1 class="h5 mb-0">All Events</h1>
			</div>
			<div class="card-body">
				<?php if (empty($events)): ?>
					<p class="text-muted mb-0">No events found.</p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-hover align-middle">
							<thead>
								<tr>
									<th>Title</th>
									<th>Date</th>
									<th>Time</th>
									<th>Course</th>
									<th>Teacher</th>
									<th class="text-end">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($events as $event): ?>
									<tr>
										<td>
											<div class="fw-semibold"><?= htmlspecialchars($event['title'] ?? '') ?></div>
											<?php if (!empty($event['description'])): ?>
												<div class="text-muted small"><?= htmlspecialchars($event['description']) ?></div>
											<?php endif; ?>
										</td>
										<td><?= htmlspecialchars($event['date'] ?? '') ?></td>
										<td><?= htmlspecialchars(($event['startTime'] ?? '') . ' - ' . ($event['endTime'] ?? '')) ?></td>
										<td><?= htmlspecialchars($event['course'] ?? '') ?></td>
										<td><?= htmlspecialchars((string)($event['teacherID'] ?? '')) ?></td>
										<td class="text-end">
											<form method="post" class="d-inline" onsubmit="return confirm('Delete this event?');">
												<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
												<input type="hidden" name="action" value="delete">
												<input type="hidden" name="deleteID" value="<?= htmlspecialchars($event['id'] ?? '') ?>">
												<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
	</div>

	<script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

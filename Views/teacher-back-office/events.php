<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Controllers/EventController.php';

if (($_SESSION['role'] ?? null) !== 'teacher') {
	http_response_code(403);
	die('Forbidden');
}

$controller = new EventController($db_connection);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';

	if ($action === 'create') {
		$ok = $controller->create();
		if ($ok) {
			$message = 'Event created.';
		} else {
			$error = 'Could not create event (check CSRF and fields).';
		}
	} elseif ($action === 'delete') {
		$ok = $controller->delete();
		if ($ok) {
			$message = 'Event deleted.';
		} else {
			$error = 'Could not delete event (check CSRF).';
		}
	}
}

$teacherId = (int)($_SESSION['teacher_id'] ?? $_SESSION['user_id'] ?? 0);
$events = $controller->getByTeacher($teacherId);
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>EduMind+ | Events Management</title>
	<link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
	<link href="../../shared-assets/css/global.css" rel="stylesheet">
	<link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-events" class="bg-light">
	<?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

	<div class="container my-5">
		<?php if ($message): ?>
			<div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<div class="card mb-4 shadow-sm">
			<div class="card-header">
				<h1 class="h5 mb-0">Add New Event</h1>
			</div>
			<div class="card-body">
				<form method="post">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
					<input type="hidden" name="action" value="create">

					<div class="mb-3">
						<label for="title" class="form-label">Event Title</label>
						<input type="text" class="form-control" id="title" name="title" required>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-md-4">
							<label for="date" class="form-label">Event Date</label>
							<input type="date" class="form-control" id="date" name="date" required>
						</div>
						<div class="col-md-4">
							<label for="startTime" class="form-label">Start Time</label>
							<input type="time" class="form-control" id="startTime" name="startTime" required>
						</div>
						<div class="col-md-4">
							<label for="endTime" class="form-label">End Time</label>
							<input type="time" class="form-control" id="endTime" name="endTime" required>
						</div>
					</div>

					<div class="mb-3">
						<label for="course" class="form-label">Course/Subject</label>
						<input type="text" class="form-control" id="course" name="course" required>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-md-6">
							<label for="type" class="form-label">Event Type</label>
							<select class="form-select" id="type" name="type" required>
								<option value="Lecture">Lecture</option>
								<option value="Quiz">Quiz</option>
								<option value="Webinar">Webinar</option>
								<option value="Other" selected>Other</option>
							</select>
						</div>
						<div class="col-md-6">
							<label for="maxParticipants" class="form-label">Max Participants</label>
							<input type="number" class="form-control" id="maxParticipants" name="maxParticipants" value="30" min="1" required>
						</div>
					</div>

					<div class="mb-3">
						<label for="location" class="form-label">Event Location (Lecture only)</label>
						<input type="text" class="form-control" id="location" name="location">
					</div>

					<div class="mb-3">
						<label for="description" class="form-label">Description</label>
						<textarea class="form-control" id="description" name="description" rows="3"></textarea>
					</div>

					<button type="submit" class="btn btn-success">Create Event</button>
				</form>
			</div>
		</div>

		<div class="card shadow-sm">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h2 class="h5 mb-0">My Events</h2>
			</div>
			<div class="card-body">
				<?php if (empty($events)): ?>
					<p class="text-muted mb-0">No events yet.</p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-hover align-middle">
							<thead>
								<tr>
									<th>Title</th>
									<th>Date</th>
									<th>Time</th>
									<th>Course</th>
									<th>Type</th>
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
										<td><?= htmlspecialchars($event['type'] ?? '') ?></td>
										<td class="text-end">
											<form method="post" class="d-inline" onsubmit="return confirm('Delete this event?');">
												<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
												<input type="hidden" name="action" value="delete">
												<input type="hidden" name="deleteID" value="<?= (int)($event['eventID'] ?? 0) ?>">
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




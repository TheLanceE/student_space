<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Reward.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'teacher') {
    http_response_code(403);
    die('Forbidden');
}

$teacherId = (string)($_SESSION['teacher_id'] ?? $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? '');
$message = null;
$error = null;

function csrf_ok(): bool {
    $posted = (string)($_POST['csrf_token'] ?? '');
    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
    return $posted !== '' && $sessionToken !== '' && hash_equals($sessionToken, $posted);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Invalid CSRF token';
    } else {
        $action = (string)($_POST['action'] ?? '');
        $rid = trim((string)($_POST['redemptionId'] ?? ''));
        $note = trim((string)($_POST['note'] ?? ''));

        if ($rid !== '' && $action === 'approve') {
            $res = Reward::approveRequest($db_connection, $rid, $teacherId, $note !== '' ? $note : null);
            if (($res['success'] ?? false) === true) {
                $message = 'Request approved.';
            } else {
                $error = (string)($res['error'] ?? 'Failed to approve');
            }
        } elseif ($rid !== '' && $action === 'reject') {
            $ok = Reward::rejectRequest($db_connection, $rid, $teacherId, $note !== '' ? $note : null);
            $message = $ok ? 'Request rejected.' : 'Failed to reject.';
        }
    }
}

$pending = Reward::listPendingRequestsForTeacher($db_connection);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reward Requests | Teacher</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="teacher-reward-requests">
<?php include __DIR__ . '/../partials/navbar_teacher.php'; ?>

<main class="container py-4">
    <h1 class="h4 mb-3">Pending Reward Requests</h1>

    <?php if ($message): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!$pending): ?>
                <div class="text-muted">No pending requests.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Reward</th>
                            <th>Cost</th>
                            <th>Short By</th>
                            <th>Requested</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pending as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string)($p['studentName'] ?? $p['studentUsername'] ?? '')) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars((string)($p['studentUsername'] ?? '')) ?></div>
                                </td>
                                <td><?= htmlspecialchars((string)($p['rewardName'] ?? '')) ?></td>
                                <td><?= (int)($p['costPoints'] ?? 0) ?> pts</td>
                                <td class="text-muted small"><?= (int)($p['shortBy'] ?? 0) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars((string)($p['requestedAt'] ?? '')) ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline-flex gap-2 align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="redemptionId" value="<?= htmlspecialchars((string)($p['id'] ?? '')) ?>">
                                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Note (optional)">
                                        <button class="btn btn-sm btn-success" name="action" value="approve" type="submit">Approve</button>
                                        <button class="btn btn-sm btn-outline-danger" name="action" value="reject" type="submit">Reject</button>
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

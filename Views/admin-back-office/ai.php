<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/AIInsights.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

$rows = AIInsights::adminPredictions($db_connection);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AI Insights | Admin</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-ai">
<?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">AI Insights (Predictions)</h1>
        <div class="text-muted small">Based on points ledger activity</div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!$rows): ?>
                <div class="text-muted">No students found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Current</th>
                            <th>Projected</th>
                            <th>Tier</th>
                            <th>Confidence</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string)($r['fullName'] ?? $r['username'] ?? '')) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars((string)($r['username'] ?? '')) ?></div>
                                </td>
                                <td><?= (int)($r['currentBalance'] ?? 0) ?> pts</td>
                                <td><?= (int)($r['projectedBalance'] ?? 0) ?> pts</td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars((string)($r['projectedTier'] ?? '')) ?></span></td>
                                <td><?= (int)($r['confidence'] ?? 0) ?>%</td>
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

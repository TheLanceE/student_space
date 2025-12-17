<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/AIInsights.php';
require_once __DIR__ . '/../../Models/Points.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'student') {
    http_response_code(403);
    die('Forbidden');
}

$studentId = (string)($_SESSION['user']['id'] ?? $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? '');
$username = (string)($_SESSION['user']['username'] ?? $_SESSION['username'] ?? '');

$balance = Points::getBalance($db_connection, $studentId);
$pred = AIInsights::studentPrediction($db_connection, $studentId);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AI Predictions | Student</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="front-ai">
<?php include __DIR__ . '/../partials/navbar_student.php'; ?>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">AI Predictions</h1>
        <span class="badge bg-info text-dark">Confidence <?= (int)($pred['confidence'] ?? 0) ?>%</span>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header"><strong>This Month</strong></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span class="text-muted">Points earned</span><span class="fw-semibold"><?= (int)($pred['monthEarned'] ?? 0) ?></span></div>
                    <div class="d-flex justify-content-between mt-2"><span class="text-muted">Active days</span><span class="fw-semibold"><?= (int)($pred['activeDays'] ?? 0) ?></span></div>
                    <div class="d-flex justify-content-between mt-2"><span class="text-muted">Avg per active day</span><span class="fw-semibold"><?= htmlspecialchars((string)($pred['dailyAvg'] ?? '0')) ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Projected Month End</strong></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span class="text-muted">Projected earned</span><span class="fw-semibold"><?= (int)($pred['projectedMonthEarned'] ?? 0) ?></span></div>
                    <div class="d-flex justify-content-between mt-2"><span class="text-muted">Projected balance</span><span class="fw-semibold"><?= (int)($pred['projectedBalance'] ?? 0) ?> pts</span></div>
                    <div class="d-flex justify-content-between mt-2"><span class="text-muted">Projected tier</span><span class="fw-semibold"><?= htmlspecialchars((string)($pred['projectedTier'] ?? '')) ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Reasoning</strong></div>
                <div class="card-body text-muted">
                    <?= htmlspecialchars((string)($pred['reasoning'] ?? '')) ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

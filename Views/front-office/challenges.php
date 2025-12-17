<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Challenge.php';
require_once __DIR__ . '/../../Models/Points.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'student') {
    http_response_code(403);
    die('Forbidden');
}

$studentId = (string)($_SESSION['user']['id'] ?? $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? '');
$username = (string)($_SESSION['user']['username'] ?? $_SESSION['username'] ?? '');

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
        if ($action === 'complete') {
            $challengeId = trim((string)($_POST['challengeId'] ?? ''));
            $c = $challengeId !== '' ? Challenge::getById($db_connection, $challengeId) : null;
            if (!$c) {
                $error = 'Challenge not found';
            } else {
                $level = (int)($c['level'] ?? 0);
                $pr = $c['prerequisiteLevel'];
                $prereq = ($pr === null) ? max(0, $level - 1) : (int)$pr;
                $unlocked = ($level <= 0) ? true : Challenge::hasCompletedLevel($db_connection, $studentId, $prereq);

                if (!$unlocked) {
                    $error = 'This challenge is locked.';
                } else {
                    $ok = Challenge::complete($db_connection, $challengeId, $studentId);
                    if (!$ok) {
                        $error = 'Could not complete challenge (maybe already completed).';
                    } else {
                        $points = (int)($c['points'] ?? 10);
                        Points::add($db_connection, $studentId, $points, 'Challenge completed: ' . (string)($c['title'] ?? ''), 'challenge', $challengeId);
                        $message = 'Challenge completed! +' . $points . ' points.';
                    }
                }
            }
        } elseif ($action === 'rate') {
            $challengeId = trim((string)($_POST['challengeId'] ?? ''));
            $rating = (int)($_POST['rating'] ?? 0);
            if ($challengeId === '' || $rating < 1 || $rating > 5) {
                $error = 'Invalid rating.';
            } else {
                $ok = Challenge::rate($db_connection, $challengeId, $studentId, $rating);
                $message = $ok ? 'Thanks for rating!' : 'Could not save rating.';
            }
        }
    }
}

$balance = Points::getBalance($db_connection, $studentId);
$tiers = Points::tierForPoints($balance);
$all = Challenge::listAll($db_connection);
$completedByLevel = Challenge::getStudentProgress($db_connection, $studentId);

$completedSet = [];
$stmt = $db_connection->prepare('SELECT challengeId FROM challenge_completions WHERE studentId = ?');
$stmt->execute([$studentId]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $completedSet[(string)$r['challengeId']] = true;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Challenges | Student</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="front-challenges">
<?php include __DIR__ . '/../partials/navbar_student.php'; ?>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Challenges</h1>
        <div class="text-muted small">Tier: <strong><?= htmlspecialchars($tiers['current']['name']) ?></strong></div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$all): ?>
        <div class="alert alert-info" role="alert">No challenges available yet.</div>
    <?php else: ?>
        <?php
        $currentLevel = null;
        foreach ($all as $c):
            $level = (int)($c['level'] ?? 0);
            if ($currentLevel !== $level):
                if ($currentLevel !== null) echo '</div></div>';
                $currentLevel = $level;
                $completedCount = (int)($completedByLevel[$level] ?? 0);
                echo '<div class="card shadow-sm mb-3">';
                echo '<div class="card-header d-flex justify-content-between align-items-center">';
                echo '<div class="fw-semibold">Level ' . $level . '</div>';
                echo '<span class="badge bg-secondary">' . $completedCount . ' completed</span>';
                echo '</div><div class="card-body"><div class="row g-3">';
            endif;

            $cid = (string)($c['id'] ?? '');
            $pr = $c['prerequisiteLevel'];
            $prereq = ($pr === null) ? max(0, $level - 1) : (int)$pr;
            $unlocked = ($level <= 0) ? true : Challenge::hasCompletedLevel($db_connection, $studentId, $prereq);
            $done = isset($completedSet[$cid]);
            $avg = $c['avgRating'] !== null ? (string)$c['avgRating'] : null;
        ?>
            <div class="col-12 col-lg-6">
                <div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars((string)($c['title'] ?? '')) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars((string)($c['category'] ?? '')) ?></div>
                        </div>
                        <span class="badge bg-dark"><?= (int)($c['points'] ?? 10) ?> pts</span>
                    </div>

                    <?php if (!empty($c['description'])): ?>
                        <div class="mt-2 text-muted small"><?= htmlspecialchars((string)$c['description']) ?></div>
                    <?php endif; ?>

                    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                        <?php if ($done): ?>
                            <span class="badge bg-success">Completed</span>
                        <?php elseif ($unlocked): ?>
                            <span class="badge bg-primary">Unlocked</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Locked</span>
                            <span class="text-muted small">Requires level <?= (int)$prereq ?> completion</span>
                        <?php endif; ?>

                        <?php if ($avg !== null): ?>
                            <span class="text-muted small">Avg rating: <?= htmlspecialchars($avg) ?>/5</span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3 d-flex justify-content-end gap-2">
                        <?php if (!$done && $unlocked): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="challengeId" value="<?= htmlspecialchars($cid) ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Complete</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($done): ?>
                            <form method="post" class="d-inline d-flex align-items-center gap-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" value="rate">
                                <input type="hidden" name="challengeId" value="<?= htmlspecialchars($cid) ?>">
                                <select class="form-select form-select-sm" name="rating" aria-label="Rate">
                                    <option value="">Rate…</option>
                                    <?php for ($i=1; $i<=5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Send</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach;
        echo '</div></div></div>';
        ?>
    <?php endif; ?>
</main>

<script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

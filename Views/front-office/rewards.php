<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Reward.php';
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
        if ($action === 'redeem') {
            $rewardId = trim((string)($_POST['rewardId'] ?? ''));
            $reward = $rewardId !== '' ? Reward::getById($db_connection, $rewardId) : null;
            if (!$reward) {
                $error = 'Reward not found';
            } else {
                $res = Reward::redeemOrRequest($db_connection, $reward, $studentId);
                if (($res['success'] ?? false) === true) {
                    $message = ($res['status'] ?? '') === 'pending'
                        ? 'Request sent for teacher approval.'
                        : 'Reward redeemed!';
                } else {
                    $error = (string)($res['error'] ?? 'Failed');
                }
            }
        }
    }
}

$balance = Points::getBalance($db_connection, $studentId);
$tiers = Points::tierForPoints($balance);
$rewards = Reward::listAll($db_connection);
$history = Reward::listForStudent($db_connection, $studentId);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Rewards | Student</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="front-rewards">
<?php include __DIR__ . '/../partials/navbar_student.php'; ?>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Rewards Store</h1>
        <div class="text-muted small">Tier: <strong><?= htmlspecialchars($tiers['current']['name']) ?></strong></div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Available Rewards</strong></div>
                <div class="card-body">
                    <?php if (!$rewards): ?>
                        <div class="text-muted">No rewards available yet.</div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($rewards as $r): ?>
                                <?php
                                $cost = (int)($r['costPoints'] ?? 0);
                                $stock = $r['stock'];
                                $shortBy = $cost - $balance;
                                $canRedeem = ($shortBy <= 0);
                                $canRequest = ($shortBy > 0 && $shortBy <= 10);
                                $out = ($stock !== null && (int)$stock <= 0);
                                ?>
                                <div class="col-12 col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars((string)($r['name'] ?? '')) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars((string)($r['category'] ?? '')) ?></div>
                                            </div>
                                            <span class="badge bg-dark"><?= $cost ?> pts</span>
                                        </div>

                                        <div class="mt-2 d-flex flex-wrap gap-2">
                                            <?php if ($out): ?>
                                                <span class="badge bg-secondary">Out of stock</span>
                                            <?php elseif ($canRedeem): ?>
                                                <span class="badge bg-success">Ready</span>
                                            <?php elseif ($canRequest): ?>
                                                <span class="badge bg-warning text-dark">Short by <?= (int)$shortBy ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Need <?= (int)$shortBy ?> more</span>
                                            <?php endif; ?>

                                            <?php if (!empty($r['tierRequired'])): ?>
                                                <span class="badge bg-info text-dark">Tier ≥ <?= (int)$r['tierRequired'] ?></span>
                                            <?php endif; ?>

                                            <?php if ($stock !== null): ?>
                                                <span class="text-muted small">Stock: <?= (int)$stock ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-3 d-flex justify-content-end">
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="action" value="redeem">
                                                <input type="hidden" name="rewardId" value="<?= htmlspecialchars((string)($r['id'] ?? '')) ?>">
                                                <button type="submit" class="btn btn-sm btn-primary" <?= ($out || (!$canRedeem && !$canRequest)) ? 'disabled' : '' ?>>
                                                    <?= $canRequest ? 'Request' : 'Redeem' ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><strong>Your Balance</strong></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-muted">Points</div>
                        <div class="fw-semibold"><?= (int)$balance ?></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <div class="text-muted">Tier</div>
                        <div class="fw-semibold"><?= htmlspecialchars($tiers['current']['name']) ?></div>
                    </div>
                    <?php if ($tiers['next']): ?>
                        <div class="text-muted small mt-2">Next: <?= htmlspecialchars($tiers['next']['name']) ?> at <?= (int)$tiers['next']['min'] ?> pts</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header"><strong>Redemption History</strong></div>
                <div class="card-body">
                    <?php if (!$history): ?>
                        <div class="text-muted">No redemptions yet.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($history as $h): ?>
                                <?php
                                $st = (string)($h['status'] ?? '');
                                $badge = $st === 'redeemed' ? 'bg-success' : ($st === 'pending' ? 'bg-warning text-dark' : 'bg-danger');
                                ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars((string)($h['rewardName'] ?? '')) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars((string)($h['requestedAt'] ?? '')) ?></div>
                                        <?php if (!empty($h['note'])): ?>
                                            <div class="text-muted small">Note: <?= htmlspecialchars((string)$h['note']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($st) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

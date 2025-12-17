<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Reward.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

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
        if ($action === 'create') {
            $name = trim((string)($_POST['name'] ?? ''));
            $category = trim((string)($_POST['category'] ?? ''));
            $cost = (int)($_POST['costPoints'] ?? 0);
            $tier = ($_POST['tierRequired'] ?? '');
            $tier = $tier === '' ? null : (int)$tier;
            $stock = ($_POST['stock'] ?? '');
            $stock = $stock === '' ? null : (int)$stock;

            if ($name === '' || $category === '' || $cost <= 0) {
                $error = 'Name, category, and cost are required.';
            } else {
                $ok = Reward::create($db_connection, [
                    'id' => 'rw_' . bin2hex(random_bytes(8)),
                    'name' => $name,
                    'category' => $category,
                    'costPoints' => $cost,
                    'tierRequired' => $tier,
                    'stock' => $stock,
                ]);
                $message = $ok ? 'Reward created.' : 'Failed to create reward.';
            }
        } elseif ($action === 'update') {
            $id = trim((string)($_POST['id'] ?? ''));
            $name = trim((string)($_POST['name'] ?? ''));
            $category = trim((string)($_POST['category'] ?? ''));
            $cost = (int)($_POST['costPoints'] ?? 0);
            $tier = ($_POST['tierRequired'] ?? '');
            $tier = $tier === '' ? null : (int)$tier;
            $stock = ($_POST['stock'] ?? '');
            $stock = $stock === '' ? null : (int)$stock;

            if ($id === '' || $name === '' || $category === '' || $cost <= 0) {
                $error = 'Missing fields.';
            } else {
                $ok = Reward::update($db_connection, $id, [
                    'name' => $name,
                    'category' => $category,
                    'costPoints' => $cost,
                    'tierRequired' => $tier,
                    'stock' => $stock,
                ]);
                $message = $ok ? 'Reward updated.' : 'Failed to update reward.';
            }
        } elseif ($action === 'delete') {
            $id = trim((string)($_POST['id'] ?? ''));
            if ($id !== '') {
                $ok = Reward::delete($db_connection, $id);
                $message = $ok ? 'Reward deleted.' : 'Failed to delete reward.';
            }
        }
    }
}

$rewards = Reward::listAll($db_connection);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Rewards | Admin</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-rewards">
<?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

<main class="container py-4">
    <h1 class="h4 mb-3">Manage Rewards</h1>

    <?php if ($message): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-header"><strong>Create Reward</strong></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="create">
                <div class="col-12 col-md-6">
                    <label class="form-label">Name</label>
                    <input class="form-control" name="name" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Category</label>
                    <input class="form-control" name="category" placeholder="Badges" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Cost (pts)</label>
                    <input class="form-control" type="number" min="1" name="costPoints" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Tier Required</label>
                    <input class="form-control" type="number" min="1" name="tierRequired" placeholder="(opt)">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Stock</label>
                    <input class="form-control" type="number" min="0" name="stock" placeholder="(blank=unlimited)">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header"><strong>All Rewards</strong></div>
        <div class="card-body">
            <?php if (!$rewards): ?>
                <div class="text-muted">No rewards found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Cost</th>
                            <th>Tier</th>
                            <th>Stock</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rewards as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string)($r['name'] ?? '')) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars((string)($r['category'] ?? '')) ?></td>
                                <td><?= (int)($r['costPoints'] ?? 0) ?> pts</td>
                                <td class="text-muted small"><?= htmlspecialchars((string)($r['tierRequired'] ?? '')) ?></td>
                                <td class="text-muted small"><?= $r['stock'] === null ? '∞' : (int)$r['stock'] ?></td>
                                <td class="text-end">
                                    <details class="d-inline">
                                        <summary class="btn btn-sm btn-outline-secondary">Edit</summary>
                                        <div class="border rounded p-3 mt-2" style="min-width: 320px;">
                                            <form method="post" class="row g-2">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($r['id'] ?? '')) ?>">
                                                <div class="col-12">
                                                    <label class="form-label">Name</label>
                                                    <input class="form-control" name="name" value="<?= htmlspecialchars((string)($r['name'] ?? '')) ?>" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Category</label>
                                                    <input class="form-control" name="category" value="<?= htmlspecialchars((string)($r['category'] ?? '')) ?>" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Cost</label>
                                                    <input class="form-control" type="number" min="1" name="costPoints" value="<?= (int)($r['costPoints'] ?? 0) ?>" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Tier</label>
                                                    <input class="form-control" type="number" min="1" name="tierRequired" value="<?= htmlspecialchars((string)($r['tierRequired'] ?? '')) ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Stock</label>
                                                    <input class="form-control" type="number" min="0" name="stock" value="<?= htmlspecialchars((string)($r['stock'] ?? '')) ?>" placeholder="(blank=unlimited)">
                                                </div>
                                                <div class="col-12 d-flex justify-content-end">
                                                    <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                                </div>
                                            </form>
                                            <form method="post" class="mt-2" onsubmit="return confirm('Delete this reward?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($r['id'] ?? '')) ?>">
                                                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </details>
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

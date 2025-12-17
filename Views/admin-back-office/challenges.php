<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
require_once __DIR__ . '/../../Models/Challenge.php';

$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

$adminId = (string)($_SESSION['user']['id'] ?? $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 'admin');
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
            $title = trim((string)($_POST['title'] ?? ''));
            $level = (int)($_POST['level'] ?? 0);
            $points = (int)($_POST['points'] ?? 10);
            $category = trim((string)($_POST['category'] ?? ''));
            $desc = trim((string)($_POST['description'] ?? ''));
            $pr = ($_POST['prerequisiteLevel'] ?? '');
            $pr = $pr === '' ? null : (int)$pr;
            $createdBy = trim((string)($_POST['createdBy'] ?? ''));

            if ($title === '' || $createdBy === '') {
                $error = 'Title and createdBy (teacher id) are required.';
            } else {
                $ok = Challenge::create($db_connection, [
                    'id' => 'ch_' . bin2hex(random_bytes(8)),
                    'title' => $title,
                    'description' => $desc,
                    'level' => max(0, $level),
                    'points' => max(1, $points),
                    'category' => $category !== '' ? $category : null,
                    'skillTags' => null,
                    'prerequisiteLevel' => $pr,
                    'createdBy' => $createdBy,
                ]);
                $message = $ok ? 'Challenge created.' : 'Failed to create challenge.';
            }
        } elseif ($action === 'update') {
            $id = trim((string)($_POST['id'] ?? ''));
            $title = trim((string)($_POST['title'] ?? ''));
            $level = (int)($_POST['level'] ?? 0);
            $points = (int)($_POST['points'] ?? 10);
            $category = trim((string)($_POST['category'] ?? ''));
            $desc = trim((string)($_POST['description'] ?? ''));
            $pr = ($_POST['prerequisiteLevel'] ?? '');
            $pr = $pr === '' ? null : (int)$pr;

            if ($id === '' || $title === '') {
                $error = 'Missing fields.';
            } else {
                $ok = Challenge::update($db_connection, $id, [
                    'title' => $title,
                    'description' => $desc,
                    'level' => max(0, $level),
                    'points' => max(1, $points),
                    'category' => $category !== '' ? $category : null,
                    'skillTags' => null,
                    'prerequisiteLevel' => $pr,
                ], null);
                $message = $ok ? 'Challenge updated.' : 'Failed to update challenge.';
            }
        } elseif ($action === 'delete') {
            $id = trim((string)($_POST['id'] ?? ''));
            if ($id !== '') {
                $ok = Challenge::delete($db_connection, $id, null);
                $message = $ok ? 'Challenge deleted.' : 'Failed to delete challenge.';
            }
        }
    }
}

$all = Challenge::listAll($db_connection);
$teachers = $db_connection->query('SELECT id, fullName, username FROM teachers ORDER BY fullName ASC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Challenges | Admin</title>
    <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="../../shared-assets/css/global.css" rel="stylesheet">
    <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-challenges">
<?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

<main class="container py-4">
    <h1 class="h4 mb-3">Manage Challenges</h1>

    <?php if ($message): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-header"><strong>Create Challenge</strong></div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="create">
                <div class="col-12 col-md-6">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="title" required>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Level</label>
                    <input class="form-control" type="number" min="0" max="10" name="level" value="0">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Points</label>
                    <input class="form-control" type="number" min="1" name="points" value="10">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Prereq</label>
                    <input class="form-control" type="number" min="0" max="10" name="prerequisiteLevel" placeholder="(opt)">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Category</label>
                    <input class="form-control" name="category" placeholder="e.g., Science">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" name="createdBy" required>
                        <option value="">Select teacher…</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= htmlspecialchars((string)$t['id']) ?>"><?= htmlspecialchars((string)($t['fullName'] ?? $t['username'] ?? $t['id'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="2" name="description"></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header"><strong>All Challenges</strong></div>
        <div class="card-body">
            <?php if (!$all): ?>
                <div class="text-muted">No challenges found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Level</th>
                            <th>Points</th>
                            <th>Teacher</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($all as $c): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string)($c['title'] ?? '')) ?></td>
                                <td><?= (int)($c['level'] ?? 0) ?></td>
                                <td><?= (int)($c['points'] ?? 0) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars((string)($c['teacherName'] ?? $c['createdBy'] ?? '')) ?></td>
                                <td class="text-end">
                                    <details class="d-inline">
                                        <summary class="btn btn-sm btn-outline-secondary">Edit</summary>
                                        <div class="border rounded p-3 mt-2" style="min-width: 320px;">
                                            <form method="post" class="row g-2">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($c['id'] ?? '')) ?>">
                                                <div class="col-12">
                                                    <label class="form-label">Title</label>
                                                    <input class="form-control" name="title" value="<?= htmlspecialchars((string)($c['title'] ?? '')) ?>" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Level</label>
                                                    <input class="form-control" type="number" min="0" max="10" name="level" value="<?= (int)($c['level'] ?? 0) ?>">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Points</label>
                                                    <input class="form-control" type="number" min="1" name="points" value="<?= (int)($c['points'] ?? 10) ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Category</label>
                                                    <input class="form-control" name="category" value="<?= htmlspecialchars((string)($c['category'] ?? '')) ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Prerequisite Level</label>
                                                    <input class="form-control" type="number" min="0" max="10" name="prerequisiteLevel" value="<?= htmlspecialchars((string)($c['prerequisiteLevel'] ?? '')) ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" rows="2" name="description"><?= htmlspecialchars((string)($c['description'] ?? '')) ?></textarea>
                                                </div>
                                                <div class="col-12 d-flex justify-content-end gap-2">
                                                    <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                                </div>
                                            </form>
                                            <form method="post" class="mt-2" onsubmit="return confirm('Delete this challenge?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($c['id'] ?? '')) ?>">
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

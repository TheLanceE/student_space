<?php
require_once __DIR__ . '/../../Controllers/auth_check.php';
$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}
$csrfToken = SessionManager::getCSRFToken();
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Settings | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
</head>
<body data-page="admin-settings">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h1 class="h5">Platform Settings (Demo)</h1>
 <form id="settingsForm" class="mt-3">
 <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="form-label">Suggestion Engine: Inactivity Days</label>
 <input id="inactDays" type="number" min="1" value="7" class="form-control" />
 </div>
 <div class="col-md-6">
 <label class="form-label">Report Export Prefix</label>
 <input id="exportPrefix" type="text" value="edumind" class="form-control" />
 </div>
 </div>
 <div class="mt-3">
 <button class="btn btn-primary" type="submit">Save</button>
 </div>
 </form>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <!-- Deprecated client-side admin data scripts removed for consistency -->
</body>
</html>
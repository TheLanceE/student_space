<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Roles | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-roles">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h1 class="h5">Role Assignment</h1>
 <p class="text-muted">Simple role assignment for demo data.</p>
 <div class="row g-3 align-items-end">
 <div class="col-md-6"><label class="form-label">User</label><select id="userId" class="form-select"></select></div>
 <div class="col-md-3"><label class="form-label">Role</label><select id="role" class="form-select"><option>student</option><option>teacher</option><option>admin</option></select></div>
 <div class="col-md-3"><button id="assignBtn" class="btn btn-primary w-100">Assign</button></div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/database.js"></script>
 <script src="assets/js/storage.js"></script>
 <script src="assets/js/data-admin.js"></script>
 <script src="assets/js/auth-admin.js"></script>
 <script src="assets/js/pages.js"></script>
</body>
</html>
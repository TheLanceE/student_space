<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Logs | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/vendor/bootstrap-icons.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
</head>
<body data-page="admin-logs">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h1 class="h5">System Logs</h1>
 <div id="logTable"></div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <!-- Deprecated client-side admin data scripts removed for consistency -->
</body>
</html>
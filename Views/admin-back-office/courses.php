<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Courses | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-courses">
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

 <main class="container py-4">
 <div class="card shadow-sm">
 <div class="card-body">
 <h1 class="h5">Course Approvals</h1>
 <div id="courseTable"></div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <!-- Deprecated client-side admin data scripts removed for consistency -->
</body>
</html>
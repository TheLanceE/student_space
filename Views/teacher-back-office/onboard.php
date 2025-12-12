<?php
require_once __DIR__ . '/../../Controllers/config.php';

// Verify logged in and role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login.php?error=not_logged_in');
  exit;
}

if (($_SESSION['role'] ?? 'teacher') !== 'teacher') {
  header('Location: login.php?error=wrong_role');
  exit;
}

$fullName = $_SESSION['google_name'] ?? $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Complete Your Profile | Teacher</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
 <nav class="navbar navbar-expand-lg navbar-dark teacher-nav">
  <div class="container-fluid">
   <a class="navbar-brand" href="dashboard.php"><i class="bi bi-briefcase"></i> Welcome</a>
  </div>
 </nav>
 <main class="container py-4">
  <div class="row justify-content-center">
   <div class="col-12 col-md-8 col-lg-6">
    <div class="card shadow-sm">
     <div class="card-header">
      <h1 class="h5 mb-0">Complete Your Profile</h1>
     </div>
     <div class="card-body">
      <form method="POST" action="../../Controllers/oauth_onboard.php">
       <input type="hidden" name="role" value="teacher" />
       <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>" required />
       </div>
       <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required />
       </div>
      <div class="mb-3">
       <label class="form-label">Mobile</label>
       <input class="form-control" name="mobile" />
      </div>
      <div class="mb-3">
       <label class="form-label">Address</label>
       <input class="form-control" name="address" />
      </div>
      <div class="mb-3">
       <label class="form-label">Department / Specialty</label>
       <input class="form-control" name="specialty" />
      </div>
       <div class="d-flex justify-content-end gap-2">
        <a class="btn btn-outline-secondary" href="dashboard.php">Skip</a>
        <button class="btn btn-primary" type="submit">Save & Continue</button>
       </div>
      </form>
     </div>
    </div>
   </div>
  </div>
 </main>
 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

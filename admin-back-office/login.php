<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Admin Login</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;" data-page="admin-login">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Admin Login</h1>
            <form id="loginForm" class="needs-validation" novalidate>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" class="form-control" required>
                <div class="invalid-feedback">Please enter your username.</div>
              </div>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Log In</button>
              </div>
            </form>
          </div>
        </div>
        <p class="text-center mt-3 small text-muted">Demo only · stored in localStorage</p>
      </div>
    </div>
  </div>

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/data-admin.js"></script>
  <script src="assets/js/auth-admin.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>
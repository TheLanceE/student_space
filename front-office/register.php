<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Register</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="front-register" class="bg-light d-flex align-items-center" style="min-height: 100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Create Student Account</h1>
            <form id="registerForm" class="needs-validation" novalidate>
              <div class="mb-3">
                <label for="regLogin" class="form-label">Login ID</label>
                <input type="text" id="regLogin" class="form-control" required>
                <div class="invalid-feedback">Please enter a login ID.</div>
              </div>
              <div class="mb-3">
                <label for="regFullName" class="form-label">Full Name</label>
                <input type="text" id="regFullName" class="form-control" required>
                <div class="invalid-feedback">Please enter your full name.</div>
              </div>
              <div class="mb-3">
                <label for="regEmail" class="form-label">Email</label>
                <input type="email" id="regEmail" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid email.</div>
              </div>
              <div class="mb-3">
                <label for="regMobile" class="form-label">Mobile Number</label>
                <input type="tel" id="regMobile" class="form-control" required>
                <div class="invalid-feedback">Please enter your mobile number.</div>
              </div>
              <div class="mb-3">
                <label for="regAddress" class="form-label">Address</label>
                <textarea id="regAddress" class="form-control" rows="2" required></textarea>
                <div class="invalid-feedback">Please enter your address.</div>
              </div>
              <div class="mb-3">
                <label for="regGrade" class="form-label">Grade Level</label>
                <select id="regGrade" class="form-select" required>
                  <option value="">Choose...</option>
                  <option>Grade 8</option>
                  <option>Grade 9</option>
                  <option>Grade 10</option>
                  <option>Grade 11</option>
                  <option>Grade 12</option>
                </select>
                <div class="invalid-feedback">Please select a grade level.</div>
              </div>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success">Create Account</button>
                <a class="btn btn-outline-secondary" href="login.php">Back to login</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>
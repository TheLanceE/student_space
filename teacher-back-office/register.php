<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Teacher Registration</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="teacher-register" class="bg-light d-flex align-items-center" style="min-height: 100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Create Teacher Account</h1>
            <form id="registerForm" class="needs-validation" novalidate>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="regLogin" class="form-label">Login ID</label>
                  <input type="text" id="regLogin" class="form-control" required>
                  <div class="invalid-feedback">Please enter a login ID.</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="regFullName" class="form-label">Full Name</label>
                  <input type="text" id="regFullName" class="form-control" required>
                  <div class="invalid-feedback">Please enter your full name.</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="regEmail" class="form-label">Email</label>
                  <input type="email" id="regEmail" class="form-control" required>
                  <div class="invalid-feedback">Please enter a valid email.</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="regMobile" class="form-label">Mobile Number</label>
                  <input type="tel" id="regMobile" class="form-control" required>
                  <div class="invalid-feedback">Please enter your mobile number.</div>
                </div>
              </div>
              <div class="mb-3">
                <label for="regAddress" class="form-label">Address</label>
                <textarea id="regAddress" class="form-control" rows="2" required></textarea>
                <div class="invalid-feedback">Please enter your address.</div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="regSubject" class="form-label">Subject Specialty</label>
                  <select id="regSubject" class="form-select" required>
                    <option value="">Choose...</option>
                    <option>Mathematics</option>
                    <option>Science</option>
                    <option>English</option>
                    <option>History</option>
                    <option>Computer Science</option>
                    <option>Physics</option>
                    <option>Chemistry</option>
                    <option>Biology</option>
                    <option>Other</option>
                  </select>
                  <div class="invalid-feedback">Please select your subject.</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="regNationalId" class="form-label">National ID</label>
                  <input type="text" id="regNationalId" class="form-control" required>
                  <div class="invalid-feedback">Please enter your national ID.</div>
                </div>
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
  <script src="assets/js/auth-teacher.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>

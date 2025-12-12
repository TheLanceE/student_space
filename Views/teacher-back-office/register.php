<?php
require_once '../../Controllers/config.php';
$csrfToken = SessionManager::getCSRFToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Teacher Registration</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="teacher-register" class="bg-light d-flex align-items-center" style="min-height: 100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Create Teacher Account</h1>
            <form id="registerForm" class="needs-validation" novalidate method="POST" action="../../Controllers/register_handler.php">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="role" value="teacher">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="regLogin" class="form-label">Login ID</label>
                  <input type="text" id="regLogin" name="username" class="form-control" pattern="^[a-zA-Z0-9_]{3,20}$" required>
                  <div class="invalid-feedback">Login must be 3-20 characters (letters, numbers, underscore).</div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
                <div class="mb-3">
                  <label for="regPassword" class="form-label">Password</label>
                  <input type="password" id="regPassword" name="password" class="form-control" minlength="6" required>
                  <div class="invalid-feedback">Password must be at least 6 characters.</div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="regFullName" class="form-label">Full Name</label>
                  <input type="text" id="regFullName" name="fullName" class="form-control" pattern="^[a-zA-Z\s]{2,50}$" required>
                  <div class="invalid-feedback">Full name must be 2-50 characters (letters only).</div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="regEmail" class="form-label">Email</label>
                  <input type="email" id="regEmail" name="email" class="form-control" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" required>
                  <div class="invalid-feedback">Please enter a valid email address.</div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="regMobile" class="form-label">Mobile Number</label>
                  <input type="tel" id="regMobile" name="mobile" class="form-control" pattern="^[+]?[0-9]{10,15}$" required>
                  <div class="invalid-feedback">Phone must be 10-15 digits.</div>
                  <div class="valid-feedback">Looks good!</div>
                </div>
              </div>
              <div class="mb-3">
                <label for="regAddress" class="form-label">Address</label>
                <textarea id="regAddress" name="address" class="form-control" rows="2" minlength="10" maxlength="200" required></textarea>
                <div class="invalid-feedback">Address must be 10-200 characters.</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="regSubject" class="form-label">Subject Specialty</label>
                  <select id="regSubject" name="subject" class="form-select" required>
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
                  <div class="valid-feedback">Looks good!</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="regNationalId" class="form-label">National ID</label>
                  <input type="text" id="regNationalId" name="nationalId" class="form-control" pattern="^[A-Z0-9-]{5,20}$" required>
                  <div class="invalid-feedback">National ID must be 5-20 characters (uppercase, numbers, dashes).</div>
                  <div class="valid-feedback">Looks good!</div>
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

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth-teacher.js"></script>
  <script src="assets/js/pages.js"></script>
  <script>
    // Real-time validation feedback
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('registerForm');
      const inputs = form.querySelectorAll('input, textarea, select');
      inputs.forEach(input => {
        input.addEventListener('blur', () => {
          if (input.checkValidity()) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
          } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
          }
        });
        input.addEventListener('input', () => {
          if (input.classList.contains('is-invalid') && input.checkValidity()) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
          }
        });
      });
    });
  </script>
</body>
</html>


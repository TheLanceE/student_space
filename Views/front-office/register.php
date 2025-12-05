<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduMind+ | Register</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="front-register" class="bg-light d-flex align-items-center" style="min-height: 100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Create Student Account</h1>
            <form id="registerForm" class="needs-validation" novalidate method="POST" action="../../Controllers/register_handler.php">
              <div class="mb-3">
                <label for="regLogin" class="form-label">Login ID</label>
                <input type="text" id="regLogin" name="username" class="form-control" pattern="^[a-zA-Z0-9_]{3,20}$" required>
                <div class="invalid-feedback">Login must be 3-20 characters (letters, numbers, underscore only).</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="mb-3">
                <label for="regPassword" class="form-label">Password</label>
                <input type="password" id="regPassword" name="password" class="form-control" minlength="6" required>
                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="mb-3">
                <label for="regFullName" class="form-label">Full Name</label>
                <input type="text" id="regFullName" name="fullName" class="form-control" pattern="^[a-zA-Z\s]{2,50}$" required>
                <div class="invalid-feedback">Please enter a valid full name (2-50 characters, letters only).</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="mb-3">
                <label for="regEmail" class="form-label">Email</label>
                <input type="email" id="regEmail" name="email" class="form-control" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" required>
                <div class="invalid-feedback">Please enter a valid email address.</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="mb-3">
                <label for="regMobile" class="form-label">Mobile Number</label>
                <input type="tel" id="regMobile" name="mobile" class="form-control" pattern="^[+]?[0-9]{10,15}$" required>
                <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="mb-3">
                <label for="regAddress" class="form-label">Address</label>
                <textarea id="regAddress" name="address" class="form-control" rows="2" minlength="10" maxlength="200" required></textarea>
                <div class="invalid-feedback">Address must be 10-200 characters.</div>
                <div class="valid-feedback">Looks good!</div>
              </div>
              <div class="mb-3">
                <label for="regGrade" class="form-label">Grade Level</label>
                <select id="regGrade" name="gradeLevel" class="form-select" required>
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

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="assets/js/auth.js"></script>
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

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Users | Admin</title>
  <link href="../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="admin-users">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="roles.php">Roles</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
        </ul>
        <button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h6">Add User</h2>
            <form id="addUserForm">
              <div class="mb-2"><label class="form-label">Username</label><input id="uName" class="form-control" required></div>
              <div class="mb-2"><label class="form-label">Role</label><select id="uRole" class="form-select" onchange="toggleTeacherFields(this.value)"><option value="student">student</option><option value="teacher">teacher</option><option value="admin">admin</option></select></div>
              <div id="teacherFields" style="display:none;">
                <div class="mb-2"><label class="form-label">Email</label><input id="uEmail" class="form-control" type="email"></div>
                <div class="mb-2"><label class="form-label">Mobile Number</label><input id="uMobile" class="form-control" type="tel"></div>
                <div class="mb-2"><label class="form-label">Address</label><textarea id="uAddress" class="form-control" rows="2"></textarea></div>
                <div class="mb-2"><label class="form-label">Subject Specialty</label><input id="uSubject" class="form-control"></div>
                <div class="mb-2"><label class="form-label">National ID</label><input id="uNationalId" class="form-control"></div>
              </div>
              <button class="btn btn-primary btn-sm" type="submit">Add</button>
            </form>
            <script>
              function toggleTeacherFields(role){
                const fields = document.getElementById('teacherFields');
                fields.style.display = role === 'teacher' ? 'block' : 'none';
                const inputs = fields.querySelectorAll('input, textarea');
                inputs.forEach(inp => inp.required = role === 'teacher');
              }
            </script>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h6">All Users</h2>
            <div id="userTable"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/data-admin.js"></script>
  <script src="assets/js/auth-admin.js"></script>
  <script src="assets/js/pages.js"></script>
</body>
</html>
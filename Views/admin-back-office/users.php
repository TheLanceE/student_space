<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Users | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-users">
 <nav class="navbar navbar-expand-lg navbar-dark admin-nav">
 <div class="container-fluid">
 <a class="navbar-brand" href="dashboard.php"><i class="bi bi-shield-check"></i> EduMind+ Admin</a>
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
 <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="nav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
 <li class="nav-item"><a class="nav-link" href="projects.php"><i class="bi bi-folder me-1"></i>Projects</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php"><i class="bi bi-person-badge me-1"></i>Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php"><i class="bi bi-journal-text me-1"></i>Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
 </ul>
 <button id="logoutBtn" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
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
 <?php
 try {
		 $pdo = new PDO('mysql:host=localhost;dbname=edumind;charset=utf8mb4', 'root', '');
		 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 $admins = $pdo->query("SELECT username, name, createdAt, lastLoginAt FROM admins ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
		 $teachers = $pdo->query("SELECT username, fullName, specialty, email, createdAt, lastLoginAt FROM teachers ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
		 $students = $pdo->query("SELECT username, fullName, email, gradeLevel, createdAt, lastLoginAt FROM students ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
 } catch (Exception $e) {
		 $admins = $teachers = $students = [];
		 echo '<div class="alert alert-danger">Database error loading users.</div>';
 }
 ?>
 <div class="mb-3">
	 <span class="badge bg-primary me-2">Admins: <?php echo count($admins); ?></span>
	 <span class="badge bg-success me-2">Teachers: <?php echo count($teachers); ?></span>
	 <span class="badge bg-warning text-dark">Students: <?php echo count($students); ?></span>
 </div>
 <div class="table-responsive">
	 <table class="table table-sm table-striped align-middle">
		 <thead>
			 <tr><th>Role</th><th>Username</th><th>Name</th><th>Extra</th><th>Created</th><th>Last Login</th></tr>
		 </thead>
		 <tbody>
			 <?php foreach ($admins as $a): ?>
				 <tr>
					 <td><span class="badge bg-primary">Admin</span></td>
					 <td><?php echo htmlspecialchars($a['username']); ?></td>
					 <td><?php echo htmlspecialchars($a['name'] ?? ''); ?></td>
					 <td>-</td>
					 <td><?php echo htmlspecialchars($a['createdAt'] ?? ''); ?></td>
					 <td><?php echo htmlspecialchars($a['lastLoginAt'] ?? ''); ?></td>
				 </tr>
			 <?php endforeach; ?>
			 <?php foreach ($teachers as $t): ?>
				 <tr>
					 <td><span class="badge bg-success">Teacher</span></td>
					 <td><?php echo htmlspecialchars($t['username']); ?></td>
					 <td><?php echo htmlspecialchars($t['fullName'] ?? ''); ?></td>
					 <td><?php echo htmlspecialchars(($t['specialty'] ?? '') . ($t['email'] ? ' · ' . $t['email'] : '')); ?></td>
					 <td><?php echo htmlspecialchars($t['createdAt'] ?? ''); ?></td>
					 <td><?php echo htmlspecialchars($t['lastLoginAt'] ?? ''); ?></td>
				 </tr>
			 <?php endforeach; ?>
			 <?php foreach ($students as $s): ?>
				 <tr>
					 <td><span class="badge bg-warning text-dark">Student</span></td>
					 <td><?php echo htmlspecialchars($s['username']); ?></td>
					 <td><?php echo htmlspecialchars($s['fullName'] ?? ''); ?></td>
					 <td><?php echo htmlspecialchars(($s['gradeLevel'] ?? '') . ($s['email'] ? ' · ' . $s['email'] : '')); ?></td>
					 <td><?php echo htmlspecialchars($s['createdAt'] ?? ''); ?></td>
					 <td><?php echo htmlspecialchars($s['lastLoginAt'] ?? ''); ?></td>
				 </tr>
			 <?php endforeach; ?>
		 </tbody>
	 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <!-- Removed old JS data layer; server-side rendering used above -->
</body>
</html>
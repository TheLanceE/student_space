<?php 
require_once '../../Controllers/auth_check.php';
$role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}
?>
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
 <?php include __DIR__ . '/../partials/navbar_admin.php'; ?>

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

 <hr>
 <h2 class="h6">Send Invite</h2>
 <div class="d-flex flex-column gap-2">
	 <input id="inviteEmail" type="email" class="form-control form-control-sm" placeholder="email@example.com">
	 <select id="inviteRole" class="form-select form-select-sm">
		 <option value="student" selected>student</option>
		 <option value="teacher">teacher</option>
	 </select>
	 <div class="d-flex gap-2">
		 <button id="sendInvite" type="button" class="btn btn-outline-primary btn-sm">Send invite link</button>
		 <button id="copyInvite" type="button" class="btn btn-outline-secondary btn-sm">Copy link</button>
	 </div>
	 <small class="text-muted">Creates a link to registration with email prefilled.</small>
	 <div id="inviteStatus" class="text-success small" style="display:none;"></div>
 </div>
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
 <div class="d-flex justify-content-between align-items-center mb-2">
	 <h2 class="h6 mb-0">All Users</h2>
	 <div class="d-flex gap-2">
		 <button id="selectAll" class="btn btn-outline-secondary btn-sm">Select All</button>
		 <button id="deleteSelected" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete Selected</button>
	 </div>
 </div>
 <?php
 // Ensure CSRF exists
 if (!isset($_SESSION['csrf_token'])) {
     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
 }

 try {
	 $admins = $db_connection->query("SELECT id, username, name, createdAt, lastLoginAt FROM admins WHERE deleted_at IS NULL OR deleted_at IS NULL ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
	 $teachers = $db_connection->query("SELECT id, username, fullName, specialty, email, createdAt, lastLoginAt FROM teachers WHERE deleted_at IS NULL ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
	 $students = $db_connection->query("SELECT id, username, fullName, email, gradeLevel, createdAt, lastLoginAt FROM students WHERE deleted_at IS NULL ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
 } catch (Exception $e) {
	 $admins = $teachers = $students = [];
	 echo '<div class="alert alert-danger">Database error loading users.</div>';
 }
 ?>
 <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
	 <div>
		 <span class="badge bg-primary me-2">Admins: <?php echo count($admins); ?></span>
		 <span class="badge bg-success me-2">Teachers: <?php echo count($teachers); ?></span>
		 <span class="badge bg-warning text-dark">Students: <?php echo count($students); ?></span>
	 </div>
	 <div class="d-flex gap-2 align-items-center">
		 <select id="roleFilter" class="form-select form-select-sm" style="width:auto;">
			 <option value="all">All Roles</option>
			 <option value="admin">Admins</option>
			 <option value="teacher">Teachers</option>
			 <option value="student">Students</option>
		 </select>
		 <input type="search" id="userSearch" class="form-control form-control-sm" placeholder="Search users..." style="width:180px;">
	 </div>
 </div>
 <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
	 <table class="table table-sm table-striped align-middle" id="usersTable">
		 <thead>
			 <tr>
			 	<th style="width:32px;"></th>
			 	<th data-sort="text">Role</th>
			 	<th data-sort="text">Username</th>
			 	<th data-sort="text">Name</th>
			 	<th data-sort="text">Extra</th>
			 	<th data-sort="date">Created</th>
			 	<th data-sort="date">Last Login</th>
			 </tr>
		 </thead>
		 <tbody>
			 <?php foreach ($admins as $a): ?>
				 <tr>
					 <td><input type="checkbox" class="user-select" data-role="admin" data-id="<?php echo htmlspecialchars($a['id']); ?>"></td>
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
					 <td><input type="checkbox" class="user-select" data-role="teacher" data-id="<?php echo htmlspecialchars($t['id']); ?>"></td>
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
					 <td><input type="checkbox" class="user-select" data-role="student" data-id="<?php echo htmlspecialchars($s['id']); ?>"></td>
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
 <script src="../../shared-assets/js/admin-modern.js"></script>
 <script>
	const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';

	 // Bulk selection and deletion
	 document.getElementById('selectAll').addEventListener('click', function(){
		 document.querySelectorAll('.user-select').forEach(cb => cb.checked = true);
	 });
	 document.getElementById('deleteSelected').addEventListener('click', async function(){
		 const selected = Array.from(document.querySelectorAll('.user-select:checked'))
			 .map(cb => ({ role: cb.dataset.role, id: cb.dataset.id }));
		 if (selected.length === 0) { return; }
		 if (!confirm(`Delete ${selected.length} selected user(s)?`)) return;
		 
		 // Group by role since API expects same role per request
		 const byRole = selected.reduce((acc, u) => {
			 if (!acc[u.role]) acc[u.role] = [];
			 acc[u.role].push(u.id);
			 return acc;
		 }, {});
		 
		 try {
			 let allSuccess = true;
			 for (const [role, ids] of Object.entries(byRole)) {
				 const res = await fetch('../../Controllers/AdminApiController.php?path=/users/bulk-delete', {
					 method: 'POST',
					 headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
					 body: JSON.stringify({ role, ids })
				 });
				 const data = await res.json();
				 if (!data.success) {
					 console.error('Delete failed for role:', role, data);
					 allSuccess = false;
				 }
			 }
			 if (allSuccess) { 
				 location.reload(); 
			 } else { 
				 alert('Some deletions failed'); 
			 }
		 } catch (e) {
			 console.error('Delete error:', e);
			 alert('Network error during delete');
		 }
	 });

	// Invite links
	const inviteEmail = document.getElementById('inviteEmail');
	const inviteRole = document.getElementById('inviteRole');
	const inviteStatus = document.getElementById('inviteStatus');
	function buildInviteLink() {
	 	 const email = inviteEmail.value.trim();
	 	 const role = inviteRole.value;
	 	 if (!email) return null;
	 	 const base = `${window.location.origin}/edumind/Views/front-office/register.php`;
	 	 const params = new URLSearchParams({ prefill_email: email, role });
	 	 return `${base}?${params.toString()}`;
	}

	async function copyInvite() {
	 	 const link = buildInviteLink();
	 	 if (!link) return;
	 	 await navigator.clipboard.writeText(link);
	 	 inviteStatus.style.display = 'block';
	 	 inviteStatus.textContent = 'Link copied to clipboard';
	 	 setTimeout(() => inviteStatus.style.display = 'none', 2000);
	}

	document.getElementById('copyInvite').addEventListener('click', copyInvite);
	document.getElementById('sendInvite').addEventListener('click', () => {
	 	 const link = buildInviteLink();
	 	 if (!link) return;
	 	 const email = inviteEmail.value.trim();
	 	 const subject = encodeURIComponent('You are invited to join EduMind+');
	 	 const body = encodeURIComponent(`Hi,\n\nClick the link to finish your account setup:\n${link}\n\nThis will prefill your email.`);
	 	 const mailto = `mailto:${encodeURIComponent(email)}?subject=${subject}&body=${body}`;
	 	 window.location.href = mailto;
	 	 inviteStatus.style.display = 'block';
	 	 inviteStatus.textContent = 'Invite link opened in mail client';
	 	 setTimeout(() => inviteStatus.style.display = 'none', 2000);
	});

	// Table sorting
	(function(){
	 const table = document.getElementById('usersTable');
	 const headers = table.querySelectorAll('th[data-sort]');
	 let sortState = {};
	 headers.forEach((th, idx) => {
	 	 th.style.cursor = 'pointer';
	 	 th.addEventListener('click', () => {
	 	 	 const type = th.dataset.sort;
	 	 	 const asc = !(sortState.idx === idx && sortState.asc);
	 	 	 sortState = { idx, asc };
	 	 	 const rows = Array.from(table.querySelectorAll('tbody tr'));
	 	 	 rows.sort((a,b) => {
	 	 	 	 const av = a.children[idx].innerText.trim();
	 	 	 	 const bv = b.children[idx].innerText.trim();
	 	 	 	 if (type === 'date') {
	 	 	 	 	 const ad = av ? Date.parse(av) : 0;
	 	 	 	 	 const bd = bv ? Date.parse(bv) : 0;
	 	 	 	 	 return asc ? ad - bd : bd - ad;
	 	 	 	 }
	 	 	 	 return asc ? av.localeCompare(bv) : bv.localeCompare(av);
	 	 	 });
	 	 	 const tbody = table.querySelector('tbody');
	 	 	 tbody.innerHTML = '';
	 	 	 rows.forEach(r => tbody.appendChild(r));
	 	 });
	 });
	})();

	// Search and filter functionality
	(function(){
	 const searchInput = document.getElementById('userSearch');
	 const roleFilter = document.getElementById('roleFilter');
	 const table = document.getElementById('usersTable');
	 
	 function applyFilters() {
		 const searchTerm = searchInput.value.toLowerCase().trim();
		 const selectedRole = roleFilter.value;
		 const rows = table.querySelectorAll('tbody tr');
		 let visibleCount = 0;
		 
		 rows.forEach(row => {
			 const roleCell = row.querySelector('td:nth-child(2) .badge');
			 const role = roleCell ? roleCell.textContent.toLowerCase() : '';
			 const text = row.textContent.toLowerCase();
			 
			 const matchesRole = selectedRole === 'all' || role === selectedRole;
			 const matchesSearch = !searchTerm || text.includes(searchTerm);
			 
			 if (matchesRole && matchesSearch) {
				 row.style.display = '';
				 visibleCount++;
			 } else {
				 row.style.display = 'none';
			 }
		 });
	 }
	 
	 searchInput.addEventListener('input', applyFilters);
	 roleFilter.addEventListener('change', applyFilters);
	})();
 </script>
 <!-- Removed old JS data layer; server-side rendering used above -->
</body>
</html>
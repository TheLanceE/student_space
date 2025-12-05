<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Quiz Management | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/global.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-quizzes">
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
 <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li>
 <li class="nav-item"><a class="nav-link" href="roles.php"><i class="bi bi-person-badge me-1"></i>Roles</a></li>
 <li class="nav-item"><a class="nav-link" href="courses.php"><i class="bi bi-book me-1"></i>Courses</a></li>
 <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
 <li class="nav-item"><a class="nav-link active" aria-current="page" href="quizzes.php"><i class="bi bi-question-circle me-1"></i>Quizzes</a></li>
 <li class="nav-item"><a class="nav-link" href="quiz-reports.php"><i class="bi bi-graph-up me-1"></i>Quiz Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="logs.php"><i class="bi bi-journal-text me-1"></i>Logs</a></li>
 <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-file-bar-graph me-1"></i>Reports</a></li>
 <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear me-1"></i>Settings</a></li>
 </ul>
 <a href="../../Controllers/logout_handler.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
 </div>
 </div>
 </nav>

 <main class="container py-4">
 <div class="d-flex justify-content-between align-items-center mb-4">
 <h1 class="h3">Quiz Management</h1>
 <div class="text-muted">Admin can view and delete quizzes</div>
 </div>

 <div class="row g-3 mb-4">
 <div class="col-12 col-lg-3">
 <div class="stat">
 <div class="text-muted small"><i class="bi bi-question-circle-fill me-1"></i>Total Quizzes</div>
 <div id="totalQuizzes" class="h4 mb-0">-</div>
 </div>
 </div>
 <div class="col-12 col-lg-3">
 <div class="stat">
 <div class="text-muted small"><i class="bi bi-check-circle me-1"></i>Active</div>
 <div id="activeQuizzes" class="h4 mb-0">-</div>
 </div>
 </div>
 <div class="col-12 col-lg-3">
 <div class="stat">
 <div class="text-muted small"><i class="bi bi-pencil me-1"></i>Draft</div>
 <div id="draftQuizzes" class="h4 mb-0">-</div>
 </div>
 </div>
 <div class="col-12 col-lg-3">
 <div class="stat">
 <div class="text-muted small"><i class="bi bi-people me-1"></i>Attempts</div>
 <div id="totalAttempts" class="h4 mb-0">-</div>
 </div>
 </div>
 </div>

 <div class="card shadow-sm">
 <div class="card-body">
 <h2 class="h6 mb-3">All Quizzes</h2>
 <div class="table-responsive">
 <table class="table table-hover align-middle">
 <thead>
 <tr>
 <th>Quiz Name</th>
 <th>Category</th>
 <th>Grade</th>
 <th>Questions</th>
 <th>Status</th>
 <th>Created By</th>
 <th>Created</th>
 <th>Attempts</th>
 <th>Actions</th>
 </tr>
 </thead>
 <tbody id="quizzesTable">
 <tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script>
 document.addEventListener('DOMContentLoaded', function() {
 loadQuizzes();
 });

 function loadQuizzes() {
 fetch('../../Quizzes/quizzes/controller/quizcontroller.php?action=getAllQuizzes')
 .then(res => res.json())
 .then(data => {
 const quizzes = data.quizzes || data.data || [];
 displayQuizzes(quizzes);
 updateStats(quizzes);
 })
 .catch(err => {
 document.getElementById('quizzesTable').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading quizzes</td></tr>';
 console.error('Error loading quizzes:', err);
 });
 }

 function displayQuizzes(quizzes) {
 const tbody = document.getElementById('quizzesTable');
 
 if (!quizzes || quizzes.length === 0) {
 tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No quizzes found</td></tr>';
 return;
 }

 let html = '';
 quizzes.forEach(quiz => {
 const statusBadge = quiz.status === 'active' 
 ? '<span class="badge bg-success">Active</span>' 
 : '<span class="badge bg-warning text-dark">Draft</span>';
 
 const categoryBadge = getCategoryBadge(quiz.category || '');
 
 html += `
 <tr>
 <td>${quiz.title || 'Untitled'}</td>
 <td>${categoryBadge}</td>
 <td>Grade ${quiz.gradeLevel || quiz.grade || ''}</td>
 <td>${quiz.questionCount || 0}</td>
 <td>${statusBadge}</td>
 <td>${quiz.createdByName || 'Teacher'}</td>
 <td>${quiz.createdDate || ''}</td>
 <td>${quiz.attempts || 0}</td>
 <td>
 <button class="btn btn-sm btn-danger" onclick="deleteQuiz('${quiz.id}')">
 <i class="bi bi-trash"></i> Delete
 </button>
 </td>
 </tr>
 `;
 });
 
 tbody.innerHTML = html;
 }

 function getCategoryBadge(category) {
 const categories = {
 'math': { name: 'Mathematics', class: 'bg-primary' },
 'science': { name: 'Science', class: 'bg-info' },
 'history': { name: 'History', class: 'bg-warning text-dark' },
 'english': { name: 'English', class: 'bg-success' },
 'geography': { name: 'Geography', class: 'bg-secondary' },
 'art': { name: 'Art', class: 'bg-danger' }
 };
 
 const cat = categories[category.toLowerCase()] || { name: category, class: 'bg-secondary' };
 return `<span class="badge ${cat.class}">${cat.name}</span>`;
 }

 function updateStats(quizzes) {
 const total = quizzes.length;
 const active = quizzes.filter(q => q.status === 'active').length;
 const draft = total - active;
 const attempts = quizzes.reduce((sum, q) => sum + (q.attempts || 0), 0);
 
 document.getElementById('totalQuizzes').textContent = total;
 document.getElementById('activeQuizzes').textContent = active;
 document.getElementById('draftQuizzes').textContent = draft;
 document.getElementById('totalAttempts').textContent = attempts;
 }

 function deleteQuiz(quizId) {
 if (!confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) return;
 
 fetch('../../Quizzes/quizzes/controller/quizcontroller.php?action=deleteQuiz', {
 method: 'POST',
 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
 body: 'id=' + encodeURIComponent(quizId)
 })
 .then(res => res.json())
 .then(data => {
 if (data.success || data.deleted) {
 alert('Quiz deleted successfully');
 loadQuizzes();
 } else {
 alert('Failed to delete quiz: ' + (data.error || 'Unknown error'));
 }
 })
 .catch(err => {
 alert('Error deleting quiz');
 console.error('Delete error:', err);
 });
 }
 </script>
</body>
</html>

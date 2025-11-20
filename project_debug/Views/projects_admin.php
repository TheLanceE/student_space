<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Projects Debug (Admin) | EduMind+</title>
  <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/debug.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    /* Hide edit button for admins, keep delete */
    .btn-edit { display: none !important; }
  </style>
</head>
<body data-page="admin-projects">
  <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+ [ADMIN DEBUG]</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="projects_admin.php">Projects</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">All Projects (Admin View - Delete Only)</h1>
    </div>

    <div id="projectsList" class="row g-4">
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-danger" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
  </main>

  <!-- Project Detail Modal -->
  <div class="modal fade" id="projectDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="projectDetailTitle">Project Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="projectDetailBody">
          <!-- Project details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Debug Badge -->
  <div class="debug-badge" style="background-color: #dc3545;">
    🔒 ADMIN DEBUG MODE
  </div>

  <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/projects.js"></script>
</body>
</html>

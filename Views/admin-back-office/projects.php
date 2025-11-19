<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Projects | EduMind+</title>
  <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
  <link href="../../shared-assets/css/global.css" rel="stylesheet">
</head>
<body data-page="admin-projects">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">EduMind+</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        </ul>
        <div class="d-flex"><button id="logoutBtn" class="btn btn-outline-light btn-sm">Logout</button></div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">All Projects</h1>
    </div>

    <div id="projectsList" class="row g-4">
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
  </main>

  <!-- Project Modal -->
  <div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="projectModalTitle">New Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="projectForm">
            <input type="hidden" id="projectId">
            <div class="mb-3">
              <label for="projectName" class="form-label">Project Name</label>
              <input type="text" class="form-control" id="projectName" required>
            </div>
            <div class="mb-3">
              <label for="projectDesc" class="form-label">Description</label>
              <textarea class="form-control" id="projectDesc" rows="3"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="projectStatus" class="form-label">Status</label>
                <select class="form-select" id="projectStatus">
                  <option value="not_started">Not Started</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="on_hold">On Hold</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="projectDue" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="projectDue">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveProject()">Save Project</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Task Modal -->
  <div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="taskForm">
            <input type="hidden" id="taskProjectId">
            <input type="hidden" id="taskId">
            <div class="mb-3">
              <label for="taskName" class="form-label">Task Name</label>
              <input type="text" class="form-control" id="taskName" required>
            </div>
            <div class="mb-3">
              <label for="taskDesc" class="form-label">Description</label>
              <textarea class="form-control" id="taskDesc" rows="2"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="taskPriority" class="form-label">Priority</label>
                <select class="form-select" id="taskPriority">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="taskDue" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="taskDue">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveTask()">Save Task</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
  <script src="../../shared-assets/js/database.js"></script>
  <script src="assets/js/storage.js"></script>
  <script src="assets/js/auth-admin.js"></script>
  <script>
    const API_URL = '/edumind/Controllers/ProjectController.php';
    let currentUser = null;
    let projects = [];
    let currentProjectId = null;

    // Check authentication
    document.addEventListener('DOMContentLoaded', async () => {
      currentUser = AAuth.current();
      if (!currentUser) {
        window.location.href = 'login.php';
        return;
      }
      
      // Bind logout
      document.getElementById('logoutBtn')?.addEventListener('click', () => AAuth.logout());
      
      // Load projects
      await loadProjects();
    });

    async function loadProjects() {
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'get_all_projects',
            userId: currentUser.id,
            role: currentUser.role
          })
        });
        
        const result = await response.json();
        if (result.success) {
          projects = result.projects;
          renderProjects();
        }
      } catch (error) {
        console.error('Error loading projects:', error);
        document.getElementById('projectsList').innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load projects</div></div>';
      }
    }

    function renderProjects() {
      const container = document.getElementById('projectsList');
      
      if (projects.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No projects yet. Create your first project!</p></div>';
        return;
      }
      
      container.innerHTML = projects.map(proj => `
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title">${escapeHtml(proj.projectName)}</h5>
                <span class="badge bg-${getStatusColor(proj.status)}">${proj.status.replace('_', ' ')}</span>
              </div>
              <p class="card-text text-muted small">${escapeHtml(proj.description || 'No description')}</p>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">${proj.completedTasks}/${proj.taskCount} tasks</small>
                ${proj.dueDate ? `<small class="text-muted">Due: ${proj.dueDate}</small>` : ''}
              </div>
            </div>
            <div class="card-footer bg-transparent">
              <button class="btn btn-sm btn-outline-danger" onclick="deleteProject('${proj.id}')">Delete</button>
            </div>
          </div>
        </div>
      `).join('');
    }

    function getStatusColor(status) {
      const colors = {
        not_started: 'secondary',
        in_progress: 'primary',
        completed: 'success',
        on_hold: 'warning'
      };
      return colors[status] || 'secondary';
    }

    function openProjectModal() {
      document.getElementById('projectModalTitle').textContent = 'New Project';
      document.getElementById('projectForm').reset();
      document.getElementById('projectId').value = '';
    }

    async function saveProject() {
      const projectId = document.getElementById('projectId').value;
      const data = {
        projectName: document.getElementById('projectName').value,
        description: document.getElementById('projectDesc').value,
        status: document.getElementById('projectStatus').value,
        dueDate: document.getElementById('projectDue').value || null,
        createdBy: currentUser.id,
        assignedTo: currentUser.id
      };
      
      if (!data.projectName) {
        alert('Please enter a project name');
        return;
      }
      
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: projectId ? 'update_project' : 'create_project',
            projectId: projectId || undefined,
            data: data
          })
        });
        
        const result = await response.json();
        if (result.success) {
          bootstrap.Modal.getInstance(document.getElementById('projectModal')).hide();
          await loadProjects();
        } else {
          alert('Error: ' + result.error);
        }
      } catch (error) {
        console.error('Error saving project:', error);
        alert('Failed to save project');
      }
    }

    async function editProject(projectId) {
      const proj = projects.find(p => p.id === projectId);
      if (!proj) return;
      
      document.getElementById('projectModalTitle').textContent = 'Edit Project';
      document.getElementById('projectId').value = proj.id;
      document.getElementById('projectName').value = proj.projectName;
      document.getElementById('projectDesc').value = proj.description || '';
      document.getElementById('projectStatus').value = proj.status;
      document.getElementById('projectDue').value = proj.dueDate || '';
      
      new bootstrap.Modal(document.getElementById('projectModal')).show();
    }

    async function deleteProject(projectId) {
      if (!confirm('Are you sure you want to delete this project?')) return;
      
      try {
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'delete_project',
            projectId: projectId
          })
        });
        
        const result = await response.json();
        if (result.success) {
          await loadProjects();
        }
      } catch (error) {
        console.error('Error deleting project:', error);
        alert('Failed to delete project');
      }
    }

    function viewProject(projectId) {
      // TODO: Open project details view with tasks
      alert('Project details view - coming soon!');
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</body>
</html>

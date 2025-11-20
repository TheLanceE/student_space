// Project Management JavaScript for Debug Environment

const ProjectDebug = (function() {
  'use strict';

  const API_URL = '../Controllers/ProjectController.php';
  let currentUser = { id: 'stu_debug', username: 'debug_student', role: 'student' };
  let currentProjects = [];

  // Utility Functions
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatDate(dateString) {
    if (!dateString) return 'No date';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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

  function getPriorityColor(priority) {
    const colors = {
      low: 'text-secondary',
      medium: 'text-warning',
      high: 'text-danger'
    };
    return colors[priority] || 'text-secondary';
  }

  // API Functions
  async function apiRequest(action, data = {}) {
    console.log(`[API] ${action}:`, data);
    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, ...data })
      });

      console.log(`[API] Response status: ${response.status}`);
      const result = await response.json();
      console.log(`[API] Response data:`, result);

      if (!result.success) {
        throw new Error(result.error || `Failed to ${action}`);
      }

      return result;
    } catch (error) {
      console.error(`[API] Error in ${action}:`, error);
      throw error;
    }
  }

  // Project Functions
  async function loadProjects() {
    try {
      showLoading();
      const result = await apiRequest('get_all_projects');
      currentProjects = result.projects || [];
      renderProjects(currentProjects);
    } catch (error) {
      showError('Failed to load projects: ' + error.message);
    }
  }

  function renderProjects(projects) {
    const container = document.getElementById('projectsList');

    if (!projects || projects.length === 0) {
      container.innerHTML = `
        <div class="col-12 empty-state">
          <div class="mb-3">📋</div>
          <h4>No projects yet</h4>
          <p class="text-muted">Create your first project to get started!</p>
        </div>
      `;
      return;
    }

    container.innerHTML = projects.map(proj => `
      <div class="col-md-6 col-lg-4" data-project-id="${proj.id}">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 class="card-title mb-0">${escapeHtml(proj.projectName)}</h5>
              <span class="badge bg-${getStatusColor(proj.status)}">${proj.status.replace('_', ' ')}</span>
            </div>
            <p class="card-text text-muted small mt-2">${escapeHtml(proj.description || 'No description')}</p>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <small class="text-muted">
                <i class="bi bi-check2-square"></i> ${proj.completedTasks || 0}/${proj.taskCount || 0} tasks
              </small>
              ${proj.dueDate ? `<small class="text-muted"><i class="bi bi-calendar"></i> ${formatDate(proj.dueDate)}</small>` : ''}
            </div>
          </div>
          <div class="card-footer bg-transparent">
            <button class="btn btn-sm btn-outline-primary" onclick="ProjectDebug.viewProject('${proj.id}')">
              <i class="bi bi-eye"></i> View
            </button>
            <button class="btn btn-sm btn-outline-secondary btn-edit" onclick="ProjectDebug.editProject('${proj.id}')">
              <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="btn btn-sm btn-outline-danger btn-delete" onclick="ProjectDebug.deleteProject('${proj.id}')">
              <i class="bi bi-trash"></i> Delete
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  function openProjectModal() {
    document.getElementById('projectModalTitle').textContent = 'New Project';
    document.getElementById('projectForm').reset();
    document.getElementById('projectId').value = '';
    new bootstrap.Modal(document.getElementById('projectModal')).show();
  }

  async function saveProject() {
    const projectId = document.getElementById('projectId').value;
    const projectName = document.getElementById('projectName').value.trim();
    
    if (!projectName) {
      alert('Please enter a project name');
      return;
    }

    const data = {
      projectName: projectName,
      description: document.getElementById('projectDesc').value.trim(),
      status: document.getElementById('projectStatus').value,
      dueDate: document.getElementById('projectDueDate').value || null
    };

    try {
      const action = projectId ? 'update_project' : 'create_project';
      await apiRequest(action, projectId ? { projectId, data } : { data });
      
      const modalElement = document.getElementById('projectModal');
      const modal = bootstrap.Modal.getInstance(modalElement);
      modal.hide();
      
      // Remove backdrop manually
      setTimeout(() => {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
      }, 100);
      
      await loadProjects();
      showSuccess(projectId ? 'Project updated!' : 'Project created!');
    } catch (error) {
      alert('Failed to save project: ' + error.message);
    }
  }

  async function editProject(projectId) {
    try {
      const result = await apiRequest('get_project', { projectId });
      const proj = result.project;

      document.getElementById('projectModalTitle').textContent = 'Edit Project';
      document.getElementById('projectId').value = proj.id;
      document.getElementById('projectName').value = proj.projectName;
      document.getElementById('projectDesc').value = proj.description || '';
      document.getElementById('projectStatus').value = proj.status;
      document.getElementById('projectDueDate').value = proj.dueDate || '';

      new bootstrap.Modal(document.getElementById('projectModal')).show();
    } catch (error) {
      alert('Failed to load project: ' + error.message);
    }
  }

  async function deleteProject(projectId) {
    if (!confirm('Are you sure you want to delete this project and all its tasks?')) {
      return;
    }

    try {
      await apiRequest('delete_project', { projectId });
      await loadProjects();
      showSuccess('Project deleted!');
    } catch (error) {
      alert('Failed to delete project: ' + error.message);
    }
  }

  async function viewProject(projectId) {
    try {
      const result = await apiRequest('get_project', { projectId });
      const proj = result.project;
      const tasks = result.tasks || [];

      document.getElementById('projectDetailTitle').textContent = proj.projectName;
      document.getElementById('projectDetailBody').innerHTML = `
        <div class="row">
          <div class="col-md-8">
            <h6 class="text-muted">Description</h6>
            <p>${escapeHtml(proj.description || 'No description')}</p>
            
            <h6 class="text-muted mt-4">Tasks (${tasks.length})</h6>
            <div class="list-group">
              ${tasks.length === 0 ? '<p class="text-muted">No tasks yet</p>' : tasks.map(task => `
                <div class="list-group-item task-item ${task.isComplete ? 'task-completed' : ''}">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h6 class="mb-1">${escapeHtml(task.taskName)}</h6>
                      <small class="text-muted">${escapeHtml(task.description || 'No description')}</small>
                    </div>
                    <span class="badge ${getPriorityColor(task.priority)}">${task.priority}</span>
                  </div>
                  ${task.dueDate ? `<small class="text-muted d-block mt-2"><i class="bi bi-calendar"></i> ${formatDate(task.dueDate)}</small>` : ''}
                </div>
              `).join('')}
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <h6 class="card-subtitle mb-3 text-muted">Project Info</h6>
                <div class="mb-3">
                  <small class="text-muted d-block">Status</small>
                  <span class="badge bg-${getStatusColor(proj.status)}">${proj.status.replace('_', ' ')}</span>
                </div>
                ${proj.dueDate ? `
                  <div class="mb-3">
                    <small class="text-muted d-block">Due Date</small>
                    <strong>${formatDate(proj.dueDate)}</strong>
                  </div>
                ` : ''}
                <div class="mb-3">
                  <small class="text-muted d-block">Progress</small>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar" style="width: ${proj.completionPercentage || 0}%">
                      ${proj.completionPercentage || 0}%
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;

      new bootstrap.Modal(document.getElementById('projectDetailModal')).show();
    } catch (error) {
      alert('Failed to load project details: ' + error.message);
    }
  }

  // UI Functions
  function showLoading() {
    document.getElementById('projectsList').innerHTML = `
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-3">Loading projects...</p>
      </div>
    `;
  }

  function showError(message) {
    document.getElementById('projectsList').innerHTML = `
      <div class="col-12">
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle"></i> ${escapeHtml(message)}
        </div>
      </div>
    `;
  }

  function showSuccess(message) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '11';
    toast.innerHTML = `
      <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
          <strong class="me-auto">Success</strong>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">${escapeHtml(message)}</div>
      </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  // Initialize
  function init() {
    console.log('[ProjectDebug] Initializing...');
    console.log('[ProjectDebug] Current user:', currentUser);
    
    // Load projects
    loadProjects();

    // Setup event listeners
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      window.location.href = '../index.php';
    });

    console.log('[ProjectDebug] Initialization complete');
  }

  // Public API
  return {
    init,
    loadProjects,
    openProjectModal,
    saveProject,
    editProject,
    deleteProject,
    viewProject
  };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  ProjectDebug.init();
});

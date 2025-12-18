// Project Management JavaScript for Debug Environment

const ProjectDebug = (function() {
  'use strict';

  let currentUser = { id: 'stu_debug', username: 'debug_student', role: 'student' };
  // Projects are server-rendered; JS only handles modal/form interactions now.

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

  // No API functions — server renders the project list.

  function openProjectModal() {
    document.getElementById('projectModalTitle').textContent = 'New Project';
    document.getElementById('projectForm').reset();
    // ensure POST action is create
    document.getElementById('projectAction').value = 'create_project';
    document.getElementById('projectId').value = '';
    new bootstrap.Modal(document.getElementById('projectModal')).show();
  }

  function openEditForm(projectId) {
    // find project card and read data attributes
    const el = document.querySelector(`[data-project-id="${projectId}"]`);
    if (!el) return alert('Project element not found');

    document.getElementById('projectModalTitle').textContent = 'Edit Project';
    document.getElementById('projectAction').value = 'update_project';
    document.getElementById('projectId').value = projectId;
    document.getElementById('projectName').value = el.getAttribute('data-project-name') || '';
    document.getElementById('projectDesc').value = el.getAttribute('data-project-desc') || '';
    document.getElementById('projectStatus').value = el.getAttribute('data-project-status') || 'not_started';
    document.getElementById('projectDueDate').value = el.getAttribute('data-project-due') || '';

    new bootstrap.Modal(document.getElementById('projectModal')).show();
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
    

    // Setup event listeners
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      window.location.href = '../index.php';
    });

    console.log('[ProjectDebug] Initialization complete');
  }

  // Public API
  return {
    init,
    openProjectModal,
    openEditForm
  };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  ProjectDebug.init();
});

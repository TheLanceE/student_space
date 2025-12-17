// Description validator - validates as user types
// Must be at least 10 characters long and only contain letters (A-Z, a-z) and numbers (0-9)

(function() {
  'use strict';

  function validateDescriptionField(textarea, errorDiv, showErrors) {
    const value = textarea.value.trim();
    const errors = [];

    if (value.length === 0) {
      errors.push('Description is required');
    } else {
      if (value.length < 10) {
        errors.push('Description must be at least 10 characters long');
      }
      // Allow letters, numbers, spaces, and common punctuation for natural text/speech input
      if (!/^[A-Za-z0-9\s.,!?'"()\-:;]+$/.test(value)) {
        errors.push('Description contains invalid characters');
      }
    }

    if (showErrors && errors.length > 0) {
      errorDiv.innerHTML = errors.join('<br>');
      errorDiv.style.display = 'block';
      textarea.classList.add('is-invalid');
    } else if (showErrors) {
      errorDiv.innerHTML = '';
      errorDiv.style.display = 'none';
      textarea.classList.remove('is-invalid');
    }

    return errors.length === 0;
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Project description validation
    const projectDesc = document.getElementById('projectDesc');
    const projectDescError = document.getElementById('projectDescError');
    const projectForm = projectDesc ? projectDesc.closest('form') : null;

    if (projectDesc && projectDescError) {
      projectDesc.addEventListener('input', function() {
        if (projectDesc.value.length > 0) {
          validateDescriptionField(projectDesc, projectDescError, true);
        } else {
          projectDescError.innerHTML = '';
          projectDescError.style.display = 'none';
          projectDesc.classList.remove('is-invalid');
        }
      });

      if (projectForm) {
        projectForm.addEventListener('submit', function(e) {
          if (!validateDescriptionField(projectDesc, projectDescError, true)) {
            e.preventDefault();
            projectDesc.focus();
            return false;
          }
        });
      }
    }

    // Task description validation
    const taskDesc = document.getElementById('taskDescription');
    const taskDescError = document.getElementById('taskDescError');
    const taskForm = taskDesc ? taskDesc.closest('form') : null;

    if (taskDesc && taskDescError) {
      taskDesc.addEventListener('input', function() {
        if (taskDesc.value.length > 0) {
          validateDescriptionField(taskDesc, taskDescError, true);
        } else {
          taskDescError.innerHTML = '';
          taskDescError.style.display = 'none';
          taskDesc.classList.remove('is-invalid');
        }
      });

      if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
          if (!validateDescriptionField(taskDesc, taskDescError, true)) {
            e.preventDefault();
            taskDesc.focus();
            return false;
          }
        });
      }
    }
  });
})();

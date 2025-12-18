// Task name validator - validates as user types
// Must contain at least 4 letters and only contain letters (A-Z, a-z) and numbers (0-9)

(function() {
  'use strict';

  function countLetters(str) {
    const matches = str.match(/[A-Za-z]/g);
    return matches ? matches.length : 0;
  }

  function validateTaskNameField(input, errorDiv, showErrors) {
    const value = input.value.trim();
    const errors = [];

    if (value.length === 0) {
      errors.push('Task name is required');
    } else {
      const letterCount = countLetters(value);
      if (letterCount < 4) {
        errors.push('Task name must contain at least 4 letters');
      }
      if (!/^[A-Za-z0-9\s]*$/.test(value)) {
        errors.push('Task name can only contain letters (A-Z, a-z) and numbers (0-9)');
      }
    }

    if (showErrors && errors.length > 0) {
      errorDiv.innerHTML = errors.join('<br>');
      errorDiv.style.display = 'block';
      input.classList.add('is-invalid');
    } else if (showErrors) {
      errorDiv.innerHTML = '';
      errorDiv.style.display = 'none';
      input.classList.remove('is-invalid');
    }

    return errors.length === 0;
  }

  document.addEventListener('DOMContentLoaded', function() {
    const taskNameInput = document.getElementById('taskName');
    const taskNameError = document.getElementById('taskNameError');
    const form = taskNameInput ? taskNameInput.closest('form') : null;

    if (taskNameInput && taskNameError) {
      taskNameInput.addEventListener('input', function() {
        if (taskNameInput.value.length > 0) {
          validateTaskNameField(taskNameInput, taskNameError, true);
        } else {
          taskNameError.innerHTML = '';
          taskNameError.style.display = 'none';
          taskNameInput.classList.remove('is-invalid');
        }
      });

      if (form) {
        form.addEventListener('submit', function(e) {
          if (!validateTaskNameField(taskNameInput, taskNameError, true)) {
            e.preventDefault();
            taskNameInput.focus();
            return false;
          }
        });
      }
    }
  });
})();

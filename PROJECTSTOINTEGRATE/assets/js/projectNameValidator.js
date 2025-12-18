/**
 * Project Name Validator Controller
 * Enforces alphanumeric characters only and minimum length of 4 characters
 */

const ProjectNameValidator = (function() {
  'use strict';

  const CONFIG = {
    MIN_LENGTH: 4,
    MAX_LENGTH: 100,
    PATTERN: /^[a-zA-Z0-9]+$/,
    ERROR_CLASS: 'is-invalid',
    FEEDBACK_CLASS: 'invalid-feedback'
  };

  /**
   * Check if project name is valid
   * @param {string} name - Project name to validate
   * @returns {object} - { isValid: boolean, errors: string[] }
   */
  function validateProjectName(name) {
    const errors = [];

    // Trim whitespace
    const trimmedName = name.trim();

    // Check if empty
    if (!trimmedName) {
      errors.push('Project name is required');
      return { isValid: false, errors };
    }

    // Check minimum length
    if (trimmedName.length < CONFIG.MIN_LENGTH) {
      errors.push(`Project name must be at least ${CONFIG.MIN_LENGTH} characters long`);
    }

    // Check maximum length
    if (trimmedName.length > CONFIG.MAX_LENGTH) {
      errors.push(`Project name must not exceed ${CONFIG.MAX_LENGTH} characters`);
    }

    // Check alphanumeric pattern
    if (!CONFIG.PATTERN.test(trimmedName)) {
      errors.push('Project name can only contain letters (A-Z, a-z) and numbers (0-9)');
    }

    return {
      isValid: errors.length === 0,
      errors: errors
    };
  }

  /**
   * Display validation error messages in the UI
   * @param {HTMLElement} inputElement - The input field element
   * @param {array} errors - Array of error messages
   */
  function displayErrors(inputElement, errors) {
    if (!inputElement) return;

    // Remove existing feedback elements
    const existingFeedback = inputElement.parentElement.querySelector('.' + CONFIG.FEEDBACK_CLASS);
    if (existingFeedback) {
      existingFeedback.remove();
    }

    if (errors.length > 0) {
      // Add error class to input
      inputElement.classList.add(CONFIG.ERROR_CLASS);

      // Create and insert feedback element
      const feedbackDiv = document.createElement('div');
      feedbackDiv.className = CONFIG.FEEDBACK_CLASS;
      feedbackDiv.innerHTML = errors.map(error => `<div>${escapeHtml(error)}</div>`).join('');
      inputElement.parentElement.appendChild(feedbackDiv);

      // Show feedback
      feedbackDiv.style.display = 'block';
    } else {
      // Remove error class
      inputElement.classList.remove(CONFIG.ERROR_CLASS);
    }
  }

  /**
   * Clear validation state
   * @param {HTMLElement} inputElement - The input field element
   */
  function clearValidation(inputElement) {
    if (!inputElement) return;

    inputElement.classList.remove(CONFIG.ERROR_CLASS);
    const feedbackDiv = inputElement.parentElement.querySelector('.' + CONFIG.FEEDBACK_CLASS);
    if (feedbackDiv) {
      feedbackDiv.remove();
    }
  }

  /**
   * Escape HTML characters to prevent XSS
   * @param {string} text - Text to escape
   * @returns {string} - Escaped text
   */
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Initialize validator on project name input field
   * Attaches event listeners for real-time validation
   * @param {string|HTMLElement} inputSelector - CSS selector or HTML element
   */
  function initializeInput(inputSelector) {
    const inputElement = typeof inputSelector === 'string'
      ? document.querySelector(inputSelector)
      : inputSelector;

    if (!inputElement) {
      console.warn('ProjectNameValidator: Input element not found');
      return;
    }

    // Real-time validation on input
    inputElement.addEventListener('input', function() {
      const validation = validateProjectName(this.value);
      if (!validation.isValid) {
        displayErrors(this, validation.errors);
      } else {
        clearValidation(this);
      }
    });

    // Validation on blur
    inputElement.addEventListener('blur', function() {
      const validation = validateProjectName(this.value);
      displayErrors(this, validation.errors);
    });

    // Clear errors on focus
    inputElement.addEventListener('focus', function() {
      clearValidation(this);
    });
  }

  /**
   * Validate input before form submission
   * @param {string|HTMLElement} inputSelector - CSS selector or HTML element
   * @returns {boolean} - True if valid, false otherwise
   */
  function validateBeforeSubmit(inputSelector) {
    const inputElement = typeof inputSelector === 'string'
      ? document.querySelector(inputSelector)
      : inputSelector;

    if (!inputElement) {
      console.warn('ProjectNameValidator: Input element not found');
      return false;
    }

    const validation = validateProjectName(inputElement.value);

    if (!validation.isValid) {
      displayErrors(inputElement, validation.errors);
      inputElement.focus();
      return false;
    }

    clearValidation(inputElement);
    return true;
  }

  // Public API
  return {
    validateProjectName,
    displayErrors,
    clearValidation,
    initializeInput,
    validateBeforeSubmit,
    CONFIG
  };
})();

// Auto-initialize when DOM is ready if projectName input exists
document.addEventListener('DOMContentLoaded', function() {
  const projectNameInput = document.getElementById('projectName');
  if (projectNameInput) {
    ProjectNameValidator.initializeInput(projectNameInput);
  }
});

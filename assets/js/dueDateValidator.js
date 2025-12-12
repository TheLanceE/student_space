/**
 * Due Date Validator Controller
 * Prevents users from selecting dates earlier than today
 */

const DueDateValidator = (function() {
  'use strict';

  const CONFIG = {
    ERROR_CLASS: 'is-invalid',
    FEEDBACK_CLASS: 'invalid-feedback'
  };

  /**
   * Get today's date in YYYY-MM-DD format
   * @returns {string} - Today's date
   */
  function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  /**
   * Check if a date is in the past
   * @param {string} dateString - Date string in YYYY-MM-DD format
   * @returns {boolean} - True if date is in the past
   */
  function isDateInPast(dateString) {
    if (!dateString) return false;

    const selectedDate = new Date(dateString);
    const today = new Date();

    // Reset time to midnight for fair comparison
    today.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);

    return selectedDate < today;
  }

  /**
   * Format date for display
   * @param {string} dateString - Date string in YYYY-MM-DD format
   * @returns {string} - Formatted date
   */
  function formatDateForDisplay(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  }

  /**
   * Validate due date
   * @param {string} dateString - Date string in YYYY-MM-DD format
   * @returns {object} - { isValid: boolean, error: string }
   */
  function validateDueDate(dateString) {
    // Empty dates are allowed (optional field)
    if (!dateString) {
      return { isValid: true, error: '' };
    }

    if (isDateInPast(dateString)) {
      const formattedDate = formatDateForDisplay(dateString);
      const todayFormatted = formatDateForDisplay(getTodayDate());
      return {
        isValid: false,
        error: `Due date (${formattedDate}) cannot be earlier than today (${todayFormatted})`
      };
    }

    return { isValid: true, error: '' };
  }

  /**
   * Display validation error message in the UI
   * @param {HTMLElement} inputElement - The input field element
   * @param {string} error - Error message
   */
  function displayError(inputElement, error) {
    if (!inputElement) return;

    // Remove existing feedback elements
    const existingFeedback = inputElement.parentElement.querySelector('.' + CONFIG.FEEDBACK_CLASS);
    if (existingFeedback) {
      existingFeedback.remove();
    }

    if (error) {
      // Add error class to input
      inputElement.classList.add(CONFIG.ERROR_CLASS);

      // Create and insert feedback element
      const feedbackDiv = document.createElement('div');
      feedbackDiv.className = CONFIG.FEEDBACK_CLASS;
      feedbackDiv.textContent = error;
      inputElement.parentElement.appendChild(feedbackDiv);
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
   * Set minimum date on input to today
   * Prevents users from selecting past dates via the date picker
   * @param {string|HTMLElement} inputSelector - CSS selector or HTML element
   */
  function setMinimumDate(inputSelector) {
    const inputElement = typeof inputSelector === 'string'
      ? document.querySelector(inputSelector)
      : inputSelector;

    if (!inputElement) {
      console.warn('DueDateValidator: Input element not found');
      return;
    }

    const todayDate = getTodayDate();
    inputElement.min = todayDate;
  }

  /**
   * Initialize validator on due date input field
   * Attaches event listeners for validation and sets minimum date
   * @param {string|HTMLElement} inputSelector - CSS selector or HTML element
   */
  function initializeInput(inputSelector) {
    const inputElement = typeof inputSelector === 'string'
      ? document.querySelector(inputSelector)
      : inputSelector;

    if (!inputElement) {
      console.warn('DueDateValidator: Input element not found');
      return;
    }

    // Set minimum date to today
    setMinimumDate(inputElement);

    // Validation on change
    inputElement.addEventListener('change', function() {
      const validation = validateDueDate(this.value);
      displayError(this, validation.error);
    });

    // Clear errors on focus
    inputElement.addEventListener('focus', function() {
      clearValidation(this);
    });

    // Validate on blur
    inputElement.addEventListener('blur', function() {
      if (this.value) {
        const validation = validateDueDate(this.value);
        if (!validation.isValid) {
          displayError(this, validation.error);
        }
      }
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
      console.warn('DueDateValidator: Input element not found');
      return true; // Allow submission if input not found
    }

    if (!inputElement.value) {
      // Empty dates are optional
      clearValidation(inputElement);
      return true;
    }

    const validation = validateDueDate(inputElement.value);

    if (!validation.isValid) {
      displayError(inputElement, validation.error);
      inputElement.focus();
      return false;
    }

    clearValidation(inputElement);
    return true;
  }

  /**
   * Get today's date
   * @returns {string} - Today's date in YYYY-MM-DD format
   */
  function getToday() {
    return getTodayDate();
  }

  // Public API
  return {
    validateDueDate,
    displayError,
    clearValidation,
    initializeInput,
    validateBeforeSubmit,
    setMinimumDate,
    getToday,
    isDateInPast,
    formatDateForDisplay,
    CONFIG
  };
})();

// Auto-initialize when DOM is ready if due date input exists
document.addEventListener('DOMContentLoaded', function() {
  const projectDueDateInput = document.getElementById('projectDueDate');
  if (projectDueDateInput) {
    DueDateValidator.initializeInput(projectDueDateInput);
  }

  const taskDueDateInput = document.getElementById('taskDueDate');
  if (taskDueDateInput) {
    DueDateValidator.initializeInput(taskDueDateInput);
  }
});

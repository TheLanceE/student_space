/**
 * Security Utilities for EduMind
 * Provides XSS prevention and CSRF token handling
 */
(function() {
  'use strict';

  /**
   * Escape HTML to prevent XSS attacks
   * @param {string|null|undefined} str - The string to escape
   * @returns {string} Escaped HTML string
   */
  const escapeHtml = (str) => {
    if (str == null) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
  };

  /**
   * Get CSRF token from page meta tag or hidden input
   * @returns {string} CSRF token or empty string
   */
  const getCSRFToken = () => {
    // Try meta tag first
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content') || '';
    
    // Fall back to hidden input
    const input = document.querySelector('input[name="csrf_token"]');
    if (input) return input.value || '';
    
    return '';
  };

  /**
   * Create fetch options with CSRF token header
   * @param {Object} options - Base fetch options
   * @returns {Object} Fetch options with CSRF header added
   */
  const secureFetch = (options = {}) => {
    const token = getCSRFToken();
    const headers = options.headers || {};
    
    return {
      ...options,
      headers: {
        ...headers,
        'X-CSRF-Token': token
      }
    };
  };

  /**
   * Safe innerHTML setter - escapes all dynamic content
   * @param {Element} element - Target element
   * @param {string} html - HTML with placeholders in format {{key}}
   * @param {Object} data - Key-value pairs to safely interpolate
   */
  const safeInnerHTML = (element, html, data = {}) => {
    let result = html;
    for (const [key, value] of Object.entries(data)) {
      const placeholder = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
      result = result.replace(placeholder, escapeHtml(value));
    }
    element.innerHTML = result;
  };

  /**
   * Sanitize user input for display
   * @param {string} input - User input
   * @param {number} maxLength - Maximum allowed length
   * @returns {string} Sanitized and truncated string
   */
  const sanitizeInput = (input, maxLength = 255) => {
    if (typeof input !== 'string') return '';
    return escapeHtml(input.trim().substring(0, maxLength));
  };

  // Expose utilities globally
  window.SecurityUtils = {
    escapeHtml,
    getCSRFToken,
    secureFetch,
    safeInnerHTML,
    sanitizeInput
  };

  // Also expose escapeHtml directly for convenience
  window.escapeHtml = escapeHtml;

})();

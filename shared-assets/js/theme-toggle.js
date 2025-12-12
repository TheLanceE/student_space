(function() {
  const STORAGE_KEY = 'edumind-theme';
  const root = document.documentElement;

  const getPreferredTheme = () => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'light' || stored === 'dark') return stored;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const updateToggleLabels = (theme) => {
    const labelText = theme === 'dark' ? 'Dark' : 'Light';
    document.querySelectorAll('.theme-toggle').forEach(btn => {
      btn.setAttribute('aria-pressed', theme === 'dark');
      const label = btn.querySelector('.theme-toggle__label');
      if (label) label.textContent = labelText;
    });
  };

  const applyTheme = (theme) => {
    const normalized = theme === 'dark' ? 'dark' : 'light';
    root.setAttribute('data-theme', normalized);
    localStorage.setItem(STORAGE_KEY, normalized);
    updateToggleLabels(normalized);
  };

  const renderToggle = () => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'theme-toggle';
    button.setAttribute('aria-label', 'Toggle light or dark mode');
    button.setAttribute('aria-pressed', 'false');
    button.innerHTML = [
      '<span class="theme-toggle__icon" aria-hidden="true">',
      '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" role="presentation">',
      '<path d="M12 3a1 1 0 0 1 1 1v1.05a7 7 0 0 1 6.95 6.95H21a1 1 0 1 1 0 2h-1.05A7 7 0 0 1 13 20.95V22a1 1 0 1 1-2 0v-1.05A7 7 0 0 1 4.05 14H3a1 1 0 1 1 0-2h1.05A7 7 0 0 1 11 5.05V4a1 1 0 0 1 1-1Zm0 4a5 5 0 1 0 0 10a5 5 0 0 0 0-10Z"/>',
      '</svg>',
      '</span>',
      '<span class="theme-toggle__label">Light</span>'
    ].join('');

    button.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      applyTheme(next);
    });

    document.body.appendChild(button);
  };

  const init = () => {
    const initial = getPreferredTheme();
    applyTheme(initial);
    renderToggle();

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleSchemeChange = (event) => {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored === 'light' || stored === 'dark') return;
      applyTheme(event.matches ? 'dark' : 'light');
    };
    mediaQuery.addEventListener('change', handleSchemeChange);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

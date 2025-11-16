# EduMind+ Templates

This workspace contains three separate, role-specific templates. Each runs entirely in the browser using HTML/CSS/JS with Bootstrap and optional Chart.js, storing demo data in localStorage.

- front-office (student) — Learner experience: quizzes, feedback, progress, suggestions
- teacher-back-office — Teacher console: content/quiz builder, student analytics, CSV export
- admin-back-office — Admin console: users/roles, course approvals, logs, reports, settings

## Open locally (Windows)

Option A — open directly in browser:
- Student: open `front-office/index.html`
- Teacher: open `teacher-back-office/index.html`
- Admin: open `admin-back-office/index.html`

Option B — serve locally (recommended):
```powershell
# From the Front&Back folder
python -m http.server 5500
# Then open:
# http://localhost:5500/front-office/index.html
# http://localhost:5500/teacher-back-office/index.html
# http://localhost:5500/admin-back-office/index.html
```

## Notes
- Each template is isolated with its own localStorage keys (TEACHER_*, ADMIN_*). The student template uses generic keys.
- Demo data is seeded on first load in teacher/admin templates (courses, quizzes, users, logs).
- You can customize demos via `assets/js/data-*.js` in each template folder.
- These are front-end templates for demonstration; no backend is required.

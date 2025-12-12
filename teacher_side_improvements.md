# Teacher-Side Audit (Dec 7, 2025)

Scope: Reviewed teacher back-office PHP pages, JS helpers, and controllers (`Views/teacher-back-office/*`, `Controllers/*`) for functionality, data flow, and security. Below are improvement opportunities.

## Highest-Risk Gaps
- **Auth state mishandled in JS**: `auth().current()` is async but used synchronously in `assets/js/pages.js` (e.g., `ensureAuth`, dashboard, courses). This means auth checks and teacher context are unreliable in JS and can leave stale/unauthenticated pages running.
- **Front-end-only data layer**: Teacher pages (courses, events, quiz builder, quiz reports, reports) depend on a missing `shared-assets/js/database.js` stub and local `TData` storage, so most CRUD persists only in-memory/localStorage and is detached from real DB/session authorization.
- **Password verification fallback**: `teacher_login_handler.php` accepts raw-password equality as a fallback (`password_verify(...) || $password === $teacher['password']`), weakening password security and allowing legacy plaintext records to remain.
- **CSRF/session protection**: No CSRF tokens on forms (login, register, courses/events/quiz forms), and no per-request session freshness/timeout enforcement on teacher endpoints.

## Page-Level Functional Gaps
- **Dashboard (`dashboard.php`)**: Server renders basic counts, but charts rely on local `TData.getScoresForTeacher` and never hit DB; no error handling for empty datasets. No quick links to resolve low performers or recent reports.
- **Courses (`courses.php`)**: Add-course form and list are entirely client-side. No server validation, ownership checks, or persistence. Delete cascades quizzes only in local storage.
- **Events (`events.php`)**: Events are local-only; no association to real courses/students, no time zone handling or capacity enforcement, and delete is a global JS function without auth.
- **Quiz Builder (`quiz-builder.php`)**: Saves quizzes to local storage; no linkage to real courses or student delivery. Missing question validation (empty options, duplicate correct answers) and no draft/publish states.
- **Quiz Reports (`quiz-reports.php`)**: Renders from `TData.quizReports` local array; status changes are not persisted server-side and have no audit trail.
- **Reports export (`reports.php`)**: Exports demo CSV from local scores; not tied to actual DB results. No date/course filters or per-student drill-down.
- **Projects (`projects.php`)**: Read-only but still pulls shared `projects.js` (front-office) with no teacher-specific filter or access control; hidden edit/delete via CSS only.
- **Students (`students.php`)**: Now server-driven, but lacks pagination/search/filter/export and course-by-course drill-down. No per-student timeline or intervention actions.

## Architecture/Data Improvements
- Provide a real teacher-facing API (or extend existing controllers) for courses, quizzes, events, reports, and quiz-report status updates. Use server-side ownership checks (`teacherId` from session) and return JSON for JS pages.
- Replace `TData`/`Database` stub usage with API calls; remove the missing `shared-assets/js/database.js` reference or replace with a proper data client.
- Normalize quiz storage: quizzes/questions/attempts persisted in DB with migrations; ensure foreign keys to `courses` and `teachers`.
- Add pagination and filtering to server queries (students, courses, reports) to avoid heavy payloads.
- Centralize teacher nav/footer and theme assets; avoid per-page inline CSS and duplicate navbar markup.

## Security & Compliance
- Enforce hashed passwords only; migrate legacy plaintext passwords and remove `$password === $teacher['password']` fallback.
- Add CSRF tokens to all teacher POST forms and validate server-side. Consider SameSite=strict cookies.
- Rate-limit login and registration, add basic bot/abuse protections, and log suspicious attempts.
- Harden session handling: regenerate session ID after login, enforce inactivity timeout, and validate role on every controller entry (not just in views).
- Validate and sanitize all inputs on the server (courses/events/quizzes) and escape outputs in views to prevent XSS.

## UX/Workflow Opportunities
- Add global light/dark theme toggle to teacher pages (they currently miss `theme-toggle.js`), and align navbar/background with shared tokens.
- Improve dashboard with actionable cards: recent quiz reports, low-performing students, upcoming events, and quick-create course/quiz buttons.
- Courses: allow status (draft/published/archive), tagging, and bulk actions; show enrollment/attempt counts per course.
- Events: integrate with courses and student list, support reminders/ICS export, and attendance tracking.
- Quiz Builder: add question banks, templates, point weights, randomization, and preview mode. Support saving drafts and publishing to selected courses.
- Quiz Reports: add filters by course/quiz/date, inline comment/feedback to students, and export. Persist status changes server-side with audit fields.
- Students: add search/filter by course, risk level, and activity; per-student detail page with attempts timeline and contact actions; CSV export.
- Reports: server-generated CSV/PDF with filters by date range/course/student; schedule recurring exports via email.
- Accessibility: ensure form labels/ARIA, focus states, and keyboard access on modals/buttons; reduce reliance on color-only badges.

## Observability/Operations
- Add server-side logging around teacher actions (course create, quiz publish, report status changes) with teacher ID and IP.
- Add health checks and simple analytics (page loads, JS errors) to spot breakages when API/auth changes.

## Quick Wins (low effort)
1) Remove plaintext password fallback and regenerate session ID on login. 
2) Include `theme-toggle.js` + theme tokens on teacher pages; drop inline gradients in favor of shared CSS.
3) Fix JS auth check by awaiting `TAuth.current()` (or caching session user) before rendering pages.
4) Remove/replace missing `shared-assets/js/database.js` dependency; guard UI rendering when data client is unavailable.
5) Add search/filter/export to `students.php` query plus pagination to avoid heavy pages.
6) Wire quiz-report status updates to a server endpoint and persist reviewer info.

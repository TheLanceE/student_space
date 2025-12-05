# EduMind+ (student_space)

Lightweight PHP/MySQL portal for students, teachers, and admins. Includes Google OAuth login, role-based dashboards, quizzes, courses, events, and projects.

## Quick start
1. Import `database.sql` (and `database_updates.sql` if needed) into MySQL.
2. Copy `Controllers/oauth_config.local.example.php` to `oauth_config.local.php` and fill Google credentials plus redirect URI (`http://localhost/edumind/Controllers/google_oauth_callback.php`).
3. Set web root to the repo folder (or map `http://localhost/edumind/`).
4. Default sample logins:
   - Student: `superkid` / `password123`
   - Teacher: `teacher_jane` / `password123`
   - Admin: `admin` / `password123`
5. Google login: choose role on the login page (defaults to student) then click "Sign in with Google".

## Notes
- Sessions are centralized via `Controllers/SessionManager.php` and `config.php` autoloads auth checks.
- If Google email already exists, the flow links the account instead of failing.
- Soft deletes: many tables use `deleted_at`; queries filter them out by default.

## Troubleshooting
- If you see `oauth_failed`, confirm client ID/secret/redirect URI and that cookies are allowed.
- If `not_logged_in` appears, ensure sessions are writable and that login callbacks complete.
- For teacher access issues, verify `teachers` table has the account and `role=teacher` in session.

# EduMind+ (student_space)

> **Main branch is in work-in-progress integration.** We are merging all role experiences here; expect rapid changes.

Lightweight PHP/MySQL portal for students, teachers, and admins. Includes Google OAuth login, role-based dashboards, quizzes, courses, events, and projects.

## Integration status
<p align="center">
   <svg width="520" height="86" viewBox="0 0 520 86" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="10" y="22" width="500" height="24" rx="12" fill="#0f172a" stroke="#1d4ed8" stroke-width="2" />
      <defs>
         <pattern id="stripe" x="0" y="0" width="40" height="24" patternUnits="userSpaceOnUse" patternTransform="translate(0 0)">
            <rect x="0" y="0" width="40" height="24" fill="#2563eb" />
            <rect x="0" y="0" width="20" height="24" fill="#3b82f6" opacity="0.75" />
         </pattern>
      </defs>
      <rect x="12" y="24" width="100" height="20" rx="10" fill="url(#stripe)">
         <animate attributeName="x" values="12;32" dur="1.5s" repeatCount="indefinite" />
      </rect>
      <rect x="12" y="24" width="100" height="20" rx="10" fill="transparent" stroke="#60a5fa" stroke-width="1.5" />
      <text x="122" y="40" fill="#e2e8f0" font-family="Inter,Segoe UI,Arial" font-size="14" font-weight="700">20% · Integration in progress</text>
      <circle cx="480" cy="34" r="6" fill="#22c55e">
         <animate attributeName="r" values="6;9;6" dur="1.8s" repeatCount="indefinite" />
         <animate attributeName="opacity" values="1;0.4;1" dur="1.8s" repeatCount="indefinite" />
      </circle>
   </svg>
</p>

![Student dashboard preview](shared-assets/img/dashboard-preview.jpg)
![Teacher workspace preview](shared-assets/img/teacher-workspace.jpg)

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

Private server configuration for this project

What I added
- `.htaccess` in the project root that restricts access to localhost only (127.0.0.1 and ::1).
  - This prevents external/public clients from reaching the app when running under Apache (XAMPP).

Why this helps
- Makes the site private on the machine running XAMPP: only requests originating from the same machine (e.g., `http://localhost/...`) will be allowed.
- Minimal and non-invasive: no changes to your PHP/HTML code required.

How to test
1. Start Apache (XAMPP).
2. From the same machine, open: `http://localhost/quizzes/view/frontofficequizstudent.html` — the site should load as before.
3. From another machine (or via an external IP), the server will deny access (HTTP 403).

If you want password access instead of IP restriction
- Create an `.htpasswd` file and enable HTTP Basic Auth in `.htaccess` (instructions below):
  1. Create `.htpasswd` using Apache's `htpasswd` utility (found in XAMPP `apache/bin/htpasswd.exe`) or any generator.
     Example (PowerShell, running inside Apache bin):
     ```powershell
     .\htpasswd.exe -c C:\path\to\.htpasswd username
     ```
  2. Edit `.htaccess` and uncomment the Basic Auth block, set `AuthUserFile` to the absolute path of your `.htpasswd`.
  3. Restart Apache.

If you need per-user sessions (login page)
- I can add a PHP-based login (session auth) that protects all PHP endpoints and redirects unauthenticated visitors to a `login.php` page. This is useful when you want remote access for a small set of users without using Apache-level auth.

How to revert to public access
- Remove or rename `.htaccess` or comment out the `Require ip`/`Order` directives and restart Apache.

Next steps I can take for you (pick one):
- Add HTTP Basic Auth example with `.htpasswd` creation helper script.
- Add PHP session-based login (`login.php`, `auth.php`) and include protection across PHP endpoints.
- Restrict by specific IP ranges instead of just localhost.

If you want, tell me which option you prefer and I will implement it.

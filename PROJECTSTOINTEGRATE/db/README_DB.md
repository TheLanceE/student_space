# Database Setup and Testing for EduMind+ (Debug)

This file explains how to create the `edumind` database, import the schema, and test the API endpoints included with this project.

Files
- `db/edumind_schema.sql` — creates `edumind` database, `users`, `projects`, `tasks` tables and inserts debug seed data.

Quick import (PowerShell)
- If MySQL is available and the local `root` user has no password (common with XAMPP):

```powershell
cd C:\xampp\htdocs\project_debug
mysql -u root < .\db\edumind_schema.sql
```

- If `root` has a password or you want to use another user:

```powershell
mysql -u <username> -p -h <host> < .\db\edumind_schema.sql
# you'll be prompted for the password
```

- You can also import the file using phpMyAdmin: log in -> Import -> choose `db/edumind_schema.sql` -> Go.

Verify DB credentials
- Open `Controllers/ProjectController.php` and check the constants at the top:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edumind');
```

Adjust as needed for your environment.

API test (browser)
- Visit this controller test route in your browser (adjust host/path as needed):

http://localhost/project_debug/Controllers/ProjectController.php?action=test

API test (PowerShell)

```powershell
Invoke-RestMethod -Uri "http://localhost/project_debug/Controllers/ProjectController.php?action=test"
```

API usage (POST JSON)
- All actions are POSTed to `Controllers/ProjectController.php` as JSON. Example: get all projects

```powershell
$body = @{ action = 'get_all_projects' } | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost/project_debug/Controllers/ProjectController.php" -Method POST -Body $body -ContentType 'application/json'
```

Create project example

```powershell
$project = @{ projectName = 'My New Project'; description = 'Notes'; status = 'not_started'; dueDate = (Get-Date -Format yyyy-MM-dd) }
$body = @{ action = 'create_project'; data = $project } | ConvertTo-Json -Depth 4
Invoke-RestMethod -Uri "http://localhost/project_debug/Controllers/ProjectController.php" -Method POST -Body $body -ContentType 'application/json'
```

Notes and troubleshooting
- If the controller returns a PDO connection error, verify that MySQL is running and credentials are correct.
- If you're using XAMPP, ensure Apache and MySQL are started via the XAMPP control panel.
- If `Controllers/ProjectController.php` returns an "Invalid action" error, check that your POST JSON includes an `action` field.

If you'd like, I can run the import here — provide the DB username/password you want me to use, or let me know if you prefer step-by-step instructions instead.

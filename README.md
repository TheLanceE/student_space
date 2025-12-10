# EduMind Quizzes

PHP/MySQL quiz application with teacher and student front-office views.

## Getting Started
1. Install XAMPP (PHP + MySQL).
2. Clone repo into `htdocs` (path currently `c:\xampp\htdocs\quizzes2\quizzes2\quizzes2\quiz`).
3. Create database and tables:
   - Import `db/quiz_schema.sql` into MySQL (`edumind` database by default).
4. Configure database credentials:
   - Edit `config.php` (DB host/port/user/password/dbname).
5. Run locally:
   - Visit `http://localhost/quizzes2/quizzes2/quiz/`.

## Key Directories
- `controller/` — request handling and routing.
- `model/` — data models and DB logic.
- `view/` — UI (teacher/student pages).
- `db/` — SQL schema.

## Notes
- `config.php` is tracked here; ensure credentials are safe or keep the repo private.
- Teacher view allows creating/editing quizzes (including deleting questions in edit mode) and marking correct options.
- Student view lists available quizzes.

## Troubleshooting
- If options are not saving, verify `question_options` table exists and `config.php` DB settings are correct.
- Ensure MySQL service is running (default XAMPP port 3306 unless customized).

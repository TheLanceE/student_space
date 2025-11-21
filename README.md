# EduMind Reports Management System

A dynamic CRUD (Create, Read, Update, Delete) system for managing reports with MySQL database integration.

## Features

- ✅ **Create Reports**: Add new reports with student information, quiz details, type, status, and content
- ✅ **Read Reports**: View all reports with filtering by status (All, Pending, Reviewed, Kept)
- ✅ **Update Reports**: Edit existing reports or update their status
- ✅ **Delete Reports**: Remove reports from the database
- ✅ **Dynamic UI**: Real-time updates without page refresh
- ✅ **Status Management**: Quick actions to mark reports as Reviewed or Kept

## Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Web server (Apache/Nginx) or PHP built-in server

### Step 1: Database Setup

1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Import the database schema:
   ```sql
   source database/schema.sql
   ```
   Or manually execute the SQL file: `database/schema.sql`

3. The database `edumind_db` will be created with the `reports` table and sample data.

### Step 2: Configure Database Connection

1. Open `config/database.php`
2. Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');  // Your MySQL host
   define('DB_USER', 'root');        // Your MySQL username
   define('DB_PASS', '');            // Your MySQL password
   define('DB_NAME', 'edumind_db');  // Database name
   ```

### Step 3: Start the Server

#### Option A: PHP Built-in Server
```bash
cd edumind_proooj
php -S localhost:8000
```

#### Option B: Apache/Nginx
- Place the project in your web server's document root
- Ensure mod_rewrite is enabled (if using Apache)

### Step 4: Access the Application

Open your browser and navigate to:
```
http://localhost:8000/View/Back.html
```

## Project Structure

```
edumind_proooj/
├── api/
│   └── reports.php          # REST API endpoints for CRUD operations
├── config/
│   └── database.php         # Database configuration
├── database/
│   └── schema.sql           # Database schema and sample data
└── View/
    └── Back.html            # Frontend interface
```

## API Endpoints

### GET Reports
- **Get All Reports**: `GET /api/reports.php`
- **Get Report by ID**: `GET /api/reports.php?id={id}`
- **Filter by Status**: `GET /api/reports.php?status={status}`

### CREATE Report
- **Endpoint**: `POST /api/reports.php`
- **Body** (JSON):
  ```json
  {
    "student": "John Doe",
    "quiz": "Math Quiz 1",
    "type": "Performance",
    "status": "Pending",
    "content": "Report content here",
    "created_date": "2025-11-11 23:04:00"
  }
  ```

### UPDATE Report
- **Endpoint**: `PUT /api/reports.php`
- **Body** (JSON):
  ```json
  {
    "id": 1,
    "student": "John Doe",
    "quiz": "Math Quiz 1",
    "type": "Performance",
    "status": "Reviewed",
    "content": "Updated content"
  }
  ```

### DELETE Report
- **Endpoint**: `DELETE /api/reports.php?id={id}`

## Database Schema

### Reports Table

| Column | Type | Description |
|--------|------|-------------|
| id | INT (AUTO_INCREMENT) | Primary key |
| student | VARCHAR(255) | Student name |
| quiz | VARCHAR(255) | Quiz name (nullable) |
| type | VARCHAR(100) | Report type (Performance, Behavioral, Attendance, Progress) |
| status | VARCHAR(50) | Status (Pending, Reviewed, Kept) |
| content | TEXT | Report content |
| created_date | DATETIME | Creation timestamp |
| updated_date | DATETIME | Last update timestamp |

## Usage

### Adding a New Report
1. Fill in the form at the bottom of the page
2. Required fields: Student, Type, Content
3. Click "Add Report"

### Editing a Report
1. Click the "Edit" button on any report row
2. The form will populate with the report data
3. Make your changes and click "Update Report"

### Updating Status
- Click "Review" to mark as Reviewed
- Click "Keep" to mark as Kept

### Filtering Reports
- Click on the filter tabs (All Reports, Pending, Reviewed, Kept) to filter by status

### Deleting a Report
1. Click the "Delete" button on any report row
2. Confirm the deletion

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure the database `edumind_db` exists

### API Not Working
- Check PHP error logs
- Verify file permissions
- Ensure CORS headers are properly set (if accessing from different domain)

### Reports Not Loading
- Open browser console (F12) to check for JavaScript errors
- Verify the API endpoint path is correct in `Back.html` (line with `API_BASE_URL`)

## Security Notes

⚠️ **Important**: This is a basic implementation. For production use, consider:
- Input validation and sanitization
- Prepared statements (already implemented)
- Authentication and authorization
- HTTPS encryption
- Rate limiting
- SQL injection prevention (prepared statements help, but add more validation)

## License

This project is provided as-is for educational purposes.


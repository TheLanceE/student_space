# Quiz Application Setup Guide

## Important: Environment-Specific Configuration

This project uses **local configuration files** that should NOT be committed to git because they contain sensitive information like database credentials.

## Quick Setup

### Step 1: Copy Configuration Template
Copy `config.example.php` to `config.php`:

```bash
# Windows (PowerShell)
Copy-Item config.example.php -Destination config.php

# Linux/Mac
cp config.example.php config.php
```

### Step 2: Update Configuration
Edit `config.php` and update these values for YOUR environment:

```php
$servername = "localhost";      // Your database server
$username = "root";             // Your database username (usually 'root' for local XAMPP)
$password = "";                 // Your database password (usually empty for local XAMPP)
$dbname = "edumind";            // Your database name
$port = 3307;                   // Your MySQL port (3306 is default, 3307 for some XAMPP installations)
```

### Step 3: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `edumind`
3. Run the SQL schema: `db/quiz_schema.sql`

### Step 4: Start Development
- Ensure XAMPP services are running (Apache + MySQL)
- Access the app at: `http://localhost/quizzes2/quizzes2/quiz/`

## File Structure

```
quiz/
├── config.example.php      ← Template (commit to git)
├── config.php              ← LOCAL configuration (DO NOT COMMIT)
├── .gitignore              ← Prevents config.php from being committed
├── controller/
├── model/
├── view/
└── db/
    └── quiz_schema.sql     ← Database schema
```

## What Files Should NOT Be Committed

The `.gitignore` file prevents these from being committed:

- ✓ `config.php` - Your local database credentials
- ✓ `.env` files - Environment variables
- ✓ Session files - Temporary data
- ✓ Node modules - Dependencies (use package.json instead)
- ✓ Vendor directory - PHP dependencies (use composer.json instead)

## Troubleshooting

### "Database Connection Failed"
1. Check if MySQL is running in XAMPP Control Panel
2. Verify the port number (default: 3306, XAMPP often uses 3307)
3. Make sure the database "edumind" exists
4. Check your credentials in `config.php`

### "Class 'config' not found"
- Ensure `config.php` exists in the quiz folder
- Check that the file path is correct

### Database Tables Not Found
- Run the SQL schema from `db/quiz_schema.sql` in phpMyAdmin
- Ensure all tables are created: quizzes, questions, question_options, quiz_attempts, attempt_answers

## For Team Development

When pulling from git:
1. Pull the repository
2. Copy `config.example.php` to `config.php`
3. Update `config.php` with YOUR local database credentials
4. DON'T commit `config.php` - git will ignore it automatically

## Git Commands

### Check what will be committed
```bash
git status
```

### After fixing configuration, verify config.php is ignored
```bash
git check-ignore config.php
```

Should return: `config.php` (if properly ignored)


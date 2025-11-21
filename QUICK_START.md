# Quick Start Guide

## Step 1: Configure Database

1. Open `config/database.php`
2. Update these lines with your MySQL credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');        // Your MySQL username
   define('DB_PASS', '');            // Your MySQL password (leave empty if no password)
   define('DB_NAME', 'edumind_db');
   ```

## Step 2: Setup Database

Run the setup script to create the database and tables:

```bash
cd edumind_proooj
php setup_database.php
```

This will:
- Create the database `edumind_db`
- Create the `reports` table
- Insert sample data

## Step 3: Start PHP Server

In a terminal, run:

```bash
cd edumind_proooj
php -S localhost:8000
```

You should see:
```
PHP 8.x.x Development Server started
Listening on http://localhost:8000
```

## Step 4: Access the Application

Open your browser and go to:
```
http://localhost:8000/View/Back.html
```

## Troubleshooting

### "Connection refused" error
- Make sure the PHP server is running (Step 3)
- Check that port 8000 is not in use by another application

### Database connection error
- Verify MySQL/MariaDB is running
- Check your credentials in `config/database.php`
- Make sure you've run `setup_database.php`

### API not working
- Check browser console (F12) for errors
- Verify the API file exists at `api/reports.php`
- Check PHP error logs

## Need Help?

Check the full README.md for detailed documentation.


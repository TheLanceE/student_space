# Project Debug Environment

Standalone testing environment for projects.php functionality.

## Setup

1. Make sure XAMPP is running (Apache + MySQL)
2. Database `edumind` should exist with projects and tasks tables
3. Access via: `http://localhost/edumind/project_debug/`

## Features

- Simulates logged-in student session (stu_debug)
- Full CRUD operations for projects
- Console logging for debugging
- Standalone testing without full system dependencies

## File Structure

```
project_debug/
├── index.php           # Entry point with session simulation
├── Views/
│   └── projects.php    # Standalone projects UI with inline JS
├── Controllers/        # (empty - uses main Controllers/)
└── Models/             # (empty - uses main Models/)
```

## Testing

1. Open browser to `http://localhost/edumind/project_debug/`
2. Check browser console (F12) for debug output
3. Try creating, editing, and deleting projects
4. All API calls go to main ProjectController.php

## Notes

- This folder is in .gitignore and won't be pushed to GitHub
- Uses hardcoded user session for testing
- All console.log statements show API requests/responses
- Bootstrap 5.3 modals and styling included

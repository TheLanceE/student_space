# ⚡ Quick Test Guide

## 30-Second Test

### 1. Test API (Copy this URL to browser):
```
http://localhost/edumind/project_debug/Controllers/ProjectController.php?action=test
```

**Expected Result:**
```json
{"success":true,"message":"ProjectController is working!","user":"stu_debug","database":"connected"}
```

✅ If you see this JSON, your API is working!

❌ If you see HTML or error, check SETUP_GUIDE.md

---

### 2. Test Full App (Copy this URL to browser):
```
http://localhost/edumind/project_debug/
```

**Expected Result:**
- Page loads with EduMind+ header
- Green "DEBUG MODE" badge visible
- "+ New Project" button
- Empty state message

✅ If page loads, you're ready to use it!

❌ If error, see troubleshooting below

---

## Common Issues

### "Unexpected token '<'" Error

**Your friend is seeing this because:**
1. API path is wrong, OR
2. PHP not running, OR
3. Database connection failed

**Fix:**
1. Make sure XAMPP Apache is running (green)
2. Make sure XAMPP MySQL is running (green)
3. Test API URL above in browser
4. Check browser console (F12) for actual error

### Blank Page

**Fix:**
1. Check XAMPP Apache is running
2. View source (Right-click → View Page Source)
3. If you see PHP code, PHP is not running

### 404 Not Found

**Fix:**
1. Verify folder is at `C:\xampp\htdocs\edumind\project_debug\`
2. Check spelling of URL
3. Try: `http://localhost/edumind/project_debug/index.php`

---

## Test Checklist

Before sharing with your friend:

- [ ] Apache running in XAMPP
- [ ] MySQL running in XAMPP
- [ ] Database `edumind` exists
- [ ] API test URL returns JSON ✅
- [ ] Main app loads ✅
- [ ] Can create a project ✅
- [ ] Can add a task ✅
- [ ] Can delete a project ✅

---

## Quick Database Setup

If database doesn't exist, run this in phpMyAdmin (http://localhost/phpmyadmin):

```sql
CREATE DATABASE IF NOT EXISTS edumind;
USE edumind;

CREATE TABLE projects (
  id VARCHAR(20) PRIMARY KEY,
  user_id VARCHAR(20) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  status VARCHAR(20) DEFAULT 'Not Started',
  priority VARCHAR(20) DEFAULT 'Medium',
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE tasks (
  id VARCHAR(20) PRIMARY KEY,
  project_id VARCHAR(20) NOT NULL,
  title VARCHAR(255) NOT NULL,
  completed BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

---

## For Your Friend (Different Computer)

### Option 1: Same Files

1. Copy entire `project_debug` folder to them
2. They install XAMPP
3. Copy folder to `C:\xampp\htdocs\`
4. Run database setup above
5. Access at `http://localhost/project_debug/`

### Option 2: ZIP Package

You can ZIP the entire folder and send it:
- All CSS/JS included (no internet needed)
- Just needs XAMPP + database setup
- Works on Windows, Mac, Linux

---

## API Endpoint Reference

All endpoints use POST with `action` parameter:

```javascript
// Get all projects
fetch('../Controllers/ProjectController.php', {
  method: 'POST',
  body: JSON.stringify({ action: 'get_all_projects' })
})

// Create project
fetch('../Controllers/ProjectController.php', {
  method: 'POST',
  body: JSON.stringify({ 
    action: 'create_project',
    title: 'My Project',
    description: 'Description here',
    status: 'Not Started',
    priority: 'High',
    due_date: '2024-12-31'
  })
})
```

---

## Success! 🎉

If the API test returns JSON and the app loads, everything is working!

**Next**: See SETUP_GUIDE.md for detailed usage instructions.

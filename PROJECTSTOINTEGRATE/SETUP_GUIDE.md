# 🚀 Project Debug - Setup Guide

This is a **fully standalone** version of the EduMind+ Projects module for testing and mobile deployment.

## ✨ Features

- ✅ **Completely Standalone**: All CSS, JS, and Bootstrap files included locally
- ✅ **No External Dependencies**: Works without internet (except Bootstrap Icons)
- ✅ **Mobile-Ready**: Responsive design, works on any device
- ✅ **Full CRUD**: Create, Read, Update, Delete projects and tasks
- ✅ **Local Controller**: Own PHP backend with database connection

## 📁 Structure

```
project_debug/
├── index.php                      # Entry point (session setup)
├── Controllers/
│   └── ProjectController.php      # Standalone PHP API
├── Views/
│   └── projects.php               # Main UI
├── assets/
│   ├── css/
│   │   └── debug.css              # Custom styling
│   ├── js/
│   │   └── projects.js            # Frontend logic (300+ lines)
│   └── vendor/
│       ├── bootstrap.min.css      # Bootstrap CSS (local)
│       └── bootstrap.bundle.min.js # Bootstrap JS (local)
└── README.md
```

## 🔧 Setup Instructions

### Step 1: Database Setup

Make sure MySQL is running with the `edumind` database:

```sql
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS edumind;

-- Create projects table
CREATE TABLE IF NOT EXISTS projects (
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

-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks (
  id VARCHAR(20) PRIMARY KEY,
  project_id VARCHAR(20) NOT NULL,
  title VARCHAR(255) NOT NULL,
  completed BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

### Step 2: Configure Database (if needed)

Edit `Controllers/ProjectController.php` lines 16-19:

```php
define('DB_HOST', 'localhost');    // Change if needed
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password (if any)
define('DB_NAME', 'edumind');      // Database name
```

### Step 3: Deploy

#### Option A: XAMPP (Windows)
```powershell
# Copy to htdocs
Copy-Item -Path "project_debug" -Destination "C:\xampp\htdocs\" -Recurse

# Start XAMPP (Apache + MySQL)
# Access at: http://localhost/project_debug/
```

#### Option B: PHP Built-in Server
```bash
cd project_debug
php -S localhost:8000
# Access at: http://localhost:8000/
```

#### Option C: Upload to Hosting
- Upload entire folder to web hosting
- Update database credentials
- Access via your domain

## 🧪 Testing

### 1. Test API Connection

Open in browser:
```
http://localhost/project_debug/Controllers/ProjectController.php?action=test
```

**Expected Response:**
```json
{
  "success": true,
  "message": "ProjectController is working!",
  "user": "stu_debug",
  "database": "connected"
}
```

### 2. Test Projects List

```
http://localhost/project_debug/Controllers/ProjectController.php?action=get_all_projects
```

**Expected Response:**
```json
{
  "success": true,
  "projects": []
}
```

### 3. Test Main UI

```
http://localhost/project_debug/
```

Should show:
- Green "DEBUG MODE" badge
- "+ New Project" button
- Empty state message (if no projects)

## 🐛 Troubleshooting

### ❌ "Unexpected token '<'" JSON Parse Error

**Problem**: API returning HTML instead of JSON

**Solutions**:

1. **Check Apache is running**:
   - Start XAMPP Control Panel
   - Click "Start" on Apache and MySQL

2. **Test API directly** (see Testing section above)

3. **Check file paths**:
   - Open Browser Dev Tools (F12) → Network tab
   - Look for API request
   - Check actual URL being called

4. **Verify database connection**:
   - Check MySQL is running
   - Verify `edumind` database exists
   - Test credentials in `ProjectController.php`

5. **Check PHP errors**:
   - Open `xampp/apache/logs/error.log`
   - Look for recent errors

### ❌ Projects Not Saving

**Solutions**:

1. **Open Browser Console** (F12):
   - Look for error messages
   - Check API response

2. **Check Network Tab**:
   - See what the API is returning
   - Should be JSON with `{"success": true}`

3. **Verify database structure**:
   - Run the CREATE TABLE commands again
   - Check user_id matches (default: 'stu_debug')

### ❌ Blank Page

**Solutions**:

1. **Check PHP is installed**:
   - Create file `test.php` with: `<?php phpinfo(); ?>`
   - Access in browser

2. **Check file permissions**:
   - Ensure files are readable by web server

3. **Check Apache error logs**

### ❌ CSS/JS Not Loading

**Solutions**:

1. **Verify file structure**:
   ```
   project_debug/
   ├── assets/
   │   ├── css/debug.css
   │   ├── js/projects.js
   │   └── vendor/bootstrap.min.css
   ```

2. **Check browser console** (F12):
   - Look for 404 errors
   - Verify paths are correct

3. **Hard refresh**: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)

## 📱 Mobile Access

### Same WiFi Network

1. Find your computer's IP:
   ```powershell
   ipconfig
   # Look for "IPv4 Address" (e.g., 192.168.1.100)
   ```

2. On mobile browser:
   ```
   http://192.168.1.100/project_debug/
   ```

### Online Hosting

1. Upload to free hosting (e.g., 000webhost, InfinityFree)
2. Import database
3. Update `ProjectController.php` with hosting database credentials
4. Access via hosting URL

## 🎯 Usage Guide

### Create a Project

1. Click **"+ New Project"** button
2. Fill in:
   - **Title**: Project name (required)
   - **Description**: Details about the project
   - **Status**: Not Started / In Progress / Completed
   - **Priority**: Low / Medium / High
   - **Due Date**: Deadline (optional)
3. Click **"Save Project"**

### Add Tasks

1. Click on a project card
2. View project details in modal
3. Enter task description
4. Click **"+ Add Task"**
5. Check off tasks when completed

### Edit Project

1. Click on project card
2. Click **"Edit Project"** button
3. Update fields
4. Click **"Save Changes"**

### Delete Project

1. Click on project card
2. Click **"Delete Project"** button
3. Confirm deletion
4. Project and all tasks will be removed

## 💡 Features Explained

### Debug Badge
- Green **"DEBUG MODE"** badge at top
- Indicates standalone testing environment
- Can be removed in production

### Status Colors
- 🔵 **Not Started**: Blue
- 🟡 **In Progress**: Yellow
- 🟢 **Completed**: Green

### Priority Badges
- 🔴 **High**: Red
- 🟡 **Medium**: Yellow
- 🟢 **Low**: Green

### Progress Tracking
- Automatic task completion calculation
- Progress bar on each project card
- Shows X/Y tasks completed

### Responsive Design
- Works on mobile phones
- Tablet optimized
- Desktop full-screen

## 🔒 Security Notes

⚠️ **This is a DEBUG/TEST environment**:

- ❌ No authentication required
- ❌ Default user hardcoded ('stu_debug')
- ❌ Error messages visible
- ❌ Not production-ready

**For production use**:
- ✅ Add proper login system
- ✅ Validate all user inputs
- ✅ Hide error messages
- ✅ Use HTTPS
- ✅ Add CSRF protection
- ✅ Implement rate limiting

**Already implemented**:
- ✅ SQL injection protection (PDO prepared statements)
- ✅ JSON responses
- ✅ Error handling

## 📞 Still Having Issues?

### Debug Checklist

- [ ] XAMPP Apache is running (green light)
- [ ] XAMPP MySQL is running (green light)
- [ ] Database `edumind` exists
- [ ] Tables `projects` and `tasks` exist
- [ ] API test returns JSON: `...ProjectController.php?action=test`
- [ ] Browser console (F12) shows no errors
- [ ] Network tab shows API requests succeeding

### Get More Help

1. **Check Browser Console**:
   - Press F12
   - Go to Console tab
   - Look for red error messages

2. **Check Network Requests**:
   - F12 → Network tab
   - Reload page
   - Look for failed requests (red)
   - Click on request → Preview tab

3. **Check PHP Errors**:
   - Open `C:\xampp\apache\logs\error.log`
   - Look at the bottom for recent errors

4. **Test Database Connection**:
   - Create file `test_db.php`:
     ```php
     <?php
     try {
         $db = new PDO("mysql:host=localhost;dbname=edumind", "root", "");
         echo "Database connected!";
     } catch (Exception $e) {
         echo "Error: " . $e->getMessage();
     }
     ```
   - Access in browser

## 🎉 Success!

If you see the projects page with the debug badge and can create projects, you're all set!

**Next Steps**:
- Create your first project
- Add some tasks
- Test on mobile
- Share with friends

---

**Version**: 1.0 Standalone  
**PHP**: 7.4+ required  
**MySQL**: 5.7+ required  
**Dependencies**: PDO extension (included with XAMPP)

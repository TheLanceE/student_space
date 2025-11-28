# 🚀 EduMind+ Modernization Implementation Guide

## Overview
This guide covers the implementation of:
1. ✅ **Google OAuth Login** (Create/Login with Google account)
2. ✅ **Admin Bulk Operations** (Delete users, courses, events, quizzes)
3. ✅ **Fake Account Detection** (Auto-detect and remove spam accounts)
4. ✅ **Security Enhancements** (CSRF protection, input validation, rate limiting)
5. ✅ **Soft Deletes** (Safe deletion with recovery option)

---

## 📋 Prerequisites

### 1. Google OAuth Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable **Google+ API**
4. Go to **Credentials** → Create **OAuth 2.0 Client ID**
5. Configure:
   - Application type: **Web application**
   - Authorized JavaScript origins: `http://localhost`
   - Authorized redirect URIs: `http://localhost/edumind/Controllers/google_oauth_callback.php`
6. Copy **Client ID** and **Client Secret**

### 2. PHP Requirements
- PHP 7.4+ (8.x recommended)
- Extensions: `curl`, `json`, `pdo_mysql`
- Composer (optional, for dependencies)

---

## 🔧 Installation Steps

### Step 1: Run Database Migration

```powershell
# Open MySQL command line or phpMyAdmin
cd c:\xampp\mysql\bin
.\mysql.exe -u root -p
```

```sql
source C:/Users/LanceE/Downloads/Front&Back/database_modernization_migration.sql
```

Or via phpMyAdmin:
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select `edumind` database
3. Go to **SQL** tab
4. Copy contents of `database_modernization_migration.sql`
5. Execute

**What this does:**
- Adds `deleted_at` columns for soft deletes
- Adds `google_id` columns for OAuth
- Creates `admin_audit_log` table
- Creates `rate_limits` table
- Adds performance indexes

---

### Step 2: Configure Google OAuth

Edit `Controllers/GoogleOAuthHandler.php`:

```php
// Line 9-10, replace with your credentials
define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
```

**Example:**
```php
define('GOOGLE_CLIENT_ID', '123456789-abc123.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'GOCSPX-xyz789_example');
```

---

### Step 3: Update Login Pages with Google Button

Add to **all login pages** (student, teacher, admin):

```php
<!-- Add before closing </head> -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<meta name="csrf-token" content="<?php echo CSRFProtection::getToken(); ?>">
```

```php
<!-- Add after password field, before submit button -->
<div class="divider my-3">
    <span class="divider-text">OR</span>
</div>

<div class="d-grid">
    <a href="<?php echo get_google_oauth_url(); ?>" class="btn btn-outline-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
            <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
        </svg>
        Sign in with Google
    </a>
</div>
```

**CSS for divider:**
```css
.divider {
    display: flex;
    align-items: center;
    text-align: center;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #ddd;
}

.divider-text {
    padding: 0 10px;
    color: #666;
    font-size: 0.9rem;
}
```

---

### Step 4: Deploy Files to XAMPP

```powershell
# Copy all new files
$src = "c:\Users\LanceE\Downloads\Front&Back"
$dst = "C:\xampp\htdocs\edumind"

# Controllers
Copy-Item "$src\Controllers\GoogleOAuthHandler.php" "$dst\Controllers\" -Force
Copy-Item "$src\Controllers\google_oauth_callback.php" "$dst\Controllers\" -Force
Copy-Item "$src\Controllers\AdminApiController.php" "$dst\Controllers\" -Force
Copy-Item "$src\Controllers\SecurityHelpers.php" "$dst\Controllers\" -Force

# JavaScript
Copy-Item "$src\shared-assets\js\admin-modern.js" "$dst\shared-assets\js\" -Force

Write-Output "✅ All files deployed!"
```

---

### Step 5: Update Admin Pages for Bulk Operations

#### Example: users.php

```php
<?php
require_once '../../Controllers/config.php';
require_once '../../Controllers/SecurityHelpers.php';

// Generate CSRF token
$csrf_token = CSRFProtection::getToken();
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
 <title>Users | Admin</title>
 <link href="../../shared-assets/vendor/bootstrap.min.css" rel="stylesheet">
 <link href="../../shared-assets/css/navbar-styles.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body data-page="admin-users">
 <!-- Navbar here -->
 
 <main class="container py-4">
   <div class="card shadow-sm">
     <div class="card-body">
       <div class="d-flex justify-content-between align-items-center mb-3">
         <h2 class="h5 mb-0">User Management</h2>
         <button class="btn btn-warning" onclick="detectFakeAccounts()">
           <i class="bi bi-search"></i> Detect Fake Accounts
         </button>
       </div>
       
       <div id="usersTable">Loading...</div>
     </div>
   </div>
 </main>

 <script src="../../shared-assets/vendor/bootstrap.bundle.min.js"></script>
 <script src="../../shared-assets/js/admin-modern.js"></script>
 <script>
   // Load users on page load
   document.addEventListener('DOMContentLoaded', async () => {
     try {
       const result = await adminAPI.getUsers();
       const allUsers = [
         ...result.data.students.map(u => ({...u, role: 'student'})),
         ...result.data.teachers.map(u => ({...u, role: 'teacher'})),
         ...result.data.admins.map(u => ({...u, role: 'admin'}))
       ];
       
       bulkUI.renderTable('usersTable', allUsers, [
         { label: 'Role', field: 'role', format: (v) => `<span class="badge bg-${v === 'admin' ? 'danger' : v === 'teacher' ? 'success' : 'primary'}">${v}</span>` },
         { label: 'Username', field: 'username' },
         { label: 'Name', field: 'fullName' },
         { label: 'Email', field: 'email' },
         { label: 'Created', field: 'createdAt', format: (v) => new Date(v).toLocaleDateString() },
         { label: 'Last Login', field: 'lastLoginAt', format: (v) => v ? new Date(v).toLocaleDateString() : 'Never' }
       ]);
     } catch (error) {
       document.getElementById('usersTable').innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
     }
   });
 </script>
</body>
</html>
```

Repeat similar pattern for:
- `courses.php`
- `events.php`
- `quiz-reports.php` (rename to `quizzes.php`)

---

### Step 6: Enable CSRF Protection on Forms

Update all POST handlers to validate CSRF:

```php
<?php
require_once __DIR__ . '/SecurityHelpers.php';

session_start();

// Validate CSRF token
CSRFProtection::validateRequest();

// ... rest of handler code
?>
```

---

### Step 7: Add Input Validation

Update login handlers:

```php
<?php
require_once __DIR__ . '/SecurityHelpers.php';
require_once __DIR__ . '/config.php';

CSRFProtection::validateRequest();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validate inputs
if (!InputValidator::validateUsername($username)) {
    $_SESSION['error'] = 'Invalid username format';
    header('Location: ../Views/admin-back-office/login.php');
    exit;
}

// Rate limiting
$rate_limiter = new RateLimiter($db_connection);
$ip = $_SERVER['REMOTE_ADDR'];

if ($rate_limiter->isRateLimited('admin_login', $ip, 5, 300)) {
    $_SESSION['error'] = 'Too many login attempts. Please try again later.';
    header('Location: ../Views/admin-back-office/login.php');
    exit;
}

// ... rest of login logic
?>
```

---

## 🧪 Testing

### Test Google OAuth
1. Navigate to login page
2. Click "Sign in with Google"
3. Select Google account
4. Should redirect to dashboard
5. Check database for new user with `google_id` populated

### Test Bulk Delete
1. Log in as admin
2. Go to Users page
3. Select multiple users
4. Click "Delete Selected"
5. Confirm deletion
6. Check database: `deleted_at` should be set (not hard deleted)

### Test Fake Account Detection
1. Admin → Users
2. Click "Detect Fake Accounts"
3. Should show accounts with no login or generic usernames
4. Select and delete

### Test CSRF Protection
1. Try submitting a form without CSRF token (should fail)
2. Inspect browser console for token presence
3. Verify 403 error on invalid token

---

## 🔒 Security Checklist

- [x] CSRF tokens on all forms
- [x] Input validation on all endpoints
- [x] Rate limiting on login (5 attempts per 5 minutes)
- [x] Soft deletes (can recover)
- [x] Audit logging for admin actions
- [x] Password hashing with bcrypt
- [x] SQL injection prevention (PDO prepared statements)
- [x] XSS prevention (htmlspecialchars)
- [x] OAuth state validation
- [x] Secure session management

---

## 📊 API Endpoints Reference

### Users
- `GET /api/users` - List all users
- `POST /api/users/bulk-delete` - Delete multiple users
  ```json
  {
    "ids": ["user1", "user2"],
    "role": "student"
  }
  ```
- `GET /api/users/detect-fake` - Find fake accounts

### Courses
- `GET /api/courses` - List courses
- `POST /api/courses/bulk-delete` - Delete courses
  ```json
  {
    "ids": ["course1", "course2"]
  }
  ```

### Events
- `GET /api/events` - List events
- `POST /api/events/bulk-delete` - Delete events

### Quizzes
- `GET /api/quizzes` - List quizzes
- `POST /api/quizzes/bulk-delete` - Delete quizzes

**Headers Required:**
```
Content-Type: application/json
X-CSRF-Token: [token from meta tag]
```

---

## 🐛 Troubleshooting

### Google OAuth not working
1. Check `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` are correct
2. Verify redirect URI matches Google Console exactly
3. Check PHP `curl` extension is enabled: `php -m | grep curl`
4. Enable error logging: `error_reporting(E_ALL); ini_set('display_errors', 1);`

### Bulk delete not working
1. Check browser console for JavaScript errors
2. Verify CSRF token is in `<meta>` tag
3. Check database migration ran successfully
4. Verify `AdminApiController.php` is accessible

### Rate limiting too aggressive
Adjust in `SecurityHelpers.php`:
```php
// Line in RateLimiter class
if ($rate_limiter->isRateLimited('login', $ip, 10, 600)) { // 10 attempts per 10 minutes
```

---

## 🎯 Next Steps

### Phase 1 (Completed)
- ✅ Google OAuth integration
- ✅ Bulk delete operations
- ✅ Fake account detection
- ✅ CSRF protection
- ✅ Input validation
- ✅ Rate limiting

### Phase 2 (Recommended)
- [ ] Email notifications (PHPMailer)
- [ ] 2FA with TOTP (Google Authenticator)
- [ ] Password reset flow
- [ ] User profile pictures with upload
- [ ] Export data (GDPR compliance)

### Phase 3 (Advanced)
- [ ] Real-time notifications (WebSockets)
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native + REST API)
- [ ] Automated backups
- [ ] Performance monitoring

---

## 📞 Support

For issues or questions:
1. Check `audit_results.json` for code quality issues
2. Review `CODE_AUDIT_COMPREHENSIVE_2025.md` for security recommendations
3. Enable debug mode: Set `define('DEBUG_MODE', true);` in `config.php`
4. Check PHP error logs: `C:\xampp\php\logs\php_error_log`

---

## ✅ Verification Commands

```powershell
# Check if migration ran
mysql -u root -e "DESCRIBE edumind.students" | findstr deleted_at

# Check if OAuth handler exists
Test-Path "C:\xampp\htdocs\edumind\Controllers\GoogleOAuthHandler.php"

# Check if admin API is accessible
Invoke-WebRequest -Uri "http://localhost/edumind/Controllers/AdminApiController.php/users" -Method GET
```

**Implementation Complete!** 🎉

# EduMind+ Codebook Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Database Architecture](#database-architecture)
3. [MVC Structure](#mvc-structure)
4. [User Management System](#user-management-system)
5. [JavaScript Functions Reference](#javascript-functions-reference)
6. [Database Interactions](#database-interactions)
7. [Authentication Flow](#authentication-flow)

---

## System Overview

**EduMind+** is a PHP-based educational management platform using the MVC (Model-View-Controller) architecture pattern. The system supports three user roles:
- **Students** (Front Office) - Blue theme
- **Teachers** (Teacher Back Office) - Green theme  
- **Admins** (Admin Back Office) - Red theme

**Technology Stack:**
- **Backend**: PHP 7.4+ with PDO for database operations
- **Frontend**: Bootstrap 5.3, vanilla JavaScript
- **Database**: MySQL via PDO (PHP Data Objects)
- **Architecture**: MVC pattern with role-based access control

---

## Database Architecture

### PDO Connection

PDO (PHP Data Objects) is used for secure database interactions with prepared statements to prevent SQL injection.

**Connection Setup** (Controllers/config.php):
```php
$host = 'localhost';
$dbname = 'edumind';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

**Key PDO Features Used:**
- `PDO::ATTR_ERRMODE` → `ERRMODE_EXCEPTION` - Throws exceptions on errors
- `PDO::ATTR_DEFAULT_FETCH_MODE` → `FETCH_ASSOC` - Returns associative arrays
- `charset=utf8mb4` - Full UTF-8 support including emojis

### Database Schema (Users Table)

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- BCrypt hashed
    role_id INT NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL  -- 'student', 'teacher', 'admin'
);
```

**Password Security:**
- Passwords are hashed using BCrypt via `password_hash()`
- Verification uses `password_verify()` for constant-time comparison
- Minimum hash strength: `PASSWORD_DEFAULT` (currently BCrypt with cost 10)

---

## MVC Structure

### Directory Organization

```
├── Controllers/          # Business logic and API endpoints
│   ├── AuthController.php      # User authentication
│   ├── config.php              # Database connection
│   └── ...
├── Models/              # Data layer (database interactions)
│   ├── User.php               # User CRUD operations
│   └── ...
├── Views/               # Presentation layer
│   ├── front-office/          # Student portal
│   ├── teacher-back-office/   # Teacher portal
│   └── admin-back-office/     # Admin portal
└── shared-assets/       # Shared CSS, JS, images
    ├── css/
    ├── js/
    └── vendor/
```

### How Files Are Linked

**1. View → Controller → Model Flow:**
```
[View: login.php]
    ↓ (User submits form)
[JavaScript: auth.js] 
    ↓ (Fetch API POST request)
[Controller: AuthController.php]
    ↓ (Business logic, validation)
[Model: User.php]
    ↓ (Database query via PDO)
[Database: MySQL]
```

**2. Example Request Flow:**

**View (Views/front-office/login.php):**
```html
<form id="loginForm">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>

<script src="assets/js/auth.js"></script>
```

**JavaScript (Views/front-office/assets/js/auth.js):**
```javascript
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const response = await fetch('/edumind/Controllers/AuthController.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    if (result.success) {
        window.location.href = 'dashboard.php';
    }
});
```

**Controller (Controllers/AuthController.php):**
```php
require_once '../Models/User.php';
require_once 'config.php';

$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $user = $userModel->login($email, $password);
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}
```

**Model (Models/User.php):**
```php
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Don't return password hash
            return $user;
        }
        return false;
    }
}
```

---

## User Management System

### CRUD Operations (Models/User.php)

#### CREATE - Add New User
```php
public function create($username, $email, $password, $role_id, $first_name, $last_name) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $this->pdo->prepare("
        INSERT INTO users (username, email, password, role_id, first_name, last_name)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    try {
        $stmt->execute([$username, $email, $hashedPassword, $role_id, $first_name, $last_name]);
        return $this->pdo->lastInsertId();
    } catch (PDOException $e) {
        // Handle duplicate username/email errors
        if ($e->getCode() === '23000') {
            return false; // Duplicate entry
        }
        throw $e;
    }
}
```

**Key Points:**
- Uses `password_hash()` with `PASSWORD_DEFAULT` (BCrypt)
- Returns `lastInsertId()` for the new user's ID
- Catches `23000` error code for duplicate entries
- Prepared statements prevent SQL injection

#### READ - Get User(s)
```php
// Get single user by ID
public function getById($user_id) {
    $stmt = $this->pdo->prepare("
        SELECT u.*, r.role_name 
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Get all users (with optional role filter)
public function getAll($role_id = null) {
    $sql = "
        SELECT u.*, r.role_name 
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
    ";
    
    if ($role_id !== null) {
        $sql .= " WHERE u.role_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$role_id]);
    } else {
        $stmt = $this->pdo->query($sql);
    }
    
    return $stmt->fetchAll();
}
```

**Key Points:**
- Joins with `roles` table for role name
- `fetch()` returns single record, `fetchAll()` returns array
- Password hash excluded from returned data (use `unset()`)

#### UPDATE - Modify User
```php
public function update($user_id, $data) {
    // Build dynamic UPDATE query
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        if ($key === 'password') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    
    $values[] = $user_id; // WHERE clause parameter
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
    $stmt = $this->pdo->prepare($sql);
    
    return $stmt->execute($values);
}
```

**Key Points:**
- Dynamic query building for flexible updates
- Automatically hashes password if updated
- Returns boolean success status
- All values sanitized via prepared statement

#### DELETE - Remove User
```php
public function delete($user_id) {
    $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}
```

**Key Points:**
- Simple prepared statement
- Returns `true` on success
- Cascade deletes handled by foreign key constraints

---

## JavaScript Functions Reference

### Authentication Module (auth.js, auth-teacher.js, auth-admin.js)

All three portals have similar authentication JavaScript with portal-specific paths.

#### `handleLogin(event)`
```javascript
async function handleLogin(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Store user data in localStorage
            localStorage.setItem('user', JSON.stringify(result.user));
            window.location.href = 'dashboard.php';
        } else {
            alert(result.message || 'Login failed');
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('An error occurred during login');
    }
}

// Attach to form
document.getElementById('loginForm')?.addEventListener('submit', handleLogin);
```

**What it does:**
- Prevents default form submission
- Sends credentials to AuthController via Fetch API
- Stores user data in `localStorage` on success
- Redirects to dashboard
- Shows error messages on failure

#### `handleRegister(event)`
```javascript
async function handleRegister(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Validate passwords match
    if (formData.get('password') !== formData.get('confirmPassword')) {
        alert('Passwords do not match');
        return;
    }
    
    try {
        const response = await fetch('../../Controllers/AuthController.php?action=register', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Registration successful! Please login.');
            window.location.href = 'login.php';
        } else {
            alert(result.message || 'Registration failed');
        }
    } catch (error) {
        console.error('Registration error:', error);
    }
}
```

**What it does:**
- Validates password confirmation
- Sends registration data to AuthController
- Redirects to login page on success
- Client-side validation before API call

#### `checkAuth()`
```javascript
function checkAuth() {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    
    if (!user) {
        window.location.href = 'login.php';
        return null;
    }
    
    // Check role-based access
    const currentPortal = document.body.dataset.portal; // 'student', 'teacher', 'admin'
    if (user.role_name !== currentPortal) {
        alert('Access denied');
        localStorage.clear();
        window.location.href = 'login.php';
        return null;
    }
    
    return user;
}

// Run on page load
const currentUser = checkAuth();
```

**What it does:**
- Checks if user is logged in (localStorage check)
- Validates role matches portal (prevents URL manipulation)
- Redirects to login if unauthorized
- Returns user object for page use

#### `handleLogout()`
```javascript
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.clear();
        window.location.href = 'login.php';
    }
}

// Attach to logout button
document.getElementById('logoutBtn')?.addEventListener('click', handleLogout);
```

**What it does:**
- Clears all localStorage data
- Confirms before logout
- Redirects to login page

---

### Storage Module (storage.js)

Provides localStorage abstraction for all portals.

#### `StorageManager` Object
```javascript
const StorageManager = {
    // Get user data
    getUser() {
        return JSON.parse(localStorage.getItem('user') || 'null');
    },
    
    // Set user data
    setUser(userData) {
        localStorage.setItem('user', JSON.stringify(userData));
    },
    
    // Get cached data with expiration
    getCache(key, maxAge = 3600000) { // Default 1 hour
        const cached = localStorage.getItem(key);
        if (!cached) return null;
        
        const { data, timestamp } = JSON.parse(cached);
        if (Date.now() - timestamp > maxAge) {
            localStorage.removeItem(key);
            return null;
        }
        return data;
    },
    
    // Set cached data with timestamp
    setCache(key, data) {
        localStorage.setItem(key, JSON.stringify({
            data,
            timestamp: Date.now()
        }));
    },
    
    // Clear all storage
    clearAll() {
        localStorage.clear();
    }
};
```

**What it does:**
- Wraps localStorage operations
- Adds timestamp-based cache expiration
- Provides consistent interface across all portals
- Handles JSON serialization automatically

---

### Data Loading Modules (data.js, data-teacher.js, data-admin.js)

#### `loadUsers()` - Admin Portal
```javascript
async function loadUsers() {
    try {
        // Check cache first
        let users = StorageManager.getCache('users');
        
        if (!users) {
            const response = await fetch('../../Controllers/UserController.php?action=getAll');
            const result = await response.json();
            
            if (result.success) {
                users = result.users;
                StorageManager.setCache('users', users);
            }
        }
        
        renderUsersTable(users);
    } catch (error) {
        console.error('Error loading users:', error);
    }
}
```

**What it does:**
- Checks localStorage cache before API call
- Fetches from UserController if cache expired
- Caches response for 1 hour
- Renders data to UI

#### `createUser(userData)`
```javascript
async function createUser(userData) {
    try {
        const formData = new FormData();
        Object.entries(userData).forEach(([key, value]) => {
            formData.append(key, value);
        });
        
        const response = await fetch('../../Controllers/UserController.php?action=create', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear cache to force reload
            localStorage.removeItem('users');
            loadUsers();
            return true;
        } else {
            alert(result.message);
            return false;
        }
    } catch (error) {
        console.error('Error creating user:', error);
        return false;
    }
}
```

**What it does:**
- Converts object to FormData for API
- Sends POST request to UserController
- Invalidates cache on success (removes 'users' key)
- Reloads data to show new user

#### `updateUser(user_id, userData)`
```javascript
async function updateUser(user_id, userData) {
    try {
        const formData = new FormData();
        formData.append('user_id', user_id);
        Object.entries(userData).forEach(([key, value]) => {
            formData.append(key, value);
        });
        
        const response = await fetch('../../Controllers/UserController.php?action=update', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            localStorage.removeItem('users');
            loadUsers();
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error updating user:', error);
        return false;
    }
}
```

**What it does:**
- Same pattern as create but includes `user_id`
- Updates existing record
- Cache invalidation and reload

#### `deleteUser(user_id)`
```javascript
async function deleteUser(user_id) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return false;
    }
    
    try {
        const response = await fetch(`../../Controllers/UserController.php?action=delete&user_id=${user_id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            localStorage.removeItem('users');
            loadUsers();
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error deleting user:', error);
        return false;
    }
}
```

**What it does:**
- Confirms deletion with user
- Sends DELETE request with user_id parameter
- Cache invalidation and reload
- Returns boolean for error handling

---

### UI Helper Functions (ui.js, ui-teacher.js)

#### `renderUsersTable(users)`
```javascript
function renderUsersTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.user_id}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td><span class="badge bg-${getRoleBadgeColor(user.role_name)}">${user.role_name}</span></td>
            <td>${user.first_name} ${user.last_name}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editUser(${user.user_id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.user_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function getRoleBadgeColor(role) {
    const colors = {
        'admin': 'danger',
        'teacher': 'success',
        'student': 'primary'
    };
    return colors[role] || 'secondary';
}
```

**What it does:**
- Generates HTML table rows from user array
- Color-codes role badges
- Attaches onclick handlers for edit/delete
- Uses template literals for clean HTML generation

#### `showModal(modalId, data = null)`
```javascript
function showModal(modalId, data = null) {
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    
    if (data) {
        // Pre-fill form fields with data
        Object.entries(data).forEach(([key, value]) => {
            const input = document.querySelector(`#${modalId} [name="${key}"]`);
            if (input) {
                input.value = value;
            }
        });
    } else {
        // Clear form for new entry
        document.querySelector(`#${modalId} form`)?.reset();
    }
    
    modal.show();
}
```

**What it does:**
- Creates Bootstrap modal instance
- Pre-fills form if editing (data provided)
- Clears form if creating new (data null)
- Programmatically shows modal

---

## Database Interactions

### How Controllers Interact with Models

**Controller (Controllers/UserController.php):**
```php
<?php
require_once '../Models/User.php';
require_once 'config.php';

header('Content-Type: application/json');

$userModel = new User($pdo);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        $users = $userModel->getAll();
        echo json_encode(['success' => true, 'users' => $users]);
        break;
        
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $userModel->create(
                $_POST['username'],
                $_POST['email'],
                $_POST['password'],
                $_POST['role_id'],
                $_POST['first_name'],
                $_POST['last_name']
            );
            
            if ($userId) {
                echo json_encode(['success' => true, 'user_id' => $userId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User creation failed']);
            }
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'];
            unset($_POST['user_id']);
            
            $success = $userModel->update($user_id, $_POST);
            echo json_encode(['success' => $success]);
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $user_id = $_GET['user_id'];
            $success = $userModel->delete($user_id);
            echo json_encode(['success' => $success]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
```

**Key Points:**
- Controller receives HTTP request
- Validates action parameter
- Calls appropriate Model method
- Returns JSON response
- Model handles all database operations
- Controller focuses on request/response logic

### PDO Query Patterns

#### Prepared Statements (Prevents SQL Injection)
```php
// BAD - Vulnerable to SQL injection
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($sql);

// GOOD - Safe with prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$result = $stmt->fetch();
```

#### Named Placeholders
```php
$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password) 
    VALUES (:username, :email, :password)
");

$stmt->execute([
    ':username' => $username,
    ':email' => $email,
    ':password' => $hashedPassword
]);
```

#### Transactions (Atomic Operations)
```php
try {
    $pdo->beginTransaction();
    
    // Multiple operations
    $stmt1 = $pdo->prepare("INSERT INTO users ...");
    $stmt1->execute([...]);
    
    $userId = $pdo->lastInsertId();
    
    $stmt2 = $pdo->prepare("INSERT INTO user_profiles ...");
    $stmt2->execute(['user_id' => $userId, ...]);
    
    $pdo->commit(); // Both succeed or both fail
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## Authentication Flow

### Complete Login Process

```
1. User enters credentials on login.php
   ↓
2. JavaScript (auth.js) prevents default form submit
   ↓
3. FormData sent via Fetch API to AuthController.php
   ↓
4. AuthController.php receives POST request
   ↓
5. Creates User model instance with PDO connection
   ↓
6. Calls $userModel->login($email, $password)
   ↓
7. Model queries database with prepared statement:
   SELECT u.*, r.role_name 
   FROM users u
   JOIN roles r ON u.role_id = r.role_id
   WHERE u.email = ?
   ↓
8. PDO executes query with bound parameter
   ↓
9. Model fetches user record
   ↓
10. Model verifies password with password_verify()
    ↓
11. If valid, returns user data (without password hash)
    ↓
12. Controller sends JSON response: {success: true, user: {...}}
    ↓
13. JavaScript stores user in localStorage
    ↓
14. JavaScript redirects to dashboard.php
    ↓
15. Dashboard calls checkAuth() on load
    ↓
16. checkAuth() validates localStorage and role
    ↓
17. Page content loads with user data
```

### Role-Based Access Control

**Every protected page runs this check:**
```javascript
// At top of page load
const currentUser = checkAuth();

function checkAuth() {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    
    // Not logged in
    if (!user) {
        window.location.href = 'login.php';
        return null;
    }
    
    // Wrong role (URL manipulation attempt)
    const portalRole = document.body.dataset.portal; // From <body data-portal="student">
    if (user.role_name !== portalRole) {
        alert('Access denied - wrong role');
        localStorage.clear();
        window.location.href = 'login.php';
        return null;
    }
    
    return user;
}
```

**Security Layers:**
1. **Client-side**: JavaScript checks localStorage and role
2. **Server-side**: Controllers should verify session/token (not implemented yet)
3. **Database**: Role stored in database, joined on queries

**Current Limitations:**
- No server-side session validation (relies on localStorage)
- No CSRF token protection
- No rate limiting on login attempts

---

## Common Patterns & Best Practices

### Error Handling Pattern
```javascript
async function apiCall() {
    try {
        const response = await fetch('/api/endpoint');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const result = await response.json();
        
        if (!result.success) {
            alert(result.message || 'Operation failed');
            return false;
        }
        
        return result.data;
    } catch (error) {
        console.error('API Error:', error);
        alert('An unexpected error occurred');
        return false;
    }
}
```

### Cache Invalidation Strategy
```javascript
// After CREATE/UPDATE/DELETE operations
localStorage.removeItem('cacheKey'); // Invalidate
loadData(); // Reload fresh data

// Example:
async function updateUser(id, data) {
    const success = await apiUpdate(id, data);
    if (success) {
        localStorage.removeItem('users'); // Clear cache
        loadUsers(); // Fetch fresh from API
    }
}
```

### Modal Lifecycle
```javascript
// Open modal
function openModal(data = null) {
    const modal = new bootstrap.Modal('#myModal');
    
    if (data) {
        fillForm(data); // Edit mode
    } else {
        clearForm(); // Create mode
    }
    
    modal.show();
}

// Save and close
async function saveModal() {
    const formData = getFormData();
    const success = await apiSave(formData);
    
    if (success) {
        bootstrap.Modal.getInstance('#myModal').hide();
        
        // Clean up backdrop (fix for Bootstrap bug)
        setTimeout(() => {
            document.querySelector('.modal-backdrop')?.remove();
            document.body.classList.remove('modal-open');
        }, 100);
    }
}
```

---

## Quick Reference

### File Path Conventions
- **Absolute from root**: `/edumind/Controllers/AuthController.php`
- **Relative from view**: `../../Controllers/AuthController.php`
- **Shared assets**: `../../shared-assets/css/global.css`

### Common HTTP Methods
- **GET**: Retrieve data (query string parameters)
- **POST**: Create/update data (FormData body)
- **DELETE**: Remove data (query string parameters)
- **PUT**: Full update (not commonly used in this project)

### LocalStorage Keys
- `user` - Current logged-in user object
- `users` - Cached user list (admin portal)
- `courses` - Cached course list
- `[key]` - Generic cached data with timestamp

### Bootstrap Components Used
- **Navbar**: `navbar`, `navbar-brand`, `nav-link`, `nav-item`
- **Modal**: `modal`, `modal-dialog`, `modal-content`, `modal-header/body/footer`
- **Forms**: `form-control`, `form-label`, `form-select`
- **Buttons**: `btn`, `btn-primary`, `btn-outline-*`
- **Badges**: `badge`, `bg-danger/success/primary`
- **Utilities**: `d-flex`, `justify-content-between`, `mb-4`, `py-4`

---

## Maintenance Notes

**When adding new features:**
1. Create Model class with CRUD methods using PDO
2. Create Controller as API endpoint with action routing
3. Add JavaScript functions for UI interactions
4. Update this documentation with new patterns

**When debugging:**
1. Check browser DevTools Console for JavaScript errors
2. Check Network tab for API responses
3. Check PHP error logs at `C:/xampp/php/logs/php_error_log`
4. Use `console.log()` in JS and `error_log()` in PHP

**Security Checklist:**
- [ ] All database queries use prepared statements
- [ ] Passwords hashed with `password_hash()`
- [ ] User input validated on client and server
- [ ] Role checks on protected pages
- [ ] HTTPS used in production (not localhost)
- [ ] Session tokens used instead of localStorage (future)

---

*Last Updated: November 21, 2025*  
*Version: 1.0*  
*Maintainer: EduMind+ Development Team*

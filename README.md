# EduMind+ 🎓

> **A comprehensive, offline-first educational platform for students, teachers, and administrators**

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 🌟 Quick Start

```bash
# 1. Clone repository
git clone https://github.com/Fatmazha/student_space.git edumind
cd edumind

# 2. Copy to web server (XAMPP example)
cp -r . C:/xampp/htdocs/edumind/

# 3. Import database
# Open http://localhost/phpmyadmin
# Create database 'edumind'
# Import 'database.sql'

# 4. Access application
# Visit: http://localhost/edumind
```

**Default Credentials:**
- Admin: `admin`
- Teacher: `teacher_jane`
- Student: `alice`

---

## 📋 Features

### 🎓 Student Portal
- ✅ Interactive timed quizzes with instant feedback
- ✅ Progress tracking with visual charts
- ✅ Course browsing and enrollment
- ✅ Quiz issue reporting system
- ✅ Event calendar and registration

### 👨‍🏫 Teacher Workspace
- ✅ Intuitive quiz builder with drag-and-drop
- ✅ Course creation and management
- ✅ Student analytics dashboard
- ✅ Event scheduling (lectures, quizzes, webinars)
- ✅ Quiz reports management
- ✅ CSV data export

### 👨‍💼 Admin Console
- ✅ User management (students, teachers, admins)
- ✅ Role assignment and conversion
- ✅ Course approval workflow
- ✅ System-wide quiz reports dashboard
- ✅ Activity logs and monitoring
- ✅ Comprehensive reporting and export
- ✅ System settings configuration

---

## 🚀 Installation

### Prerequisites
- **XAMPP** 8.0+ (includes Apache, PHP, MySQL)
- **Git** (optional, for cloning)
- Modern web browser

### Step-by-Step Setup

#### 1. Install XAMPP
Download from [apachefriends.org](https://www.apachefriends.org/) and install to `C:\xampp`

#### 2. Get EduMind+
```bash
# Option A: Clone with Git
cd C:\xampp\htdocs
git clone https://github.com/Fatmazha/student_space.git edumind

# Option B: Download ZIP
# Extract to C:\xampp\htdocs\edumind
```

#### 3. Start Services
- Open XAMPP Control Panel
- Start **Apache**
- Start **MySQL**

#### 4. Create Database
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click "New" to create database
3. Name it `edumind`, select `utf8mb4_unicode_ci` collation
4. Click "Create"
5. Select `edumind` database
6. Click "Import" tab
7. Choose `database.sql` from project folder
8. Click "Go"

#### 5. Access Application
- **Welcome Page:** [http://localhost/edumind](http://localhost/edumind)
- **Student Portal:** [http://localhost/edumind/Views/front-office/](http://localhost/edumind/Views/front-office/)
- **Teacher Portal:** [http://localhost/edumind/Views/teacher-back-office/](http://localhost/edumind/Views/teacher-back-office/)
- **Admin Portal:** [http://localhost/edumind/Views/admin-back-office/](http://localhost/edumind/Views/admin-back-office/)

---

## 📁 Project Structure

```
edumind/
├── index.php                 # Entry point (redirects to welcome)
├── .htaccess                 # Apache configuration
├── database.sql              # MySQL schema with sample data
├── README.md                 # This file
├── DESCRIPTION.md            # Detailed feature descriptions
├── ROADMAP.md                # Future development plans
│
├── Controllers/              # Business logic
│   ├── config.php           # Database configuration
│   └── EventController.php  # Event handling
│
├── Models/                   # Data models
│   └── Event.php            # Event entity
│
├── Views/                    # User interfaces
│   ├── welcome.php          # Landing page
│   ├── front-office/        # Student portal (11 files)
│   ├── teacher-back-office/ # Teacher portal (12 files)
│   └── admin-back-office/   # Admin portal (11 files)
│
└── shared-assets/           # Shared resources
    ├── css/                 # Stylesheets
    ├── js/                  # JavaScript modules
    ├── img/                 # Images
    └── vendor/              # Third-party libraries
```

---

## 🔧 Configuration

### Database Settings
Edit `Controllers/config.php` (create from template if needed):

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'edumind');
define('DB_USER', 'root');
define('DB_PASS', '');
?>
```

### Apache Configuration
The `.htaccess` file handles:
- URL rewriting
- Security headers
- Directory protection
- GZIP compression

Ensure `mod_rewrite` is enabled in Apache.

### LocalStorage Mode
EduMind+ works without MySQL using browser localStorage:
- Perfect for offline demos
- Sample data preloaded
- No server setup required
- Data persists in browser

---

## 🎯 Usage Guide

### For Students
1. Register account → Login
2. Browse courses → Select quiz
3. Complete quiz within time limit
4. View score and feedback
5. Track progress on dashboard
6. Report quiz issues if found

### For Teachers
1. Login with teacher account
2. Create courses (needs admin approval)
3. Build quizzes with question editor
4. Monitor student progress
5. Schedule events
6. Review and resolve quiz reports
7. Export data to CSV

### For Administrators
1. Login with admin account
2. Manage users (add/edit/delete)
3. Approve teacher courses
4. Review system-wide quiz reports
5. Monitor activity logs
6. Configure system settings
7. Export compliance reports

---

## 🐛 Troubleshooting

### Buttons Not Working
**Issue:** Links lead to 404 errors

**Fix:**
```bash
# Enable Apache rewrite module
a2enmod rewrite

# Restart Apache
systemctl restart apache2

# Verify .htaccess exists
ls -la .htaccess
```

### Database Connection Failed
**Fix:**
1. Check MySQL is running
2. Verify credentials in `Controllers/config.php`
3. Ensure database exists: `SHOW DATABASES;`
4. Test connection: `mysql -u root -p edumind`

### Foreign Key Error on Import
**Fix:**
```sql
# Drop and recreate database
DROP DATABASE IF EXISTS edumind;
CREATE DATABASE edumind CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edumind;
SOURCE database.sql;
```

### Quiz Not Loading
**Fix:**
- Check browser console (F12) for errors
- Verify quiz exists in database
- Check JSON format of questions
- Clear localStorage and refresh

---

## 📚 Documentation

- **[DESCRIPTION.md](DESCRIPTION.md)** - Detailed feature descriptions
- **[ROADMAP.md](ROADMAP.md)** - Future development plans
- **[MVC_ARCHITECTURE.md](MVC_ARCHITECTURE.md)** - Technical architecture
- **[database.sql](database.sql)** - Database schema reference

---

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create feature branch: `git checkout -b feature/name`
3. Commit changes: `git commit -m 'Add feature'`
4. Push to branch: `git push origin feature/name`
5. Submit Pull Request

**Code Guidelines:**
- PHP: PSR-12 standard
- JavaScript: ES6+, 2-space indent
- HTML: Semantic HTML5
- CSS: BEM methodology

---

## 📄 License

MIT License - See [LICENSE](LICENSE) file

Copyright © 2025 EduMind+ / Weblynx Studio

---

## 💬 Support

- **Issues:** [GitHub Issues](https://github.com/Fatmazha/student_space/issues)
- **Email:** contact@weblinx.studio
- **Discussions:** [GitHub Discussions](https://github.com/Fatmazha/student_space/discussions)

---

## 🙏 Credits

Built with ❤️ by **Weblynx Studio**

**Powered by:**
- [Bootstrap 5](https://getbootstrap.com)
- [Chart.js](https://chartjs.org)
- [PHP](https://php.net)
- [MySQL](https://mysql.com)

---

**[⬆ Back to top](#edumind-)**

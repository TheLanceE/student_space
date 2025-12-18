<p align="center">
  <img src="logo.jpg" alt="EduMind+ Logo" width="180" style="border-radius: 20px;">
</p>

<h1 align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Inter&weight=700&size=32&pause=1000&color=4F46E5&center=true&vCenter=true&random=false&width=500&lines=EduMind%2B;Smart+Learning+Platform;Built+with+%E2%9D%A4%EF%B8%8F+by+TheLanceE" alt="Typing SVG" />
</h1>

<p align="center">
  <strong>A modern, lightweight PHP/MySQL learning management system for students, teachers, and administrators.</strong>
</p>

<p align="center">
  <a href="#features"><img src="https://img.shields.io/badge/Features-✨-4f46e5?style=for-the-badge" alt="Features"></a>
  <a href="#quick-start"><img src="https://img.shields.io/badge/Quick%20Start-🚀-10b981?style=for-the-badge" alt="Quick Start"></a>
  <a href="#tech-stack"><img src="https://img.shields.io/badge/Tech%20Stack-💻-3b82f6?style=for-the-badge" alt="Tech Stack"></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat-square&logo=chartdotjs&logoColor=white" alt="Chart.js">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

---

## ✨ Features

<table>
<tr>
<td width="50%">

### 🎓 For Students
- 📊 Personal dashboard with progress tracking
- 📝 Interactive quizzes with instant feedback
- 🏆 Gamification: earn points, badges & rewards
- 📅 Event calendar & course management
- 🤖 AI-powered learning insights
- 🌙 Dark/Light theme toggle

</td>
<td width="50%">

### 👨‍🏫 For Teachers
- 📋 Quiz builder with multiple question types
- 📈 Class performance analytics
- 📊 Detailed student reports
- 🎯 Challenge & reward system
- 📚 Course & project management
- 🔔 Event announcements

</td>
</tr>
<tr>
<td colspan="2">

### 🔐 For Administrators
- 👥 Complete user management (CRUD)
- 🔒 Role-based access control
- 📊 System-wide analytics dashboard
- ⚙️ Platform settings & configuration
- 📜 Activity logs & audit trails
- 🎨 Theme customization

</td>
</tr>
</table>

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 / MariaDB 10.4+
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Installation

```bash
# Clone the repository
git clone https://github.com/TheLanceE/student_space.git

# Navigate to project directory
cd student_space

# Import the database
mysql -u your_username -p your_database < database.sql

# Configure Google OAuth (optional)
cp Controllers/oauth_config.local.example.php Controllers/oauth_config.local.php
# Edit the file with your Google OAuth credentials

# Set your web root to the project folder
# Or configure virtual host to point to the project
```

### 🔑 Default Credentials

| Role | Username | Password |
|------|----------|----------|
| 👨‍🎓 Student | `superkid` | `password123` |
| 👨‍🏫 Teacher | `teacher_jane` | `password123` |
| 👨‍💼 Admin | `admin` | `password123` |

> ⚠️ **Security Note:** Change default passwords immediately in production!

---

## 💻 Tech Stack

<p align="center">
  <img src="https://skillicons.dev/icons?i=php,mysql,bootstrap,js,css,html,git" alt="Tech Stack Icons" />
</p>

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 7.4+ (MVC Architecture) |
| **Database** | MySQL 8.0 / MariaDB |
| **Frontend** | Bootstrap 5.3, Vanilla JS |
| **Charts** | Chart.js |
| **Authentication** | Session-based + Google OAuth 2.0 |
| **Styling** | Custom CSS with CSS Variables (Dark/Light themes) |

---

## 📁 Project Structure

```
📦 EduMind+
├── 📂 Controllers/      # Business logic & API handlers
├── 📂 Models/           # Database models & entities
├── 📂 Views/            # UI templates
│   ├── 📂 front-office/     # Student interface
│   ├── 📂 teacher-back-office/  # Teacher dashboard
│   ├── 📂 admin-back-office/    # Admin panel
│   └── 📂 partials/         # Reusable components
├── 📂 shared-assets/    # CSS, JS, images
│   ├── 📂 css/          # Global & component styles
│   ├── 📂 js/           # Client-side scripts
│   └── 📂 vendor/       # Third-party libraries
├── 📂 uploads/          # User uploads (avatars, etc.)
├── 📄 database.sql      # Database schema
├── 📄 index.php         # Landing page
└── 📄 .htaccess         # URL rewriting rules
```

---

## 🌟 Screenshots

<p align="center">
  <img src="shared-assets/img/dashboard-preview.jpg" alt="Student Dashboard" width="45%">
  <img src="shared-assets/img/teacher-workspace.jpg" alt="Teacher Workspace" width="45%">
</p>

---

## 🔧 Configuration

### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI:
   ```
   http://localhost/edumind/Controllers/google_oauth_callback.php
   ```
6. Copy credentials to `Controllers/oauth_config.local.php`

### Session Configuration

Sessions are managed centrally via `Controllers/SessionManager.php`. Customize timeout and security settings as needed.

---

## 🛡️ Security Features

- 🔐 **Password Hashing** - Bcrypt with secure salts
- 🛡️ **CSRF Protection** - Token-based form protection
- 🧹 **Input Sanitization** - All inputs validated & escaped
- 🔒 **Prepared Statements** - SQL injection prevention
- 🍪 **Secure Sessions** - HttpOnly, SameSite cookies
- 👁️ **Soft Deletes** - Data recovery capability

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) - Frontend framework
- [Chart.js](https://www.chartjs.org/) - Beautiful charts
- [Font Awesome](https://fontawesome.com/) - Icons
- [Google Fonts](https://fonts.google.com/) - Inter typeface

---

<p align="center">
  <strong>Made with ❤️ by <a href="https://github.com/TheLanceE">TheLanceE</a></strong>
</p>

<p align="center">
  <a href="https://github.com/TheLanceE/student_space/stargazers">
    <img src="https://img.shields.io/github/stars/TheLanceE/student_space?style=social" alt="Stars">
  </a>
  <a href="https://github.com/TheLanceE/student_space/network/members">
    <img src="https://img.shields.io/github/forks/TheLanceE/student_space?style=social" alt="Forks">
  </a>
</p>

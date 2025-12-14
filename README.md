# EduMind Quizzes

A comprehensive PHP/MySQL quiz management system with separate teacher and student interfaces, AI-powered quiz generation, and detailed attempt tracking.

---

## 📋 Table of Contents
- [Overview](#overview)
- [Features](#features)
- [System Architecture](#system-architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage Guide](#usage-guide)
- [Database Schema](#database-schema)
- [AI Integration](#ai-integration)
- [API Endpoints](#api-endpoints)
- [Troubleshooting](#troubleshooting)
- [Security Notes](#security-notes)

---

## 🎯 Overview

EduMind Quizzes is a full-featured educational quiz platform designed for teachers and students. Teachers can create, edit, and manage quizzes manually or generate them using AI. Students can browse available quizzes, take them with time limits, and review their performance with detailed feedback.

**Tech Stack:**
- Backend: PHP 7.4+, PDO (MySQL)
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Database: MySQL 5.7+
- AI: DeepSeek/Gemini API integration
- Server: Apache (XAMPP)

---

## ✨ Features

### For Teachers
- **Quiz Management**
  - Create quizzes with custom titles, categories, grade levels, descriptions
  - Add multiple-choice questions (A, B, C, D options)
  - Mark correct answers for each question
  - Set passing grades (percentage)
  - Configure optional time limits
  - Edit existing quizzes with full CRUD operations
  - Delete individual questions during editing
  - Preview quizzes before publishing
  - Publish/draft status control

- **AI-Powered Quiz Generation**
  - Generate quiz questions using AI (DeepSeek or Gemini)
  - Customize: subject, topic, difficulty level, number of questions
  - AI generates questions with 4 multiple-choice options
  - Review and edit AI-generated content before saving
  - Voice recognition support for hands-free quiz creation

- **Search & Filtering**
  - Search quizzes by title in teacher dashboard
  - Browse all quizzes in the system

### For Students
- **Quiz Taking**
  - Browse available active quizzes
  - View quiz details (category, grade level, passing grade, time limit)
  - Take quizzes with clear question presentation
  - Real-time timer (if time limit set)
  - Submit answers and get immediate scoring

- **Performance Tracking**
  - View score percentage after completion
  - See correct vs total questions answered
  - Pass/fail indication based on passing grade
  - Review quiz attempts with detailed feedback
  - See which answers were correct/incorrect
  - Track quiz history

### Common Features
- Session management (role-based access)
- Responsive design
- Real-time form validation
- AJAX-powered interactions
- Bootstrap 5 UI components

---

## 🏗️ System Architecture

### Project Structure
```
quiz/
├── index.php                    # Entry point, routes to controller
├── config.php                   # Database configuration
├── ai_config.php               # AI provider configuration
├── generate_quiz.php           # AI quiz generation endpoint
├── QuizGenerator.php           # AI generation logic (empty placeholder)
├── ai.js                       # Frontend AI integration (OpenAI client)
├── quiz-generator.js           # Quiz generation UI logic
├── README.md                   # This file
│
├── controller/
│   └── quizcontroller.php      # Main controller (all routes & business logic)
│
├── model/
│   ├── QuizModel.php           # Database operations
│   ├── Quiz.php                # Quiz entity class
│   ├── Question.php            # Question entity class
│   └── QuestionOption.php      # Option entity class
│
├── view/
│   ├── frontofficequizteacher.html  # Teacher quiz creation UI
│   ├── teacher_quizzes.php          # Teacher dashboard
│   ├── frontofficequizstudent.html  # Student quiz browser (deprecated)
│   ├── student_quizzes.php          # Student dashboard
│   ├── backofficequiz.html          # Back office UI
│   └── quiz-form.js                 # Form validation & dynamic question handling
│
└── db/
    └── quiz_schema.sql         # Database schema
```

### MVC Pattern
- **Model**: Data entities (Quiz, Question, QuestionOption) and database operations (QuizModel)
- **View**: HTML templates and JavaScript for UI
- **Controller**: QuizController handles all HTTP requests and coordinates model/view

---

## 🚀 Installation

### Prerequisites
- XAMPP (includes Apache, MySQL, PHP)
- Modern web browser (Chrome, Firefox, Edge)
- Git (optional, for cloning)

### Step-by-Step Setup

1. **Install XAMPP**
   - Download from [apachefriends.org](https://www.apachefriends.org/)
   - Install with default settings
   - Start Apache and MySQL services

2. **Clone or Copy Project**
   ```bash
   cd c:\xampp\htdocs
   git clone <your-repo-url> quizzes2/quizzes2/quizzes2/quiz
   # Or manually copy the quiz folder to this path
   ```

3. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database named `edumind`
   - Import schema: Import → Select `db/quiz_schema.sql` → Go

4. **Configure Database Connection**
   - Open `config.php`
   - Update database credentials if different:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";          // Your MySQL password
     $dbname = "edumind";
     $port = 3307;            // Change to 3306 if using default MySQL port
     ```

5. **Access Application**
   - Teacher Interface: `http://localhost/quizzes2/quizzes2/quiz/view/teacher_quizzes.php`
   - Student Interface: `http://localhost/quizzes2/quizzes2/quiz/view/student_quizzes.php`
   - Main Entry: `http://localhost/quizzes2/quizzes2/quiz/`

---

## ⚙️ Configuration

### Database Configuration (`config.php`)
The `config` class uses PDO for secure database connections with error handling:

```php
class config {  
    public static function getConnexion() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "edumind";
        $port = 3307;  // Adjust based on your MySQL port
        
        // Returns PDO instance with error mode and fetch mode configured
    }
}
```

**Important**: This file contains credentials. Keep it secure and never commit sensitive passwords to public repositories.

### Session Configuration
Sessions are initialized in `QuizController`:
- `user_id`: User identifier
- `role`: 'teacher' or 'student'
- `user_name`: Display name

**Default Test Session** (for development):
```php
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'teacher';
$_SESSION['user_name'] = 'John Smith';
```

---

## 📖 Usage Guide

### Teacher Workflow

#### Creating a Quiz Manually
1. Navigate to Teacher Dashboard
2. Click "Create New Quiz" tab
3. Fill in quiz details:
   - Quiz Title (minimum 3 characters)
   - Category (Math, Science, History, etc.)
   - Grade Level
   - Passing Grade (1-100%)
   - Description (optional)
   - Time Limit (optional, in minutes)
4. Add questions:
   - Click "Add Question"
   - Enter question text
   - Add 4 options (A, B, C, D)
   - Mark the correct answer
5. Validate form (real-time validation)
6. Save as Draft or Publish (Active)

#### Generating Quizzes with AI
1. Fill in basic quiz info (title, category, grade, passing grade)
2. Click "Generate Quiz with AI"
3. In modal dialog:
   - Enter subject (e.g., "Biology")
   - Enter topic (e.g., "Photosynthesis")
   - Select difficulty (easy/medium/hard)
   - Choose number of questions (1-20)
4. Click "Generate Questions"
5. Review AI-generated questions
6. Edit if needed or regenerate
7. Click "Use These Questions" to populate form
8. Save quiz

#### Managing Quizzes
- **My Quizzes Tab**: View all your created quizzes
- **Edit**: Modify quiz details, add/remove/edit questions
- **Preview**: See how the quiz appears to students
- **Delete**: Remove quiz (cascades to questions and options)
- **Search**: Filter quizzes by title

### Student Workflow

#### Taking a Quiz
1. Navigate to Student Dashboard
2. Browse available quizzes
3. Click "Take Quiz" on desired quiz
4. Read quiz information (time limit, passing grade)
5. Answer all questions (radio button selection)
6. Watch timer if time limit is set
7. Click "Submit Quiz"

#### Viewing Results
- Immediate score display (percentage)
- Correct answers count
- Pass/fail status
- Option to review answers

#### Reviewing Attempts
1. Click "Review Quiz" after completing
2. See all questions with:
   - Your selected answer (highlighted)
   - Correct answer (highlighted green)
   - Incorrect answers (highlighted red)
3. Learn from mistakes

---

## 🗄️ Database Schema

### Tables Overview

#### `quizzes`
Stores quiz metadata and configuration.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment primary key |
| title | VARCHAR(255) | Quiz title |
| category | VARCHAR(100) | Subject category |
| grade | VARCHAR(50) | Target grade level |
| passing_grade | DECIMAL(5,2) | Percentage required to pass |
| description | TEXT | Optional quiz description |
| status | ENUM | 'active', 'inactive', 'draft' |
| time_limit | INT | Time limit in minutes (nullable) |
| created_at | TIMESTAMP | Auto-generated creation time |
| updated_at | TIMESTAMP | Auto-updated modification time |

**Indexes**: `category`, `status`, `grade`

#### `questions`
Stores quiz questions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment primary key |
| quiz_id | INT (FK) | Reference to quizzes.id |
| question_text | TEXT | The question content |
| question_order | INT | Display order (0-based) |
| created_at | TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | Modification time |

**Foreign Keys**: `quiz_id` → `quizzes(id)` ON DELETE CASCADE  
**Indexes**: `quiz_id`, `question_order`  
**Unique Constraint**: `(quiz_id, question_order)`

#### `question_options`
Stores answer options for questions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment primary key |
| question_id | INT (FK) | Reference to questions.id |
| option_label | VARCHAR(10) | 'A', 'B', 'C', 'D' |
| option_text | TEXT | The option content |
| is_correct | BOOLEAN | True if this is the correct answer |
| created_at | TIMESTAMP | Creation time |

**Foreign Keys**: `question_id` → `questions(id)` ON DELETE CASCADE  
**Indexes**: `question_id`, `is_correct`  
**Unique Constraint**: `(question_id, option_label)`

#### `quiz_attempts`
Tracks student quiz attempts.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment primary key |
| quiz_id | INT (FK) | Reference to quizzes.id |
| user_id | INT | Reference to users (external table) |
| score | DECIMAL(5,2) | Percentage score |
| passed | BOOLEAN | True if score >= passing_grade |
| time_taken | INT | Time in seconds (nullable) |
| completed_at | TIMESTAMP | Completion time (nullable) |
| created_at | TIMESTAMP | Attempt start time |

**Foreign Keys**: `quiz_id` → `quizzes(id)` ON DELETE CASCADE  
**Indexes**: `(quiz_id, user_id)`, `completed_at`

#### `attempt_answers`
Stores individual answers for each attempt.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment primary key |
| attempt_id | INT (FK) | Reference to quiz_attempts.id |
| question_id | INT (FK) | Reference to questions.id |
| selected_option_id | INT (FK) | Reference to question_options.id |
| is_correct | BOOLEAN | True if answer is correct |
| answered_at | TIMESTAMP | Time answer was submitted |

**Foreign Keys**:
- `attempt_id` → `quiz_attempts(id)` ON DELETE CASCADE
- `question_id` → `questions(id)` ON DELETE CASCADE
- `selected_option_id` → `question_options(id)` ON DELETE CASCADE

**Indexes**: `(attempt_id, question_id)`

### Database Relationships
```
quizzes (1) ──→ (N) questions
questions (1) ──→ (N) question_options
quizzes (1) ──→ (N) quiz_attempts
quiz_attempts (1) ──→ (N) attempt_answers
questions (1) ──→ (N) attempt_answers
question_options (1) ──→ (N) attempt_answers
```

---

## 🤖 AI Integration

### Supported Providers
1. **DeepSeek** (default): Cost-effective, coding-optimized
2. **Gemini**: Google's AI model

### Configuration

#### Environment Variables (Recommended)
**Windows PowerShell:**
```powershell
# Set for current session
$env:AI_PROVIDER = 'deepseek'
$env:DEEPSEEK_API_KEY = 'your-api-key-here'

# Persist for user (permanent)
[Environment]::SetEnvironmentVariable('AI_PROVIDER', 'deepseek', 'User')
[Environment]::SetEnvironmentVariable('DEEPSEEK_API_KEY', 'your-api-key', 'User')
```

**Restart Apache/XAMPP** after setting environment variables.

#### File-Based Configuration (Alternative)
Create a key file in the quiz root directory:

**DeepSeek:**
```bash
echo your-deepseek-api-key > deepseek.key
```

**Gemini:**
```bash
echo your-gemini-api-key > gemini.key
```

Set provider:
```powershell
$env:AI_PROVIDER = 'gemini'
```

### How AI Generation Works

1. **User Input**: Subject, topic, difficulty, number of questions
2. **API Call**: `generate_quiz.php` sends request to AI provider
3. **AI Response**: Structured JSON with questions and options
4. **Processing**: Backend parses and formats response
5. **Frontend Display**: Questions populate the form
6. **Manual Review**: Teacher can edit before saving

### AI Configuration File (`ai_config.php`)

```php
// Determines provider: 'deepseek' or 'gemini'
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: 'deepseek');

// DeepSeek configuration
define('AI_BASE_URL', 'https://api.deepseek.com');

// Gemini configuration
define('AI_BASE_URL', 'https://generativelanguage.googleapis.com');
```

### API Endpoints

**DeepSeek**: `https://api.deepseek.com`  
**Gemini**: `https://generativelanguage.googleapis.com`

### Troubleshooting AI
- **"AI service not available"**: Check API key configuration
- **Empty response**: Verify API key is valid and has credits
- **Malformed JSON**: AI response format may have changed; check logs
- **Timeout**: AI provider may be slow; increase PHP timeout

---

## 🔌 API Endpoints

All endpoints are handled by `QuizController` via `index.php`.

### Teacher Endpoints

| Action | Method | Parameters | Description |
|--------|--------|------------|-------------|
| `create` | POST | Quiz data, questions, options | Create new quiz |
| `edit` | POST | quiz_id, updated data | Update existing quiz |
| `getQuizFormData` | GET | id | Get quiz data for editing |
| `getMyQuizzes` | GET | - | Get all quizzes by current teacher |
| `delete` | POST | quiz_id | Delete quiz and related data |
| `preview` | GET | id | Get quiz preview HTML |
| `generateQuestions` | POST | subject, topic, difficulty, num_questions | AI generate questions |
| `saveGeneratedQuiz` | POST | Quiz data with AI-generated questions | Save AI quiz |
| `aiStatus` | GET | - | Check AI configuration status |

### Student Endpoints

| Action | Method | Parameters | Description |
|--------|--------|------------|-------------|
| `getAllQuizzes` | GET | - | Browse all active quizzes |
| `getQuizForTaking` | GET | id | Get quiz for student (no answers) |
| `submitAttempt` | POST | quiz_id, answers | Submit quiz and get results |
| `reviewAttempt` | GET | id (attempt_id) | Review completed attempt |
| `getStats` | GET | - | Get student statistics |
| `getAttemptedQuizIds` | GET | - | Get list of attempted quiz IDs |

### Common Endpoints

| Action | Method | Parameters | Description |
|--------|--------|------------|-------------|
| `index` | GET | - | Default landing page |
| `take` | GET | id | Quiz taking interface |

### Example Usage

**Creating a Quiz (AJAX):**
```javascript
const formData = new FormData();
formData.append('action', 'create');
formData.append('title', 'Math Quiz');
formData.append('category', 'Mathematics');
formData.append('gradeLevel', 'Grade 10');
formData.append('passing_grade', 70);
formData.append('questions[0][text]', 'What is 2+2?');
formData.append('questions[0][options][0][text]', '3');
formData.append('questions[0][options][0][is_correct]', '0');
formData.append('questions[0][options][1][text]', '4');
formData.append('questions[0][options][1][is_correct]', '1');

fetch('../controller/quizcontroller.php', {
    method: 'POST',
    body: formData
});
```

**Submitting Quiz Attempt:**
```javascript
const formData = new FormData();
formData.append('action', 'submitAttempt');
formData.append('quiz_id', 123);
formData.append('question_45', 'option_178'); // question_id, option_id

fetch('../controller/quizcontroller.php', {
    method: 'POST',
    body: formData
});
```

---

## 🛠️ Troubleshooting

### Database Connection Issues

**Symptoms**: "Database Connection Failed" error

**Solutions**:
1. Verify MySQL is running in XAMPP Control Panel
2. Check database name exists: Open phpMyAdmin → Check for `edumind` database
3. Verify port in `config.php` matches MySQL port:
   - Default MySQL port: `3306`
   - Custom XAMPP port: often `3307`
4. Test connection:
   ```php
   // Add to config.php temporarily
   var_dump(self::$pdo);
   ```

### Options Not Saving

**Symptoms**: Questions save but options disappear

**Solutions**:
1. Verify `question_options` table exists:
   ```sql
   SHOW TABLES LIKE 'question_options';
   ```
2. Re-import schema if missing:
   ```sql
   DROP DATABASE IF EXISTS edumind;
   CREATE DATABASE edumind;
   USE edumind;
   SOURCE path/to/quiz_schema.sql;
   ```
3. Check foreign key constraints:
   ```sql
   SHOW CREATE TABLE question_options;
   ```

### Quiz Not Appearing

**Symptoms**: Created quiz doesn't show in list

**Solutions**:
1. Check quiz status: Only 'active' quizzes show to students
2. Verify AJAX response:
   - Open browser DevTools → Network tab
   - Check response from `getMyQuizzes` or `getAllQuizzes`
3. Check session:
   ```php
   // Add to controller temporarily
   error_log(print_r($_SESSION, true));
   ```

### AI Generation Fails

**Symptoms**: "AI service not available" or no questions generated

**Solutions**:
1. Check AI provider configuration:
   ```php
   // Check ai_config.php
   echo AI_PROVIDER;
   echo AI_API_KEY;
   ```
2. Verify API key is valid
3. Check API key has credits/quota
4. Test API directly with cURL:
   ```bash
   curl -X POST https://api.deepseek.com/v1/chat/completions \
     -H "Authorization: Bearer YOUR_KEY" \
     -H "Content-Type: application/json" \
     -d '{"model":"deepseek-chat","messages":[{"role":"user","content":"test"}]}'
   ```

### Form Validation Not Working

**Symptoms**: Can submit empty forms

**Solutions**:
1. Check JavaScript errors in browser console
2. Verify `quiz-form.js` is loaded:
   ```html
   <script src="quiz-form.js"></script>
   ```
3. Check element IDs match:
   - `quizTitle` → `quizTitle_error`
   - `quizCategory` → `quizCategory_error`

### Port Conflicts

**Symptoms**: Apache won't start

**Solutions**:
1. Check if port 80 is in use:
   ```powershell
   netstat -ano | findstr :80
   ```
2. Change Apache port:
   - Edit `xampp/apache/conf/httpd.conf`
   - Change `Listen 80` to `Listen 8080`
   - Access via `http://localhost:8080/`

---

## 🔒 Security Notes

### Current Security Model
This application is designed for **local development and testing**. It includes basic security measures but should not be deployed to production without additional hardening.

### Implemented Security Features
- **PDO Prepared Statements**: Protects against SQL injection
- **Session Management**: Basic role-based access control
- **Input Validation**: Client-side and server-side validation
- **Error Handling**: Graceful error messages (no sensitive data exposure)
- **Foreign Key Constraints**: Data integrity at database level

### Security Recommendations for Production

1. **Authentication System**
   - Replace hardcoded session values with real login system
   - Implement password hashing (bcrypt/argon2)
   - Add user registration and email verification

2. **Authorization**
   - Verify user owns quiz before edit/delete operations
   - Add teacher verification for quiz creation
   - Implement row-level security

3. **Configuration Security**
   - Move `config.php` outside web root
   - Use environment variables for credentials
   - Add `.gitignore` for sensitive files
   - Use separate config for dev/staging/production

4. **Input Sanitization**
   - Add server-side validation for all inputs
   - Sanitize HTML output (prevent XSS)
   - Validate file uploads (if added)

5. **HTTPS**
   - Use SSL/TLS in production
   - Enable HSTS headers
   - Secure cookies (HttpOnly, Secure flags)

6. **Rate Limiting**
   - Limit AI generation requests per user
   - Prevent quiz spam
   - Add CAPTCHA for public forms

7. **API Security**
   - Store API keys in secure vault
   - Rotate API keys regularly
   - Monitor API usage and costs

8. **Database Security**
   - Create dedicated MySQL user (not root)
   - Grant minimal required privileges
   - Regular backups
   - Enable audit logging

9. **Session Security**
   ```php
   session_start([
       'cookie_lifetime' => 0,
       'cookie_httponly' => true,
       'cookie_secure' => true,
       'cookie_samesite' => 'Strict'
   ]);
   ```

10. **Additional Headers**
    ```php
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Content-Security-Policy: default-src 'self'");
    ```

### Known Limitations
- Default session credentials (user_id=1, role='teacher')
- No password protection
- No user management system
- Config file contains hardcoded credentials
- No CSRF protection
- No rate limiting
- Teacher_id column handling is inconsistent

---

## 📝 Additional Notes

### Form Validation Rules
- **Quiz Title**: Minimum 3 characters
- **Category**: Required selection
- **Grade Level**: Required selection
- **Passing Grade**: 1-100 integer
- **Time Limit**: Optional, positive integer (minutes)
- **Questions**: At least 1 question required
- **Options**: Exactly 4 options per question
- **Correct Answer**: Exactly 1 correct option per question

### Supported Categories
- Mathematics
- Science
- History
- Geography
- English
- Computer Science
- Arts
- Physical Education
- (Extensible via form)

### Grade Levels
- Grade 1 - Grade 12
- University
- Adult Education
- (Extensible via form)

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Future Enhancements
- Multi-language support
- Question types (true/false, multiple select, fill-in-blank)
- Media support (images, videos in questions)
- Quiz analytics dashboard
- Export/import quizzes (JSON, XML)
- Quiz templates library
- Collaborative quiz creation
- Mobile app integration
- Gamification (badges, leaderboards)

---

## 📞 Support

For issues, questions, or contributions:
1. Check this documentation first
2. Review error logs: `xampp/apache/logs/error.log`
3. Enable PHP error display for debugging:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
4. Check browser console for JavaScript errors

---

## 📄 License

This project is for educational purposes. Adjust licensing as needed for your use case.

---

**Last Updated**: December 2025  
**Version**: 2.0  
**Platform**: XAMPP/Apache/PHP/MySQL

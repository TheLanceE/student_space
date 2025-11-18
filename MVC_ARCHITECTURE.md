# EduMind+ Platform - MVC Architecture

## Project Overview
EduMind+ is a comprehensive educational platform with three portals (Student, Teacher, Admin) built with PHP and localStorage for offline-first functionality.

## MVC Structure

### Models (`/Models/`)
- **Event.php**: Event entity with CRUD operations for MySQL database
  - `create($pdo)`: Insert new event
  - `getAll($pdo)`: Retrieve all events
  - `getByTeacher($pdo, $teacherID)`: Get events by teacher
  - `delete($pdo, $id)`: Remove event

### Controllers (`/Controllers/`)
- **EventController.php**: Handles event business logic
  - `create()`: Process event creation from POST
  - `delete()`: Remove event by ID
  - `getAll()`: Fetch all events
  - `getByTeacher($teacherID)`: Fetch teacher-specific events
- **config.php**: Database configuration with fallback for offline mode
  - Connects to MySQL (`localhost/edumind`)
  - Sets `DB_AVAILABLE` constant (true/false)
  - Falls back to localStorage when DB unavailable

### Views
- **front-office/**: Student portal views (dashboard, courses, quiz, profile)
- **teacher-back-office/**: Teacher portal views (dashboard, courses, students, quiz-builder, **events**, reports)
- **admin-back-office/**: Admin portal views (dashboard, users, courses, **events**, logs, reports, settings)

## New Features

### 1. Teacher Self-Registration
- **File**: `teacher-back-office/register.php`
- **Fields**: Login ID, Full Name, Email, Mobile, Address, Subject Specialty, National ID
- **Auth**: `teacher-back-office/assets/js/auth-teacher.js` → `TAuth.register(formData)`
- Teachers can create accounts without admin intervention

### 2. Events Module
- **Teachers**: Can create and delete their own events
  - Page: `teacher-back-office/events.php`
  - Handler: `handleEvents()` in `pages.js`
- **Admins**: Can only view and delete events (no creation)
  - Page: `admin-back-office/events.php`
  - Handler: `handleEvents()` in admin `pages.js`
- **Storage**: localStorage table `events` with fallback to MySQL
- **Fields**: Title, Date, Start/End Time, Course, Type (Lecture/Quiz/Webinar), Location (lecture only), Max Participants, Description

### 3. Quiz CRUD (Teachers)
- **Create**: Via `quiz-builder.php` with dynamic question blocks
- **Delete**: Through `courses.php` course cards
- **Storage**: `shared-assets/js/database.js` quizzes table
- Verified via `teacher-back-office/assets/js/data-teacher.js` methods

### 4. Logout Redirect
- All logout functions now redirect to `/index.php` (root landing page)
- Files updated:
  - `front-office/assets/js/auth.js`
  - `teacher-back-office/assets/js/auth-teacher.js`
  - `admin-back-office/assets/js/auth-admin.js`

### 5. Offline Images
- Downloaded images to `shared-assets/img/`:
  - `dashboard-preview.jpg`, `student-portal.jpg`, `teacher-workspace.jpg`
  - `admin-console.jpg`, `stem-showcase.jpg`, `react-icon.svg`
- Updated `index.php` to reference local paths
- Ensures full offline functionality

## Database Schema

### LocalStorage Tables
```javascript
{
  admins: [{ id, username, name, createdAt, lastLoginAt }],
  students: [{ id, username, fullName, email, mobile, address, gradeLevel, createdAt, lastLoginAt }],
  teachers: [{ id, username, fullName, email, mobile, address, specialty, nationalId, createdAt, lastLoginAt }],
  courses: [{ id, title, description, teacherId, status, createdAt }],
  quizzes: [{ id, courseId, title, durationSec, questions, createdBy }],
  scores: [{ id, userId, username, courseId, quizId, score, total, durationSec, attempt, timestamp, type }],
  events: [{ id, title, date, startTime, endTime, course, type, location, maxParticipants, description, teacherId, createdAt }],
  recommendations: [],
  logs: [{ id, level, message, ts }]
}
```

### MySQL Schema (Optional)
Create database `edumind` and table `events`:
```sql
CREATE DATABASE edumind;
USE edumind;
CREATE TABLE events (
  eventID INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  startTime TIME NOT NULL,
  endTime TIME NOT NULL,
  maxParticipants INT DEFAULT 30,
  nbrParticipants INT DEFAULT 0,
  course VARCHAR(255),
  type ENUM('Lecture', 'Quiz', 'Webinar', 'Other') DEFAULT 'Other',
  location VARCHAR(255),
  description TEXT,
  teacherID INT NOT NULL,
  INDEX(teacherID),
  INDEX(date)
);
```

## Deployment

### XAMPP Setup
1. Copy project to `C:\xampp\htdocs\edumind`
2. Start Apache from XAMPP control panel
3. Visit `http://localhost/edumind/index.php`
4. (Optional) Create MySQL database for Events persistence

### Testing Accounts
- **Student**: `alice` / `bob`
- **Teacher**: `teacher_jane` / `teacher_lee`
- **Admin**: `admin`

## Architecture Benefits
- **Separation of Concerns**: Models handle data, Controllers process logic, Views render UI
- **Offline-First**: LocalStorage ensures functionality without database
- **Gradual Enhancement**: MySQL integration optional (falls back gracefully)
- **Modular**: Each portal isolated with shared assets (`database.js`, `global.css`)

## Next Steps
1. Test Events creation in both teacher and admin portals
2. Verify quiz builder functionality
3. Consider merging `feature/users-module` into `main`
4. Add server-side PHP validation for forms
5. Implement user authentication sessions (PHP $_SESSION)

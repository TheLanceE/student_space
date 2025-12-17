# EduMind+ Student Project Management System

A comprehensive web-based project and task management system designed for educational environments, featuring role-based access control, real-time collaboration, and AI-powered voice input capabilities.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [User Roles](#user-roles)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [API Documentation](#api-documentation)
- [Advanced Features](#advanced-features)
- [File Structure](#file-structure)
- [Technologies Used](#technologies-used)
- [Contributing](#contributing)

## 🎯 Overview

EduMind+ is a full-stack PHP application built for educational institutions to manage student projects and tasks. The system provides separate interfaces for students, teachers, and administrators, each with role-specific functionality. It features project tracking, task management, progress monitoring, and teacher feedback through an emoji reaction system.

## ✨ Features

### Core Functionality

#### Project Management
- **Create & Edit Projects**: Students can create projects with detailed descriptions, due dates, and expected task counts
- **Project Status Tracking**: Four status levels - Not Started, In Progress, On Hold, Completed
- **Task Count Validation**: Projects enforce expected task count limits to maintain project scope
- **Progress Monitoring**: Visual indicators showing completed tasks vs. expected tasks
- **Due Date Management**: Calendar-based due date selection with validation (must be today or later)
- **Project Descriptions**: Rich text descriptions with voice input support

#### Task Management
- **Hierarchical Task Structure**: Tasks belong to projects and inherit project context
- **Task Status Management**: Four-state workflow (Not Started, In Progress, Completed, On Hold)
- **File Attachments**: Support for multiple file types (images, PDFs, Office documents, ZIP files)
- **Task Descriptions**: Detailed descriptions with AI voice input
- **Due Date Tracking**: Individual task due dates with visual calendar indicators
- **Validation**: Task names must contain at least 4 letters; descriptions require 10+ characters

#### Role-Based Access Control

##### Student View (`projects_student.php`)
- Create, view, edit, and delete own projects
- Create and manage tasks within projects
- View task progress and project completion status
- No access to other students' projects
- Session automatically initialized with `stu_debug` user

##### Teacher View (`projects_teacher.php`)
- View all projects across all students
- Access all tasks for any project
- React to student projects with emoji feedback (❤️, 😍, 🤩, 👏, 🔥)
- Search and filter projects by name or status
- Cannot create projects (read/react only)
- Session forced to `teach_debug` with role='teacher'

##### Admin View (`projects_admin.php`)
- Complete oversight of all projects and tasks
- View-only access (no creation or editing)
- Access to all student work for monitoring
- Cannot react to projects
- Session forced to `admin_debug` with role='admin'

### Advanced Features

#### 🎤 AI Voice Input (Speech-to-Text)
- **Technology**: Web Speech API (webkitSpeechRecognition)
- **Supported Fields**: Project descriptions and task descriptions
- **Functionality**: 
  - Click microphone button to start listening
  - Real-time speech recognition with visual feedback
  - Automatic text appending to existing content
  - Error handling with user-friendly messages
  - Browser compatibility check (Chrome/Edge)
- **User Experience**:
  - Button changes to red ("Listening...") during recording
  - Success message on completion
  - Error messages for failed recognition
  - Disabled state for unsupported browsers

#### 📊 Teacher Reaction System
- **Purpose**: Teachers provide quick feedback on student projects
- **Reaction Types**: ❤️ (Heart), 😍 (Heart Eyes), 🤩 (Star Struck), 👏 (Clapping), 🔥 (Fire)
- **Features**:
  - One reaction per teacher per project
  - Update existing reactions
  - Remove reactions by selecting same emoji
  - Display latest reaction with teacher ID in Project Info modal
  - Restricted to teacher role only (enforced server-side)
- **Database**: Reactions stored in separate `reactions` table with unique constraint on (projectId, userId)

#### 🔒 Session Management
- Automatic session initialization per page type
- Role enforcement at page level
- User ID consistency across operations
- Flash message system for user feedback

#### ✅ Validation System
- **Project Names**: Must contain at least 4 letters (Unicode-aware)
- **Task Names**: Must contain at least 4 letters
- **Descriptions**: Minimum 10 characters, allows letters, numbers, spaces, and punctuation (`,.'!?-:;()`)
- **Due Dates**: Must be today or later
- **Expected Task Count**: Must be at least 1
- **File Uploads**: 
  - Maximum 10MB size
  - Allowed types: images, PDF, Office documents (.doc, .docx, .xls, .xlsx, .ppt, .pptx), text, ZIP
  - Server-side validation with proper error handling

## 🏗️ System Architecture

### MVC Pattern Implementation

```
├── Controllers/           # Business logic and data operations
│   ├── ProjectController.php   # Project CRUD, reactions, list filtering
│   └── TaskController.php      # Task CRUD, file uploads, status management
├── Views/                 # Presentation layer
│   ├── FrontOffice/      # Student interfaces
│   └── BackOffice/       # Teacher/Admin interfaces
├── assets/               # Static resources
│   ├── css/             # Stylesheets
│   ├── js/              # Client-side validation and interactions
│   └── vendor/          # Bootstrap CSS/JS
├── db/                   # Database scripts
│   └── migrations/      # Schema updates
└── config.php           # Database configuration
```

### Request Flow

1. **User Request** → View (PHP)
2. **View** → Controller method call
3. **Controller** → Validates input → Database query (PDO)
4. **Database** → Returns data
5. **Controller** → Processes data → Returns to View
6. **View** → Renders HTML with data

## 💾 Database Schema

### `projects` Table
```sql
- id (VARCHAR(64), PRIMARY KEY)          # Format: proj_[16-char-hex]
- projectName (VARCHAR(255))             # Project title
- description (TEXT)                     # Detailed description
- createdBy (VARCHAR(64))                # Student user ID (FK to users.id)
- assignedTo (VARCHAR(64))               # Optional co-owner
- status (VARCHAR(50))                   # not_started|in_progress|completed|on_hold
- dueDate (DATE)                         # Project deadline
- expectedTaskCount (INT)                # Number of tasks student plans to create
- completedTasks (INT, DEFAULT 0)        # Calculated field for progress
- createdAt (TIMESTAMP)                  # Creation timestamp
- updatedAt (TIMESTAMP)                  # Last modification timestamp
- Collation: utf8mb4_unicode_ci          # Unicode support
- INDEX on createdBy for filtering
```

### `tasks` Table
```sql
- id (VARCHAR(64), PRIMARY KEY)          # Format: task_[16-char-hex]
- projectId (VARCHAR(64), FK)            # Parent project
- taskName (VARCHAR(255))                # Task title
- description (TEXT)                     # Detailed description
- status (VARCHAR(50))                   # not_started|in_progress|completed|on_hold
- dueDate (DATE)                         # Task deadline
- attachmentPath (VARCHAR(500))          # File path for uploads
- createdAt (TIMESTAMP)                  # Creation timestamp
- updatedAt (TIMESTAMP)                  # Last modification timestamp
- Collation: utf8mb4_unicode_ci
- INDEX on projectId for task listing
- FOREIGN KEY projectId REFERENCES projects(id) ON DELETE CASCADE
```

### `reactions` Table
```sql
- id (VARCHAR(64), PRIMARY KEY)          # Format: reaction_[16-char-hex]
- projectId (VARCHAR(64), FK)            # Project being reacted to
- userId (VARCHAR(64), FK)               # Teacher who reacted
- type (VARCHAR(32))                     # Emoji reaction type
- createdAt (TIMESTAMP)                  # When reaction was created
- updatedAt (TIMESTAMP)                  # When reaction was updated
- Collation: utf8mb4_unicode_ci
- UNIQUE KEY (projectId, userId)         # One reaction per teacher per project
- FOREIGN KEY projectId REFERENCES projects(id) ON DELETE CASCADE
- FOREIGN KEY userId REFERENCES users(id) ON DELETE CASCADE
```

### `users` Table (assumed structure)
```sql
- id (VARCHAR(64), PRIMARY KEY)          # User identifier
- username (VARCHAR(100))                # Display name
- role (VARCHAR(32))                     # student|teacher|admin
- Collation: utf8mb4_unicode_ci
```

## 👥 User Roles

### Student Role
- **Access**: Personal projects and tasks only
- **Permissions**: Full CRUD on own content
- **Restrictions**: Cannot see other students' work
- **Default User**: `stu_debug`
- **Use Cases**:
  - Create project proposals
  - Break down projects into tasks
  - Track personal progress
  - Upload supporting files

### Teacher Role
- **Access**: All projects across all students
- **Permissions**: Read-only + React capability
- **Features**:
  - View all student submissions
  - Provide emoji feedback
  - Search/filter projects
  - Monitor class progress
- **Default User**: `teach_debug`
- **Restrictions**: Cannot modify student work

### Admin Role
- **Access**: Complete system overview
- **Permissions**: Read-only across all entities
- **Purpose**: System monitoring and oversight
- **Default User**: `admin_debug`
- **Restrictions**: No creation, editing, or reactions

## 🚀 Installation

### Prerequisites
- PHP 7.4+ or 8.x
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- XAMPP (recommended for Windows)

### Step-by-Step Setup

1. **Clone Repository**
```bash
git clone https://github.com/TheLanceE/student_space.git
cd student_space
git checkout projects
```

2. **Database Setup**
```bash
# Create database
mysql -u root -p
CREATE DATABASE edumind CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edumind;

# Import schema
SOURCE db/edumind_schema.sql;

# Run migrations
SOURCE db/migrations/20251127_add_project_members_and_assignedTo.sql;
SOURCE db/migrations/20251216_add_reactions_table.sql;
```

3. **Configure Database Connection**
```php
// Edit config.php
class config {
    private static $host = 'localhost';
    private static $dbname = 'edumind';
    private static $username = 'root';
    private static $password = '';
}
```

4. **Set Permissions**
```bash
# Create uploads directory
mkdir -p uploads/task_attachments
chmod 755 uploads
chmod 755 uploads/task_attachments
```

5. **Web Server Configuration**
- Place project in web root (`htdocs` for XAMPP)
- Ensure `mod_rewrite` is enabled
- Set DocumentRoot to project directory

6. **Access Application**
- Students: `http://localhost/space/space/Views/FrontOffice/projects_student.php`
- Teachers: `http://localhost/space/space/Views/BackOffice/projects_teacher.php`
- Admins: `http://localhost/space/space/Views/BackOffice/projects_admin.php`

## 📖 Usage Guide

### Creating a Project (Student)

1. Navigate to Student Projects page
2. Click "New Project" button
3. Fill in form:
   - **Project Name**: Descriptive title (min 4 letters)
   - **Description**: Use keyboard or 🎤 voice button (min 10 chars)
   - **Expected Tasks**: Number of tasks you plan to create
   - **Due Date**: Select from calendar (today or later)
   - **Status**: Select current status
4. Click "Create Project"
5. Success message confirms creation

### Creating Tasks

1. From project card, click "📋 Tasks"
2. Click "New Task" button
3. Fill in form:
   - **Task Name**: Specific task title (min 4 letters)
   - **Project**: Pre-selected from context
   - **Description**: Use keyboard or 🎤 voice (min 10 chars)
   - **Status**: Current task state
   - **Due Date**: Task deadline
   - **Attachment**: Optional file upload (max 10MB)
4. Click "Create"
5. Task appears in filtered list

### Teacher Reactions

1. Navigate to Teacher Projects page
2. Hover over project card
3. Click "React" dropdown
4. Select emoji (❤️, 😍, 🤩, 👏, 🔥)
5. Reaction saved instantly
6. View reaction in Project Info modal
7. Change reaction by selecting different emoji
8. Remove by clicking same emoji again

### Voice Input Usage

1. Click microphone 🎤 button next to description field
2. Button turns red with "🎤 Listening..." status
3. Speak clearly into microphone
4. System automatically transcribes speech
5. Text appends to existing description
6. Success message: "✓ Voice input captured"
7. Edit text as needed before saving

## 🔧 API Documentation

### ProjectController Methods

#### `listProjects(?string $projectId = null): array`
- **Purpose**: Fetch projects with role-based filtering
- **Logic**:
  - Teachers/Admins: Returns all projects
  - Students: Returns only owned/assigned projects
- **Returns**: Array of project records with latest reaction data
- **Joins**: LEFT JOIN reactions + users for teacher reaction display

#### `showProject(string $id): array`
- **Purpose**: Retrieve single project with detailed information
- **Returns**: 
  - `project`: Project record
  - `latestReaction`: Most recent reaction emoji
  - `latestReactionBy`: Teacher user ID who reacted
  - `myReaction`: Current user's reaction (if teacher)
- **Filters**: Only includes reactions from users with role='teacher'

#### `addProject(array $data): string`
- **Validation**:
  - Project name contains ≥4 letters
  - Expected task count ≥1
  - Due date is today or later
- **Returns**: New project ID (proj_[hex])
- **Side Effects**: Sets session flash message

#### `updateReaction(string $projectId, string $reaction): string`
- **Purpose**: Create, update, or remove teacher reaction
- **Logic**:
  - Checks for existing reaction
  - If exists + same: DELETE (remove)
  - If exists + different: UPDATE
  - If not exists: INSERT
- **Returns**: 'created' | 'updated' | 'removed'
- **Authorization**: Requires teacher role (enforced by `requireTeacher()`)

#### `handleReaction(): void`
- **Purpose**: POST endpoint for AJAX reaction submissions
- **Error Handling**: try/catch prevents HTTP 500 errors
- **Flash Messages**:
  - "Reaction saved" (created)
  - "Reaction updated" (changed)
  - "Reaction removed" (cleared)

### TaskController Methods

#### `listTasks(?string $projectId = null): array`
- **Purpose**: Fetch tasks with project context
- **Filtering**:
  - If `$projectId` provided: Returns tasks for that project only
  - If null: Returns all accessible tasks
- **Role Logic**:
  - Teachers/Admins: All tasks
  - Students: Tasks from owned/assigned projects only
- **Returns**: Array with task + project name

#### `addTask(array $data): string`
- **Validation**:
  - Task name contains ≥4 letters
  - Description ≥10 characters
  - Due date is today or later
  - Project has room for more tasks (respects expectedTaskCount)
- **File Upload**: Calls `handleFileUpload()` for attachments
- **Returns**: New task ID (task_[hex])

#### `handleFileUpload(string $taskId): ?string`
- **Purpose**: Process and validate file uploads
- **Validation**:
  - Max size: 10MB
  - Allowed extensions: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip
  - MIME type verification
- **Storage**: `uploads/task_attachments/`
- **Naming**: `{taskId}_{timestamp}.{ext}`
- **Returns**: Relative path or null

#### `deleteExistingTask(string $id): void`
- **Purpose**: Remove task and associated file
- **Side Effects**: 
  - Deletes file from filesystem if exists
  - Cascades to related records via FK
- **Flash Message**: "Task deleted"

## 🎨 Frontend Architecture

### JavaScript Validators

#### `projectNameValidator.js`
- Real-time validation as user types
- Checks for minimum 4 letters (Unicode-aware)
- Visual feedback with red border and error message
- Form submission prevention if invalid

#### `descriptionValidator.js`
- Validates project and task descriptions
- Minimum 10 characters
- Allows: letters, numbers, spaces, punctuation (`,.'!?-:;()`)
- Real-time feedback on input event
- Form submission gate

#### `dueDateValidator.js`
- Ensures due dates are not in the past
- Sets `min` attribute to today's date
- Client-side validation before submission

### Voice Recognition Implementation

```javascript
// Web Speech API setup
const recognition = new webkitSpeechRecognition();
recognition.continuous = false;           // Single phrase mode
recognition.interimResults = false;       // Wait for complete result
recognition.lang = 'en-US';              // English language

// Event handlers
recognition.onresult = (event) => {
    const transcript = event.results[0][0].transcript;
    textarea.value += ' ' + transcript;   // Append to existing text
};

recognition.onerror = (event) => {
    // Display user-friendly error message
    console.error('Speech error:', event.error);
};
```

### Bootstrap Integration
- **Framework**: Bootstrap 5.x
- **Icons**: Bootstrap Icons (bi-*)
- **Components Used**:
  - Cards for project display
  - Modals for project details and forms
  - Badges for status indicators
  - Buttons with proper sizing and styling
  - Form controls with validation states
  - Alerts for flash messages

## 📁 File Structure

```
space/
├── assets/
│   ├── css/
│   │   └── debug.css                    # Custom styles for debug pages
│   ├── js/
│   │   ├── projectNameValidator.js      # Project name validation
│   │   ├── dueDateValidator.js          # Due date validation
│   │   └── descriptionValidator.js      # Description validation
│   └── vendor/
│       ├── bootstrap.min.css            # Bootstrap 5 CSS
│       └── bootstrap.bundle.min.js      # Bootstrap 5 JS + Popper
├── Controllers/
│   ├── ProjectController.php            # Project CRUD + Reactions
│   └── TaskController.php               # Task CRUD + File uploads
├── Views/
│   ├── FrontOffice/                     # Student interfaces
│   │   ├── projects_student.php         # Main project list
│   │   ├── addProject.php               # Create project form
│   │   ├── updateProject.php            # Edit project form
│   │   ├── deleteProject.php            # Delete confirmation
│   │   ├── showProject.php              # Project details
│   │   ├── taskList.php                 # Task list (filtered)
│   │   ├── addTask.php                  # Create task form
│   │   ├── updateTask.php               # Edit task form
│   │   ├── deleteTask.php               # Delete task handler
│   │   └── showTask.php                 # Task details
│   └── BackOffice/                      # Teacher/Admin interfaces
│       ├── projects_teacher.php         # Teacher project list + React
│       ├── projects_admin.php           # Admin overview
│       ├── taskList.php                 # Teacher task list
│       ├── addTask.php                  # Teacher create task
│       └── showTask.php                 # Teacher task view
├── db/
│   ├── edumind_schema.sql               # Main database schema
│   ├── README_DB.md                     # Database documentation
│   └── migrations/
│       ├── 20251127_add_project_members_and_assignedTo.sql
│       └── 20251216_add_reactions_table.sql
├── scripts/
│   ├── test_db_connect.php              # Database connection test
│   ├── test_add_task.php                # Task creation test
│   └── push_to_github.ps1               # Git automation script
├── uploads/
│   └── task_attachments/                # File upload storage
├── config.php                           # Database configuration
├── index.php                            # Application entry point
└── README.md                            # This file
```

## 💻 Technologies Used

### Backend
- **PHP** 7.4+ / 8.x
  - PDO for database abstraction
  - Session management
  - File upload handling
  - Exception handling
- **MySQL** 5.7+ / MariaDB 10.3+
  - InnoDB engine
  - Foreign key constraints
  - UTF-8 (utf8mb4) character set
  - Unicode collation (utf8mb4_unicode_ci)

### Frontend
- **HTML5**
  - Semantic markup
  - Form validation attributes
  - Accessibility features
- **CSS3**
  - Bootstrap 5 framework
  - Custom styles for debug pages
  - Responsive design
- **JavaScript (ES6+)**
  - Web Speech API
  - DOM manipulation
  - Event handling
  - Form validation
  - AJAX interactions

### Libraries & Frameworks
- **Bootstrap 5**: UI components and responsive grid
- **Bootstrap Icons**: Icon font
- **PDO**: Database access layer
- **Web Speech API**: Voice recognition

### Development Tools
- **Git**: Version control
- **GitHub**: Repository hosting
- **XAMPP**: Local development environment
- **VS Code**: Code editor
- **phpMyAdmin**: Database management

## 🔐 Security Considerations

### Input Validation
- Server-side validation for all user inputs
- Prepared statements for SQL queries (prevents SQL injection)
- File upload validation (type, size, extension)
- HTML escaping for output (`htmlspecialchars()`)

### Session Security
- Role enforcement at controller level
- User ID validation
- Session regeneration on role changes
- Flash message clearing after display

### File Upload Security
- Whitelist of allowed file extensions
- MIME type verification
- File size limits (10MB)
- Stored outside web-accessible directory
- Unique filenames to prevent overwrites

### Access Control
- Role-based filtering in database queries
- Authorization checks before data operations
- User ownership validation
- Teacher-only reaction enforcement

## 🐛 Known Issues & Limitations

1. **Browser Compatibility**: Voice input only works in Chrome and Edge (Web Speech API limitation)
2. **Debug Users**: System uses hardcoded debug users; production should implement proper authentication
3. **File Storage**: Uploaded files stored in local filesystem; consider cloud storage for scalability
4. **Real-time Updates**: No WebSocket/polling for live collaboration; requires manual refresh
5. **Localization**: Currently English-only; no i18n support

## 🚦 Testing

### Manual Testing Checklist

#### Student Workflow
- [ ] Create project with valid data
- [ ] Create project with invalid data (validate errors)
- [ ] Edit existing project
- [ ] Delete project (confirm cascade to tasks)
- [ ] Create task with file attachment
- [ ] Update task status
- [ ] Delete task
- [ ] Voice input in description fields
- [ ] Task count enforcement (reach expected limit)

#### Teacher Workflow
- [ ] View all student projects
- [ ] Search/filter projects
- [ ] React to project
- [ ] Update existing reaction
- [ ] Remove reaction
- [ ] View project details with reaction history
- [ ] Verify cannot modify student work

#### Database Testing
```sql
-- Test reaction uniqueness
INSERT INTO reactions (id, projectId, userId, type) VALUES ('r1', 'proj_123', 'teach_debug', '❤️');
INSERT INTO reactions (id, projectId, userId, type) VALUES ('r2', 'proj_123', 'teach_debug', '😍'); -- Should fail (UNIQUE constraint)

-- Test cascade delete
DELETE FROM projects WHERE id = 'proj_123'; -- Should delete all related tasks and reactions

-- Test collation compatibility
SELECT * FROM projects p JOIN reactions r ON p.id = r.projectId; -- Should not fail on collation mismatch
```

## 🤝 Contributing

### Development Workflow

1. **Fork repository**
2. **Create feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make changes**
4. **Test thoroughly**
5. **Commit with descriptive messages**
   ```bash
   git commit -m "Add: Feature description"
   ```
6. **Push to branch**
   ```bash
   git push origin feature/your-feature-name
   ```
7. **Create Pull Request**

### Code Style Guidelines

#### PHP
- Use PSR-12 coding standards
- Type hints for parameters and return types
- Descriptive variable names
- Comment complex logic
- Error handling with try/catch

#### JavaScript
- ES6+ syntax
- Const/let over var
- Arrow functions where appropriate
- Event delegation for dynamic elements
- Comments for non-obvious code

#### SQL
- Uppercase keywords
- Lowercase table/column names
- Use prepared statements
- Proper indexing for performance

## 📊 Performance Optimization

### Database Optimization
- Indexes on frequently queried columns (`createdBy`, `projectId`)
- Efficient JOINs for reaction queries
- Limit result sets where appropriate
- Connection pooling via PDO

### Frontend Optimization
- Minified Bootstrap CSS/JS
- Lazy loading for images in attachments
- Debounced validation for better UX
- Efficient DOM queries

### File Handling
- Size limits prevent server overload
- File type restrictions
- Organized directory structure
- Optional: Implement CDN for static assets

## 📝 Changelog

### Version 2.0 (Current - Projects Branch)
- ✅ Added AI voice input for descriptions (Web Speech API)
- ✅ Implemented teacher reaction system
- ✅ Fixed task filtering to maintain project context
- ✅ Added session initialization for all views
- ✅ Fixed project pre-selection in task forms
- ✅ Enhanced description validation (allow punctuation)
- ✅ Removed blank space from project cards
- ✅ Added reactions table migration
- ✅ Improved error handling

### Version 1.0 (Initial Release)
- Basic project CRUD operations
- Task management with file uploads
- Role-based access control
- Status tracking
- Due date validation

## 📄 License

This project is developed for educational purposes as part of the EduMind+ learning platform.

## 👨‍💻 Author

**TheLanceE**
- GitHub: [@TheLanceE](https://github.com/TheLanceE)
- Repository: [student_space](https://github.com/TheLanceE/student_space)
- Branch: `projects`

## 🙏 Acknowledgments

- Bootstrap team for the excellent UI framework
- Web Speech API for voice recognition capabilities
- PHP and MySQL communities for comprehensive documentation
- Educational institutions providing project requirements and feedback

---

**Last Updated**: December 17, 2025  
**Version**: 2.0  
**Status**: Active Development on `projects` branch

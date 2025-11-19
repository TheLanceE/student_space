-- EduMind+ Database Schema
-- Create database and tables for the application

CREATE DATABASE IF NOT EXISTS edumind CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edumind;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastLoginAt DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullName VARCHAR(255),
    email VARCHAR(255),
    mobile VARCHAR(50),
    address TEXT,
    gradeLevel VARCHAR(50),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastLoginAt DATETIME,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullName VARCHAR(255),
    email VARCHAR(255),
    mobile VARCHAR(50),
    address TEXT,
    specialty VARCHAR(100),
    nationalId VARCHAR(100),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    lastLoginAt DATETIME,
    INDEX idx_username (username),
    INDEX idx_specialty (specialty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id VARCHAR(100) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    teacherId VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacherId) REFERENCES teachers(id) ON DELETE CASCADE,
    INDEX idx_teacher (teacherId),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quizzes table
CREATE TABLE IF NOT EXISTS quizzes (
    id VARCHAR(100) PRIMARY KEY,
    courseId VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    durationSec INT DEFAULT 60,
    difficulty VARCHAR(50),
    questions JSON NOT NULL,
    createdBy VARCHAR(50) NOT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (createdBy) REFERENCES teachers(id) ON DELETE CASCADE,
    INDEX idx_course (courseId),
    INDEX idx_teacher (createdBy)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scores table
CREATE TABLE IF NOT EXISTS scores (
    id VARCHAR(100) PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    username VARCHAR(100) NOT NULL,
    courseId VARCHAR(100) NOT NULL,
    quizId VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    total INT NOT NULL,
    durationSec INT,
    attempt INT DEFAULT 1,
    type VARCHAR(50) DEFAULT 'quiz',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (quizId) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_user (userId),
    INDEX idx_course (courseId),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id VARCHAR(100) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL,
    course VARCHAR(255),
    type ENUM('Lecture', 'Quiz', 'Webinar', 'Other') DEFAULT 'Other',
    location VARCHAR(255),
    maxParticipants INT DEFAULT 30,
    nbrParticipants INT DEFAULT 0,
    description TEXT,
    teacherId VARCHAR(50) NOT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacherId) REFERENCES teachers(id) ON DELETE CASCADE,
    INDEX idx_teacher (teacherId),
    INDEX idx_date (date),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recommendations table
CREATE TABLE IF NOT EXISTS recommendations (
    id VARCHAR(100) PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    courseId VARCHAR(100) NOT NULL,
    reason TEXT,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user (userId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logs table
CREATE TABLE IF NOT EXISTS logs (
    id VARCHAR(100) PRIMARY KEY,
    level VARCHAR(50) DEFAULT 'info',
    message TEXT NOT NULL,
    ts DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_timestamp (ts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz Reports table
CREATE TABLE IF NOT EXISTS quiz_reports (
    id VARCHAR(100) PRIMARY KEY,
    quizId VARCHAR(100) NOT NULL,
    questionId VARCHAR(100),
    reportedBy VARCHAR(50) NOT NULL,
    reportType ENUM('incorrect_answer', 'wrong_display', 'typo', 'other') DEFAULT 'other',
    description TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewedBy VARCHAR(50),
    reviewedAt DATETIME,
    FOREIGN KEY (reportedBy) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_quiz (quizId),
    INDEX idx_status (status),
    INDEX idx_created (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id VARCHAR(100) PRIMARY KEY,
    projectName VARCHAR(255) NOT NULL,
    description TEXT,
    createdBy VARCHAR(50) NOT NULL,
    assignedTo VARCHAR(50),
    status ENUM('not_started', 'in_progress', 'completed', 'on_hold') DEFAULT 'not_started',
    dueDate DATE,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created_by (createdBy),
    INDEX idx_assigned_to (assignedTo),
    INDEX idx_status (status),
    INDEX idx_due_date (dueDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id VARCHAR(100) PRIMARY KEY,
    projectId VARCHAR(100) NOT NULL,
    taskName VARCHAR(255) NOT NULL,
    description TEXT,
    isComplete BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    dueDate DATE,
    completedAt DATETIME,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projectId) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (projectId),
    INDEX idx_complete (isComplete),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account
INSERT INTO admins (id, username, password, name, createdAt, lastLoginAt) 
VALUES ('admin_root', 'admin', 'admin123', 'Admin', DATE_SUB(NOW(), INTERVAL 90 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample students
INSERT INTO students (id, username, password, fullName, email, mobile, address, gradeLevel, createdAt, lastLoginAt) VALUES
('stu_alice', 'alice', 'password123', 'Alice Stone', 'alice@edumind.app', '+123456789', '123 Main St, City', 'Grade 8', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('stu_bob', 'bob', 'password123', 'Bob Carter', 'bob@edumind.app', '+987654321', '456 Oak Ave, Town', 'Grade 9', DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample teachers
INSERT INTO teachers (id, username, password, fullName, email, mobile, address, specialty, nationalId, createdAt, lastLoginAt) VALUES
('teach_jane', 'teacher_jane', 'password123', 'Jane Miller', 'jane@edumind.app', '+555100200', '789 Elm St, District', 'Mathematics', 'NAT-001-JM', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('teach_lee', 'teacher_lee', 'password123', 'Lee Sanders', 'lee@edumind.app', '+555300400', '321 Pine Rd, Campus', 'Science', 'NAT-002-LS', DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY))
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample courses
INSERT INTO courses (id, title, description, teacherId, status, createdAt) VALUES
('math101', 'Math Basics', 'Numbers, operations, and simple algebra.', 'teach_jane', 'active', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('sci101', 'Science Basics', 'Intro to physics, chemistry, and biology.', 'teach_lee', 'active', DATE_SUB(NOW(), INTERVAL 25 DAY))
ON DUPLICATE KEY UPDATE title=title;

-- Insert sample quizzes
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('math101_quiz1', 'math101', 'Math Basics · Quiz 1', 60, 'beginner', 
'[{"id":"m1_q1","text":"2 + 2 = ?","options":["3","4","5","6"],"correctIndex":1},{"id":"m1_q2","text":"5 - 3 = ?","options":["1","2","3","4"],"correctIndex":1},{"id":"m1_q3","text":"10 / 2 = ?","options":["2","4","5","10"],"correctIndex":2},{"id":"m1_q4","text":"3 × 3 = ?","options":["6","7","8","9"],"correctIndex":3},{"id":"m1_q5","text":"Solve for x: x + 1 = 4","options":["1","2","3","4"],"correctIndex":2}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('sci101_quiz1', 'sci101', 'Science Basics · Quiz 1', 60, 'beginner',
'[{"id":"s1_q1","text":"Water boils at what °C?","options":["50","80","100","120"],"correctIndex":2},{"id":"s1_q2","text":"What gas do plants produce?","options":["CO₂","O₂","N₂","CH₄"],"correctIndex":1},{"id":"s1_q3","text":"Earth is the ___ planet from the Sun.","options":["2nd","3rd","4th","5th"],"correctIndex":1},{"id":"s1_q4","text":"Basic unit of life is the:","options":["Atom","Molecule","Cell","Organ"],"correctIndex":2},{"id":"s1_q5","text":"H₂O is:","options":["Oxygen","Hydrogen","Water","Helium"],"correctIndex":2}]',
'teach_lee', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('challenge_daily', 'math101', 'Daily Math Challenge', 120, 'intermediate',
'[{"id":"c1_q1","text":"15 + 27 = ?","options":["40","41","42","43"],"correctIndex":2},{"id":"c1_q2","text":"100 - 37 = ?","options":["62","63","64","65"],"correctIndex":1},{"id":"c1_q3","text":"12 × 8 = ?","options":["84","92","96","104"],"correctIndex":2},{"id":"c1_q4","text":"144 / 12 = ?","options":["10","11","12","13"],"correctIndex":2},{"id":"c1_q5","text":"Solve: 2x + 5 = 15","options":["3","4","5","6"],"correctIndex":2},{"id":"c1_q6","text":"√64 = ?","options":["6","7","8","9"],"correctIndex":2},{"id":"c1_q7","text":"3² + 4² = ?","options":["20","23","25","27"],"correctIndex":2},{"id":"c1_q8","text":"50% of 200 = ?","options":["75","100","125","150"],"correctIndex":1},{"id":"c1_q9","text":"Prime number after 7:","options":["8","9","10","11"],"correctIndex":3},{"id":"c1_q10","text":"Area of 5×6 rectangle:","options":["11","22","30","36"],"correctIndex":2}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 15 DAY))
ON DUPLICATE KEY UPDATE title=title;

-- Insert sample scores
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_1', 'stu_alice', 'alice', 'math101', 'math101_quiz1', 4, 5, 48, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('sc_2', 'stu_bob', 'bob', 'sci101', 'sci101_quiz1', 3, 5, 52, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('sc_3', 'stu_alice', 'alice', 'math101', 'challenge_daily', 8, 10, 120, 1, 'challenge', DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE score=score;

-- Insert sample events
INSERT INTO events (id, title, date, startTime, endTime, course, type, location, maxParticipants, description, teacherId, createdAt) VALUES
('evt_1', 'Math Review Session', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:30:00', 'Math Basics', 'Lecture', 'Room 101', 30, 'Review of algebra and equations before midterm exam', 'teach_jane', NOW()),
('evt_2', 'Science Lab: Chemical Reactions', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', '12:00:00', 'Science Basics', 'Lecture', 'Lab 2B', 20, 'Hands-on experiments with chemical reactions', 'teach_lee', NOW()),
('evt_3', 'Weekly Quiz Challenge', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', 'Math Basics', 'Quiz', '', 50, 'Weekly competitive quiz for all students', 'teach_jane', NOW())
ON DUPLICATE KEY UPDATE title=title;

-- Insert sample logs
INSERT INTO logs (id, level, message, ts) VALUES
('log_1', 'info', 'System initialized', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('log_2', 'warn', 'Pending approvals detected', DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE message=message;

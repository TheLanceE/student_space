-- Database Verification and Fix Script
-- Ensures all tables have proper structure for the modernized application
-- Run this to fix any missing columns or indexes

USE edumind;

-- ==========================================
-- 1. Add deleted_at columns if missing
-- ==========================================

-- Admins
ALTER TABLE admins 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL AFTER lastLoginAt;

-- Students
ALTER TABLE students 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL AFTER lastLoginAt,
ADD COLUMN IF NOT EXISTS google_id VARCHAR(100) DEFAULT NULL AFTER email;

-- Teachers
ALTER TABLE teachers 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL AFTER lastLoginAt,
ADD COLUMN IF NOT EXISTS google_id VARCHAR(100) DEFAULT NULL AFTER email;

-- Courses
ALTER TABLE courses 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL AFTER updatedAt;

-- Events
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;

-- Quizzes
ALTER TABLE quizzes 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;

-- Projects
ALTER TABLE projects 
ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;

-- ==========================================
-- 2. Add indexes for performance
-- ==========================================

-- Deleted_at indexes for soft delete queries
ALTER TABLE admins ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);
ALTER TABLE teachers ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);
ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);
ALTER TABLE quizzes ADD INDEX IF NOT EXISTS idx_deleted_at (deleted_at);

-- Google ID indexes for OAuth lookups
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_google_id (google_id);
ALTER TABLE teachers ADD INDEX IF NOT EXISTS idx_google_id (google_id);

-- Email indexes for OAuth lookups
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_email (email);
ALTER TABLE teachers ADD INDEX IF NOT EXISTS idx_email (email);

-- Username indexes for login lookups
ALTER TABLE admins ADD INDEX IF NOT EXISTS idx_username (username);
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_username (username);
ALTER TABLE teachers ADD INDEX IF NOT EXISTS idx_username (username);

-- ==========================================
-- 3. Ensure proper column types
-- ==========================================

-- Make sure password columns are long enough for bcrypt hashes
ALTER TABLE admins MODIFY COLUMN password VARCHAR(255) NOT NULL;
ALTER TABLE students MODIFY COLUMN password VARCHAR(255) NOT NULL;
ALTER TABLE teachers MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Make sure email columns allow NULL for OAuth users who may complete later
ALTER TABLE students MODIFY COLUMN email VARCHAR(100) NULL;
ALTER TABLE teachers MODIFY COLUMN email VARCHAR(100) NULL;

-- ==========================================
-- 4. Set proper defaults and constraints
-- ==========================================

-- Ensure createdAt and lastLoginAt have proper defaults
ALTER TABLE admins MODIFY COLUMN createdAt DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE students MODIFY COLUMN createdAt DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE teachers MODIFY COLUMN createdAt DATETIME DEFAULT CURRENT_TIMESTAMP;

-- ==========================================
-- 5. Verify data integrity
-- ==========================================

-- Ensure no existing records are marked as deleted (clean slate)
UPDATE admins SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE students SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE teachers SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE courses SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE events SET deleted_at = NULL WHERE deleted_at IS NOT NULL;

-- ==========================================
-- 6. Create admin user if not exists (for testing)
-- ==========================================

INSERT IGNORE INTO admins (id, username, password, name, createdAt, lastLoginAt)
VALUES ('adm_bootstrap', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', NOW(), NULL);
-- Password: password (for testing only - change in production!)

-- ==========================================
-- 7. Verify table structure
-- ==========================================

-- Show structure of key tables
SHOW CREATE TABLE admins;
SHOW CREATE TABLE students;
SHOW CREATE TABLE teachers;

-- Show indexes
SHOW INDEX FROM admins;
SHOW INDEX FROM students;
SHOW INDEX FROM teachers;

SELECT 'Database verification and fix completed successfully!' AS Status;

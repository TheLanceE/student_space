-- Database Migration: Add soft deletes and OAuth support
-- Run this script to update the EduMind database schema

USE edumind;

-- Add deleted_at columns for soft deletes
ALTER TABLE admins ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;
ALTER TABLE students ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;
ALTER TABLE courses ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;
ALTER TABLE events ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;
ALTER TABLE quizzes ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;

-- Add Google OAuth columns
ALTER TABLE students ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL;
ALTER TABLE admins ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL;

-- Add indexes for soft deletes
ALTER TABLE students ADD INDEX IF NOT EXISTS idx_deleted (deleted_at);
ALTER TABLE teachers ADD INDEX IF NOT EXISTS idx_deleted (deleted_at);
ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_deleted (deleted_at);
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_deleted (deleted_at);
ALTER TABLE quizzes ADD INDEX IF NOT EXISTS idx_deleted (deleted_at);

-- Add unique indexes for Google IDs
ALTER TABLE students ADD UNIQUE INDEX IF NOT EXISTS idx_google_id (google_id);
ALTER TABLE teachers ADD UNIQUE INDEX IF NOT EXISTS idx_google_id (google_id);
ALTER TABLE admins ADD UNIQUE INDEX IF NOT EXISTS idx_google_id (google_id);

-- Performance indexes
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_teacher_date (teacherId, date);
ALTER TABLE scores ADD INDEX IF NOT EXISTS idx_user_quiz (userId, quizId, timestamp);
ALTER TABLE quiz_reports ADD INDEX IF NOT EXISTS idx_quiz_status (quizId, status);

-- Create audit log table for admin actions
CREATE TABLE IF NOT EXISTS admin_audit_log (
    id VARCHAR(100) PRIMARY KEY,
    admin_id VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id VARCHAR(100),
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create OAuth tokens table (optional, for refresh tokens)
CREATE TABLE IF NOT EXISTS oauth_tokens (
    id VARCHAR(100) PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_role ENUM('student', 'teacher', 'admin') NOT NULL,
    provider VARCHAR(50) NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_role),
    INDEX idx_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update existing records to set deleted_at as NULL (safety)
UPDATE admins SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE students SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE teachers SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE courses SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE events SET deleted_at = NULL WHERE deleted_at IS NOT NULL;
UPDATE quizzes SET deleted_at = NULL WHERE deleted_at IS NOT NULL;

-- Add sample fake accounts for testing bulk delete
INSERT INTO students (id, username, password, fullName, email, gradeLevel, createdAt, lastLoginAt) VALUES
('fake_user1', 'user123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', '', 'Grade 7', DATE_SUB(NOW(), INTERVAL 120 DAY), NULL),
('fake_user2', 'testaccount', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Account', NULL, 'Grade 8', DATE_SUB(NOW(), INTERVAL 150 DAY), NULL)
ON DUPLICATE KEY UPDATE username=username;

SELECT '✅ Migration complete! Added soft deletes, OAuth support, and audit logging.' as status;

-- ============================================
-- EduMind+ Database Schema Updates
-- Date: November 21, 2025
-- Purpose: Add soft delete support and session management
-- ============================================

-- Add soft delete column to students table
ALTER TABLE students 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER lastLoginAt;

-- Add soft delete column to teachers table
ALTER TABLE teachers 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER lastLoginAt;

-- Add index for better query performance on deleted_at
ALTER TABLE students ADD INDEX idx_deleted_at (deleted_at);
ALTER TABLE teachers ADD INDEX idx_deleted_at (deleted_at);

-- ============================================
-- Optional: Session tracking table (for future use)
-- ============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(255) PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_role ENUM('student', 'teacher', 'admin') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Optional: Audit log table (for tracking changes)
-- ============================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50),
    user_role ENUM('student', 'teacher', 'admin'),
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id VARCHAR(50),
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Verify changes
-- ============================================
-- Run these to verify the changes were applied:
-- DESCRIBE students;
-- DESCRIBE teachers;
-- SHOW TABLES LIKE '%session%';
-- SHOW TABLES LIKE '%audit%';

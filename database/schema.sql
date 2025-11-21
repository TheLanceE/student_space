-- EduMind Reports Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS edumind_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE edumind_db;

-- Create reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student VARCHAR(255) NOT NULL,
    quiz VARCHAR(255) DEFAULT NULL,
    type VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    content TEXT NOT NULL,
    created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_date DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_student (student),
    INDEX idx_type (type),
    INDEX idx_created_date (created_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
INSERT INTO reports (student, quiz, type, status, content, created_date) VALUES
('John Doe', 'Math Quiz 1', 'Performance', 'Pending', 'Review', '2025-11-11 23:04:00'),
('Alex Johnson', NULL, 'Performance', 'Pending', 'PERFORMANCE SUMMARY REPORT Generated on: 2025-11-11. This report contains detailed analysis of student performance metrics.', '2025-11-11 22:25:00'),
('Alex Johnson', NULL, 'Performance', 'Pending', 'PERFORMANCE SUMMARY REPORT Generated on: 2025-11-11. This report contains detailed analysis of student performance metrics.', '2025-11-11 22:20:00');


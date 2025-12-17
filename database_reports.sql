-- Reports table (Teacher/Admin)
-- Uses existing database configured in Controllers/config.php (default: edumind)

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student VARCHAR(255) NOT NULL,
    quiz VARCHAR(255) NULL,
    type VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    content TEXT NOT NULL,
    created_by VARCHAR(50) NOT NULL,
    created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_date DATETIME NULL,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_date (created_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

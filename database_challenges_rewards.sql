-- Challenges / Rewards / Points schema for EduMind+
-- Safe to run multiple times.

USE edumind;

CREATE TABLE IF NOT EXISTS challenges (
    id VARCHAR(100) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    level INT NOT NULL DEFAULT 0,
    points INT NOT NULL DEFAULT 10,
    category VARCHAR(100),
    skillTags JSON,
    prerequisiteLevel INT DEFAULT NULL,
    createdBy VARCHAR(50) NOT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_createdBy (createdBy),
    FOREIGN KEY (createdBy) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS challenge_completions (
    id VARCHAR(100) PRIMARY KEY,
    challengeId VARCHAR(100) NOT NULL,
    studentId VARCHAR(50) NOT NULL,
    rating TINYINT NULL,
    completedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_student_challenge (challengeId, studentId),
    INDEX idx_student (studentId),
    INDEX idx_challenge (challengeId),
    FOREIGN KEY (challengeId) REFERENCES challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (studentId) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS points_ledger (
    id VARCHAR(100) PRIMARY KEY,
    studentId VARCHAR(50) NOT NULL,
    delta INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    refType VARCHAR(50) DEFAULT NULL,
    refId VARCHAR(100) DEFAULT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_student_created (studentId, createdAt),
    FOREIGN KEY (studentId) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rewards (
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    costPoints INT NOT NULL,
    tierRequired INT DEFAULT NULL,
    stock INT DEFAULT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_cost (costPoints)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reward_redemptions (
    id VARCHAR(100) PRIMARY KEY,
    rewardId VARCHAR(100) NOT NULL,
    studentId VARCHAR(50) NOT NULL,
    status ENUM('pending','redeemed','rejected') NOT NULL DEFAULT 'redeemed',
    requestedBalance INT DEFAULT NULL,
    shortBy INT DEFAULT NULL,
    note TEXT,
    requestedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewedBy VARCHAR(50) DEFAULT NULL,
    reviewedAt DATETIME DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_student (studentId),
    INDEX idx_reward (rewardId),
    FOREIGN KEY (rewardId) REFERENCES rewards(id) ON DELETE CASCADE,
    FOREIGN KEY (studentId) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

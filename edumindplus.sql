DROP DATABASE IF EXISTS edumind;
CREATE DATABASE edumind CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE edumind;

-- STUDENTS TABLE
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    points INT DEFAULT 0,
    role VARCHAR(20) DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO students (name, email, password, role, points)
VALUES ('Test Student', 'student@example.com', 'password', 'student', 0);

-- CHALLENGES TABLE
CREATE TABLE challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) DEFAULT 'quiz',
    points INT DEFAULT 0,
    criteria TEXT,
    status VARCHAR(20) DEFAULT 'active',
    createdBy INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (createdBy) REFERENCES students(id) ON DELETE SET NULL
);

INSERT INTO challenges (title, description, type, points, createdBy)
VALUES 
('Read a Chapter', 'Read one chapter of any book', 'reading', 10, 1),
('Daily Quiz', 'Complete the daily quiz', 'quiz', 5, 1);

-- REWARDS TABLE
CREATE TABLE rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) DEFAULT 'badge',
    pointsCost INT DEFAULT 0,
    availability INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO rewards (title, description, type, pointsCost, availability)
VALUES 
('Sticker', 'A cool sticker!', 'badge', 10, 100),
('Notebook', 'A small notebook', 'perk', 25, 50);

-- POINTS HISTORY TABLE
CREATE TABLE points_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    points INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (studentID) REFERENCES students(id) ON DELETE CASCADE
);

-- CHALLENGE COMPLETIONS TABLE
CREATE TABLE challenge_completions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    challengeID INT NOT NULL,
    pointsAwarded INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (studentID) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (challengeID) REFERENCES challenges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_completion (studentID, challengeID)  -- Prevents duplicate completions
);

-- REWARD REDEMPTIONS TABLE
CREATE TABLE reward_redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    rewardID INT NOT NULL,
    redeemedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (studentID) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (rewardID) REFERENCES rewards(id) ON DELETE CASCADE
);
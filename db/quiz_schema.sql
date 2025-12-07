-- Create the database
CREATE DATABASE IF NOT EXISTS edumind;
USE edumind;

-- Table for quizzes
CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    grade VARCHAR(50) NOT NULL,
    passing_grade DECIMAL(5,2) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
    time_limit INT NULL COMMENT 'Time limit in minutes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_grade (grade)
);

-- Table for questions
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_question_order (question_order),
    UNIQUE KEY unique_quiz_question_order (quiz_id, question_order)
);

-- Table for question options
CREATE TABLE question_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_label VARCHAR(10) NOT NULL COMMENT 'e.g., A, B, C, D or 1, 2, 3, 4',
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question_id (question_id),
    INDEX idx_is_correct (is_correct),
    UNIQUE KEY unique_question_option_label (question_id, option_label)
);

-- Optional: Table for quiz attempts (if you need to track user attempts)
CREATE TABLE quiz_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'Reference to your users table',
    score DECIMAL(5,2) DEFAULT 0,
    passed BOOLEAN DEFAULT FALSE,
    time_taken INT COMMENT 'Time taken in seconds',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz_user (quiz_id, user_id),
    INDEX idx_completed_at (completed_at)
);

-- Optional: Table for attempt answers (if you need to track user answers)
CREATE TABLE attempt_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES question_options(id) ON DELETE CASCADE,
    INDEX idx_attempt_question (attempt_id, question_id)
);

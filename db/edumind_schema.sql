-- edumind_schema.sql
-- Schema for the edumind database used by ProjectController.php

CREATE DATABASE IF NOT EXISTS `edumind` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `edumind`;

-- Users table (minimal)
CREATE TABLE IF NOT EXISTS `users` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'student',
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects table
CREATE TABLE IF NOT EXISTS `projects` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `projectName` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `createdBy` VARCHAR(64) NOT NULL,
  `assignedTo` VARCHAR(64) DEFAULT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'not_started',
  `dueDate` DATE DEFAULT NULL,
  `expectedTaskCount` INT DEFAULT 0,
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` TIMESTAMP NULL DEFAULT NULL,
  INDEX (`createdBy`),
  INDEX (`assignedTo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tasks table
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `projectId` VARCHAR(64) NOT NULL,
  `taskName` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `attachmentPath` VARCHAR(255) DEFAULT NULL,
  `priority` ENUM('low','medium','high') DEFAULT 'low',
  `isComplete` TINYINT(1) NOT NULL DEFAULT 0,
  `dueDate` DATE DEFAULT NULL,
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`projectId`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  INDEX (`projectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed a debug user and a sample project and tasks
INSERT IGNORE INTO `users` (`id`, `username`, `password`, `role`) VALUES
('stu_debug', 'debug_student', NULL, 'student'),
('teach_debug', 'debug_teacher', NULL, 'teacher');

-- Sample project id matching ProjectController generated IDs prefix 'proj_' + hex
INSERT IGNORE INTO `projects` (`id`, `projectName`, `description`, `createdBy`, `assignedTo`, `status`, `dueDate`) VALUES
('proj_debug_1', 'Debug Project', 'This is a seeded debug project for development.', 'stu_debug', 'stu_debug', 'in_progress', DATE_ADD(CURDATE(), INTERVAL 14 DAY));

INSERT IGNORE INTO `tasks` (`id`, `projectId`, `taskName`, `description`, `priority`, `isComplete`, `dueDate`) VALUES
('task_debug_1', 'proj_debug_1', 'Write spec', 'Write the project specification', 'high', 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY)),
('task_debug_2', 'proj_debug_1', 'Initial implementation', 'Create the basic project CRUD', 'medium', 0, DATE_ADD(CURDATE(), INTERVAL 7 DAY));

-- End of file

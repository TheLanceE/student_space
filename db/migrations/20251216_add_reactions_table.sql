-- Reactions table migration
-- Creates a dedicated table to store per-user reactions on projects
-- Ensures one reaction per (projectId, userId) via a unique key

CREATE TABLE IF NOT EXISTS `reactions` (
  `id` VARCHAR(64) NOT NULL,
  `projectId` VARCHAR(64) NOT NULL,
  `userId` VARCHAR(64) NOT NULL,
  `type` VARCHAR(32) NOT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_project_user` (`projectId`, `userId`),
  KEY `idx_projectId` (`projectId`),
  KEY `idx_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Migration: Add reactions table to model teacher reactions per project
-- Allows many reactions per project and many per teacher; prevents duplicate per (projectId,userId)

CREATE TABLE IF NOT EXISTS reactions (
  id VARCHAR(64) NOT NULL,
  projectId VARCHAR(64) NOT NULL,
  userId VARCHAR(64) NOT NULL,
  type VARCHAR(32) NOT NULL,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reactions_project (projectId),
  KEY idx_reactions_user (userId),
  UNIQUE KEY uniq_project_user (projectId, userId)
);

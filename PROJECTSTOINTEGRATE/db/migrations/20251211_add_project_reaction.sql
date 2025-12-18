-- Migration: Add reaction field to projects table
-- Date: 2025-12-11
-- Description: Allow teachers to add reactions (emoji) to student projects

USE `edumind`;

ALTER TABLE `projects` 
ADD COLUMN `reaction` VARCHAR(10) DEFAULT NULL AFTER `expectedTaskCount`;

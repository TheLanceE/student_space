-- Migration: Add project_members table and assignedTo column on tasks
-- Run this SQL in your edumind database (phpMyAdmin or mysql CLI)

START TRANSACTION;

-- Create mapping table for project members
CREATE TABLE IF NOT EXISTS project_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  projectId VARCHAR(128) NOT NULL,
  userId VARCHAR(128) NOT NULL,
  role VARCHAR(32) DEFAULT 'student',
  addedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (projectId),
  INDEX (userId)
);

-- Add assignedTo column to tasks for per-task assignment
ALTER TABLE tasks
  ADD COLUMN IF NOT EXISTS assignedTo VARCHAR(128) DEFAULT NULL,
  ADD INDEX (assignedTo);

COMMIT;

-- Notes:
-- 1) This migration adds a `project_members` mapping table and an `assignedTo`
--    column to `tasks`. After running it you can insert project members and
--    assign tasks to users via the `assignedTo` column (user id string).
-- 2) Adjust any application-level user id format to match your existing users.

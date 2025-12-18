-- Add expectedTaskCount column to projects table for calculating progress percentage
ALTER TABLE `projects`
  ADD COLUMN `expectedTaskCount` INT DEFAULT 0 AFTER `dueDate`;

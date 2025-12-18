-- Add attachmentPath column to tasks table for storing file attachments
ALTER TABLE `tasks`
  ADD COLUMN `attachmentPath` VARCHAR(255) NULL AFTER `description`;

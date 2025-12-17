-- Fix Admin Password Script
-- Run this in phpMyAdmin or MySQL CLI to fix the admin login
-- Username: admin
-- Password: admin123

USE edumind;

-- Update existing admin password to a proper bcrypt hash for 'admin123'
UPDATE admins 
SET password = '$2y$10$pUm1WIjYDvm2pmsHDvpSE.rH7SOQ0O2ApKKyO02.d/aL8EldYNaC6' 
WHERE username = 'admin';

-- If no admin exists, insert one
INSERT INTO admins (id, username, password, name, createdAt, lastLoginAt) 
SELECT 'admin_root', 'admin', '$2y$10$pUm1WIjYDvm2pmsHDvpSE.rH7SOQ0O2ApKKyO02.d/aL8EldYNaC6', 'Admin', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = 'admin');

-- Verify the fix
SELECT id, username, LEFT(password, 30) as password_preview, name FROM admins;

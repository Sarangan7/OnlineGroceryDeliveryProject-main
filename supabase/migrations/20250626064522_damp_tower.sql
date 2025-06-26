-- Admin Setup SQL Statements
-- Run these commands to set up the admin account

USE quickart;

-- Insert admin account (if not already exists)
INSERT IGNORE INTO account(AccID, AccEmail, AccPassword) VALUES(1, 'admin@quickart.com', 'admin123');

-- Update existing admin account (if needed)
UPDATE account SET AccEmail='admin@quickart.com', AccPassword='admin123' WHERE AccID=1;

-- Verify admin account
SELECT * FROM account WHERE AccID=1;

-- Admin Login Credentials:
-- Email: admin@quickart.com
-- Password: admin123
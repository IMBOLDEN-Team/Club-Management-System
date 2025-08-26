-- Add member_limit column to CLUB table
-- Run this script to update existing databases

USE `CLUB-MANAGEMENT-SYSTEM`;

-- Add member_limit column if it doesn't exist
ALTER TABLE CLUB ADD COLUMN IF NOT EXISTS member_limit INT DEFAULT NULL;

-- Update existing clubs to have no limit (NULL) by default
UPDATE CLUB SET member_limit = NULL WHERE member_limit IS NULL;

-- Archive Table SQL Script
-- This script creates the archive table for storing deleted, graduated, or inactive users
-- Version: 1.0
-- Created: 2025-11-14

-- Create archive table
CREATE TABLE IF NOT EXISTS archive (
    ArchiveID INT AUTO_INCREMENT PRIMARY KEY,
    OriginalUserType ENUM('students', 'faculty', 'security') NOT NULL,
    OriginalUserID VARCHAR(50) NOT NULL,
    FirstName VARCHAR(100),
    MiddleName VARCHAR(100),
    LastName VARCHAR(100),
    Gender VARCHAR(20),
    BirthDate DATE,
    Department VARCHAR(50),
    CourseOrSchedule VARCHAR(100),
    YearLevelOrPosition VARCHAR(50),
    Section VARCHAR(50),
    ImagePath VARCHAR(255),
    ArchiveReason ENUM('deleted', 'graduated', 'resigned', 'inactive') NOT NULL,
    ArchiveDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    OriginalCreatedDate DATETIME,
    AdminNotes LONGTEXT,
    INDEX idx_original_user_id (OriginalUserID),
    INDEX idx_user_type (OriginalUserType),
    INDEX idx_archive_reason (ArchiveReason),
    INDEX idx_archive_date (ArchiveDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

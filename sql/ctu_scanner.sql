-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 03:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ctu_scanner`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` varchar(20) NOT NULL,
  `AdminFName` varchar(50) NOT NULL,
  `AdminMName` varchar(50) DEFAULT NULL,
  `AdminLName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `AdminFName`, `AdminMName`, `AdminLName`, `email`, `password`, `isActive`, `created_at`) VALUES
('ADM-001', 'Admin', '', 'User', 'admin@ctu.edu.ph', '$2y$10$b6JxHPzI3MXul/FML1cEauTjM3FigPgHyTwux8A/SYH2K2cOUDjEa', 1, '2025-08-30 01:39:52');

-- --------------------------------------------------------

--
-- Table structure for table `archive`
--

CREATE TABLE `archive` (
  `ArchiveID` int(11) NOT NULL,
  `OriginalUserType` enum('students','faculty','security') NOT NULL,
  `OriginalUserID` varchar(50) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Gender` varchar(20) DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `Department` varchar(50) DEFAULT NULL,
  `CourseOrSchedule` varchar(100) DEFAULT NULL,
  `YearLevelOrPosition` varchar(50) DEFAULT NULL,
  `Section` varchar(50) DEFAULT NULL,
  `ImagePath` varchar(255) DEFAULT NULL,
  `ArchiveReason` enum('deleted','graduated','resigned','inactive') NOT NULL,
  `ArchiveDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `OriginalCreatedDate` datetime DEFAULT NULL,
  `AdminNotes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entrylogs`
--

CREATE TABLE `entrylogs` (
  `EntryID` int(11) NOT NULL,
  `PersonID` varchar(20) NOT NULL,
  `PersonType` enum('student','faculty') NOT NULL,
  `Date` date NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ScannerID` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrylogs`
--

INSERT INTO `entrylogs` (`EntryID`, `PersonID`, `PersonType`, `Date`, `Timestamp`, `ScannerID`) VALUES
(1, '8221182', 'student', '2025-09-23', '2025-09-23 08:12:59', 'SC001'),
(2, '8220469', 'student', '2025-09-23', '2025-09-23 08:13:08', 'SC001'),
(3, '8221363', 'student', '2025-09-23', '2025-09-23 08:13:13', 'SC001'),
(4, '8220811', 'student', '2025-09-23', '2025-09-23 08:13:18', 'SC001'),
(5, '8221366', 'student', '2025-09-23', '2025-09-23 08:13:24', 'SC001'),
(6, '8221425', 'student', '2025-09-23', '2025-09-23 08:13:32', 'SC001'),
(7, '8221363', 'student', '2025-09-23', '2025-09-23 08:16:06', 'SC001'),
(8, '8221363', 'student', '2025-09-23', '2025-09-23 08:16:30', 'SC001'),
(9, '8221363', 'student', '2025-09-23', '2025-09-23 08:17:27', 'SC001'),
(10, '8221363', 'student', '2025-09-23', '2025-09-23 08:17:33', 'SC001'),
(11, '8221363', 'student', '2025-09-23', '2025-09-23 08:21:54', 'SC001'),
(12, '8221182', 'student', '2025-10-08', '2025-10-08 02:11:17', 'SC001'),
(13, '8221182', 'student', '2025-10-08', '2025-10-08 02:11:35', 'SC001'),
(14, '8221182', 'student', '2025-10-08', '2025-10-08 02:11:38', 'SC001'),
(15, '8221182', 'student', '2025-10-08', '2025-10-08 02:11:40', 'SC001'),
(16, '8221182', 'student', '2025-10-08', '2025-10-08 02:11:43', 'SC001'),
(17, '8221182', 'student', '2025-10-08', '2025-10-08 02:11:44', 'SC001'),
(18, '8221182', 'student', '2025-10-24', '2025-10-24 12:34:02', 'SC001'),
(19, '8221182', 'student', '2025-10-24', '2025-10-24 12:34:15', 'SC001'),
(20, '8221183', 'student', '2025-11-14', '2025-11-14 12:53:08', 'SC001'),
(21, 'FAC-002', 'faculty', '2025-11-14', '2025-11-14 12:53:49', 'SC001'),
(22, '8221183', 'student', '2025-11-14', '2025-11-14 15:36:00', 'SC001'),
(23, '8221183', 'student', '2025-11-14', '2025-11-14 15:36:24', 'SC001'),
(24, '8221183', 'student', '2025-11-14', '2025-11-14 15:45:41', 'SC001'),
(25, '8221183', 'student', '2025-11-14', '2025-11-14 15:45:42', 'SC001'),
(26, '8221183', 'student', '2025-11-14', '2025-11-14 15:46:07', 'SC001'),
(27, '8221183', 'student', '2025-11-19', '2025-11-19 12:58:23', 'SC001'),
(28, '8221183', 'student', '2025-11-19', '2025-11-19 12:58:34', 'SC001'),
(29, '8221183', 'student', '2025-11-19', '2025-11-19 13:03:19', 'SC001'),
(30, '8221183', 'student', '2025-11-19', '2025-11-19 13:03:25', 'SC001'),
(31, '8221183', 'student', '2025-11-19', '2025-11-19 13:03:26', 'SC001'),
(32, '8221183', 'student', '2025-11-19', '2025-11-19 13:03:36', 'SC001'),
(33, '8221183', 'student', '2025-11-19', '2025-11-19 13:06:13', 'SC001'),
(34, '8221183', 'student', '2025-11-19', '2025-11-19 13:06:14', 'SC001'),
(35, '8221183', 'student', '2025-11-19', '2025-11-19 13:06:22', 'SC001'),
(36, '8221183', 'student', '2025-11-19', '2025-11-19 13:06:36', 'SC001'),
(37, '8221183', 'student', '2025-11-19', '2025-11-19 13:08:25', 'SC001'),
(38, '8221183', 'student', '2025-11-19', '2025-11-19 13:08:26', 'SC001'),
(39, '8221183', 'student', '2025-11-19', '2025-11-19 13:08:35', 'SC001'),
(40, '8221183', 'student', '2025-11-19', '2025-11-19 13:09:36', 'SC001'),
(41, '8221183', 'student', '2025-11-19', '2025-11-19 13:09:54', 'SC001'),
(42, '8221183', 'student', '2025-11-19', '2025-11-19 13:10:02', 'SC001'),
(43, '8221183', 'student', '2025-11-19', '2025-11-19 13:11:04', 'SC001'),
(44, '8221183', 'student', '2025-11-19', '2025-11-19 13:11:21', 'SC001'),
(45, '8221183', 'student', '2025-11-19', '2025-11-19 13:14:56', 'SC001'),
(46, '8221183', 'student', '2025-11-19', '2025-11-19 13:22:59', 'SC001'),
(47, '8111111', 'student', '2025-11-19', '2025-11-19 13:29:40', 'SC001'),
(48, '8111111', 'student', '2025-11-19', '2025-11-19 13:29:53', 'SC001'),
(49, '8111111', 'student', '2025-11-19', '2025-11-19 13:29:59', 'SC001'),
(50, '8111111', 'student', '2025-11-19', '2025-11-19 13:30:17', 'SC001'),
(51, '8111111', 'student', '2025-11-19', '2025-11-19 13:31:04', 'SC001'),
(52, '8221183', 'student', '2025-11-19', '2025-11-19 13:31:21', 'SC001'),
(53, '8221183', 'student', '2025-11-19', '2025-11-19 13:31:28', 'SC001'),
(54, '8221183', 'student', '2025-11-19', '2025-11-19 13:31:51', 'SC001'),
(55, '8221183', 'student', '2025-11-19', '2025-11-19 13:41:37', 'SC001'),
(56, '8111111', 'student', '2025-11-19', '2025-11-19 13:59:49', 'SC001'),
(57, '8111111', 'student', '2025-11-19', '2025-11-19 14:00:11', 'SC001'),
(58, '8111111', 'student', '2025-11-19', '2025-11-19 14:01:10', 'SC001'),
(59, 'FAC-002', 'faculty', '2025-11-19', '2025-11-19 14:02:12', 'SC001'),
(60, 'FAC-002', 'faculty', '2025-11-19', '2025-11-19 14:02:31', 'SC001');

-- --------------------------------------------------------

--
-- Table structure for table `exitlogs`
--

CREATE TABLE `exitlogs` (
  `ExitID` int(11) NOT NULL,
  `PersonID` varchar(20) NOT NULL,
  `PersonType` enum('student','faculty') NOT NULL,
  `Date` date NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ScannerID` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exitlogs`
--

INSERT INTO `exitlogs` (`ExitID`, `PersonID`, `PersonType`, `Date`, `Timestamp`, `ScannerID`) VALUES
(1, '8221363', 'student', '2025-09-23', '2025-09-23 08:16:52', 'SC002'),
(2, '8221183', 'student', '2025-11-14', '2025-11-14 13:05:36', 'SC002'),
(3, '8221183', 'student', '2025-11-14', '2025-11-14 13:47:29', 'SC002'),
(4, '8221183', 'student', '2025-11-14', '2025-11-14 14:40:55', 'SC002'),
(5, '8221183', 'student', '2025-11-19', '2025-11-19 13:15:10', 'SC002'),
(6, '8111111', 'student', '2025-11-19', '2025-11-19 13:19:33', 'SC002'),
(7, '8221183', 'student', '2025-11-19', '2025-11-19 13:32:09', 'SC002'),
(8, 'FAC-003', 'faculty', '2025-11-19', '2025-11-19 13:34:56', 'SC002'),
(9, 'FAC-002', 'faculty', '2025-11-19', '2025-11-19 13:35:06', 'SC002'),
(10, 'FAC-003', 'faculty', '2025-11-19', '2025-11-19 13:39:37', 'SC002');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `FacultyID` varchar(20) NOT NULL,
  `FacultyFName` varchar(50) NOT NULL,
  `FacultyMName` varchar(50) DEFAULT NULL,
  `FacultyLName` varchar(50) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `Birthdate` date NOT NULL,
  `Department` enum('COTE','COED') NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`FacultyID`, `FacultyFName`, `FacultyMName`, `FacultyLName`, `Gender`, `Birthdate`, `Department`, `isActive`, `image`, `created_at`) VALUES
('FAC-002', 'Prof. Kenneth Roi', '', 'Novabos', 'Male', '1975-11-30', 'COTE', 1, NULL, '2025-08-30 01:39:52'),
('FAC-003', 'Dr. Iris', 'Layon', 'Gulbe', 'Female', '1975-11-30', 'COTE', 1, NULL, '2025-08-30 09:54:09');

-- --------------------------------------------------------

--
-- Table structure for table `scanner`
--

CREATE TABLE `scanner` (
  `ScannerID` varchar(20) NOT NULL,
  `Location` varchar(100) NOT NULL,
  `typeofScanner` enum('entrance','exit') NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scanner`
--

INSERT INTO `scanner` (`ScannerID`, `Location`, `typeofScanner`, `isActive`, `created_at`) VALUES
('SC001', 'Main Entrance Gate', 'entrance', 1, '2025-08-30 01:39:52'),
('SC002', 'Main Exit Gate', 'exit', 1, '2025-08-30 01:39:52'),
('SC003', 'Library Entrance', 'entrance', 1, '2025-08-30 01:39:52'),
('SC004', 'Library Exit', 'exit', 1, '2025-08-30 01:39:52');

-- --------------------------------------------------------

--
-- Table structure for table `scan_attempts`
--

CREATE TABLE `scan_attempts` (
  `id` int(11) NOT NULL,
  `scanned_at` datetime NOT NULL,
  `qr_data` text DEFAULT NULL,
  `person_id` varchar(100) DEFAULT NULL,
  `person_type` enum('student','faculty','security','unknown') DEFAULT 'unknown',
  `scanner_id` varchar(64) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'failed',
  `reason` varchar(64) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security`
--

CREATE TABLE `security` (
  `SecurityID` varchar(20) NOT NULL,
  `SecurityFName` varchar(50) NOT NULL,
  `SecurityMName` varchar(50) DEFAULT NULL,
  `SecurityLName` varchar(50) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `BirthDate` date NOT NULL,
  `TimeSched` varchar(50) NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security`
--

INSERT INTO `security` (`SecurityID`, `SecurityFName`, `SecurityMName`, `SecurityLName`, `Gender`, `BirthDate`, `TimeSched`, `isActive`, `image`, `password`, `created_at`) VALUES
('SEC-001', 'Guard. Leonides', '', 'Conde', 'Male', '1985-01-15', '6AM-6PM', 1, NULL, '$2y$10$4DDwNyNmIJRPzhkcGWEDzO3f0ZFX7IYiVoHg1mECRdjIBEjqBkURW', '2025-08-30 01:39:52');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `StudentID` varchar(20) NOT NULL,
  `StudentFName` varchar(50) NOT NULL,
  `StudentMName` varchar(50) DEFAULT NULL,
  `StudentLName` varchar(50) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `BirthDate` date NOT NULL,
  `Course` varchar(100) NOT NULL,
  `YearLvl` int(11) NOT NULL,
  `Section` varchar(10) NOT NULL,
  `Department` enum('COTE','COED') NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `IsEnroll` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=enrolled, 0=not enrolled',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`StudentID`, `StudentFName`, `StudentMName`, `StudentLName`, `Gender`, `BirthDate`, `Course`, `YearLvl`, `Section`, `Department`, `isActive`, `IsEnroll`, `image`, `created_at`) VALUES
('8111111', 'Secret', 'S.', 'Secret', 'Male', '2002-02-19', 'BSED', 4, 'B', 'COED', 1, 1, 'uploads/students/8111111_1763558273.png', '2025-11-19 13:17:54'),
('8220469', 'Siara Lee', 'Sericon', 'Conde', 'Female', '1885-07-23', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:38:31'),
('8220811', 'Reil', '', 'Canete', 'Male', '2003-10-02', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:41:26'),
('8221182', 'Angelica Joy', '', 'Coyoca', 'Female', '2004-01-04', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-08-29 23:12:50'),
('8221183', 'Deonan Leo', 'D.', 'Baslan', 'Male', '2002-10-11', 'BSIT', 4, 'B', 'COTE', 1, 1, 'uploads/students/8221183_1763556870.jpg', '2025-11-14 16:04:51'),
('8221363', 'Joshein', 'Villarin', 'Amag', 'Female', '2003-10-07', 'BSIT', 4, 'B', 'COTE', 1, 1, 'uploads/students/8221363_1763556497.png', '2025-09-20 00:37:39'),
('8221366', 'Nin Kylle', '', 'Valiente', 'Male', '2004-07-19', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:40:48'),
('8221425', 'Judy Ann', 'Panay', 'Diaga', 'Female', '1998-06-20', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:40:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`ArchiveID`),
  ADD KEY `idx_original_user_id` (`OriginalUserID`),
  ADD KEY `idx_user_type` (`OriginalUserType`),
  ADD KEY `idx_archive_reason` (`ArchiveReason`),
  ADD KEY `idx_archive_date` (`ArchiveDate`);

--
-- Indexes for table `entrylogs`
--
ALTER TABLE `entrylogs`
  ADD PRIMARY KEY (`EntryID`),
  ADD KEY `ScannerID` (`ScannerID`);

--
-- Indexes for table `exitlogs`
--
ALTER TABLE `exitlogs`
  ADD PRIMARY KEY (`ExitID`),
  ADD KEY `ScannerID` (`ScannerID`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`FacultyID`);

--
-- Indexes for table `scanner`
--
ALTER TABLE `scanner`
  ADD PRIMARY KEY (`ScannerID`);

--
-- Indexes for table `scan_attempts`
--
ALTER TABLE `scan_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scanned_at` (`scanned_at`),
  ADD KEY `status` (`status`),
  ADD KEY `reason` (`reason`),
  ADD KEY `person_id` (`person_id`);

--
-- Indexes for table `security`
--
ALTER TABLE `security`
  ADD PRIMARY KEY (`SecurityID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`StudentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archive`
--
ALTER TABLE `archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `entrylogs`
--
ALTER TABLE `entrylogs`
  MODIFY `EntryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `exitlogs`
--
ALTER TABLE `exitlogs`
  MODIFY `ExitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `scan_attempts`
--
ALTER TABLE `scan_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `entrylogs`
--
ALTER TABLE `entrylogs`
  ADD CONSTRAINT `entrylogs_ibfk_1` FOREIGN KEY (`ScannerID`) REFERENCES `scanner` (`ScannerID`);

--
-- Constraints for table `exitlogs`
--
ALTER TABLE `exitlogs`
  ADD CONSTRAINT `exitlogs_ibfk_1` FOREIGN KEY (`ScannerID`) REFERENCES `scanner` (`ScannerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

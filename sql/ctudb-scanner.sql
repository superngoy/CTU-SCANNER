-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 03:16 PM
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
  `PersonType` enum('student','faculty','staff') NOT NULL,
  `Date` date NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ScannerID` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entrylogs`
--

INSERT INTO `entrylogs` (`EntryID`, `PersonID`, `PersonType`, `Date`, `Timestamp`, `ScannerID`) VALUES
(1, 'STF-001', 'staff', '2025-11-21', '2025-11-21 03:56:28', 'SC001'),
(2, '8221183', 'student', '2025-11-21', '2025-11-21 03:59:42', 'SC001'),
(3, '999999', 'student', '2025-11-21', '2025-11-20 21:34:14', NULL),
(4, '8111111', 'student', '2025-11-21', '2025-11-20 21:34:14', NULL),
(5, 'INVALID-053447', 'student', '2025-11-21', '2025-11-20 21:34:47', NULL),
(6, '8111111', 'student', '2025-11-21', '2025-11-20 21:34:47', NULL),
(7, 'FAKE-20251121053529', 'student', '2025-11-21', '2025-11-21 04:35:29', NULL),
(8, '8111111', 'student', '2025-11-21', '2025-11-21 04:35:29', NULL),
(9, '8111111', 'student', '2025-11-21', '2025-11-21 04:39:20', NULL),
(10, '8221183', 'student', '2025-11-21', '2025-11-21 05:13:06', 'SC001'),
(11, '8221183', 'student', '2025-11-21', '2025-11-21 05:13:45', 'SC001'),
(12, '8221183', 'student', '2025-11-21', '2025-11-21 05:19:17', 'SC001'),
(13, '8221183', 'student', '2025-11-21', '2025-11-21 05:53:24', 'SC001'),
(14, '8221183', 'student', '2025-11-21', '2025-11-21 07:29:45', 'SC001'),
(15, '8221182', 'student', '2025-11-21', '2025-11-21 08:18:58', 'SC001'),
(16, '8221183', 'student', '2025-11-21', '2025-11-21 13:21:30', 'SC001'),
(17, '8221183', 'student', '2025-11-21', '2025-11-21 13:22:26', 'SC001'),
(18, '8220469', 'student', '2025-11-21', '2025-11-21 13:23:36', 'SC001'),
(19, '8221183', 'student', '2025-11-21', '2025-11-21 13:25:34', 'SC001'),
(20, 'FAC-002', 'faculty', '2025-11-21', '2025-11-21 13:27:29', 'SC001'),
(21, 'FAC-002', 'faculty', '2025-11-21', '2025-11-21 13:27:30', 'SC001'),
(22, 'FAC-003', 'faculty', '2025-11-21', '2025-11-21 13:29:17', 'SC001'),
(23, 'FAC-003', 'faculty', '2025-11-21', '2025-11-21 13:30:18', 'SC001'),
(24, 'FAC-003', 'faculty', '2025-11-21', '2025-11-21 13:30:25', 'SC001'),
(25, 'FAC-002', 'faculty', '2025-11-21', '2025-11-21 13:36:42', 'SC001'),
(26, 'FAC-003', 'faculty', '2025-11-21', '2025-11-21 13:36:47', 'SC001'),
(27, '8220469', 'student', '2025-11-21', '2025-11-21 13:37:58', 'SC001'),
(28, '8220811', 'student', '2025-11-21', '2025-11-21 13:38:15', 'SC001'),
(29, '8220811', 'student', '2025-11-21', '2025-11-21 13:39:06', 'SC001'),
(30, '8221182', 'student', '2025-11-21', '2025-11-21 13:39:39', 'SC001'),
(31, '8221183', 'student', '2025-11-21', '2025-11-21 13:40:39', 'SC001'),
(32, '8221183', 'student', '2025-11-21', '2025-11-21 13:40:58', 'SC001'),
(33, '8221183', 'student', '2025-11-21', '2025-11-21 13:41:00', 'SC001'),
(34, '8221183', 'student', '2025-11-21', '2025-11-21 13:41:12', 'SC001');

-- --------------------------------------------------------

--
-- Table structure for table `exitlogs`
--

CREATE TABLE `exitlogs` (
  `ExitID` int(11) NOT NULL,
  `PersonID` varchar(20) NOT NULL,
  `PersonType` enum('student','faculty','staff') NOT NULL,
  `Date` date NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ScannerID` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exitlogs`
--

INSERT INTO `exitlogs` (`ExitID`, `PersonID`, `PersonType`, `Date`, `Timestamp`, `ScannerID`) VALUES
(1, 'STF-001', 'staff', '2025-11-21', '2025-11-21 03:55:51', 'SC002'),
(2, 'FAC-003', 'faculty', '2025-11-21', '2025-11-21 13:30:46', 'SC002'),
(3, 'FAC-003', 'faculty', '2025-11-21', '2025-11-21 13:30:47', 'SC002'),
(4, 'FAC-002', 'faculty', '2025-11-21', '2025-11-21 13:36:20', 'SC002');

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

--
-- Dumping data for table `scan_attempts`
--

INSERT INTO `scan_attempts` (`id`, `scanned_at`, `qr_data`, `person_id`, `person_type`, `scanner_id`, `location`, `status`, `reason`, `meta`, `ip_address`, `image_path`, `created_at`) VALUES
(1, '2025-11-19 22:54:35', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '::1', NULL, '2025-11-19 14:54:35'),
(2, '2025-11-19 22:55:22', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '::1', NULL, '2025-11-19 14:55:22'),
(3, '2025-11-19 22:59:29', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '::1', NULL, '2025-11-19 14:59:29'),
(4, '2025-11-19 23:03:27', '8111111', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '::1', NULL, '2025-11-19 15:03:27'),
(5, '2025-11-19 23:03:50', '8111111', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '::1', NULL, '2025-11-19 15:03:50'),
(6, '2025-11-19 23:05:37', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'inactive', NULL, '::1', NULL, '2025-11-19 15:05:37'),
(7, '2025-11-19 23:06:16', 'FAC-O10', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '::1', NULL, '2025-11-19 15:06:16'),
(8, '2025-11-19 23:06:39', 'FAC-O10', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '::1', NULL, '2025-11-19 15:06:39'),
(9, '2025-11-19 23:06:57', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'inactive', NULL, '::1', NULL, '2025-11-19 15:06:57'),
(10, '2025-11-19 23:07:19', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '::1', NULL, '2025-11-19 15:07:19'),
(11, '2025-11-19 23:08:03', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'failed', 'inactive', NULL, '::1', NULL, '2025-11-19 15:08:03'),
(12, '2025-11-19 23:08:55', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COED\"}', '::1', NULL, '2025-11-19 15:08:55'),
(13, '2025-11-20 15:01:09', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COED\"}', '192.168.254.105', NULL, '2025-11-20 07:01:09'),
(14, '2025-11-20 15:01:19', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 07:01:19'),
(15, '2025-11-20 15:04:04', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:04'),
(16, '2025-11-20 15:04:11', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:11'),
(17, '2025-11-20 15:04:21', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:21'),
(18, '2025-11-20 15:04:33', '8220469', '8220469', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:33'),
(19, '2025-11-20 15:04:35', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:35'),
(20, '2025-11-20 15:04:38', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COED\"}', '192.168.254.100', NULL, '2025-11-20 07:04:38'),
(21, '2025-11-20 15:04:47', '8221363', '8221363', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:47'),
(22, '2025-11-20 15:04:52', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:52'),
(23, '2025-11-20 15:04:54', '8221366', '8221366', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:54'),
(24, '2025-11-20 15:04:55', '8221366', '8221366', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:55'),
(25, '2025-11-20 15:04:57', '8221425', '8221425', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:04:57'),
(26, '2025-11-20 15:05:52', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:05:52'),
(27, '2025-11-20 15:06:22', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:06:22'),
(28, '2025-11-20 15:06:29', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-20 07:06:29'),
(29, '2025-11-20 17:03:26', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:03:26'),
(30, '2025-11-20 17:13:09', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:13:09'),
(31, '2025-11-20 17:20:00', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:20:00'),
(32, '2025-11-20 17:20:10', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:20:10'),
(33, '2025-11-20 17:20:25', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:20:25'),
(34, '2025-11-20 17:20:37', 'FAC-O10', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.105', NULL, '2025-11-20 09:20:37'),
(35, '2025-11-20 17:27:44', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:27:44'),
(36, '2025-11-20 17:27:54', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:27:54'),
(37, '2025-11-20 17:28:15', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:28:15'),
(38, '2025-11-20 17:36:29', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:36:29'),
(39, '2025-11-20 17:53:44', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:53:44'),
(40, '2025-11-20 17:53:53', 'FAC-O10', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.105', NULL, '2025-11-20 09:53:53'),
(41, '2025-11-20 17:54:00', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.105', NULL, '2025-11-20 09:54:00'),
(42, '2025-11-20 17:54:38', '8111111', '8111111', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COED\"}', '192.168.254.105', NULL, '2025-11-20 09:54:38'),
(43, '2025-11-20 17:54:46', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:54:46'),
(44, '2025-11-20 17:54:54', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:54:54'),
(45, '2025-11-20 17:54:59', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:54:59'),
(46, '2025-11-20 17:55:09', 'FAC-002', 'FAC-002', 'faculty', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:55:09'),
(47, '2025-11-20 17:55:13', 'FAC-003', 'FAC-003', 'faculty', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"COTE\"}', '192.168.254.105', NULL, '2025-11-20 09:55:13'),
(48, '2025-11-21 11:11:52', 'STF-002', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.102', NULL, '2025-11-21 03:11:52'),
(49, '2025-11-21 11:17:31', 'STF-002', 'STF-002', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:17:31'),
(50, '2025-11-21 11:22:55', 'STF-002', 'STF-002', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:22:55'),
(51, '2025-11-21 11:23:55', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.102', NULL, '2025-11-21 03:23:55'),
(52, '2025-11-21 11:26:07', 'STF-002', 'STF-002', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:26:07'),
(53, '2025-11-21 11:30:42', 'STF-001', 'STF-001', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:30:42'),
(54, '2025-11-21 11:35:56', 'STF-002', 'STF-002', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:35:56'),
(55, '2025-11-21 11:40:41', 'STF-002', 'STF-002', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:40:41'),
(56, '2025-11-21 11:41:02', 'STF-001', 'STF-001', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:41:02'),
(57, '2025-11-21 11:41:21', 'STF-001', 'STF-001', '', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:41:21'),
(58, '2025-11-21 11:55:51', 'STF-001', 'STF-001', '', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:55:51'),
(59, '2025-11-21 11:56:28', 'STF-001', 'STF-001', '', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"Support\"}', '192.168.254.102', NULL, '2025-11-21 03:56:28'),
(60, '2025-11-21 11:59:42', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.102', NULL, '2025-11-21 03:59:42'),
(61, '2025-11-21 12:31:20', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:31:20'),
(62, '2025-11-21 12:32:06', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:32:06'),
(63, '2025-11-21 12:37:46', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:37:46'),
(64, '2025-11-21 12:41:33', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:41:33'),
(65, '2025-11-21 12:43:32', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:43:32'),
(66, '2025-11-21 12:43:59', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:43:59'),
(67, '2025-11-21 12:46:42', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:46:42'),
(68, '2025-11-21 12:47:16', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:47:16'),
(69, '2025-11-21 12:49:54', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 04:49:54'),
(70, '2025-11-21 13:12:49', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.102', NULL, '2025-11-21 05:12:49'),
(71, '2025-11-21 13:13:06', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.102', NULL, '2025-11-21 05:13:06'),
(72, '2025-11-21 13:13:45', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.102', NULL, '2025-11-21 05:13:45'),
(73, '2025-11-21 13:19:17', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.102', NULL, '2025-11-21 05:19:17'),
(74, '2025-11-21 13:53:24', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.102', NULL, '2025-11-21 05:53:24'),
(75, '2025-11-21 15:29:45', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '10.246.62.163', NULL, '2025-11-21 07:29:45'),
(76, '2025-11-21 16:18:58', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '10.246.62.197', NULL, '2025-11-21 08:18:58'),
(77, '2025-11-21 16:28:52', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '10.246.62.215', NULL, '2025-11-21 08:28:52'),
(78, '2025-11-21 21:21:30', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:21:30'),
(79, '2025-11-21 21:21:31', '82211', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:21:31'),
(80, '2025-11-21 21:22:21', 'B221183', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:22:21'),
(81, '2025-11-21 21:22:26', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:22:26'),
(82, '2025-11-21 21:23:15', 'L220469', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:23:15'),
(83, '2025-11-21 21:23:20', 'B220469', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:23:20'),
(84, '2025-11-21 21:23:36', '8220469', '8220469', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:23:36'),
(85, '2025-11-21 21:23:45', '822081', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:23:45'),
(86, '2025-11-21 21:25:29', '822183', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:25:29'),
(87, '2025-11-21 21:25:30', 'VF1.', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:25:30'),
(88, '2025-11-21 21:25:33', '822183', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:25:33'),
(89, '2025-11-21 21:25:34', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:25:34'),
(90, '2025-11-21 21:25:35', '822183', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:25:35'),
(91, '2025-11-21 21:26:09', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:09'),
(92, '2025-11-21 21:26:10', '822182', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:10'),
(93, '2025-11-21 21:26:11', '822182', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:11'),
(94, '2025-11-21 21:26:12', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:12'),
(95, '2025-11-21 21:26:13', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:13'),
(96, '2025-11-21 21:26:14', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:14'),
(97, '2025-11-21 21:26:15', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.100', NULL, '2025-11-21 13:26:15'),
(98, '2025-11-21 21:27:29', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:27:29'),
(99, '2025-11-21 21:27:30', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:27:30'),
(100, '2025-11-21 21:28:30', 'F1C-002', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:28:30'),
(101, '2025-11-21 21:29:13', 'FBC-003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:29:13'),
(102, '2025-11-21 21:29:17', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:29:17'),
(103, '2025-11-21 21:30:09', 'F1C-003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:09'),
(104, '2025-11-21 21:30:14', '177003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:14'),
(105, '2025-11-21 21:30:15', '077003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:15'),
(106, '2025-11-21 21:30:17', 'F1C-003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:17'),
(107, '2025-11-21 21:30:18', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:30:18'),
(108, '2025-11-21 21:30:20', '277003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:20'),
(109, '2025-11-21 21:30:22', '277003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:22'),
(110, '2025-11-21 21:30:23', '277003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:23'),
(111, '2025-11-21 21:30:25', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:30:25'),
(112, '2025-11-21 21:30:27', 'TBC-003', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:30:27'),
(113, '2025-11-21 21:30:46', 'FAC-003', 'FAC-003', 'faculty', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:30:46'),
(114, '2025-11-21 21:30:47', 'FAC-003', 'FAC-003', 'faculty', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:30:47'),
(115, '2025-11-21 21:36:20', 'FAC-002', 'FAC-002', 'faculty', 'SC002', 'Main Exit Gate', 'success', NULL, '{\"action\":\"Exit\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:36:20'),
(116, '2025-11-21 21:36:42', 'FAC-002', 'FAC-002', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:36:42'),
(117, '2025-11-21 21:36:47', 'FAC-003', 'FAC-003', 'faculty', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:36:47'),
(118, '2025-11-21 21:37:58', '8220469', '8220469', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:37:58'),
(119, '2025-11-21 21:38:15', '8220811', '8220811', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:38:15'),
(120, '2025-11-21 21:38:28', '8221882', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:38:28'),
(121, '2025-11-21 21:39:00', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'failed', 'not_enrolled', NULL, '192.168.254.100', NULL, '2025-11-21 13:39:00'),
(122, '2025-11-21 21:39:06', '8220811', '8220811', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:39:06'),
(123, '2025-11-21 21:39:39', '8221182', '8221182', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:39:39'),
(124, '2025-11-21 21:40:23', '8921783', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:40:23'),
(125, '2025-11-21 21:40:30', '8221113', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:40:30'),
(126, '2025-11-21 21:40:36', '822183', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:40:36'),
(127, '2025-11-21 21:40:39', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:40:39'),
(128, '2025-11-21 21:40:46', '822113', NULL, 'unknown', 'SC001', 'Main Entrance Gate', 'failed', 'invalid_qr', NULL, '192.168.254.100', NULL, '2025-11-21 13:40:46'),
(129, '2025-11-21 21:40:58', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:40:58'),
(130, '2025-11-21 21:41:00', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:41:00'),
(131, '2025-11-21 21:41:13', '8221183', '8221183', 'student', 'SC001', 'Main Entrance Gate', 'success', NULL, '{\"action\":\"Entry\",\"department\":\"COTE\"}', '192.168.254.100', NULL, '2025-11-21 13:41:13');

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
('SEC-001', 'Guard. Leonides', '', 'Conde', 'Male', '1985-01-15', '6AM-6PM', 1, NULL, '$2y$10$7Xwv1GsQrQ/FNtezy0sFIOIyP/LwdOScFVPOnV5/3HfBJFeHXD24G', '2025-11-20 11:38:40'),
('SEC-002', 'Secret', 'S.', 'Secret', 'Female', '2025-11-15', '10:00am - 10:00pm', 1, 'uploads/security/SEC-002_1763637877.png', '$2y$10$NbbQHjcq5yk02XB8ukbTpewo4eeMcKeVG430xjLK5NXL0j3aZP/LS', '2025-11-20 11:24:38');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` varchar(20) NOT NULL,
  `StaffFName` varchar(50) NOT NULL,
  `StaffMName` varchar(50) DEFAULT NULL,
  `StaffLName` varchar(50) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `BirthDate` date NOT NULL,
  `Position` varchar(100) NOT NULL,
  `Department` enum('COTE','COED','Admin','Support') NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `StaffFName`, `StaffMName`, `StaffLName`, `Gender`, `BirthDate`, `Position`, `Department`, `isActive`, `image`, `created_at`, `updated_at`) VALUES
('STF-001', 'Secret', 'S.', 'Secret', 'Female', '2025-11-13', 'Secret', 'Support', 1, 'uploads/staff/STF-001_1763695757.jpg', '2025-11-21 03:29:17', '2025-11-21 03:29:17'),
('STF-002', 'Liza', 'S.', 'Sobirano', 'Female', '2025-11-05', 'Secret', 'Support', 1, 'uploads/staff/STF-002_1763641107.png', '2025-11-20 12:19:41', '2025-11-20 12:19:41');

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
('8111111', 'Secret', 'S.', 'Secret', 'Male', '2002-02-19', 'BSED', 4, 'B', 'COED', 0, 0, 'uploads/students/8111111_1763627153.png', '2025-11-20 11:05:49'),
('8220469', 'Siara Lee', 'Sericon', 'Conde', 'Female', '1885-07-23', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:38:31'),
('8220811', 'Reil', '', 'Canete', 'Male', '2003-10-02', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:41:26'),
('8221182', 'Angelica Joy', '', 'Coyoca', 'Female', '2004-01-04', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-08-29 23:12:50'),
('8221183', 'Deonan Leo', 'D.', 'Baslan', 'Male', '2002-10-11', 'BSIT', 4, 'B', 'COTE', 1, 1, 'uploads/students/8221183_1763556870.jpg', '2025-11-14 16:04:51'),
('8221363', 'Joshein', 'Villarin', 'Amag', 'Female', '2003-10-07', 'BSIT', 4, 'B', 'COTE', 1, 1, 'uploads/students/8221363_1763556497.png', '2025-09-20 00:37:39'),
('8221366', 'Nin Kylle', '', 'Valiente', 'Male', '2004-07-19', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:40:48'),
('8221425', 'Judy Ann', 'Panay', 'Diaga', 'Female', '1998-06-20', 'BSIT', 4, 'B', 'COTE', 1, 1, NULL, '2025-09-20 00:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `visitor_code` varchar(64) DEFAULT NULL,
  `first_name` varchar(128) DEFAULT NULL,
  `middle_name` varchar(128) DEFAULT NULL,
  `last_name` varchar(128) DEFAULT NULL,
  `contact_number` varchar(32) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `id_provided_type` varchar(128) DEFAULT NULL,
  `id_provided_number` varchar(128) DEFAULT NULL,
  `id_image` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `last_visit` datetime DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `visitor_code`, `first_name`, `middle_name`, `last_name`, `contact_number`, `email`, `company`, `purpose`, `id_provided_type`, `id_provided_number`, `id_image`, `image`, `notes`, `isActive`, `last_visit`, `meta`, `created_at`, `updated_at`) VALUES
(1, 'V202511201116354897', 'Secret', 'S.', 'Secret', '09616345555', 'admin@ctu.edu.ph', 'N/A', 'Mo kaog iro', NULL, NULL, NULL, 'uploads/visitors/V202511201116354897_1763633795.jpg', NULL, 1, NULL, NULL, '2025-11-20 10:16:35', NULL),
(2, 'V202511201202519663', 'Deonan Leo', 'D.', 'Baslan', '0991234567', 'accooount444@gmail.com', 'Secret', 'Mag boxing', NULL, NULL, NULL, 'uploads/visitors/V202511201202519663_1763636571.jpg', NULL, 1, NULL, NULL, '2025-11-20 11:02:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_logs`
--

CREATE TABLE `visitor_logs` (
  `id` int(11) NOT NULL,
  `visitor_id` int(11) NOT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visitor_logs`
--

INSERT INTO `visitor_logs` (`id`, `visitor_id`, `check_in_time`, `check_out_time`, `location`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-11-20 18:19:26', '2025-11-20 18:50:21', 'Main Entrance', NULL, '2025-11-20 10:19:26', '2025-11-20 10:50:21'),
(2, 1, '2025-11-20 18:50:10', '2025-11-20 18:50:15', 'Main Entrance', NULL, '2025-11-20 10:50:10', '2025-11-20 10:50:15'),
(3, 2, '2025-11-20 19:02:56', NULL, 'Main Entrance', NULL, '2025-11-20 11:02:56', NULL);

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
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`StudentID`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `visitor_code_idx` (`visitor_code`),
  ADD KEY `last_visit_idx` (`last_visit`);

--
-- Indexes for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `check_in_idx` (`check_in_time`),
  ADD KEY `visitor_idx` (`visitor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archive`
--
ALTER TABLE `archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `entrylogs`
--
ALTER TABLE `entrylogs`
  MODIFY `EntryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `exitlogs`
--
ALTER TABLE `exitlogs`
  MODIFY `ExitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `scan_attempts`
--
ALTER TABLE `scan_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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

--
-- Constraints for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD CONSTRAINT `visitor_logs_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

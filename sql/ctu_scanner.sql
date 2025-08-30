-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 10:21 AM
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
(1, '2024-001', 'student', '2025-08-30', '2025-08-30 07:06:25', 'SC001'),
(2, '8221183', 'student', '2025-08-30', '2025-08-30 07:14:19', 'SC001'),
(3, '8221182', 'student', '2025-08-30', '2025-08-30 07:14:53', 'SC001'),
(4, 'FAC-002', 'faculty', '2025-08-30', '2025-08-30 07:15:43', 'SC001'),
(5, '2024-001', 'student', '2025-08-30', '2025-08-30 07:23:08', 'SC001'),
(6, 'FAC-002', 'faculty', '2025-08-30', '2025-08-30 07:23:27', 'SC001'),
(7, '8221182', 'student', '2025-08-30', '2025-08-30 07:23:32', 'SC001'),
(8, '8221183', 'student', '2025-08-30', '2025-08-30 07:23:35', 'SC001'),
(9, '8221183', 'student', '2025-08-30', '2025-08-30 07:43:06', 'SC001'),
(10, '8221182', 'student', '2025-08-30', '2025-08-30 07:43:10', 'SC001'),
(11, 'FAC-002', 'faculty', '2025-08-30', '2025-08-30 07:43:14', 'SC001'),
(12, 'FAC-002', 'faculty', '2025-08-30', '2025-08-30 07:44:29', 'SC001'),
(13, '2024-001', 'student', '2025-08-30', '2025-08-30 08:18:37', 'SC001'),
(14, '2024-001', 'student', '2025-08-30', '2025-08-30 08:18:42', 'SC001'),
(15, '2024-001', 'student', '2025-08-30', '2025-08-30 08:19:16', 'SC001');

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
(1, '2024-001', 'student', '2025-08-30', '2025-08-30 07:07:16', 'SC002'),
(2, '8221183', 'student', '2025-08-30', '2025-08-30 07:13:59', 'SC002');

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
('FAC-001', 'Dr. Ana', 'Lopez', 'Rodriguez', 'Female', '1980-08-25', 'COTE', 1, NULL, '2025-08-30 01:39:52'),
('FAC-002', 'Prof. Carlos', 'Miguel', 'Torres', 'Male', '1975-11-30', 'COED', 1, NULL, '2025-08-30 01:39:52');

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
('SEC-001', 'Guard', 'One', 'Security', 'Male', '1985-01-15', '6AM-6PM', 1, NULL, '$2y$10$4DDwNyNmIJRPzhkcGWEDzO3f0ZFX7IYiVoHg1mECRdjIBEjqBkURW', '2025-08-30 01:39:52');

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
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`StudentID`, `StudentFName`, `StudentMName`, `StudentLName`, `Gender`, `BirthDate`, `Course`, `YearLvl`, `Section`, `Department`, `isActive`, `image`, `created_at`) VALUES
('2024-001', 'Juan', 'Cruz', 'Santos', 'Male', '2000-05-15', 'Computer Engineering', 3, 'A', 'COTE', 1, NULL, '2025-08-30 01:39:52'),
('2024-002', 'Maria', 'Dela', 'Cruz', 'Female', '2001-03-20', 'Information Technology', 2, 'B', 'COTE', 1, NULL, '2025-08-30 01:39:52'),
('2024-003', 'Pedro', 'Garcia', 'Reyes', 'Male', '1999-12-10', 'Education', 4, 'A', 'COED', 1, NULL, '2025-08-30 01:39:52'),
('8221181', 'Judy Ann', '', 'Diaga', 'Female', '1999-12-10', 'Information Technology', 4, 'B', 'COTE', 1, NULL, '2025-08-30 07:12:50'),
('8221182', 'Angelica Joy', '', 'Coyoca', 'Female', '2004-01-04', 'Information Technology', 4, 'B', 'COTE', 1, NULL, '2025-08-30 07:12:50'),
('8221183', 'Deonan Leo', 'Despacio', 'Baslan', 'Male', '2002-10-11', 'Information Technology', 4, 'B', 'COTE', 1, NULL, '2025-08-30 07:12:50');

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
-- AUTO_INCREMENT for table `entrylogs`
--
ALTER TABLE `entrylogs`
  MODIFY `EntryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `exitlogs`
--
ALTER TABLE `exitlogs`
  MODIFY `ExitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

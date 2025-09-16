-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2025 at 12:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `enrolled_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`, `year`, `enrolled_date`, `status`) VALUES
(1, 1, 1, 2020, '2020-01-01', 'active'),
(2, 1, 2, 2020, '2020-01-01', 'active'),
(3, 1, 3, 2020, '2020-01-01', 'active'),
(4, 1, 4, 2020, '2020-01-01', 'active'),
(5, 1, 5, 2020, '2020-01-01', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `grade_number` int(11) NOT NULL,
  `class_name` varchar(10) NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `grade_number`, `class_name`, `year`, `status`) VALUES
(1, 1, 'A', 2020, 'active'),
(2, 2, 'A', 2020, 'active'),
(3, 3, 'A', 2020, 'active'),
(4, 4, 'A', 2020, 'active'),
(5, 5, 'A', 2020, 'active'),
(6, 6, 'A', 2020, 'active'),
(7, 7, 'A', 2020, 'active'),
(8, 8, 'A', 2020, 'active'),
(9, 9, 'A', 2020, 'active'),
(10, 10, 'A', 2020, 'active'),
(11, 11, 'A', 2020, 'active'),
(12, 12, 'A', 2020, 'active'),
(13, 13, 'A', 2020, 'active'),
(14, 1, 'A', 2021, 'active'),
(15, 2, 'A', 2021, 'active'),
(16, 3, 'A', 2021, 'active'),
(17, 4, 'A', 2021, 'active'),
(18, 5, 'A', 2021, 'active'),
(19, 6, 'A', 2021, 'active'),
(20, 7, 'A', 2021, 'active'),
(21, 8, 'A', 2021, 'active'),
(22, 9, 'A', 2021, 'active'),
(23, 10, 'A', 2021, 'active'),
(24, 11, 'A', 2021, 'active'),
(25, 12, 'A', 2021, 'active'),
(26, 13, 'A', 2021, 'active'),
(27, 1, 'A', 2022, 'active'),
(28, 2, 'A', 2022, 'active'),
(29, 3, 'A', 2022, 'active'),
(30, 4, 'A', 2022, 'active'),
(31, 5, 'A', 2022, 'active'),
(32, 6, 'A', 2022, 'active'),
(33, 7, 'A', 2022, 'active'),
(34, 8, 'A', 2022, 'active'),
(35, 9, 'A', 2022, 'active'),
(36, 10, 'A', 2022, 'active'),
(37, 11, 'A', 2022, 'active'),
(38, 12, 'A', 2022, 'active'),
(39, 13, 'A', 2022, 'active'),
(40, 1, 'A', 2023, 'active'),
(41, 2, 'A', 2023, 'active'),
(42, 3, 'A', 2023, 'active'),
(43, 4, 'A', 2023, 'active'),
(44, 5, 'A', 2023, 'active'),
(45, 6, 'A', 2023, 'active'),
(46, 7, 'A', 2023, 'active'),
(47, 8, 'A', 2023, 'active'),
(48, 9, 'A', 2023, 'active'),
(49, 10, 'A', 2023, 'active'),
(50, 11, 'A', 2023, 'active'),
(51, 12, 'A', 2023, 'active'),
(52, 13, 'A', 2023, 'active'),
(53, 1, 'A', 2024, 'active'),
(54, 2, 'A', 2024, 'active'),
(55, 3, 'A', 2024, 'active'),
(56, 4, 'A', 2024, 'active'),
(57, 5, 'A', 2024, 'active'),
(58, 6, 'A', 2024, 'active'),
(59, 7, 'A', 2024, 'active'),
(60, 8, 'A', 2024, 'active'),
(61, 9, 'A', 2024, 'active'),
(62, 10, 'A', 2024, 'active'),
(63, 11, 'A', 2024, 'active'),
(64, 12, 'A', 2024, 'active'),
(65, 13, 'A', 2024, 'active'),
(66, 1, 'A', 2025, 'active'),
(67, 2, 'A', 2025, 'active'),
(68, 3, 'A', 2025, 'active'),
(69, 4, 'A', 2025, 'active'),
(70, 5, 'A', 2025, 'active'),
(71, 6, 'A', 2025, 'active'),
(72, 7, 'A', 2025, 'active'),
(73, 8, 'A', 2025, 'active'),
(74, 9, 'A', 2025, 'active'),
(75, 10, 'A', 2025, 'active'),
(76, 11, 'A', 2025, 'active'),
(77, 12, 'A', 2025, 'active'),
(78, 13, 'A', 2025, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `term` enum('1st term','2nd term','3rd term') NOT NULL,
  `mark` decimal(5,2) NOT NULL,
  `grade_letter` char(1) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`id`, `student_id`, `subject_id`, `grade_id`, `year`, `term`, `mark`, `grade_letter`, `remarks`, `updated_at`) VALUES
(1, 1, 1, 1, 2020, '1st term', 85.50, NULL, 'Excellent performance', '2025-08-23 17:24:55'),
(2, 1, 1, 1, 2020, '2nd term', 88.00, NULL, 'Very good work', '2025-08-23 17:24:55'),
(3, 1, 1, 1, 2020, '3rd term', 90.50, NULL, 'Outstanding achievement', '2025-08-23 17:24:55'),
(4, 1, 2, 1, 2020, '1st term', 78.00, NULL, 'Good progress', '2025-08-23 17:24:55'),
(5, 1, 2, 1, 2020, '2nd term', 82.50, NULL, 'Improving well', '2025-08-23 17:24:55'),
(6, 1, 2, 1, 2020, '3rd term', 85.00, NULL, 'Excellent improvement', '2025-08-23 17:24:55');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `birth_date` date NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(15) DEFAULT NULL,
  `guardian_email` varchar(100) DEFAULT NULL,
  `special_details` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `full_name`, `gender`, `birth_date`, `address`, `phone`, `email`, `religion`, `father_name`, `mother_name`, `guardian_name`, `guardian_phone`, `guardian_email`, `special_details`, `status`, `image_path`, `created_at`) VALUES
(1, 'Nimal Perera', 'male', '2005-01-15', 'No. 123, Main Street, Colombo', '0712345678', 'nimal.perera@email.com', 'Buddhism', 'Sunil Perera', 'Kumari Perera', 'Sunil Perera', '0712345678', 'sunil@email.com', NULL, 'active', NULL, '2025-08-23 17:24:54'),
(2, 'Samantha Silva', 'female', '2005-03-22', 'No. 456, Lake Road, Kandy', '0723456789', 'samantha.silva@email.com', 'Christianity', 'Robert Silva', 'Mary Silva', 'Robert Silva', '0723456789', 'robert@email.com', NULL, 'active', NULL, '2025-08-23 17:24:54'),
(3, 'Dilshan Fernando', 'male', '2005-06-10', 'No. 789, Hill Street, Galle', '0734567890', 'dilshan.fernando@email.com', 'Buddhism', 'Ajith Fernando', 'Nayana Fernando', 'Ajith Fernando', '0734567890', 'ajith@email.com', NULL, 'active', NULL, '2025-08-23 17:24:54'),
(4, 'Anjali Wijesekara', 'female', '2005-08-05', 'No. 321, Beach Road, Negombo', '0745678901', 'anjali.wijesekara@email.com', 'Hinduism', 'Priya Wijesekara', 'Lakshmi Wijesekara', 'Priya Wijesekara', '0745678901', 'priya@email.com', NULL, 'active', NULL, '2025-08-23 17:24:54'),
(5, 'Kavindu Rajapaksa', 'male', '2005-11-18', 'No. 654, Temple Road, Anuradhapura', '0756789012', 'kavindu.rajapaksa@email.com', 'Buddhism', 'Mahinda Rajapaksa', 'Shiranthi Rajapaksa', 'Mahinda Rajapaksa', '0756789012', 'mahinda@email.com', NULL, 'active', NULL, '2025-08-23 17:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `enrolled_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`id`, `student_id`, `grade_id`, `enrolled_date`, `status`) VALUES
(1, 1, 1, '2020-01-01', 'active'),
(2, 1, 14, '2021-01-01', 'active'),
(3, 1, 27, '2022-01-01', 'active'),
(4, 1, 40, '2023-01-01', 'active'),
(5, 1, 53, '2024-01-01', 'active'),
(6, 1, 66, '2025-01-01', 'active'),
(7, 2, 1, '2020-01-01', 'active'),
(8, 2, 14, '2021-01-01', 'active'),
(9, 2, 27, '2022-01-01', 'active'),
(10, 2, 40, '2023-01-01', 'active'),
(11, 2, 53, '2024-01-01', 'active'),
(12, 2, 66, '2025-01-01', 'active'),
(13, 3, 1, '2020-01-01', 'active'),
(14, 3, 14, '2021-01-01', 'active'),
(15, 3, 27, '2022-01-01', 'active'),
(16, 3, 40, '2023-01-01', 'active'),
(17, 3, 53, '2024-01-01', 'active'),
(18, 3, 66, '2025-01-01', 'active'),
(19, 4, 1, '2020-01-01', 'active'),
(20, 4, 14, '2021-01-01', 'active'),
(21, 4, 27, '2022-01-01', 'active'),
(22, 4, 40, '2023-01-01', 'active'),
(23, 4, 53, '2024-01-01', 'active'),
(24, 4, 66, '2025-01-01', 'active'),
(25, 5, 1, '2020-01-01', 'active'),
(26, 5, 14, '2021-01-01', 'active'),
(27, 5, 27, '2022-01-01', 'active'),
(28, 5, 40, '2023-01-01', 'active'),
(29, 5, 53, '2024-01-01', 'active'),
(30, 5, 66, '2025-01-01', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `status`) VALUES
(1, 'Sinhala', 'Sinhala language and literature', 'active'),
(2, 'English', 'English language and literature', 'active'),
(3, 'Mathematics', 'Core mathematics', 'active'),
(4, 'Environment', 'Environmental studies for primary grades', 'active'),
(5, 'Religion', 'Religious studies (Buddhism, Christianity, etc.)', 'active'),
(6, 'Science', 'General science', 'active'),
(7, 'History', 'Sri Lankan and world history', 'active'),
(8, 'Geography', 'Geography studies', 'active'),
(9, 'Civics', 'Civics and governance', 'active'),
(10, 'Tamil', 'Tamil language', 'active'),
(11, 'PTS', 'Practical and Technical Skills', 'active'),
(12, 'ICT', 'Information and Communication Technology', 'active'),
(13, 'Health', 'Health and physical education', 'active'),
(14, 'Dance', 'Traditional and modern dance', 'active'),
(15, 'Art', 'Visual arts', 'active'),
(16, 'Music', 'Music studies', 'active'),
(17, 'English Literature', 'Advanced English literature', 'active'),
(18, 'Commerce', 'Commerce studies', 'active'),
(19, 'Politics', 'Political science for Arts stream', 'active'),
(20, 'Economics', 'Economics for Commerce stream', 'active'),
(21, 'Accounting', 'Accounting for Commerce stream', 'active'),
(22, 'Chemistry', 'Chemistry for Science stream', 'active'),
(23, 'Physics', 'Physics for Science stream', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('admin','teacher') NOT NULL DEFAULT 'teacher',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `status`, `created_at`) VALUES
(2, 'Teacher1', '$2y$10$HZEu44upDasp4e77hWIXeOj6DtVFQutkE8Pkr.6KgUzSfz8pqtOy6', 'Teacher1', 'teacher1@gmail.com', '12345', 'teacher', 'active', '2025-08-24 09:59:35'),
(3, 'admin1', '$2y$10$t7NgSN/41tXVAVMQA/uPke3CmEyqPj0qXfAuBDf.eECPyjdFme1XG', 'Schoolsync Administrator', 'Administrator@schoolsync.com', '', 'admin', 'active', '2025-08-24 10:35:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`subject_id`,`year`),
  ADD KEY `idx_enrollments_student` (`student_id`),
  ADD KEY `idx_enrollments_subject` (`subject_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`grade_number`,`class_name`,`year`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_marks_student` (`student_id`),
  ADD KEY `idx_marks_subject` (`subject_id`),
  ADD KEY `idx_marks_grade` (`grade_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_grades_student` (`student_id`),
  ADD KEY `idx_student_grades_grade` (`grade_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `marks_ibfk_3` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`);

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 15, 2026 at 07:05 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `helpdesk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL COMMENT 'FK -> tickets.id',
  `comment_id` int(11) DEFAULT NULL COMMENT 'FK -> comments.id (optional)',
  `file_name` varchar(255) NOT NULL COMMENT 'Original file name',
  `file_path` varchar(500) NOT NULL COMMENT 'Server storage path',
  `uploaded_by` int(11) NOT NULL COMMENT 'FK -> users.id',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Technical Issue', 'Hardware, software, and network problems', '2026-04-15 23:04:51'),
(2, 'Account Access', 'Login issues, password resets, account lockouts', '2026-04-15 23:04:51'),
(3, 'Course Registration', 'Enrollment, section changes, prerequisite overrides', '2026-04-15 23:04:51'),
(4, 'Billing & Payment', 'Tuition fees, payment gateway, refunds', '2026-04-15 23:04:51'),
(5, 'General Inquiry', 'Campus info, policies, and other questions', '2026-04-15 23:04:51'),
(6, 'Lab & Equipment', 'Lab access, equipment booking, borrowed items', '2026-04-15 23:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL COMMENT 'FK -> tickets.id',
  `user_id` int(11) NOT NULL COMMENT 'FK -> users.id  (author)',
  `body` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = staff-only internal note',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `ticket_id`, `user_id`, `body`, `is_internal`, `created_at`) VALUES
(1, 1, 1, 'We are aware of the WiFi outage in Building 7. The network team is investigating. Will update once we have an ETA.', 0, '2026-04-15 22:04:51'),
(2, 1, 1, 'Root cause: a firmware update on the access points failed overnight. Rolling back now.', 1, '2026-04-15 22:19:51'),
(3, 2, 2, 'Hi Tasnim, this is a known sync delay between the central directory and RDS. I have manually triggered a sync. Please try again in 15 minutes.', 0, '2026-04-15 03:04:51'),
(4, 2, 8, 'Thank you! It works now.', 0, '2026-04-15 05:04:51'),
(5, 4, 1, 'Escalated to the finance department with reference #FIN-2026-0412. They will process the reversal within 3-5 business days.', 0, '2026-04-11 23:04:51'),
(6, 4, 10, 'Thanks for the quick response. I will check my bank statement next week.', 0, '2026-04-12 23:04:51'),
(7, 5, 3, 'Inspected the projector. The HDMI port on the projector side was damaged. Replaced with a new unit from inventory.', 0, '2026-04-09 23:04:51'),
(8, 5, 3, 'Replacement projector model: Epson EB-X51. Asset tag: NSU-PROJ-0087.', 1, '2026-04-09 23:04:51'),
(9, 5, 11, 'Projector is working perfectly now. Thank you!', 0, '2026-04-10 23:04:51'),
(10, 8, 1, 'Contacted MathWorks support. Our campus-wide license renewal was delayed in processing. They are expediting it.', 0, '2026-04-15 19:04:51'),
(11, 8, 1, 'MathWorks ticket ref: MW-2026-88421. Expected resolution: within 24 hours.', 1, '2026-04-15 19:04:51'),
(12, 10, 3, 'Performed a factory reset and full recalibration of the smartboard. The touch alignment is now accurate. Tested with both pen and finger input.', 0, '2026-04-07 23:04:51'),
(13, 10, 4, 'Working great now. Appreciate the quick fix!', 0, '2026-04-08 23:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'FK -> users.id  (ticket creator)',
  `assigned_staff_id` int(11) DEFAULT NULL COMMENT 'FK -> users.id  (assigned staff)',
  `category_id` int(11) DEFAULT NULL COMMENT 'FK -> categories.id',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `assigned_staff_id`, `category_id`, `subject`, `description`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 1, 'WiFi not connecting in Building 7', 'I have been unable to connect to NSU-WiFi from any device since this morning. Other students nearby are also affected. The network shows up but authentication fails every time.', 'high', 'open', '2026-04-15 21:04:51', '2026-04-15 23:04:51'),
(2, 8, 2, 2, 'Cannot access RDS portal after password change', 'I changed my NSU password yesterday and now I get \"Invalid Credentials\" when logging into the RDS student portal. My email login works fine.', 'medium', 'in_progress', '2026-04-14 23:04:51', '2026-04-15 23:04:51'),
(3, 9, NULL, 3, 'Prerequisite override request for CSE311', 'I completed CSE221 at another university before transfer. My transcript has been submitted but the system still blocks CSE311 enrollment. Requesting manual override.', 'medium', 'open', '2026-04-12 23:04:51', '2026-04-15 23:04:51'),
(4, 10, 1, 4, 'Duplicate tuition charge on my account', 'My spring 2026 tuition was charged twice on April 1st. Total overcharge is BDT 52,000. I have the bank statement to prove this. Please issue a reversal.', 'critical', 'in_progress', '2026-04-10 23:04:51', '2026-04-15 23:04:51'),
(5, 11, 3, 1, 'Projector not working in Room NAC-401', 'The ceiling-mounted projector in NAC-401 shows no signal regardless of which laptop is connected. The HDMI cable was also tested with a different projector.', 'high', 'resolved', '2026-04-08 23:04:51', '2026-04-15 23:04:51'),
(6, 12, NULL, 5, 'Parking pass renewal process', 'What is the process to renew my campus parking pass for the summer semester? The admin office told me to submit a ticket.', 'low', 'open', '2026-04-11 23:04:51', '2026-04-15 23:04:51'),
(7, 7, 2, 6, 'Need access to Robotics Lab on weekends', 'Our senior project group needs access to the Robotics Lab (SAC-901) on Saturdays from 10am to 4pm for the next 6 weeks. Faculty advisor Dr. Tanvir Alam has approved.', 'medium', 'open', '2026-04-14 23:04:51', '2026-04-15 23:04:51'),
(8, 13, 1, 1, 'MATLAB license expired on lab computers', 'All 30 workstations in the CSE lab show \"License expired\" when launching MATLAB R2025b. This is affecting multiple courses.', 'critical', 'in_progress', '2026-04-15 17:04:51', '2026-04-15 23:04:51'),
(9, 14, NULL, 2, 'Email forwarding not working', 'I set up email forwarding from my NSU account to my Gmail two weeks ago, but emails are not being forwarded. I verified the forwarding address is correct.', 'low', 'open', '2026-04-13 23:04:51', '2026-04-15 23:04:51'),
(10, 4, 3, 1, 'Smartboard calibration off in SAC-201', 'The interactive smartboard in SAC-201 has significant touch offset - pointer appears about 3cm to the right of where I actually touch. Recalibration via the on-screen tool does not persist.', 'medium', 'resolved', '2026-04-05 23:04:51', '2026-04-15 23:04:51'),
(11, 5, 1, 3, 'Cannot add TA to course section in RDS', 'The \"Add Teaching Assistant\" option is greyed out for my CSE482 section. I need to assign two TAs before the semester starts next week.', 'high', 'in_progress', '2026-04-12 23:04:51', '2026-04-15 23:04:51'),
(12, 6, NULL, 5, 'Request for guest WiFi account for visiting researcher', 'Dr. Sarah Chen from MIT is visiting our department April 20-25. She will need campus WiFi access for the duration. What is the process to arrange a guest network account?', 'medium', 'open', '2026-04-15 11:04:51', '2026-04-15 23:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_history`
--

CREATE TABLE `ticket_history` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL COMMENT 'FK -> tickets.id',
  `changed_by` int(11) NOT NULL COMMENT 'FK -> users.id',
  `field_changed` varchar(50) NOT NULL COMMENT 'Column name that was modified',
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_history`
--

INSERT INTO `ticket_history` (`id`, `ticket_id`, `changed_by`, `field_changed`, `old_value`, `new_value`, `created_at`) VALUES
(1, 2, 2, 'status', 'open', 'in_progress', '2026-04-15 03:04:51'),
(2, 4, 1, 'assigned_staff_id', 'none', '1', '2026-04-10 23:04:51'),
(3, 4, 1, 'status', 'open', 'in_progress', '2026-04-10 23:04:51'),
(4, 5, 3, 'assigned_staff_id', 'none', '3', '2026-04-08 23:04:51'),
(5, 5, 3, 'status', 'open', 'in_progress', '2026-04-08 23:04:51'),
(6, 5, 3, 'status', 'in_progress', 'resolved', '2026-04-09 23:04:51'),
(7, 8, 1, 'assigned_staff_id', 'none', '1', '2026-04-15 18:04:51'),
(8, 8, 1, 'status', 'open', 'in_progress', '2026-04-15 18:04:51'),
(9, 10, 3, 'assigned_staff_id', 'none', '3', '2026-04-06 23:04:51'),
(10, 10, 3, 'status', 'open', 'in_progress', '2026-04-06 23:04:51'),
(11, 10, 3, 'status', 'in_progress', 'resolved', '2026-04-07 23:04:51'),
(12, 11, 1, 'assigned_staff_id', 'none', '1', '2026-04-12 23:04:51'),
(13, 11, 1, 'status', 'open', 'in_progress', '2026-04-12 23:04:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_md5` char(32) NOT NULL COMMENT 'MD5 hex digest of password',
  `role` enum('student','faculty','staff') NOT NULL DEFAULT 'student',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_md5`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@northsouth.edu', 'de9bf5643eabf80f4a56fda3bbb84483', 'staff', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(2, 'Rafiq Ahmed', 'rafiq.ahmed@northsouth.edu', 'de9bf5643eabf80f4a56fda3bbb84483', 'staff', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(3, 'Nusrat Jahan', 'nusrat.jahan@northsouth.edu', 'de9bf5643eabf80f4a56fda3bbb84483', 'staff', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(4, 'Dr. Kamal Hossain', 'kamal.hossain@northsouth.edu', '85b954cf9565b9c54add85f09281a50b', 'faculty', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(5, 'Dr. Faria Rahman', 'faria.rahman@northsouth.edu', '85b954cf9565b9c54add85f09281a50b', 'faculty', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(6, 'Dr. Tanvir Alam', 'tanvir.alam@northsouth.edu', '85b954cf9565b9c54add85f09281a50b', 'faculty', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(7, 'Sakib Hasan', 'sakib.hasan@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(8, 'Tasnim Akter', 'tasnim.akter@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(9, 'Arif Mahmud', 'arif.mahmud@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(10, 'Fatima Noor', 'fatima.noor@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(11, 'Raihan Kabir', 'raihan.kabir@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(12, 'Mithila Das', 'mithila.das@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(13, 'Zahid Islam', 'zahid.islam@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51'),
(14, 'Nadia Sultana', 'nadia.sultana@northsouth.edu', 'ad6a280417a0f533d8b670c61667e1a0', 'student', '2026-04-15 23:04:51', '2026-04-15 23:04:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attach_comment` (`comment_id`),
  ADD KEY `fk_attach_user` (`uploaded_by`),
  ADD KEY `idx_attach_ticket` (`ticket_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comment_author` (`user_id`),
  ADD KEY `idx_comment_ticket` (`ticket_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ticket_creator` (`user_id`),
  ADD KEY `fk_ticket_staff` (`assigned_staff_id`),
  ADD KEY `fk_ticket_category` (`category_id`),
  ADD KEY `idx_ticket_status` (`status`),
  ADD KEY `idx_ticket_priority` (`priority`);

--
-- Indexes for table `ticket_history`
--
ALTER TABLE `ticket_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_user` (`changed_by`),
  ADD KEY `idx_history_ticket` (`ticket_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ticket_history`
--
ALTER TABLE `ticket_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `fk_attach_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_attach_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attach_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comment_author` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ticket_creator` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ticket_staff` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_history`
--
ALTER TABLE `ticket_history`
  ADD CONSTRAINT `fk_history_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

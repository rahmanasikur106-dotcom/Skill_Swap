-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2026 at 03:23 PM
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
-- Database: `skillswap`
--

-- --------------------------------------------------------

--
-- Table structure for table `call_signals`
--

CREATE TABLE `call_signals` (
  `id` int(11) NOT NULL,
  `room_id` varchar(64) NOT NULL,
  `from_user` int(11) NOT NULL,
  `to_user` int(11) NOT NULL,
  `type` enum('offer','answer','ice','hangup','ring') NOT NULL,
  `payload` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `MessageID` int(11) NOT NULL,
  `MessageText` varchar(255) NOT NULL,
  `IsRead` tinyint(1) DEFAULT 0,
  `Timestamp` datetime DEFAULT current_timestamp(),
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL,
  `IsEdited` tinyint(1) DEFAULT 0,
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`MessageID`, `MessageText`, `IsRead`, `Timestamp`, `sender_id`, `receiver_id`, `FilePath`, `IsEdited`, `file_path`, `file_name`, `file_type`, `file_size`) VALUES
(1, 'Hey, are you available?', 0, '2026-04-24 03:01:49', 1, 2, NULL, 0, NULL, NULL, NULL, NULL),
(2, 'Yes, what do you need?', 1, '2026-04-24 03:01:49', 2, 1, NULL, 0, NULL, NULL, NULL, NULL),
(3, 'Can we swap skills?', 0, '2026-04-24 03:01:49', 1, 2, NULL, 0, NULL, NULL, NULL, NULL),
(4, 'Sure, what skills do you have?', 1, '2026-04-24 03:01:49', 2, 1, NULL, 0, NULL, NULL, NULL, NULL),
(5, 'I know Python and MySQL', 0, '2026-04-24 03:01:49', 1, 2, NULL, 0, NULL, NULL, NULL, NULL),
(6, 'Hello this is a test message!', 0, '2026-05-05 13:08:51', 1, 2, NULL, 0, NULL, NULL, NULL, NULL),
(7, 'Hello this is a test message!', 0, '2026-05-05 13:11:11', 1, 2, NULL, 0, NULL, NULL, NULL, NULL),
(8, 'Hello this is a test message!', 0, '2026-05-05 13:12:55', 1, 2, NULL, 0, NULL, NULL, NULL, NULL),
(9, 'hi', 1, '2026-05-05 13:50:12', 3, 5, NULL, 0, NULL, NULL, NULL, NULL),
(10, 'how are you doing', 1, '2026-05-05 13:54:25', 5, 5, NULL, 0, NULL, NULL, NULL, NULL),
(11, 'hi', 0, '2026-05-05 14:08:34', 5, 4, NULL, 0, NULL, NULL, NULL, NULL),
(12, 'what are you doing', 0, '2026-05-05 14:22:39', 5, 4, NULL, 0, NULL, NULL, NULL, NULL),
(13, 'hi', 0, '2026-05-05 14:26:14', 5, 4, NULL, 1, NULL, NULL, NULL, NULL),
(14, 'hi', 0, '2026-05-05 14:28:11', 5, 3, NULL, 0, NULL, NULL, NULL, NULL),
(16, 'hi', 0, '2026-05-08 09:29:36', 5, 0, NULL, 0, NULL, NULL, NULL, NULL),
(17, 'hi', 0, '2026-05-08 09:29:43', 5, 0, NULL, 0, NULL, NULL, NULL, NULL),
(18, 'hi', 0, '2026-05-08 09:29:52', 5, 0, NULL, 0, NULL, NULL, NULL, NULL),
(19, 'hi', 0, '2026-05-08 09:39:51', 5, 0, NULL, 0, NULL, NULL, NULL, NULL),
(24, 'hello imran', 0, '2026-05-08 10:29:55', 6, 4, NULL, 0, NULL, NULL, NULL, NULL),
(25, 'hi', 0, '2026-05-08 10:32:43', 7, 6, NULL, 0, NULL, NULL, NULL, NULL),
(27, 'hi', 1, '2026-05-10 02:06:13', 8, 7, NULL, 0, NULL, NULL, NULL, NULL),
(28, 'hello', 1, '2026-05-10 02:06:27', 7, 8, NULL, 0, NULL, NULL, NULL, NULL),
(29, 'Screenshot 2026-03-27 011101.png', 1, '2026-05-10 02:33:14', 7, 8, NULL, 0, 'uploads/chat_files/cf_69ffd24a35f8e8.02778986.png', 'Screenshot 2026-03-27 011101.png', 'image/png', 94423),
(30, '1778152076_dashboard.php', 1, '2026-05-10 02:33:41', 8, 7, NULL, 0, 'uploads/chat_files/cf_69ffd265bb4e16.86012176.php', '1778152076_dashboard.php', 'application/octet-stream', 1233),
(31, 'gftdc', 1, '2026-05-12 13:38:56', 7, 8, NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `message` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `reviewed_id` int(11) DEFAULT NULL,
  `stars` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`rating_id`, `reviewer_id`, `reviewed_id`, `stars`) VALUES
(1, 3, 1, 4),
(2, 3, 5, 4),
(3, 3, 7, 5),
(4, 3, 8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `skill_offered` varchar(100) NOT NULL,
  `skill_requested` varchar(100) NOT NULL,
  `date_time` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `user1_id`, `user2_id`, `skill_offered`, `skill_requested`, `date_time`, `status`) VALUES
(3, 9, 10, 'hacking', 'curd', '2026-05-08 15:29:00', 'Accepted'),
(4, 13, 12, 'database', 'networking', '2026-05-08 13:53:00', 'Accepted'),
(5, 9, 10, 'coding', 'html', '2026-05-30 10:55:00', 'Rejected'),
(6, 13, 12, 'class diagram', 'UI', '2026-05-10 15:27:00', 'Pending'),
(8, 14, 10, 'frontend', 'java', '2026-05-13 15:30:00', 'Pending'),
(9, 12, 11, 'python', 'programming', '2026-05-04 15:34:00', 'Pending'),
(10, 10, 14, 'database', 'curd', '2026-05-12 15:35:00', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `skill_types`
--

CREATE TABLE `skill_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_types`
--

INSERT INTO `skill_types` (`type_id`, `type_name`) VALUES
(2, 'Learn'),
(1, 'Teach');

-- --------------------------------------------------------

--
-- Table structure for table `swaps`
--

CREATE TABLE `swaps` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `user_location` varchar(100) DEFAULT NULL,
  `rating_average` decimal(2,1) DEFAULT 0.0,
  `total_reviews` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `account_status` enum('active','suspended','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `country` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `username`, `first_name`, `last_name`, `bio`, `profile_picture`, `city`, `user_location`, `rating_average`, `total_reviews`, `is_verified`, `account_status`, `created_at`, `updated_at`, `country`) VALUES
(8, 'ih0162445@gmail.com', '$2y$10$Bn/v4zHFKEfMxwCI/aEH4OyI4KYRiOmgmELBXtDYl560eCW7PQp1W', 'imran', 'Imran', 'Hossain', 'i am so lonely', NULL, 'Taastrup', NULL, 0.0, 0, 0, 'active', '2026-05-12 11:23:25', '2026-05-12 11:25:04', NULL),
(9, 'asikur@gamil.com', '$2y$10$oBc8FuEd6E3RiUuwkc2ivOg9OmE6GhS2JyGNNfg.LeGbQllImDcnK', 'asik', 'Asikur', 'Rahman', 'suuuuuuui', NULL, 'Albertslund', NULL, 0.0, 0, 0, 'active', '2026-05-12 11:27:07', '2026-05-12 11:28:57', NULL),
(10, 'krtikaasingh@gmail.com', '$2y$10$Pj76ZjXRNYm7pwjJd21YYuNImyiI./uG5CkTz7VpK8XBxGkpwECry', 'kritikaa285', 'Kritika ', 'Singh', 'Bye TTYL\r\n', '/skillswap/uploads/profile_pictures/user_10_1778678094.webp', 'Dhangadhi', NULL, 0.0, 0, 0, 'active', '2026-05-13 13:12:54', '2026-05-13 13:15:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `user_skill_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `level_name` varchar(50) DEFAULT NULL,
  `type_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`user_skill_id`, `user_id`, `skill_id`, `level_name`, `type_name`) VALUES
(3, 2, 3, 'Advanced', 'Teach'),
(4, 2, 1, 'Beginner', 'Learn'),
(6, 3, 2, 'Intermediate', 'Teach'),
(7, 2, 3, 'Beginner', 'Learn'),
(11, 1, 1, 'Expert', 'Teach'),
(13, 1, 7, 'Advanced', 'Learn'),
(15, 1, 8, 'Advanced', 'Learn'),
(16, 1, 3, 'Beginner', 'Learn'),
(18, 1, 6, 'Advanced', 'Teach'),
(19, 4, 2, 'Intermediate', 'learn');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `call_signals`
--
ALTER TABLE `call_signals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room` (`room_id`),
  ADD KEY `idx_touser` (`to_user`,`created_at`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `user1_id` (`user1_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`MessageID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewed_id` (`reviewed_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user1_id` (`user1_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indexes for table `skill_types`
--
ALTER TABLE `skill_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `swaps`
--
ALTER TABLE `swaps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`user_skill_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `level_name` (`level_name`),
  ADD KEY `type_name` (`type_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `call_signals`
--
ALTER TABLE `call_signals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `skill_types`
--
ALTER TABLE `skill_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `swaps`
--
ALTER TABLE `swaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `user_skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

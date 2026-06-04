-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 12:35 PM
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
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `date_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `creator_id`, `location`, `date_time`, `created_at`) VALUES
(2, 12, 'Copenhage', '2026-05-13 13:51:00', '2026-05-18 11:51:16'),
(3, 12, 'Copenhage', '2026-05-22 13:51:00', '2026-05-18 11:51:32'),
(4, 17, 'Copenhagen', '2026-05-30 15:50:00', '2026-05-29 08:47:50'),
(5, 17, 'ishoj', '2026-05-30 16:33:00', '2026-05-29 09:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `event_participant`
--

CREATE TABLE `event_participant` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_participant`
--

INSERT INTO `event_participant` (`id`, `event_id`, `user_id`, `joined_at`) VALUES
(2, 4, 17, '2026-05-29 08:47:57'),
(3, 4, 18, '2026-05-29 08:52:51'),
(4, 5, 17, '2026-05-29 09:28:31');

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

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`match_id`, `user1_id`, `user2_id`, `type`, `created_at`) VALUES
(3, 12, 14, 'skill_swap', '2026-05-25 02:03:23'),
(4, 16, 12, 'skill_swap', '2026-05-25 02:27:27'),
(5, 17, 18, 'skill_swap', '2026-05-29 11:03:25');

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
(31, 'gftdc', 1, '2026-05-12 13:38:56', 7, 8, NULL, 0, NULL, NULL, NULL, NULL),
(32, 'hi', 0, '2026-05-18 03:52:45', 11, 9, NULL, 0, NULL, NULL, NULL, NULL),
(33, 'Login Page.png', 0, '2026-05-18 04:06:25', 11, 9, NULL, 0, 'uploads/chat_files/cf_6a0a7421653710.17673243.png', 'Login Page.png', 'image/png', 1784393),
(34, 'Proposal_University of Greater Manchester.pdf', 0, '2026-05-18 04:11:05', 11, 9, NULL, 0, 'uploads/chat_files/cf_6a0a7539120cd2.89146682.pdf', 'Proposal_University of Greater Manchester.pdf', 'application/pdf', 158515),
(35, 'hi', 0, '2026-05-18 04:25:26', 11, 8, NULL, 0, NULL, NULL, NULL, NULL),
(37, 'hi', 0, '2026-05-18 14:23:58', 12, 9, NULL, 1, NULL, NULL, NULL, NULL),
(38, 'hi', 1, '2026-05-19 15:59:32', 13, 12, NULL, 0, NULL, NULL, NULL, NULL),
(39, 'hlw', 0, '2026-05-19 16:14:18', 12, 9, NULL, 0, NULL, NULL, NULL, NULL),
(40, 'hi', 0, '2026-05-25 02:03:41', 12, 14, NULL, 0, NULL, NULL, NULL, NULL),
(41, 'hi', 1, '2026-05-25 02:39:10', 12, 16, NULL, 0, NULL, NULL, NULL, NULL),
(43, 'hi', 1, '2026-05-25 03:07:00', 16, 12, NULL, 0, NULL, NULL, NULL, NULL),
(44, 'Screenshot 2026-03-27 011604.png', 1, '2026-05-25 03:07:20', 16, 12, NULL, 0, 'uploads/chat_files/cf_6a13a0c8bca4f4.81726150.png', 'Screenshot 2026-03-27 011604.png', 'image/png', 111063),
(45, 'hi', 1, '2026-05-29 11:03:32', 17, 18, NULL, 0, NULL, NULL, NULL, NULL),
(46, 'Screenshot 2024-09-28 212029.png', 1, '2026-05-29 11:26:44', 17, 18, NULL, 0, 'uploads/chat_files/cf_6a195bd4d9dd41.68798785.png', 'Screenshot 2024-09-28 212029.png', 'image/png', 308943);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `message` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `message_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `type`, `created_at`, `message`, `is_read`, `message_text`) VALUES
(3, 14, NULL, '2026-05-25 02:03:23', 0, 0, NULL),
(4, 12, NULL, '2026-05-25 02:27:27', 0, 1, NULL),
(5, 12, NULL, '2026-05-25 02:38:23', 0, 1, NULL),
(6, 16, NULL, '2026-05-25 02:39:02', 0, 1, NULL),
(7, 14, NULL, '2026-05-25 02:44:11', 0, 0, NULL),
(8, 17, 'swap_request', '2026-05-29 10:53:54', NULL, 1, 'Radiah Anan sent you a swap request!'),
(9, 18, 'swap_request', '2026-05-29 11:03:25', NULL, 1, 'Jui Talukder accepted your swap request! You can now chat.');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(2, 17, '10165ecdcdf396df312100d7909f2ba920b5944891c1a92067e3031076753dc7', '2026-05-29 12:58:14', '2026-05-29 09:58:14');

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
(4, 3, 8, 2),
(5, 11, 8, 5),
(6, 12, 8, 4);

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
  `status` enum('Pending','Accepted','Rejected') NOT NULL DEFAULT 'Pending'
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
(10, 10, 14, 'database', 'curd', '2026-05-12 15:35:00', 'Pending'),
(11, 17, 18, 'python', 'sql', '2026-11-12 01:02:00', 'Accepted'),
(12, 17, 18, 'HTML/CSS', 'sql', '2026-05-30 17:35:00', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skill_id`, `skill_name`) VALUES
(16, 'art'),
(17, 'dance'),
(9, 'Data Science'),
(14, 'English Writing'),
(11, 'Graphic Design'),
(5, 'HTML/CSS'),
(6, 'Java'),
(2, 'JavaScript'),
(18, 'latte art'),
(10, 'Machine Learning'),
(4, 'MySQL'),
(7, 'Networking'),
(13, 'Photography'),
(3, 'PHP'),
(15, 'Public Speaking'),
(1, 'Python'),
(8, 'UI/UX Design'),
(12, 'Video Editing');

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
(7, 'Graphic Design'),
(6, 'JavaScript'),
(2, 'Learn'),
(4, 'MySQL Databases'),
(3, 'PHP Web Development'),
(1, 'Teach'),
(5, 'UI/UX Design');

-- --------------------------------------------------------

--
-- Table structure for table `swaps`
--

CREATE TABLE `swaps` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swaps`
--

INSERT INTO `swaps` (`id`, `sender_id`, `receiver_id`, `message`, `status`, `created_at`) VALUES
(1, 12, 13, NULL, 'accepted', '2026-05-19 13:16:25'),
(2, 14, 12, NULL, 'accepted', '2026-05-19 14:17:39'),
(4, 16, 12, NULL, 'accepted', '2026-05-25 00:38:23'),
(5, 18, 17, NULL, 'accepted', '2026-05-29 08:53:54');

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
  `location` varchar(100) DEFAULT NULL,
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

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `username`, `first_name`, `last_name`, `bio`, `location`, `profile_picture`, `city`, `user_location`, `rating_average`, `total_reviews`, `is_verified`, `account_status`, `created_at`, `updated_at`, `country`) VALUES
(4, '', '', NULL, 'Alex', 'Morgan', 'Full-stack web developer specializing in PHP backend engines and modern CSS layout structures.', NULL, NULL, NULL, NULL, 4.8, 5, 0, 'active', '2026-05-18 01:39:24', '2026-05-18 01:39:24', NULL),
(8, 'ih0162445@gmail.com', '$2y$10$Bn/v4zHFKEfMxwCI/aEH4OyI4KYRiOmgmELBXtDYl560eCW7PQp1W', 'imran', 'Imran', 'Hossain', 'i am so lonely', NULL, NULL, 'Taastrup', NULL, 3.7, 3, 0, 'active', '2026-05-12 11:23:25', '2026-05-18 11:49:51', NULL),
(9, 'asikur@gamil.com', '$2y$10$oBc8FuEd6E3RiUuwkc2ivOg9OmE6GhS2JyGNNfg.LeGbQllImDcnK', 'asik', 'Asikur', 'Rahman', 'suuuuuuui', NULL, NULL, 'Albertslund', NULL, 0.0, 0, 0, 'active', '2026-05-12 11:27:07', '2026-05-12 11:28:57', NULL),
(10, 'krtikaasingh@gmail.com', '$2y$10$Pj76ZjXRNYm7pwjJd21YYuNImyiI./uG5CkTz7VpK8XBxGkpwECry', 'kritikaa285', 'Kritika ', 'Singh', 'Bye TTYL\r\n', NULL, '/skillswap/uploads/profile_pictures/user_10_1778678094.webp', 'Dhangadhi', NULL, 0.0, 0, 0, 'active', '2026-05-13 13:12:54', '2026-05-13 13:15:30', NULL),
(11, 'rahmanasikur@gmail.com', '$2y$10$/R8eafN/mXo0hnI2NxFm5uJS1ThOR.So82YpRxSN.sYq3xxDsB8A.', 'admin', 'Asikur', 'Rahman', '', '', NULL, 'Brondby Kommune', '', 0.0, 0, 0, 'active', '2026-05-18 01:47:13', '2026-05-18 02:20:45', NULL),
(12, 'rahmanasikur1@gmail.com', '$2y$10$KEYxEe2wf4HwnvNXnVje6elsyrISx2DDE2Ip2Te5pEjzl3hEJiLSq', 'asik12', 'asik', 'fhf', '', NULL, NULL, 'Brondby Kommune', '', 0.0, 0, 0, 'active', '2026-05-18 11:47:54', '2026-05-18 11:51:52', NULL),
(13, 'asasik201@gmail.com', '$2y$10$hLxLiSfKmOPo/aVpYpx9RegmY9775E3Cl.zsjf4VNmwK.WYpiqchy', 'asik1', 'Asikur', 'Rahman', '', NULL, NULL, 'Dhaka', '', 0.0, 0, 0, 'active', '2026-05-19 13:10:22', '2026-05-19 13:11:06', NULL),
(14, 'jhondoe@gmail.com', '$2y$10$avWdVTT.Mzr14o/HXcWMWuFVZKr.AALaNnqy.Mc/SEYURH5rrWa.K', 'jhon', 'Jhon', 'Doe', '', NULL, NULL, '', '', 0.0, 0, 0, 'active', '2026-05-19 14:16:32', '2026-05-19 14:17:28', NULL),
(15, 'riff199901@gmail.com', '$2y$10$n.f7gLdbTBmiEsEnChnpTeI5icE5gy20dKYfNWeQIZwV4TKco9XnS', 'Riff99', 'Rifat', 'Hosen', NULL, NULL, NULL, 'Dhaka', NULL, 0.0, 0, 0, 'active', '2026-05-25 00:23:39', '2026-05-25 00:23:39', NULL),
(16, 'riff9@gmail.com', '$2y$10$rqmYwtA.BNbj1eO5zE6unuzbF2fSUVhLHa2wLObQhRRKmppxBkLO2', 'Riff9', 'Rifat', 'Hosen', '', NULL, NULL, '', '', 0.0, 0, 0, 'active', '2026-05-25 00:24:54', '2026-05-25 00:25:24', NULL),
(17, 'juitalukder01@gmail.com', '$2y$10$A8HWF5KF0YLc..EBuoUdsultXJTv8esS2oNL13qKrdTey753yoaC6', 'Jui01', 'Jui', 'Talukder', '', NULL, NULL, 'Copenhagen', '', 0.0, 0, 0, 'active', '2026-05-29 08:20:54', '2026-05-29 08:40:51', NULL),
(18, 'radiah01@gmail.com', '$2y$10$NsfAgDyU4bVTzIAsX45xTejs6cHVKZo7m1aNL277NzMBwiKrWUiVK', 'radiah01', 'Radiah', 'Anan', '', NULL, NULL, 'ishoj', 'ishoj', 0.0, 0, 0, 'active', '2026-05-29 08:52:24', '2026-05-29 08:53:38', NULL),
(19, 'kritika01@gmail.com', '$2y$10$Hd3m6pOrC8fo8Zum9kHqM.kqz5fj7M48CUnlmVUV5OtAZ0kT0Uv.W', 'kritika01', 'Kritika', 'Singh', NULL, NULL, NULL, 'Alberslund', NULL, 0.0, 0, 0, 'active', '2026-05-29 08:55:30', '2026-05-29 08:55:30', NULL),
(20, 'imran01@gmail.com', '$2y$10$pX7JM7y8JUuFV9yqTjM6vOKwaYdeWKj.Bb0ubtaQhq4B8D8ndL11u', 'imran01', 'Imran', 'Hossen', NULL, NULL, NULL, 'Greve', NULL, 0.0, 0, 0, 'active', '2026-05-29 08:56:14', '2026-05-29 08:56:14', NULL),
(21, 'asikur01@gmail.com', '$2y$10$0p6WZlGZaXfrv.D6s/QMw.zdqoC55u3h0O/4b3miIkW2FvguiE3hC', 'asikur01', 'Asikur', 'Rahman', NULL, NULL, NULL, 'Vesterbro', NULL, 0.0, 0, 0, 'active', '2026-05-29 08:57:12', '2026-05-29 08:57:12', NULL),
(22, 'ih12@gmail.com', '$2y$10$CtbOQ1R8ojsUAsb50pRQj.yEKbyF8dM7.7aLpV.rdGJee4A5sxbme', 'admi', 'imran', 'hossain', NULL, NULL, NULL, 'di', NULL, 0.0, 0, 0, 'active', '2026-05-29 09:18:52', '2026-05-29 09:18:52', NULL);

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
(19, 4, 2, 'Intermediate', 'learn'),
(20, 4, 1, 'Expert', 'Teach'),
(21, 4, 2, 'Intermediate', 'Teach'),
(22, 4, 4, 'Beginner', 'Learn'),
(31, 11, 9, 'Beginner', 'Teach'),
(32, 11, 11, 'Beginner', 'Learn'),
(47, 13, 14, 'Beginner', 'Teach'),
(48, 13, 9, 'Beginner', 'Learn'),
(58, 14, 6, 'Beginner', 'Teach'),
(59, 14, 14, 'Beginner', 'Learn'),
(64, 12, 6, 'Beginner', 'Teach'),
(65, 12, 4, 'Beginner', 'Learn'),
(66, 16, 4, 'Beginner', 'Teach'),
(67, 16, 6, 'Beginner', 'Learn'),
(71, 18, 11, 'Beginner', 'Teach'),
(72, 18, 5, 'Beginner', 'Learn'),
(73, 17, 5, 'Beginner', 'Teach'),
(74, 17, 11, 'Beginner', 'Learn'),
(75, 17, 17, 'Beginner', 'Teach'),
(76, 17, 18, 'Beginner', 'Learn');

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
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `event_participant`
--
ALTER TABLE `event_participant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_join` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
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
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD UNIQUE KEY `skill_name` (`skill_name`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event_participant`
--
ALTER TABLE `event_participant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `skill_types`
--
ALTER TABLE `skill_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `swaps`
--
ALTER TABLE `swaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `user_skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_participant`
--
ALTER TABLE `event_participant`
  ADD CONSTRAINT `event_participant_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_participant_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `pr_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

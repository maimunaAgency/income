-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 25, 2025 at 08:48 AM
-- Server version: 11.4.4-MariaDB
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qydipzkd_Income`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `admin_id`, `activity_type`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, NULL, 'registration', 'New user registered with username: rafi', '139.162.166.85', '2025-04-21 07:19:25'),
(2, 2, NULL, 'registration', 'New user registered with username: rafi2', '139.162.166.85', '2025-04-21 10:04:04'),
(3, 2, NULL, 'deposit_request', 'Deposit request of à§³ 100.00 via bKash', '31.185.107.200', '2025-04-21 10:43:08'),
(4, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:00'),
(5, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:04'),
(6, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:07'),
(7, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:11'),
(8, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:13'),
(9, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:18'),
(10, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:32'),
(11, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:44:36'),
(12, 2, NULL, 'deposit_request', 'Deposit request of à§³ 0.00 via ', '31.185.107.200', '2025-04-21 10:50:01'),
(13, 2, NULL, 'deposit_request', 'Deposit request of à§³ 100.00 via bKash', '31.185.107.200', '2025-04-21 10:50:36'),
(14, 1, NULL, 'deposit_request', 'Deposit request of à§³ 100.00 via bKash', '51.178.28.37', '2025-04-22 04:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'moderator',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposit_proofs`
--

CREATE TABLE `deposit_proofs` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earning_tasks`
--

CREATE TABLE `earning_tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `reward` decimal(12,2) NOT NULL,
  `type` enum('daily','one_time') NOT NULL DEFAULT 'one_time',
  `requirements` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `daily_earning_limit` decimal(12,2) DEFAULT NULL,
  `withdrawal_limit` decimal(12,2) DEFAULT NULL,
  `referral_commission` decimal(5,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `name`, `description`, `price`, `duration_days`, `daily_earning_limit`, `withdrawal_limit`, `referral_commission`, `status`, `created_at`) VALUES
(1, 'Free', 'Basic free membership with limited features', 0.00, 365, 100.00, 1000.00, 5.00, 'active', '2025-04-21 07:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `related_id`, `created_at`) VALUES
(1, 1, 'Welcome to MZ Income!', 'Thank you for joining MZ Income. Start exploring our platform to earn money.', 'welcome', 0, NULL, '2025-04-21 07:19:25'),
(2, 2, 'Welcome to MZ Income!', 'Thank you for joining MZ Income. Start exploring our platform to earn money.', 'welcome', 0, NULL, '2025-04-21 10:04:04'),
(3, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 100.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452321885281', 'deposit', 0, 1, '2025-04-21 10:43:08'),
(4, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 100.00. Reference: DEP17452321885281', 'admin_deposit', 0, 1, '2025-04-21 10:43:08'),
(5, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322407222', 'deposit', 0, 2, '2025-04-21 10:44:00'),
(6, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322407222', 'admin_deposit', 0, 2, '2025-04-21 10:44:00'),
(7, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322447340', 'deposit', 0, 3, '2025-04-21 10:44:04'),
(8, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322447340', 'admin_deposit', 0, 3, '2025-04-21 10:44:04'),
(9, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322471580', 'deposit', 0, 4, '2025-04-21 10:44:07'),
(10, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322471580', 'admin_deposit', 0, 4, '2025-04-21 10:44:07'),
(11, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322518453', 'deposit', 0, 5, '2025-04-21 10:44:11'),
(12, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322518453', 'admin_deposit', 0, 5, '2025-04-21 10:44:11'),
(13, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322538395', 'deposit', 0, 6, '2025-04-21 10:44:13'),
(14, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322538395', 'admin_deposit', 0, 6, '2025-04-21 10:44:13'),
(15, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322583924', 'deposit', 0, 7, '2025-04-21 10:44:18'),
(16, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322583924', 'admin_deposit', 0, 7, '2025-04-21 10:44:18'),
(17, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322728479', 'deposit', 0, 8, '2025-04-21 10:44:32'),
(18, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322728479', 'admin_deposit', 0, 8, '2025-04-21 10:44:32'),
(19, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452322767680', 'deposit', 0, 9, '2025-04-21 10:44:36'),
(20, 1, 'New Deposit Request', 'User rafi2 has requested a deposit of à§³ 0.00. Reference: DEP17452322767680', 'admin_deposit', 0, 9, '2025-04-21 10:44:36'),
(21, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 0.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452326015391', 'deposit', 0, 10, '2025-04-21 10:50:01'),
(22, 2, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 100.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452326362533', 'deposit', 0, 11, '2025-04-21 10:50:36'),
(23, 1, 'à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡', 'à¦†à¦ªà¦¨à¦¾à¦° à§³ 100.00 à¦¡à¦¿à¦ªà§‹à¦œà¦¿à¦Ÿ à¦…à¦¨à§à¦°à§‹à¦§ à¦—à§ƒà¦¹à§€à¦¤ à¦¹à¦¯à¦¼à§‡à¦›à§‡à¥¤ à¦°à§‡à¦«à¦¾à¦°à§‡à¦¨à§à¦¸: DEP17452958616466', 'deposit', 0, 12, '2025-04-22 04:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `payment_accounts`
--

CREATE TABLE `payment_accounts` (
  `id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_type` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_accounts`
--

INSERT INTO `payment_accounts` (`id`, `payment_method_id`, `account_number`, `account_name`, `account_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '01712345678', 'MZ Income bKash', 'Personal', 1, '2025-04-21 10:31:18', '2025-04-21 10:31:18'),
(2, 2, '01812345678', 'MZ Income Nagad', 'Personal', 1, '2025-04-21 10:31:18', '2025-04-21 10:31:18'),
(3, 3, '01912345678', 'MZ Income Rocket', 'Personal', 1, '2025-04-21 10:31:18', '2025-04-21 10:31:18');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `min_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_amount` decimal(12,2) DEFAULT NULL,
  `fee_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `type` enum('deposit','withdraw','both') NOT NULL DEFAULT 'both',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `instructions`, `min_amount`, `max_amount`, `fee_percentage`, `type`, `status`, `created_at`) VALUES
(1, 'bKash', 'Mobile banking service in Bangladesh', NULL, 100.00, NULL, 0.00, 'both', 'active', '2025-04-21 07:19:10'),
(2, 'Nagad', 'Digital financial service in Bangladesh', NULL, 100.00, NULL, 0.00, 'both', 'active', '2025-04-21 07:19:10'),
(3, 'Rocket', 'Mobile financial service by Dutch-Bangla Bank', NULL, 100.00, NULL, 0.00, 'both', 'active', '2025-04-21 07:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `referral_earnings`
--

CREATE TABLE `referral_earnings` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','completed','rejected') NOT NULL DEFAULT 'pending',
  `transaction_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_description`, `created_at`) VALUES
(1, 'site_name', 'MZ Income', 'Website Name', '2025-04-21 07:19:10'),
(2, 'site_description', 'আপনার আয়ের নতুন দিগন্ত', 'Website Description', '2025-04-21 07:19:10'),
(3, 'contact_email', 'support@mzincome.com', 'Support Email Address', '2025-04-21 07:19:10'),
(4, 'contact_phone', '+880 123456789', 'Support Phone Number', '2025-04-21 07:19:10'),
(5, 'withdraw_min_amount', '500', 'Minimum Withdrawal Amount', '2025-04-21 07:19:10'),
(6, 'withdraw_fee_percentage', '2', 'Withdrawal Fee Percentage', '2025-04-21 07:19:10'),
(7, 'deposit_min_amount', '100', 'Minimum Deposit Amount', '2025-04-21 07:19:10'),
(8, 'referral_bonus_percentage', '5', 'Referral Bonus Percentage', '2025-04-21 07:19:10'),
(9, 'maintenance_mode', 'false', 'Website Maintenance Mode', '2025-04-21 07:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdraw','transfer','bonus','earning') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `recipient_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_details` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `reference_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `fee`, `status`, `recipient_id`, `payment_method`, `transaction_details`, `admin_note`, `reference_id`, `created_at`, `updated_at`) VALUES
(1, 2, 'deposit', 100.00, 0.00, 'pending', NULL, 'bKash', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":1}', NULL, 'DEP17452321885281', '2025-04-21 10:43:08', '2025-04-21 10:43:08'),
(2, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322407222', '2025-04-21 10:44:00', '2025-04-21 10:44:00'),
(3, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322447340', '2025-04-21 10:44:04', '2025-04-21 10:44:04'),
(4, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322471580', '2025-04-21 10:44:07', '2025-04-21 10:44:07'),
(5, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322518453', '2025-04-21 10:44:11', '2025-04-21 10:44:11'),
(6, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322538395', '2025-04-21 10:44:13', '2025-04-21 10:44:13'),
(7, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH987\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322583924', '2025-04-21 10:44:18', '2025-04-21 10:44:18'),
(8, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH98\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322728479', '2025-04-21 10:44:32', '2025-04-21 10:44:32'),
(9, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5596GH98\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452322767680', '2025-04-21 10:44:36', '2025-04-21 10:44:36'),
(10, 2, 'deposit', 0.00, 0.00, 'pending', NULL, '', '{\"transaction_id\":\"CD5896GH98\",\"sender_number\":\"01845789685\",\"payment_method_id\":0}', NULL, 'DEP17452326015391', '2025-04-21 10:50:01', '2025-04-21 10:50:01'),
(11, 2, 'deposit', 100.00, 0.00, 'pending', NULL, 'bKash', '{\"transaction_id\":\"CD5596GH98\",\"sender_number\":\"01845789685\",\"payment_method_id\":1}', NULL, 'DEP17452326362533', '2025-04-21 10:50:36', '2025-04-21 10:50:36'),
(12, 1, 'deposit', 100.00, 0.00, 'pending', NULL, 'bKash', '{\"transaction_id\":\"CD5596GH98\",\"sender_number\":\"01845789685\",\"payment_method_id\":1}', NULL, 'DEP17452958616466', '2025-04-22 04:24:21', '2025-04-22 04:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `account_number` varchar(6) NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `referral_code` varchar(10) NOT NULL,
  `referred_by` varchar(50) DEFAULT NULL,
  `membership_id` int(11) DEFAULT 1,
  `status` enum('active','suspended','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `mobile`, `account_number`, `balance`, `referral_code`, `referred_by`, `membership_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'rafi', '$2y$10$6wH5ASMtd7bpISr.l/ts9eNjHFd./7TnX7b1ptnKAjsy5ywk6DHCu', '01849685241', '641538', 0.00, 'EHS3CR', NULL, 1, 'active', '2025-04-21 07:19:25', '2025-04-21 07:19:25'),
(2, 'rafi2', '$2y$10$3BRGNs41LMDKYqSK79gRTO9RU6cywZJ8Nc1mPv.2YSNE978ndZPBC', '01874598536', '329317', 0.00, 'S07RR3', NULL, 1, 'active', '2025-04-21 10:04:04', '2025-04-21 10:04:04');

-- --------------------------------------------------------

--
-- Table structure for table `user_memberships`
--

CREATE TABLE `user_memberships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `transaction_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_task_completions`
--

CREATE TABLE `user_task_completions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reward_amount` decimal(12,2) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `deposit_proofs`
--
ALTER TABLE `deposit_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `earning_tasks`
--
ALTER TABLE `earning_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_accounts`
--
ALTER TABLE `payment_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referred_id` (`referred_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `membership_id` (`membership_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `user_task_completions`
--
ALTER TABLE `user_task_completions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deposit_proofs`
--
ALTER TABLE `deposit_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earning_tasks`
--
ALTER TABLE `earning_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `payment_accounts`
--
ALTER TABLE `payment_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_memberships`
--
ALTER TABLE `user_memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_task_completions`
--
ALTER TABLE `user_task_completions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deposit_proofs`
--
ALTER TABLE `deposit_proofs`
  ADD CONSTRAINT `deposit_proofs_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_accounts`
--
ALTER TABLE `payment_accounts`
  ADD CONSTRAINT `payment_accounts_ibfk_1` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_earnings`
--
ALTER TABLE `referral_earnings`
  ADD CONSTRAINT `referral_earnings_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_earnings_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_earnings_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD CONSTRAINT `user_memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_memberships_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `membership_plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_memberships_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_task_completions`
--
ALTER TABLE `user_task_completions`
  ADD CONSTRAINT `user_task_completions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_task_completions_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `earning_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_task_completions_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

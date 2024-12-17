-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2024 at 06:17 PM
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
-- Database: `burial_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `section_title` varchar(20) NOT NULL,
  `sub_title` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `section_title`, `sub_title`) VALUES
(1, 'Our History', ''),
(2, 'Our Mission & Values', ''),
(3, 'Our Services', ''),
(4, 'Our Team', 'Dedicated professionals committed to serving our community');

-- --------------------------------------------------------

--
-- Table structure for table `about_2`
--

CREATE TABLE `about_2` (
  `id` int(11) NOT NULL,
  `card_icon` varchar(50) NOT NULL,
  `card_title` text NOT NULL,
  `card_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_2`
--

INSERT INTO `about_2` (`id`, `card_icon`, `card_title`, `card_text`) VALUES
(1, 'fas fa-heart', 'Compassion', 'We provide caring and understanding service to families during their time of loss, ensuring dignity and respect in every interaction.'),
(2, 'fas fa-hands-helping', 'Service', 'Our dedicated team is committed to maintaining a beautiful and peaceful environment while providing professional assistance to all visitors.'),
(3, 'fas fa-cross', 'Faith', 'We honor our Catholic heritage while welcoming all faiths, fostering a sacred space for prayer and remembrance.'),
(4, 'fas fa-map-marked-alt', 'Burial Lots', '• Family Estates\r\n• Individual Plots\r\n• Memorial Gardens\r\n• Cremation Spaces'),
(5, 'fas fa-hands', 'Additional Services', '• Memorial Services\r\n• Maintenance Programs\r\n• Family Assistance\r\n• Documentation Support');

-- --------------------------------------------------------

--
-- Table structure for table `about_main`
--

CREATE TABLE `about_main` (
  `id` int(11) NOT NULL,
  `image` varchar(50) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_main`
--

INSERT INTO `about_main` (`id`, `image`, `text`) VALUES
(1, 'assets/images/history.jpg', 'Founded in 1970, Sto. Niño Parish Cemetery has served as a sacred resting place for generations of families in our community. Nestled in serene surroundings, the cemetery reflects the enduring faith and cherished traditions of our parish, offering a place of solace and connection for those honoring their loved ones.\r\n\r\nOver the years, we have grown and evolved while maintaining our unwavering commitment to providing a peaceful and dignified environment for remembrance and reflection. With thoughtfully maintained grounds, a strong focus on heritage preservation, and compassionate support for grieving families, Sto. Niño Parish Cemetery remains a cornerstone of our community’s spiritual and historical legacy.\r\n\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `about_team`
--

CREATE TABLE `about_team` (
  `id` int(11) NOT NULL,
  `image` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `position` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_team`
--

INSERT INTO `about_team` (`id`, `image`, `name`, `position`) VALUES
(1, 'assets/images/father.webp', 'Fr. Elmer L. Roque', 'Parish Priest'),
(2, 'assets/images/rosalia.jpg', 'Rosalia S. Abenido', 'Cemetery Manager'),
(3, 'assets/images/mary.jpg', 'Mary Jane Dela Cruz', 'Customer Service');

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `account_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(250) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_customer` tinyint(1) NOT NULL DEFAULT 0,
  `is_staff` tinyint(1) NOT NULL DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `username`, `password`, `first_name`, `middle_name`, `last_name`, `email`, `phone_number`, `created_at`, `updated_at`, `is_customer`, `is_staff`, `is_admin`, `is_banned`, `is_deleted`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'admin', '$2y$10$xEbCA7dBAxshPGfPsUjPjOeCWx/yvdQ4S7RqeZh3ZBz1XDmhejn6u', 'admin', '', 'admin', 'stoninoparishcemetery@gmail.com', '09991234567', '2024-10-12 14:11:57', '2024-12-17 15:33:55', 0, 0, 1, 0, 0, NULL, NULL),
(2, 'reign', '$2y$10$1QHbZfc.zg2TfV4RcheypOpFwI7k7GUVCyHmaOzK/FmuMeiyk4PL2', 'Reign Ian', 'Carreon', 'Magno', 'reignianc.magno@gmail.com', '09123456789', '2024-10-12 14:16:13', '2024-12-17 15:34:02', 1, 0, 0, 0, 0, NULL, NULL),
(3, 'moonarch', '$2y$10$/n4BBRZUoaTLGFaZR8a3JuQ4Wh/Q8M.Xct7ezGXdPnhxvXfjB9NBS', 'moonarch', '', 'eye', 'reignianc.magno2@gmail.com', '09752441070', '2024-12-15 19:08:34', '2024-12-17 15:34:10', 0, 1, 0, 0, 0, NULL, NULL),
(40, 'jamal', '$2y$10$km.7j1ywRo3AZ3mCABn2He4oERwtU61PfPIlkj7zksMNkLjxngEV.', 'jamal', 'alumbre', 'albadi', 'reigniancarreonmagno@gmail.com', '09998065631', '2024-12-17 15:37:06', '2024-12-17 15:37:06', 1, 0, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `phone`, `email`, `address`) VALUES
(1, '(+63) 975-244-1070', 'stoninoparishcemetery@gmail.com', 'Brgy. Tabid, Ozamiz City, Misamis Occidental, Philippines 7200');

-- --------------------------------------------------------

--
-- Table structure for table `lots`
--

CREATE TABLE `lots` (
  `lot_id` int(11) NOT NULL,
  `lot_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `size` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `lot_image` varchar(100) NOT NULL,
  `status` enum('Available','On Request','Reserved') NOT NULL DEFAULT 'Available',
  `description` varchar(1000) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lots`
--

INSERT INTO `lots` (`lot_id`, `lot_name`, `location`, `size`, `price`, `lot_image`, `status`, `description`, `is_deleted`, `created_at`) VALUES
(26, 'Lot 1', 'Block 1', '25', 100000.00, 'lots_images/single.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 15:55:41'),
(28, 'Lot 2', 'Block 1', '25', 100000.00, 'lots_images/single_1.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:05:58'),
(29, 'Lot 3', 'Block 1', '25', 100000.00, 'lots_images/single_2.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:06:22'),
(30, 'Lot 4', 'Block 1', '25', 100000.00, 'lots_images/single_3.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:06:33'),
(31, 'Lot 5', 'Block 1', '25', 100000.00, 'lots_images/single_5.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:06:52'),
(32, 'Lot 6', 'Block 1', '25', 100000.00, 'lots_images/single_6.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:07:34'),
(33, 'Lot 7', 'Block 1', '25', 100000.00, 'lots_images/single_7.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:07:49'),
(34, 'Lot 8', 'Block 1', '25', 100000.00, 'lots_images/single_8.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:08:04'),
(35, 'Lot 9', 'Block 1', '25', 100000.00, 'lots_images/single_9.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:08:18'),
(36, 'Lot 10', 'Block 1', '25', 100000.00, 'lots_images/single_10.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:09:20'),
(37, 'Lot 11', 'Block 2', '25', 100000.00, 'lots_images/single2.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:21:19'),
(38, 'Lot 12', 'Block 2', '25', 100000.00, 'lots_images/single2_1.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:22:22'),
(39, 'Lot 13', 'Block 2', '25', 100000.00, 'lots_images/single2_2.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:22:32'),
(40, 'Lot 14', 'Block 2', '25', 100000.00, 'lots_images/single2_3.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:22:42'),
(41, 'Lot 15', 'Block 2', '25', 100000.00, 'lots_images/single2_4.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:22:50'),
(42, 'Lot 16', 'Block 2', '25', 100000.00, 'lots_images/single2_5.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:23:00'),
(43, 'Lot 17', 'Block 2', '25', 100000.00, 'lots_images/single2_6.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:23:11'),
(44, 'Lot 18', 'Block 2', '25', 100000.00, 'lots_images/single2_7.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:23:21'),
(45, 'Lot 19', 'Block 2', '25', 100000.00, 'lots_images/single2_8.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:23:30'),
(46, 'Lot 20', 'Block 2', '25', 100000.00, 'lots_images/single2_9.jpg', 'Available', 'Standard-sized burial plot that accommodates a single casket or interment. Design is simple and uniform, focusing on maintaining a clean, flat lawn appearance with minimal surface structures.', 0, '2024-12-17 16:23:40'),
(47, 'Lot 21', 'Block 3', '50', 200000.00, 'lots_images/double.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:27:10'),
(48, 'lot 22', 'Block 3', '50', 200000.00, 'lots_images/double_1.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:28:47'),
(49, 'Lot 23', 'Block 3', '50', 200000.00, 'lots_images/double_2.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:29:46'),
(50, 'Lot 24', 'Block 3', '50', 200000.00, 'lots_images/double_3.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:30:07'),
(51, 'Lot 25', 'Block 3', '50', 200000.00, 'lots_images/double_4.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:30:20'),
(54, 'Lot 26', 'Block 3', '50', 200000.00, 'lots_images/double2.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:33:21'),
(55, 'Lot 27', 'Block 3', '50', 200000.00, 'lots_images/double2_1.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:33:34'),
(56, 'Lot 28', 'Block 3', '50', 200000.00, 'lots_images/double2_2.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:33:51'),
(57, 'Lot 29', 'Block 3', '50', 200000.00, 'lots_images/double2_3.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:35:19'),
(58, 'Lot 30', 'Block 3', '50', 200000.00, 'lots_images/double2_4.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:35:30'),
(59, 'Lot 31', 'Block 3', '50', 200000.00, 'lots_images/lot.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:35:51'),
(60, 'Lot 32', 'Block 3', '50', 200000.00, 'lots_images/lot_1.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:36:01'),
(61, 'Lot 33', 'Block 3', '50', 200000.00, 'lots_images/lot_2.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:36:10'),
(62, 'Lot 34', 'Block 3', '50', 200000.00, 'lots_images/lot_3.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:36:19'),
(63, 'Lot 35', 'Block 3', '50', 200000.00, 'lots_images/lot_4.jpg', 'Available', 'A double-size burial lot designed to accommodate two interments, typically for couples or family members. It maintains the clean, flat appearance characteristic of lawn cemeteries.', 0, '2024-12-17 16:36:27'),
(64, 'Lot 36', 'Block 3', '100', 500000.00, 'lots_images/big.jpg', 'Available', 'A family-size burial lot is a larger plot designed to accommodate multiple interments for family members, maintaining the clean and uniform lawn appearance. The lot can accommodate multiple graves side-by-side or utilize double-depth burials for space efficiency.', 0, '2024-12-17 16:43:41'),
(65, 'Lot 37', 'Block 3', '100', 500000.00, 'lots_images/big_2.jpg', 'Available', 'A family-size burial lot is a larger plot designed to accommodate multiple interments for family members, maintaining the clean and uniform lawn appearance. The lot can accommodate multiple graves side-by-side or utilize double-depth burials for space efficiency.', 0, '2024-12-17 16:44:30'),
(66, 'Lot 38', 'Block 3', '100', 500000.00, 'lots_images/big_4.jpg', 'Available', 'A family-size burial lot is a larger plot designed to accommodate multiple interments for family members, maintaining the clean and uniform lawn appearance. The lot can accommodate multiple graves side-by-side or utilize double-depth burials for space efficiency.', 0, '2024-12-17 16:45:12'),
(67, 'Lot 39', 'Block 3', '100', 500000.00, 'lots_images/big_5.jpg', 'Available', 'A family-size burial lot is a larger plot designed to accommodate multiple interments for family members, maintaining the clean and uniform lawn appearance. The lot can accommodate multiple graves side-by-side or utilize double-depth burials for space efficiency.', 0, '2024-12-17 16:45:36'),
(68, 'Lot 40', 'Block 3', '100', 500000.00, 'lots_images/big_6.jpg', 'Available', 'A family-size burial lot is a larger plot designed to accommodate multiple interments for family members, maintaining the clean and uniform lawn appearance. The lot can accommodate multiple graves side-by-side or utilize double-depth burials for space efficiency.', 0, '2024-12-17 16:45:48'),
(69, 'Lot 41', 'Block 4', '100', 1000000.00, 'lots_images/mausoleum.jpg', 'Available', 'A mausoleum crypt refers to a burial space within a mausoleum, which is an above-ground structure designed to house the remains of individuals or families. Mausoleum crypts are ideal for those seeking an alternative to in-ground burials and often carry an air of tradition, prestige, and permanence.', 0, '2024-12-17 16:49:13'),
(70, 'Lot 42', 'Block 4', '100', 1000000.00, 'lots_images/mausoleum_1.jpg', 'Available', 'A mausoleum crypt refers to a burial space within a mausoleum, which is an above-ground structure designed to house the remains of individuals or families. Mausoleum crypts are ideal for those seeking an alternative to in-ground burials and often carry an air of tradition, prestige, and permanence.', 0, '2024-12-17 16:50:24'),
(71, 'Lot 43', 'Block 5', '100', 1500000.00, 'lots_images/mau.jpg', 'Available', 'A mausoleum crypt refers to a burial space within a mausoleum, which is an above-ground structure designed to house the remains of individuals or families. Mausoleum crypts are ideal for those seeking an alternative to in-ground burials and often carry an air of tradition, prestige, and permanence.', 0, '2024-12-17 16:55:21'),
(72, 'Lot 44', 'Block 5', '100', 2500000.00, 'lots_images/modernmau.jpg', 'Available', 'A mausoleum crypt refers to a burial space within a mausoleum, which is an above-ground structure designed to house the remains of individuals or families. Mausoleum crypts are ideal for those seeking an alternative to in-ground burials and often carry an air of tradition, prestige, and permanence.', 0, '2024-12-17 16:55:56'),
(73, 'Lot 45', 'Block 5', '100', 2500000.00, 'lots_images/mau2.jpg', 'Available', 'A mausoleum crypt refers to a burial space within a mausoleum, which is an above-ground structure designed to house the remains of individuals or families. Mausoleum crypts are ideal for those seeking an alternative to in-ground burials and often carry an air of tradition, prestige, and permanence.', 0, '2024-12-17 16:56:07');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `type` enum('reservation_status','payment_success','payment_due_today','payment_due_tomorrow','payment_missed') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_plan`
--

CREATE TABLE `payment_plan` (
  `payment_plan_id` int(11) NOT NULL,
  `plan` varchar(250) NOT NULL,
  `duration` int(11) NOT NULL,
  `down_payment` float NOT NULL,
  `interest_rate` float NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_plan`
--

INSERT INTO `payment_plan` (`payment_plan_id`, `plan`, `duration`, `down_payment`, `interest_rate`, `is_deleted`) VALUES
(1, 'Spot Cash', 0, 0, 0, 0),
(2, '12 months installment', 12, 20, 0, 0),
(3, '24 months installment', 24, 20, 0, 0),
(4, '3 years installment', 48, 0, 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `penalty`
--

CREATE TABLE `penalty` (
  `penalty_id` int(11) NOT NULL,
  `penalty_amount` decimal(10,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalty`
--

INSERT INTO `penalty` (`penalty_id`, `penalty_amount`) VALUES
(1, 3.0);

-- --------------------------------------------------------

--
-- Table structure for table `penalty_log`
--

CREATE TABLE `penalty_log` (
  `penalty_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `penalty_amount` decimal(10,2) NOT NULL,
  `penalty_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pubmat_1`
--

CREATE TABLE `pubmat_1` (
  `id` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  `heading` text DEFAULT NULL,
  `text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pubmat_1`
--

INSERT INTO `pubmat_1` (`id`, `image`, `heading`, `text`) VALUES
(1, 'assets/images/slide1.jpg', 'Welcome to Sto. Niño Parish Cemetery', 'A peaceful resting place for your loved ones'),
(2, 'assets/images/slide2.jpg', 'Serene Environment', 'Beautiful landscapes and peaceful surroundings'),
(3, 'assets/images/slide3.jpg', 'Professional Services', 'Dedicated to providing respectful memorial services');

-- --------------------------------------------------------

--
-- Table structure for table `pubmat_2`
--

CREATE TABLE `pubmat_2` (
  `id` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  `heading` text DEFAULT NULL,
  `text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pubmat_2`
--

INSERT INTO `pubmat_2` (`id`, `image`, `heading`, `text`) VALUES
(1, 'assets/images/display4.jpg', 'Single Lot', 'Basic and affordable burial lot designed to accommodate one casket. \r\n\r\nLocated within designated row sections at garden lawn.'),
(2, 'assets/images/display2.jpg', 'Companion Lot', 'A burial lot designed for two individuals, perfect for spouses or family members.\r\n\r\nSide-by-side orientation within the garden lawn.\r\n'),
(3, 'assets/images/display5.jpg', 'Family Estate', 'Larger lots designed to accommodate multiple family members.\r\n\r\nProvides a dedicated area for an entire family’s legacy.'),
(4, 'assets/images/display3.jpg', 'Mausoleum Crypt', 'Above-ground burial within a mausoleum structure.\r\n\r\nOffers an alternative to in-ground burial, appealing to those seeking entombment.');

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `lot_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `payment_plan_id` int(11) NOT NULL,
  `monthly_payment` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `request` enum('Pending','Confirmed','Cancelled') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_penalty_month` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_logs`
--

CREATE TABLE `staff_logs` (
  `log_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_2`
--
ALTER TABLE `about_2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_main`
--
ALTER TABLE `about_main`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_team`
--
ALTER TABLE `about_team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lots`
--
ALTER TABLE `lots`
  ADD PRIMARY KEY (`lot_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `notifications_ibfk_2` (`reference_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_reservation_id` (`reservation_id`);

--
-- Indexes for table `payment_plan`
--
ALTER TABLE `payment_plan`
  ADD PRIMARY KEY (`payment_plan_id`);

--
-- Indexes for table `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`penalty_id`);

--
-- Indexes for table `penalty_log`
--
ALTER TABLE `penalty_log`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `pubmat_1`
--
ALTER TABLE `pubmat_1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pubmat_2`
--
ALTER TABLE `pubmat_2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_lot_id` (`lot_id`),
  ADD KEY `fk_account_id` (`account_id`),
  ADD KEY `fk_payment_plan_id` (`payment_plan_id`);

--
-- Indexes for table `staff_logs`
--
ALTER TABLE `staff_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `about_2`
--
ALTER TABLE `about_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `about_main`
--
ALTER TABLE `about_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `about_team`
--
ALTER TABLE `about_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lots`
--
ALTER TABLE `lots`
  MODIFY `lot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=961;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `payment_plan`
--
ALTER TABLE `payment_plan`
  MODIFY `payment_plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penalty`
--
ALTER TABLE `penalty`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penalty_log`
--
ALTER TABLE `penalty_log`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `pubmat_1`
--
ALTER TABLE `pubmat_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pubmat_2`
--
ALTER TABLE `pubmat_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `staff_logs`
--
ALTER TABLE `staff_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `reservation` (`reservation_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_reservation_id` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `penalty_log`
--
ALTER TABLE `penalty_log`
  ADD CONSTRAINT `penalty_log_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `fk_account_id` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_lot_id` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`lot_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_payment_plan_id` FOREIGN KEY (`payment_plan_id`) REFERENCES `payment_plan` (`payment_plan_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `staff_logs`
--
ALTER TABLE `staff_logs`
  ADD CONSTRAINT `staff_logs_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `account` (`account_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

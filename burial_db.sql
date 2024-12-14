-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2024 at 05:25 PM
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
(4, 'fas fa-map-marked-alt', 'Burial Lots', '• Family Estates <br><br>\r\n• Individual Plots <br><br>\r\n• Memorial Gardens <br><br>\r\n• Cremation Spaces'),
(5, 'fas fa-hands', 'Additional Services', '• Memorial Services<br><br>\r\n• Maintenance Programs<br><br>\r\n• Family Assistance<br><br>\r\n• Documentation Support<br><br>');

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
(1, 'assets/images/slide2.jpg', 'Founded in [Year], Sto. Niño Parish Cemetery has served as a sacred resting place for generations of families in our community. Our cemetery stands as a testament to the rich history and enduring faith of our parish.\r\n<br>\r\n<br>\r\nOver the years, we have grown and evolved while maintaining our commitment to providing a peaceful and dignified environment for remembrance and reflection.');

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
(1, 'assets/images/image.jpg', 'Fr. [Name]', 'Parish Priest'),
(2, 'assets/images/image.jpg', 'Fr. [Name]', 'Cemetery Manager'),
(3, 'assets/images/image.jpg', 'Fr. [Name]', 'Customer Service');

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
  `is_customer` tinyint(1) NOT NULL DEFAULT 0,
  `is_staff` tinyint(1) NOT NULL DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `username`, `password`, `first_name`, `middle_name`, `last_name`, `email`, `phone_number`, `created_at`, `is_customer`, `is_staff`, `is_admin`, `is_banned`, `reset_token`, `reset_token_expiry`) VALUES
(8, 'admin', '$2y$10$N0qDeYTcgKNeSHCIqFb/cupIQPBV5g2SNtDVb7FlT1EfqVNYdk6C2', 'admin', '', 'admin', 'admin@gmail.com', '09991234567', '2024-10-12 14:11:57', 0, 0, 1, 0, NULL, NULL),
(9, 'reign', '$2y$10$cliEw.S4Tq7vLAU0n/TjhusXoNyW6ZLVNe//K7wWK53jWYTLKqqv6', 'Reign Ian', 'Carreon', 'Magno', 'reign@gmail.com', '09123456789', '2024-10-12 14:16:13', 1, 0, 0, 0, NULL, NULL),
(12, 'staff', '$2y$10$vcHUVbLyqVKgkUcTHpa.jurg9AOmE5zGzFrNfKFkxMslXDnUlGmC.', 'staff', 'staff', 'staff', 'moonarcheye@gmail.com', '09752441070', '2024-12-13 20:51:55', 0, 1, 0, 0, NULL, NULL),
(13, 'staff2', '$2y$10$6DsqfnmpIPt9q6/5Nbfn9eTiBaTlUoXRulY36qorKaxQCM7w3/p5W', 'staff2', 'staff2', 'staff2', 'moonarcheye@gmail.com', '09752441070', '2024-12-13 21:15:13', 0, 1, 0, 0, NULL, NULL),
(14, 'staff22', '$2y$10$8wFUkXwDvnBh6hyvKhwHH.RytIjDizuld1Vms2Kr.Z2rVTrusNlpK', 'staff2', 'staff2', 'staff2', 'moonarcheye@gmail.com', '09123456789', '2024-12-13 21:37:42', 0, 1, 0, 0, NULL, NULL),
(15, 'jamal', '$2y$10$GEQscVOa6QgppRKwJy6YNOsqlB067r3bGgpeUEOEG3QmuJKdbGRsG', 'jamal', 'al', 'badi', 'jamalalbadi03@gmail.com', '09752441070', '2024-12-14 09:15:55', 1, 0, 0, 0, NULL, NULL),
(18, 'haha', '$2y$10$4qYksx5yXEtKKuqg4uaOR.dIBrDkm0iD4ucBCYiEtSxnhx7mADL26', 'haha', 'haha', 'haha', 'reignianc.magno@gmail.com', '09752441070', '2024-12-14 09:39:48', 1, 0, 0, 0, NULL, NULL);

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
(1, '+1234567890', 'info@example.com', 'Cemetery Address Here');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lots`
--

INSERT INTO `lots` (`lot_id`, `lot_name`, `location`, `size`, `price`, `lot_image`, `status`, `description`, `created_at`) VALUES
(20, 'lot 1', 'block 1', '25', 1000.00, 'lots_images/lot.jpg', 'Reserved', 'a', '2024-12-12 20:06:03');

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `account_id`, `type`, `title`, `message`, `reference_id`, `is_read`, `created_at`) VALUES
(144, 15, 'payment_missed', 'Missed Payment Due', 'A penalty of ₱0.76 has been applied to your balance due to late payment.', 86, 0, '2024-12-14 16:25:07');

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
(4, '3 years installment', 48, 0, 10, 0),
(5, 'qq', 12, 30, 10, 0);

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

--
-- Dumping data for table `penalty_log`
--

INSERT INTO `penalty_log` (`penalty_id`, `reservation_id`, `penalty_amount`, `penalty_date`) VALUES
(73, 86, 0.76, '2024-12-14 00:00:00'),
(74, 86, 0.76, '2024-12-14 00:00:00');

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
(1, 'assets/images/slide2.jpg', 'Lawn Lot', 'Peaceful garden setting with well-maintained grounds.'),
(2, 'assets/images/slide2.jpg', 'Garden Lot', 'Beautiful garden lots with scenic views.'),
(3, 'assets/images/slide2.jpg', 'Family Estate', 'Spacious family estates for generations to come.');

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

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `account_id`, `lot_id`, `reservation_date`, `payment_plan_id`, `monthly_payment`, `balance`, `request`, `created_at`, `updated_at`, `last_penalty_month`) VALUES
(86, 15, 20, '2024-12-14', 4, 25.36, 1218.92, 'Confirmed', '2024-12-14 09:04:38', '2024-12-14 16:25:07', NULL);

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
  ADD KEY `account_id` (`account_id`);

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
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lots`
--
ALTER TABLE `lots`
  MODIFY `lot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `payment_plan`
--
ALTER TABLE `payment_plan`
  MODIFY `payment_plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penalty_log`
--
ALTER TABLE `penalty_log`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `pubmat_1`
--
ALTER TABLE `pubmat_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pubmat_2`
--
ALTER TABLE `pubmat_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `staff_logs`
--
ALTER TABLE `staff_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE;

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

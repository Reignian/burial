-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2024 at 05:58 AM
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
  `is_customer` tinyint(1) NOT NULL DEFAULT 1,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `username`, `password`, `first_name`, `middle_name`, `last_name`, `email`, `phone_number`, `created_at`, `is_customer`, `is_admin`) VALUES
(8, 'admin', '$2y$10$N0qDeYTcgKNeSHCIqFb/cupIQPBV5g2SNtDVb7FlT1EfqVNYdk6C2', 'admin', '', 'admin', 'admin@gmail.com', '09991234567', '2024-10-12 14:11:57', 1, 1),
(9, 'reign', '$2y$10$cliEw.S4Tq7vLAU0n/TjhusXoNyW6ZLVNe//K7wWK53jWYTLKqqv6', 'Reign Ian', 'Carreon', 'Magno', 'reign@gmail.com', '09123456789', '2024-10-12 14:16:13', 1, 0);

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
  `status` varchar(255) NOT NULL DEFAULT 'Available',
  `description` varchar(1000) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lots`
--

INSERT INTO `lots` (`lot_id`, `lot_name`, `location`, `size`, `price`, `lot_image`, `status`, `description`, `created_at`) VALUES
(6, 'lot 1', 'block 1', '2', 400000.00, 'lots_images/HAKDOG.png', 'Reserved', 'fordago', '2024-10-12 14:18:03'),
(11, '123', '213', '123', 213.00, 'lots_images/462638319_1025131135758893_3299917056877386691_n.jpg', 'Reserved', 'ddasdwecesd', '2024-10-23 18:52:38'),
(13, 'qwe', 'qwe', '12', 123.00, 'lots_images/Screenshot (3).png', 'Available', 'dawdafe', '2024-10-23 19:38:08');

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

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `reservation_id`, `payment_date`, `amount_paid`) VALUES
(76, 37, '2024-10-23 18:59:27', 1.00);

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
(5, 'qq', 12, 20, 10, 0);

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
(55, 37, 843.99, '2024-11-23 00:00:00');

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
  `request` varchar(255) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_penalty_month` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `account_id`, `lot_id`, `reservation_date`, `payment_plan_id`, `monthly_payment`, `balance`, `request`, `created_at`, `updated_at`, `last_penalty_month`) VALUES
(37, 9, 6, '2024-10-23', 5, 28133.08, 418441.00, 'Confirmed', '2024-10-22 20:26:37', '2024-11-24 20:27:14', NULL),
(38, 9, 11, '2024-10-24', 5, 14.98, 222.37, 'Confirmed', '2024-10-23 18:53:06', '2024-10-23 18:53:19', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `lots`
--
ALTER TABLE `lots`
  ADD PRIMARY KEY (`lot_id`);

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
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `fk_lot_id` (`lot_id`),
  ADD KEY `fk_account_id` (`account_id`),
  ADD KEY `fk_payment_plan_id` (`payment_plan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `lots`
--
ALTER TABLE `lots`
  MODIFY `lot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `payment_plan`
--
ALTER TABLE `payment_plan`
  MODIFY `payment_plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penalty_log`
--
ALTER TABLE `penalty_log`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_reservation_id` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

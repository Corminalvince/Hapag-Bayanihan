-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2025 at 01:36 PM
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
-- Database: `hapagbayanihan`
--

-- --------------------------------------------------------

--
-- Table structure for table `catering_requests`
--

CREATE TABLE `catering_requests` (
  `catering_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `food_id` int(100) NOT NULL,
  `requested_at` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `food_id` int(100) NOT NULL,
  `kitchen_id` int(11) NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `image_path` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `carinderia_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foods`
--

INSERT INTO `foods` (`food_id`, `kitchen_id`, `food_name`, `description`, `price`, `quantity`, `image_path`, `latitude`, `longitude`, `carinderia_name`, `created_at`) VALUES
(1, 5, 'Chicken Adobo', 'An iconic Filipino dish, this chicken adobo recipe features succulent chicken simmered in a bright vinegar-soy sauce enriched with creamy coconut milk. It’s tangy, savory, and deeply comforting—the kind of craveable dinner your family will ask for again and again.', 125.00, 73, 'uploads/OSK (2).jpg', 0.0000000, 0.0000000, 'Carlo\'s Carinderias', '2025-05-19 00:31:03'),
(2, 7, 'Sweet and Sour Pork', 'This Sweet and Sour Pork Recipe is all that you need to make a good Sweet and Sour Pork dish. It is simple, straightforward, and easy to follow. The result is absolutely delicious.', 165.00, 100, 'uploads/sweet and sour pork.jpg', 9.9478705, 123.9607315, 'Trisha\'S Carinderias', '2025-05-20 08:10:54'),
(3, 7, 'Bicol Express', 'Bicol Express is a creamy stew that combines spice and sweetness in a well-balanced manner. The dish makes use of ginger and local seasonings resulting in a flavorful combination of meat and sauce.', 125.00, 100, 'uploads/bicol.jpg', 9.9458754, 123.9615898, 'Trisha\'S Carinderias', '2025-05-20 12:31:40'),
(4, 7, 'Pinakbet ', 'Pinakbet Tagalog is a Filipino vegetable dish. It is composed of a variety of vegetables and it also has a protein component. I made use of lechon kawali or crispy deep-fried pork belly for this recipe. This recipe is a variation of the popular Pinakbet Ilocano.', 90.00, 98, 'uploads/pinagkatbet.jpg', 9.9479794, 123.9619675, 'Trisha\'S Carinderias', '2025-05-20 12:32:30'),
(5, 5, 'Dinuguan', 'Dinuguan, also known as chocolate meat, is a savory dish made with diced pork, pork blood, and spices. This classic Filipino pork stew is hearty, boldly flavored, and delicious as a main meal with steamed rice or a midday snack with puto.', 50.00, 100, 'uploads/dinuguan.jpg', 0.0000000, 0.0000000, 'Carlo\'s Carinderias', '2025-05-22 07:20:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `change` decimal(10,2) DEFAULT NULL,
  `location` text NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'Pending Driver Confirmation',
  `order_date` datetime DEFAULT current_timestamp(),
  `assigned_at` datetime DEFAULT current_timestamp(),
  `voucher_id` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `id`, `food_id`, `quantity`, `total_price`, `payment_method`, `payment_amount`, `change`, `location`, `driver_id`, `status`, `order_date`, `assigned_at`, `voucher_id`) VALUES
(7, 2, 1, 2, 250.00, 'Cash on Delivery', 250.00, 0.00, 'Bentig, Calape,Bohol', 4, 'Pending', '2025-05-22 15:13:19', '2025-05-22 15:13:19', 0),
(8, 2, 1, 2, 250.00, 'Cash on Delivery', NULL, NULL, 'Bentig, Calape,Bohol', NULL, 'Pending Driver Confirmation', '2025-05-22 15:15:43', '2025-05-22 15:15:43', 0),
(9, 3, 4, 2, 180.00, 'Cash on Delivery', 180.00, 0.00, 'Bentig, Calape,Bohol', 3, 'Accepted and rider will pick up and it will deliver to you', '2025-05-22 15:17:54', '2025-05-22 15:17:54', 0),
(10, 2, 5, 1, 50.00, 'Cash on Delivery', NULL, NULL, 'Tagbilaran North Road, Abucayan Sur, Bohol, Central Visayas, 6328, Philippines', 3, 'Pending', '2025-05-22 15:22:35', '2025-05-22 15:22:35', 0),
(11, 2, 3, 1, 125.00, 'Cash on Delivery', NULL, NULL, 'Tagbilaran North Road, Abucayan Sur, Bohol, Central Visayas, 6328, Philippines', 4, 'Pending', '2025-05-22 15:22:35', '2025-05-22 15:22:35', 0);

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `id` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `ContactNumber` varchar(20) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `User_type` varchar(20) NOT NULL DEFAULT 'user',
  `RegistrationDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration`
--

INSERT INTO `registration` (`id`, `FirstName`, `LastName`, `ContactNumber`, `Username`, `Email`, `Password`, `User_type`, `RegistrationDate`, `profile_image`) VALUES
(1, 'Joe Lito Vince', 'Corminal', '09276992343', 'corminaljoelito10', 'corminaljoelito10@gmail.com', '$2y$10$if7TMVl41oimk7KwkZ32d.Xedu/aVVyVYJuOnB/SXSFcQViLWqlZO', 'admin', '2025-05-19 00:22:13', 'default.png'),
(2, 'Pauline', 'Quindo', '09925335362', 'pauline2', 'pauline@gmail.com', '$2y$10$LQjg.c75U4V3Gwb8C2h7oeHqzsKEuDVx.MOPjcyxeP4rTjYzMrC5y', 'user', '2025-05-19 00:22:40', 'user_2.jpg'),
(3, 'Joerock', 'Valleser', '09276992343', 'joerock12', 'joerock@gmail.com', '$2y$10$zqIBjsKGdRsTtjWEI7pqSuQNntT4DYlWn7J/GxmVs38AW2KGSTs1O', 'driver', '2025-05-19 00:23:05', 'default.png'),
(4, 'John Valentine', 'Estrada', '09923434323', 'valentine21', 'valentine@gmail.com', '$2y$10$eJ5/JMylxde1lXQgw5EWm.9LfXyUk2ziGD7zlnIZGnh/LOfFT1Cxm', 'driver', '2025-05-19 00:23:28', 'default.png'),
(5, 'Carlo', 'Gelicame', '09267774567', 'carlogelicame', 'carlo@gmail.com', '$2y$10$DH29c.KJ8lM/U6wgzWn3AeP0sdnT6qCt3idK8nyouYZSTzmag7xRC', 'kitchen', '2025-05-19 00:24:34', 'default.png'),
(6, 'Ritchie', 'Mapulak', '0943537232323', 'ritchie12', 'ritchie@gmail.com', 'ritch123', 'kitchen', '2025-05-20 08:06:43', 'default.png'),
(7, 'Trishia Mae', 'Yagong', '09276992343', 'trisha12', 'trish@gmail.com', '$2y$10$s5nT8Ppu4pS72u0swbknHu4Bt37mM65a3oCqRSogD2XqJ50bgQwyK', 'kitchen', '2025-05-20 08:08:48', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `registration_requests`
--

CREATE TABLE `registration_requests` (
  `request_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_requests`
--

INSERT INTO `registration_requests` (`request_id`, `name`, `message`, `submitted_at`, `email`, `phone`, `role`) VALUES
(2, 'Pauline Quindo', 'can you make me a driver\r\n', '2025-05-22 10:31:51', 'pauline@gmail.com', '09925335362', 'driver');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount` decimal(5,2) NOT NULL,
  `expiry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `code`, `discount`, `expiry_date`) VALUES
(1, 'SAVE10', 10.00, '2025-06-30'),
(2, 'WELCOME20', 20.00, '2025-07-15'),
(3, 'FREESHIP', 0.00, '2025-08-01'),
(4, 'BUNDLE5', 5.00, '2025-06-01'),
(5, 'SUMMER25', 25.00, '2025-09-30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `catering_requests`
--
ALTER TABLE `catering_requests`
  ADD PRIMARY KEY (`catering_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`food_id`),
  ADD KEY `kitchen_id` (`kitchen_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `id` (`id`),
  ADD KEY `food_id` (`food_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `registration_requests`
--
ALTER TABLE `registration_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `catering_requests`
--
ALTER TABLE `catering_requests`
  MODIFY `catering_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `foods`
--
ALTER TABLE `foods`
  MODIFY `food_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `registration`
--
ALTER TABLE `registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `registration_requests`
--
ALTER TABLE `registration_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `foods`
--
ALTER TABLE `foods`
  ADD CONSTRAINT `foods_ibfk_1` FOREIGN KEY (`kitchen_id`) REFERENCES `registration` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id`) REFERENCES `registration` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `foods` (`food_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `registration` (`ID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

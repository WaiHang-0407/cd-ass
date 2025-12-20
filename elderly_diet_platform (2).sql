-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 07:08 PM
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
-- Database: `elderly_diet_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `adminID` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`adminID`) VALUES
('U_69456611bd22d');

-- --------------------------------------------------------

--
-- Table structure for table `caretakers`
--

CREATE TABLE `caretakers` (
  `caretakerID` varchar(50) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `emergencyContact` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dietitians`
--

CREATE TABLE `dietitians` (
  `dietitianID` varchar(50) NOT NULL,
  `qualification` text DEFAULT NULL,
  `licenseNo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dietitians`
--

INSERT INTO `dietitians` (`dietitianID`, `qualification`, `licenseNo`) VALUES
('U_6945662a244b0', '[]', '1212'),
('U_694693812f6a3', '[\"PhD Nutrition\"]', 'D-1001'),
('U_694693812ff86', '[\"MSc Dietetics\"]', 'D-1002');

-- --------------------------------------------------------

--
-- Table structure for table `diet_plans`
--

CREATE TABLE `diet_plans` (
  `dietPlanID` varchar(50) NOT NULL,
  `elderlyID` varchar(50) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_plans`
--

INSERT INTO `diet_plans` (`dietPlanID`, `elderlyID`, `createdAt`) VALUES
('DP_6946350deaa6f', 'U_6943abac29ea8', '2025-12-20 13:33:01'),
('DP_6946e5cbf21c1', 'U_6946e59b595dc', '2025-12-21 02:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `diet_plan_approvals`
--

CREATE TABLE `diet_plan_approvals` (
  `approvalID` varchar(50) NOT NULL,
  `dietPlanID` varchar(50) NOT NULL,
  `dietitianID` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Approved','Revise') NOT NULL DEFAULT 'Pending',
  `approvalDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diet_plan_approvals`
--

INSERT INTO `diet_plan_approvals` (`approvalID`, `dietPlanID`, `dietitianID`, `status`, `approvalDate`) VALUES
('AP_6946350deadd2', 'DP_6946350deaa6f', 'U_6945662a244b0', 'Approved', '2025-12-20 14:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `elderly`
--

CREATE TABLE `elderly` (
  `elderlyID` varchar(50) NOT NULL,
  `caretakerID` varchar(50) DEFAULT NULL,
  `assignedDietitianID` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elderly`
--

INSERT INTO `elderly` (`elderlyID`, `caretakerID`, `assignedDietitianID`) VALUES
('U_6943abac29ea8', NULL, NULL),
('U_6946e59b595dc', NULL, 'U_694693812f6a3');

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `foodID` varchar(50) NOT NULL,
  `mealID` varchar(50) NOT NULL,
  `recipeID` varchar(50) DEFAULT NULL,
  `foodName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foods`
--

INSERT INTO `foods` (`foodID`, `mealID`, `recipeID`, `foodName`) VALUES
('F_6946352a5e626', 'M_6946352a5e2ef', 'R_6946352a5e44b', 'Oatmeal with Berries and Nuts'),
('F_6946352a5f2b8', 'M_6946352a5e6db', 'R_6946352a5e7ad', 'Chicken and Vegetable Stir-fry (Chinese)'),
('F_6946352a5f8e4', 'M_6946352a5f35a', 'R_6946352a5f7f1', 'Steamed Fish with Bok Choy and Ginger'),
('F_6946352a5fb6f', 'M_6946352a5f9b1', 'R_6946352a5fa82', 'Scrambled Eggs with Spinach and Whole-Wheat Toast'),
('F_6946352a5fd9a', 'M_6946352a5fbfe', 'R_6946352a5fc9c', 'Malay Chicken Curry (light version) with Brown Rice'),
('F_6946352a5ffd0', 'M_6946352a5fe30', 'R_6946352a5fee3', 'Tofu and Vegetable Soup'),
('F_6946352a601ea', 'M_6946352a6005e', 'R_6946352a600fb', 'Smoothie (Spinach, Banana, Almond Milk)'),
('F_6946352a6043e', 'M_6946352a6026f', 'R_6946352a60353', 'Salmon Salad with Mixed Greens'),
('F_6946352a60671', 'M_6946352a604d2', 'R_6946352a60579', 'Stir-fried Beef with Broccoli and Noodles (Chinese)'),
('F_6946352a608ca', 'M_6946352a6071e', 'R_6946352a607cb', 'Whole Wheat Pancakes (2) with Berries'),
('F_6946352a60b1a', 'M_6946352a60973', 'R_6946352a60a1d', 'Chicken Nasi Lemak (light version)'),
('F_6946352a60e1e', 'M_6946352a60ba7', 'R_6946352a60c43', 'Lentil Soup with Whole-Wheat Bread'),
('F_6946352a610b8', 'M_6946352a60ec6', 'R_6946352a60fac', 'Yogurt Parfait (Greek Yogurt, Granola, Berries)'),
('F_6946352a61386', 'M_6946352a61174', 'R_6946352a61265', 'Vegetable and Tofu Spring Rolls (Chinese)'),
('F_6946352a615c4', 'M_6946352a61426', 'R_6946352a614cf', 'Baked Chicken with Roasted Vegetables'),
('F_6946352a61805', 'M_6946352a61664', 'R_6946352a61712', 'Breakfast Burrito (Whole Wheat Tortilla, Egg, Veggies)'),
('F_6946352a61a07', 'M_6946352a6189f', 'R_6946352a61936', 'Beef Rendang (light version) with Cauliflower Rice'),
('F_6946352a61beb', 'M_6946352a61a8a', 'R_6946352a61b1d', 'Shrimp and Vegetable Stir-fry (Chinese)'),
('F_6946352a61e23', 'M_6946352a61c6f', 'R_6946352a61d4f', 'Cereal (Whole Grain) with Almond Milk and Berries'),
('F_6946352a620b7', 'M_6946352a61ea6', 'R_6946352a61f32', 'Chicken Salad Sandwich (Whole Wheat Bread)'),
('F_6946352a622fc', 'M_6946352a62166', 'R_6946352a62206', 'Steamed Fish with Brown Rice and Green Beans');

-- --------------------------------------------------------

--
-- Table structure for table `food_logs`
--

CREATE TABLE `food_logs` (
  `logID` varchar(50) NOT NULL,
  `elderlyID` varchar(50) NOT NULL,
  `foodName` varchar(100) NOT NULL,
  `calories` double DEFAULT 0,
  `protein` double DEFAULT 0,
  `carbs` double DEFAULT 0,
  `fibre` double DEFAULT 0,
  `sugar` double DEFAULT 0,
  `sodium` double DEFAULT 0,
  `loggedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_logs`
--

INSERT INTO `food_logs` (`logID`, `elderlyID`, `foodName`, `calories`, `protein`, `carbs`, `fibre`, `sugar`, `sodium`, `loggedAt`) VALUES
('L_6946400be147f', 'U_6943abac29ea8', 'Breakfast', 380, 12, 55, 8, 15, 150, '2025-12-20 14:19:55'),
('L_6946ddbd20f53', 'U_6943abac29ea8', 'Nasi Lemak', 850, 35, 110, 8, 15, 1200, '2025-12-21 01:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `mealID` varchar(50) NOT NULL,
  `dietPlanID` varchar(50) NOT NULL,
  `mealType` varchar(50) NOT NULL,
  `totalCalories` double DEFAULT 0,
  `totalFibre` double DEFAULT 0,
  `totalProtein` double DEFAULT 0,
  `totalCarbs` double DEFAULT 0,
  `totalSodium` double DEFAULT 0,
  `totalCholesterol` double DEFAULT 0,
  `day` int(11) NOT NULL DEFAULT 1,
  `totalSugar` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`mealID`, `dietPlanID`, `mealType`, `totalCalories`, `totalFibre`, `totalProtein`, `totalCarbs`, `totalSodium`, `totalCholesterol`, `day`, `totalSugar`) VALUES
('M_6946352a5e2ef', 'DP_6946350deaa6f', 'Breakfast', 380, 8, 12, 55, 150, 0, 1, 15),
('M_6946352a5e6db', 'DP_6946350deaa6f', 'Lunch', 450, 7, 35, 40, 550, 0, 1, 5),
('M_6946352a5f35a', 'DP_6946350deaa6f', 'Dinner', 420, 6, 30, 35, 300, 0, 1, 3),
('M_6946352a5f9b1', 'DP_6946350deaa6f', 'Breakfast', 350, 5, 25, 25, 250, 0, 2, 2),
('M_6946352a5fbfe', 'DP_6946350deaa6f', 'Lunch', 480, 8, 30, 55, 600, 0, 2, 7),
('M_6946352a5fe30', 'DP_6946350deaa6f', 'Dinner', 400, 10, 20, 40, 200, 0, 2, 5),
('M_6946352a6005e', 'DP_6946350deaa6f', 'Breakfast', 320, 8, 10, 50, 50, 0, 3, 25),
('M_6946352a6026f', 'DP_6946350deaa6f', 'Lunch', 460, 6, 35, 20, 350, 0, 3, 3),
('M_6946352a604d2', 'DP_6946350deaa6f', 'Dinner', 430, 7, 30, 45, 400, 0, 3, 8),
('M_6946352a6071e', 'DP_6946350deaa6f', 'Breakfast', 370, 8, 10, 60, 200, 0, 4, 15),
('M_6946352a60973', 'DP_6946350deaa6f', 'Lunch', 470, 7, 32, 50, 580, 0, 4, 10),
('M_6946352a60ba7', 'DP_6946350deaa6f', 'Dinner', 410, 12, 22, 50, 250, 0, 4, 5),
('M_6946352a60ec6', 'DP_6946350deaa6f', 'Breakfast', 360, 7, 20, 45, 120, 0, 5, 18),
('M_6946352a61174', 'DP_6946350deaa6f', 'Lunch', 440, 8, 25, 40, 450, 0, 5, 6),
('M_6946352a61426', 'DP_6946350deaa6f', 'Dinner', 420, 7, 35, 30, 320, 0, 5, 4),
('M_6946352a61664', 'DP_6946350deaa6f', 'Breakfast', 380, 8, 20, 40, 280, 0, 6, 5),
('M_6946352a6189f', 'DP_6946350deaa6f', 'Lunch', 460, 6, 35, 35, 550, 0, 6, 8),
('M_6946352a61a8a', 'DP_6946350deaa6f', 'Dinner', 410, 7, 30, 35, 380, 0, 6, 6),
('M_6946352a61c6f', 'DP_6946350deaa6f', 'Breakfast', 340, 10, 10, 60, 150, 0, 7, 15),
('M_6946352a61ea6', 'DP_6946350deaa6f', 'Lunch', 450, 8, 30, 40, 400, 0, 7, 5),
('M_6946352a62166', 'DP_6946350deaa6f', 'Dinner', 400, 8, 30, 40, 300, 0, 7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `messageID` varchar(50) NOT NULL,
  `senderID` varchar(50) NOT NULL,
  `receiverID` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `sentAt` datetime NOT NULL DEFAULT current_timestamp(),
  `isRead` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`messageID`, `senderID`, `receiverID`, `message`, `sentAt`, `isRead`) VALUES
('MSG_6946e5ec3e075', 'U_6946e59b595dc', 'U_694693812f6a3', 'Hello', '2025-12-21 02:07:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `profileID` varchar(50) NOT NULL,
  `elderlyID` varchar(50) NOT NULL,
  `height` double NOT NULL,
  `weight` double NOT NULL,
  `bmi` double NOT NULL,
  `allergies` text DEFAULT NULL,
  `healthCondition` text DEFAULT NULL,
  `caloriesLimit` double DEFAULT 0,
  `carbsLimit` double DEFAULT 0,
  `sugarLimit` double DEFAULT 0,
  `sodiumLimit` double DEFAULT 0,
  `fibreRequirement` double DEFAULT 0,
  `softFoodRequirement` tinyint(1) DEFAULT 0,
  `medicationList` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`profileID`, `elderlyID`, `height`, `weight`, `bmi`, `allergies`, `healthCondition`, `caloriesLimit`, `carbsLimit`, `sugarLimit`, `sodiumLimit`, `fibreRequirement`, `softFoodRequirement`, `medicationList`) VALUES
('P_6943ade09ffc3', 'U_6943abac29ea8', 166, 66, 23.951226593119, '[\"\"]', '[\"Diabetes\"]', 1647, 185, 20, 2300, 25, 0, '[\"\"]'),
('P_6946e5c654f03', 'U_6946e59b595dc', 180, 60, 18.518518518519, '[\"\"]', '[\"Diabetes\"]', 1776, 200, 20, 2300, 25, 0, '[\"\"]');

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `progressID` varchar(50) NOT NULL,
  `elderlyID` varchar(50) NOT NULL,
  `dietPlanID` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `caloriesTaken` double DEFAULT 0,
  `proteinTaken` double DEFAULT 0,
  `carbohydrateTaken` double DEFAULT 0,
  `fatTaken` double DEFAULT 0,
  `fiberTaken` double DEFAULT 0,
  `sodiumTaken` double DEFAULT 0,
  `sugarTaken` double DEFAULT 0,
  `waterIntake` double DEFAULT 0,
  `mealTextureType` varchar(50) DEFAULT NULL,
  `mealCompletedCount` int(11) DEFAULT 0,
  `progressStatus` varchar(50) DEFAULT NULL,
  `state` enum('Green','Yellow','Red') DEFAULT 'Green',
  `lastUpdated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`progressID`, `elderlyID`, `dietPlanID`, `date`, `caloriesTaken`, `proteinTaken`, `carbohydrateTaken`, `fatTaken`, `fiberTaken`, `sodiumTaken`, `sugarTaken`, `waterIntake`, `mealTextureType`, `mealCompletedCount`, `progressStatus`, `state`, `lastUpdated`) VALUES
('PG_69463537081e5', 'U_6943abac29ea8', 'DP_6946350deaa6f', '2025-12-20', 2460, 94, 330, 0, 32, 2450, 90, 2.3, NULL, 0, NULL, 'Red', '2025-12-21 01:32:45'),
('PG_6946e5cbf24ed', 'U_6946e59b595dc', 'DP_6946e5cbf21c1', '2025-12-20', 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, 'Green', '2025-12-21 02:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipeID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ingredients` text NOT NULL,
  `calories` double NOT NULL,
  `protein` double NOT NULL,
  `carbs` double NOT NULL,
  `fibre` double NOT NULL,
  `fat` double NOT NULL,
  `sodium` double NOT NULL,
  `preference` varchar(50) DEFAULT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipeID`, `name`, `ingredients`, `calories`, `protein`, `carbs`, `fibre`, `fat`, `sodium`, `preference`, `instructions`) VALUES
('R_6943adef0a466', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943adef71892', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943adefc01c9', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3515a4d', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae351a3e7', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae351f12d', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae378c4be', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae379200b', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae37957a0', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3b571b4', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3b5d080', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3b62f6f', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3c9b5c0', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3c9f489', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943ae3ca45e9', 'Standard Balanced Meal (AI Unreachable - Using Approved Backup)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aec2d6972', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aec2dc1a6', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aec2e1caf', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aee0ec2ad', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aee112c2f', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aee12451f', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aefa0dede', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aefa27f00', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aefa37fbb', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af11338eb', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af114b9df', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af11649f2', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af3ac1db6', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af3adc8d6', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af3b012e7', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af4a2f021', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af4a4764b', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af4a5da03', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af5998ffc', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af59b1f53', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af59cbce5', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af5ada148', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af5aea165', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943af5b08006', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943afa3059bc', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943afa31e0be', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943afa335c70', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943afa43db36', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943afa455549', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943afa46591a', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aff8cd07a', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aff8e4255', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aff907cd6', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943aff9edd64', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943affa098d5', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943affa20b65', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b01d0cdab', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b01d3cfba', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b01d70b42', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b01e3c55e', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b01e4c308', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b01e66f7f', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b02f169aa', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b02f37a75', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b02f48785', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b03856a19', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b03869a97', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b0389168e', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b1175f9fe', 'Gentle Salmon & Sweet Potato Bake', '[\"Salmon fillet (120g)\",\"Sweet potato (1 medium, 180g)\",\"Asparagus spears (8-10)\",\"Olive oil (1 tsp)\",\"Lemon wedge (for serving)\",\"Pinch of salt\",\"Black pepper (to taste)\"]', 450, 30, 40, 8, 19, 150, NULL, NULL),
('R_6943b11edfb7f', 'Mediterranean Baked Salmon with Sweet Potato Mash', '[\"110g salmon fillet\",\"1 medium sweet potato (approx. 150g)\",\"1 cup fresh green beans (approx. 100g)\",\"1 tsp olive oil\",\"Juice of 1\\/4 lemon\",\"Pinch of dried dill (optional)\",\"Black pepper to taste\"]', 450, 30, 40, 7, 18, 120, NULL, NULL),
('R_6943b12949241', 'Gentle Salmon & Sweet Potato Mash', '[\"1 (4oz) salmon fillet, baked or steamed until flaky\",\"1 medium sweet potato, steamed until very soft\",\"1 cup fresh spinach, lightly steamed\",\"2 tablespoons low-fat milk (or unsweetened plant-based milk)\",\"1 teaspoon olive oil\",\"Pinch of garlic powder\",\"Pinch of black pepper\"]', 480, 32, 40, 7, 16, 180, NULL, NULL),
('R_6943b13f7aca1', 'Gentle Baked Salmon with Sweet Potato & Spinach Mash', '[\"Salmon fillet (4 oz)\",\"Sweet potato (1 medium)\",\"Fresh spinach (1 cup, packed)\",\"Olive oil (1 tsp)\",\"Lemon juice (1 tbsp)\",\"Low-sodium chicken broth or water (2 tbsp)\",\"Pinch of salt\",\"Pinch of black pepper\"]', 450, 35, 40, 8, 20, 250, NULL, NULL),
('R_6943b148418c1', 'Creamy Salmon & Sweet Potato Mash', '[\"Salmon fillet (skinless, boneless, 100g)\",\"Sweet potato (1 medium, ~150g)\",\"Frozen peas (1\\/4 cup)\",\"Low-sodium chicken or vegetable broth (1\\/4 cup)\",\"Olive oil (1 tsp)\",\"Fresh dill or parsley (1 tsp, chopped, optional)\",\"Pinch of black pepper\"]', 430, 28, 38, 7, 17, 180, NULL, NULL),
('R_6943b14975589', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b227e4447', 'Gentle Salmon & Sweet Potato Mash', '[\"4 oz (113g) salmon fillet, skin removed\",\"1 medium sweet potato (approx. 150g), peeled and diced\",\"1 cup fresh spinach\",\"1\\/4 cup low-sodium vegetable broth\",\"1 tsp olive oil\",\"Pinch of black pepper\"]', 420, 29, 32, 5, 19, 180, NULL, NULL),
('R_6943b22d97b0a', 'Standard Balanced Meal (AI Unreachable - Check Log/Quota)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6943b2b342441', 'Salmon and Sweet Potato Mash with Spinach', '[\"4 oz (113g) baked or steamed salmon fillet, flaked\",\"1 medium sweet potato (approx. 150g), boiled and mashed\",\"1 cup fresh spinach, steamed and finely chopped or pureed\",\"2 tablespoons low-sodium chicken or vegetable broth\",\"1 teaspoon olive oil\",\"Pinch of black pepper (optional)\"]', 450, 30, 45, 8, 18, 280, NULL, NULL),
('R_6943b2bfabe62', 'Gentle Chicken & Root Vegetable Puree', '[\"1 boneless, skinless chicken breast (about 5-6 oz)\",\"1 medium sweet potato, peeled and diced\",\"2 medium carrots, peeled and diced\",\"2 cups fresh spinach\",\"1 cup low-sodium chicken broth\",\"1 tablespoon olive oil\",\"2 tablespoons unsweetened almond milk (or regular milk)\",\"Pinch of black pepper\"]', 530, 43, 48, 10, 18, 340, NULL, NULL),
('R_6943b2cbcf22d', 'Creamy Salmon & Spinach Mash', '[\"100g cooked salmon fillet, flaked\",\"1 medium potato (about 150g), boiled and mashed\",\"50g fresh spinach, steamed and finely chopped\",\"50ml low-fat milk or unsweetened almond milk\",\"1 tsp olive oil\",\"Pinch of black pepper\"]', 400, 31, 31, 4, 17, 140, NULL, NULL),
('R_6943b32fb5980', 'Creamy Salmon & Spinach Mash', '[\"120g salmon fillet, skinless\",\"1 medium sweet potato (about 200g)\",\"1 cup fresh spinach\",\"2 tbsp low-fat milk\",\"1 tsp olive oil\",\"1\\/4 tsp dried dill\",\"Pinch of black pepper\"]', 440, 29, 33, 5, 19, 150, NULL, NULL),
('R_6943b33bd1f8d', 'Creamy Salmon & Sweet Potato Puree', '[\"4 oz (113g) salmon fillet, cooked (baked or poached)\",\"1 medium (approx. 150g) sweet potato, peeled and cooked until very soft\",\"1 cup packed fresh spinach, steamed or wilted\",\"1\\/2 cup (120ml) low-sodium chicken or vegetable broth\",\"2 tablespoons whole milk (or fortified plant-based milk)\",\"1 teaspoon olive oil\",\"Pinch of black pepper\"]', 450, 30, 34, 5, 21, 225, NULL, NULL),
('R_6943b34a1b6aa', 'Gentle Baked Salmon with Sweet Potato Mash', '[\"Salmon fillet (4 oz)\",\"Sweet potato (medium)\",\"Green beans (1 cup)\",\"Olive oil (1 tsp)\",\"Dried dill\",\"Black pepper\",\"Pinch of sea salt (optional)\"]', 440, 27, 39, 7, 18, 215, NULL, NULL),
('R_6943b3908f034', 'Gentle Salmon & Sweet Potato Mash', '[\"113g (4 oz) salmon fillet\",\"1 medium sweet potato\",\"1 cup fresh spinach\",\"1 tsp olive oil\",\"2 tbsp low-fat milk (or vegetable broth)\",\"Pinch of salt\",\"Pinch of black pepper\",\"Lemon wedge (optional)\"]', 420, 28, 37, 5, 18, 195, NULL, NULL),
('R_6943b3a2614e7', 'Gentle Baked Salmon with Soft Sweet Potato and Steamed Green Beans', '[\"Salmon fillet, 4 oz (112g)\",\"Medium sweet potato, 1 (about 150g)\",\"Fresh green beans, 1 cup (about 100g)\",\"Olive oil, 1 teaspoon\",\"Pinch of salt (optional, to taste)\",\"Pinch of black pepper (optional, to taste)\"]', 435, 29, 38, 7, 18, 115, NULL, NULL),
('R_6943b3ada08f9', 'Soft Salmon & Sweet Potato Mash with Creamy Spinach', '[\"100g cooked salmon fillet, flaked\",\"1 medium sweet potato (approx. 150g), steamed and mashed\",\"100g fresh spinach, steamed and pureed\",\"50ml whole milk (or unsweetened almond milk)\",\"1 teaspoon olive oil\",\"Pinch of salt (optional, to taste)\",\"Pinch of black pepper (optional, to taste)\"]', 425, 27, 36, 6, 20, 200, NULL, NULL),
('R_69442b490cb3c', 'Gentle Poached Salmon with Steamed Green Beans and Sweet Potato Mash', '[\"Salmon fillet (approx. 4 oz \\/ 113g)\",\"Sweet potato (approx. 1 small \\/ 100g)\",\"Green beans (approx. 1\\/2 cup \\/ 60g)\",\"Olive oil (1 tsp)\",\"Fresh dill (1\\/2 tsp)\",\"Pinch of salt (optional)\",\"Lemon wedge (optional)\"]', 380, 28, 25, 5, 16, 140, NULL, NULL),
('R_69442b55bee7a', 'Gentle Baked Salmon with Roasted Vegetables', '[\"4 oz Salmon Fillet\",\"1\\/2 medium Sweet Potato (diced)\",\"1 cup Broccoli Florets\",\"1 tbsp Olive Oil\",\"Pinch of Dried Dill\",\"Salt-Free Seasoning Blend (optional)\",\"1\\/4 cup Low-Sodium Broth\"]', 480, 29, 26, 5, 26, 185, NULL, NULL),
('R_69442b63a7006', 'Gentle Salmon & Sweet Potato Medley', '[\"3.5 oz (100g) baked or steamed salmon fillet, flaked\",\"1 medium sweet potato (150g), baked or boiled until very soft, mashed\",\"1 cup fresh spinach, wilted\",\"1 tbsp extra virgin olive oil\",\"1\\/4 tsp dried dill or parsley\",\"Pinch of black pepper\"]', 470, 27, 38, 6, 25, 120, NULL, NULL),
('R_69442bee95871', 'Gentle Salmon & Sweet Potato Bake', '[\"1 Salmon fillet (approx. 4-5 oz \\/ 115-140g)\",\"1 medium Sweet potato\",\"6-8 Asparagus spears\",\"1 tablespoon Olive oil\",\"1 tablespoon Lemon juice\",\"1 teaspoon Fresh dill (or 1\\/2 tsp dried)\",\"Pinch of sea salt\",\"Pinch of black pepper\"]', 550, 35, 35, 6, 30, 220, NULL, NULL),
('R_69442bf9920f9', 'Gentle Baked Salmon with Sweet Potato & Asparagus', '[\"1 (4 oz \\/ 113g) salmon fillet\",\"1 medium sweet potato (approx. 150g), peeled and diced\",\"1 cup fresh asparagus (approx. 180g), tough ends trimmed\",\"1 tbsp olive oil\",\"1 tsp fresh lemon juice\",\"1\\/4 tsp dried dill or parsley\",\"Pinch of salt (e.g., 1\\/8 tsp)\",\"Pinch of black pepper\"]', 490, 30, 38, 8, 26, 180, NULL, NULL),
('R_69442c0adcaae', 'Lemon Herb Baked Salmon with Soft Roasted Vegetables', '[\"Salmon fillet (4 oz)\",\"Broccoli florets (1 cup)\",\"Carrots (1 medium, sliced thin)\",\"Red bell pepper (1\\/2, sliced)\",\"Olive oil (1 tbsp)\",\"Fresh lemon juice (1 tbsp)\",\"Fresh dill (1 tsp, chopped)\",\"Black pepper (1\\/4 tsp)\",\"Pinch of sea salt\"]', 450, 28, 22, 8, 28, 315, NULL, NULL),
('R_69442c4abe2ec', 'Bubur Ayam Sihat (Healthy Chicken Porridge)', '[\"1\\/3 cup uncooked rice (white or mixed with brown)\",\"2.5 cups low-sodium chicken broth (or water)\",\"70g boneless, skinless chicken breast, shredded or diced\",\"1 small carrot, finely diced\",\"1 inch ginger, thinly sliced\",\"1 clove garlic, minced\",\"1\\/2 cup finely chopped spinach or bok choy\",\"1 tsp low-sodium light soy sauce\",\"Pinch of white pepper\",\"1 tbsp fresh coriander, chopped (for garnish)\",\"1 tsp fried shallots (optional, for garnish)\"]', 350, 25, 50, 4, 3, 350, NULL, NULL),
('R_69442c745d560', 'Bubur Ayam Sihat', '[\"1\\/4 cup white rice\",\"100g skinless, boneless chicken breast\",\"1 cup low-sodium chicken broth\",\"1.5 cups water\",\"1-inch fresh ginger, finely grated\",\"1 clove garlic, minced\",\"1\\/4 cup carrot, finely diced\",\"1\\/4 cup green peas, cooked until soft\",\"1 tsp light soy sauce (low sodium)\",\"A pinch of white pepper\",\"1 tbsp fresh coriander or spring onion, chopped (for garnish)\",\"1\\/4 tsp sesame oil (optional)\"]', 390, 37, 42, 3, 4, 400, NULL, NULL),
('R_69442c86ceaa8', 'Ikan Bakar Sihat dengan Nasi Putih & Sup Sayur Bening', '[\"150g Ikan Kembung (Indian Mackerel), cleaned\",\"1 tsp Turmeric powder\",\"Pinch of salt (for fish marinade)\",\"1 cup cooked White Rice\",\"50g Choy Sum (Sawi), cut into 2-inch pieces\",\"30g Carrots, thinly sliced\",\"20g Enoki mushrooms (optional)\",\"1 clove Garlic, sliced thinly\",\"1\\/2 Shallot, sliced thinly\",\"2 cups Water\",\"Pinch of salt (for soup)\",\"Dash of white pepper\"]', 540, 41, 53, 5, 16, 220, NULL, NULL),
('R_69442caa9f4ee', 'Ikan Bakar Sihat dengan Sayur Bening', '[\"180g white fish fillet (e.g., Tilapia, Seabass, Mackerel), skinless and boneless\",\"1 tbsp lime juice\",\"1 tsp turmeric powder\",\"1\\/4 tsp salt\",\"Pinch of black pepper\",\"1 tsp olive oil (for brushing fish)\",\"120g cooked white rice (approximately 1\\/2 cup uncooked rice)\",\"200g mixed soft vegetables (e.g., spinach, pumpkin cubes, soft gourd), cleaned and chopped\",\"200ml water or low-sodium vegetable broth\",\"1 small shallot, thinly sliced\",\"1\\/2 clove garlic, thinly sliced\",\"Pinch of salt\",\"Pinch of white pepper\"]', 450, 45, 50, 4, 8, 300, NULL, NULL),
('R_69442d24bdd30', 'Soft Moong Dal Cheela with Vegetables', '[\"1\\/3 cup Yellow Moong Dal (split peeled lentils), soaked for 2-3 hours\",\"1\\/4 inch fresh Ginger, grated\",\"1\\/4 tsp Cumin Seeds\",\"Pinch of Turmeric Powder\",\"Salt to taste (low sodium recommended)\",\"1.5 tbsp grated Carrot\",\"1.5 tbsp finely chopped Spinach\",\"1 tsp cooking oil (e.g., olive or canola)\",\"Water as needed for grinding and batter\"]', 270, 16, 40, 5, 6, 220, NULL, NULL),
('R_69442d3362054', 'Gentle Spinach Masoor Dal with Jeera Rice', '[\"Red Lentils (Masoor Dal), 1\\/4 cup dry\",\"Fresh Spinach, 1 cup chopped\",\"Basmati Rice, 1\\/3 cup dry\",\"Ghee, 2 tsp\",\"Cumin Seeds, 1\\/2 tsp divided\",\"Turmeric Powder, 1\\/4 tsp\",\"Asafoetida (Hing), a pinch\",\"Fresh Ginger, 1\\/2 inch grated\",\"Garlic, 1 clove minced (optional, for milder taste)\",\"Salt, to taste (low sodium)\",\"Fresh Coriander, 1 tbsp chopped (for garnish)\"]', 480, 16, 72, 9, 11, 200, NULL, NULL),
('R_69442d446991f', 'Gentle Vegetable Moong Dal Khichdi', '[\"1\\/4 cup Moong Dal (Split Yellow Lentils), washed and soaked for 15-20 minutes\",\"1\\/4 cup Basmati Rice (or Sona Masoori Rice), washed and soaked for 15-20 minutes\",\"1\\/2 cup mixed vegetables (finely chopped carrots, peas, spinach)\",\"1 tsp Ghee (Clarified Butter)\",\"1\\/2 tsp Cumin Seeds\",\"1 tsp Fresh Ginger, grated\",\"1\\/4 tsp Turmeric Powder\",\"Salt to taste (approximately 1\\/4 tsp)\",\"2.5 cups Water\"]', 400, 17, 70, 9, 6, 500, NULL, NULL),
('R_69442dde62f25', 'Wholesome Vegetable Upma (Soft & Easy-to-Digest)', '[\"Fine Semolina (Rava): 60g\",\"Mixed Vegetables (finely chopped carrots, peas, green beans): 75g\",\"Onion (small, finely chopped): 30g\",\"Ginger (grated): 1\\/2 tsp\",\"Green Chili (slit, optional, or omit for sensitive palates): 1 small\",\"Mustard Seeds: 1\\/2 tsp\",\"Curry Leaves: 5-6 leaves\",\"Healthy Cooking Oil (e.g., sunflower, rice bran): 1.5 tsp (7.5ml)\",\"Water: 2 cups (approx. 480ml, for soft consistency)\",\"Salt: To taste (approx. 1\\/8 tsp for low sodium)\",\"Lemon Juice: 1 tsp\",\"Fresh Coriander Leaves (chopped): For garnish\"]', 370, 10, 61, 7, 9, 250, NULL, NULL),
('R_69442deced0b7', 'Gentle Moong Dal and Spinach Khichdi', '[\"Yellow Moong Dal\",\"Basmati Rice\",\"Fresh Spinach\",\"Ghee\",\"Cumin Seeds\",\"Turmeric Powder\",\"Asafoetida (Hing)\",\"Fresh Ginger\",\"Salt\",\"Water\"]', 500, 21, 85, 12, 9, 250, NULL, NULL),
('R_69442e0aa4025', 'Palak Moong Dal (Spinach Lentil Curry)', '[\"Moong Dal (Split Yellow Lentils), 100g (dry weight)\",\"Fresh Spinach, 75g, chopped\",\"Onion, 1\\/4 small, finely chopped\",\"Tomato, 1\\/4 small, finely chopped\",\"Garlic, 2 cloves, minced\",\"Ginger, 1\\/2 inch piece, grated\",\"Turmeric powder, 1\\/4 tsp\",\"Cumin powder, 1\\/2 tsp\",\"Coriander powder, 1\\/2 tsp\",\"Asafoetida (Hing), a pinch (optional)\",\"Cumin seeds, 1\\/4 tsp\",\"Ghee or Olive oil, 1.5 tsp\",\"Salt, 1\\/8 tsp (or to taste, sparingly)\",\"Water, as needed\"]', 470, 27, 70, 20, 10, 310, NULL, NULL),
('R_69442e6fcd823', 'Daliya Porridge with Milk, Dates and Almonds', '[\"Broken wheat (daliya)\",\"Low-fat milk\",\"Water\",\"Chopped dates\",\"Slivered almonds\",\"Cardamom powder\",\"Banana slices (for garnish)\"]', 380, 15, 60, 8, 10, 150, NULL, NULL),
('R_69442e6fcdd98', 'Moong Dal Khichdi with Steamed Vegetables and Cucumber Raita', '[\"Basmati rice\",\"Yellow moong dal\",\"Ghee\",\"Turmeric powder\",\"Cumin seeds\",\"Ginger paste\",\"Salt\",\"Water\",\"Carrots\",\"Green beans\",\"Peas\",\"Plain yogurt\",\"Cucumber\",\"Roasted cumin powder\",\"Black salt\"]', 620, 25, 90, 12, 18, 350, NULL, NULL),
('R_69442e6fce7a0', 'Soft Whole Wheat Chapati with Lauki Sabzi and Yellow Dal Tadka', '[\"Whole wheat flour\",\"Water\",\"Salt\",\"Bottle gourd (lauki)\",\"Ginger\",\"Turmeric powder\",\"Cumin powder\",\"Coriander powder\",\"Ghee\",\"Cumin seeds\",\"Yellow lentils (toor dal)\",\"Asafoetida\",\"Red chilli powder (mild, optional)\",\"Fresh coriander\"]', 550, 20, 80, 10, 15, 300, NULL, NULL),
('R_69442f34e94c3', 'Shredded Chicken & Ginger Congee', '[\"White rice\",\"Shredded chicken breast\",\"Fresh ginger\",\"Water\",\"Light soy sauce (low sodium)\",\"Sesame oil\",\"Chopped scallions\"]', 380, 28, 45, 2, 10, 280, NULL, NULL),
('R_69442f34e9c69', 'Steamed Cod with Ginger & Scallions, Stir-fried Bok Choy, Brown Rice', '[\"Cod fillet\",\"Fresh ginger\",\"Scallions\",\"Low-sodium soy sauce\",\"Shaoxing wine (optional)\",\"Sesame oil\",\"Bok Choy\",\"Garlic\",\"Brown rice\"]', 550, 38, 65, 7, 16, 450, NULL, NULL),
('R_69442f34eaa4f', 'Steamed Silken Tofu with Minced Pork and Mixed Vegetables, Plain Rice', '[\"Silken tofu\",\"Lean minced pork\",\"Carrots (diced)\",\"Green peas\",\"Mushrooms (sliced)\",\"Low-sodium soy sauce\",\"Cornstarch\",\"Water\",\"White rice\"]', 480, 32, 55, 6, 14, 380, NULL, NULL),
('R_69442f4bd4e80', 'Bubur Ayam Halia', '[\"White rice\",\"Water or low-sodium chicken broth\",\"Lean chicken breast (shredded, cooked)\",\"Fresh ginger (julienned)\",\"Spring onion (chopped)\",\"White pepper (pinch)\",\"Low-sodium soy sauce (dash)\"]', 350, 15, 50, 3, 7, 250, NULL, NULL),
('R_69442f4bd5be4', 'Nasi Putih dengan Ikan Kukus Halia dan Sayur Campur Tumis Air', '[\"Steamed white rice\",\"White fish fillet (e.g., dory, snapper)\",\"Fresh ginger (sliced)\",\"Spring onion (chopped)\",\"Light soy sauce (low sodium)\",\"Sesame oil (optional, very small amount)\",\"Mixed vegetables (e.g., carrots, cabbage, broccoli florets)\",\"Garlic (minced)\",\"Cooking oil (tiny amount)\",\"Water\"]', 550, 25, 70, 6, 12, 350, NULL, NULL),
('R_69442f4bd6161', 'Sup Sayur Daging Cincang', '[\"Lean minced chicken or beef\",\"Low-sodium chicken broth\",\"Potatoes (diced)\",\"Carrots (diced)\",\"Green beans (chopped)\",\"Garlic (minced)\",\"Onion (diced)\",\"White pepper\",\"Salt (tiny pinch)\",\"Small portion of plain steamed rice or soft wholemeal bread\"]', 450, 20, 55, 5, 10, 300, NULL, NULL),
('R_69453ce14e79c', 'Bubur Ayam', '[\"Rice\",\"Shredded chicken breast\",\"Ginger\",\"Spring onion\",\"Light soy sauce (low sodium)\",\"White pepper\",\"A pinch of salt\"]', 350, 20, 45, 3, 8, 250, NULL, NULL),
('R_69453ce14f281', 'Nasi Kerabu with Grilled Fish (Ikan Bakar) and Fresh Herbs (Ulam)', '[\"Blue pea flower rice\",\"Grilled mackerel fillet (Ikan Kembung)\",\"Shredded long beans\",\"Cucumber slices\",\"Bean sprouts\",\"Cabbage strips\",\"Lime wedge\",\"Turmeric, ginger, garlic (for fish marinade)\",\"Minimal salt (for fish)\"]', 550, 30, 65, 10, 12, 350, NULL, NULL),
('R_69453ce14fe0d', 'Sup Ikan Merah with Mixed Vegetables', '[\"Red snapper fillet\",\"Ginger slices\",\"Gralic\",\"Tomato wedges\",\"Carrot chunks\",\"Daikon radish slices\",\"Light soy sauce (low sodium)\",\"White pepper\",\"Spring onion\",\"Coriander leaves\",\"Water or light vegetable broth\"]', 400, 25, 25, 7, 10, 300, NULL, NULL),
('R_69453cef64064', 'Bubur Ayam (Chicken Porridge)', '[\"White rice (cooked until very soft)\",\"Chicken broth or water\",\"Shredded cooked chicken breast (tenderized)\",\"Finely minced ginger\",\"Thinly sliced spring onion\",\"A pinch of salt (low sodium)\",\"Dash of white pepper\",\"Optional: Minimal fried shallots for garnish (ensure soft)\"]', 350, 18, 45, 3, 7, 300, NULL, NULL),
('R_69453cef646f4', 'Nasi Putih with Ikan Stim Limau and Sayur Campur Tumis (Steamed White Rice with Steamed Fish with Li', '[\"Steamed white rice\",\"White fish fillet (e.g., Barramundi or Tilapia, steamed with ginger, garlic, lime juice, light soy sauce, and spring onion)\",\"Mixed vegetables (e.g., broccoli florets, carrots sliced thinly, bok choy, stir-fried until tender with minimal oil, garlic, and light soy sauce)\"]', 550, 32, 65, 7, 12, 450, NULL, NULL),
('R_69453cef65519', 'Soto Ayam with Lontong/Nasi Impit (Chicken Soup with Compressed Rice Cakes)', '[\"Chicken broth (light and clear)\",\"Shredded cooked chicken breast\",\"Thin slices of lontong or nasi impit (compressed rice cakes, soft)\",\"Small diced potatoes (boiled until soft)\",\"Minimal blanched bean sprouts\",\"Finely chopped celery and spring onion for garnish\",\"Spices: lemongrass, galangal, turmeric, ginger, garlic (blended and saut\\u00e9ed lightly for aroma, then added to broth)\"]', 450, 28, 55, 4, 10, 400, NULL, NULL),
('R_69453d1899bab', 'Bubur Ayam (Malay Chicken Porridge)', '[\"White rice\",\"Chicken breast (shredded)\",\"Ginger\",\"Garlic\",\"Chicken broth (low sodium)\",\"Light soy sauce (reduced sodium)\",\"White pepper\",\"Spring onions\",\"Chinese celery\"]', 380, 20, 55, 3, 9, 350, NULL, NULL),
('R_69453d189a5c3', 'Steamed Basmati Rice with Palak Dhal & Baked Fish (Indian)', '[\"Basmati rice\",\"Yellow lentils (moong dal)\",\"Spinach\",\"Ginger\",\"Garlic\",\"Turmeric powder\",\"Cumin seeds\",\"Mustard seeds\",\"Curry leaves\",\"Tomato\",\"Coriander powder (mild)\",\"White fish fillet (e.g., seabass, cod)\",\"Lemon\",\"Salt (reduced)\",\"Ghee or vegetable oil (minimal)\"]', 580, 30, 75, 9, 18, 480, NULL, NULL),
('R_69453d189b3d9', 'Idli with Sambar & Coconut Chutney (Indian)', '[\"Idli (steamed rice and urad dal cakes)\",\"Toor dal (split pigeon peas)\",\"Mixed vegetables (e.g., drumstick, pumpkin, brinjal, carrots)\",\"Tamarind pulp\",\"Sambar powder\",\"Mustard seeds\",\"Curry leaves\",\"Tomato\",\"Coconut (grated, for chutney)\",\"Green chillies (minimal, for flavour, optional)\",\"Ginger (for chutney)\",\"Salt (reduced)\"]', 450, 18, 65, 7, 12, 380, NULL, NULL),
('R_69453dea505ec', 'Mild Chicken Congee (Bubur Ayam style)', '[\"Cooked white rice (congee consistency)\",\"Shredded lean chicken breast\",\"Fresh ginger slices\",\"Spring onions (garnish)\",\"Low-sodium soy sauce (dash)\",\"Chicken broth\",\"Water\"]', 350, 25, 45, 2, 8, 250, NULL, NULL),
('R_69453dea50de3', 'Steamed Basmati Rice with Mild Dal Tadka and Stir-fried Spinach', '[\"Steamed Basmati rice\",\"Toor dal (split pigeon peas)\",\"Turmeric powder\",\"Cumin seeds\",\"Garlic (minced)\",\"Ginger (grated)\",\"Fresh spinach\",\"Vegetable oil (or mustard oil)\",\"Salt (low-sodium)\",\"Water\"]', 550, 20, 80, 10, 15, 300, NULL, NULL),
('R_69453dea519fd', 'Light Chicken and Vegetable Soto (Malay Clear Soup)', '[\"Chicken broth (low-sodium)\",\"Shredded lean chicken breast\",\"Rice vermicelli (mee hoon)\",\"Carrots (softened, diced)\",\"Green beans (softened, cut)\",\"Celery (diced)\",\"Fresh ginger (sliced)\",\"Turmeric powder\",\"Salt (low-sodium)\",\"Black pepper\",\"Spring onions (garnish)\"]', 450, 30, 50, 6, 12, 280, NULL, NULL),
('R_69453e0278290', 'Chicken & Ginger Congee', '[\"White rice\",\"Chicken breast (shredded)\",\"Fresh ginger (julienned)\",\"Chicken broth (low sodium)\",\"Spring onions (sliced, for garnish)\",\"A touch of sesame oil\",\"Light soy sauce (low sodium, optional)\"]', 400, 22, 55, 3, 10, 300, NULL, NULL),
('R_69453e0278f66', 'Steamed Barramundi with Tofu & Bok Choy, served with Brown Rice', '[\"Brown rice (steamed)\",\"Barramundi fillet (or other white fish)\",\"Soft tofu (cubed)\",\"Bok choy (steamed)\",\"Fresh ginger (sliced)\",\"Garlic (minced)\",\"Light soy sauce (low sodium)\",\"A drizzle of sesame oil\",\"Water\\/broth\"]', 600, 38, 70, 7, 18, 400, NULL, NULL),
('R_69453e027961a', 'Stir-fried Lean Beef with Mixed Vegetables & Glass Noodles', '[\"Lean beef slices (e.g., sirloin)\",\"Glass noodles (vermicelli)\",\"Carrots (julienned)\",\"Snow peas\",\"Mushrooms (sliced)\",\"Bell peppers (sliced)\",\"Garlic (minced)\",\"Ginger (grated)\",\"Light soy sauce (low sodium)\",\"Oyster sauce (low sodium option preferred)\",\"Vegetable oil (for stir-frying)\",\"Cornstarch (for thickening sauce)\"]', 500, 30, 65, 8, 15, 350, NULL, NULL),
('R_69453ea39aabd', 'Shredded Chicken Congee with Ginger', '[\"White rice\",\"Chicken breast (shredded)\",\"Ginger (julienned)\",\"Spring onions (chopped)\",\"Light soy sauce\",\"Sesame oil (a drizzle)\",\"Water or low-sodium chicken broth\"]', 380, 22, 55, 2, 7, 280, NULL, NULL),
('R_69453ea39b1a2', 'Steamed Cod with Ginger & Garlic Brown Rice and Bok Choy', '[\"Cod fillet\",\"Brown rice\",\"Ginger (sliced)\",\"Garlic (minced)\",\"Light soy sauce\",\"Sesame oil\",\"Bok choy\",\"Water\"]', 560, 35, 70, 7, 15, 450, NULL, NULL),
('R_69453ea39b977', 'Steamed Egg Custard with Minced Lean Pork and Mixed Vegetables', '[\"Eggs\",\"Lean minced pork\",\"Low-sodium chicken broth\",\"Carrots (finely diced)\",\"Green peas\",\"Light soy sauce\",\"White pepper\"]', 480, 30, 18, 4, 28, 350, NULL, NULL),
('R_69453f213d39a', 'Berry Almond Oatmeal', '[\"Rolled Oats\",\"Low-Fat Milk (or plant-based alternative)\",\"Mixed Berries (fresh or frozen)\",\"Sliced Almonds\",\"Touch of Maple Syrup or Honey\"]', 380, 12, 55, 9, 10, 120, NULL, NULL),
('R_69453f213e153', 'Grilled Salmon with Brown Rice and Steamed Greens', '[\"Salmon Fillet\",\"Brown Rice\",\"Broccoli Florets\",\"Carrots\",\"Spinach\",\"Light Soy Sauce (low sodium)\",\"Sesame Oil (drizzle)\",\"Ginger (grated)\"]', 550, 35, 55, 7, 22, 350, NULL, NULL),
('R_69453f213e7d9', 'Herbed Chicken Breast with Mashed Sweet Potato and Green Beans', '[\"Skinless Chicken Breast\",\"Sweet Potato\",\"Green Beans\",\"Olive Oil\",\"Low-Fat Milk (for mashing)\",\"Fresh Herbs (e.g., rosemary, thyme)\",\"Black Pepper\"]', 500, 40, 45, 8, 12, 300, NULL, NULL),
('R_69453fac576e6', 'Creamy Oatmeal with Berries and Soft-Boiled Egg', '[\"1\\/2 cup rolled oats\",\"1 cup milk (dairy or non-dairy) or water\",\"1\\/2 cup mixed berries (fresh or frozen)\",\"1 tablespoon chopped walnuts\",\"1 soft-boiled egg\"]', 380, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac57f64', 'Miso Soup with Tofu and Salmon Onigiri', '[\"2 cups dashi broth\",\"2 tablespoons miso paste (low sodium)\",\"4 oz silken tofu, cubed\",\"1 tablespoon dried wakame seaweed\",\"1 scallion, sliced\",\"1 cup cooked short-grain rice\",\"3 oz flaked cooked salmon\",\"1 small sheet nori seaweed\"]', 430, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac58b9e', 'Baked Cod with Roasted Asparagus and Mashed Sweet Potato', '[\"5 oz cod fillet\",\"1 teaspoon olive oil\",\"1\\/2 lemon, sliced\",\"Pinch of dried dill or parsley\",\"1 cup asparagus spears\",\"1 medium sweet potato\",\"2 tablespoons milk (dairy or non-dairy)\",\"1 teaspoon butter or olive oil\"]', 530, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac59204', 'Scrambled Eggs with Spinach and Whole Wheat Toast', '[\"2 large eggs\",\"1\\/2 cup fresh spinach\",\"1 teaspoon olive oil\",\"Salt and pepper to taste\",\"2 slices whole wheat toast\"]', 380, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac598c0', 'Turkey & Avocado Sandwich with Carrot Sticks', '[\"2 slices soft whole-grain bread\",\"3 oz sliced turkey breast (low sodium)\",\"1\\/4 avocado, sliced\",\"Lettuce, tomato slices (optional)\",\"1 small carrot, cut into sticks\"]', 430, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac59e7f', 'Chicken and Vegetable Stir-fry with Brown Rice', '[\"4 oz chicken breast, thinly sliced\",\"1 cup mixed vegetables (e.g., broccoli florets, bell peppers, snow peas)\",\"1 tablespoon low-sodium soy sauce\",\"1 teaspoon grated fresh ginger\",\"1 clove garlic, minced\",\"1 teaspoon sesame oil\",\"1 tablespoon vegetable broth (optional)\",\"1 cup cooked brown rice\"]', 530, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5a4bf', 'Silken Tofu with Soy Sauce and Steamed Rice', '[\"6 oz silken tofu\",\"1 tablespoon low-sodium soy sauce\",\"1 scallion, thinly sliced\",\"1\\/2 cup steamed white rice\",\"1 cup green tea\"]', 320, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5af93', 'Hearty Lentil Soup with Whole-Grain Roll', '[\"1 cup cooked brown or green lentils\",\"1\\/2 cup mixed diced vegetables (carrots, celery, onion)\",\"2 cups vegetable broth (low sodium)\",\"1\\/2 teaspoon dried thyme\",\"1 small whole-grain roll\"]', 430, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5b83b', 'Poached Salmon with Steamed Bok Choy and Quinoa', '[\"5 oz salmon fillet\",\"1 cup water or vegetable broth\",\"2 lemon slices\",\"1 sprig fresh dill (optional)\",\"1 head baby bok choy, halved\",\"1 cup cooked quinoa\"]', 580, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5bf35', 'Whole-Wheat Pancakes with Berries', '[\"2 small whole-wheat pancakes (from mix or scratch)\",\"1\\/2 cup mixed berries\",\"1 tablespoon maple syrup\"]', 380, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5c5f0', 'Udon Noodle Soup with Chicken and Vegetables', '[\"1 serving udon noodles (pre-cooked or dried)\",\"2 cups dashi broth\",\"1 tablespoon low-sodium soy sauce\",\"1 teaspoon mirin (optional)\",\"3 oz cooked chicken breast, sliced\",\"1\\/2 cup soft vegetables (e.g., spinach, sliced shiitake mushrooms)\",\"1 scallion, sliced\"]', 480, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5cc5d', 'Lean Turkey Shepherd\'s Pie with Cauliflower Topping', '[\"5 oz lean ground turkey\",\"1\\/2 cup mixed frozen vegetables (peas, carrots, corn)\",\"1\\/4 cup chopped onion\",\"1 tablespoon tomato paste\",\"1\\/2 cup chicken or vegetable broth (low sodium)\",\"1\\/2 teaspoon dried mixed herbs (e.g., thyme, rosemary)\",\"1 small head cauliflower, chopped\",\"2 tablespoons milk (dairy or non-dairy)\",\"1 teaspoon butter or olive oil\"]', 580, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5d338', 'Oatmeal with Berries, Walnuts & Milk', '[]', 380, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5d9ba', 'Turkey & Avocado Whole Wheat Sandwich with Side Salad', '[]', 480, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5e006', 'Baked Salmon with Roasted Sweet Potatoes & Green Beans', '[]', 550, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5e422', 'Miso Soup with Tofu, Small Bowl of Rice & Green Tea', '[]', 300, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5e9d1', 'Chicken Teriyaki Bento (Grilled Chicken, Rice, Steamed Vegetables & Miso Soup)', '[]', 600, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5ed31', 'Udon Noodle Soup with Shrimp & Mixed Vegetables', '[]', 480, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5f048', 'Scrambled Eggs with Spinach & Whole-Grain Toast', '[]', 390, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5f367', 'Sushi Rolls (e.g., California Roll, Avocado Roll) with Cucumber Salad', '[]', 450, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fac5f686', 'Lean Pork Stir-fry with Brown Rice & Steamed Broccoli/Carrots', '[]', 530, 0, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49b179', 'Idli with Sambar and Coconut Chutney (Indian)', '[]', 400, 15, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49b4e8', 'Steamed Fish with Ginger & Scallions, Brown Rice, and Stir-fried Greens (Chinese)', '[]', 500, 35, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49b8e8', 'Moong Dal Khichdi with Mixed Vegetable Raita (Indian)', '[]', 550, 20, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49c083', 'Chicken Congee (Rice Porridge) with Ginger and Green Onion (Chinese)', '[]', 420, 25, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49c6ac', 'Mild Chicken Curry with Whole Wheat Roti and Cucumber Salad (Indian)', '[]', 580, 30, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49cd86', 'Tofu and Mixed Vegetable Stir-fry with Quinoa (Chinese-inspired)', '[]', 520, 25, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49d399', 'Vegetable Poha with Peanuts (Indian)', '[]', 400, 12, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49daa1', 'Shrimp Lo Mein with Whole Wheat Noodles and Steamed Vegetables (Chinese)', '[]', 550, 30, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49e0ab', 'Paneer Bhurji with Whole Wheat Paratha and Steamed Green Beans (Indian)', '[]', 580, 30, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49e529', 'Steamed Vegetable and Lean Meat Baozi (Buns) with Green Tea (Chinese)', '[]', 450, 20, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49ea2c', 'Dal Tadka (Lentil Soup) with Brown Rice and Spinach Sabzi (Indian)', '[]', 520, 25, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49edfe', 'Braised Chicken with Mushrooms and Bok Choy, Sweet Potato (Chinese-inspired)', '[]', 580, 35, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49f238', 'Idli with Sambar and Coconut Chutney', '[]', 350, 10, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49f7fb', 'Steamed Fish with Ginger and Scallions, served with plain rice and steamed bok choy', '[]', 480, 35, 0, 0, 0, 0, NULL, NULL),
('R_69453fe49fcbd', 'Mild Vegetable Korma with whole wheat chapati and cucumber raita', '[]', 520, 15, 0, 0, 0, 0, NULL, NULL),
('R_69453fe4a002f', 'Chicken Congee (rice porridge) with chopped spring onions', '[]', 320, 20, 0, 0, 0, 0, NULL, NULL),
('R_69453fe4a034b', 'Moong Dal (yellow lentil soup) with brown rice and a side salad', '[]', 420, 25, 0, 0, 0, 0, NULL, NULL),
('R_69453fe4a06b0', 'Tofu and Vegetable Stir-fry (broccoli, carrots, mushrooms) with brown rice', '[]', 480, 25, 0, 0, 0, 0, NULL, NULL),
('R_69453fe4a0a71', 'Vegetable Uttapam (savory pancake) with Sambar', '[]', 380, 12, 0, 0, 0, 0, NULL, NULL),
('R_69453fe4a0de8', 'Clear Chicken and Vegetable Soup with whole wheat crackers', '[]', 380, 28, 0, 0, 0, 0, NULL, NULL),
('R_69453fe4a1141', 'Baked Salmon with a light ginger-garlic sauce, steamed green beans, and quinoa', '[]', 530, 35, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d23a57', 'Bubur Ayam (Chicken Porridge)', '[\"Rice porridge\",\"Shredded chicken breast\",\"Ginger\",\"Spring onions\",\"Light soy sauce\",\"White pepper\"]', 380, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d24151', 'Grilled Sardines with Mixed Green Salad', '[\"Fresh sardines (grilled)\",\"Mixed greens\",\"Cherry tomatoes\",\"Cucumber\",\"Bell peppers\",\"Lemon-herb vinaigrette\",\"Small piece of whole wheat pita\"]', 480, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d247b7', 'Ikan Assam Pedas (Mild Sour Spicy Fish Stew) with Brown Rice and Steamed Kangkung', '[\"White fish fillet (e.g., snapper)\",\"Tamarind paste\",\"Ginger\",\"Garlic\",\"Shallots\",\"Turmeric\",\"Tomatoes\",\"Lady\'s fingers\",\"Reduced mild chili (optional)\",\"Brown rice\",\"Steamed kangkung (water spinach)\"]', 490, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d24ee8', 'Scrambled Eggs with Spinach and Whole Wheat Toast', '[\"2 large eggs (scrambled with a touch of milk)\",\"Fresh spinach (saut\\u00e9ed)\",\"1 slice whole wheat toast\",\"Olive oil\"]', 340, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2558d', 'Soto Ayam (Chicken Noodle Soup)', '[\"Chicken broth\",\"Shredded chicken\",\"Vermicelli noodles\",\"Bean sprouts\",\"Half hard-boiled egg\",\"Celery leaves\",\"Minimal fried shallots (optional)\"]', 430, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d25bea', 'Lentil Soup with Whole Grain Bread', '[\"Red lentils\",\"Carrots\",\"Celery\",\"Onion\",\"Garlic\",\"Vegetable broth\",\"Diced tomatoes\",\"Herbs (thyme, bay leaf)\",\"1 slice whole grain bread\"]', 470, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2632e', 'Wholemeal Toast with Kaya and Soft Boiled Egg', '[\"2 slices wholemeal bread (toasted)\",\"Kaya (coconut jam, reduced sugar)\",\"1 soft boiled egg\"]', 370, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d269e0', 'Chickpea and Vegetable Stew with Brown Rice', '[\"Chickpeas\",\"Diced tomatoes\",\"Zucchini\",\"Eggplant\",\"Bell peppers\",\"Onion\",\"Garlic\",\"Vegetable broth\",\"Herbs (oregano, basil)\",\"Olive oil\",\"Small portion of brown rice\"]', 490, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d27110', 'Chicken and Vegetable Stir-fry with Brown Rice', '[\"Lean chicken breast (sliced)\",\"Broccoli florets\",\"Carrots\",\"Snap peas\",\"Mushrooms\",\"Light soy sauce (low sodium)\",\"Ginger\",\"Garlic\",\"Minimal sesame oil\",\"Brown rice\"]', 480, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d27dcf', 'Oatmeal with Nuts and Dates', '[\"Rolled oats (cooked with water or milk)\",\"Chopped walnuts or almonds\",\"Pitted dates (chopped)\",\"Cinnamon\"]', 390, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d28512', 'Mee Hoon Soup (Vermicelli Noodle Soup with Prawns)', '[\"Rice vermicelli noodles\",\"Prawns\",\"Fish balls (optional, well-cooked)\",\"Bean sprouts\",\"Choy sum\",\"Clear chicken or fish broth\",\"Minimal fried shallots (optional)\"]', 420, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d28d15', 'Baked Salmon with Roasted Sweet Potatoes and Green Beans', '[\"Salmon fillet (baked)\",\"Sweet potatoes (roasted)\",\"Green beans (steamed or roasted)\",\"Olive oil\",\"Lemon\",\"Dill\"]', 520, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d290f5', 'Bubur Ayam (Malay Chicken Porridge)', '\"Rice porridge, shredded chicken breast, spring onions, ginger, light soy sauce, fried shallots (garnish).\"', 380, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2945f', 'Mediterranean Grilled Salmon with Quinoa and Roasted Vegetables', '\"Salmon fillet, quinoa, broccoli florets, bell peppers, zucchini, olive oil, lemon, fresh dill, minced garlic, salt, pepper.\"', 580, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d298a7', 'Malay Steamed Fish with Ginger & Scallion, Brown Rice & Stir-fried Kailan', '\"White fish fillet (e.g., barramundi, cod), fresh ginger, spring onions, light soy sauce, sesame oil, brown rice, kailan (Chinese broccoli), garlic, hint of oyster sauce (optional, use low-sodium).\"', 550, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d29ca1', 'Mediterranean Scrambled Eggs with Spinach and Feta', '\"2 eggs, fresh spinach, crumbled feta cheese, olive oil, 1 slice whole grain toast, cherry tomatoes.\"', 390, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2a041', 'Mee Soto (Malay Chicken Noodle Soup)', '\"Light chicken broth, shredded chicken breast, rice vermicelli noodles, bean sprouts, half a hard-boiled egg, celery, fried shallots (garnish), spring onions (garnish).\"', 520, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2a3ed', 'Mediterranean Chicken & Vegetable Skewers with Couscous Salad', '\"Chicken breast pieces, bell peppers (various colors), zucchini, cherry tomatoes, red onion, olive oil, dried oregano, fresh thyme, couscous, chickpeas, cucumber, lemon juice, fresh mint.\"', 620, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2a770', 'Malay Soft-boiled Eggs with Whole Wheat Toast', '\"2 soft-boiled eggs, 2 slices whole wheat toast, light soy sauce, white pepper, sliced tomato.\"', 350, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2aadf', 'Mediterranean Grilled Chicken Salad', '\"Grilled chicken breast, mixed greens, cucumber, cherry tomatoes, red onion, Kalamata olives, bell peppers, olive oil, red wine vinegar, dried oregano.\"', 500, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454b2d2aeba', 'Malay Assam Pedas Fish with Brown Rice & Steamed Okra', '\"White fish fillet (e.g., mackerel, snapper), tamarind paste, shallots, garlic, ginger, lemongrass, 1-2 dried chilies (optional, or omit for no spice), lady\'s finger (okra), tomato, brown rice.\"', 580, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd770006', 'Warm Water', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd770990', 'Still Water with Lemon Slice (Mediterranean inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd771324', 'Still Water with Cucumber Slice (Mediterranean inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd771978', 'Unsweetened Herbal Tea (e.g., Chamomile)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd771dce', 'Still Water with Mint Leaves (Mediterranean inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd772150', 'Still Water with Thin Ginger Slice (Malay inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd77248b', 'Plain Still Water', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd7727d0', 'Still Water with Kaffir Lime Leaf (Malay inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd772ba4', 'Still Water with Orange Peel (Mediterranean inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd772ea6', 'Still Water with Lemongrass (Malay inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd7731a0', 'Still Water with Basil Leaf (Mediterranean inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd7734fa', 'Still Water with Pandan Leaf (Malay inspired)', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd773aab', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd773e19', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd77419a', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd7744a9', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd7747ae', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd774b9e', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd774f75', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd7752e6', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454bd775618', 'No food consumed', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c300b375', 'Chicken and Mushroom Congee', '[\"White rice\",\"Chicken breast (shredded)\",\"Shiitake mushrooms (sliced)\",\"Ginger (shredded)\",\"Low-sodium chicken broth\",\"White pepper\",\"Sesame oil (a drizzle)\",\"Spring onion (garnish)\",\"Minimal salt\"]', 380, 25, 45, 3, 10, 250, NULL, NULL),
('R_69454c300b9cf', 'Steamed Cod with Ginger-Scallion Sauce, White Rice, and Steamed Broccoli', '[\"Cod fillet\",\"Fresh ginger (julienned)\",\"Spring onions (sliced)\",\"Low-sodium light soy sauce\",\"Sesame oil\",\"White rice\",\"Broccoli florets\"]', 550, 35, 60, 7, 15, 350, NULL, NULL),
('R_69454c300c2d6', 'Stir-fried Tofu with Mixed Vegetables and Brown Rice', '[\"Firm tofu (pressed and cubed)\",\"Broccoli florets\",\"Carrots (sliced)\",\"Snow peas\",\"Bell pepper (diced)\",\"Garlic (minced)\",\"Ginger (minced)\",\"Low-sodium soy sauce\",\"Low-sodium oyster sauce\",\"Cornstarch slurry\",\"Vegetable oil\",\"Brown rice\"]', 520, 28, 65, 9, 16, 300, NULL, NULL),
('R_69454c826635d', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8266dc7', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8267314', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826793d', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8267fdc', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c82684ef', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL);
INSERT INTO `recipes` (`recipeID`, `name`, `ingredients`, `calories`, `protein`, `carbs`, `fibre`, `fat`, `sodium`, `preference`, `instructions`) VALUES
('R_69454c8268be8', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8269269', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8269882', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8269bf1', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c8269f40', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826a3ec', 'No food or drink permitted due to 0 kcal, <0mg sodium, <0g carbs limit', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826a747', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826aa9b', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826add5', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826b1ed', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826bb52', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826be68', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826c38c', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826c914', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454c826ce86', 'No food', '[]', 0, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f585e9', 'Bubur Ayam (Chicken Porridge with Egg)', '[]', 380, 0, 45, 0, 0, 300, NULL, NULL),
('R_69454e5f591de', 'Ikan Bakar (Grilled Fish) with Nasi Putih, Ulam & Sayur Lemak', '[]', 600, 0, 55, 0, 0, 380, NULL, NULL),
('R_69454e5f599f3', 'Ayam Masak Merah (Chicken in Red Sauce) with Brown Rice & Stir-fried Kailan', '[]', 640, 0, 58, 0, 0, 450, NULL, NULL),
('R_69454e5f5a2ea', 'Thosai with Dhal Curry', '[]', 400, 0, 70, 0, 0, 300, NULL, NULL),
('R_69454e5f5a96c', 'Nasi Kerabu (Blue Rice) with Ayam Percik & Kerabu Salad', '[]', 650, 0, 60, 0, 0, 400, NULL, NULL),
('R_69454e5f5af99', 'Sup Tulang (Beef Ribs Soup) with Nasi Putih & Sayur Campur', '[]', 620, 0, 50, 0, 0, 420, NULL, NULL),
('R_69454e5f5b678', 'Mee Hoon Goreng (Fried Vermicelli) with Tofu & Egg', '[]', 450, 0, 55, 0, 0, 500, NULL, NULL),
('R_69454e5f5bb03', 'Laksa Johor (Spaghetti-based Laksa)', '[]', 680, 0, 65, 0, 0, 400, NULL, NULL),
('R_69454e5f5c06b', 'Sambal Udang (Prawn Sambal) with Nasi Putih & Stir-fried Okra', '[]', 600, 0, 50, 0, 0, 600, NULL, NULL),
('R_69454e5f5c652', 'Roti Jala (Net Crepes) with Chicken Curry (light)', '[]', 450, 0, 60, 0, 0, 350, NULL, NULL),
('R_69454e5f5cd82', 'Curry Mee (Noodle Curry) - Lighter Version', '[]', 580, 0, 55, 0, 0, 400, NULL, NULL),
('R_69454e5f5d2c5', 'Asam Pedas Ikan (Sour Spicy Fish Stew) with Nasi Putih & Steamed Cabbage', '[]', 550, 0, 45, 0, 0, 350, NULL, NULL),
('R_69454e5f5d688', 'Healthier Nasi Lemak', '[]', 415, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5da69', 'Ayam Percik (Grilled Chicken) with Brown Rice and Stir-fried Mixed Vegetables', '[]', 610, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5df53', 'Ikan Bakar (Grilled Fish) with Assorted Ulam and Tempeh', '[]', 490, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5e2ae', 'Soft-boiled Eggs with Wholemeal Toast, Avocado and Papaya', '[]', 440, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5e5fa', 'Gado-Gado (Malay Style) with Brown Rice', '[]', 680, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5ec4c', 'Fish Curry (Light) with Okra & Tomato', '[]', 440, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5efdd', 'Malay-style Scrambled Eggs with Tempeh and Wholemeal Bread', '[]', 430, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5f353', 'Grilled Chicken Satay with Brown Rice and Vegetables', '[]', 695, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454e5f5f695', 'Steamed Fish with Ginger & Low-Sodium Soy Sauce, Stir-fried Bok Choy, and Steamed Tofu', '[]', 455, 0, 0, 0, 0, 0, NULL, NULL),
('R_69454ee86ea81', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee86f46c', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee86fafd', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee8701f7', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee87088f', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee870f63', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee871632', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee871c56', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee872307', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee872990', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee87343a', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee8738a2', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee873c32', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee87407c', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee8744b0', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee874afa', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee874eb6', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee87523b', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee8755dd', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee87592b', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454ee875e8f', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f615a5f8', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f615aef2', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f615b2fc', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0ae99', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0b680', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0bd00', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0c2d3', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0c9b8', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0d041', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0d7a3', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0ded8', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0e45d', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0eaf4', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0f140', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0f539', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a0fdd6', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a101a9', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a10534', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a1087a', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a10bc7', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a11073', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a1176e', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a11a87', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69454f8a11d87', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455050eb942', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455050ec294', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455050ec8e1', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455177d685a', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455177d6edd', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455177d7671', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28cc44', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28d468', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28d88f', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28dc53', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28dfda', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28e366', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28e72a', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28eb55', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28f510', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a28fd30', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a2901e9', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a2905a7', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a2909af', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a290d09', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a29119d', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a291772', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a291dbf', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a292482', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a292b99', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a2931ff', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694551a2938b8', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b631d5', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b63bc7', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b644b6', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b64d25', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b656ac', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b65dcb', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b66229', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b6657d', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b6689c', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b66eb2', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b67534', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b67a90', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b67e45', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b6866c', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b689dd', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b68cf6', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b69069', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b69441', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b697f1', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b69b91', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945527b69f06', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd6398', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd6b20', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd7172', 'Standard Balanced Meal  (Fallback Day 1)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd76ee', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd7c64', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd82eb', 'Standard Balanced Meal  (Fallback Day 2)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd88f0', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd8fea', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd9644', 'Standard Balanced Meal  (Fallback Day 3)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afd9cab', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afda370', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afda7a7', 'Standard Balanced Meal  (Fallback Day 4)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdaac6', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdae44', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdb190', 'Standard Balanced Meal  (Fallback Day 5)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdb51a', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdb83f', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdbb55', 'Standard Balanced Meal  (Fallback Day 6)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdbe5c', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdc176', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_694552afdc8ce', 'Standard Balanced Meal  (Fallback Day 7)', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945562c8195f', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945562c81e18', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_6945562c82021', 'Standard Balanced Meal ', '[\"Chicken\",\"Rice\",\"Vegetables\"]', 500, 30, 40, 5, 10, 300, NULL, NULL),
('R_69455ec8086a4', 'Oatmeal with Berries and Nuts', '[\"1\\/2 cup rolled oats\",\"1 cup unsweetened almond milk\",\"1\\/4 cup mixed berries\",\"1 tbsp chopped walnuts\",\"1 tsp chia seeds\"]', 350, 12, 55, 0, 15, 150, NULL, NULL),
('R_69455ec808aca', 'Chicken and Vegetable Stir-fry (Chinese)', '[\"4oz chicken breast\",\"1 cup mixed vegetables (broccoli, carrots, bell peppers)\",\"1 tbsp low-sodium soy sauce\",\"1 tsp sesame oil\",\"1\\/2 cup brown rice\"]', 450, 35, 45, 0, 20, 500, NULL, NULL),
('R_69455ec808cde', 'Steamed Fish with Ginger and Bok Choy', '[\"4oz white fish (cod, tilapia)\",\"1 cup bok choy\",\"1 tbsp ginger\",\"1 tsp soy sauce\",\"1\\/2 cup quinoa\"]', 420, 30, 35, 0, 15, 400, NULL, NULL),
('R_69455ec808f31', 'Scrambled Eggs with Spinach and Whole-Wheat Toast', '[\"2 eggs\",\"1 cup spinach\",\"1 slice whole-wheat toast\",\"1 tsp olive oil\"]', 380, 25, 30, 0, 20, 250, NULL, NULL),
('R_69455ec809144', 'Malay Chicken Curry (light version) with Cauliflower Rice', '[\"4oz chicken breast\",\"1\\/2 cup coconut milk (light)\",\"1\\/2 cup mixed vegetables (onion, tomatoes, bell peppers)\",\"1 cup cauliflower rice\",\"curry powder (low sodium)\"]', 480, 35, 50, 0, 20, 600, NULL, NULL),
('R_69455ec8093ad', 'Tofu and Vegetable Noodle Soup (Chinese)', '[\"4oz tofu\",\"1 cup mixed vegetables (mushrooms, carrots, celery)\",\"1 cup vegetable broth (low sodium)\",\"1\\/2 cup whole-wheat noodles\",\"1 tsp sesame oil\"]', 400, 25, 50, 0, 15, 450, NULL, NULL),
('R_69455ec8095e8', 'Smoothie (Spinach, Banana, Almond Milk, Protein Powder)', '[\"1 cup spinach\",\"1\\/2 banana\",\"1 cup unsweetened almond milk\",\"1 scoop protein powder (whey or plant-based)\"]', 360, 25, 50, 0, 10, 100, NULL, NULL),
('R_69455ec809885', 'Beef and Broccoli (Chinese)', '[\"4oz lean beef\",\"1 cup broccoli\",\"1 tbsp low-sodium soy sauce\",\"1 tsp sesame oil\",\"1\\/2 cup brown rice\"]', 460, 40, 40, 0, 20, 550, NULL, NULL),
('R_69455ec809aaa', 'Grilled Fish with Asparagus and Sweet Potato', '[\"4oz white fish\",\"1 cup asparagus\",\"1\\/2 medium sweet potato\",\"1 tsp olive oil\"]', 410, 30, 45, 0, 15, 350, NULL, NULL),
('R_69455ec809cfe', 'Greek Yogurt with Berries and Chia Seeds', '[\"1 cup Greek yogurt\",\"1\\/2 cup mixed berries\",\"1 tbsp chia seeds\"]', 370, 25, 50, 0, 15, 80, NULL, NULL),
('R_69455ec809f06', 'Chicken Rendang (Malay, light version) with Salad', '[\"4oz chicken breast\",\"1\\/4 cup coconut milk (light)\",\"mixed vegetables (onion, ginger, lemongrass)\",\"large mixed green salad with light vinaigrette\"]', 470, 35, 45, 0, 22, 650, NULL, NULL),
('R_69455ec80a101', 'Shrimp Stir-fry with Snow Peas and Brown Rice (Chinese)', '[\"4oz shrimp\",\"1 cup snow peas\",\"1 tbsp low-sodium soy sauce\",\"1 tsp sesame oil\",\"1\\/2 cup brown rice\"]', 400, 30, 40, 0, 15, 400, NULL, NULL),
('R_69455ec80a305', 'Whole-Wheat Toast with Avocado and Egg', '[\"1 slice whole-wheat toast\",\"1\\/4 avocado\",\"1 egg\"]', 390, 20, 40, 0, 22, 200, NULL, NULL),
('R_69455ec80a505', 'Vegetable Curry with Tofu (Malay)', '[\"4oz tofu\",\"1 cup mixed vegetables (eggplant, okra, potatoes)\",\"1\\/4 cup coconut milk (light)\",\"curry powder (low sodium)\"]', 450, 25, 55, 0, 18, 550, NULL, NULL),
('R_69455ec80a945', 'Steamed Chicken and Mushrooms (Chinese)', '[\"4oz chicken breast\",\"1 cup mushrooms\",\"1 tbsp soy sauce\",\"1 tsp sesame oil\"]', 420, 35, 35, 0, 15, 350, NULL, NULL),
('R_69455ec80ac07', 'Yogurt Parfait (Greek Yogurt, Granola, Berries)', '[\"1 cup Greek yogurt\",\"1\\/4 cup granola (low sugar)\",\"1\\/2 cup mixed berries\"]', 350, 20, 50, 0, 15, 120, NULL, NULL),
('R_69455ec80ae89', 'Beef Noodle Soup (Chinese, light broth)', '[\"4oz lean beef\",\"1 cup mixed vegetables (bok choy, carrots)\",\"1 cup vegetable broth (low sodium)\",\"1\\/2 cup whole-wheat noodles\"]', 460, 35, 50, 0, 18, 600, NULL, NULL),
('R_69455ec80b091', 'Grilled Salmon with Roasted Vegetables', '[\"4oz salmon\",\"1 cup mixed roasted vegetables (zucchini, bell peppers, onions)\",\"1 tsp olive oil\"]', 430, 35, 35, 0, 18, 300, NULL, NULL),
('R_69455ec80b286', 'Cottage Cheese with Pineapple', '[\"1 cup cottage cheese\",\"1\\/2 cup pineapple chunks\"]', 360, 25, 40, 0, 15, 180, NULL, NULL),
('R_69455ec80b46d', 'Malay Laksa (light version)', '[\"1\\/2 cup noodles\",\"1\\/4 cup coconut milk (light)\",\"shrimp or tofu\",\"vegetables (bean sprouts, chives)\"]', 480, 30, 55, 0, 20, 700, NULL, NULL),
('R_69455ec80b695', 'Chicken and Vegetable Lo Mein (Chinese)', '[\"4oz chicken breast\",\"1 cup mixed vegetables (cabbage, carrots, mushrooms)\",\"1 tbsp low-sodium soy sauce\",\"1\\/2 cup whole-wheat lo mein noodles\"]', 410, 30, 45, 0, 15, 450, NULL, NULL),
('R_694565dc5bd5a', 'Oatmeal with Berries and Nuts', '[\"1\\/2 cup rolled oats\",\"1 cup unsweetened almond milk\",\"1\\/4 cup mixed berries\",\"1 tbsp chopped walnuts\",\"1 tsp cinnamon\"]', 380, 12, 55, 0, 15, 150, NULL, NULL),
('R_694565dc5bf6d', 'Grilled Chicken Salad with Light Vinaigrette', '[\"4oz grilled chicken breast\",\"2 cups mixed greens\",\"1\\/4 cup chopped cucumber\",\"1\\/4 cup chopped bell pepper\",\"2 tbsp light vinaigrette dressing\"]', 450, 35, 35, 0, 20, 350, NULL, NULL),
('R_694565dc5c1b7', 'Baked Salmon with Roasted Asparagus and Quinoa', '[\"4oz baked salmon\",\"1 cup roasted asparagus\",\"1\\/2 cup cooked quinoa\",\"1 tbsp olive oil\",\"lemon juice\"]', 550, 40, 50, 0, 25, 250, NULL, NULL),
('R_694565dc5c416', 'Scrambled Eggs with Spinach and Whole-Wheat Toast', '[\"2 eggs\",\"1 cup spinach\",\"1 slice whole-wheat toast\",\"1 tsp olive oil\"]', 350, 25, 25, 0, 15, 200, NULL, NULL),
('R_694565dc5c65e', 'Turkey and Avocado Wrap', '[\"4oz sliced turkey breast\",\"1\\/4 avocado\",\"1 whole-wheat tortilla\",\"lettuce\",\"tomato\"]', 480, 30, 45, 0, 25, 400, NULL, NULL),
('R_694565dc5c860', 'Chicken Stir-Fry with Brown Rice', '[\"4oz chicken breast\",\"1 cup mixed vegetables (broccoli, carrots, peppers)\",\"1\\/2 cup cooked brown rice\",\"1 tbsp low-sodium soy sauce\"]', 520, 35, 60, 0, 18, 300, NULL, NULL),
('R_694565dc5ceef', 'Smoothie (Spinach, Banana, Almond Milk, Protein Powder)', '[\"1 cup unsweetened almond milk\",\"1\\/2 banana\",\"1 cup spinach\",\"1 scoop protein powder\"]', 370, 20, 50, 0, 10, 100, NULL, NULL),
('R_694565dc5db51', 'Lentil Soup with Whole-Grain Bread', '[\"1.5 cups lentil soup\",\"1 slice whole-grain bread\"]', 460, 25, 65, 0, 12, 320, NULL, NULL),
('R_694565dc5dd70', 'Baked Cod with Steamed Green Beans and Sweet Potato', '[\"4oz baked cod\",\"1 cup steamed green beans\",\"1\\/2 medium sweet potato\"]', 530, 40, 55, 0, 15, 280, NULL, NULL),
('R_694565dc5dfae', 'Cottage Cheese with Peach Slices', '[\"1 cup cottage cheese\",\"1 medium peach, sliced\"]', 360, 25, 35, 0, 12, 220, NULL, NULL),
('R_694565dc5e1a8', 'Tuna Salad (light mayo) on Lettuce Wraps', '[\"4oz canned tuna (in water)\",\"2 tbsp light mayonnaise\",\"lettuce leaves\"]', 440, 30, 20, 0, 22, 380, NULL, NULL),
('R_694565dc5e37f', 'Ground Turkey and Vegetable Skillet', '[\"4oz ground turkey\",\"1 cup mixed vegetables (peppers, onions, zucchini)\",\"1 tbsp olive oil\"]', 540, 40, 45, 0, 20, 270, NULL, NULL),
('R_694565dc5e51e', 'Greek Yogurt with Berries and Chia Seeds', '[\"1 cup Greek yogurt\",\"1\\/2 cup mixed berries\",\"1 tbsp chia seeds\"]', 390, 25, 50, 0, 15, 80, NULL, NULL),
('R_694565dc5e6b9', 'Chicken Caesar Salad (light dressing)', '[\"4oz grilled chicken breast\",\"2 cups romaine lettuce\",\"2 tbsp light Caesar dressing\"]', 470, 35, 30, 0, 25, 350, NULL, NULL),
('R_694565dc5e928', 'Shrimp Scampi with Zucchini Noodles', '[\"4oz shrimp\",\"2 cups zucchini noodles\",\"1 tbsp olive oil\",\"garlic\",\"lemon juice\"]', 510, 40, 35, 0, 20, 310, NULL, NULL),
('R_694565dc5ebdd', 'Whole-Wheat Toast with Avocado and Egg', '[\"1 slice whole-wheat toast\",\"1\\/4 avocado\",\"1 egg\"]', 380, 20, 35, 0, 20, 180, NULL, NULL),
('R_694565dc5edef', 'Leftover Shrimp Scampi with Zucchini Noodles', '[\"4oz shrimp\",\"2 cups zucchini noodles\",\"1 tbsp olive oil\",\"garlic\",\"lemon juice\"]', 510, 40, 35, 0, 20, 310, NULL, NULL),
('R_694565dc5efdd', 'Baked Chicken Breast with Roasted Broccoli and Brown Rice', '[\"4oz baked chicken breast\",\"1 cup roasted broccoli\",\"1\\/2 cup cooked brown rice\"]', 530, 40, 60, 0, 15, 290, NULL, NULL),
('R_694565dc5f1ec', 'Breakfast Burrito (Egg, Black Beans, Salsa)', '[\"2 eggs\",\"1\\/4 cup black beans\",\"1 whole-wheat tortilla\",\"salsa\"]', 390, 20, 40, 0, 15, 250, NULL, NULL),
('R_694565dc5f3f0', 'Leftover Baked Chicken Breast with Roasted Broccoli and Brown Rice', '[\"4oz baked chicken breast\",\"1 cup roasted broccoli\",\"1\\/2 cup cooked brown rice\"]', 530, 40, 60, 0, 15, 290, NULL, NULL),
('R_694565dc5f5f3', 'Vegetarian Chili', '[\"1.5 cups vegetarian chili\"]', 480, 25, 65, 0, 15, 330, NULL, NULL),
('R_69457cd5ace7d', 'Oatmeal with Berries and Nuts', '[\"1\\/2 cup rolled oats\",\"1 cup unsweetened almond milk\",\"1\\/4 cup mixed berries\",\"1 tbsp chopped walnuts\",\"1 tsp cinnamon\"]', 380, 12, 55, 0, 15, 150, NULL, NULL),
('R_69457cd5ad7e1', 'Malay Chicken Curry with Brown Rice', '[\"4oz Chicken breast (cooked)\",\"1\\/2 cup brown rice\",\"1\\/4 cup mixed vegetables (carrots, peas, beans)\",\"2 tbsp Malay curry sauce (low sodium)\"]', 520, 35, 65, 0, 20, 600, NULL, NULL),
('R_69457cd5ae490', 'Baked Salmon with Roasted Vegetables', '[\"4oz Salmon fillet\",\"1 cup roasted vegetables (broccoli, bell peppers, zucchini)\",\"1 tbsp olive oil\",\"Lemon juice\",\"Herbs (dill, parsley)\"]', 550, 40, 40, 0, 25, 450, NULL, NULL),
('R_69457cd5ae78d', 'Scrambled Eggs with Spinach and Whole-Wheat Toast', '[\"2 eggs\",\"1 cup spinach\",\"1 slice whole-wheat toast\",\"1 tsp olive oil\"]', 350, 25, 30, 0, 15, 200, NULL, NULL),
('R_69457cd5aeaf7', 'Mediterranean Quinoa Salad', '[\"1\\/2 cup cooked quinoa\",\"1\\/4 cup chopped cucumber\",\"1\\/4 cup chopped tomatoes\",\"1\\/4 cup chickpeas\",\"2 tbsp olive oil and lemon juice dressing\"]', 500, 20, 70, 0, 18, 550, NULL, NULL),
('R_69457cd5aedfe', 'Chicken and Vegetable Stir-Fry', '[\"4oz Chicken breast (cooked)\",\"1 cup mixed vegetables (broccoli, carrots, snap peas)\",\"2 tbsp low-sodium soy sauce\",\"1 tbsp olive oil\"]', 530, 35, 55, 0, 20, 500, NULL, NULL),
('R_69457cd5af06f', 'Smoothie with Protein Powder and Berries', '[\"1 cup unsweetened almond milk\",\"1 scoop protein powder (whey or plant-based)\",\"1\\/2 cup mixed berries\",\"1 tbsp chia seeds\"]', 370, 25, 45, 0, 10, 100, NULL, NULL),
('R_69457cd5af2c9', 'Lentil Soup with Whole-Grain Bread', '[\"1.5 cup lentil soup (low sodium)\",\"1 slice whole-grain bread\"]', 510, 25, 75, 0, 12, 650, NULL, NULL),
('R_69457cd5af4f3', 'Baked Cod with Asparagus', '[\"4oz Cod fillet\",\"1 cup asparagus\",\"1 tbsp olive oil\",\"Lemon juice\",\"Garlic\"]', 540, 45, 35, 0, 20, 500, NULL, NULL),
('R_69457cd5af6f0', 'Greek Yogurt with Granola and Fruit', '[\"1 cup plain Greek yogurt\",\"1\\/4 cup low-sugar granola\",\"1\\/2 cup chopped fruit (peaches, pears)\"]', 390, 20, 50, 0, 15, 120, NULL, NULL),
('R_69457cd5af89e', 'Chicken Salad Sandwich on Whole-Wheat Bread', '[\"4oz cooked chicken breast\",\"2 tbsp light mayonnaise\",\"1\\/4 cup chopped celery\",\"2 slices whole-wheat bread\"]', 530, 35, 60, 0, 20, 600, NULL, NULL),
('R_69457cd5afa4c', 'Vegetable Curry with Cauliflower Rice', '[\"1 cup mixed vegetables (potatoes, peas, carrots)\",\"1\\/2 cup cauliflower rice\",\"2 tbsp curry sauce (low sodium)\"]', 520, 25, 60, 0, 20, 550, NULL, NULL),
('R_69457cd5afd77', 'Whole-Wheat Toast with Avocado and Egg', '[\"1 slice whole-wheat toast\",\"1\\/4 avocado\",\"1 egg\"]', 360, 18, 35, 0, 18, 180, NULL, NULL),
('R_69457cd5aff9a', 'Tuna Salad with Lettuce Wraps', '[\"4oz canned tuna (in water)\",\"2 tbsp light mayonnaise\",\"Lettuce leaves\"]', 480, 35, 25, 0, 20, 550, NULL, NULL),
('R_69457cd5b0206', 'Shrimp Scampi with Zucchini Noodles', '[\"4oz Shrimp\",\"1 cup zucchini noodles\",\"1 tbsp olive oil\",\"Garlic\",\"Lemon juice\"]', 540, 40, 30, 0, 25, 500, NULL, NULL),
('R_69457cd5b0462', 'Breakfast Burrito (Whole Wheat)', '[\"1 whole wheat tortilla\",\"2 scrambled egg whites\",\"1\\/4 cup black beans\",\"1 tbsp salsa\"]', 410, 20, 55, 0, 15, 250, NULL, NULL),
('R_69457cd5b060b', 'Mediterranean Hummus and Veggie Wrap', '[\"1 whole wheat tortilla\",\"2 tbsp hummus\",\"1\\/4 cup chopped cucumber\",\"1\\/4 cup chopped tomatoes\",\"Spinach\"]', 500, 20, 65, 0, 20, 550, NULL, NULL),
('R_69457cd5b07a5', 'Chicken Nasi Lemak (light version)', '[\"4oz Chicken breast (cooked)\",\"1\\/2 cup brown rice\",\"1\\/4 cup cucumber\",\"1\\/4 cup peanuts\",\"2 tbsp Nasi Lemak sauce (low sodium)\"]', 510, 30, 60, 0, 18, 600, NULL, NULL),
('R_69457cd5b093e', 'Cinnamon Swirl Cottage Cheese', '[\"1 cup cottage cheese\",\"1\\/4 cup berries\",\"1 tsp cinnamon\",\"1 tbsp chopped walnuts\"]', 380, 25, 40, 0, 12, 150, NULL, NULL),
('R_69457cd5b0af8', 'Leftover Chicken Nasi Lemak', '[\"4oz Chicken breast (cooked)\",\"1\\/2 cup brown rice\",\"1\\/4 cup cucumber\",\"1\\/4 cup peanuts\",\"2 tbsp Nasi Lemak sauce (low sodium)\"]', 500, 30, 55, 0, 18, 550, NULL, NULL),
('R_69457cd5b0ca4', 'Baked Turkey Breast with Roasted Sweet Potatoes', '[\"4oz Turkey breast\",\"1 cup roasted sweet potatoes\",\"1 tbsp olive oil\",\"Herbs (rosemary, thyme)\"]', 530, 40, 50, 0, 15, 450, NULL, NULL),
('R_69457efab82ff', 'Oatmeal with Berries and Nuts', '[\"1\\/2 cup rolled oats\",\"1 cup water\",\"1\\/4 cup mixed berries\",\"1 tbsp chopped walnuts\",\"1 tsp cinnamon\"]', 380, 12, 55, 8, 12, 150, NULL, NULL),
('R_69457efab85ea', 'Chicken Rendang with Brown Rice & Vegetables', '[\"4oz Chicken Rendang (lean chicken, coconut milk, spices)\",\"1\\/2 cup cooked brown rice\",\"1 cup steamed mixed vegetables (broccoli, carrots, green beans)\"]', 520, 35, 60, 7, 18, 650, NULL, NULL),
('R_69457efab908f', 'Fish Curry with Cauliflower Rice', '[\"4oz Fish Curry (cod, tomatoes, spices)\",\"1 cup cauliflower rice\",\"1\\/2 cup spinach\"]', 480, 30, 45, 6, 15, 550, NULL, NULL),
('R_69457efab9325', 'Scrambled Eggs with Whole Wheat Toast & Avocado', '[\"2 eggs\",\"1 slice whole wheat toast\",\"1\\/4 avocado\",\"1 tbsp chopped tomatoes\"]', 410, 22, 35, 7, 20, 250, NULL, NULL),
('R_69457efab9590', 'Nasi Lemak (light version) with Grilled Chicken', '[\"3oz Grilled Chicken\",\"1\\/4 cup brown rice\",\"1\\/4 cup steamed beans\",\"1 tbsp sambal (small amount)\",\"cucumber slices\"]', 500, 38, 55, 8, 15, 700, NULL, NULL),
('R_69457efab987f', 'Vegetable and Tofu Stir-fry with Quinoa', '[\"4oz firm tofu\",\"1 cup mixed vegetables (bell peppers, onions, mushrooms)\",\"1\\/2 cup cooked quinoa\",\"low-sodium soy sauce\"]', 450, 25, 50, 9, 12, 400, NULL, NULL),
('R_69457efab9b52', 'Whole Wheat Roti Canai (small) with Dhal', '[\"1 small roti canai\",\"1\\/2 cup dhal\"]', 420, 15, 60, 8, 10, 300, NULL, NULL),
('R_69457efab9de1', 'Sayur Lodeh with Tempeh', '[\"1 cup Sayur Lodeh (vegetable soup)\",\"3oz Tempeh\"]', 510, 32, 58, 10, 16, 600, NULL, NULL),
('R_69457efaba07b', 'Steamed Fish with Asparagus and Brown Rice', '[\"4oz Steamed Fish\",\"1 cup asparagus\",\"1\\/2 cup brown rice\"]', 460, 35, 48, 7, 10, 450, NULL, NULL),
('R_69457efaba252', 'Yogurt Parfait with Granola and Berries', '[\"1 cup plain yogurt\",\"1\\/4 cup granola (low sugar)\",\"1\\/2 cup mixed berries\"]', 390, 18, 50, 9, 12, 180, NULL, NULL),
('R_69457efaba43b', 'Ayam Percik with Salad', '[\"4oz Ayam Percik (grilled chicken)\",\"Large mixed green salad with light vinaigrette\"]', 530, 40, 50, 8, 18, 750, NULL, NULL),
('R_69457efaba604', 'Vegetable Kari with Tofu and Brown Rice', '[\"1 cup Vegetable Kari (vegetables, tofu, coconut milk)\",\"1\\/2 cup brown rice\"]', 470, 28, 55, 9, 14, 500, NULL, NULL),
('R_69457efaba7c6', 'Whole Wheat Bread with Egg and Tomato', '[\"2 slices whole wheat bread\",\"1 egg\",\"1\\/2 tomato\"]', 370, 16, 40, 6, 12, 220, NULL, NULL),
('R_69457efabaa0f', 'Laksa (light version) with extra vegetables', '[\"1 bowl Laksa (light coconut milk)\",\"extra vegetables (bean sprouts, choy sum)\"]', 520, 35, 65, 9, 15, 800, NULL, NULL),
('R_69457efabac4d', 'Steamed Chicken with Bok Choy and Sweet Potato', '[\"4oz Steamed Chicken\",\"1 cup Bok Choy\",\"1\\/2 cup Sweet Potato\"]', 450, 32, 52, 8, 8, 420, NULL, NULL),
('R_69457efabae89', 'Smoothie with Spinach, Banana, and Protein Powder', '[\"1 cup spinach\",\"1\\/2 banana\",\"1 scoop protein powder\",\"1\\/2 cup water\"]', 385, 25, 45, 7, 8, 100, NULL, NULL),
('R_69457efabb0c5', 'Gado-Gado with Tofu and Peanut Sauce (light)', '[\"Mixed vegetables (lettuce, bean sprouts, long beans)\",\"2oz Tofu\",\"light peanut sauce\"]', 515, 30, 60, 12, 18, 680, NULL, NULL),
('R_69457efabb2ff', 'Grilled Fish with Roasted Vegetables', '[\"4oz Grilled Fish\",\"1 cup roasted vegetables (zucchini, bell peppers, onions)\"]', 465, 38, 35, 8, 12, 480, NULL, NULL),
('R_69457efabb4ef', 'Congee with Chicken and Vegetables', '[\"1 cup Congee\",\"2oz Chicken\",\"1\\/4 cup mixed vegetables\"]', 400, 20, 55, 6, 8, 280, NULL, NULL),
('R_69457efabb6af', 'Nasi Goreng (light version) with Egg', '[\"1\\/2 cup brown rice\",\"1 egg\",\"vegetables (carrots, peas)\"]', 505, 33, 62, 7, 14, 720, NULL, NULL),
('R_69457efabb871', 'Steamed Prawns with Green Beans and Brown Rice', '[\"4oz Steamed Prawns\",\"1 cup green beans\",\"1\\/2 cup brown rice\"]', 455, 35, 45, 8, 10, 520, NULL, NULL),
('R_69457fc874f75', 'Savory Chinese Porridge with Egg and Vegetables', '[\"White rice (1\\/2 cup cooked)\",\"Water (2 cups)\",\"Egg (1)\",\"Spinach (1\\/2 cup chopped)\",\"Mushrooms (1\\/4 cup sliced)\",\"Ginger (1\\/2 tsp grated)\",\"Soy sauce (1 tsp, low sodium)\",\"Sesame oil (1\\/2 tsp)\",\"Scallions (1 tbsp chopped)\"]', 410, 12, 55, 6, 12, 250, NULL, '[\"Combine cooked white rice and water in a pot.\",\"Bring to a simmer over medium heat, stirring occasionally.\",\"Grate ginger and add to the pot.\",\"Whisk the egg lightly.\",\"Create a swirl in the simmering porridge and pour the whisked egg into the swirl.\",\"Add chopped spinach and sliced mushrooms to the pot.\",\"Stir gently until the spinach wilts and mushrooms are heated through.\",\"Stir in soy sauce and sesame oil.\",\"Garnish with chopped scallions before serving.\"]'),
('R_69457fc875987', 'Steamed Fish with Brown Rice and Stir-Fried Bok Choy', '[\"Cod fillet (120g)\",\"Brown rice (1 cup cooked)\",\"Bok choy (1 cup chopped)\",\"Garlic (1 clove minced)\",\"Soy sauce (1 tbsp, low sodium)\",\"Rice vinegar (1 tsp)\",\"Sesame oil (1\\/2 tsp)\",\"Cornstarch (1\\/2 tsp)\",\"Ginger (1\\/4 tsp grated)\",\"Water (2 tbsp)\"]', 620, 35, 75, 10, 18, 400, NULL, '[\"Prepare the fish: Pat the cod fillet dry. In a small bowl, mix soy sauce, rice vinegar, sesame oil, cornstarch, and grated ginger. Pour this mixture over the cod fillet and let it marinate for 10 minutes.\",\"Steam the fish: Place the marinated cod fillet in a heatproof dish. Add 2 tbsp of water to the dish. Cover the dish with foil or a lid and steam for 8-10 minutes, or until the fish is cooked through and flakes easily with a fork.\",\"Stir-fry the bok choy: While the fish is steaming, heat a wok or frying pan over medium-high heat. Add the minced garlic and stir-fry for 30 seconds until fragrant.\",\"Add bok choy: Add the chopped bok choy to the wok and stir-fry for 2-3 minutes, or until the bok choy is wilted but still crisp.\",\"Serve: Serve the steamed fish over a bed of cooked brown rice, alongside the stir-fried bok choy.\"]'),
('R_69457fc875ecc', 'Chicken and Vegetable Soup with Tofu and Noodles', '[\"Chicken breast (80g, diced)\",\"Broccoli florets (1\\/2 cup)\",\"Carrots (1\\/4 cup diced)\",\"Tofu (50g, cubed)\",\"Egg noodles (1\\/4 cup cooked, whole wheat)\",\"Chicken broth (1.5 cups, low sodium)\",\"Soy sauce (1 tsp, low sodium)\",\"Ginger (1\\/2 tsp grated)\",\"Garlic (1 clove minced)\",\"Green onions (1 tbsp chopped)\"]', 510, 30, 50, 8, 15, 350, NULL, NULL),
('R_6946352a5e44b', 'Oatmeal with Berries and Nuts', '[\"1\\/2 cup rolled oats\",\"1 cup unsweetened almond milk\",\"1\\/4 cup mixed berries\",\"1 tbsp chopped walnuts\",\"1 tsp chia seeds\"]', 380, 12, 55, 8, 12, 150, NULL, '[\"Combine oats and almond milk in a saucepan.\",\"Bring to a boil over medium heat, then reduce heat and simmer for 5-7 minutes, stirring occasionally, until oats are cooked to your desired consistency.\",\"Pour oatmeal into a bowl.\",\"Top with mixed berries, chopped walnuts, and chia seeds.\",\"Enjoy!\"]'),
('R_6946352a5e7ad', 'Chicken and Vegetable Stir-fry (Chinese)', '[\"4oz grilled chicken breast\",\"1 cup mixed vegetables (broccoli, carrots, bell peppers)\",\"1 tbsp low-sodium soy sauce\",\"1 tsp sesame oil\",\"1\\/2 cup brown rice\"]', 450, 35, 40, 7, 15, 550, NULL, '[\"Cook brown rice according to package directions.\",\"If chicken is not already grilled, grill 4oz of chicken breast.\",\"Prepare mixed vegetables: chop broccoli, carrots, and bell peppers into bite-sized pieces.\",\"In a wok or large skillet, heat sesame oil over medium-high heat.\",\"Add mixed vegetables to the wok and stir-fry for 3-5 minutes, or until tender-crisp.\",\"Add grilled chicken breast to the wok and stir-fry for 1-2 minutes to heat through.\",\"Pour in low-sodium soy sauce and stir to coat the chicken and vegetables.\",\"Serve the chicken and vegetable stir-fry over cooked brown rice.\"]'),
('R_6946352a5f7f1', 'Steamed Fish with Bok Choy and Ginger', '[\"4oz steamed cod\",\"1 cup bok choy\",\"1 tbsp grated ginger\",\"1 tbsp light soy sauce\",\"1\\/2 cup quinoa\"]', 420, 30, 35, 6, 10, 300, NULL, '[\"Cook quinoa according to package directions.\",\"Wash and chop bok choy.\",\"Place cod on a heat-safe plate or steamer basket.\",\"Arrange bok choy around the cod.\",\"Sprinkle grated ginger over the cod and bok choy.\",\"Drizzle light soy sauce over the cod and bok choy.\",\"Steam for 8-10 minutes, or until the fish is cooked through and flakes easily with a fork.\",\"Serve the steamed fish with bok choy and ginger over cooked quinoa.\"]'),
('R_6946352a5fa82', 'Scrambled Eggs with Spinach and Whole-Wheat Toast', '[\"2 eggs\",\"1 cup spinach\",\"1 slice whole-wheat toast\",\"1 tsp olive oil\"]', 350, 25, 25, 5, 15, 250, NULL, NULL),
('R_6946352a5fc9c', 'Malay Chicken Curry (light version) with Brown Rice', '[\"4oz chicken breast\",\"1\\/2 cup coconut milk (light)\",\"1\\/2 cup mixed vegetables (eggplant, okra, long beans)\",\"1\\/2 cup brown rice\",\"curry powder (low sodium)\"]', 480, 30, 55, 8, 18, 600, NULL, NULL),
('R_6946352a5fee3', 'Tofu and Vegetable Soup', '[\"4oz firm tofu\",\"1 cup mixed vegetables (mushrooms, carrots, celery)\",\"1 cup vegetable broth (low sodium)\",\"1 tbsp soy sauce (low sodium)\"]', 400, 20, 40, 10, 12, 200, NULL, NULL),
('R_6946352a600fb', 'Smoothie (Spinach, Banana, Almond Milk)', '[\"1 cup spinach\",\"1\\/2 banana\",\"1 cup unsweetened almond milk\",\"1 tbsp chia seeds\"]', 320, 10, 50, 8, 8, 50, NULL, NULL),
('R_6946352a60353', 'Salmon Salad with Mixed Greens', '[\"4oz grilled salmon\",\"2 cups mixed greens\",\"1 tbsp olive oil and vinegar dressing\",\"1\\/4 avocado\"]', 460, 35, 20, 6, 25, 350, NULL, NULL),
('R_6946352a60579', 'Stir-fried Beef with Broccoli and Noodles (Chinese)', '[\"4oz lean beef\",\"1 cup broccoli\",\"1\\/2 cup whole-wheat noodles\",\"1 tbsp low-sodium soy sauce\",\"1 tsp sesame oil\"]', 430, 30, 45, 7, 12, 400, NULL, NULL),
('R_6946352a607cb', 'Whole Wheat Pancakes (2) with Berries', '[\"2 whole wheat pancakes\",\"1\\/2 cup mixed berries\",\"1 tbsp maple syrup (sugar-free)\"]', 370, 10, 60, 8, 8, 200, NULL, NULL),
('R_6946352a60a1d', 'Chicken Nasi Lemak (light version)', '[\"4oz chicken breast\",\"1\\/4 cup brown rice\",\"1\\/4 cup cucumber\",\"1\\/4 cup peanuts\",\"light coconut sauce\"]', 470, 32, 50, 7, 18, 580, NULL, NULL),
('R_6946352a60c43', 'Lentil Soup with Whole-Wheat Bread', '[\"1.5 cups lentil soup\",\"1 slice whole-wheat bread\"]', 410, 22, 50, 12, 8, 250, NULL, NULL),
('R_6946352a60fac', 'Yogurt Parfait (Greek Yogurt, Granola, Berries)', '[\"1 cup Greek yogurt\",\"1\\/4 cup granola (low sugar)\",\"1\\/2 cup mixed berries\"]', 360, 20, 45, 7, 10, 120, NULL, '[\"Layer 1\\/4 cup granola in the bottom of a glass or bowl.\",\"Add 1\\/2 cup mixed berries on top of the granola.\",\"Top with 1 cup Greek yogurt.\",\"Repeat layers if desired.\",\"Enjoy!\"]'),
('R_6946352a61265', 'Vegetable and Tofu Spring Rolls (Chinese)', '[\"3 spring rolls (vegetable and tofu)\",\"low-sodium dipping sauce\"]', 440, 25, 40, 8, 18, 450, NULL, NULL),
('R_6946352a614cf', 'Baked Chicken with Roasted Vegetables', '[\"4oz baked chicken breast\",\"1 cup roasted vegetables (carrots, zucchini, bell peppers)\",\"1 tsp olive oil\"]', 420, 35, 30, 7, 12, 320, NULL, NULL),
('R_6946352a61712', 'Breakfast Burrito (Whole Wheat Tortilla, Egg, Veggies)', '[\"1 whole-wheat tortilla\",\"1 egg\",\"1\\/4 cup chopped vegetables (onions, peppers)\",\"1 tbsp salsa\"]', 380, 20, 40, 8, 12, 280, NULL, NULL),
('R_6946352a61936', 'Beef Rendang (light version) with Cauliflower Rice', '[\"4oz beef\",\"light coconut milk\",\"spices\",\"1 cup cauliflower rice\"]', 460, 35, 35, 6, 22, 550, NULL, NULL),
('R_6946352a61b1d', 'Shrimp and Vegetable Stir-fry (Chinese)', '[\"4oz shrimp\",\"1 cup mixed vegetables (broccoli, snap peas, carrots)\",\"1 tbsp low-sodium soy sauce\",\"1 tsp sesame oil\"]', 410, 30, 35, 7, 10, 380, NULL, NULL),
('R_6946352a61d4f', 'Cereal (Whole Grain) with Almond Milk and Berries', '[\"1 cup whole-grain cereal\",\"1 cup unsweetened almond milk\",\"1\\/2 cup mixed berries\"]', 340, 10, 60, 10, 6, 150, NULL, NULL),
('R_6946352a61f32', 'Chicken Salad Sandwich (Whole Wheat Bread)', '[\"4oz cooked chicken\",\"2 slices whole-wheat bread\",\"1 tbsp light mayonnaise\",\"lettuce, tomato\"]', 450, 30, 40, 8, 18, 400, NULL, NULL),
('R_6946352a62206', 'Steamed Fish with Brown Rice and Green Beans', '[\"4oz steamed fish\",\"1\\/2 cup brown rice\",\"1 cup green beans\",\"1 tbsp soy sauce (low sodium)\"]', 400, 30, 40, 8, 8, 300, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shopping_items`
--

CREATE TABLE `shopping_items` (
  `itemID` varchar(50) NOT NULL,
  `userID` varchar(50) NOT NULL,
  `item` varchar(255) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_items`
--

INSERT INTO `shopping_items` (`itemID`, `userID`, `item`, `createdAt`) VALUES
('S_6946b5ff355d3', 'U_6943abac29ea8', '4oz steamed cod', '2025-12-20 22:43:11'),
('S_6946b5ff897e0', 'U_6943abac29ea8', '1 tbsp grated ginger', '2025-12-20 22:43:11'),
('S_6946b5ffe5589', 'U_6943abac29ea8', '1 cup bok choy', '2025-12-20 22:43:11'),
('S_6946b6002a79b', 'U_6943abac29ea8', '1 tbsp light soy sauce', '2025-12-20 22:43:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phoneNo` varchar(20) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `role` enum('Admin','Dietitian','User','Caretaker') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `name`, `username`, `password`, `email`, `phoneNo`, `age`, `gender`, `role`) VALUES
('U_6943abac29ea8', 'Ron Tan', 'wheylong', '$2y$12$9maDwsvKlJEbqWE8Upqaye5HHk991OLz4AhL1cqDYpAm8RozGWoN2', 'rotanrontan@gmail.com', '0137383232', 66, 'Male', 'User'),
('U_69456611bd22d', 'admin', 'admin', '$2y$12$9kfOssWbIkavF9Myl2odjePpDDKzGJGCWyDvt0A7z3LBxk6NtctB.', 'rotan1rontan@gmail.com', '0137383232', 12, 'Male', 'Admin'),
('U_6945662a244b0', 'dietitian', 'dietitian', '$2y$12$zZZbXMmE7OsfnspmyuhKBehpy6qSpPxUcFayqhgd/tirOLgBsWYqe', 'diet@gmail.com', '123123123', 12, 'Male', 'Dietitian'),
('U_694693812f6a3', 'Dr. Alice', 'alice_diet', '$2y$12$x6Uq.BtTqJwpAeBT7y4ex.XsGqWxvrVd9/BQo0DXvjq2RrhBYq3me', 'alice@example.com', '', 0, 'Male', 'Dietitian'),
('U_694693812ff86', 'Dr. Bob', 'bob_diet', '$2y$12$82Rl/bA3EDn6HGK1eLWi0.Shng8Nk9HEYgJrNzcrm7aetjynEezti', 'bob@example.com', '', 0, 'Male', 'Dietitian'),
('U_6946e59b595dc', 'user1', 'user1', '$2y$12$AbMeV4MwjE9wzmck9sifdeqFA7r6s/zu/vXmen5DpHbUC.AE/3v/6', 'user@gmail.com', '012123123', 50, 'Male', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD PRIMARY KEY (`caretakerID`);

--
-- Indexes for table `dietitians`
--
ALTER TABLE `dietitians`
  ADD PRIMARY KEY (`dietitianID`);

--
-- Indexes for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD PRIMARY KEY (`dietPlanID`),
  ADD KEY `elderlyID` (`elderlyID`);

--
-- Indexes for table `diet_plan_approvals`
--
ALTER TABLE `diet_plan_approvals`
  ADD PRIMARY KEY (`approvalID`),
  ADD KEY `dietPlanID` (`dietPlanID`),
  ADD KEY `dietitianID` (`dietitianID`);

--
-- Indexes for table `elderly`
--
ALTER TABLE `elderly`
  ADD PRIMARY KEY (`elderlyID`),
  ADD KEY `caretakerID` (`caretakerID`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`foodID`),
  ADD KEY `mealID` (`mealID`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `food_logs`
--
ALTER TABLE `food_logs`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `elderlyID` (`elderlyID`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`mealID`),
  ADD KEY `dietPlanID` (`dietPlanID`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageID`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`profileID`),
  ADD KEY `elderlyID` (`elderlyID`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`progressID`),
  ADD KEY `elderlyID` (`elderlyID`),
  ADD KEY `dietPlanID` (`dietPlanID`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipeID`);

--
-- Indexes for table `shopping_items`
--
ALTER TABLE `shopping_items`
  ADD PRIMARY KEY (`itemID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD CONSTRAINT `caretakers_ibfk_1` FOREIGN KEY (`caretakerID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `dietitians`
--
ALTER TABLE `dietitians`
  ADD CONSTRAINT `dietitians_ibfk_1` FOREIGN KEY (`dietitianID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD CONSTRAINT `diet_plans_ibfk_1` FOREIGN KEY (`elderlyID`) REFERENCES `elderly` (`elderlyID`) ON DELETE CASCADE;

--
-- Constraints for table `diet_plan_approvals`
--
ALTER TABLE `diet_plan_approvals`
  ADD CONSTRAINT `diet_plan_approvals_ibfk_1` FOREIGN KEY (`dietPlanID`) REFERENCES `diet_plans` (`dietPlanID`) ON DELETE CASCADE,
  ADD CONSTRAINT `diet_plan_approvals_ibfk_2` FOREIGN KEY (`dietitianID`) REFERENCES `dietitians` (`dietitianID`) ON DELETE SET NULL;

--
-- Constraints for table `elderly`
--
ALTER TABLE `elderly`
  ADD CONSTRAINT `elderly_ibfk_1` FOREIGN KEY (`elderlyID`) REFERENCES `users` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `elderly_ibfk_2` FOREIGN KEY (`caretakerID`) REFERENCES `caretakers` (`caretakerID`) ON DELETE SET NULL;

--
-- Constraints for table `foods`
--
ALTER TABLE `foods`
  ADD CONSTRAINT `foods_ibfk_1` FOREIGN KEY (`mealID`) REFERENCES `meals` (`mealID`) ON DELETE CASCADE,
  ADD CONSTRAINT `foods_ibfk_2` FOREIGN KEY (`recipeID`) REFERENCES `recipes` (`recipeID`) ON DELETE SET NULL;

--
-- Constraints for table `food_logs`
--
ALTER TABLE `food_logs`
  ADD CONSTRAINT `food_logs_ibfk_1` FOREIGN KEY (`elderlyID`) REFERENCES `elderly` (`elderlyID`) ON DELETE CASCADE;

--
-- Constraints for table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`dietPlanID`) REFERENCES `diet_plans` (`dietPlanID`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`elderlyID`) REFERENCES `elderly` (`elderlyID`) ON DELETE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`elderlyID`) REFERENCES `elderly` (`elderlyID`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`dietPlanID`) REFERENCES `diet_plans` (`dietPlanID`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_items`
--
ALTER TABLE `shopping_items`
  ADD CONSTRAINT `shopping_items_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

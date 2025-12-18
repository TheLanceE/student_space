-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 04:53 PM
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
-- Database: `edumind`
--

-- SAFE IMPORT PREAMBLE ADDED: create DB if missing and drop tables instead of dropping database directory
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;
CREATE DATABASE IF NOT EXISTS `edumind` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `edumind`;
-- Drop tables if they exist to avoid DROP DATABASE which can fail on Windows when files remain
DROP TABLE IF EXISTS `user_sessions`, `teachers`, `tasks`, `students`, `scores`, `reward_redemptions`, `rewards`, `reports`, `recommendations`, `rate_limits`, `quiz_reports`, `quizzes`, `projects`, `points_ledger`, `oauth_tokens`, `logs`, `events`, `courses`, `challenge_completions`, `challenges`, `audit_log`, `admin_audit_log`, `admins`;


-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `lastLoginAt` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `createdAt`, `lastLoginAt`, `deleted_at`, `google_id`) VALUES
('admin_root', 'admin', '$2y$10$pUm1WIjYDvm2pmsHDvpSE.rH7SOQ0O2ApKKyO02.d/aL8EldYNaC6', 'Admin', '2025-08-30 16:18:47', '2025-12-18 15:05:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit_log`
--

CREATE TABLE `admin_audit_log` (
  `id` varchar(100) NOT NULL,
  `admin_id` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` varchar(100) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_role` enum('student','teacher','admin') DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `id` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 10,
  `category` varchar(100) DEFAULT NULL,
  `skillTags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skillTags`)),
  `prerequisiteLevel` int(11) DEFAULT NULL,
  `createdBy` varchar(50) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `description`, `level`, `points`, `category`, `skillTags`, `prerequisiteLevel`, `createdBy`, `createdAt`, `updatedAt`) VALUES
('ch_code_beginner', 'Code Beginner', 'Complete Introduction to Programming quiz', 1, 75, 'Computer Science', '[\"coding\", \"basics\"]', NULL, 'teach_kim', '2025-11-20 15:22:01', '2025-12-18 15:22:01'),
('ch_code_builder', 'Code Builder', 'Complete both Python and JavaScript quizzes', 2, 150, 'Computer Science', '[\"python\", \"javascript\"]', 1, 'teach_kim', '2025-11-24 15:22:01', '2025-12-18 15:22:01'),
('ch_full_stack', 'Full Stack Ready', 'Score 85%+ on all CS quizzes', 3, 300, 'Computer Science', '[\"full-stack\", \"mastery\"]', 2, 'teach_kim', '2025-11-28 15:22:01', '2025-12-18 15:22:01'),
('ch_historian', 'History Buff', 'Complete any history quiz', 1, 50, 'History', '[\"world-history\", \"ancient\"]', NULL, 'teach_mark', '2025-11-22 15:22:01', '2025-12-18 15:22:01'),
('ch_lab_expert', 'Lab Expert', 'Score 90%+ on Chemistry Basics', 3, 200, 'Science', '[\"chemistry\", \"lab-skills\"]', 2, 'teach_lee', '2025-11-26 15:22:01', '2025-12-18 15:22:01'),
('ch_math_adept', 'Math Adept', 'Score 80%+ on any math quiz', 2, 100, 'Mathematics', '[\"algebra\", \"problem-solving\"]', 1, 'teach_jane', '2025-11-20 15:22:01', '2025-12-18 15:22:01'),
('ch_math_basic', 'Math Novice', 'Complete 3 math quizzes with at least 60% score', 1, 50, 'Mathematics', '[\"arithmetic\", \"basics\"]', NULL, 'teach_jane', '2025-11-18 15:22:01', '2025-12-18 15:22:01'),
('ch_math_master', 'Math Master', 'Score 100% on an advanced math quiz', 3, 250, 'Mathematics', '[\"mastery\", \"advanced\"]', 2, 'teach_jane', '2025-11-23 15:22:01', '2025-12-18 15:22:01'),
('ch_perfectionist', 'Perfectionist', 'Get 100% on any 3 quizzes', 3, 500, 'Achievement', '[\"mastery\", \"perfection\"]', NULL, 'teach_jane', '2025-11-23 15:22:01', '2025-12-18 15:22:01'),
('ch_sci_explorer', 'Science Explorer', 'Complete your first science quiz', 1, 50, 'Science', '[\"biology\", \"chemistry\", \"physics\"]', NULL, 'teach_lee', '2025-11-18 15:22:01', '2025-12-18 15:22:01'),
('ch_sci_researcher', 'Science Researcher', 'Complete 5 science quizzes', 2, 150, 'Science', '[\"research\", \"experimentation\"]', 1, 'teach_lee', '2025-11-22 15:22:01', '2025-12-18 15:22:01'),
('ch_storyteller', 'Storyteller', 'Score 80%+ on Vocabulary Builder', 2, 125, 'English', '[\"vocabulary\", \"creativity\"]', 1, 'teach_sarah', '2025-11-25 15:22:01', '2025-12-18 15:22:01'),
('ch_streak_3', '3-Day Streak', 'Complete quizzes 3 days in a row', 1, 100, 'Consistency', '[\"dedication\", \"streak\"]', NULL, 'teach_jane', '2025-11-18 15:22:01', '2025-12-18 15:22:01'),
('ch_streak_7', 'Weekly Warrior', 'Complete quizzes 7 days in a row', 2, 250, 'Consistency', '[\"dedication\", \"streak\", \"weekly\"]', 1, 'teach_jane', '2025-11-20 15:22:01', '2025-12-18 15:22:01'),
('ch_time_traveler', 'Time Traveler', 'Score 80%+ on Ancient Empires', 2, 150, 'History', '[\"ancient-history\", \"civilizations\"]', 1, 'teach_mark', '2025-11-27 15:22:01', '2025-12-18 15:22:01'),
('ch_wordsmith', 'Wordsmith', 'Complete Grammar Essentials quiz', 1, 50, 'English', '[\"grammar\", \"writing\"]', NULL, 'teach_sarah', '2025-11-21 15:22:01', '2025-12-18 15:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `challenge_completions`
--

CREATE TABLE `challenge_completions` (
  `id` varchar(100) NOT NULL,
  `challengeId` varchar(100) NOT NULL,
  `studentId` varchar(50) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `completedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challenge_completions`
--

INSERT INTO `challenge_completions` (`id`, `challengeId`, `studentId`, `rating`, `completedAt`) VALUES
('cc_ava_1', 'ch_wordsmith', 'stu_ava', 4, '2025-12-16 15:22:01'),
('cc_ben_1', 'ch_math_basic', 'stu_ben', 5, '2025-12-17 15:22:01'),
('cc_charlotte_1', 'ch_streak_7', 'stu_charlotte', 4, '2025-12-16 15:22:01'),
('cc_emma_1', 'ch_math_basic', 'stu_emma', 5, '2025-12-09 15:22:01'),
('cc_ethan_1', 'ch_full_stack', 'stu_ethan', 5, '2025-12-17 15:22:01'),
('cc_isabella_1', 'ch_streak_3', 'stu_isabella', 5, '2025-12-15 15:22:01'),
('cc_james_1', 'ch_code_beginner', 'stu_james', 5, '2025-12-14 15:22:01'),
('cc_liam_1', 'ch_code_beginner', 'stu_liam', 4, '2025-12-11 15:22:01'),
('cc_liam_2', 'ch_code_builder', 'stu_liam', 4, '2025-12-15 15:22:01'),
('cc_mason_1', 'ch_perfectionist', 'stu_mason', 5, '2025-12-14 15:22:01'),
('cc_mia_1', 'ch_math_basic', 'stu_mia', 4, '2025-12-16 15:22:01'),
('cc_noah_1', 'ch_historian', 'stu_noah', 4, '2025-12-12 15:22:01'),
('cc_olivia_1', 'ch_math_adept', 'stu_olivia', 5, '2025-12-13 15:22:01'),
('cc_sk_1', 'ch_math_basic', 'stu_superkid', 5, '2025-12-05 15:22:01'),
('cc_sk_10', 'ch_streak_3', 'stu_superkid', 5, '2025-12-08 15:22:01'),
('cc_sk_11', 'ch_streak_7', 'stu_superkid', 5, '2025-12-12 15:22:01'),
('cc_sk_12', 'ch_perfectionist', 'stu_superkid', 5, '2025-12-16 15:22:01'),
('cc_sk_2', 'ch_math_adept', 'stu_superkid', 5, '2025-12-07 15:22:01'),
('cc_sk_3', 'ch_math_master', 'stu_superkid', 5, '2025-12-10 15:22:01'),
('cc_sk_4', 'ch_sci_explorer', 'stu_superkid', 5, '2025-12-06 15:22:01'),
('cc_sk_5', 'ch_sci_researcher', 'stu_superkid', 4, '2025-12-11 15:22:01'),
('cc_sk_6', 'ch_code_beginner', 'stu_superkid', 5, '2025-12-09 15:22:01'),
('cc_sk_7', 'ch_code_builder', 'stu_superkid', 5, '2025-12-15 15:22:01'),
('cc_sk_8', 'ch_wordsmith', 'stu_superkid', 4, '2025-12-14 15:22:01'),
('cc_sk_9', 'ch_historian', 'stu_superkid', 5, '2025-12-13 15:22:01'),
('cc_sophia_1', 'ch_sci_explorer', 'stu_sophia', 5, '2025-12-08 15:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `teacherId` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `createdAt` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `teacherId`, `status`, `createdAt`, `deleted_at`) VALUES
('art101', 'Art Foundations', 'Color theory, sketching, and design basics.', 'teach_omar', 'active', '2025-11-30 15:22:01', NULL),
('art201', 'Digital Illustration', 'Tablets, vectors, and composition.', 'teach_omar', 'pending', '2025-12-12 15:22:01', NULL),
('cs101', 'Introduction to Programming', 'Learn the basics of coding with Python and JavaScript.', 'teach_kim', 'active', '2025-11-08 15:22:01', NULL),
('cs201', 'Web Development', 'HTML, CSS, JavaScript, and modern frameworks.', 'teach_kim', 'pending', '2025-12-08 15:22:01', NULL),
('eng101', 'Creative Writing', 'Express yourself through stories, poetry, and essays.', 'teach_sarah', 'active', '2025-11-13 15:22:01', NULL),
('eng201', 'Public Speaking', 'Build confidence and communication skills.', 'teach_sarah', 'pending', '2025-12-10 15:22:01', NULL),
('geo101', 'World Geography', 'Maps, regions, and climate systems.', 'teach_nina', 'active', '2025-11-28 15:22:01', NULL),
('geo201', 'Climate Change Studies', 'Impacts, mitigation, and global policy.', 'teach_nina', 'pending', '2025-12-11 15:22:01', NULL),
('hist101', 'World History', 'Explore major events that shaped our world.', 'teach_mark', 'active', '2025-11-16 15:22:01', NULL),
('hist201', 'Ancient Civilizations', 'Egypt, Rome, Greece, and Mesopotamia.', 'teach_mark', 'pending', '2025-12-13 15:22:01', NULL),
('math101', 'Math Basics', 'Fundamental mathematics: arithmetic, fractions, and basic algebra.', 'teach_jane', 'active', '2025-10-29 15:22:01', NULL),
('math201', 'Algebra II', 'Advanced algebraic concepts and equations.', 'teach_jane', 'active', '2025-11-20 15:22:01', NULL),
('sci101', 'Science Basics', 'Introduction to biology, chemistry, and physics concepts.', 'teach_lee', 'active', '2025-11-03 15:22:01', NULL),
('sci201', 'Physics Fundamentals', 'Motion, forces, energy, and waves.', 'teach_lee', 'active', '2025-11-22 15:22:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `course` varchar(255) DEFAULT NULL,
  `type` enum('Lecture','Quiz','Webinar','Other') DEFAULT 'Other',
  `location` varchar(255) DEFAULT NULL,
  `maxParticipants` int(11) DEFAULT 30,
  `nbrParticipants` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `teacherId` varchar(50) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `date`, `startTime`, `endTime`, `course`, `type`, `location`, `maxParticipants`, `nbrParticipants`, `description`, `teacherId`, `createdAt`, `deleted_at`) VALUES
('evt_4', 'Python Workshop', '2025-12-25', '13:00:00', '15:00:00', 'Introduction to Programming', 'Lecture', 'Computer Lab A', 25, 12, 'Hands-on Python coding session for beginners', 'teach_kim', '2025-12-18 15:22:01', NULL),
('evt_5', 'Essay Writing Tips', '2025-12-22', '11:00:00', '12:30:00', 'Creative Writing', 'Lecture', 'Room 205', 30, 8, 'Learn techniques for compelling essays', 'teach_sarah', '2025-12-18 15:22:01', NULL),
('evt_6', 'History Documentary Screening', '2025-12-24', '14:00:00', '16:00:00', 'World History', 'Other', 'Auditorium', 100, 45, 'Screening of Ancient Rome documentary', 'teach_mark', '2025-12-18 15:22:01', NULL),
('evt_7', 'Math Olympiad Prep', '2025-12-20', '15:00:00', '17:00:00', 'Algebra II', 'Lecture', 'Room 101', 20, 15, 'Preparation for upcoming Math Olympiad', 'teach_jane', '2025-12-18 15:22:01', NULL),
('evt_8', 'Science Fair Info Session', '2025-12-26', '09:00:00', '10:00:00', 'Science Basics', 'Webinar', 'Online', 200, 67, 'Information about the annual science fair', 'teach_lee', '2025-12-18 15:22:01', NULL),
('evt_jane_1', 'Math Office Hours', '2025-12-19', '14:00:00', '15:00:00', 'Math Basics', 'Other', 'Room 102', 10, 5, 'Open office hours for math questions', 'teach_jane', '2025-12-18 15:22:01', NULL),
('evt_jane_2', 'Algebra Review Session', '2025-12-21', '10:00:00', '11:30:00', 'Algebra II', 'Lecture', 'Room 101', 30, 18, 'Review session before the midterm exam', 'teach_jane', '2025-12-18 15:22:01', NULL),
('evt_jane_3', 'Parent-Teacher Math Night', '2025-12-28', '18:00:00', '20:00:00', 'Math Basics', 'Webinar', 'Online', 100, 42, 'Tips for parents to support math learning at home', 'teach_jane', '2025-12-18 15:22:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` varchar(100) NOT NULL,
  `level` varchar(50) DEFAULT 'info',
  `message` text NOT NULL,
  `ts` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `level`, `message`, `ts`) VALUES
('log_10', 'info', 'Backup completed successfully', '2025-12-17 15:22:01'),
('log_11', 'info', 'Student mason completed Perfectionist challenge', '2025-12-14 15:22:01'),
('log_12', 'warn', 'Unusual activity on student profile uploads', '2025-12-16 15:22:01'),
('log_3', 'info', 'Student emma completed Math Novice challenge', '2025-12-09 15:22:01'),
('log_4', 'info', 'New course submitted: Web Development', '2025-12-08 15:22:01'),
('log_5', 'info', 'New course submitted: Public Speaking', '2025-12-10 15:22:01'),
('log_6', 'warn', 'High traffic detected on quiz server', '2025-12-13 15:22:01'),
('log_7', 'info', 'Student liam achieved perfect score on Programming Basics', '2025-12-09 15:22:01'),
('log_8', 'info', 'New course submitted: Ancient Civilizations', '2025-12-13 15:22:01'),
('log_9', 'error', 'Database connection timeout (recovered)', '2025-12-15 15:22:01'),
('log_sk_1', 'info', 'Student superkid achieved Math Master challenge!', '2025-12-10 15:22:01'),
('log_sk_2', 'info', 'Student superkid completed 7-day streak', '2025-12-12 15:22:01'),
('log_sk_3', 'info', 'Student superkid earned Perfectionist badge', '2025-12-16 15:22:01'),
('log_sk_4', 'info', 'Student superkid redeemed Genius Badge reward', '2025-12-15 15:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_tokens`
--

CREATE TABLE `oauth_tokens` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `user_role` enum('student','teacher','admin') NOT NULL,
  `provider` varchar(50) NOT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `points_ledger`
--

CREATE TABLE `points_ledger` (
  `id` varchar(100) NOT NULL,
  `studentId` varchar(50) NOT NULL,
  `delta` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `refType` varchar(50) DEFAULT NULL,
  `refId` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `points_ledger`
--

INSERT INTO `points_ledger` (`id`, `studentId`, `delta`, `reason`, `refType`, `refId`, `createdAt`) VALUES
('pl_1', 'stu_emma', 50, 'Completed: Math Novice challenge', 'challenge', 'ch_math_basic', '2025-12-09 15:22:01'),
('pl_10', 'stu_mason', 500, 'Completed: Perfectionist challenge', 'challenge', 'ch_perfectionist', '2025-12-14 15:22:01'),
('pl_11', 'stu_mia', 50, 'Completed: Math Novice challenge', 'challenge', 'ch_math_basic', '2025-12-16 15:22:01'),
('pl_12', 'stu_ethan', 300, 'Completed: Full Stack Ready challenge', 'challenge', 'ch_full_stack', '2025-12-17 15:22:01'),
('pl_13', 'stu_charlotte', 250, 'Completed: Weekly Warrior challenge', 'challenge', 'ch_streak_7', '2025-12-16 15:22:01'),
('pl_14', 'stu_ben', 50, 'Completed: Math Novice challenge', 'challenge', 'ch_math_basic', '2025-12-17 15:22:01'),
('pl_15', 'stu_emma', 20, 'Perfect score: Math Basics Quiz 1', 'quiz', 'math101_quiz1', '2025-12-08 15:22:01'),
('pl_16', 'stu_liam', 20, 'Perfect score: Programming Basics', 'quiz', 'cs101_quiz1', '2025-12-09 15:22:01'),
('pl_17', 'stu_sophia', 20, 'Perfect score: Science Basics Quiz 1', 'quiz', 'sci101_quiz1', '2025-12-07 15:22:01'),
('pl_18', 'stu_olivia', 20, 'Perfect score: Math Basics Quiz 1', 'quiz', 'math101_quiz1', '2025-12-11 15:22:01'),
('pl_19', 'stu_ava', 20, 'Perfect score: Grammar Essentials', 'quiz', 'eng101_quiz1', '2025-12-15 15:22:01'),
('pl_2', 'stu_liam', 75, 'Completed: Code Beginner challenge', 'challenge', 'ch_code_beginner', '2025-12-11 15:22:01'),
('pl_20', 'stu_isabella', 20, 'Perfect score: World Geography', 'quiz', 'geo101_quiz1', '2025-12-14 15:22:01'),
('pl_3', 'stu_liam', 150, 'Completed: Code Builder challenge', 'challenge', 'ch_code_builder', '2025-12-15 15:22:01'),
('pl_4', 'stu_sophia', 50, 'Completed: Science Explorer challenge', 'challenge', 'ch_sci_explorer', '2025-12-08 15:22:01'),
('pl_5', 'stu_noah', 50, 'Completed: History Buff challenge', 'challenge', 'ch_historian', '2025-12-12 15:22:01'),
('pl_6', 'stu_olivia', 100, 'Completed: Math Adept challenge', 'challenge', 'ch_math_adept', '2025-12-13 15:22:01'),
('pl_7', 'stu_james', 75, 'Completed: Code Beginner challenge', 'challenge', 'ch_code_beginner', '2025-12-14 15:22:01'),
('pl_8', 'stu_ava', 50, 'Completed: Wordsmith challenge', 'challenge', 'ch_wordsmith', '2025-12-16 15:22:01'),
('pl_9', 'stu_isabella', 100, 'Completed: 3-Day Streak challenge', 'challenge', 'ch_streak_3', '2025-12-15 15:22:01'),
('pl_sk_1', 'stu_superkid', 50, 'Completed: Math Novice challenge', 'challenge', 'ch_math_basic', '2025-12-05 15:22:01'),
('pl_sk_10', 'stu_superkid', 100, 'Completed: 3-Day Streak challenge', 'challenge', 'ch_streak_3', '2025-12-08 15:22:01'),
('pl_sk_11', 'stu_superkid', 250, 'Completed: Weekly Warrior challenge', 'challenge', 'ch_streak_7', '2025-12-12 15:22:01'),
('pl_sk_12', 'stu_superkid', 500, 'Completed: Perfectionist challenge', 'challenge', 'ch_perfectionist', '2025-12-16 15:22:01'),
('pl_sk_13', 'stu_superkid', 20, 'Perfect score: Math Basics Quiz 1', 'quiz', 'math101_quiz1', '2025-12-04 15:22:01'),
('pl_sk_14', 'stu_superkid', 20, 'Perfect score: Math Basics Quiz 2', 'quiz', 'math101_quiz2', '2025-12-06 15:22:01'),
('pl_sk_15', 'stu_superkid', 20, 'Perfect score: Algebra II Equations', 'quiz', 'math201_quiz1', '2025-12-10 15:22:01'),
('pl_sk_16', 'stu_superkid', 20, 'Perfect score: Science Basics Quiz 1', 'quiz', 'sci101_quiz1', '2025-12-05 15:22:01'),
('pl_sk_17', 'stu_superkid', 20, 'Perfect score: Programming Basics', 'quiz', 'cs101_quiz1', '2025-12-09 15:22:01'),
('pl_sk_18', 'stu_superkid', 20, 'Perfect score: JavaScript Intro', 'quiz', 'cs101_quiz3', '2025-12-15 15:22:01'),
('pl_sk_19', 'stu_superkid', 20, 'Perfect score: Grammar Essentials', 'quiz', 'eng101_quiz1', '2025-12-14 15:22:01'),
('pl_sk_2', 'stu_superkid', 100, 'Completed: Math Adept challenge', 'challenge', 'ch_math_adept', '2025-12-07 15:22:01'),
('pl_sk_20', 'stu_superkid', 20, 'Perfect score: Ancient Empires', 'quiz', 'hist101_quiz2', '2025-12-17 15:22:01'),
('pl_sk_21', 'stu_superkid', 20, 'Perfect score: Map Skills', 'quiz', 'geo101_quiz1', '2025-12-16 15:22:01'),
('pl_sk_3', 'stu_superkid', 250, 'Completed: Math Master challenge', 'challenge', 'ch_math_master', '2025-12-10 15:22:01'),
('pl_sk_4', 'stu_superkid', 50, 'Completed: Science Explorer challenge', 'challenge', 'ch_sci_explorer', '2025-12-06 15:22:01'),
('pl_sk_5', 'stu_superkid', 150, 'Completed: Science Researcher challenge', 'challenge', 'ch_sci_researcher', '2025-12-11 15:22:01'),
('pl_sk_6', 'stu_superkid', 75, 'Completed: Code Beginner challenge', 'challenge', 'ch_code_beginner', '2025-12-09 15:22:01'),
('pl_sk_7', 'stu_superkid', 150, 'Completed: Code Builder challenge', 'challenge', 'ch_code_builder', '2025-12-15 15:22:01'),
('pl_sk_8', 'stu_superkid', 50, 'Completed: Wordsmith challenge', 'challenge', 'ch_wordsmith', '2025-12-14 15:22:01'),
('pl_sk_9', 'stu_superkid', 50, 'Completed: History Buff challenge', 'challenge', 'ch_historian', '2025-12-13 15:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` varchar(100) NOT NULL,
  `projectName` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `createdBy` varchar(50) NOT NULL,
  `assignedTo` varchar(50) DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','on_hold') DEFAULT 'not_started',
  `dueDate` date DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `projectName`, `description`, `createdBy`, `assignedTo`, `status`, `dueDate`, `createdAt`, `updatedAt`, `deleted_at`) VALUES
('proj_2f3fc2ea6cd0f516', 'test', 'Hello', 'stu_superkid', 'stu_superkid', 'in_progress', '2025-12-25', '2025-12-18 16:47:45', '2025-12-18 16:47:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` varchar(100) NOT NULL,
  `courseId` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `durationSec` int(11) DEFAULT 60,
  `difficulty` varchar(50) DEFAULT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`questions`)),
  `createdBy` varchar(50) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `courseId`, `title`, `durationSec`, `difficulty`, `questions`, `createdBy`, `createdAt`, `deleted_at`) VALUES
('art101_quiz1', 'art101', 'Color Theory', 80, 'beginner', '[{\"id\":\"a101_q1\",\"text\":\"Primary colors (RGB model):\",\"options\":[\"Red, Green, Blue\",\"Red, Yellow, Blue\",\"Cyan, Magenta, Yellow\",\"Red, Green, Yellow\"],\"correctIndex\":0},{\"id\":\"a101_q2\",\"text\":\"Complement of blue in RGB:\",\"options\":[\"Red\",\"Green\",\"Cyan\",\"Orange\"],\"correctIndex\":1},{\"id\":\"a101_q3\",\"text\":\"Value refers to:\",\"options\":[\"Lightness/darkness\",\"Texture\",\"Hue\",\"Saturation\"],\"correctIndex\":0},{\"id\":\"a101_q4\",\"text\":\"Warm color example:\",\"options\":[\"Blue\",\"Green\",\"Purple\",\"Orange\"],\"correctIndex\":3},{\"id\":\"a101_q5\",\"text\":\"Analogous palette uses colors:\",\"options\":[\"Opposite on wheel\",\"Next to each other\",\"Randomly picked\",\"Only neutrals\"],\"correctIndex\":1}]', 'teach_omar', '2025-12-06 15:22:01', NULL),
('art201_quiz1', 'art201', 'Digital Illustration Basics', 110, 'intermediate', '[{\"id\":\"a201_q1\",\"text\":\"Common vector format:\",\"options\":[\"SVG\",\"JPG\",\"BMP\",\"TIFF\"],\"correctIndex\":0},{\"id\":\"a201_q2\",\"text\":\"Tablet pressure helps control:\",\"options\":[\"Line weight\",\"Canvas size\",\"File type\",\"Export speed\"],\"correctIndex\":0},{\"id\":\"a201_q3\",\"text\":\"Layer blending mode for lightening:\",\"options\":[\"Multiply\",\"Screen\",\"Overlay\",\"Difference\"],\"correctIndex\":1},{\"id\":\"a201_q4\",\"text\":\"Shortcut concept:\",\"options\":[\"Use only mouse\",\"Flatten often\",\"Name layers\",\"Avoid references\"],\"correctIndex\":2},{\"id\":\"a201_q5\",\"text\":\"Resolution for print (dpi):\",\"options\":[\"72\",\"96\",\"150\",\"300\"],\"correctIndex\":3}]', 'teach_omar', '2025-12-11 15:22:01', NULL),
('challenge_daily', 'math101', 'Daily Math Challenge', 120, 'intermediate', '[{\"id\":\"c1_q1\",\"text\":\"15 + 27 = ?\",\"options\":[\"40\",\"41\",\"42\",\"43\"],\"correctIndex\":2},{\"id\":\"c1_q2\",\"text\":\"100 - 37 = ?\",\"options\":[\"62\",\"63\",\"64\",\"65\"],\"correctIndex\":1},{\"id\":\"c1_q3\",\"text\":\"12 × 8 = ?\",\"options\":[\"84\",\"92\",\"96\",\"104\"],\"correctIndex\":2},{\"id\":\"c1_q4\",\"text\":\"144 / 12 = ?\",\"options\":[\"10\",\"11\",\"12\",\"13\"],\"correctIndex\":2},{\"id\":\"c1_q5\",\"text\":\"Solve: 2x + 5 = 15\",\"options\":[\"3\",\"4\",\"5\",\"6\"],\"correctIndex\":2},{\"id\":\"c1_q6\",\"text\":\"√64 = ?\",\"options\":[\"6\",\"7\",\"8\",\"9\"],\"correctIndex\":2},{\"id\":\"c1_q7\",\"text\":\"3² + 4² = ?\",\"options\":[\"20\",\"23\",\"25\",\"27\"],\"correctIndex\":2},{\"id\":\"c1_q8\",\"text\":\"50% of 200 = ?\",\"options\":[\"75\",\"100\",\"125\",\"150\"],\"correctIndex\":1},{\"id\":\"c1_q9\",\"text\":\"Prime number after 7:\",\"options\":[\"8\",\"9\",\"10\",\"11\"],\"correctIndex\":3},{\"id\":\"c1_q10\",\"text\":\"Area of 5×6 rectangle:\",\"options\":[\"11\",\"22\",\"30\",\"36\"],\"correctIndex\":2}]', 'teach_jane', '2025-12-03 15:22:01', NULL),
('cs101_quiz1', 'cs101', 'Programming Basics', 90, 'beginner', '[{\"id\":\"cs1_q1\",\"text\":\"What does HTML stand for?\",\"options\":[\"Hyper Text Markup Language\",\"High Tech Modern Language\",\"Home Tool Markup Language\",\"Hyper Tool Multi Language\"],\"correctIndex\":0},{\"id\":\"cs1_q2\",\"text\":\"Which is NOT a programming language?\",\"options\":[\"Python\",\"JavaScript\",\"HTTP\",\"Java\"],\"correctIndex\":2},{\"id\":\"cs1_q3\",\"text\":\"What is a variable?\",\"options\":[\"A constant value\",\"A container for data\",\"A type of loop\",\"A function\"],\"correctIndex\":1},{\"id\":\"cs1_q4\",\"text\":\"What does CSS style?\",\"options\":[\"Logic\",\"Databases\",\"Web pages\",\"Servers\"],\"correctIndex\":2},{\"id\":\"cs1_q5\",\"text\":\"Loop that runs at least once:\",\"options\":[\"for\",\"while\",\"do-while\",\"if\"],\"correctIndex\":2}]', 'teach_kim', '2025-11-28 15:22:01', NULL),
('cs101_quiz2', 'cs101', 'Python Fundamentals', 120, 'beginner', '[{\"id\":\"py_q1\",\"text\":\"Python comment starts with:\",\"options\":[\"//\",\"/*\",\"#\",\"--\"],\"correctIndex\":2},{\"id\":\"py_q2\",\"text\":\"Print function in Python:\",\"options\":[\"echo()\",\"console.log()\",\"print()\",\"printf()\"],\"correctIndex\":2},{\"id\":\"py_q3\",\"text\":\"Python list is defined with:\",\"options\":[\"{}\",\"[]\",\"()\",\"<>\"],\"correctIndex\":1},{\"id\":\"py_q4\",\"text\":\"What is len([1,2,3])?\",\"options\":[\"1\",\"2\",\"3\",\"4\"],\"correctIndex\":2},{\"id\":\"py_q5\",\"text\":\"Python is case-sensitive?\",\"options\":[\"Yes\",\"No\",\"Sometimes\",\"Only for variables\"],\"correctIndex\":0}]', 'teach_kim', '2025-12-02 15:22:01', NULL),
('cs101_quiz3', 'cs101', 'JavaScript Intro', 120, 'intermediate', '[{\"id\":\"js_q1\",\"text\":\"Declare a variable in JS:\",\"options\":[\"var x = 5\",\"x := 5\",\"int x = 5\",\"variable x = 5\"],\"correctIndex\":0},{\"id\":\"js_q2\",\"text\":\"JS array method to add item:\",\"options\":[\"add()\",\"push()\",\"insert()\",\"append()\"],\"correctIndex\":1},{\"id\":\"js_q3\",\"text\":\"=== checks:\",\"options\":[\"Value only\",\"Type only\",\"Value and type\",\"Reference\"],\"correctIndex\":2},{\"id\":\"js_q4\",\"text\":\"undefined vs null:\",\"options\":[\"Same thing\",\"undefined=not assigned, null=intentional\",\"undefined=error\",\"null=not assigned\"],\"correctIndex\":1},{\"id\":\"js_q5\",\"text\":\"Event listener syntax:\",\"options\":[\"on.click()\",\"addEvent()\",\"addEventListener()\",\"attachEvent()\"],\"correctIndex\":2}]', 'teach_kim', '2025-12-07 15:22:01', NULL),
('eng101_quiz1', 'eng101', 'Grammar Essentials', 90, 'beginner', '[{\"id\":\"eng1_q1\",\"text\":\"Plural of child:\",\"options\":[\"Childs\",\"Children\",\"Childes\",\"Childern\"],\"correctIndex\":1},{\"id\":\"eng1_q2\",\"text\":\"Past tense of go:\",\"options\":[\"Goed\",\"Gone\",\"Went\",\"Going\"],\"correctIndex\":2},{\"id\":\"eng1_q3\",\"text\":\"A noun names a:\",\"options\":[\"Action\",\"Person/place/thing\",\"Description\",\"Connection\"],\"correctIndex\":1},{\"id\":\"eng1_q4\",\"text\":\"Their/There/They are - which is possessive?\",\"options\":[\"There\",\"Their\",\"They are\",\"All of them\"],\"correctIndex\":1},{\"id\":\"eng1_q5\",\"text\":\"An adjective describes a:\",\"options\":[\"Verb\",\"Noun\",\"Preposition\",\"Conjunction\"],\"correctIndex\":1}]', 'teach_sarah', '2025-11-23 15:22:01', NULL),
('eng101_quiz2', 'eng101', 'Vocabulary Builder', 90, 'intermediate', '[{\"id\":\"voc_q1\",\"text\":\"Synonym for happy:\",\"options\":[\"Sad\",\"Joyful\",\"Angry\",\"Tired\"],\"correctIndex\":1},{\"id\":\"voc_q2\",\"text\":\"Antonym for brave:\",\"options\":[\"Fearless\",\"Bold\",\"Cowardly\",\"Strong\"],\"correctIndex\":2},{\"id\":\"voc_q3\",\"text\":\"Benevolent means:\",\"options\":[\"Evil\",\"Kind\",\"Neutral\",\"Angry\"],\"correctIndex\":1},{\"id\":\"voc_q4\",\"text\":\"A metaphor is:\",\"options\":[\"A direct comparison\",\"An implied comparison\",\"An exaggeration\",\"A question\"],\"correctIndex\":1},{\"id\":\"voc_q5\",\"text\":\"Ubiquitous means:\",\"options\":[\"Rare\",\"Common everywhere\",\"Hidden\",\"Ancient\"],\"correctIndex\":1}]', 'teach_sarah', '2025-11-30 15:22:01', NULL),
('geo101_quiz1', 'geo101', 'Map Skills', 90, 'beginner', '[{\"id\":\"g1_q1\",\"text\":\"Largest ocean:\",\"options\":[\"Atlantic\",\"Indian\",\"Pacific\",\"Arctic\"],\"correctIndex\":2},{\"id\":\"g1_q2\",\"text\":\"The Equator is a line of:\",\"options\":[\"Longitude\",\"Latitude\",\"Altitude\",\"Attitude\"],\"correctIndex\":1},{\"id\":\"g1_q3\",\"text\":\"Mount Everest is in:\",\"options\":[\"Andes\",\"Alps\",\"Himalayas\",\"Rockies\"],\"correctIndex\":2},{\"id\":\"g1_q4\",\"text\":\"Sahara is in:\",\"options\":[\"Asia\",\"Europe\",\"Africa\",\"Australia\"],\"correctIndex\":2},{\"id\":\"g1_q5\",\"text\":\"Number of continents:\",\"options\":[\"5\",\"6\",\"7\",\"8\"],\"correctIndex\":2}]', 'teach_nina', '2025-12-04 15:22:01', NULL),
('geo201_quiz1', 'geo201', 'Climate Systems', 120, 'intermediate', '[{\"id\":\"gc_q1\",\"text\":\"Greenhouse gas NOT commonly cited:\",\"options\":[\"CO2\",\"CH4\",\"N2\",\"N2O\"],\"correctIndex\":2},{\"id\":\"gc_q2\",\"text\":\"Current CO2 ppm ~:\",\"options\":[\"150\",\"280\",\"420\",\"600\"],\"correctIndex\":2},{\"id\":\"gc_q3\",\"text\":\"El Niño impacts:\",\"options\":[\"Pacific temperatures\",\"Atlantic salinity\",\"Indian Ocean depth\",\"Arctic ice age\"],\"correctIndex\":0},{\"id\":\"gc_q4\",\"text\":\"Main driver of sea level rise:\",\"options\":[\"Tectonics\",\"Thermal expansion + ice melt\",\"River flooding\",\"Sand mining\"],\"correctIndex\":1},{\"id\":\"gc_q5\",\"text\":\"IPCC stands for:\",\"options\":[\"International Panel on Climate Change\",\"Intergovernmental Panel on Climate Change\",\"Interplanetary Climate Council\",\"International Pollution Control Commission\"],\"correctIndex\":1}]', 'teach_nina', '2025-12-09 15:22:01', NULL),
('hist101_quiz1', 'hist101', 'World History Basics', 90, 'beginner', '[{\"id\":\"h1_q1\",\"text\":\"World War II ended in:\",\"options\":[\"1943\",\"1944\",\"1945\",\"1946\"],\"correctIndex\":2},{\"id\":\"h1_q2\",\"text\":\"Who discovered America?\",\"options\":[\"Columbus\",\"Magellan\",\"Cook\",\"Vespucci\"],\"correctIndex\":0},{\"id\":\"h1_q3\",\"text\":\"Great Wall is in:\",\"options\":[\"Japan\",\"India\",\"China\",\"Korea\"],\"correctIndex\":2},{\"id\":\"h1_q4\",\"text\":\"French Revolution started:\",\"options\":[\"1689\",\"1789\",\"1889\",\"1989\"],\"correctIndex\":1},{\"id\":\"h1_q5\",\"text\":\"First President of USA:\",\"options\":[\"Lincoln\",\"Jefferson\",\"Adams\",\"Washington\"],\"correctIndex\":3}]', 'teach_mark', '2025-11-26 15:22:01', NULL),
('hist101_quiz2', 'hist101', 'Ancient Empires', 120, 'intermediate', '[{\"id\":\"anc_q1\",\"text\":\"Capital of Roman Empire:\",\"options\":[\"Athens\",\"Rome\",\"Constantinople\",\"Alexandria\"],\"correctIndex\":1},{\"id\":\"anc_q2\",\"text\":\"Pyramids built by:\",\"options\":[\"Greeks\",\"Romans\",\"Egyptians\",\"Persians\"],\"correctIndex\":2},{\"id\":\"anc_q3\",\"text\":\"Alexander the Great was from:\",\"options\":[\"Rome\",\"Persia\",\"Macedonia\",\"Egypt\"],\"correctIndex\":2},{\"id\":\"anc_q4\",\"text\":\"Democracy originated in:\",\"options\":[\"Rome\",\"Athens\",\"Sparta\",\"Persia\"],\"correctIndex\":1},{\"id\":\"anc_q5\",\"text\":\"Silk Road connected:\",\"options\":[\"Africa-Europe\",\"Europe-Americas\",\"Asia-Europe\",\"Australia-Asia\"],\"correctIndex\":2}]', 'teach_mark', '2025-12-03 15:22:01', NULL),
('math101_quiz1', 'math101', 'Math Basics · Quiz 1', 60, 'beginner', '[{\"id\":\"m1_q1\",\"text\":\"2 + 2 = ?\",\"options\":[\"3\",\"4\",\"5\",\"6\"],\"correctIndex\":1},{\"id\":\"m1_q2\",\"text\":\"5 - 3 = ?\",\"options\":[\"1\",\"2\",\"3\",\"4\"],\"correctIndex\":1},{\"id\":\"m1_q3\",\"text\":\"10 / 2 = ?\",\"options\":[\"2\",\"4\",\"5\",\"10\"],\"correctIndex\":2},{\"id\":\"m1_q4\",\"text\":\"3 × 3 = ?\",\"options\":[\"6\",\"7\",\"8\",\"9\"],\"correctIndex\":3},{\"id\":\"m1_q5\",\"text\":\"Solve for x: x + 1 = 4\",\"options\":[\"1\",\"2\",\"3\",\"4\"],\"correctIndex\":2}]', 'teach_jane', '2025-11-28 15:22:01', NULL),
('math101_quiz2', 'math101', 'Math Basics Quiz 2', 90, 'beginner', '[{\"id\":\"m2_q1\",\"text\":\"7 x 8 = ?\",\"options\":[\"54\",\"56\",\"58\",\"60\"],\"correctIndex\":1},{\"id\":\"m2_q2\",\"text\":\"What is 25% of 80?\",\"options\":[\"15\",\"18\",\"20\",\"25\"],\"correctIndex\":2},{\"id\":\"m2_q3\",\"text\":\"9 squared = ?\",\"options\":[\"72\",\"81\",\"90\",\"99\"],\"correctIndex\":1},{\"id\":\"m2_q4\",\"text\":\"What is the next prime after 13?\",\"options\":[\"15\",\"16\",\"17\",\"19\"],\"correctIndex\":2},{\"id\":\"m2_q5\",\"text\":\"15 / 3 + 2 = ?\",\"options\":[\"5\",\"6\",\"7\",\"8\"],\"correctIndex\":2}]', 'teach_jane', '2025-12-03 15:22:01', NULL),
('math101_quiz3', 'math101', 'Fractions Challenge', 120, 'intermediate', '[{\"id\":\"m3_q1\",\"text\":\"1/2 + 1/4 = ?\",\"options\":[\"1/6\",\"2/6\",\"3/4\",\"1/4\"],\"correctIndex\":2},{\"id\":\"m3_q2\",\"text\":\"3/5 x 10 = ?\",\"options\":[\"3\",\"5\",\"6\",\"8\"],\"correctIndex\":2},{\"id\":\"m3_q3\",\"text\":\"2/3 / 1/3 = ?\",\"options\":[\"1\",\"2\",\"3\",\"6\"],\"correctIndex\":1},{\"id\":\"m3_q4\",\"text\":\"Convert 0.75 to fraction\",\"options\":[\"1/2\",\"2/3\",\"3/4\",\"4/5\"],\"correctIndex\":2},{\"id\":\"m3_q5\",\"text\":\"What is 3/8 as decimal?\",\"options\":[\"0.375\",\"0.38\",\"0.35\",\"0.325\"],\"correctIndex\":0}]', 'teach_jane', '2025-12-06 15:22:01', NULL),
('math201_quiz1', 'math201', 'Algebra II Equations', 150, 'advanced', '[{\"id\":\"a1_q1\",\"text\":\"Solve: 2x + 5 = 17\",\"options\":[\"4\",\"5\",\"6\",\"7\"],\"correctIndex\":2},{\"id\":\"a1_q2\",\"text\":\"Solve: 3(x-2) = 15\",\"options\":[\"5\",\"6\",\"7\",\"8\"],\"correctIndex\":2},{\"id\":\"a1_q3\",\"text\":\"If y = 2x + 3, what is y when x = 5?\",\"options\":[\"10\",\"11\",\"12\",\"13\"],\"correctIndex\":3},{\"id\":\"a1_q4\",\"text\":\"Simplify: 4x + 3x - 2x\",\"options\":[\"3x\",\"4x\",\"5x\",\"9x\"],\"correctIndex\":2},{\"id\":\"a1_q5\",\"text\":\"Factor: x squared - 9\",\"options\":[\"(x-3)(x+3)\",\"(x-9)(x+1)\",\"(x-3) squared\",\"Cannot factor\"],\"correctIndex\":0}]', 'teach_jane', '2025-12-10 15:22:01', NULL),
('sci101_quiz1', 'sci101', 'Science Basics · Quiz 1', 60, 'beginner', '[{\"id\":\"s1_q1\",\"text\":\"Water boils at what °C?\",\"options\":[\"50\",\"80\",\"100\",\"120\"],\"correctIndex\":2},{\"id\":\"s1_q2\",\"text\":\"What gas do plants produce?\",\"options\":[\"CO₂\",\"O₂\",\"N₂\",\"CH₄\"],\"correctIndex\":1},{\"id\":\"s1_q3\",\"text\":\"Earth is the ___ planet from the Sun.\",\"options\":[\"2nd\",\"3rd\",\"4th\",\"5th\"],\"correctIndex\":1},{\"id\":\"s1_q4\",\"text\":\"Basic unit of life is the:\",\"options\":[\"Atom\",\"Molecule\",\"Cell\",\"Organ\"],\"correctIndex\":2},{\"id\":\"s1_q5\",\"text\":\"H₂O is:\",\"options\":[\"Oxygen\",\"Hydrogen\",\"Water\",\"Helium\"],\"correctIndex\":2}]', 'teach_lee', '2025-11-30 15:22:01', NULL),
('sci101_quiz2', 'sci101', 'The Human Body', 90, 'beginner', '[{\"id\":\"sb_q1\",\"text\":\"How many bones in adult body?\",\"options\":[\"186\",\"206\",\"226\",\"246\"],\"correctIndex\":1},{\"id\":\"sb_q2\",\"text\":\"Largest organ in the body?\",\"options\":[\"Heart\",\"Liver\",\"Skin\",\"Brain\"],\"correctIndex\":2},{\"id\":\"sb_q3\",\"text\":\"Blood is pumped by the:\",\"options\":[\"Brain\",\"Lungs\",\"Heart\",\"Kidney\"],\"correctIndex\":2},{\"id\":\"sb_q4\",\"text\":\"Oxygen is carried by:\",\"options\":[\"White blood cells\",\"Platelets\",\"Red blood cells\",\"Plasma\"],\"correctIndex\":2},{\"id\":\"sb_q5\",\"text\":\"The brain is part of the:\",\"options\":[\"Circulatory system\",\"Nervous system\",\"Digestive system\",\"Respiratory system\"],\"correctIndex\":1}]', 'teach_lee', '2025-12-04 15:22:01', NULL),
('sci101_quiz3', 'sci101', 'Chemistry Basics', 120, 'intermediate', '[{\"id\":\"ch_q1\",\"text\":\"Symbol for Gold?\",\"options\":[\"Go\",\"Gd\",\"Au\",\"Ag\"],\"correctIndex\":2},{\"id\":\"ch_q2\",\"text\":\"pH of pure water?\",\"options\":[\"5\",\"6\",\"7\",\"8\"],\"correctIndex\":2},{\"id\":\"ch_q3\",\"text\":\"Atomic number represents:\",\"options\":[\"Mass\",\"Protons\",\"Neutrons\",\"Electrons\"],\"correctIndex\":1},{\"id\":\"ch_q4\",\"text\":\"NaCl is:\",\"options\":[\"Sugar\",\"Baking soda\",\"Salt\",\"Vinegar\"],\"correctIndex\":2},{\"id\":\"ch_q5\",\"text\":\"Noble gases are in Group:\",\"options\":[\"1\",\"8\",\"17\",\"18\"],\"correctIndex\":3}]', 'teach_lee', '2025-12-08 15:22:01', NULL),
('sci201_quiz1', 'sci201', 'Physics Motion', 150, 'intermediate', '[{\"id\":\"ph_q1\",\"text\":\"Speed = Distance divided by ?\",\"options\":[\"Mass\",\"Force\",\"Time\",\"Velocity\"],\"correctIndex\":2},{\"id\":\"ph_q2\",\"text\":\"Unit of Force?\",\"options\":[\"Joule\",\"Watt\",\"Newton\",\"Pascal\"],\"correctIndex\":2},{\"id\":\"ph_q3\",\"text\":\"Acceleration due to gravity approx\",\"options\":[\"8 m/s2\",\"9.8 m/s2\",\"10.8 m/s2\",\"12 m/s2\"],\"correctIndex\":1},{\"id\":\"ph_q4\",\"text\":\"F = m times ?\",\"options\":[\"v\",\"t\",\"a\",\"d\"],\"correctIndex\":2},{\"id\":\"ph_q5\",\"text\":\"Object at rest stays at rest - this is:\",\"options\":[\"Newton 1st Law\",\"Newton 2nd Law\",\"Newton 3rd Law\",\"Gravity Law\"],\"correctIndex\":0}]', 'teach_lee', '2025-12-12 15:22:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_reports`
--

CREATE TABLE `quiz_reports` (
  `id` varchar(100) NOT NULL,
  `quizId` varchar(100) NOT NULL,
  `questionId` varchar(100) DEFAULT NULL,
  `reportedBy` varchar(50) NOT NULL,
  `reportType` enum('incorrect_answer','wrong_display','typo','other') DEFAULT 'other',
  `description` text NOT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `createdAt` datetime DEFAULT current_timestamp(),
  `reviewedBy` varchar(50) DEFAULT NULL,
  `reviewedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_reports`
--

INSERT INTO `quiz_reports` (`id`, `quizId`, `questionId`, `reportedBy`, `reportType`, `description`, `status`, `createdAt`, `reviewedBy`, `reviewedAt`) VALUES
('qr_1', 'math101_quiz2', 'm2_q3', 'stu_emma', 'typo', 'The question shows 9 squared but the answer options are confusing', 'pending', '2025-12-14 15:22:01', NULL, NULL),
('qr_2', 'sci101_quiz3', 'ch_q5', 'stu_liam', 'incorrect_answer', 'Noble gases are in Group 18, not Group 8 - please verify', 'reviewed', '2025-12-12 15:22:01', NULL, NULL),
('qr_3', 'cs101_quiz1', 'cs1_q2', 'stu_sophia', 'other', 'HTTP could be considered a language in some contexts', 'dismissed', '2025-12-13 15:22:01', NULL, NULL),
('qr_4', 'hist101_quiz1', 'h1_q2', 'stu_noah', 'incorrect_answer', 'Columbus didnt really discover America, Vikings were first', 'pending', '2025-12-16 15:22:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `first_attempt` datetime DEFAULT current_timestamp(),
  `last_attempt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `action`, `identifier`, `attempts`, `first_attempt`, `last_attempt`) VALUES
('rl_011bebc280097da5', 'login', '::1', 1, '2025-12-18 16:13:51', '2025-12-18 16:13:51'),
('rl_049b628169111fb4', 'teacher_login', '::1', 1, '2025-12-18 16:47:57', '2025-12-18 16:47:57'),
('rl_105628e8aed347c2', 'login', '::1', 1, '2025-12-18 16:00:44', '2025-12-18 16:00:44'),
('rl_19903897046ed7fe', 'login', '::1', 6, '2025-12-18 00:12:06', '2025-12-18 15:27:45'),
('rl_31c52c039d53298a', 'login', '::1', 1, '2025-12-18 16:07:23', '2025-12-18 16:07:23'),
('rl_336c5d49d11eeec6', 'teacher_login', '::1', 1, '2025-12-18 13:46:58', '2025-12-18 13:46:58'),
('rl_4382a1b5c86c6ec5', 'teacher_login', '::1', 1, '2025-12-18 16:14:03', '2025-12-18 16:14:03'),
('rl_4bba53f1de3a16e1', 'teacher_login', '::1', 1, '2025-12-18 15:29:54', '2025-12-18 15:29:54'),
('rl_4d5a468633ee42eb', 'login', '::1', 1, '2025-12-18 15:46:31', '2025-12-18 15:46:31'),
('rl_4df0260e4cc15234', 'admin_login', '::1', 1, '2025-12-18 00:11:42', '2025-12-18 00:11:42'),
('rl_539b576f1b129685', 'teacher_login', '::1', 1, '2025-12-18 15:05:28', '2025-12-18 15:05:28'),
('rl_592a6883bf131c64', 'login', '::1', 3, '2025-12-18 15:04:09', '2025-12-18 15:27:45'),
('rl_6839520d720b6516', 'admin_login', '::1', 5, '2025-12-17 22:55:33', '2025-12-18 00:10:10'),
('rl_6c3002245ea152a3', 'admin_login', '::1', 1, '2025-12-18 14:19:52', '2025-12-18 14:19:52'),
('rl_6eadfa0ece7d45b5', 'login', '::1', 3, '2025-12-18 15:22:45', '2025-12-18 15:27:45'),
('rl_75035e4ca69df41c', 'login', '::1', 6, '2025-12-18 00:13:18', '2025-12-18 15:27:45'),
('rl_78ca70a0ebbaf086', 'admin_login', '::1', 1, '2025-12-18 00:15:08', '2025-12-18 00:15:08'),
('rl_7ad5ec95f4533243', 'admin_login', '::1', 1, '2025-12-18 15:05:02', '2025-12-18 15:05:02'),
('rl_7ad94473bd4931c2', 'login', '::1', 5, '2025-12-18 14:58:20', '2025-12-18 15:27:45'),
('rl_7e6409725eef9b3a', 'admin_login', '::1', 1, '2025-12-18 13:43:27', '2025-12-18 13:43:27'),
('rl_804e15858cc74012', 'admin_login', '::1', 2, '2025-12-17 23:42:57', '2025-12-18 00:10:10'),
('rl_81b6bb2be548ab92', 'teacher_login', '::1', 1, '2025-12-17 22:54:46', '2025-12-17 22:54:46'),
('rl_85dc4798e34ff688', 'teacher_login', '::1', 1, '2025-12-18 15:11:32', '2025-12-18 15:11:32'),
('rl_93139af953315b79', 'login', '::1', 5, '2025-12-18 14:17:20', '2025-12-18 15:27:45'),
('rl_a63f9e17dc4e1fa6', 'login', '::1', 6, '2025-12-17 22:53:01', '2025-12-18 15:27:45'),
('rl_bf34858de57d0ac2', 'login', '::1', 2, '2025-12-18 15:27:39', '2025-12-18 15:27:45'),
('rl_c22d2ccf208eb2df', 'admin_login', '::1', 4, '2025-12-17 23:41:40', '2025-12-18 00:10:10'),
('rl_c55b28926d46205d', 'teacher_login', '::1', 1, '2025-12-18 15:04:14', '2025-12-18 15:04:14'),
('rl_d3387deadfe15878', 'admin_login', '::1', 2, '2025-12-18 00:10:08', '2025-12-18 00:10:10'),
('rl_e7746eafdb37f82a', 'login', '::1', 1, '2025-12-18 16:28:26', '2025-12-18 16:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `id` varchar(100) NOT NULL,
  `userId` varchar(50) NOT NULL,
  `courseId` varchar(100) NOT NULL,
  `reason` text DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`id`, `userId`, `courseId`, `reason`, `createdAt`) VALUES
('rec_1', 'stu_emma', 'math201', 'Based on your excellent performance in Math Basics, try Algebra II!', '2025-12-10 15:22:01'),
('rec_2', 'stu_liam', 'cs201', 'You aced the CS quizzes! Web Development is the natural next step.', '2025-12-12 15:22:01'),
('rec_3', 'stu_sophia', 'sci201', 'Your science scores are impressive. Physics Fundamentals awaits!', '2025-12-14 15:22:01'),
('rec_4', 'stu_olivia', 'math201', 'Strong math fundamentals! Consider advancing to Algebra II.', '2025-12-13 15:22:01'),
('rec_5', 'stu_james', 'cs201', 'Javascript mastery - time for Web Development!', '2025-12-15 15:22:01'),
('rec_6', 'stu_ava', 'eng201', 'Grammar excellence unlocked! Ready for Public Speaking.', '2025-12-16 15:22:01'),
('rec_7', 'stu_isabella', 'geo201', 'World Geography fundamentals strong. Climate studies next!', '2025-12-15 15:22:01'),
('rec_8', 'stu_mason', 'art201', 'Art Foundations mastered! Digital Illustration awaits.', '2025-12-16 15:22:01'),
('rec_sk_1', 'stu_superkid', 'math201', 'Outstanding math performance! Algebra II is your next challenge.', '2025-12-08 15:22:01'),
('rec_sk_2', 'stu_superkid', 'cs201', 'Excellent coding skills! Try Web Development.', '2025-12-12 15:22:01'),
('rec_sk_3', 'stu_superkid', 'sci201', 'Strong science foundation - Physics Fundamentals awaits!', '2025-12-14 15:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `student` varchar(255) NOT NULL,
  `quiz` varchar(255) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `content` text NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `costPoints` int(11) NOT NULL,
  `tierRequired` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `name`, `category`, `costPoints`, `tierRequired`, `stock`, `createdAt`, `updatedAt`) VALUES
('rw_avatar_diamond', 'Diamond Avatar Frame', 'Cosmetic', 500, 3, 50, '2025-11-18 00:40:15', '2025-12-18 00:40:15'),
('rw_avatar_gold', 'Golden Avatar Frame', 'Cosmetic', 100, 1, NULL, '2025-11-18 00:40:15', '2025-12-18 00:40:15'),
('rw_badge_genius', 'Genius Badge', 'Badge', 300, 2, 100, '2025-11-20 00:40:15', '2025-12-18 00:40:15'),
('rw_badge_legend', 'Legend Badge', 'Badge', 1000, 3, 25, '2025-11-20 00:40:15', '2025-12-18 00:40:15'),
('rw_badge_scholar', 'Scholar Badge', 'Badge', 75, 1, NULL, '2025-11-20 00:40:15', '2025-12-18 00:40:15'),
('rw_cert_coding', 'Coding Completion Certificate', 'Certificate', 300, 2, NULL, '2025-11-24 00:40:15', '2025-12-18 00:40:15'),
('rw_cert_math', 'Math Completion Certificate', 'Certificate', 250, 2, NULL, '2025-11-24 00:40:15', '2025-12-18 00:40:15'),
('rw_cert_science', 'Science Completion Certificate', 'Certificate', 250, 2, NULL, '2025-11-24 00:40:15', '2025-12-18 00:40:15'),
('rw_extra_attempt', 'Extra Quiz Attempt', 'Power-up', 25, 1, NULL, '2025-11-30 00:40:15', '2025-12-18 00:40:15'),
('rw_hint_pack', 'Hint Pack (5 hints)', 'Power-up', 60, 1, NULL, '2025-11-30 00:40:15', '2025-12-18 00:40:15'),
('rw_merch_cap', 'EduMind Cap', 'Merchandise', 400, 2, 30, '2025-11-28 00:40:15', '2025-12-18 00:40:15'),
('rw_merch_shirt', 'EduMind T-Shirt', 'Merchandise', 750, 3, 15, '2025-11-28 00:40:15', '2025-12-18 00:40:15'),
('rw_theme_dark', 'Dark Theme Unlock', 'Theme', 50, 1, NULL, '2025-11-22 00:40:15', '2025-12-18 00:40:15'),
('rw_theme_ocean', 'Ocean Theme', 'Theme', 150, 2, NULL, '2025-11-22 00:40:15', '2025-12-18 00:40:15'),
('rw_theme_sunset', 'Sunset Theme', 'Theme', 200, 2, NULL, '2025-11-22 00:40:15', '2025-12-18 00:40:15'),
('rw_time_boost', 'Time Boost (+30 sec)', 'Power-up', 40, 1, NULL, '2025-11-30 00:40:15', '2025-12-18 00:40:15'),
('rw_voucher_10', '$10 Gift Voucher', 'Voucher', 900, 3, 10, '2025-11-26 00:40:15', '2025-12-18 00:40:15'),
('rw_voucher_5', '$5 Gift Voucher', 'Voucher', 500, 2, 20, '2025-11-26 00:40:15', '2025-12-18 00:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `reward_redemptions`
--

CREATE TABLE `reward_redemptions` (
  `id` varchar(100) NOT NULL,
  `rewardId` varchar(100) NOT NULL,
  `studentId` varchar(50) NOT NULL,
  `status` enum('pending','redeemed','rejected') NOT NULL DEFAULT 'redeemed',
  `requestedBalance` int(11) DEFAULT NULL,
  `shortBy` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `requestedAt` datetime DEFAULT current_timestamp(),
  `reviewedBy` varchar(50) DEFAULT NULL,
  `reviewedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reward_redemptions`
--

INSERT INTO `reward_redemptions` (`id`, `rewardId`, `studentId`, `status`, `requestedBalance`, `shortBy`, `note`, `requestedAt`, `reviewedBy`, `reviewedAt`) VALUES
('rr_1', 'rw_avatar_gold', 'stu_emma', 'redeemed', 150, 0, 'First reward!', '2025-12-13 15:22:01', NULL, NULL),
('rr_2', 'rw_theme_dark', 'stu_liam', 'redeemed', 100, 0, 'Love dark mode!', '2025-12-15 15:22:01', NULL, NULL),
('rr_3', 'rw_badge_scholar', 'stu_sophia', 'pending', 200, 0, 'Want this badge!', '2025-12-17 15:22:01', NULL, NULL),
('rr_4', 'rw_avatar_gold', 'stu_olivia', 'redeemed', 200, 0, 'Second gold frame', '2025-12-16 15:22:01', NULL, NULL),
('rr_5', 'rw_theme_ocean', 'stu_james', 'redeemed', 250, 0, 'Ocean theme is cool', '2025-12-17 15:22:01', NULL, NULL),
('rr_sk_1', 'rw_avatar_gold', 'stu_superkid', 'redeemed', 1925, 0, 'My first avatar upgrade!', '2025-12-06 15:22:01', NULL, NULL),
('rr_sk_2', 'rw_theme_dark', 'stu_superkid', 'redeemed', 1825, 0, 'Dark mode is easier on my eyes', '2025-12-08 15:22:01', NULL, NULL),
('rr_sk_3', 'rw_badge_scholar', 'stu_superkid', 'redeemed', 1775, 0, 'Scholar badge earned!', '2025-12-10 15:22:01', NULL, NULL),
('rr_sk_4', 'rw_theme_ocean', 'stu_superkid', 'redeemed', 1700, 0, 'Ocean vibes', '2025-12-13 15:22:01', NULL, NULL),
('rr_sk_5', 'rw_badge_genius', 'stu_superkid', 'redeemed', 1550, 0, 'Genius status unlocked!', '2025-12-15 15:22:01', NULL, NULL),
('rr_sk_6', 'rw_cert_math', 'stu_superkid', 'pending', 1250, 0, 'Want my math certificate!', '2025-12-17 15:22:01', NULL, NULL),
('rr_sk_7', 'rw_hint_pack', 'stu_superkid', 'redeemed', 1240, 0, 'Hints for tough quizzes', '2025-12-16 15:22:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` varchar(100) NOT NULL,
  `userId` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `courseId` varchar(100) NOT NULL,
  `quizId` varchar(100) NOT NULL,
  `score` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `durationSec` int(11) DEFAULT NULL,
  `attempt` int(11) DEFAULT 1,
  `type` varchar(50) DEFAULT 'quiz',
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `userId`, `username`, `courseId`, `quizId`, `score`, `total`, `durationSec`, `attempt`, `type`, `timestamp`) VALUES
('sc_ava_1', 'stu_ava', 'ava', 'eng101', 'eng101_quiz1', 4, 5, 65, 1, 'quiz', '2025-12-15 15:22:01'),
('sc_ava_2', 'stu_ava', 'ava', 'eng101', 'eng101_quiz2', 5, 5, 72, 1, 'quiz', '2025-12-17 15:22:01'),
('sc_ben_1', 'stu_ben', 'ben', 'art101', 'art101_quiz1', 5, 5, 70, 1, 'quiz', '2025-12-17 15:22:01'),
('sc_charlotte_1', 'stu_charlotte', 'charlotte', 'geo201', 'geo201_quiz1', 3, 5, 120, 1, 'quiz', '2025-12-16 15:22:01'),
('sc_emma_1', 'stu_emma', 'emma', 'math101', 'math101_quiz1', 4, 5, 55, 1, 'quiz', '2025-12-08 15:22:01'),
('sc_emma_2', 'stu_emma', 'emma', 'eng101', 'eng101_quiz1', 5, 5, 60, 1, 'quiz', '2025-12-10 15:22:01'),
('sc_ethan_1', 'stu_ethan', 'ethan', 'art201', 'art201_quiz1', 5, 5, 110, 1, 'quiz', '2025-12-16 15:22:01'),
('sc_isabella_1', 'stu_isabella', 'isabella', 'geo101', 'geo101_quiz1', 5, 5, 78, 1, 'quiz', '2025-12-14 15:22:01'),
('sc_james_1', 'stu_james', 'james', 'cs101', 'cs101_quiz3', 4, 5, 95, 1, 'quiz', '2025-12-14 15:22:01'),
('sc_liam_1', 'stu_liam', 'liam', 'cs101', 'cs101_quiz1', 5, 5, 45, 1, 'quiz', '2025-12-09 15:22:01'),
('sc_liam_2', 'stu_liam', 'liam', 'cs101', 'cs101_quiz2', 4, 5, 80, 1, 'quiz', '2025-12-11 15:22:01'),
('sc_mason_1', 'stu_mason', 'mason', 'art101', 'art101_quiz1', 4, 5, 82, 1, 'quiz', '2025-12-13 15:22:01'),
('sc_mia_1', 'stu_mia', 'mia', 'geo101', 'geo101_quiz1', 4, 5, 93, 1, 'quiz', '2025-12-15 15:22:01'),
('sc_noah_1', 'stu_noah', 'noah', 'hist101', 'hist101_quiz1', 3, 5, 70, 1, 'quiz', '2025-12-10 15:22:01'),
('sc_olivia_1', 'stu_olivia', 'olivia', 'math101', 'math101_quiz1', 5, 5, 48, 1, 'quiz', '2025-12-11 15:22:01'),
('sc_olivia_2', 'stu_olivia', 'olivia', 'math101', 'math101_quiz2', 4, 5, 75, 1, 'quiz', '2025-12-13 15:22:01'),
('sc_sk_chal', 'stu_superkid', 'superkid', 'math101', 'challenge_daily', 9, 10, 115, 1, 'quiz', '2025-12-12 15:22:01'),
('sc_sk_cs1', 'stu_superkid', 'superkid', 'cs101', 'cs101_quiz1', 5, 5, 60, 1, 'quiz', '2025-12-09 15:22:01'),
('sc_sk_cs2', 'stu_superkid', 'superkid', 'cs101', 'cs101_quiz2', 4, 5, 100, 1, 'quiz', '2025-12-13 15:22:01'),
('sc_sk_cs3', 'stu_superkid', 'superkid', 'cs101', 'cs101_quiz3', 5, 5, 95, 1, 'quiz', '2025-12-15 15:22:01'),
('sc_sk_eng1', 'stu_superkid', 'superkid', 'eng101', 'eng101_quiz1', 5, 5, 75, 1, 'quiz', '2025-12-14 15:22:01'),
('sc_sk_eng2', 'stu_superkid', 'superkid', 'eng101', 'eng101_quiz2', 4, 5, 82, 1, 'quiz', '2025-12-16 15:22:01'),
('sc_sk_geo1', 'stu_superkid', 'superkid', 'geo101', 'geo101_quiz1', 5, 5, 72, 1, 'quiz', '2025-12-16 15:22:01'),
('sc_sk_hist1', 'stu_superkid', 'superkid', 'hist101', 'hist101_quiz1', 4, 5, 80, 1, 'quiz', '2025-12-12 15:22:01'),
('sc_sk_hist2', 'stu_superkid', 'superkid', 'hist101', 'hist101_quiz2', 5, 5, 108, 1, 'quiz', '2025-12-17 15:22:01'),
('sc_sk_math1', 'stu_superkid', 'superkid', 'math101', 'math101_quiz1', 5, 5, 45, 1, 'quiz', '2025-12-04 15:22:01'),
('sc_sk_math2', 'stu_superkid', 'superkid', 'math101', 'math101_quiz2', 5, 5, 70, 1, 'quiz', '2025-12-06 15:22:01'),
('sc_sk_math3', 'stu_superkid', 'superkid', 'math101', 'math101_quiz3', 4, 5, 110, 1, 'quiz', '2025-12-08 15:22:01'),
('sc_sk_math4', 'stu_superkid', 'superkid', 'math201', 'math201_quiz1', 5, 5, 140, 1, 'quiz', '2025-12-10 15:22:01'),
('sc_sk_sci1', 'stu_superkid', 'superkid', 'sci101', 'sci101_quiz1', 5, 5, 52, 1, 'quiz', '2025-12-05 15:22:01'),
('sc_sk_sci2', 'stu_superkid', 'superkid', 'sci101', 'sci101_quiz2', 4, 5, 88, 1, 'quiz', '2025-12-07 15:22:01'),
('sc_sk_sci3', 'stu_superkid', 'superkid', 'sci101', 'sci101_quiz3', 4, 5, 115, 1, 'quiz', '2025-12-11 15:22:01'),
('sc_sophia_1', 'stu_sophia', 'sophia', 'sci101', 'sci101_quiz1', 5, 5, 50, 1, 'quiz', '2025-12-07 15:22:01'),
('sc_sophia_2', 'stu_sophia', 'sophia', 'sci101', 'sci101_quiz2', 4, 5, 85, 1, 'quiz', '2025-12-12 15:22:01'),
('sc_zara_1', 'stu_zara', 'zara', 'geo201', 'geo201_quiz1', 4, 5, 130, 1, 'quiz', '2025-12-17 15:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gradeLevel` varchar(50) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `lastLoginAt` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `username`, `password`, `fullName`, `email`, `mobile`, `address`, `gradeLevel`, `createdAt`, `lastLoginAt`, `deleted_at`, `google_id`) VALUES
('s_6939320ab58e5', 'kanderamdi_3881', '$2y$10$X69OwYKth/Aw2bhgzzOtOeU1ocIXSOIaH2E3lHECXBprwiKoKUHi.', 'Skander Hamdi', 'skanderhamdi21@gmail.com', NULL, NULL, NULL, '2025-12-10 09:40:42', '2025-12-18 15:11:44', NULL, '100814601614417543881'),
('stu_alice', 'alice', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Stone', 'alice@edumind.app', '+123456789', '123 Main St, City', 'Grade 8', '2025-11-08 16:18:47', '2025-11-27 16:18:47', NULL, NULL),
('stu_ava', 'ava', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ava Martinez', 'ava@edumind.app', '+2526272829', '88 Quest Ct', 'Grade 8', '2025-12-03 15:22:01', '2025-12-16 15:22:01', NULL, NULL),
('stu_ben', 'ben', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Benjamin Lopez', 'ben@edumind.app', '+5556575859', '81 River St', 'Grade 10', '2025-12-11 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('stu_bob', 'bob', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Carter', 'bob@edumind.app', '+987654321', '456 Oak Ave, Town', 'Grade 9', '2025-10-31 16:18:47', '2025-11-25 16:18:47', NULL, NULL),
('stu_charlotte', 'charlotte', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlotte Brooks', 'charlotte@edumind.app', '+5051525354', '9 Lakeview Rd', 'Grade 9', '2025-12-10 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('stu_emma', 'emma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma Watson', 'emma@edumind.app', '+1112223333', '22 Reading Rd', 'Grade 9', '2025-11-03 15:22:01', '2025-12-16 15:22:01', NULL, NULL),
('stu_ethan', 'ethan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ethan Garcia', 'ethan@edumind.app', '+4546474849', '11 Maple Cir', 'Grade 11', '2025-12-08 15:22:01', '2025-12-14 15:22:01', NULL, NULL),
('stu_isabella', 'isabella', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Isabella Rivera', 'isabella@edumind.app', '+3031323334', '12 Skyline Dr', 'Grade 10', '2025-11-30 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('stu_james', 'james', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James Wilson', 'james@edumind.app', '+2021222324', '77 Logic Lane', 'Grade 11', '2025-11-30 15:22:01', '2025-12-12 15:22:01', NULL, NULL),
('stu_liam', 'liam', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Liam Smith', 'liam@edumind.app', '+4445556666', '33 Tech Blvd', 'Grade 11', '2025-11-10 15:22:01', '2025-12-13 15:22:01', NULL, NULL),
('stu_mason', 'mason', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mason Patel', 'mason@edumind.app', '+3536373839', '90 Park Ave', 'Grade 9', '2025-12-02 15:22:01', '2025-12-15 15:22:01', NULL, NULL),
('stu_mia', 'mia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mia Nguyen', 'mia@edumind.app', '+4041424344', '70 Garden St', 'Grade 8', '2025-12-06 15:22:01', '2025-12-16 15:22:01', NULL, NULL),
('stu_noah', 'noah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Noah Johnson', 'noah@edumind.app', '+1011121314', '55 Bright St', 'Grade 10', '2025-11-23 15:22:01', '2025-12-15 15:22:01', NULL, NULL),
('stu_olivia', 'olivia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Olivia Brown', 'olivia@edumind.app', '+1516171819', '66 Wisdom Way', 'Grade 9', '2025-11-26 15:22:01', '2025-12-14 15:22:01', NULL, NULL),
('stu_sophia', 'sophia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophia Chen', 'sophia@edumind.app', '+7778889999', '44 Scholar Ave', 'Grade 8', '2025-11-18 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('stu_superkid', 'superkid', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Kid', 'superkid@edumind.app', '+1234567890', '100 Hero Lane, Metropolis', 'Grade 10', '2025-10-19 15:22:01', '2025-12-18 16:28:26', NULL, NULL),
('stu_zara', 'zara', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zara Ali', 'zara@edumind.app', '+6061626364', '3 Summit Pl', 'Grade 8', '2025-12-13 15:22:01', '2025-12-17 15:22:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` varchar(100) NOT NULL,
  `projectId` varchar(100) NOT NULL,
  `taskName` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `isComplete` tinyint(1) DEFAULT 0,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `dueDate` date DEFAULT NULL,
  `completedAt` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `nationalId` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `lastLoginAt` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `username`, `password`, `fullName`, `email`, `mobile`, `address`, `specialty`, `nationalId`, `createdAt`, `lastLoginAt`, `deleted_at`, `google_id`) VALUES
('t_6935814d38ccb', 'kanderamdi_3881', '$2y$10$TVJqiRgz/yfv5k3SzydIGu52V/.NcDKYP4hFGliFtTBCpsbCEiZGy', 'Skander Hamdi', 'skanderhamdi21@gmail.com', '44310079', 'Marsa, Sidi abdel aziz', 'Math', NULL, '2025-12-07 14:29:49', '2025-12-07 17:25:51', NULL, '100814601614417543881'),
('teach_jane', 'teacher_jane', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Miller', 'jane@edumind.app', '+555000111', '50 Math Lane, Eduville', 'Mathematics', 'NAT-001-JM', '2025-08-20 15:22:01', '2025-12-18 16:47:57', NULL, NULL),
('teach_kim', 'teacher_kim', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Kim Park', 'kim@edumind.app', '+555111222', '100 Faculty Dr', 'Computer Science', 'NAT-003-KP', '2025-10-29 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('teach_lee', 'teacher_lee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Michael Lee', 'lee@edumind.app', '+555999000', '75 Science Way, Eduville', 'Science', 'NAT-002-ML', '2025-09-09 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('teach_mark', 'teacher_mark', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark Thompson', 'mark@edumind.app', '+555555666', '300 Campus Blvd', 'History', 'NAT-005-MT', '2025-11-08 15:22:01', '2025-12-15 15:22:01', NULL, NULL),
('teach_nina', 'teacher_nina', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nina Torres', 'nina@edumind.app', '+555777888', '400 Learning Ln', 'Geography', 'NAT-006-NT', '2025-11-10 15:22:01', '2025-12-17 15:22:01', NULL, NULL),
('teach_omar', 'teacher_omar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Omar Khalil', 'omar@edumind.app', '+555888999', '500 Mentor Rd', 'Art & Design', 'NAT-007-OK', '2025-11-13 15:22:01', '2025-12-16 15:22:01', NULL, NULL),
('teach_sarah', 'teacher_sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Adams', 'sarah@edumind.app', '+555333444', '200 Academic Way', 'English Literature', 'NAT-004-SA', '2025-11-03 15:22:01', '2025-12-16 15:22:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` varchar(255) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `user_role` enum('student','teacher','admin') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_google_id` (`google_id`),
  ADD UNIQUE KEY `uniq_admin_google_id` (`google_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_createdBy` (`createdBy`);

--
-- Indexes for table `challenge_completions`
--
ALTER TABLE `challenge_completions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_challenge` (`challengeId`,`studentId`),
  ADD KEY `idx_student` (`studentId`),
  ADD KEY `idx_challenge` (`challengeId`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher` (`teacherId`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_deleted` (`deleted_at`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher` (`teacherId`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_deleted` (`deleted_at`),
  ADD KEY `idx_teacher_date` (`teacherId`,`date`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_timestamp` (`ts`);

--
-- Indexes for table `oauth_tokens`
--
ALTER TABLE `oauth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`user_role`),
  ADD KEY `idx_provider` (`provider`);

--
-- Indexes for table `points_ledger`
--
ALTER TABLE `points_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_created` (`studentId`,`createdAt`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_by` (`createdBy`),
  ADD KEY `idx_assigned_to` (`assignedTo`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`dueDate`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course` (`courseId`),
  ADD KEY `idx_teacher` (`createdBy`),
  ADD KEY `idx_deleted` (`deleted_at`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `quiz_reports`
--
ALTER TABLE `quiz_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reportedBy` (`reportedBy`),
  ADD KEY `idx_quiz` (`quizId`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`createdAt`),
  ADD KEY `idx_quiz_status` (`quizId`,`status`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_identifier` (`action`,`identifier`),
  ADD KEY `idx_last_attempt` (`last_attempt`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courseId` (`courseId`),
  ADD KEY `idx_user` (`userId`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_date` (`created_date`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_cost` (`costPoints`);

--
-- Indexes for table `reward_redemptions`
--
ALTER TABLE `reward_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_student` (`studentId`),
  ADD KEY `idx_reward` (`rewardId`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizId` (`quizId`),
  ADD KEY `idx_user` (`userId`),
  ADD KEY `idx_course` (`courseId`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_user_quiz` (`userId`,`quizId`,`timestamp`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_google_id` (`google_id`),
  ADD UNIQUE KEY `uniq_student_google_id` (`google_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`projectId`),
  ADD KEY `idx_complete` (`isComplete`),
  ADD KEY `idx_priority` (`priority`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_google_id` (`google_id`),
  ADD UNIQUE KEY `uniq_teacher_google_id` (`google_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_specialty` (`specialty`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD CONSTRAINT `admin_audit_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenge_completions`
--
ALTER TABLE `challenge_completions`
  ADD CONSTRAINT `challenge_completions_ibfk_1` FOREIGN KEY (`challengeId`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challenge_completions_ibfk_2` FOREIGN KEY (`studentId`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacherId`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`teacherId`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `points_ledger`
--
ALTER TABLE `points_ledger`
  ADD CONSTRAINT `points_ledger_ibfk_1` FOREIGN KEY (`studentId`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_reports`
--
ALTER TABLE `quiz_reports`
  ADD CONSTRAINT `quiz_reports_ibfk_1` FOREIGN KEY (`reportedBy`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reward_redemptions`
--
ALTER TABLE `reward_redemptions`
  ADD CONSTRAINT `reward_redemptions_ibfk_1` FOREIGN KEY (`rewardId`) REFERENCES `rewards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reward_redemptions_ibfk_2` FOREIGN KEY (`studentId`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`quizId`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

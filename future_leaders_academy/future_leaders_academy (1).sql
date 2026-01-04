-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 09:34 PM
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
-- Database: `future_leaders_academy`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `status` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$JOF7rfe3.vkUki/3RG8owOsT7J18iyzNE0pEujsIAYY.KL/FhH6W.', 'super_admin', 1, '2026-01-04 01:45:58', '2025-12-31 19:54:07'),
(2, 'moderator', '$2y$10$JOF7rfe3.vkUki/3RG8owOsT7J18iyzNE0pEujsIAYY.KL/FhH6W.', 'moderator', 1, NULL, '2025-12-31 19:54:07');

-- --------------------------------------------------------

--
-- Table structure for table `allowed_file_types`
--

CREATE TABLE `allowed_file_types` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `max_size` int(11) DEFAULT 5242880,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allowed_file_types`
--

INSERT INTO `allowed_file_types` (`id`, `type`, `extension`, `mime_type`, `max_size`, `created_at`) VALUES
(1, 'image', 'jpg', 'image/jpeg', 10485760, '2025-12-29 20:02:40'),
(2, 'image', 'jpeg', 'image/jpeg', 10485760, '2025-12-29 20:02:40'),
(3, 'image', 'png', 'image/png', 10485760, '2025-12-29 20:02:40'),
(4, 'image', 'gif', 'image/gif', 10485760, '2025-12-29 20:02:40'),
(5, 'image', 'webp', 'image/webp', 10485760, '2025-12-29 20:02:40'),
(6, 'image', 'bmp', 'image/bmp', 10485760, '2025-12-29 20:02:40'),
(7, 'image', 'svg', 'image/svg+xml', 10485760, '2025-12-29 20:02:40'),
(8, 'image', 'ico', 'image/x-icon', 10485760, '2025-12-29 20:02:40'),
(9, 'video', 'mp4', 'video/mp4', 524288000, '2025-12-29 20:02:40'),
(10, 'video', 'avi', 'video/x-msvideo', 524288000, '2025-12-29 20:02:40'),
(11, 'video', 'mov', 'video/quicktime', 524288000, '2025-12-29 20:02:40'),
(12, 'video', 'wmv', 'video/x-ms-wmv', 524288000, '2025-12-29 20:02:40'),
(13, 'video', 'flv', 'video/x-flv', 524288000, '2025-12-29 20:02:40'),
(14, 'video', 'mkv', 'video/x-matroska', 524288000, '2025-12-29 20:02:40'),
(15, 'video', 'webm', 'video/webm', 524288000, '2025-12-29 20:02:40'),
(16, 'pdf', 'pdf', 'application/pdf', 104857600, '2025-12-29 20:02:40'),
(17, 'word', 'doc', 'application/msword', 52428800, '2025-12-29 20:02:40'),
(18, 'word', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 52428800, '2025-12-29 20:02:40'),
(19, 'excel', 'xls', 'application/vnd.ms-excel', 52428800, '2025-12-29 20:02:40'),
(20, 'excel', 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 52428800, '2025-12-29 20:02:40'),
(21, 'powerpoint', 'ppt', 'application/vnd.ms-powerpoint', 52428800, '2025-12-29 20:02:40'),
(22, 'powerpoint', 'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 52428800, '2025-12-29 20:02:40'),
(23, 'text', 'txt', 'text/plain', 1048576, '2025-12-29 20:02:40'),
(24, 'text', 'rtf', 'application/rtf', 10485760, '2025-12-29 20:02:40'),
(25, 'archive', 'zip', 'application/zip', 209715200, '2025-12-29 20:02:40'),
(26, 'archive', 'rar', 'application/x-rar-compressed', 209715200, '2025-12-29 20:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `created_at`) VALUES
(1, 'تصميم', 'design', 'تصميم', 'fas fa-paint-brush', '2025-12-29 20:02:40'),
(2, 'برمجة', 'programming', 'أعمال البرمجة والتطوير', 'fas fa-code', '2025-12-29 20:02:40'),
(3, 'فيديو', 'video', 'مقاطع الفيديو والمرئيات', 'fas fa-video', '2025-12-29 20:02:40'),
(4, 'مستندات', 'documents', 'المستندات والعروض', 'fas fa-file', '2025-12-29 20:02:40'),
(5, 'بحوث جامعية و رسائل', 'research', 'بحوث جامعية و رسائل', 'fas fa-book', '2025-12-29 20:02:40'),
(6, 'عروض تقديمية', 'presentations', 'العروض التقديمية', 'fas fa-chalkboard-teacher', '2025-12-29 20:02:40'),
(7, 'حل منصات تعليمية', 'solves', 'حل منصات تعليمية', NULL, '2025-12-30 21:46:05'),
(8, 'ى', 'ى', 'ى', 'fas fa-language', '2026-01-02 23:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL,
  `total_files` int(11) DEFAULT 0,
  `total_size` bigint(20) DEFAULT 0,
  `images_count` int(11) DEFAULT 0,
  `videos_count` int(11) DEFAULT 0,
  `documents_count` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`id`, `total_files`, `total_size`, `images_count`, `videos_count`, `documents_count`, `updated_at`) VALUES
(1, 18, 16115345, 15, 0, 3, '2026-01-02 22:30:06'),
(2, 15, 4555236, 14, 0, 1, '2026-01-02 22:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin123', 'المسؤول العام', 'admin', '2025-12-29 22:53:11', '2026-01-03 21:42:29'),
(2, 'mona', 'mona123', 'المسؤول العام', 'admin', '2025-12-29 22:53:11', '2026-01-03 21:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_numbers`
--

CREATE TABLE `whatsapp_numbers` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `country` enum('saudi','uae') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `whatsapp_numbers`
--

INSERT INTO `whatsapp_numbers` (`id`, `phone_number`, `country`, `created_at`, `updated_at`) VALUES
(1, '+966582529631', 'saudi', '2026-01-03 22:34:56', '2026-01-03 22:34:56'),
(2, '+971553353672', 'uae', '2026-01-03 22:34:56', '2026-01-03 22:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `works`
--

CREATE TABLE `works` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT 'image',
  `media_path` varchar(500) NOT NULL,
  `media_url` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_extension` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `date` date NOT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `downloads_count` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `thumbnail_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `works`
--

INSERT INTO `works` (`id`, `title`, `category`, `country`, `type`, `media_path`, `media_url`, `file_name`, `file_size`, `file_extension`, `description`, `features`, `date`, `featured`, `tags`, `downloads_count`, `views_count`, `created_at`, `updated_at`, `thumbnail_url`) VALUES
(11, 'شهادة تقدير', 'ترجمة أكاديمية', 'uae', 'images', 'c/uploads/images/1767049842_69530a7206122.png', '/c/uploads/images/1767049842_69530a7206122.png', 'IMG-20251219-WA0060(1).jpg', 64121, 'jpg', 'ة', '[\"ة\"]', '2025-12-12', 0, '[\"شهادة تقدير\"]', 0, 7, '2025-12-29 21:40:30', '2026-01-02 20:02:48', NULL),
(12, ',m', 'برمجة', 'uae', 'images', 'c/uploads/images/1767049897_69530aa92da66.jpg', '/c/uploads/images/1767049897_69530aa92da66.jpg', 'صورة1.jpg', 23236, 'jpg', 'm,', '[\"ة\"]', '2026-01-01', 0, '[\"kk\"]', 0, 2, '2025-12-29 23:11:37', '2026-01-02 20:02:56', NULL),
(14, 'شهادة', 'بحوث جامعية و رسائل', 'saudi', 'PDF', 'c/uploads/documents/1767129775_695442af8ad04.pdf', '/c/uploads/documents/1767129775_695442af8ad04.pdf', 'شهد عنقاد.pdf', 610072, 'pdf', 'تت', '[\"cm\",\"\"]', '2025-12-30', 1, '[\"شهادة تقدير\"]', 0, 0, '2025-12-30 21:22:55', '2025-12-30 22:08:21', NULL),
(15, 'حل اختبارات', 'حل منصات تعليمية', 'saudi', 'images', 'c/uploads/images/1767131299_695448a3345c3.jpg', '/c/uploads/images/1767131299_695448a3345c3.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'حل اختبارات', '[\"حل اختبارات\",\"فل مارك\"]', '2025-12-30', 0, '[\"\"]', 0, 0, '2025-12-30 21:48:19', '2025-12-31 19:05:14', NULL),
(16, 'حل اختبارات', 'حل منصات تعليمية', 'saudi', 'images', 'c/uploads/images/1767131653_69544a05549b0.jpg', '/c/uploads/images/1767131653_69544a05549b0.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'حل اختبارات', '[\"حل اختبارات\",\"فل مارك\"]', '2025-12-30', 0, '[\"\"]', 0, 0, '2025-12-30 21:54:13', '2025-12-31 19:05:28', NULL),
(17, 'حل اختبارات', 'حل منصات تعليمية', 'uae', 'images', 'c/uploads/images/1767132090_69544bba9283d.jpg', '/c/uploads/images/1767132090_69544bba9283d.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'حل اختبارات', '[\"حل اختبارات\",\"فل مارك\"]', '2025-12-30', 0, '[\"\"]', 0, 5, '2025-12-30 22:01:30', '2025-12-31 19:05:09', NULL),
(19, 'ى', 'بحوث جامعية و رسائل', 'saudi', 'images', 'c/uploads/images/1767134358_6954549613c01.jpg', '/c/uploads/images/1767134358_6954549613c01.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'ةى', '[\"ىة\"]', '2025-12-30', 1, '[\"\"]', 0, 11, '2025-12-30 22:39:18', '2025-12-31 19:04:39', NULL),
(21, 'س', 'بحوث جامعية و رسائل', 'saudi', 'images', 'c/uploads/images/1767138308_69546404ec390.jpg', '/c/uploads/images/1767138308_69546404ec390.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'س', '[\"س\"]', '2025-12-31', 1, '[\"س\"]', 0, 3, '2025-12-30 23:45:09', '2025-12-31 20:27:48', NULL),
(23, 'ت', 'عروض تقديمية', 'uae', 'صورة', 'c/uploads/images/1767304947_6956eef335893.jpg', '/c/uploads/images/1767304947_6956eef335893.jpg', 'IMG-20251219-WA0060.jpg', 64121, 'jpg', 'ة', '[\"m\"]', '2026-01-01', 1, '[\"وة\"]', 0, 1, '2026-01-01 22:02:27', '2026-01-01 22:05:48', NULL),
(24, 'حل أختبارات', 'حل منصات تعليمية', 'uae', 'صورة', 'c/uploads/images/1767305043_6956ef5307762.jpg', 'c/uploads/images/1767305043_6956ef5307762.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'حل أختبارات طلاب المنازل المنهج الاماترتي', '[\"حل أختبارات\"]', '2026-01-01', 1, '[\"حل أختبارات \"]', 0, 3, '2026-01-01 22:04:03', '2026-01-02 20:12:17', NULL),
(25, 'ينت', 'عروض تقديمية', 'saudi', 'صورة', 'c/uploads/images/1767383118_6958204e41d1c.jpg', 'c/uploads/images/1767383118_6958204e41d1c.jpg', 'IMG-20251219-WA0060.jpg', 64121, 'jpg', 'يهي', '[]', '2026-01-02', 0, '[\"\"]', 0, 5, '2026-01-02 19:45:18', '2026-01-02 20:11:57', NULL),
(26, 'jk', 'حل منصات تعليمية', 'saudi', 'صورة', 'c/uploads/images/1767385391_6958292fa66bb.jpg', '/c/uploads/images/1767385391_6958292fa66bb.jpg', 'IMG-20251219-WA0060.jpg', 64121, 'jpg', 'jk', '[]', '2026-01-02', 0, '[\"\"]', 0, 0, '2026-01-02 20:23:11', '2026-01-02 20:23:11', NULL),
(27, 'تن', 'برمجة', '', 'صورة', 'c/uploads/images/1767388306_6958349268121.jpg', '/c/uploads/images/1767388306_6958349268121.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'ةى', '[\"ةو\"]', '2026-01-02', 0, '[\"وة\"]', 0, 0, '2026-01-02 21:11:46', '2026-01-02 21:11:46', NULL),
(28, 'قثفثف4', 'تصميم', 'saudi', 'صورة', 'c/uploads/images/1767391595_6958416ba37d5.jpg', '/c/uploads/images/1767391595_6958416ba37d5.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'ق32', '[\"سخيعخصعثخثعخه\"]', '2026-01-02', 1, '[\"خثصعخ\"]', 0, 0, '2026-01-02 22:06:35', '2026-01-02 22:06:35', NULL),
(29, 'قثفثف4', 'تصميم', 'saudi', 'صورة', 'c/uploads/images/1767391963_695842db56b12.jpg', '/c/uploads/images/1767391963_695842db56b12.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'ق32', '[\"سخيعخصعثخثعخه\"]', '2026-01-02', 1, '[\"خثصعخ\"]', 0, 0, '2026-01-02 22:12:43', '2026-01-02 22:12:43', NULL),
(30, 'قثفثف4', 'تصميم', 'saudi', 'صورة', 'c/uploads/images/1767392469_695844d5331f7.jpg', '/c/uploads/images/1767392469_695844d5331f7.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'ق32', '[\"سخيعخصعثخثعخه\"]', '2026-01-02', 1, '[\"خثصعخ\"]', 0, 0, '2026-01-02 22:21:09', '2026-01-02 22:21:09', NULL),
(31, 'قثفثف4', 'تصميم', 'saudi', 'صورة', 'c/uploads/images/1767392563_69584533dd06e.jpg', '/c/uploads/images/1767392563_69584533dd06e.jpg', 'IMG-20251219-WA0061.jpg', 60091, 'jpg', 'ق32', '[\"سخيعخصعثخثعخه\"]', '2026-01-02', 1, '[\"خثصعخ\"]', 0, 0, '2026-01-02 22:22:43', '2026-01-02 22:22:43', NULL),
(32, 'ة', 'برمجة', 'saudi', 'صورة', 'c/uploads/images/1767393006_695846ee1b926.jpg', '/c/uploads/images/1767393006_695846ee1b926.jpg', 'IMG-20251219-WA0060(1).jpg', 64121, 'jpg', 'ىة', '[]', '2026-01-02', 0, '[\"\"]', 0, 0, '2026-01-02 22:30:06', '2026-01-02 22:30:06', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `allowed_file_types`
--
ALTER TABLE `allowed_file_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `whatsapp_numbers`
--
ALTER TABLE `whatsapp_numbers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `works`
--
ALTER TABLE `works`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `allowed_file_types`
--
ALTER TABLE `allowed_file_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `whatsapp_numbers`
--
ALTER TABLE `whatsapp_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `works`
--
ALTER TABLE `works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

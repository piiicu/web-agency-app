-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 10:53 AM
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
-- Database: `web_agency_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_attachments`
--

CREATE TABLE `chat_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_attachments`
--

INSERT INTO `chat_attachments` (`id`, `message_id`, `uploaded_by`, `original_name`, `stored_name`, `mime_type`, `size_bytes`, `created_at`) VALUES
(1, 5, 1, 'a2308fe6-a792-488a-9d98-4093ef511397.png', 'ba72165806a59a54be41073c53303e7d_a2308fe6-a792-488a-9d98-4093ef511397.png', 'image/png', 4775737, '2026-01-25 21:10:47'),
(2, 6, 4, 'baia mare.jpg', 'a5af468bfd85a2dca3cc7f09526250a5_baia_mare.jpg', 'image/jpeg', 485032, '2026-01-25 21:11:06');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `type` enum('general','dm','group') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `type`, `title`, `created_by`, `owner_id`, `created_at`, `deleted_at`, `deleted_by`) VALUES
(1, 'general', 'General', 1, 1, '2026-01-31 18:33:47', NULL, NULL),
(2, 'group', 'Grup nou', 4, 4, '2026-01-31 19:52:54', '2026-02-01 22:01:00', 4),
(3, 'group', 'Grup nou', 1, 1, '2026-01-31 20:05:59', '2026-01-31 21:29:16', 1),
(4, 'group', 'Grup nou', 1, 1, '2026-01-31 20:06:10', '2026-01-31 21:29:21', 1),
(5, 'group', 'grup test', 1, 1, '2026-01-31 20:06:22', '2026-02-01 21:59:22', 1),
(6, 'group', 'grup Marius + admin2', 1, 1, '2026-02-01 21:59:43', '2026-02-01 22:01:59', 1),
(7, 'group', 'test2', 1, 1, '2026-02-01 22:02:07', '2026-02-01 22:02:33', 1),
(8, 'group', 'Grup nou', 1, 4, '2026-02-01 22:02:36', NULL, NULL),
(9, 'group', 'Grup nou', 4, 4, '2026-02-01 22:36:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `conversation_attachments`
--

CREATE TABLE `conversation_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_attachments`
--

INSERT INTO `conversation_attachments` (`id`, `message_id`, `uploaded_by`, `original_name`, `stored_name`, `mime_type`, `size_bytes`, `created_at`) VALUES
(1, 5, 1, 'a2308fe6-a792-488a-9d98-4093ef511397.png', 'ba72165806a59a54be41073c53303e7d_a2308fe6-a792-488a-9d98-4093ef511397.png', 'image/png', 4775737, '2026-01-25 21:10:47'),
(2, 6, 4, 'baia mare.jpg', 'a5af468bfd85a2dca3cc7f09526250a5_baia_mare.jpg', 'image/jpeg', 485032, '2026-01-25 21:11:06');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_messages`
--

CREATE TABLE `conversation_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `legacy_message_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_messages`
--

INSERT INTO `conversation_messages` (`id`, `conversation_id`, `sender_id`, `body`, `created_at`, `legacy_message_id`) VALUES
(1, 1, 1, 'mesaj test catre admin 2', '2026-01-25 20:57:09', 1),
(2, 1, 1, 'sacvdb', '2026-01-25 20:57:13', 2),
(3, 1, 1, 'fdghsrhgdsfcxcdvfbf', '2026-01-25 20:57:22', 3),
(4, 1, 4, 'salut', '2026-01-25 20:58:07', 4),
(5, 1, 1, '', '2026-01-25 21:10:47', 5),
(6, 1, 4, '', '2026-01-25 21:11:06', 6),
(7, 1, 1, 'testare', '2026-01-25 22:11:51', 7),
(8, 1, 1, 'sAMKPNID\"fvahakjcndshcklkasjd[cvyiudshcdsc[oids\'lkcndsklchdshf[yrildshfvioerdshivehrighvokldfsnvoehsduvhueorsdhv[iehfdsoivherohsvhdfvounsdkciodfxdhvnodsjvio jsdf erdscifniondufmronifyvuidgvnesifreionyio preygh[\r\nioichro[inhf[iregyergc]\\\r\n\r\npfo]crne]ofuerf\\\r\n\r\n[irsifmci', '2026-01-29 16:50:48', 8),
(9, 1, 1, 'te salut', '2026-01-31 12:34:11', 9),
(10, 1, 1, 'sagcj;ksdh;v', '2026-01-31 14:38:08', 10),
(11, 1, 1, 'dsdsvfbgfsdbfgdvcdsaxx cvxcz', '2026-01-31 14:44:35', 11),
(12, 1, 1, 'kldjfjkrehwfhkrhskheirquucrfrqesfcesdcdsveedcwknilflhcjsn', '2026-01-31 14:49:00', 12),
(13, 1, 1, 'sa vedem daca se citeste mesajul', '2026-01-31 15:40:22', 13),
(14, 1, 1, 'fdghhdfgbcv', '2026-01-31 15:59:32', 14),
(15, 1, 1, 'merge sa trimit?', '2026-01-31 16:18:44', 15),
(16, 1, 4, 'se pare ca da', '2026-01-31 16:19:41', 16),
(17, 1, 1, 'stai sa vad', '2026-01-31 16:19:59', 17),
(18, 1, 1, 'mesaj nou', '2026-01-31 17:45:25', 18),
(19, 1, 4, 'da am vazut mesajul', '2026-01-31 17:46:19', 19),
(32, 1, 1, 'test', '2026-01-31 20:20:48', NULL),
(33, 6, 1, 'test', '2026-02-01 21:59:52', NULL),
(34, 8, 1, 'test', '2026-02-01 22:02:42', NULL),
(35, 8, 4, 'notificare', '2026-02-01 22:03:18', NULL),
(36, 8, 4, 'motificare 2', '2026-02-01 22:03:30', NULL),
(37, 1, 4, 'ajbsgdslh;jfd', '2026-02-01 22:06:08', NULL),
(38, 1, 4, 'scdcvbnm', '2026-02-01 22:27:25', NULL),
(39, 9, 4, 'mesaj test', '2026-02-01 22:36:14', NULL),
(40, 1, 1, 'scdsvfbg', '2026-02-01 22:37:00', NULL),
(41, 1, 1, 'test notificare', '2026-02-02 09:13:35', NULL),
(42, 1, 4, 'mesaj', '2026-02-02 09:36:10', NULL),
(43, 1, 4, 'sadgf', '2026-02-02 10:19:14', NULL),
(44, 1, 4, 'test', '2026-02-02 10:23:27', NULL),
(45, 1, 4, 'mjskgdflhș', '2026-02-02 10:38:33', NULL),
(46, 1, 4, 'multumesc am primit taskul', '2026-02-02 10:55:28', NULL),
(47, 1, 4, 'de ce nu a schimbat nr?', '2026-02-02 10:55:57', NULL),
(48, 1, 1, 'da merge si pe mobil', '2026-02-02 10:56:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_delivered_at` datetime DEFAULT NULL,
  `last_read_at` datetime DEFAULT NULL,
  `hidden_at` datetime DEFAULT NULL,
  `left_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_participants`
--

INSERT INTO `conversation_participants` (`conversation_id`, `user_id`, `joined_at`, `last_delivered_at`, `last_read_at`, `hidden_at`, `left_at`) VALUES
(1, 1, '2026-01-31 18:33:47', '2026-02-02 11:46:16', '2026-02-02 11:46:12', NULL, NULL),
(1, 4, '2026-01-31 18:33:47', '2026-02-02 10:58:07', '2026-02-02 10:58:03', NULL, NULL),
(2, 1, '2026-01-31 19:52:54', '2026-01-31 21:28:30', '2026-01-31 21:28:15', '2026-02-01 22:01:00', NULL),
(2, 4, '2026-01-31 19:52:54', '2026-02-01 22:00:59', '2026-02-01 22:00:57', '2026-02-01 22:01:00', NULL),
(3, 1, '2026-01-31 20:05:59', '2026-01-31 21:29:14', '2026-01-31 21:29:16', NULL, NULL),
(3, 4, '2026-01-31 20:05:59', NULL, '2026-02-01 22:12:12', NULL, NULL),
(4, 1, '2026-01-31 20:06:10', '2026-01-31 21:29:18', '2026-01-31 21:29:21', NULL, NULL),
(4, 4, '2026-01-31 20:06:10', NULL, '2026-02-01 22:12:12', NULL, NULL),
(5, 1, '2026-01-31 20:06:22', '2026-02-01 21:59:21', '2026-02-01 21:59:18', '2026-02-01 21:59:22', NULL),
(5, 4, '2026-01-31 20:06:22', NULL, '2026-02-01 22:12:12', '2026-02-01 21:59:22', NULL),
(6, 1, '2026-02-01 21:59:43', '2026-02-01 22:01:57', '2026-02-01 22:01:55', '2026-02-01 22:01:59', NULL),
(6, 4, '2026-02-01 21:59:43', '2026-02-01 22:01:40', '2026-02-01 22:01:32', '2026-02-01 22:01:59', '2026-02-01 22:01:41'),
(7, 1, '2026-02-01 22:02:07', '2026-02-01 22:02:31', '2026-02-01 22:02:23', '2026-02-01 22:02:33', NULL),
(7, 4, '2026-02-01 22:02:07', '2026-02-01 22:02:18', '2026-02-01 22:02:16', '2026-02-01 22:02:33', NULL),
(8, 1, '2026-02-01 22:02:36', '2026-02-01 22:05:00', '2026-02-01 22:04:56', '2026-02-01 22:05:01', '2026-02-01 22:05:01'),
(8, 4, '2026-02-01 22:02:36', '2026-02-01 22:05:10', '2026-02-01 22:05:04', '2026-02-01 22:05:10', NULL),
(9, 1, '2026-02-01 22:36:05', '2026-02-02 11:53:10', '2026-02-02 11:46:18', NULL, NULL),
(9, 4, '2026-02-01 22:36:05', '2026-02-01 22:36:57', '2026-02-01 22:36:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`, `delivered_at`, `read_at`) VALUES
(1, 1, 'mesaj test catre admin 2', '2026-01-25 18:57:09', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(2, 1, 'sacvdb', '2026-01-25 18:57:13', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(3, 1, 'fdghsrhgdsfcxcdvfbf', '2026-01-25 18:57:22', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(4, 4, 'salut', '2026-01-25 18:58:07', '2026-01-31 15:40:05', '2026-01-31 15:40:05'),
(5, 1, '', '2026-01-25 19:10:47', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(6, 4, '', '2026-01-25 19:11:06', '2026-01-31 15:40:05', '2026-01-31 15:40:05'),
(7, 1, 'testare', '2026-01-25 20:11:51', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(8, 1, 'sAMKPNID\"fvahakjcndshcklkasjd[cvyiudshcdsc[oids\'lkcndsklchdshf[yrildshfvioerdshivehrighvokldfsnvoehsduvhueorsdhv[iehfdsoivherohsvhdfvounsdkciodfxdhvnodsjvio jsdf erdscifniondufmronifyvuidgvnesifreionyio preygh[\r\nioichro[inhf[iregyergc]\\\r\n\r\npfo]crne]ofuerf\\\r\n\r\n[irsifmci', '2026-01-29 14:50:48', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(9, 1, 'te salut', '2026-01-31 10:34:11', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(10, 1, 'sagcj;ksdh;v', '2026-01-31 12:38:08', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(11, 1, 'dsdsvfbgfsdbfgdvcdsaxx cvxcz', '2026-01-31 12:44:35', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(12, 1, 'kldjfjkrehwfhkrhskheirquucrfrqesfcesdcdsveedcwknilflhcjsn', '2026-01-31 12:49:00', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(13, 1, 'sa vedem daca se citeste mesajul', '2026-01-31 13:40:22', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(14, 1, 'fdghhdfgbcv', '2026-01-31 13:59:32', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(15, 1, 'merge sa trimit?', '2026-01-31 14:18:44', '2026-01-31 16:19:15', '2026-01-31 16:19:15'),
(16, 4, 'se pare ca da', '2026-01-31 14:19:41', '2026-01-31 16:19:41', '2026-01-31 16:19:41'),
(17, 1, 'stai sa vad', '2026-01-31 14:19:59', '2026-01-31 16:20:00', '2026-01-31 16:20:00'),
(18, 1, 'mesaj nou', '2026-01-31 15:45:25', '2026-01-31 17:46:01', '2026-01-31 17:46:01'),
(19, 4, 'da am vazut mesajul', '2026-01-31 15:46:19', '2026-01-31 17:46:40', '2026-01-31 17:46:40');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 4, 'f4acfd43cdbceddcb7d8e04d6eddd43b4bf91f88afb8dd58c81856ba3ee3ec7d', '2026-01-26 19:36:04', '2026-01-25 20:37:24', '2026-01-25 18:36:04'),
(2, 4, 'c3fc499d3fed4a7dc94961047fffe001f1daafd621e0175e915e5eb14a7f0dec', '2026-01-27 09:15:10', NULL, '2026-01-26 08:15:10'),
(5, 5, '1b2c233baba5ae679d55206cf0c27a3db42a0d950e7e549cca66c6ac55dbde9d', '2026-01-27 10:53:33', '2026-01-26 11:53:47', '2026-01-26 09:53:33'),
(6, 4, '09c87716bfc7ca63f203eb67977f8f7f0dfe86ea1ceb14f9db94788ca4fa903d', '2026-01-26 11:56:52', NULL, '2026-01-26 10:26:52'),
(7, 4, '7a5a5dd9e72d63e4f2108ddd2a14c607f5a0b9e23aed707664efbc85627db074', '2026-01-26 11:57:56', NULL, '2026-01-26 10:27:56'),
(8, 4, '04ba4fa2e905832e84f33dc694c7f9dd5148b053fde3892f89c847201b6b89bd', '2026-01-26 12:06:48', NULL, '2026-01-26 10:36:48'),
(9, 4, '47d82099b62b9cbadddf4d6872339c38617320986e04024cb6a5845d6be43fa9', '2026-01-26 12:07:16', NULL, '2026-01-26 10:37:16'),
(10, 4, '7ac336ab50d5762ec205ad4727210a83d22a449744d61260b4cfe6646b6236c8', '2026-01-26 12:20:55', NULL, '2026-01-26 10:50:55'),
(11, 4, 'f3168fc084888e5d2457177feac83f15a9214055e5c28cd50abab1cd7f15e3a9', '2026-01-26 12:21:05', NULL, '2026-01-26 10:51:05'),
(12, 4, 'ac0a74cf2ca3d547d3a19cbbfd299cfca45c1cd0d27fa084af4e246b43756cb9', '2026-01-26 12:21:33', NULL, '2026-01-26 10:51:33'),
(13, 4, '7f5ad4916f2dc8a150dcdb1e06ac39a3ef316d7390c636d3c188d9a651afd646', '2026-01-26 12:29:47', '2026-01-26 12:59:59', '2026-01-26 10:59:47');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('pending','done') DEFAULT 'pending',
  `priority` tinyint(4) NOT NULL DEFAULT 3,
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `status`, `priority`, `is_favorite`, `created_at`) VALUES
(1, 'task-uri interne nr 1', 'pending', 3, 0, '2026-01-08 12:15:45'),
(2, 'task-uri interne nr 2', 'done', 2, 1, '2026-01-08 12:17:22'),
(3, 'task fav 1', 'done', 3, 0, '2026-01-30 19:57:12'),
(4, 'task fav 2', 'pending', 3, 0, '2026-01-30 19:57:25'),
(5, 'task fav 3', 'pending', 3, 1, '2026-01-30 19:57:28'),
(6, 'task urgente', 'pending', 1, 0, '2026-01-30 19:57:41'),
(7, 'task urg 2', 'done', 1, 0, '2026-01-30 19:57:50'),
(8, 'motificare', 'pending', 3, 0, '2026-02-01 20:06:26'),
(9, 'task', 'pending', 3, 0, '2026-02-01 20:26:58'),
(10, 'task nou', 'pending', 3, 0, '2026-02-01 21:01:47'),
(11, 'task notificare', 'pending', 3, 0, '2026-02-02 07:35:10'),
(12, 'task', 'pending', 3, 0, '2026-02-02 08:24:13'),
(13, 'task verificare notificare', 'pending', 3, 0, '2026-02-02 08:55:09');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `priority` tinyint(4) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `client_id`, `subject`, `status`, `sort_order`, `deleted_at`, `created_at`, `updated_at`, `priority`) VALUES
(1, 2, 'tichet nr 1', 'resolved', 2, NULL, '2026-01-08 15:04:05', '2026-01-30 12:24:16', 3),
(2, 2, 'Test poze noi', 'open', 1, '2026-01-29 17:59:18', '2026-01-11 21:39:04', '2026-01-29 17:59:18', 3);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `message_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_attachments`
--

INSERT INTO `ticket_attachments` (`id`, `ticket_id`, `message_id`, `uploaded_by`, `original_name`, `stored_name`, `mime_type`, `size_bytes`, `created_at`) VALUES
(1, 1, NULL, 2, 'POLITA DE INSOLVENTA.pdf', '6ad75e43db2ceeaaa8814bad077a8345_POLITA_DE_INSOLVENTA.pdf', 'application/pdf', 868620, '2026-01-08 15:04:05'),
(2, 1, NULL, 2, 'sangerari gingivale.jpg', '1d0e19b2211053748ff7ffdac0e915d0_sangerari_gingivale.jpg', 'image/jpeg', 26182, '2026-01-08 15:04:05'),
(3, 2, 3, 2, '45_degrees_v2.jpg', '351cf7f5af792328163d947a88be3202_45_degrees_v2.jpg', 'image/jpeg', 15194, '2026-01-11 21:39:04'),
(4, 2, 3, 2, 'airflow.jpg', 'efb45c1d4849f4eb265c013ad0d59644_airflow.jpg', 'image/jpeg', 44425, '2026-01-11 21:39:04'),
(5, 2, 3, 2, 'Apa de gura.jpg', 'e15101e0572406aa4fdbeeca352ac03d_Apa_de_gura.jpg', 'image/jpeg', 58414, '2026-01-11 21:39:04'),
(6, 2, 3, 2, 'Compactare manuala.png', 'fac34c286c10eddaddfb00495820bd63_Compactare_manuala.png', 'image/png', 114393, '2026-01-11 21:39:04'),
(7, 2, 5, 1, 'copil trist.jpg', '00f28765823d087fbb3fc6908916c405_copil_trist.jpg', 'image/jpeg', 63075, '2026-01-11 21:44:07'),
(8, 2, 7, 1, 'emisiune drumul-inaltarii.jpg', 'f460ddbd8a8920269b0581e926152b43_emisiune_drumul-inaltarii.jpg', 'image/jpeg', 1162024, '2026-01-25 22:54:47');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `sender_id`, `body`, `is_internal`, `created_at`) VALUES
(1, 1, 2, 'test adaugare tichet + atasamente', 0, '2026-01-08 15:04:05'),
(2, 1, 1, 'doar test', 1, '2026-01-09 22:11:26'),
(3, 2, 2, 'Buna ziua, am de facut urmatoare modificari.\r\n1.dsafghh\r\n2.asdfghgjh\r\n3.dsfghfdjgfkhljh.\r\n4.dsaghtjyku\r\nSper sa le puteti rezolva jowehfdghgbiodhguofdjhvhfg[oh\'j;b;saljdkhvfvsjefbjfjepobfjdksnvjkdfvjkdfvkjndfvndklfvdfnchdigujhdfghijrdhgichfdncghvijdfhguivjkhrtdfghtrjdghirtghitrhgiotrhgiotrhighrtioghorthgiorhtoghtrhgiorthgorthdgokvdfjkghtrkrhgotroghtrohgjjjj', 0, '2026-01-11 21:39:04'),
(4, 2, 2, 'Buna ziua,\r\nMai am ceva de adaugat\r\nsdlklnfvhbi\'vjkdasvfbgnhmj,j.k/jl:\"liolukyjhgfd;fdjkesjrmncjrgiorheguytygoiyutioguncoirtugourtiovuhbiopuvrtiophuciougioyrioegyioprytiogbuoirgupoirtregnrtoigvtntproihguoitru', 0, '2026-01-11 21:39:58'),
(5, 2, 1, 'Buna marius', 1, '2026-01-11 21:44:07'),
(6, 2, 1, 'da o sa incercam sa rezolvam azi', 0, '2026-01-11 22:28:01'),
(7, 2, 1, 'poza noua', 0, '2026-01-25 22:54:47'),
(8, 2, 1, 'se rezolva', 0, '2026-01-26 22:46:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `vat` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen_tickets_at` datetime DEFAULT NULL,
  `last_seen_tasks_at` datetime DEFAULT NULL,
  `last_seen_chat_v2_msg_id` int(11) DEFAULT NULL,
  `last_seen_chat_msg_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `company`, `email`, `phone`, `password`, `role`, `created_at`, `address`, `avatar`, `city`, `country`, `vat`, `is_active`, `last_seen_tickets_at`, `last_seen_tasks_at`, `last_seen_chat_v2_msg_id`, `last_seen_chat_msg_id`) VALUES
(1, 'Admin Marius', NULL, 'admin@local.test', '0722590167', '$2y$10$Q3ZFGp7XrKrYGIminDiXM.S33eAlbJH36Q0t5ZMiXeqWsl5HFNAEq', 'admin', '2026-01-08 10:08:44', NULL, '1_1767873972.png', NULL, NULL, NULL, 1, '2026-02-02 09:36:00', '2026-02-02 10:57:42', 48, NULL),
(2, 'Client', 'Companie Testare', 'client@local.test', '12345678', '$2y$10$K.KqeGN0uAy4pr.aILxcMOPu8lvPeV97uysmGJ.WdOiFkvnkZc1N6', 'client', '2026-01-08 10:08:44', 'Bucuresti, 16', '0ee77e3b9b69236d81768454_itwebmedia.jpg', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL),
(4, 'Admin 2', NULL, 'marius.savucatalin@yahoo.com', NULL, '$2y$10$OzjbZJ94FkzWUVUUviQ.z.FpRywGUgnl1UStRxdLb.LlCoHh9qwOm', 'admin', '2026-01-25 18:26:24', NULL, NULL, NULL, NULL, NULL, 1, '2026-02-02 10:57:39', '2026-02-02 10:57:46', 48, NULL),
(5, 'Savu Marius', NULL, 'test_user1@createdesign.ro', NULL, '$2y$10$emVY7OGpe884QVjnWZ2ZXO55Tgyhmvdz0H1jY/WXpU.Tj5u9DjJ9e', 'client', '2026-01-25 18:28:25', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_attachments`
--
ALTER TABLE `chat_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ca_message_id` (`message_id`),
  ADD KEY `idx_ca_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conv_type` (`type`),
  ADD KEY `idx_conv_created_by` (`created_by`);

--
-- Indexes for table `conversation_attachments`
--
ALTER TABLE `conversation_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cva_message_id` (`message_id`),
  ADD KEY `idx_cva_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `conversation_messages`
--
ALTER TABLE `conversation_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_legacy_message` (`legacy_message_id`),
  ADD KEY `idx_cm_conversation` (`conversation_id`,`id`),
  ADD KEY `idx_cm_sender` (`sender_id`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `idx_cp_user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token_hash` (`token_hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_used` (`expires_at`,`used_at`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_status_deleted` (`status`,`deleted_at`);

--
-- Indexes for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ta_ticket_id` (`ticket_id`),
  ADD KEY `idx_ta_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_ta_message_id` (`message_id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket_id` (`ticket_id`),
  ADD KEY `idx_sender_id` (`sender_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_attachments`
--
ALTER TABLE `chat_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `conversation_attachments`
--
ALTER TABLE `conversation_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `conversation_messages`
--
ALTER TABLE `conversation_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_attachments`
--
ALTER TABLE `chat_attachments`
  ADD CONSTRAINT `fk_ca_message` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ca_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conv_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_attachments`
--
ALTER TABLE `conversation_attachments`
  ADD CONSTRAINT `fk_cva_message` FOREIGN KEY (`message_id`) REFERENCES `conversation_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cva_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_messages`
--
ALTER TABLE `conversation_messages`
  ADD CONSTRAINT `fk_cm_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `fk_cp_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `fk_ta_message` FOREIGN KEY (`message_id`) REFERENCES `ticket_messages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ta_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ta_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `fk_tm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tm_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

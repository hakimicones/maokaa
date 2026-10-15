-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 15 oct. 2026 à 12:02
-- Version du serveur : 10.4.27-MariaDB
-- Version de PHP : 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `site`
--

-- --------------------------------------------------------

--
-- Structure de la table `actualites`
--

CREATE TABLE `actualites` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `published_at` datetime DEFAULT current_timestamp(),
  `status` enum('published','draft') DEFAULT 'draft',
  `author_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `must_change_password` tinyint(1) DEFAULT 1,
  `role` enum('admin','editor') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `fullname`, `email`, `active`, `created_at`, `updated_at`, `must_change_password`, `role`) VALUES
(1, 'admin', '$2y$10$lcQB5JIPnZeOTONHpzso0uFDSFQ1TNh5OH2lqw/wTgrw7h.XEx7wK', 'Admin VEP', 'admin@vep.dz', 1, '2026-05-23 10:29:22', '2026-07-27 12:54:39', 0, 'admin'),
(2, 'TC', '$2y$10$t1271KwMqR/N5N/vuN.xU.MIym2M3tolSIAn5hy6RWs8vNnYmF22K', 'Admin TC', 'TC@vep.dz', 1, '2026-05-23 10:29:22', '2026-07-27 12:54:39', 0, 'admin'),
(4, 'sarah', '406361', 'Admin Sarah', 'sarah@vep.dz', 1, '2026-07-27 12:13:35', NULL, 1, 'admin');

-- --------------------------------------------------------

--
-- Structure de la table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `admin_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'inline_edit', 'Champ \'title\' modifié sur la page \'about\'', '::1', '2026-05-25 13:02:56'),
(2, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-25 13:03:56'),
(3, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-25 13:10:21'),
(4, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-25 13:45:31'),
(5, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-25 13:46:15'),
(6, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '::1', '2026-05-26 11:55:57'),
(7, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-26 13:07:15'),
(8, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-26 13:07:19'),
(9, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-26 13:09:03'),
(10, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-26 13:09:06'),
(11, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-05-26 13:09:09'),
(12, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '::1', '2026-06-07 12:21:14'),
(13, 1, 'update_product', 'KT50 / KH50', '::1', '2026-06-07 15:45:48'),
(14, 1, 'update_product', 'KT120 / KH120', '::1', '2026-06-07 15:55:44'),
(15, 1, 'update_product', 'Echantilloneur Air Standard', '::1', '2026-06-07 15:56:18'),
(16, 1, 'update_product', 'Balance Analytique BM500', '::1', '2026-06-07 16:22:17'),
(17, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '154.121.27.52', '2026-06-10 07:16:53'),
(18, 1, 'update_product', 'HIAC 9703+', '154.121.27.52', '2026-06-10 07:17:17'),
(19, 1, 'update_product', 'HIAC 9703+', '154.121.27.52', '2026-06-10 10:17:34'),
(20, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.27.52', '2026-06-10 10:18:24'),
(21, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.27.52', '2026-06-10 10:18:30'),
(22, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.27.52', '2026-06-10 10:24:09'),
(23, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.27.52', '2026-06-10 10:24:15'),
(24, 1, 'update_product', 'HIAC 9703+', '154.121.80.136', '2026-06-10 11:04:50'),
(25, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '154.121.80.136', '2026-06-10 14:02:51'),
(26, 1, 'ai_content', 'Instruction IA : Corrige le texte de la page', '154.121.80.136', '2026-06-10 14:03:08'),
(27, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '154.121.80.136', '2026-06-10 14:03:16'),
(28, 1, 'update_product', 'Poids à gorge de préhension', '154.121.80.136', '2026-06-10 14:03:40'),
(29, 1, 'update_product', 'Akivision', '154.121.80.136', '2026-06-10 14:03:47'),
(30, 1, 'update_product', 'Jeux de poids', '154.121.80.136', '2026-06-10 14:06:04'),
(31, 1, 'ai_content', 'Instruction IA : corrige la section Nos marques partenaires , en la mettons dans un div de class=container pour l\'adapter au style de la page', '129.45.67.224', '2026-06-11 08:27:42'),
(32, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '129.45.67.224', '2026-06-11 08:27:49'),
(33, 1, 'ai_content', 'Instruction IA : recrit le texte d\'une facon drole', '129.45.67.224', '2026-06-11 08:41:07'),
(34, 1, 'delete_product', 'ID: 81', '41.108.12.95', '2026-06-11 10:11:37'),
(35, 1, 'delete_product', 'ID: 80', '41.108.12.95', '2026-06-11 10:11:41'),
(36, 1, 'delete_product', 'ID: 79', '41.108.12.95', '2026-06-11 10:11:44'),
(37, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '41.108.12.95', '2026-06-11 10:25:16'),
(38, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '41.108.12.95', '2026-06-11 10:26:34'),
(39, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '41.108.12.95', '2026-06-11 10:29:36'),
(40, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '41.108.12.95', '2026-06-11 10:29:49'),
(41, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '41.108.12.95', '2026-06-11 10:32:03'),
(42, 1, 'delete_product', 'ID: 75', '41.108.12.95', '2026-06-11 10:53:52'),
(43, 1, 'delete_product', 'ID: 70', '41.108.12.95', '2026-06-11 10:53:56'),
(44, 1, 'update_product', 'HIAC 9703+', '41.108.12.95', '2026-06-11 10:55:07'),
(45, 1, 'delete_news', 'ID: 2', '41.108.12.95', '2026-06-11 11:03:33'),
(46, 1, 'update_product', 'Jeux de poids', '41.108.12.95', '2026-06-11 11:08:47'),
(47, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '129.45.67.224', '2026-06-11 11:35:41'),
(48, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '129.45.67.224', '2026-06-11 11:35:57'),
(49, 1, 'ai_content', 'Instruction IA : VEP est une société spécialisée dans l’importation et la distribution de matériel et de consommable de laboratoire , cree un paragraphe pour presentre notre entreprise dans un temps commerciale', '129.45.67.224', '2026-06-11 11:37:42'),
(50, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '129.45.67.224', '2026-06-11 11:37:48'),
(51, 1, 'delete_partner', 'ID: 3', '41.108.12.95', '2026-06-11 11:42:58'),
(52, 1, 'inline_edit', 'Champ \'title\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-11 12:06:24'),
(53, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '154.121.31.196', '2026-06-11 14:32:03'),
(54, 1, 'update_product', 'Pese-Palette IU', '154.121.31.196', '2026-06-11 14:33:29'),
(55, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '154.121.31.196', '2026-06-11 14:34:06'),
(56, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.31.196', '2026-06-11 14:44:56'),
(57, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.31.196', '2026-06-11 14:45:04'),
(58, 1, 'ai_content', 'Instruction IA : Ecris un texte qui explique ce que VEP peut faire pour les laboratoires en tant que fournisseurs d\'outils specialisés', '154.121.31.196', '2026-06-11 14:46:03'),
(59, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '154.121.31.196', '2026-06-11 14:46:12'),
(60, 1, 'update_product', 'STERISART NF', '41.108.12.95', '2026-06-11 15:08:55'),
(61, 1, 'inline_edit_product', 'Produit #71 — champ \'description_complete\' modifié', '41.108.12.95', '2026-06-11 15:09:37'),
(62, 1, 'inline_edit_product', 'Produit #71 — champ \'nom\' modifié', '41.108.12.95', '2026-06-11 15:10:47'),
(63, 1, 'inline_edit_product', 'Produit #71 — champ \'nom\' modifié', '41.108.12.95', '2026-06-11 15:11:23'),
(64, 1, 'inline_edit_product', 'Produit #71 — champ \'nom\' modifié', '41.108.12.95', '2026-06-11 15:11:30'),
(65, 1, 'inline_edit_product', 'Produit #71 — champ \'description\' modifié', '41.108.12.95', '2026-06-11 15:11:32'),
(66, 1, 'inline_edit_product', 'Produit #71 — champ \'nom\' modifié', '41.108.12.95', '2026-06-11 15:11:46'),
(67, 1, 'ai_content', 'Instruction IA : regardes le contenu de cette page , et adaptes le pour qu\'il soit adapter aux blocs de grapeJS', '129.45.67.224', '2026-06-14 08:39:30'),
(68, 1, 'create_product', 'nouveau', '129.45.67.224', '2026-06-14 08:42:31'),
(69, 1, 'upload_media', 'Fichier: img_6a2e69c90d5d5.webp', '129.45.67.224', '2026-06-14 08:43:53'),
(70, 1, 'delete_media', 'Fichier: assets/images/img_6a2e69c90d5d5.webp', '129.45.67.224', '2026-06-14 08:44:26'),
(71, 1, 'upload_media', 'Fichier: img_6a2e6aae4c296.webp', '129.45.67.224', '2026-06-14 08:47:42'),
(72, 1, 'delete_media', 'Fichier: assets/images/img_6a2e6aae4c296.webp', '129.45.67.224', '2026-06-14 08:47:47'),
(73, 1, 'upload_media', 'Fichier: img_6a2e6b9145bf1.jpg', '129.45.67.224', '2026-06-14 08:51:29'),
(74, 1, 'rename_media', 'De: assets/images/img_6a2e6b9145bf1.jpg → imglab.jpg', '129.45.67.224', '2026-06-14 08:51:39'),
(75, 1, 'update_product', 'nouveau', '129.45.67.224', '2026-06-14 08:52:37'),
(76, 1, 'delete_brand', 'ID: 1', '129.45.67.224', '2026-06-14 09:50:19'),
(77, 1, 'delete_user', 'ID: 3', '129.45.67.224', '2026-06-14 10:37:11'),
(78, 1, 'delete_product', 'ID: 83', '129.45.67.224', '2026-06-14 10:39:52'),
(79, 1, 'create_product', 'nouveau', '129.45.67.224', '2026-06-14 10:40:29'),
(80, 1, 'delete_brand', 'ID: 6', '129.45.67.224', '2026-06-14 11:02:32'),
(81, 1, 'delete_message', 'ID: 2', '129.45.67.224', '2026-06-14 11:15:01'),
(82, 1, 'delete_message', 'ID: 1', '129.45.67.224', '2026-06-14 11:15:03'),
(83, 1, 'delete_media', 'Fichier: assets/images/brands/brand_6a2e8a345ac05.png', '129.45.67.224', '2026-06-14 11:24:54'),
(84, 1, 'delete_media', 'Fichier: assets/images/imglab.jpg', '129.45.67.224', '2026-06-14 11:25:14'),
(85, 1, 'delete_media', 'Fichier: assets/images/products/prod_6a2e6bd56d75b.jpg', '129.45.67.224', '2026-06-14 11:25:18'),
(86, 1, 'delete_media', 'Fichier: assets/images/brands/brand_6a2e7ce7d895a.png', '129.45.67.224', '2026-06-14 11:25:23'),
(87, 1, 'delete_media', 'Fichier: assets/images/products/prod_6a2e851dda63d.png', '129.45.67.224', '2026-06-14 11:25:26'),
(88, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '129.45.67.224', '2026-06-14 11:32:58'),
(89, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '129.45.67.224', '2026-06-14 11:33:01'),
(90, 1, 'inline_edit_product', 'Produit #76 — champ \'image\' modifié', '129.45.67.224', '2026-06-14 11:47:08'),
(91, 1, 'inline_edit_product', 'Produit #76 — champ \'image\' modifié', '129.45.67.224', '2026-06-14 11:47:34'),
(92, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '105.101.33.221', '2026-06-14 11:53:13'),
(93, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '105.101.33.221', '2026-06-14 12:02:03'),
(94, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '105.101.33.221', '2026-06-14 12:02:10'),
(95, 1, 'inline_edit_setting', 'Setting \'footer_phone\' modifié', '154.121.29.216', '2026-06-14 16:53:54'),
(96, 1, 'inline_edit_setting', 'Setting \'footer_phone\' modifié', '154.121.29.216', '2026-06-14 16:54:08'),
(97, 1, 'inline_edit_setting', 'Setting \'footer_email\' modifié', '154.121.29.216', '2026-06-14 16:54:21'),
(98, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '105.101.102.134', '2026-06-15 08:15:14'),
(99, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:25:27'),
(100, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:25:50'),
(101, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:26:42'),
(102, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:27:18'),
(103, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:27:38'),
(104, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:28:01'),
(105, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.101.102.134', '2026-06-15 08:28:16'),
(106, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:45:31'),
(107, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:46:08'),
(108, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:48:00'),
(109, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:48:49'),
(110, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:49:06'),
(111, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:49:08'),
(112, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:49:09'),
(113, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:49:10'),
(114, 1, 'upload_media', 'Fichier: img_6a301f20a98cc.jpg', '105.101.102.134', '2026-06-15 15:49:52'),
(115, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:50:18'),
(116, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 15:58:49'),
(117, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'about\'', '105.101.102.134', '2026-06-15 16:02:23'),
(118, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.100.57.157', '2026-06-17 09:57:39'),
(119, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.100.57.157', '2026-06-17 09:58:28'),
(120, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'contact\'', '105.100.57.157', '2026-06-17 09:58:59'),
(121, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'partners\'', '105.100.57.157', '2026-06-17 10:01:06'),
(122, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'partners\'', '105.100.57.157', '2026-06-17 10:01:19'),
(123, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-17 10:27:02'),
(124, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-17 10:27:32'),
(125, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-17 10:43:18'),
(126, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-17 10:44:09'),
(127, 1, 'inline_edit_product', 'Produit #71 — champ \'nom\' modifié', '105.100.57.157', '2026-06-17 11:44:17'),
(128, 1, 'inline_edit_product', 'Produit #14 — champ \'image\' modifié', '105.100.57.157', '2026-06-17 11:44:56'),
(129, 1, 'upload_media', 'Fichier: img_6a32896213ede.jpg', '105.100.57.157', '2026-06-17 11:47:46'),
(130, 1, 'inline_edit_product', 'Produit #16 — champ \'image\' modifié', '105.100.57.157', '2026-06-17 11:47:56'),
(131, 1, 'delete_media', 'Fichier: assets/images/news/news_6a2a96189eada.jpg', '105.100.57.157', '2026-06-17 11:48:23'),
(132, 1, 'delete_media', 'Fichier: assets/images/news/news_6a2a953559b2e.jpg', '105.100.57.157', '2026-06-17 11:48:31'),
(133, 1, 'upload_media', 'Fichier: img_6a3289f5457e0.jpg', '105.100.57.157', '2026-06-17 11:50:13'),
(134, 1, 'inline_edit_product', 'Produit #18 — champ \'image\' modifié', '105.100.57.157', '2026-06-17 11:50:21'),
(135, 1, 'update_product', 'Filtres Asura', '105.100.57.157', '2026-06-17 11:52:17'),
(136, 1, 'update_product', 'Smart Armoires', '105.100.57.157', '2026-06-17 11:52:29'),
(137, 1, 'update_product', 'Captair Smart', '105.100.57.157', '2026-06-17 11:57:31'),
(138, 1, 'update_product', 'Filtre Asura', '105.100.57.157', '2026-06-17 11:57:46'),
(139, 1, 'upload_media', 'Fichier: img_6a32c39d243ad.jpg', '105.100.57.157', '2026-06-17 15:56:13'),
(140, 1, 'upload_media', 'Fichier: img_6a32c3a6b4dc2.jpg', '105.100.57.157', '2026-06-17 15:56:22'),
(141, 1, 'inline_edit_product', 'Produit #46 — champ \'image\' modifié', '105.100.57.157', '2026-06-17 15:57:24'),
(142, 1, 'inline_edit_product', 'Produit #45 — champ \'image\' modifié', '105.100.57.157', '2026-06-17 15:57:39'),
(143, 1, 'upload_media', 'Fichier: img_6a32c4acb5c17.jpg', '105.100.57.157', '2026-06-17 16:00:44'),
(144, 1, 'inline_edit_product', 'Produit #69 — champ \'image\' modifié', '105.100.57.157', '2026-06-17 16:00:51'),
(145, 1, 'inline_edit_product', 'Produit #69 — champ \'caracteristiques_techniques\' modifié', '105.100.57.157', '2026-06-17 16:01:51'),
(146, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 19:47:33'),
(147, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 19:47:43'),
(148, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 20:04:58'),
(149, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:14:42'),
(150, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:15:14'),
(151, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:16:42'),
(152, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:17:19'),
(153, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:19:04'),
(154, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:19:05'),
(155, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:19:10'),
(156, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:19:19'),
(157, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '154.243.55.226', '2026-06-17 22:19:28'),
(158, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-18 08:31:23'),
(159, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-18 08:32:22'),
(160, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-18 08:32:35'),
(161, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-18 08:33:17'),
(162, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'brands\'', '129.45.67.224', '2026-06-18 08:33:46'),
(163, 1, 'update_product', 'KIRAY 100', '105.101.207.38', '2026-06-18 10:37:07'),
(164, 1, 'update_product', 'KIRAY 200', '105.101.207.38', '2026-06-18 10:37:22'),
(165, 1, 'update_product', 'KIRAY 300', '105.101.207.38', '2026-06-18 10:37:39'),
(166, 1, 'update_product', 'TK 110 – TK 112', '105.101.207.38', '2026-06-18 10:37:52'),
(167, 1, 'update_product', 'TR 110 – TR 112', '105.101.207.38', '2026-06-18 10:38:04'),
(168, 1, 'upload_media', 'Fichier: img_6a33cb62950e7.jpg', '105.101.207.38', '2026-06-18 10:41:38'),
(169, 1, 'inline_edit_product', 'Produit #78 — champ \'image\' modifié', '105.101.207.38', '2026-06-18 10:42:27'),
(170, 1, 'delete_media', 'Fichier: assets/images/img_6a33cb62950e7.jpg', '105.101.207.38', '2026-06-18 10:46:12'),
(171, 1, 'upload_media', 'Fichier: img_6a33cc8243662.jpg', '105.101.207.38', '2026-06-18 10:46:26'),
(172, 1, 'inline_edit_product', 'Produit #78 — champ \'image\' modifié', '105.101.207.38', '2026-06-18 10:46:41'),
(173, 1, 'inline_edit_product', 'Produit #78 — champ \'nom\' modifié', '105.101.207.38', '2026-06-18 10:49:04'),
(174, 1, 'inline_edit_product', 'Produit #78 — champ \'nom\' modifié', '105.101.207.38', '2026-06-18 10:50:11'),
(175, 1, 'inline_edit_product', 'Produit #78 — champ \'nom\' modifié', '105.101.207.38', '2026-06-18 10:50:25'),
(176, 1, 'inline_edit_product', 'Produit #78 — champ \'nom\' modifié', '105.101.207.38', '2026-06-18 10:50:30'),
(177, 1, 'upload_media', 'Fichier: img_6a33cdc681e20.jpg', '105.101.207.38', '2026-06-18 10:51:50'),
(178, 1, 'update_product', 'Cubis® II Semi-Micro\nConfigurable Lab Balances', '105.101.207.38', '2026-06-18 10:57:35'),
(179, 1, 'update_product', 'Cubis® II Semi-MicroConfigurable Lab Balances', '105.101.207.38', '2026-06-18 10:58:10'),
(180, 1, 'update_product', 'Cubis® II Semi-MicroConfigurable Lab Balances', '105.101.207.38', '2026-06-18 10:58:36'),
(181, 1, 'update_product', 'Poids à gorge de préhension', '105.101.207.38', '2026-06-18 11:00:56'),
(182, 1, 'update_product', 'Cubis® II Semi-MicroConfigurable Lab Balances', '105.101.207.38', '2026-06-18 11:01:15'),
(183, 1, 'update_product', 'STERISART&amp;nbsp;Tests de Stérilité', '105.101.207.38', '2026-06-18 11:02:30'),
(184, 1, 'update_product', 'MET ONE 3400', '105.101.207.38', '2026-06-18 11:18:19'),
(185, 1, 'update_product', 'MET ONE 3400 +', '105.101.207.38', '2026-06-18 11:19:54'),
(186, 1, 'update_product', 'Jeux de poids', '105.101.207.38', '2026-06-18 11:20:28'),
(187, 1, 'update_product', 'Minisart Syringe Filters', '105.101.207.38', '2026-06-18 11:23:21'),
(188, 1, 'update_product', 'Minisart Syringe Filters', '105.101.207.38', '2026-06-18 11:24:09'),
(189, 1, 'create_product', 'Quintix® Pro Lab Balances', '105.101.207.38', '2026-06-18 11:41:56'),
(190, 1, 'create_product', 'Entris® II Advanced Line', '105.101.207.38', '2026-06-18 11:46:20'),
(191, 1, 'create_product', 'Entris® II Essential Line', '105.101.207.38', '2026-06-18 11:47:28'),
(192, 1, 'update_product', 'Cubis® II Semi-MicroConfigurable Lab Balances', '105.101.207.38', '2026-06-18 11:48:36'),
(193, 1, 'delete_product', 'ID: 84', '105.101.207.38', '2026-06-18 11:50:44'),
(194, 1, 'update_product', 'Jeux de poids', '105.101.207.38', '2026-06-18 11:52:23'),
(195, 1, 'delete_product', 'ID: 77', '105.101.207.38', '2026-06-18 11:52:40'),
(196, 1, 'delete_product', 'ID: 74', '105.101.207.38', '2026-06-18 11:52:46'),
(197, 1, 'delete_product', 'ID: 73', '105.101.207.38', '2026-06-18 11:52:52'),
(198, 1, 'delete_product', 'ID: 72', '105.101.207.38', '2026-06-18 11:52:58'),
(199, 1, 'create_product', 'Analyseur d&#039;humidité Sartorius MA37', '105.101.207.38', '2026-06-18 11:54:49'),
(200, 1, 'update_product', 'Analyseur d&#039;humidité Sartorius MA37', '105.101.207.38', '2026-06-18 11:55:12'),
(201, 1, 'create_product', 'Analyseur d&#039;humidité Sartorius MA160', '105.101.207.38', '2026-06-18 12:06:16'),
(202, 1, 'update_product', 'Analyseur d&#039;humidité Sartorius MA160', '105.101.207.38', '2026-06-18 12:06:33'),
(203, 1, 'update_product', 'Analyseur d&#039;humidité Sartorius MA37', '105.101.207.38', '2026-06-18 12:07:22'),
(204, 1, 'update_product', 'Entris® II Essential Line', '105.101.207.38', '2026-06-18 12:07:52'),
(205, 1, 'update_product', 'Entris® II Essential Line', '105.101.207.38', '2026-06-18 12:08:15'),
(206, 1, 'update_product', 'Entris® II Advanced Line', '105.101.207.38', '2026-06-18 12:08:44'),
(207, 1, 'update_product', 'Quintix® Pro Lab Balances', '105.101.207.38', '2026-06-18 12:09:12'),
(208, 1, 'update_product', 'Cubis® II Premium balances', '105.101.207.38', '2026-06-18 12:10:34'),
(209, 1, 'update_product', 'Jeux de poids', '105.101.207.38', '2026-06-18 12:12:07'),
(210, 1, 'create_product', 'Analyseurs d&#039;humidité Sartorius MA100', '105.101.207.38', '2026-06-18 12:14:09'),
(211, 1, 'update_product', 'Printer YDP 20', '105.101.207.38', '2026-06-18 12:24:52'),
(212, 1, 'delete_product', 'ID: 63', '105.101.207.38', '2026-06-18 12:25:11'),
(213, 1, 'update_product', 'Printer YDP 40', '105.101.207.38', '2026-06-18 12:30:15'),
(214, 1, 'upload_media', 'Fichier: img_6a33e8242ae9e.jpg', '105.101.207.38', '2026-06-18 12:44:20'),
(215, 1, 'update_product', 'KT 220 / KH 220 / KTT 220', '105.101.207.38', '2026-06-18 12:45:53'),
(216, 1, 'update_product', 'KT 220 / KH 220 / KTT 220', '105.101.207.38', '2026-06-18 12:46:23'),
(217, 1, 'update_product', 'KT 220 / KH 220 / KTT 220', '105.101.207.38', '2026-06-18 12:47:33'),
(218, 1, 'inline_edit_product', 'Produit #40 — champ \'description\' modifié', '105.101.207.38', '2026-06-18 12:49:53'),
(219, 1, 'inline_edit_product', 'Produit #40 — champ \'caracteristiques_techniques\' modifié', '105.101.207.38', '2026-06-18 12:50:07'),
(220, 1, 'inline_edit_product', 'Produit #41 — champ \'description\' modifié', '105.101.207.38', '2026-06-18 12:52:11'),
(221, 1, 'inline_edit_product', 'Produit #41 — champ \'description_complete\' modifié', '105.101.207.38', '2026-06-18 12:52:24'),
(222, 1, 'update_product', 'KT50 / KH50', '105.101.207.38', '2026-06-18 12:53:57'),
(223, 1, 'update_product', 'KT 120 / KH 120', '105.101.207.38', '2026-06-18 12:58:20'),
(224, 1, 'create_product', 'KT 320 / KTT 320', '105.101.207.38', '2026-06-18 13:02:42'),
(225, 1, 'create_product', 'KCC 320 / KPA 320', '105.101.207.38', '2026-06-18 13:07:14'),
(226, 1, 'update_product', 'Jeux de poids', '105.101.207.38', '2026-06-18 13:08:44'),
(227, 1, 'update_product', 'KT 220 / KH 220 / KTT 220', '105.101.207.38', '2026-06-18 13:09:03'),
(228, 1, 'update_product', 'Pese-Palette IU', '105.101.207.38', '2026-06-18 13:09:56'),
(229, 1, 'update_product', 'AMI 310', '105.101.207.38', '2026-06-18 13:15:09'),
(230, 1, 'update_product', 'AMI 310', '105.101.207.38', '2026-06-18 13:15:48'),
(231, 1, 'update_product', 'AMI 310', '105.101.207.38', '2026-06-18 13:16:12'),
(232, 1, 'update_product', 'AMI 310', '105.101.207.38', '2026-06-18 13:16:49'),
(233, 1, 'update_product', 'DBM610', '105.101.207.38', '2026-06-18 13:18:33'),
(234, 1, 'update_product', 'DBM 620', '105.101.207.38', '2026-06-18 13:20:02'),
(235, 1, 'update_product', 'DBM 620', '105.101.207.38', '2026-06-18 13:20:35'),
(236, 1, 'update_product', 'DBM 620', '105.101.207.38', '2026-06-18 13:21:38'),
(237, 1, 'update_product', 'HIAC 9703+', '105.101.207.38', '2026-06-18 13:23:12'),
(238, 1, 'update_product', 'STERISART&amp;amp;nbsp;Tests de Stérilité', '105.101.207.38', '2026-06-18 13:23:38'),
(239, 1, 'update_product', 'Rampe de filtration 3 postes 500ml', '105.101.207.38', '2026-06-18 13:24:06'),
(240, 1, 'update_product', 'Rampe 6 postes 500ml', '105.101.207.38', '2026-06-18 13:24:48'),
(241, 1, 'update_product', 'Rampe 6 postes 500ml', '105.101.207.38', '2026-06-18 13:25:31'),
(242, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '::1', '2026-07-22 12:38:03'),
(243, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '::1', '2026-07-22 12:38:11'),
(244, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '::1', '2026-07-22 12:38:17'),
(245, 1, 'delete_message', 'ID: 3', '::1', '2026-07-28 13:15:12'),
(246, 1, 'activate_theme', 'Thème \'default1\' activé', '::1', '2026-10-14 14:02:15'),
(247, 1, 'activate_theme', 'Thème \'welcome\' activé', '::1', '2026-10-14 14:02:20'),
(248, 1, 'activate_theme', 'Thème \'default1\' activé', '::1', '2026-10-14 18:26:36'),
(249, 1, 'inline_edit', 'Champ \'body\' modifié sur la page \'home\'', '::1', '2026-10-14 18:27:17'),
(250, 1, 'activate_theme', 'Thème \'default\' activé', '::1', '2026-10-14 18:27:46'),
(251, 1, 'activate_theme', 'Thème \'welcome\' activé', '::1', '2026-10-14 18:58:48');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `sujet` varchar(255) DEFAULT NULL,
  `message` longtext DEFAULT NULL,
  `lu` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contacts`
--

INSERT INTO `contacts` (`id`, `nom`, `email`, `telephone`, `sujet`, `message`, `lu`, `created_at`, `updated_at`) VALUES
(4, 'Sarah Teffahi', 'sarahteffahi@gmail.com', '1999911', '11111', 'hhhh', 0, '2026-07-29 14:40:28', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `content`
--

CREATE TABLE `content` (
  `id` int(11) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(512) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `template` varchar(100) DEFAULT 'default',
  `status` enum('published','draft') DEFAULT 'published',
  `language` varchar(10) DEFAULT 'fr',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `content`
--

INSERT INTO `content` (`id`, `slug`, `title`, `subtitle`, `meta_title`, `meta_description`, `body`, `template`, `status`, `language`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Votre partenaire incontournable du laboratoire en Algérie', 'Importation et distribution de matériels et consommables de laboratoire depuis plus de 20 ans.', 'Noor Guide — Application de guidage pour personnes aveugles', 'Noor Guide — Application mobile de guidage pour personnes aveugles et malvoyantes. Navigation intelligente, parcours personnalisés, détection Bluetooth.', '<section class=\"hero fade-in\" style=\"background-image:url(assets/images/noor-guide/mobile-app.jpg);\">\n    <div class=\"container\">\n        <div class=\"hero-content reveal\">\n            <h1 id=\"hero-title\" class=\"\">Découvrez<br>Noor Guide</h1>\n            <p class=\"\">\n                Noor Guide est une application mobile conçue pour accompagner les personnes aveugles et malvoyantes dans leurs déplacements, en leur offrant une expérience de navigation simple, intuitive et entièrement accessible.\n            </p>\n            <div class=\"hero-actions\">\n                <a href=\"#download\" class=\"btn btn-primary\"><i class=\"fab fa-apple\"></i> App Store</a>\n                <a href=\"#download\" class=\"btn btn-outline\"><i class=\"fab fa-google-play\"></i> Google Play</a>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section fade-in\" id=\"features\">\n    <div class=\"container\">\n        <div class=\"section-header reveal\">\n            <h2 id=\"domains-title\" class=\"\">Nos domaines d\'intervention</h2>\n            <p class=\"\">\n                Des solutions complètes pour rendre chaque espace accessible aux personnes en situation de handicap visuel.\n            </p>\n        </div>\n        <div class=\"cards-grid\">\n            <article class=\"card reveal delay-1 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-route\"></i></div>\n                <h3 class=\"\">Navigation intelligente</h3>\n                <p class=\"\">\n                    Choisissez votre destination parmi les lieux disponibles et laissez Noor Guide vous accompagner tout au long de votre parcours.\n                </p>\n                <a href=\"#features\" class=\"card-link\">En savoir plus</a>\n            </article>\n            <article class=\"card reveal delay-2 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-map-marked-alt\"></i></div>\n                <h3 class=\"\">Création de parcours personnalisés</h3>\n                <p class=\"\">\n                    Planifiez vos déplacements en créant des parcours personnalisés composés de plusieurs destinations. Organisez votre itinéraire selon vos besoins et suivez-le facilement depuis l\'application.\n                </p>\n                <a href=\"#features\" class=\"card-link\">En savoir plus</a>\n            </article>\n            <article class=\"card reveal delay-3 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-bluetooth-b\"></i></div>\n                <h3 class=\"\">Détection des dispositifs à proximité</h3>\n                <p class=\"\">\n                    L\'application détecte les dispositifs de guidage Bluetooth situés à proximité, affiche leur disponibilité ainsi que leur distance, afin de faciliter la connexion et l\'activation du bon dispositif.\n                </p>\n                <a href=\"#features\" class=\"card-link\">En savoir plus</a>\n            </article>\n            <article class=\"card reveal delay-4 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-info-circle\"></i></div>\n                <h3 class=\"\">Informations sur les lieux</h3>\n                <p class=\"\">\n                    Consultez les informations essentielles sur chaque lieu avant de commencer votre déplacement afin de mieux préparer votre parcours et de découvrir les services disponibles.\n                </p>\n                <a href=\"#features\" class=\"card-link\">En savoir plus</a>\n            </article>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section section-dark fade-in\">\n    <div class=\"container\">\n        <h2 id=\"stats-title\" class=\"sr-only\">Nos chiffres clés</h2>\n        <div class=\"stats-grid\">\n            <div class=\"stat-item reveal delay-1\">\n                <span class=\"stat-number\" data-count=\"5000\" data-suffix=\"+\">0</span>\n                <span class=\"stat-label\">Utilisateurs actifs</span>\n            </div>\n            <div class=\"stat-item reveal delay-2\">\n                <span class=\"stat-number\" data-count=\"50\" data-suffix=\"+\">0</span>\n                <span class=\"stat-label\">Espaces équipés</span>\n            </div>\n            <div class=\"stat-item reveal delay-3\">\n                <span class=\"stat-number\" data-count=\"100\" data-suffix=\"%\">0</span>\n                <span class=\"stat-label\">Accessible</span>\n            </div>\n            <div class=\"stat-item reveal delay-4\">\n                <span class=\"stat-number\" data-count=\"24\" data-suffix=\"/7\">0</span>\n                <span class=\"stat-label\">Disponible</span>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section section-alt fade-in\" id=\"solutions\">\n    <div class=\"container\">\n        <div class=\"section-header reveal\">\n            <h2 id=\"solutions-title\" class=\"\">Nos solutions phares</h2>\n            <p class=\"\">\n                Des technologies innovantes conçues avec et pour les personnes en situation de handicap visuel.\n            </p>\n        </div>\n        <div class=\"cards-grid\">\n            <article class=\"card reveal delay-1 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-volume-up\"></i></div>\n                <h3 class=\"\">Navigation vocale</h3>\n                <p class=\"\">\n                    Instructions audio claires et précises pour vous guider à chaque étape de votre déplacement.\n                </p>\n                <a href=\"#solutions\" class=\"card-link\">Découvrir</a>\n            </article>\n            <article class=\"card reveal delay-2 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-broadcast-tower\"></i></div>\n                <h3 class=\"\">Bluetooth intelligent</h3>\n                <p class=\"\">\n                    Détection automatique des balises et dispositifs de guidage à proximité en temps réel.\n                </p>\n                <a href=\"#solutions\" class=\"card-link\">Découvrir</a>\n            </article>\n            <article class=\"card reveal delay-3 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-project-diagram\"></i></div>\n                <h3 class=\"\">Itinéraires adaptés</h3>\n                <p class=\"\">\n                    Création d\'itinéraires personnalisés en quelques taps. Noor Guide calcule le meilleur chemin.\n                </p>\n                <a href=\"#solutions\" class=\"card-link\">Découvrir</a>\n            </article>\n            <article class=\"card reveal delay-4 fade-in\">\n                <div class=\"card-icon\"><i class=\"fas fa-hand-pointer\"></i></div>\n                <h3 class=\"\">Retour haptique</h3>\n                <p class=\"\">\n                    Vibrations et signaux sonores pour confirmer vos actions et renforcer votre repère spatial.\n                </p>\n                <a href=\"#solutions\" class=\"card-link\">Découvrir</a>\n            </article>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section fade-in\" id=\"accessibility\">\n    <div class=\"container\">\n        <div class=\"solution-hero\">\n            <div class=\"solution-hero-text reveal-left\">\n                <h2 id=\"a11y-title\" class=\"\">Une expérience pensée pour l\'accessibilité</h2>\n                <p class=\"\">\n                    Chaque écran de Noor Guide a été conçu pour être entièrement compatible avec les lecteurs d\'écran. L\'interface propose une navigation fluide, des formulaires accessibles et des interactions simples afin de garantir une expérience inclusive pour tous les utilisateurs.\n                </p>\n                <a href=\"#download\" class=\"btn btn-secondary\">Découvrir l\'accessibilité</a>\n            </div>\n            <div class=\"solution-image-placeholder reveal-right\" aria-label=\"Illustration de l\'accessibilité de Noor Guide\">\n                <img src=\"assets/images/noor-guide/illustration-accessibility.svg\" alt=\"Illustration accessibilité\" style=\"width:100%;max-width:350px;height:auto;\" class=\"\">\n            </div>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section section-alt fade-in\" id=\"autonomy\">\n    <div class=\"container\">\n        <div class=\"solution-hero reverse\">\n            <div class=\"solution-hero-text reveal-right\">\n                <h2 id=\"autonomy-title\" class=\"\">Une solution au service de l\'autonomie</h2>\n                <p class=\"\">\n                    Notre objectif est de permettre aux personnes aveugles et malvoyantes de se déplacer avec davantage d\'autonomie, de confiance et de sérénité dans les espaces équipés de la solution Noor Guide.\n                </p>\n                <a href=\"#download\" class=\"btn btn-secondary\">Découvrir notre mission</a>\n            </div>\n            <div class=\"solution-image-placeholder reveal-left\" aria-label=\"Illustration de l\'autonomie avec Noor Guide\">\n                <img src=\"assets/images/noor-guide/illustration-autonomy.svg\" alt=\"Illustration autonomie\" style=\"width:100%;max-width:350px;height:auto;\" class=\"\">\n            </div>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section fade-in\" id=\"testimonials\">\n    <div class=\"container\">\n        <div class=\"section-header reveal\">\n            <h2 id=\"testimonials-title\" class=\"\">Ils utilisent Noor Guide</h2>\n            <p class=\"\">\n                Découvrez les témoignages de nos utilisateurs qui ont adopté Noor Guide pour leurs déplacements quotidiens.\n            </p>\n        </div>\n        <div class=\"testimonials-grid\">\n            <article class=\"testimonial-card reveal delay-1\">\n                <blockquote class=\"\">\n                    <p class=\"testimonial-text\">\n                        « Noor Guide a changé ma façon de me déplacer. Je peux maintenant aller au travail de manière autonome grâce au guidage sonore. C\'est une vraie révolution dans ma vie quotidienne. »\n                    </p>\n                </blockquote>\n                <p class=\"testimonial-author\">Sarah M.</p>\n                <p class=\"testimonial-role\">Utilisatrice quotidienne</p>\n            </article>\n            <article class=\"testimonial-card reveal delay-2\">\n                <blockquote class=\"\">\n                    <p class=\"testimonial-text\">\n                        « La détection Bluetooth est incroyable. Je sais exactement où se trouve le dispositif le plus proche et l\'application me guide jusqu\'à lui sans aucune intervention. »\n                    </p>\n                </blockquote>\n                <p class=\"testimonial-author\">Ahmed K.</p>\n                <p class=\"testimonial-role\">Voyageur régulier</p>\n            </article>\n            <article class=\"testimonial-card reveal delay-3\">\n                <blockquote class=\"\">\n                    <p class=\"testimonial-text\">\n                        « Enfin une application vraiment pensée pour nous. Chaque écran est accessible et le retour sonore est clair. Je recommande vivement Noor Guide à tous mes amis malvoyants. »\n                    </p>\n                </blockquote>\n                <p class=\"testimonial-author\">Fatima B.</p>\n                <p class=\"testimonial-role\">Utilisatrice malvoyante</p>\n            </article>\n        </div>\n    </div>\n</section>\n\n\n<section class=\"section section-dark fade-in\" id=\"download\">\n    <div class=\"container reveal\" style=\"text-align: center;\">\n        <h2 id=\"cta-title\" class=\"\">Prêt à viguer en toute<br>autonomie ?</h2>\n        <p style=\"max-width: 600px; margin: 1rem auto 2rem; color: rgba(255,255,255,0.8);\" class=\"\">\n            Téléchargez Noor Guide gratuitement et découvrez une nouvelle façon de vous déplacer. Disponible sur iOS et Android.\n        </p>\n        <div style=\"display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;\">\n            <a href=\"#\" class=\"btn btn-primary\"><i class=\"fab fa-apple\"></i> App Store</a>\n            <a href=\"#\" class=\"btn btn-primary\" style=\"background:#28A745;\"><i class=\"fab fa-google-play\"></i> Google Play</a>\n        </div>\n    </div>\n</section>', 'home', 'published', 'fr', '2026-05-23 10:29:22', '2026-10-14 18:27:17'),
(2, 'about', 'Notre societe', '', 'Noor Guide — Notre societe', 'Histoire, mission et valeurs de Noor Guide — Application mobile de guidage pour personnes aveugles et malvoyantes.', '<!-- Hero Section -->\n<section class=\"hero\" style=\"background-image: url(\'assets/images/noor-guide/about.jpg\');\">\n    <div class=\"container\">\n        <div class=\"hero-content reveal\">\n            <div class=\"hero-badge\">\n                <i class=\"fas fa-info-circle\"></i>\n                NOTRE SOCIETE\n            </div>\n            <h1>Guider avec <span class=\"highlight\">technologie</span> et <span class=\"highlight\">humanite</span></h1>\n            <p>Noor Guide est ne d\'une conviction simple : la mobilite independante est un droit pour tous. Nous construisons des solutions technologiques au service de l\'autonomie des personnes aveugles et malvoyantes.</p>\n            <div class=\"hero-actions\">\n                <a href=\"#notre-histoire\" class=\"btn btn-primary\">\n                    <i class=\"fas fa-book-open\"></i>\n                    Notre histoire\n                </a>\n                <a href=\"#notre-mission\" class=\"btn btn-outline\">\n                    <i class=\"fas fa-bullseye\"></i>\n                    Notre mission\n                </a>\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Notre Histoire -->\n<section id=\"notre-histoire\" class=\"section\">\n    <div class=\"container\">\n        <div class=\"solution-hero reveal\">\n            <div class=\"solution-hero-text\">\n                <div class=\"section-label\">\n                    <i class=\"fas fa-history\"></i>\n                    NOTRE HISTOIRE\n                </div>\n                <h2>Un projet ne de l\'experience</h2>\n                <p>Noor Guide est le fruit d\'un constat partage par desmilliers de personnes aveugles et malvoyantes au quotidien : se deplacer en toute autonomie reste un defi majeur, meme avec les outils existants.</p>\n                <p>Nos fondateurs, confrontes quotidiennement a ces realites, ont decide de creer une solution mobile complete qui va au-dela de la simple navigation. Noor Guide est une application concue pour etre la <strong>boite a outils de mobilite</strong> de chaque utilisateur.</p>\n                <p>Du premier prototype teste avec des utilisateurs realistes jusqu\'a la version actuelle, chaque fonctionnalite a ete co-construite avec la communaute cible.</p>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"assets/images/noor-guide/tech.jpg\" alt=\"Illustration accessibilite Noor Guide\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Notre Mission -->\n<section id=\"notre-mission\" class=\"section section-alt\">\n    <div class=\"container\">\n        <div class=\"solution-hero reverse reveal\">\n            <div class=\"solution-hero-text\">\n                <div class=\"section-label\">\n                    <i class=\"fas fa-bullseye\"></i>\n                    NOTRE MISSION\n                </div>\n                <h2>Rendre chaque deplacement possible</h2>\n                <p>Notre mission est de fournir aux personnes aveugles et malvoyantes les outils technologiques necessaires pour se deplacer en toute confiance, ou qu\'elles se trouvent.</p>\n                <p>Nous croyons que la technologie doit servir l\'humain. Chaque fonctionnalite de Noor Guide est concue pour reduire les barrieres de mobilite et offrir une experience de navigation intuitive, fiable et adaptative.</p>\n                <p>De l\'orientation GPS aux defis de mobilite en passant par la detection d\'obstacles, chaque outil est pense pour <strong>transformer chaque deplacement en une experience securisee</strong>.</p>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"assets/images/noor-guide/histoire.jpg\" alt=\"Illustration autonomie Noor Guide\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Nos Valeurs -->\n<section class=\"section\">\n    <div class=\"container\">\n        <div class=\"section-header reveal\">\n            <div class=\"section-label\">\n                <i class=\"fas fa-heart\"></i>\n                NOS VALEURS\n            </div>\n            <h2>Ce qui nous anime</h2>\n            <p>Les principes qui guident chaque decision chez Noor Guide.</p>\n        </div>\n        <div class=\"cards-grid\">\n            <div class=\"card reveal delay-1\">\n                <div class=\"card-icon\">\n                    <i class=\"fas fa-universal-access\"></i>\n                </div>\n                <h3>Accessibilite</h3>\n                <p>Tout le monde merite de se deplacer librement. Nous concevons pour l\'inclusion totale.</p>\n            </div>\n            <div class=\"card reveal delay-2\">\n                <div class=\"card-icon\">\n                    <i class=\"fas fa-users\"></i>\n                </div>\n                <h3>Communaute</h3>\n                <p>Nous developpons avec les utilisateurs, pas seulement pour eux. Leur feedback guide nos choix.</p>\n            </div>\n            <div class=\"card reveal delay-3\">\n                <div class=\"card-icon\">\n                    <i class=\"fas fa-lightbulb\"></i>\n                </div>\n                <h3>Innovation</h3>\n                <p>Nous repoussons les limites de ce que la technologie peut offrir pour la mobilite inclusive.</p>\n            </div>\n            <div class=\"card reveal delay-4\">\n                <div class=\"card-icon\">\n                    <i class=\"fas fa-shield-alt\"></i>\n                </div>\n                <h3>Confiance</h3>\n                <p>Fiabilite et securite au coeur de chaque fonctionnalite. Nos utilisables comptent sur nous.</p>\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Stats -->\n<section class=\"stats-section\">\n    <div class=\"container\">\n        <div class=\"stats-grid\">\n            <div class=\"stat-item reveal delay-1\">\n                <span class=\"stat-number\" data-count=\"10000\">0</span>\n                <span class=\"stat-label\">Utilisateurs actifs</span>\n            </div>\n            <div class=\"stat-item reveal delay-2\">\n                <span class=\"stat-number\" data-count=\"50\" data-suffix=\"+\">0</span>\n                <span class=\"stat-label\">Pays couverts</span>\n            </div>\n            <div class=\"stat-item reveal delay-3\">\n                <span class=\"stat-number\" data-count=\"24\" data-suffix=\"/7\">0</span>\n                <span class=\"stat-label\">Support accessible</span>\n            </div>\n            <div class=\"stat-item reveal delay-4\">\n                <span class=\"stat-number\" data-count=\"100\" data-suffix=\"%\">0</span>\n                <span class=\"stat-label\">Gratuit et sans pub</span>\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- CTA -->\n<section class=\"cta-section\">\n    <div class=\"container reveal\">\n        <h2>Rejoignez la communaute Noor Guide</h2>\n        <p>Telechargez l\'application et decouvrez une nouvelle facon de vous deplacer en toute autonomie.</p>\n        <div class=\"hero-actions\" style=\"justify-content: center;\">\n            <a href=\"#\" class=\"btn btn-primary\">\n                <i class=\"fab fa-apple\"></i>\n                App Store\n            </a>\n            <a href=\"#\" class=\"btn btn-outline\">\n                <i class=\"fab fa-google-play\"></i>\n                Google Play\n            </a>\n        </div>\n    </div>\n</section>', 'about', 'published', 'fr', '2026-05-23 10:29:22', '2026-07-23 13:13:33'),
(3, 'brands', '', '', 'maokaa - Nos marques', 'Liste des marques distribuées par maokaa', '\n                        \n                        \n                        \n                        \n                        \n                        \n                        \n                        \n                        \n                        \n                        <section class=\"brands-section fade-in\"><div class=\"container\"><div class=\"brands-header text-center mb-5\"><h2 class=\"display-5 fw-bold\">Nos marques&nbsp;</h2></div><div class=\"row align-items-center mb-5 brand-row reverse\"><div class=\"col-md-5 brand-logo-col text-center mb-4 mb-md-0\"><img class=\"img-fluid\" id=\"ifa3\" src=\"/assets/images/brands/brand_6a12f7616e4d1.png\" alt=\"Sartorius\"></div><div class=\"col-md-7 brand-content-col\"><h3 class=\"brand-name h4 fw-semibold\">Sartorius</h3><p class=\"brand-desc\"></p><p class=\"MsoNormal\"><span id=\"iv4ib\">Sartorius est un fabricant international de premier plan d\'équipements de laboratoire et de production pharmaceutique.</span></p><p class=\"MsoNormal\"><span id=\"itkez\">&nbsp;</span><span id=\"iy9pi\">La division Bioprocess Solutions</span><span id=\"iqpn5\">&nbsp; </span><span id=\"iohqc\">(Solutions de bioprocessus) couvre l\'ensemble de la chaîne de valeur de la production de médicaments biologiques et de vaccins. Ses technologies phares incluent le matériel de fermentation (bioréacteurs), les systèmes de filtration, la gestion des fluides et la purificatio</span>n.</p><p class=\"MsoNormal\"><span id=\"i1f7x\">&nbsp;</span><span id=\"iunug\">La division Lab Products &amp; Services (Produits et services de laboratoire) se concentre sur les instruments haut de gamme et les consommables pour les laboratoires de recherche et de contrôle qualité. Elle est particulièrement reconnue pour ses balances de précision (le produit historique de la marque), ses pipettes et ses systèmes de purification d\'eau.</span></p><p class=\"\"></p><div class=\"brand-meta mt-3\"><a class=\"btn btn-outline-primary btn-sm\" href=\"https://www.sartorius.com/en\" target=\"_blank\" rel=\"noopener\">Sartorius AG</a></div></div></div><div class=\"row align-items-center mb-5 brand-row\"><div class=\"col-md-5 brand-logo-col text-center mb-4 mb-md-0 order-md-2\"><img class=\"img-fluid\" src=\"/assets/images/KIMO.jpg\" alt=\"Kimo\" id=\"inssm\"></div><div class=\"col-md-7 brand-content-col order-md-1\"><h3 class=\"brand-name h4 fw-semibold\">Kimo</h3><p class=\"brand-desc\"></p><p class=\"MsoNormal\">Fabricant d\'instruments de mesure depuis 1979 KIMO\nInstruments est la référence incontournable dans la mesure, la détection,\nl’analyse et le contrôle des environnements sensibles et des systèmes\naérauliques. <o:p></o:p></p>\n\n<p class=\"MsoNormal\">Spécialiste de la mesure de pression, hygrométrie,\ntempérature, qualité de l\'air, la vitesse et le débit d\'air, KIMO Instruments\nconçoit des solutions d\'appareils de mesure portables, des enregistreurs\nautonomes (data loggers), et des capteurs fixes destinés à la surveillance des\nbâtiments tertiaires, des installations industrielles et des équipements\ntechniques : salles blanches et environnements contrôlés, zones de stockage et\nde production, systèmes CVCR (ventilation, climatisation, chauffage, réfrigération),\nprocédés industriels, contrôle qualité, analyse de combustion, etc.. <o:p></o:p></p>\n\n<p class=\"MsoNormal\">Grâce à son expertise reconnue et à la robustesse de ses\nproduits, KIMO Instruments accompagne les professionnels dans la maîtrise et la\nperformance des environnements techniques, y compris les plus exigeants.<o:p></o:p></p><p class=\"\"></p><div class=\"brand-meta mt-3\"><a class=\"btn btn-outline-primary btn-sm\" href=\"https://kimo-instruments.com/fr-FR?_gl=1*5edbrx*_gcl_au*MTc2Nzk0NjcyNS4xNzgxNDM3MjQ5*_ga*MTQyODE2OTYxOS4xNzUzMDkyMzQy*_ga_3774ME3MR0*czE3ODE0MzcyNTAkbzEzJGcwJHQxNzgxNDM3MjUwJGo2MCRsMCRoNTEyODAzMzgx*_ga_9P7KCXKNHB*czE3ODE0MzcyNTAkbzEzJGcwJHQxNzgxNDM3MjUwJGo2MCRsMCRoMA.\" target=\"_blank\" rel=\"noopener\">Kimo Instruments</a></div></div></div><div class=\"row align-items-center mb-5 brand-row reverse\"><div class=\"col-md-5 brand-logo-col text-center mb-4 mb-md-0\"><img class=\"img-fluid\" id=\"iwtls\" src=\"/assets/images/beckman.jpg\" alt=\"Beckman\"></div><div class=\"col-md-7 brand-content-col\"><h3 class=\"brand-name h4 fw-semibold\">Beckman</h3><p class=\"brand-desc\"></p><p class=\"MsoNormal\">Beckman Coulter est une entreprise mondiale de premier plan\nqui conçoit, fabrique et commercialise des instruments, des réactifs et des\nlogiciels pour les laboratoires de recherche et de biologie médicale.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Sa divisions Beckman Coulter Life Sciences, fournit des\nsolutions pour la recherche universitaire, pharmaceutique et biotechnologique.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Ces équipements phares depuis plusieurs décennies sont les compteurs\nde particules MET ONE pour l’air et HIAC pour les liquides, ils sont synonymes\nd’innovation, de fiabilité et de service aux clients.<o:p></o:p></p><p class=\"\"></p><div class=\"brand-meta mt-3\"><a class=\"btn btn-outline-primary btn-sm\" href=\"https://www.beckmancoulter.com/\" target=\"_blank\" rel=\"noopener\">Beckman Coulter</a></div></div></div><div class=\"row align-items-center mb-5 brand-row\"><div class=\"col-md-5 brand-logo-col text-center mb-4 mb-md-0 order-md-2\"><img class=\"img-fluid\" src=\"/assets/images/brands/brand_6a2a85337e94e.jpg\" alt=\"Minebea Intec\" id=\"il1c7\"></div><div class=\"col-md-7 brand-content-col order-md-1\"><h3 class=\"brand-name h4 fw-semibold\">Minebea Intec</h3><p class=\"brand-desc\"></p><p class=\"MsoNormal\"><span id=\"ip8a6\">Minebea Intec est un fabricant leader de technologies de pesage et d’inspection industrielles. Sa mission est d’accroître la fiabilité et l’efficacité des processus de production de ces clients. Pour ce faire, ils proposent des produits, solutions et services haut de gamme garantissant un niveau de sécurité élevé.</span></p><p class=\"\"></p><div class=\"brand-meta mt-3\"><a class=\"btn btn-outline-primary btn-sm\" href=\"https://www.minebea-intec.com/fr/\" target=\"_blank\" rel=\"noopener\">Minebea Intec</a></div></div></div></div></section>                                                                                                                                                                                                                            ', 'page', 'published', 'fr', '2026-05-23 10:29:22', '2026-06-18 08:33:46'),
(4, 'partners', 'Nos partenaires', '', 'maokaa - Partenaires', 'Partenaires et collaborations', '\n                        <section class=\"partners fade-in\"><h2 class=\"\">Partenaires</h2><div class=\"partner\"><h3 id=\"ij6k\" class=\"\">VES</h3><p class=\"desc\"><span style=\"font-size: 11pt; line-height: 107%; font-family: &quot;Segoe UI&quot;, sans-serif; background: white;\">Notre partenaire spécialisé dans l’étalonnage\ndes paramètres température / Humidité / Pression.</span></p><div class=\"contact\"><p class=\"phone\">+213 (0) 770 57 77 04</p><p id=\"iv9kw\" class=\"\"><a id=\"iwfr2\" href=\"mailto:contact@ves-tech.com\">contact@ves-tech.com</a></p></div></div><div class=\"partner\"><h3 class=\"\">NTC</h3><p class=\"desc\">Société spécialisée dans la maintenance et l’étalonnage des balances.\n    </p><div class=\"contact\"><p class=\"\"><br></p><p class=\"phone\">+213 (0) 770 90 56 31</p><p class=\"\">ntc_pesage@yahoo.fr</p></div></div><div class=\"partner\"><div class=\"contact\"></div></div><div class=\"row g-4\"></div></section>                    ', 'page', 'published', 'fr', '2026-05-23 10:29:22', '2026-06-17 10:01:19'),
(5, 'products', 'Produits', NULL, 'maokaa - Produits', 'Catalogue produits', '<div data-vep-block=\"products\" data-limit=\"256\" ></div>', 'products', 'published', 'fr', '2026-05-23 10:29:22', '2026-06-10 12:04:51'),
(6, 'news', 'Actualité', '', 'maokaa - Actualité', 'Nouveautés, événements et annonces', '[news limit=\"3\"]', 'news', 'published', 'fr', '2026-05-23 10:29:22', '2026-06-15 15:21:08'),
(7, 'contact', 'Contact', '', 'maokaa - Contact', 'Coordonnées et formulaire de contact', '\n                                                \n                        \n                        <div class=\"row g-4\"></div><div class=\"swiper\"><div class=\"swiper-wrapper\"></div><div class=\"swiper-button-next\"></div><div class=\"swiper-button-prev\"></div></div><div class=\"row g-4\"><div class=\"col-md-8\"><div class=\"p-4 h-100\">[contact_form]</div></div><div class=\"col-md-4\"><div class=\"p-4 bg-light rounded-3 h-100\"><h3 class=\"uk-panel-title\" id=\"iofvu\">Contact</h3><p class=\"text-muted mb-0\">SARL VALIDATION ENGINEERING PROCESS</p><p class=\"text-muted mb-0\">119, Coopérative Bois des Cars 3, Dely Brahim, Alger, Algérie&nbsp;</p><p class=\"text-muted mb-0\"><strong id=\"ixsvt\">Tel:</strong><span id=\"i22sw\"> +213 (0) 20 36 46 10</span><br id=\"igfvd\"><strong id=\"iiqcu\">Mob:</strong><span id=\"iz9f7\"> +213 (0) 550 663 520</span><br id=\"i0lpk\"><strong id=\"itspf\">E-mail:</strong><span id=\"ik9p5\"> </span><a id=\"iqntt\" href=\"mailto:contact@vep-dz.com\">contact@vep-dz.com</a></p></div></div></div>                                                                                ', 'page', 'published', 'fr', '2026-05-23 10:29:22', '2026-06-17 09:58:59'),
(8, 'login', 'Login Admin', '', 'maokaa - Login', 'Espace administration', '<!-- body HTML: coordonnées, google maps, formulaire -->', 'auth', 'draft', 'fr', '2026-05-23 10:29:22', '2026-05-25 16:24:52'),
(9, 'solutions', 'Nos solutions', NULL, 'Noor Guide — Nos solutions', 'Découvrez Noor Guide : application mobile de guidage pour personnes aveugles et malvoyantes.', '<!-- Hero -->\n<section class=\"section\" style=\"padding-bottom: 0;\">\n    <div class=\"container\">\n        <div class=\"solution-hero reveal\">\n            <div class=\"solution-hero-text\">\n                <div class=\"section-label\">\n                    <i class=\"fas fa-mobile-alt\"></i>\n                    APPLICATION DE GUIDAGE\n                </div>\n                <h1>Fluidifiez le parcours de vos visiteurs avec un handicap</h1>\n                <p>Vous pouvez mieux répondre à leurs besoins et faciliter leurs déplacements au sein de votre établissement. Notre solution Noor Guide leur offre plus de confort et allège leur charge mentale.</p>\n                <p>C\'est la première application de guidage capable de s\'adapter à tous les types de handicaps. Noor Guide est 100% inclusive.</p>\n                <div class=\"hero-actions\">\n                    <a href=\"#fonctionnement\" class=\"btn btn-primary\">\n                        <i class=\"fas fa-play-circle\"></i>\n                        Comment ça marche\n                    </a>\n                    <a href=\"#contact\" class=\"btn btn-outline\" style=\"color: var(--secondary); border-color: var(--secondary);\">\n                        <i class=\"fas fa-envelope\"></i>\n                        Contactez-nous\n                    </a>\n                </div>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"/maokaa_noor/maokaa/assets/images/noor-guide/tech.jpg\" alt=\"Application Noor Guide\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Confiance -->\n<section class=\"section section-alt\" style=\"padding: 3rem 0;\">\n    <div class=\"container\">\n        <p class=\"text-center fw-bold mb-4\" style=\"color: var(--text-light);\">Ils nous font confiance</p>\n        <div class=\"d-flex justify-content-center align-items-center gap-5 flex-wrap\" style=\"opacity: 0.5;\">\n            <span class=\"fw-bold fs-5\">Partner 1</span>\n            <span class=\"fw-bold fs-5\">Partner 2</span>\n            <span class=\"fw-bold fs-5\">Partner 3</span>\n            <span class=\"fw-bold fs-5\">Partner 4</span>\n        </div>\n    </div>\n</section>\n\n<!-- Comment s\'orienter -->\n<section id=\"fonctionnement\" class=\"section\">\n    <div class=\"container\">\n        <div class=\"solution-hero reveal\">\n            <div class=\"solution-hero-text\">\n                <h2>Comment s\'orienter en toute autonomie ?</h2>\n                <p>Noor Guide, c\'est comme un GPS mais pour s\'orienter au sein des bâtiments. Notre application a été spécialement conçue pour s\'adapter aux différents besoins des personnes ayant un handicap.</p>\n                <p>Ce qui la rend encore plus innovante, c\'est qu\'elle est utilisable par tous. Elle prend en compte les capacités de l\'utilisateur pour lui fournir des instructions et des trajets adaptés. C\'est une autonomie retrouvée pour les usagers avec un handicap.</p>\n                <p>Noor Guide est disponible gratuitement sur iOS et Android.</p>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"/maokaa_noor/maokaa/assets/images/noor-guide/histoire.jpg\" alt=\"Guidage Noor Guide\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- C\'est quoi Noor Guide -->\n<section class=\"section section-alt\">\n    <div class=\"container\">\n        <div class=\"solution-hero reverse reveal\">\n            <div class=\"solution-hero-text\">\n                <h2>C\'est quoi Noor Guide ?</h2>\n                <p>Grâce à notre solution de détection Bluetooth, notre application guide les utilisateurs avec précision. Une véritable innovation technologique et d\'usage pour les aider à se repérer et s\'orienter en toute sérénité.</p>\n                <p>Ils ont accès à tous les services que vous proposez au sein de votre établissement. C\'est leur compagnon idéal pour tous leurs déplacements à l\'intérieur de votre lieu.</p>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"/maokaa_noor/maokaa/assets/images/noor-guide/tech.jpg\" alt=\"Application de guidage\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Intérieur et extérieur -->\n<section class=\"section\">\n    <div class=\"container\">\n        <div class=\"solution-hero reveal\">\n            <div class=\"solution-hero-text\">\n                <div class=\"section-label\">\n                    <i class=\"fas fa-building\"></i>\n                    INTÉRIEUR ET EXTÉRIEUR\n                </div>\n                <h2>Une application de guidage en intérieur et en extérieur !</h2>\n                <p>Noor Guide répond aux principaux besoins des personnes en situation de handicap :</p>\n                <ul style=\"list-style: none; padding: 0; margin-top: 1.5rem;\">\n                    <li style=\"padding: 0.6rem 0; padding-left: 2rem; position: relative; color: var(--text-light);\">\n                        <i class=\"fas fa-check-circle\" style=\"position: absolute; left: 0; color: var(--primary); top: 0.8rem;\"></i>\n                        Être guidé pas à pas dans des lieux complexes à la manière d\'un GPS piéton\n                    </li>\n                    <li style=\"padding: 0.6rem 0; padding-left: 2rem; position: relative; color: var(--text-light);\">\n                        <i class=\"fas fa-check-circle\" style=\"position: absolute; left: 0; color: var(--primary); top: 0.8rem;\"></i>\n                        Gagner en autonomie et en spontanéité\n                    </li>\n                    <li style=\"padding: 0.6rem 0; padding-left: 2rem; position: relative; color: var(--text-light);\">\n                        <i class=\"fas fa-check-circle\" style=\"position: absolute; left: 0; color: var(--primary); top: 0.8rem;\"></i>\n                        Découvrir les points d\'intérêts (POI)\n                    </li>\n                    <li style=\"padding: 0.6rem 0; padding-left: 2rem; position: relative; color: var(--text-light);\">\n                        <i class=\"fas fa-check-circle\" style=\"position: absolute; left: 0; color: var(--primary); top: 0.8rem;\"></i>\n                        Se rendre d\'un point à un autre en toute sécurité\n                    </li>\n                    <li style=\"padding: 0.6rem 0; padding-left: 2rem; position: relative; color: var(--text-light);\">\n                        <i class=\"fas fa-check-circle\" style=\"position: absolute; left: 0; color: var(--primary); top: 0.8rem;\"></i>\n                        Bénéficier d\'un guidage et d\'une interface adaptés en fonction de ses besoins\n                    </li>\n                    <li style=\"padding: 0.6rem 0; padding-left: 2rem; position: relative; color: var(--text-light);\">\n                        <i class=\"fas fa-check-circle\" style=\"position: absolute; left: 0; color: var(--primary); top: 0.8rem;\"></i>\n                        Accéder à l\'intégralité de l\'offre de services du lieu\n                    </li>\n                </ul>\n                <p style=\"margin-top: 1.5rem; font-style: italic;\">Notre volonté a été de créer une application permettant à toutes ces personnes d\'être guidées, pas à pas, jusqu\'à la destination de leur choix, en fonction de leurs profils et de leurs capacités, dans l\'objectif de leur rendre leur autonomie.</p>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"/maokaa_noor/maokaa/assets/images/noor-guide/about.jpg\" alt=\"Guidage intérieur extérieur\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Caractéristiques -->\n<section class=\"section section-alt\">\n    <div class=\"container\">\n        <div class=\"section-header reveal\">\n            <div class=\"section-label\">\n                <i class=\"fas fa-star\"></i>\n                CARACTÉRISTIQUES\n            </div>\n            <h2>Choisissez une solution personnalisable</h2>\n            <p>Les utilisateurs n\'ont qu\'à renseigner le type de parcours le mieux adapté à leurs besoins (choix d\'utiliser les ascenseurs ou les escaliers…).</p>\n            <p>Les interfaces et le guidage s\'accordent en fonction des préférences des usagers (langage facile à lire et à comprendre, cartographie, instructions sonores…).</p>\n            <p>Tout pour que vos visiteurs puissent être guidés, rester maîtres de leurs déplacements et profiter de votre lieu.</p>\n        </div>\n        <div class=\"row g-4\">\n            <div class=\"col-md-4 reveal delay-1\">\n                <div class=\"card text-center h-100 border-0 shadow-sm\">\n                    <div class=\"card-body p-4\">\n                        <div class=\"d-inline-flex align-items-center justify-content-center rounded-circle mb-3\" style=\"width: 60px; height: 60px; background: rgba(255,107,0,0.1); color: var(--primary); font-size: 1.5rem; font-weight: 800;\">1</div>\n                        <h5 class=\"card-title fw-bold\">Personnalisation complète</h5>\n                        <p class=\"card-text text-muted\">Solution personnalisée et personnalisable qui s\'adapte aux besoins de chaque utilisateur.</p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-4 reveal delay-2\">\n                <div class=\"card text-center h-100 border-0 shadow-sm\">\n                    <div class=\"card-body p-4\">\n                        <div class=\"d-inline-flex align-items-center justify-content-center rounded-circle mb-3\" style=\"width: 60px; height: 60px; background: rgba(255,107,0,0.1); color: var(--primary); font-size: 1.5rem; font-weight: 800;\">2</div>\n                        <h5 class=\"card-title fw-bold\">Respect de la vie privée</h5>\n                        <p class=\"card-text text-muted\">Fort engagement en matière de « Privacy by Design » et de minimisation des données.</p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-4 reveal delay-3\">\n                <div class=\"card text-center h-100 border-0 shadow-sm\">\n                    <div class=\"card-body p-4\">\n                        <div class=\"d-inline-flex align-items-center justify-content-center rounded-circle mb-3\" style=\"width: 60px; height: 60px; background: rgba(255,107,0,0.1); color: var(--primary); font-size: 1.5rem; font-weight: 800;\">3</div>\n                        <h5 class=\"card-title fw-bold\">Installation simple</h5>\n                        <p class=\"card-text text-muted\">Installation simple et discrète pour conserver l\'esthétique de votre lieu.</p>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- Autonomie -->\n<section class=\"section section-dark\">\n    <div class=\"container\">\n        <div class=\"solution-hero reveal\">\n            <div class=\"solution-hero-text\">\n                <div class=\"section-label\" style=\"background: rgba(255,255,255,0.1); color: #fff;\">\n                    <i class=\"fas fa-heart\"></i>\n                    AUTONOMIE\n                </div>\n                <h2 style=\"color: #fff;\">Nous impactons la société positivement au quotidien.</h2>\n                <p>Notre objectif est de permettre aux personnes aveugles et malvoyantes de se déplacer avec davantage d\'autonomie, de confiance et de sérénité dans les espaces équipés de la solution Noor Guide.</p>\n                <div class=\"hero-actions\" style=\"margin-top: 2rem;\">\n                    <a href=\"#contact\" class=\"btn btn-primary\">\n                        <i class=\"fas fa-envelope\"></i>\n                        Contactez-nous\n                    </a>\n                    <a href=\"#\" class=\"btn btn-outline\">\n                        <i class=\"fas fa-info-circle\"></i>\n                        En savoir plus\n                    </a>\n                </div>\n            </div>\n            <div class=\"solution-image-placeholder\">\n                <img src=\"/maokaa_noor/maokaa/assets/images/noor-guide/tech.jpg\" alt=\"Autonomie Noor Guide\">\n            </div>\n        </div>\n    </div>\n</section>\n\n<!-- CTA -->\n<section id=\"contact\" class=\"cta-section\">\n    <div class=\"container reveal\">\n        <h2>Prêt à équiper votre établissement ?</h2>\n        <p>Contactez-nous pour découvrir comment Noor Guide peut améliorer l\'expérience de vos visiteurs.</p>\n        <div class=\"hero-actions\" style=\"justify-content: center;\">\n            <a href=\"#\" class=\"btn btn-primary\">\n                <i class=\"fas fa-envelope\"></i>\n                Contactez-nous\n            </a>\n            <a href=\"#\" class=\"btn btn-outline\">\n                <i class=\"fas fa-phone\"></i>\n                Appelez-nous\n            </a>\n        </div>\n    </div>\n</section>', 'solutions', 'published', 'fr', '2026-07-23 13:30:11', '2026-07-26 09:12:23');

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `attempted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `marques`
--

CREATE TABLE `marques` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menus`
--

INSERT INTO `menus` (`id`, `name`, `label`, `description`, `created_at`, `updated_at`) VALUES
(1, 'main', 'Menu Principal', 'Menu de navigation principal du site', '2026-05-25 09:32:09', '2026-05-25 09:32:09');

-- --------------------------------------------------------

--
-- Structure de la table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `title`, `url`, `icon`, `position`, `active`, `params`, `created_at`, `updated_at`) VALUES
(8, 1, NULL, 'Notre société', '/about', NULL, 1, 1, '{\"icon\":0}', '2026-07-22 11:29:55', '2026-07-23 13:18:08'),
(10, 1, NULL, 'Nos solutions', '/solutions', NULL, 2, 1, '{\"icon\":0}', '2026-07-22 11:29:55', '2026-07-23 13:18:22'),
(11, 1, NULL, 'Contacts', '/contact', NULL, 3, 1, '{\"icon\":0}', '2026-07-22 11:29:55', '2026-07-23 13:18:41'),
(12, 1, NULL, 'Acceuil', '/', NULL, 0, 1, '{\"icon\":1}', '2026-07-23 13:16:16', '2026-07-23 13:28:19');

-- --------------------------------------------------------

--
-- Structure de la table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('core','annex','addon') NOT NULL DEFAULT 'addon',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `depends_on` varchar(255) DEFAULT NULL,
  `installed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `modules`
--

INSERT INTO `modules` (`id`, `slug`, `name`, `type`, `enabled`, `depends_on`, `installed_at`) VALUES
(1, 'pages', 'Pages CMS', 'core', 1, NULL, '2026-07-26 13:58:28'),
(2, 'media', 'Médias', 'annex', 1, 'pages', '2026-07-26 13:58:28'),
(3, 'menus', 'Menus', 'annex', 1, 'pages', '2026-07-26 13:58:28'),
(4, 'messages', 'Messages', 'annex', 1, 'pages', '2026-07-26 13:58:28'),
(5, 'sliders', 'Sliders', 'annex', 1, 'pages', '2026-07-26 13:58:28'),
(6, 'products', 'Produits', 'addon', 1, 'pages,media', '2026-07-26 13:58:28'),
(7, 'brands', 'Marques', 'addon', 0, 'pages,media', '2026-07-26 13:58:28'),
(8, 'partners', 'Partenaires', 'addon', 0, 'pages,media', '2026-07-26 13:58:28'),
(9, 'news', 'Actualités', 'addon', 0, 'pages,media', '2026-07-26 13:58:28');

-- --------------------------------------------------------

--
-- Structure de la table `partenaires`
--

CREATE TABLE `partenaires` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `description_complete` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `brochure_pdf` varchar(255) DEFAULT NULL,
  `categorie_id` int(11) NOT NULL,
  `marque_id` int(11) DEFAULT NULL,
  `lettre_alphabet` char(1) DEFAULT NULL,
  `caracteristiques_techniques` longtext DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  `featured` tinyint(4) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit_images`
--

CREATE TABLE `produit_images` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'active_theme', 'welcome', '2026-10-14 18:58:48'),
(2, 'footer_phone', '+213 21 22 33', '2026-06-14 16:54:08'),
(4, 'footer_email', ' contact@vep-dz.com', '2026-06-14 16:54:21');

-- --------------------------------------------------------

--
-- Structure de la table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `slider_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `bg` varchar(100) NOT NULL DEFAULT '#dde4ee',
  `image` varchar(500) DEFAULT NULL,
  `text_position` varchar(20) NOT NULL DEFAULT 'center',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sliders`
--

INSERT INTO `sliders` (`id`, `slider_id`, `label`, `subtitle`, `bg`, `image`, `text_position`, `sort_order`, `active`, `created_at`) VALUES
(7, 1, 'Votre autonomie, notre mission', 'Noor Guide vous accompagne dans vos déplacements grâce à la navigation intelligente et la détection Bluetooth.', '#1A1A2E', NULL, 'center-left', 1, 1, '2026-07-22 12:54:06'),
(8, 1, 'Naviguez en toute sérénité', 'Choisissez votre destination et laissez Noor Guide vous guider pas à pas, en toute sécurité.', '#0F0F1A', NULL, 'center', 2, 1, '2026-07-22 12:54:06'),
(9, 1, 'Guidage intelligent, Bluetooth connecté', 'L\'application détecte les dispositifs de guidage à proximité et vous fournit des instructions vocales précises.', '#FF6B00', NULL, 'center-right', 3, 1, '2026-07-22 12:54:06');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `actualites`
--
ALTER TABLE `actualites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `published_at` (`published_at`),
  ADD KEY `status` (`status`);

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `display_order` (`display_order`);

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lu` (`lu`),
  ADD KEY `created_at` (`created_at`);

--
-- Index pour la table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `slug_2` (`slug`),
  ADD KEY `status` (`status`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip`);

--
-- Index pour la table `marques`
--
ALTER TABLE `marques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `display_order` (`display_order`);

--
-- Index pour la table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_menu_position` (`menu_id`,`position`);

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `partenaires`
--
ALTER TABLE `partenaires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `display_order` (`display_order`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `marque_id` (`marque_id`),
  ADD KEY `lettre_alphabet` (`lettre_alphabet`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `featured` (`featured`),
  ADD KEY `active` (`active`);

--
-- Index pour la table `produit_images`
--
ALTER TABLE `produit_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `display_order` (`display_order`);

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Index pour la table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slider_id` (`slider_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `actualites`
--
ALTER TABLE `actualites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `content`
--
ALTER TABLE `content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `marques`
--
ALTER TABLE `marques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `partenaires`
--
ALTER TABLE `partenaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produit_images`
--
ALTER TABLE `produit_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `actualites`
--
ALTER TABLE `actualites`
  ADD CONSTRAINT `actualites_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produits_ibfk_2` FOREIGN KEY (`marque_id`) REFERENCES `marques` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `produit_images`
--
ALTER TABLE `produit_images`
  ADD CONSTRAINT `produit_images_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

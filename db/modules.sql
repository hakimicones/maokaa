-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 27 juil. 2026 à 14:39
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `maokaa`
--

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

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

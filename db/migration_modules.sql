-- Migration: Table des modules
-- Ce script est idempotent (peut être exécuté plusieurs fois sans erreur)

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('core','annex','addon') NOT NULL DEFAULT 'addon',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `depends_on` varchar(255) DEFAULT NULL,
  `installed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed: inserer les modules (IGNORE si déjà présents)
INSERT IGNORE INTO `modules` (`slug`, `name`, `type`, `enabled`, `depends_on`) VALUES
('pages',     'Pages CMS',       'core',  1, NULL),
('media',     'Médias',          'annex', 1, 'pages'),
('menus',     'Menus',           'annex', 1, 'pages'),
('messages',  'Messages',        'annex', 1, 'pages'),
('sliders',   'Sliders',         'annex', 1, 'pages'),
('products',  'Produits',        'addon', 1, 'pages,media'),
('brands',    'Marques',         'addon', 1, 'pages,media'),
('partners',  'Partenaires',     'addon', 1, 'pages,media'),
('news',      'Actualités',      'addon', 1, 'pages,media');

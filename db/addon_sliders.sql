-- =============================================================
-- Addon: sliders — Carrousels et slides
-- =============================================================

CREATE TABLE IF NOT EXISTS `sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slider_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `subtitle` varchar(500) DEFAULT NULL,
  `bg` varchar(100) NOT NULL DEFAULT '#dde4ee',
  `image` varchar(500) DEFAULT NULL,
  `text_position` varchar(20) NOT NULL DEFAULT 'center',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_slider_id` (`slider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

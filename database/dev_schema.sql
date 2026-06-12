-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 12, 2026 at 07:23 PM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `culture-association-website-template`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorised_editors`
--

CREATE TABLE `authorised_editors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `can_manage_editors` tinyint(1) NOT NULL DEFAULT '0',
  `otp_code` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `authorised_editors`
--

INSERT INTO `authorised_editors` (`id`, `name`, `email`, `can_manage_editors`, `otp_code`, `otp_expires_at`, `created_at`) VALUES
(1, 'Iliana', 'ilianamarquezm@gmail.com', 1, NULL, NULL, '2026-06-10 05:53:55');

-- --------------------------------------------------------

--
-- Table structure for table `contributors`
--

CREATE TABLE `contributors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `contributors_assignments`
--

CREATE TABLE `contributors_assignments` (
  `id` int(11) NOT NULL,
  `contributor_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `entity_media`
--

CREATE TABLE `entity_media` (
  `media_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `entity_media`
--

INSERT INTO `entity_media` (`media_id`, `entity_type`, `entity_id`) VALUES
(1, 'event', 6);

-- --------------------------------------------------------

--
-- Table structure for table `entity_urls`
--

CREATE TABLE `entity_urls` (
  `url_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `entity_urls`
--

INSERT INTO `entity_urls` (`url_id`, `entity_type`, `entity_id`) VALUES
(1, 'organisation', 1),
(2, 'organisation', 1),
(3, 'organisation', 1),
(4, 'organisation', 1),
(5, 'team', 1),
(6, 'team', 1);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL,
  `review` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `project_id`, `category_id`, `title`, `subtitle`, `description`, `date`, `time`, `venue_id`, `review`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 'Wortwitz & Saitenspiel', 'Literatur trifft Zitherklang', 'Ein Abend mit Wiener Charme und Feinsinn. Robert Kolar liest Polgar, Ringelnatz und Busch. Barbara Laister-Ebner spielt Zitherwerke von Tiersen bis Lennon. Eintritt frei (Spenden willkommen).', '2026-03-05', '18:00:00', 1, 'Das war das Motto der KLA-Veranstaltung, die am 5. März im gut besuchten Ferstlsaal des Hotels Regina über die Bühne ging. Vor begeistertem Publikum las Robert Kolar aus Werken von Polgar, Ringelnatz und Busch, untermalt von Barbara Laister-Ebner, die Zitherwerke von Tiersen bis Lennon zum Besten gab. KLA-Vizepräsidentin Margret Popper-Appel und Hausherr Wolfgang Kremslehner freuten sich über den gelungenen Abend.', '2026-06-12 17:45:32', '2026-06-12 18:30:47'),
(2, NULL, 2, 'Das geheime Requiem \"Mozart für Vier\"', 'Das Mozart Requiem in d-Moll KV 626 in einer Fassung für Streichquartett', 'In dieser selten gespielten Fassung für Streichquartett von Peter Lichtenthal wird das Werk zu einem intimen musikalischen Gebet. Eintritt frei - Spenden willkommen!', '2025-12-07', '15:00:00', 2, 'Ein außergewöhnliches Konzerterlebnis in der Krypta der Canisiuskirche.', '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(3, NULL, 3, 'Barock mit Freymut!', 'Musik als europäische Sprache', 'Herzliche Einladung zum Barockkonzert mit der Wiener Musikgruppe Ensemble Freymut. Eintritt frei!', '2025-11-19', '18:00:00', 3, NULL, '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(4, NULL, 2, 'Schuberts Winterreise', 'Schuberts \"Winterreise\" als Dialog der Generationen', 'Der 17-jährige Anton Guberov und sein Vater Ivaylo Guberov widmen sich gemeinsam Schuberts großem Liederzyklus. Eintritt frei - Spenden willkommen!', '2025-11-21', '18:00:00', 3, NULL, '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(5, NULL, 4, 'Karl Kraus: „Die letzten Tage der Menschheit\"', 'Szenische Lesung in Wien', 'Erleben Sie eine eindrucksvolle Lesung mit dem Comedia Bruckmühle Ensemble - erstmals in Wien! Eintritt frei | Spenden willkommen.', '2025-10-10', '18:00:00', 4, 'Ein intensiver Theaterabend unter der Leitung von Richard Maynau.', '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(6, NULL, 1, 'Kultur Klub Alsergrund im Salon Schönherr', 'Bitte vormerken!', 'Kultur Klub Alsergrund lädt in den Salon Schönherr Holde Naumann. Eintritt frei - Spenden willkommen!', '2025-09-26', '18:00:00', 5, NULL, '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(7, NULL, 3, 'Barockfestival Steyr 2025 - Eröffnungskonzert', 'Der Zauber Alter Musik trifft auf jugendliche Spielfreude', 'Eröffnungskonzert des erstmalig stattfindenden Barockfestivals Steyr. Barockensemble der Wiener Symphoniker unter Dirigent Prof. Christian Birnbaum.', '2025-05-16', '19:30:00', 6, NULL, '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(8, NULL, 3, 'Barockfestival Steyr 2025 - Vivaldi: Die Vier Jahreszeiten', 'Jugendliche Spielfreude im Alten Theater', 'Das Jugend Sinfonie Orchester Tulln gibt die \"Vier Jahreszeiten\" von Antonio Vivaldi zum Besten.', '2025-05-17', '19:30:00', 6, NULL, '2026-06-12 17:45:32', '2026-06-12 17:45:32'),
(9, NULL, 5, 'Kinderoper: \"Honigkuchen für Zerberus\"', 'Kinderoper-Highlight beim Barockfestival Steyr!', 'In \"Honigkuchen für Zerberus\" wird Feos Kuscheltier entführt - mitten in die Unterwelt! Inspiriert von Glucks Orpheus & Euridike. Tickets ab 9 €.', '2022-05-18', '15:00:00', 6, NULL, '2026-06-12 17:45:32', '2026-06-12 19:03:42');

-- --------------------------------------------------------

--
-- Table structure for table `event_categories`
--

CREATE TABLE `event_categories` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `event_categories`
--

INSERT INTO `event_categories` (`id`, `label`, `created_at`) VALUES
(1, 'Literatur & Musik', '2026-06-12 17:34:55'),
(2, 'Klassische Konzerte', '2026-06-12 17:34:55'),
(3, 'Barockmusik', '2026-06-12 17:34:55'),
(4, 'Theater & Lesung', '2026-06-12 17:34:55'),
(5, 'Kinderoper', '2026-06-12 17:34:55');

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `event_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `event_participants`
--

INSERT INTO `event_participants` (`event_id`, `participant_id`) VALUES
(1, 1),
(1, 2),
(2, 3),
(4, 4),
(4, 5),
(3, 6),
(5, 7),
(7, 8),
(8, 9);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `order_index` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `media_url`, `caption`, `stage`, `order_index`, `created_at`) VALUES
(1, 'https://files.websitebuilder.easyname.com/15/0e/150ef47b-6f88-467e-a21a-469fdf944db2.png', 'Salon Schönherr', 'promo', 0, '2026-06-12 18:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `organisation_info`
--

CREATE TABLE `organisation_info` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `description` text,
  `organisation_type` varchar(100) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `statutes_url` varchar(255) DEFAULT NULL,
  `schema_type` varchar(100) NOT NULL DEFAULT 'Organization',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `organisation_info`
--

INSERT INTO `organisation_info` (`id`, `name`, `tagline`, `description`, `organisation_type`, `logo_url`, `street`, `postcode`, `city`, `country`, `registration_number`, `email`, `phone`, `statutes_url`, `schema_type`, `updated_at`) VALUES
(1, 'Kultur Klub Alsergrund', '42 Jahre Kultur für Alle.', 'Ziel des Vereins Kultur Klub Alsergrund ist es, den Menschen des 9. Bezirks ein qualitativ hochwertiges kulturelles Angebot zu bieten und durch interessante Eigenproduktionen auch auswärtiges Publikum in den Bezirk zu bringen. Künstlerische Talente sollen gefördert und Kunst und Kultur allen Menschen zugänglich gemacht werden – insbesondere auch finanziell benachteiligten Mitbürgerinnen und Mitbürgern.', 'Verein', 'https://res.cloudinary.com/dj2lk9daf/image/upload/v1780957075/kla-logo_mjwbyn.png', 'Währinger Gürtel 110', '1090', 'Wien', 'Austria', '469481450', 'kla@klubalsergrund.at', NULL, NULL, 'NGO', '2026-06-10 19:28:13');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `page_key` varchar(50) NOT NULL,
  `section_key` varchar(50) NOT NULL,
  `type_key` varchar(50) NOT NULL DEFAULT 'section',
  `order_index` int(11) NOT NULL DEFAULT '0',
  `content` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `page_key`, `section_key`, `type_key`, `order_index`, `content`, `created_at`, `updated_at`) VALUES
(1, 'home', 'intro', 'section', 1, '{\"cta\": {\"url\": \"/ueber-uns\", \"label\": \"Mehr erfahren\"}, \"text\": \"Ein unabhängiger Kulturverein im 9. Bezirk Wien.\", \"align\": \"center\", \"image\": null, \"theme\": \"light\", \"title\": \"Über uns\", \"layout\": \"50-50\", \"bg_image\": \"https://images.pexels.com/photos/4861059/pexels-photo-4861059.jpeg\", \"image_pos\": \"none\"}', '2026-06-10 04:59:56', '2026-06-12 18:28:07'),
(3, 'home', 'hero', 'hero', 0, '{\"theme\": \"dark\"}', '2026-06-11 19:21:32', '2026-06-11 19:21:32');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `field` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_credit` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `type`, `title`, `first_name`, `last_name`, `category_id`, `field`, `image`, `image_credit`, `created_at`, `updated_at`) VALUES
(1, 'individual', NULL, 'Robert', 'Kolar', 1, 'Rezitator', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(2, 'individual', NULL, 'Barbara', 'Laister-Ebner', 1, 'Zither', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(3, 'group', NULL, 'Ensemble Lichtenthal', NULL, 2, 'Streichquartett', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(4, 'individual', NULL, 'Anton', 'Guberov', 1, 'Bariton', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(5, 'individual', NULL, 'Ivaylo', 'Guberov', 1, 'Klavier', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(6, 'group', NULL, 'Ensemble Freymut', NULL, 2, 'Barockmusik', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(7, 'group', NULL, 'Comedia Bruckmühle Ensemble', NULL, 4, 'Theater', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(8, 'group', NULL, 'Barockensemble der Wiener Symphoniker', NULL, 3, 'Barockmusik', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(9, 'group', NULL, 'Jugend Sinfonie Orchester Tulln', NULL, 3, 'Sinfonik', NULL, NULL, '2026-06-12 16:52:32', '2026-06-12 16:52:32'),
(10, 'individual', NULL, 'Robert', 'Kolar', 1, 'Rezitator', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(11, 'individual', NULL, 'Barbara', 'Laister-Ebner', 1, 'Zither', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(12, 'group', NULL, 'Ensemble Lichtenthal', NULL, 2, 'Streichquartett', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(13, 'individual', NULL, 'Anton', 'Guberov', 1, 'Bariton', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(14, 'individual', NULL, 'Ivaylo', 'Guberov', 1, 'Klavier', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(15, 'group', NULL, 'Ensemble Freymut', NULL, 2, 'Barockmusik', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(16, 'group', NULL, 'Comedia Bruckmühle Ensemble', NULL, 4, 'Theater', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(17, 'group', NULL, 'Barockensemble der Wiener Symphoniker', NULL, 3, 'Barockmusik', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(18, 'group', NULL, 'Jugend Sinfonie Orchester Tulln', NULL, 3, 'Sinfonik', NULL, NULL, '2026-06-12 17:34:55', '2026-06-12 17:34:55');

-- --------------------------------------------------------

--
-- Table structure for table `participants_categories`
--

CREATE TABLE `participants_categories` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `participants_categories`
--

INSERT INTO `participants_categories` (`id`, `label`, `created_at`) VALUES
(1, 'Solist:in', '2026-06-12 16:52:32'),
(2, 'Ensemble', '2026-06-12 16:52:32'),
(3, 'Orchester', '2026-06-12 16:52:32'),
(4, 'Theaterensemble', '2026-06-12 16:52:32'),
(5, 'Solist:in', '2026-06-12 17:34:55'),
(6, 'Ensemble', '2026-06-12 17:34:55'),
(7, 'Orchester', '2026-06-12 17:34:55'),
(8, 'Theaterensemble', '2026-06-12 17:34:55');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text,
  `venue_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `section_types`
--

CREATE TABLE `section_types` (
  `type_key` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `fields_schema` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `section_types`
--

INSERT INTO `section_types` (`type_key`, `label`, `fields_schema`) VALUES
('cta_block', 'Call to Action', '[\"title\", \"text\", \"buttons\"]'),
('hero', '', NULL),
('section', 'Free Section', NULL),
('text_block', 'Text Block', '[\"title\", \"subtitle\", \"text\", \"media_urls\", \"buttons\"]');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `role` varchar(150) NOT NULL,
  `profession` varchar(150) DEFAULT NULL,
  `motto` text,
  `biography` text,
  `image` varchar(255) DEFAULT NULL,
  `image_credits` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `first_name`, `last_name`, `title`, `role`, `profession`, `motto`, `biography`, `image`, `image_credits`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Monica', 'Guillen Chavez', NULL, 'Präsidentin', 'Sängerin', 'Kultur verbindet Menschen.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.\n\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Curabitur pretium tincidunt lacus, sed consequat mauris tristique non. Integer feugiat, sapien at interdum efficitur, nunc risus malesuada nisl, vitae faucibus augue justo sit amet lorem. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae.\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.\n\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Curabitur pretium tincidunt lacus, sed consequat mauris tristique non. Integer feugiat, sapien at interdum efficitur, nunc risus malesuada nisl, vitae faucibus augue justo sit amet lorem. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae.', 'https://images.pexels.com/photos/819105/pexels-photo-819105.jpeg', 'Sophie Lecrerc', '2026-06-11 22:38:18', '2026-06-12 04:29:20', NULL),
(2, 'Anna', 'Müller', 'Mag.', 'Kassier', 'Kulturmanagerin', NULL, NULL, NULL, NULL, '2026-06-11 22:38:18', '2026-06-11 22:38:18', NULL),
(3, 'Thomas', 'Weber', NULL, 'Schriftführer', 'Journalist', NULL, NULL, NULL, NULL, '2026-06-11 22:38:18', '2026-06-11 22:38:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `urls`
--

CREATE TABLE `urls` (
  `id` int(11) NOT NULL,
  `url_type_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `label` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `urls`
--

INSERT INTO `urls` (`id`, `url_type_id`, `url`, `label`) VALUES
(1, 3, 'https://www.instagram.com/klubalsergrund.kv', NULL),
(2, 2, 'https://www.facebook.com/KulturKlubAlsergrund', NULL),
(3, 5, 'https://www.youtube.com/channel/UCmC2H7dQSieUo3ekMTQyLaA', NULL),
(4, 1, 'https://www.klubalsergrund.at/', NULL),
(5, 1, 'https://ilianamarquez.com', NULL),
(6, 3, 'https://www.instagram.com/ili.marquez/', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `url_types`
--

CREATE TABLE `url_types` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `url_types`
--

INSERT INTO `url_types` (`id`, `label`, `icon`) VALUES
(1, 'Website', 'ti-world'),
(2, 'Facebook', 'ti-brand-facebook'),
(3, 'Instagram', 'ti-brand-instagram'),
(4, 'LinkedIn', 'ti-brand-linkedin'),
(5, 'YouTube', 'ti-brand-youtube'),
(6, 'Spotify', 'ti-brand-spotify'),
(7, 'SoundCloud', 'ti-brand-soundcloud'),
(8, 'Vimeo', 'ti-brand-vimeo'),
(9, 'Bandcamp', 'ti-brand-bandcamp'),
(10, 'Email', 'ti-mail'),
(11, 'Press', 'ti-news'),
(12, 'Radio', 'ti-radio'),
(13, 'TV', 'ti-device-tv'),
(14, 'Maps', 'ti-map-pin'),
(15, 'Other', 'ti-link');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `street`, `postcode`, `city`, `country`, `created_at`, `updated_at`) VALUES
(1, 'Hotel Regina, Salon „Alt Wien\"', 'Rooseveltplatz 15', '1090', 'Wien', 'Austria', '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(2, 'Krypta der Canisiuskirche', 'Pulverturmgasse 11', '1090', 'Wien', 'Austria', '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(3, 'Bundesgymnasium Wien 9, Festsaal', 'Wasagasse 10', '1090', 'Wien', 'Austria', '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(4, 'Historische Sammlung des NHM Wien im Narrenturm', 'Spitalgasse 2', '1090', 'Wien', 'Austria', '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(5, 'Salon Schönherr Holde Naumann', 'Severingasse 5/7', '1090', 'Wien', 'Austria', '2026-06-12 17:34:55', '2026-06-12 17:34:55'),
(6, 'Altes Theater', 'Handel-Mazzetti-Promenade 3', '4400', 'Steyr', 'Austria', '2026-06-12 17:34:55', '2026-06-12 17:34:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authorised_editors`
--
ALTER TABLE `authorised_editors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `contributors`
--
ALTER TABLE `contributors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contributors_assignments`
--
ALTER TABLE `contributors_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contributor_id` (`contributor_id`);

--
-- Indexes for table `entity_media`
--
ALTER TABLE `entity_media`
  ADD PRIMARY KEY (`media_id`,`entity_type`,`entity_id`);

--
-- Indexes for table `entity_urls`
--
ALTER TABLE `entity_urls`
  ADD PRIMARY KEY (`url_id`,`entity_type`,`entity_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `event_categories`
--
ALTER TABLE `event_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`event_id`,`participant_id`),
  ADD KEY `participant_id` (`participant_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_media_url` (`media_url`);

--
-- Indexes for table `organisation_info`
--
ALTER TABLE `organisation_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_key` (`type_key`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participants_ibfk_1` (`category_id`);

--
-- Indexes for table `participants_categories`
--
ALTER TABLE `participants_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `section_types`
--
ALTER TABLE `section_types`
  ADD PRIMARY KEY (`type_key`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `urls`
--
ALTER TABLE `urls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_url` (`url`),
  ADD KEY `url_type_id` (`url_type_id`);

--
-- Indexes for table `url_types`
--
ALTER TABLE `url_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authorised_editors`
--
ALTER TABLE `authorised_editors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contributors`
--
ALTER TABLE `contributors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contributors_assignments`
--
ALTER TABLE `contributors_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_categories`
--
ALTER TABLE `event_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `organisation_info`
--
ALTER TABLE `organisation_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `participants_categories`
--
ALTER TABLE `participants_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `urls`
--
ALTER TABLE `urls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `url_types`
--
ALTER TABLE `url_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contributors_assignments`
--
ALTER TABLE `contributors_assignments`
  ADD CONSTRAINT `contributors_assignments_ibfk_1` FOREIGN KEY (`contributor_id`) REFERENCES `contributors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `entity_media`
--
ALTER TABLE `entity_media`
  ADD CONSTRAINT `entity_media_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `entity_urls`
--
ALTER TABLE `entity_urls`
  ADD CONSTRAINT `entity_urls_ibfk_1` FOREIGN KEY (`url_id`) REFERENCES `urls` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `event_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_participants_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`type_key`) REFERENCES `section_types` (`type_key`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `urls`
--
ALTER TABLE `urls`
  ADD CONSTRAINT `urls_ibfk_1` FOREIGN KEY (`url_type_id`) REFERENCES `url_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

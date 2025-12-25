-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 03:48 PM
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
-- Database: `multi_tenant_journals`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `tenant_id`, `title`, `content`, `is_published`, `published_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Call for Papers: Special Issue on AI', 'We invite submissions for our upcoming special issue on Artificial Intelligence in Science. Deadline: March 31, 2025.', 1, '2025-12-19 19:59:45', NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 1, 'New Impact Factor Released', 'We are pleased to announce our latest impact factor of 4.5, reflecting the quality of research published in our journal.', 1, '2025-12-19 19:59:45', NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 2, 'COVID-19 Research Fast-Track', 'All COVID-19 related research will be fast-tracked for publication. No publication fees for qualifying submissions.', 1, '2025-12-19 19:59:45', NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(4, 2, 'New Editorial Board Members', 'We are pleased to welcome three distinguished researchers to our editorial board.', 1, '2025-12-22 05:01:58', NULL, '2025-12-22 05:01:58', '2025-12-22 05:01:58'),
(5, 2, 'New Editorial Board Members', 'We are pleased to welcome three distinguished researchers to our editorial board.', 1, '2025-12-22 05:02:32', NULL, '2025-12-22 05:02:32', '2025-12-22 05:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `volume_id` int(10) UNSIGNED DEFAULT NULL,
  `issue_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `abstract` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `pdf_url` varchar(500) DEFAULT NULL,
  `supplementary_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supplementary_files`)),
  `doi` varchar(100) DEFAULT NULL,
  `pages` varchar(20) DEFAULT NULL COMMENT 'e.g., 1-15',
  `article_number` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('draft','submitted','under_review','revision_required','accepted','published','rejected') DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `view_count` int(10) UNSIGNED DEFAULT 0,
  `download_count` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `tenant_id`, `volume_id`, `issue_id`, `title`, `slug`, `abstract`, `keywords`, `content`, `pdf_url`, `supplementary_files`, `doi`, `pages`, `article_number`, `status`, `submitted_at`, `accepted_at`, `published_at`, `meta_title`, `meta_description`, `view_count`, `download_count`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Machine Learning in Climate Science: A Comprehensive Review', 'ml-climate-science-review', 'This paper provides a comprehensive review of machine learning applications in climate science, covering prediction models, data analysis techniques, and future directions.', 'machine learning, climate science, artificial intelligence, prediction models', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2023-01-14 23:00:00', NULL, NULL, 1250, 0, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 1, 1, 1, 'Quantum Computing: Current State and Future Prospects', 'quantum-computing-prospects', 'An analysis of the current state of quantum computing technology and its potential impact on various scientific fields.', 'quantum computing, quantum mechanics, technology', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2023-01-19 23:00:00', NULL, NULL, 890, 0, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 1, 1, 2, 'Sustainable Energy Solutions for Urban Development', 'sustainable-energy-urban', 'This research explores innovative sustainable energy solutions tailored for urban environments, addressing challenges and opportunities.', 'sustainable energy, urban development, renewable energy', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2023-06-09 23:00:00', NULL, NULL, 650, 0, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(4, 2, 3, 4, 'Advances in Cancer Immunotherapy: A Clinical Perspective', 'cancer-immunotherapy-advances', 'A clinical review of recent advances in cancer immunotherapy, including checkpoint inhibitors and CAR-T cell therapy.', 'cancer, immunotherapy, oncology, clinical research', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2024-02-29 23:00:00', NULL, NULL, 2100, 0, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(5, 3, 4, 5, 'Blockchain Technology in Supply Chain Management', 'blockchain-supply-chain', 'Examining the transformative potential of blockchain technology in modernizing supply chain management systems.', 'blockchain, supply chain, technology, logistics', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2024-02-14 23:00:00', NULL, NULL, 780, 0, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(6, 2, 3, 4, 'Advances in Cancer Immunotherapy', 'advances-cancer-immunotherapy', 'This comprehensive review examines recent breakthroughs in cancer immunotherapy, including checkpoint inhibitors and CAR-T cell therapy.', 'cancer, immunotherapy, oncology, treatment', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2024-03-14 23:00:00', NULL, NULL, 1250, 0, '2025-12-22 05:00:57', '2025-12-22 05:00:57'),
(7, 2, 3, 4, 'COVID-19 Long-Term Effects Study', 'covid19-long-term-effects', 'A longitudinal study examining the persistent symptoms and health impacts experienced by COVID-19 survivors months after initial infection.', 'COVID-19, long COVID, respiratory health', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2024-03-19 23:00:00', NULL, NULL, 2100, 0, '2025-12-22 05:00:57', '2025-12-22 05:00:57'),
(8, 2, 3, 4, 'Mental Health in Healthcare Workers', 'mental-health-healthcare-workers', 'Investigating the psychological impact of pandemic-related stress on frontline healthcare professionals and effective intervention strategies.', 'mental health, healthcare workers, burnout, stress', NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, NULL, '2024-03-24 23:00:00', NULL, NULL, 891, 0, '2025-12-22 05:00:57', '2025-12-22 05:05:01');

-- --------------------------------------------------------

--
-- Table structure for table `article_authors`
--

CREATE TABLE `article_authors` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL if external author',
  `author_name` varchar(200) NOT NULL,
  `author_email` varchar(255) DEFAULT NULL,
  `author_affiliation` varchar(255) DEFAULT NULL,
  `author_orcid` varchar(20) DEFAULT NULL,
  `author_order` int(10) UNSIGNED DEFAULT 1,
  `is_corresponding` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `article_authors`
--

INSERT INTO `article_authors` (`id`, `tenant_id`, `article_id`, `user_id`, `author_name`, `author_email`, `author_affiliation`, `author_orcid`, `author_order`, `is_corresponding`, `created_at`) VALUES
(1, 1, 1, NULL, 'John Smith', NULL, 'MIT', NULL, 1, 1, '2025-12-19 19:59:45'),
(2, 1, 1, NULL, 'Jane Doe', NULL, 'Stanford University', NULL, 2, 0, '2025-12-19 19:59:45'),
(3, 1, 2, NULL, 'Michael Chen', NULL, 'Caltech', NULL, 1, 1, '2025-12-19 19:59:45'),
(4, 1, 3, NULL, 'Emily Brown', NULL, 'UC Berkeley', NULL, 1, 1, '2025-12-19 19:59:45'),
(5, 2, 4, NULL, 'Robert Johnson', NULL, 'Johns Hopkins', NULL, 1, 1, '2025-12-19 19:59:45'),
(6, 2, 4, NULL, 'Lisa Anderson', NULL, 'Mayo Clinic', NULL, 2, 0, '2025-12-19 19:59:45'),
(7, 3, 5, NULL, 'Sarah Williams', NULL, 'Google Research', NULL, 1, 1, '2025-12-19 19:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `article_reviews`
--

CREATE TABLE `article_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `assignment_id` int(10) UNSIGNED NOT NULL,
  `reviewer_id` int(10) UNSIGNED NOT NULL,
  `recommendation` enum('accept','minor_revision','major_revision','reject') NOT NULL,
  `comments_to_author` text DEFAULT NULL,
  `comments_to_editor` text DEFAULT NULL,
  `originality_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `methodology_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `clarity_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `significance_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `overall_score` tinyint(3) UNSIGNED DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `version` int(10) UNSIGNED DEFAULT 1,
  `is_latest` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_revisions`
--

CREATE TABLE `article_revisions` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `title` varchar(500) NOT NULL,
  `abstract` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `pdf_url` varchar(500) DEFAULT NULL,
  `revision_notes` text DEFAULT NULL,
  `response_to_reviewers` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_status_history`
--

CREATE TABLE `article_status_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `changed_by_type` enum('user','super_admin','system') DEFAULT 'user',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `editorial_board`
--

CREATE TABLE `editorial_board` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `affiliation` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `position` enum('editor_in_chief','managing_editor','associate_editor','editorial_board','advisory_board') DEFAULT 'editorial_board',
  `display_order` int(10) UNSIGNED DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `editorial_board`
--

INSERT INTO `editorial_board` (`id`, `tenant_id`, `user_id`, `name`, `email`, `title`, `affiliation`, `country`, `photo_url`, `bio`, `position`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Prof. John Smith', NULL, 'Professor', 'MIT', 'United States', NULL, NULL, 'editor_in_chief', 1, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 1, NULL, 'Dr. Maria Garcia', NULL, 'Associate Professor', 'Cambridge University', 'United Kingdom', NULL, NULL, 'associate_editor', 2, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 1, NULL, 'Prof. Hiroshi Tanaka', NULL, 'Professor', 'University of Tokyo', 'Japan', NULL, NULL, 'editorial_board', 3, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(4, 2, NULL, 'Prof. Robert Johnson', NULL, 'Professor', 'Johns Hopkins', 'United States', NULL, NULL, 'editor_in_chief', 1, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(5, 3, NULL, 'Dr. Sarah Williams', NULL, 'Research Director', 'Google Research', 'United States', NULL, NULL, 'editor_in_chief', 1, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `email_notifications`
--

CREATE TABLE `email_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(200) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `body_html` longtext NOT NULL,
  `body_text` text DEFAULT NULL,
  `notification_type` varchar(100) NOT NULL,
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `volume_id` int(10) UNSIGNED NOT NULL,
  `issue_number` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT 'Special issue title if applicable',
  `month` varchar(20) DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_special_issue` tinyint(1) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issues`
--

INSERT INTO `issues` (`id`, `tenant_id`, `volume_id`, `issue_number`, `title`, `month`, `cover_image`, `description`, `is_special_issue`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, 'January', NULL, NULL, 0, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 1, 1, 2, NULL, 'June', NULL, NULL, 0, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 1, 2, 1, NULL, 'January', NULL, NULL, 0, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(4, 2, 3, 1, NULL, 'March', NULL, NULL, 0, 1, '2024-02-29 23:00:00', '2025-12-19 19:59:45', '2025-12-22 05:02:32'),
(5, 3, 4, 1, NULL, 'February', NULL, NULL, 0, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `menu_order` int(10) UNSIGNED DEFAULT 0,
  `show_in_menu` tinyint(1) DEFAULT 1,
  `is_published` tinyint(1) DEFAULT 1,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `tenant_id`, `slug`, `title`, `content`, `menu_order`, `show_in_menu`, `is_published`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 1, 'about', 'About the Journal', '<h2>About International Journal of Science</h2><p>The International Journal of Science is a peer-reviewed academic journal dedicated to publishing high-quality research across all scientific disciplines.</p><h3>Scope</h3><p>We welcome original research articles, reviews, and perspectives that contribute to the advancement of scientific knowledge.</p>', 1, 1, 1, NULL, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 1, 'author-guidelines', 'Author Guidelines', '<h2>Author Guidelines</h2><p>Authors are encouraged to submit original research that has not been published elsewhere.</p><h3>Submission Process</h3><ul><li>Prepare your manuscript according to our template</li><li>Submit through our online system</li><li>Track your submission status</li></ul>', 2, 1, 1, NULL, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 1, 'contact', 'Contact Us', '<h2>Contact Information</h2><p>For inquiries, please email: editor@science-journal.org</p>', 3, 1, 1, NULL, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(4, 2, 'about', 'About the Journal', '<h2>About Medical Research Journal</h2><p>Medical Research Journal publishes cutting-edge research in medicine and healthcare.</p>', 1, 1, 1, NULL, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(5, 3, 'about', 'About the Journal', '<h2>About Technology Innovation Review</h2><p>Technology Innovation Review bridges the gap between technological advancement and societal impact.</p>', 1, 1, 1, NULL, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `review_assignments`
--

CREATE TABLE `review_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `reviewer_id` int(10) UNSIGNED NOT NULL,
  `assigned_by` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','declined','completed','cancelled') DEFAULT 'pending',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `invitation_sent_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_token_expires` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `is_active`, `remember_token`, `remember_token_expires`, `password_reset_token`, `password_reset_expires`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`) VALUES
(1, 'admin@system.local', '$2y$10$GwMoCS0lAjtt5bzPM3XcD.MZDZTRiketayt/341CKSOl/CggPNi3K', 'System', 'Administrator', 1, NULL, NULL, NULL, NULL, '2025-12-22 15:01:46', '::1', '2025-12-22 09:05:27', '2025-12-22 16:01:46');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `tagline` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `subdomain` varchar(100) NOT NULL COMMENT 'e.g., journal-a.yoursystem.io',
  `custom_domain` varchar(255) DEFAULT NULL COMMENT 'e.g., journalA.com',
  `logo_url` varchar(500) DEFAULT NULL,
  `favicon_url` varchar(500) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#1a73e8',
  `secondary_color` varchar(7) DEFAULT '#34a853',
  `theme` varchar(50) DEFAULT 'default',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `api_key` varchar(64) NOT NULL,
  `api_key_created_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `slug`, `name`, `tagline`, `description`, `subdomain`, `custom_domain`, `logo_url`, `favicon_url`, `primary_color`, `secondary_color`, `theme`, `email`, `phone`, `address`, `settings`, `api_key`, `api_key_created_at`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'journal-science', 'International Journal of Science', 'Advancing Scientific Knowledge', NULL, 'science.journals.local', NULL, NULL, NULL, '#1a73e8', '#34a853', 'default', 'editor@science-journal.org', NULL, NULL, NULL, 'api_key_science_12345678901234567890123456789012', NULL, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 'journal-medicine', 'Medical Research Journal', 'Excellence in Medical Research', NULL, 'medicine.journals.local', NULL, NULL, NULL, '#34a853', '#34a853', 'default', 'editor@med-journal.org', NULL, NULL, NULL, 'api_key_medicine_123456789012345678901234567890', NULL, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 'journal-tech', 'Technology Innovation Review', 'Bridging Technology and Society', NULL, 'tech.journals.local', NULL, NULL, NULL, '#ea4335', '#34a853', 'default', 'editor@tech-journal.org', NULL, NULL, NULL, 'api_key_tech_1234567890123456789012345678901234', NULL, 1, '2025-12-19 19:59:45', '2025-12-19 19:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `title` varchar(50) DEFAULT NULL COMMENT 'Prof., Dr., Mr., Mrs., etc.',
  `affiliation` varchar(255) DEFAULT NULL COMMENT 'University/Institution',
  `orcid` varchar(20) DEFAULT NULL,
  `role` enum('author','reviewer','editor','admin','editor_in_chief') DEFAULT 'author',
  `bio` text DEFAULT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_token_expires` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `email`, `password_hash`, `first_name`, `last_name`, `title`, `affiliation`, `orcid`, `role`, `bio`, `profile_image`, `is_active`, `email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `last_login_at`, `created_at`, `updated_at`, `remember_token`, `remember_token_expires`, `last_login_ip`) VALUES
(1, 1, 'admin@science-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'Prof.', 'MIT', NULL, 'editor_in_chief', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2025-12-19 19:59:45', '2025-12-22 19:09:59', NULL, NULL, NULL),
(2, 1, 'author@science-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'Dr.', 'Stanford University', NULL, 'author', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45', NULL, NULL, NULL),
(3, 2, 'admin@med-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Johnson', 'Prof.', 'Johns Hopkins', NULL, 'editor_in_chief', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2025-12-19 19:59:45', '2025-12-22 19:09:59', NULL, NULL, NULL),
(4, 3, 'admin@tech-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Williams', 'Dr.', 'Google Research', NULL, 'editor_in_chief', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, '2025-12-19 19:59:45', '2025-12-22 19:09:59', NULL, NULL, NULL),
(5, 1, 'reviewer@science-journal.org', '$2y$10$GwMoCS0lAjtt5bzPM3XcD.MZDZTRiketayt/341CKSOl/CggPNi3K', 'Mike', 'Reviewer', NULL, NULL, NULL, 'reviewer', NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, '2025-12-22 19:09:30', '2025-12-22 19:09:30', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `volumes`
--

CREATE TABLE `volumes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `volume_number` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `year` year(4) NOT NULL,
  `description` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volumes`
--

INSERT INTO `volumes` (`id`, `tenant_id`, `volume_number`, `title`, `year`, `description`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, '2023', NULL, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(2, 1, 2, NULL, '2024', NULL, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(3, 2, 1, NULL, '2024', NULL, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45'),
(4, 3, 1, NULL, '2024', NULL, 1, NULL, '2025-12-19 19:59:45', '2025-12-19 19:59:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_published` (`tenant_id`,`is_published`,`published_at`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slug_per_tenant` (`tenant_id`,`slug`),
  ADD UNIQUE KEY `unique_doi_per_tenant` (`tenant_id`,`doi`),
  ADD KEY `volume_id` (`volume_id`),
  ADD KEY `issue_id` (`issue_id`),
  ADD KEY `idx_tenant_status` (`tenant_id`,`status`),
  ADD KEY `idx_tenant_published` (`tenant_id`,`published_at`),
  ADD KEY `idx_tenant_volume_issue` (`tenant_id`,`volume_id`,`issue_id`);
ALTER TABLE `articles` ADD FULLTEXT KEY `ft_search` (`title`,`abstract`,`keywords`);

--
-- Indexes for table `article_authors`
--
ALTER TABLE `article_authors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_tenant_article` (`tenant_id`,`article_id`),
  ADD KEY `idx_article_order` (`article_id`,`author_order`);

--
-- Indexes for table `article_reviews`
--
ALTER TABLE `article_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `idx_tenant_article` (`tenant_id`,`article_id`),
  ADD KEY `idx_article_latest` (`article_id`,`is_latest`);

--
-- Indexes for table `article_revisions`
--
ALTER TABLE `article_revisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_version` (`article_id`,`version`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `idx_tenant_article` (`tenant_id`,`article_id`);

--
-- Indexes for table `article_status_history`
--
ALTER TABLE `article_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article` (`article_id`),
  ADD KEY `idx_tenant_created` (`tenant_id`,`created_at`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_action` (`tenant_id`,`action`),
  ADD KEY `idx_tenant_entity` (`tenant_id`,`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `editorial_board`
--
ALTER TABLE `editorial_board`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_tenant_position` (`tenant_id`,`position`),
  ADD KEY `idx_tenant_order` (`tenant_id`,`display_order`);

--
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tenant_type` (`tenant_id`,`notification_type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_issue_per_volume` (`volume_id`,`issue_number`),
  ADD KEY `idx_tenant_published` (`tenant_id`,`is_published`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slug_per_tenant` (`tenant_id`,`slug`),
  ADD KEY `idx_tenant_menu` (`tenant_id`,`show_in_menu`,`menu_order`);

--
-- Indexes for table `review_assignments`
--
ALTER TABLE `review_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`article_id`,`reviewer_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_tenant_article` (`tenant_id`,`article_id`),
  ADD KEY `idx_reviewer_status` (`reviewer_id`,`status`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `subdomain` (`subdomain`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD UNIQUE KEY `custom_domain` (`custom_domain`),
  ADD KEY `idx_subdomain` (`subdomain`),
  ADD KEY `idx_custom_domain` (`custom_domain`),
  ADD KEY `idx_api_key` (`api_key`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_per_tenant` (`tenant_id`,`email`),
  ADD KEY `idx_tenant_role` (`tenant_id`,`role`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `volumes`
--
ALTER TABLE `volumes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_volume_per_tenant` (`tenant_id`,`volume_number`),
  ADD KEY `idx_tenant_year` (`tenant_id`,`year`),
  ADD KEY `idx_tenant_published` (`tenant_id`,`is_published`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `article_authors`
--
ALTER TABLE `article_authors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `article_reviews`
--
ALTER TABLE `article_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_revisions`
--
ALTER TABLE `article_revisions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_status_history`
--
ALTER TABLE `article_status_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `editorial_board`
--
ALTER TABLE `editorial_board`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `review_assignments`
--
ALTER TABLE `review_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `volumes`
--
ALTER TABLE `volumes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`volume_id`) REFERENCES `volumes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_authors`
--
ALTER TABLE `article_authors`
  ADD CONSTRAINT `article_authors_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_authors_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_authors_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_reviews`
--
ALTER TABLE `article_reviews`
  ADD CONSTRAINT `article_reviews_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reviews_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reviews_ibfk_3` FOREIGN KEY (`assignment_id`) REFERENCES `review_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_reviews_ibfk_4` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_revisions`
--
ALTER TABLE `article_revisions`
  ADD CONSTRAINT `article_revisions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_revisions_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_revisions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_status_history`
--
ALTER TABLE `article_status_history`
  ADD CONSTRAINT `article_status_history_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_status_history_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `editorial_board`
--
ALTER TABLE `editorial_board`
  ADD CONSTRAINT `editorial_board_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `editorial_board_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD CONSTRAINT `email_notifications_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issues`
--
ALTER TABLE `issues`
  ADD CONSTRAINT `issues_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issues_ibfk_2` FOREIGN KEY (`volume_id`) REFERENCES `volumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_assignments`
--
ALTER TABLE `review_assignments`
  ADD CONSTRAINT `review_assignments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_assignments_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_assignments_ibfk_3` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_assignments_ibfk_4` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `volumes`
--
ALTER TABLE `volumes`
  ADD CONSTRAINT `volumes_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

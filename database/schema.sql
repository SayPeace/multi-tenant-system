-- Multi-Tenant Journal Management System
-- Database Schema
-- Approach: Distributed Frontend + Central API

-- Create database
CREATE DATABASE IF NOT EXISTS multi_tenant_journals
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE multi_tenant_journals;

-- =====================================================
-- TENANT MANAGEMENT
-- =====================================================

-- Core tenant (journal) table
CREATE TABLE IF NOT EXISTS tenants (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    tagline VARCHAR(500) NULL,
    description TEXT NULL,

    -- Domain configuration
    subdomain VARCHAR(100) UNIQUE NOT NULL COMMENT 'e.g., journal-a.yoursystem.io',
    custom_domain VARCHAR(255) UNIQUE NULL COMMENT 'e.g., journalA.com',

    -- Branding
    logo_url VARCHAR(500) NULL,
    favicon_url VARCHAR(500) NULL,
    primary_color VARCHAR(7) DEFAULT '#1a73e8',
    secondary_color VARCHAR(7) DEFAULT '#34a853',
    theme VARCHAR(50) DEFAULT 'default',

    -- Contact information
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,

    -- Settings (JSON)
    settings JSON NULL,

    -- API Access
    api_key VARCHAR(64) UNIQUE NOT NULL,
    api_key_created_at TIMESTAMP NULL,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_subdomain (subdomain),
    INDEX idx_custom_domain (custom_domain),
    INDEX idx_api_key (api_key),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =====================================================
-- USER MANAGEMENT
-- =====================================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,

    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,

    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    title VARCHAR(50) NULL COMMENT 'Prof., Dr., Mr., Mrs., etc.',
    affiliation VARCHAR(255) NULL COMMENT 'University/Institution',
    orcid VARCHAR(20) NULL,

    role ENUM('author', 'reviewer', 'editor', 'admin') DEFAULT 'author',

    bio TEXT NULL,
    profile_image VARCHAR(500) NULL,

    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(64) NULL,
    password_reset_token VARCHAR(64) NULL,
    password_reset_expires TIMESTAMP NULL,

    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_email_per_tenant (tenant_id, email),
    INDEX idx_tenant_role (tenant_id, role),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- =====================================================
-- JOURNAL STRUCTURE
-- =====================================================

-- Volumes
CREATE TABLE IF NOT EXISTS volumes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,

    volume_number INT UNSIGNED NOT NULL,
    title VARCHAR(255) NULL,
    year YEAR NOT NULL,
    description TEXT NULL,

    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_volume_per_tenant (tenant_id, volume_number),
    INDEX idx_tenant_year (tenant_id, year),
    INDEX idx_tenant_published (tenant_id, is_published)
) ENGINE=InnoDB;

-- Issues
CREATE TABLE IF NOT EXISTS issues (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    volume_id INT UNSIGNED NOT NULL,

    issue_number INT UNSIGNED NOT NULL,
    title VARCHAR(255) NULL COMMENT 'Special issue title if applicable',
    month VARCHAR(20) NULL,

    cover_image VARCHAR(500) NULL,
    description TEXT NULL,

    is_special_issue BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_issue_per_volume (volume_id, issue_number),
    INDEX idx_tenant_published (tenant_id, is_published)
) ENGINE=InnoDB;

-- =====================================================
-- ARTICLES
-- =====================================================

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,

    -- Classification
    volume_id INT UNSIGNED NULL,
    issue_id INT UNSIGNED NULL,

    -- Content
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) NOT NULL,
    abstract TEXT NULL,
    keywords TEXT NULL,
    content LONGTEXT NULL,

    -- PDF/Files
    pdf_url VARCHAR(500) NULL,
    supplementary_files JSON NULL,

    -- Metadata
    doi VARCHAR(100) NULL,
    pages VARCHAR(20) NULL COMMENT 'e.g., 1-15',
    article_number INT UNSIGNED NULL,

    -- Status workflow
    status ENUM('draft', 'submitted', 'under_review', 'revision_required', 'accepted', 'published', 'rejected') DEFAULT 'draft',

    -- Dates
    submitted_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,

    -- SEO
    meta_title VARCHAR(100) NULL,
    meta_description VARCHAR(160) NULL,

    -- Statistics
    view_count INT UNSIGNED DEFAULT 0,
    download_count INT UNSIGNED DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (volume_id) REFERENCES volumes(id) ON DELETE SET NULL,
    FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE SET NULL,

    UNIQUE KEY unique_slug_per_tenant (tenant_id, slug),
    UNIQUE KEY unique_doi_per_tenant (tenant_id, doi),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_tenant_published (tenant_id, published_at),
    INDEX idx_tenant_volume_issue (tenant_id, volume_id, issue_id),
    FULLTEXT INDEX ft_search (title, abstract, keywords)
) ENGINE=InnoDB;

-- Article Authors (junction table)
CREATE TABLE IF NOT EXISTS article_authors (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    article_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL COMMENT 'NULL if external author',

    -- Author details (for external authors or override)
    author_name VARCHAR(200) NOT NULL,
    author_email VARCHAR(255) NULL,
    author_affiliation VARCHAR(255) NULL,
    author_orcid VARCHAR(20) NULL,

    author_order INT UNSIGNED DEFAULT 1,
    is_corresponding BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_tenant_article (tenant_id, article_id),
    INDEX idx_article_order (article_id, author_order)
) ENGINE=InnoDB;

-- =====================================================
-- EDITORIAL BOARD
-- =====================================================

CREATE TABLE IF NOT EXISTS editorial_board (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,

    -- Details
    name VARCHAR(200) NOT NULL,
    email VARCHAR(255) NULL,
    title VARCHAR(50) NULL,
    affiliation VARCHAR(255) NULL,
    country VARCHAR(100) NULL,
    photo_url VARCHAR(500) NULL,
    bio TEXT NULL,

    -- Position
    position ENUM('editor_in_chief', 'managing_editor', 'associate_editor', 'editorial_board', 'advisory_board') DEFAULT 'editorial_board',
    display_order INT UNSIGNED DEFAULT 0,

    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_tenant_position (tenant_id, position),
    INDEX idx_tenant_order (tenant_id, display_order)
) ENGINE=InnoDB;

-- =====================================================
-- PAGES (CMS-like content)
-- =====================================================

CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,

    slug VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NULL,

    menu_order INT UNSIGNED DEFAULT 0,
    show_in_menu BOOLEAN DEFAULT TRUE,
    is_published BOOLEAN DEFAULT TRUE,

    meta_title VARCHAR(100) NULL,
    meta_description VARCHAR(160) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,

    UNIQUE KEY unique_slug_per_tenant (tenant_id, slug),
    INDEX idx_tenant_menu (tenant_id, show_in_menu, menu_order)
) ENGINE=InnoDB;

-- =====================================================
-- ANNOUNCEMENTS
-- =====================================================

CREATE TABLE IF NOT EXISTS announcements (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,

    is_published BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,

    INDEX idx_tenant_published (tenant_id, is_published, published_at)
) ENGINE=InnoDB;

-- =====================================================
-- AUDIT LOG
-- =====================================================

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,

    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT UNSIGNED NULL,

    old_values JSON NULL,
    new_values JSON NULL,

    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_action (tenant_id, action),
    INDEX idx_tenant_entity (tenant_id, entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert sample tenants (journals)
INSERT INTO tenants (slug, name, tagline, subdomain, custom_domain, api_key, email, primary_color) VALUES
('journal-science', 'International Journal of Science', 'Advancing Scientific Knowledge', 'science.journals.local', NULL, 'api_key_science_12345678901234567890123456789012', 'editor@science-journal.org', '#1a73e8'),
('journal-medicine', 'Medical Research Journal', 'Excellence in Medical Research', 'medicine.journals.local', NULL, 'api_key_medicine_123456789012345678901234567890', 'editor@med-journal.org', '#34a853'),
('journal-tech', 'Technology Innovation Review', 'Bridging Technology and Society', 'tech.journals.local', NULL, 'api_key_tech_1234567890123456789012345678901234', 'editor@tech-journal.org', '#ea4335');

-- Insert sample users
INSERT INTO users (tenant_id, email, password_hash, first_name, last_name, title, affiliation, role) VALUES
(1, 'admin@science-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'Prof.', 'MIT', 'admin'),
(1, 'author@science-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'Dr.', 'Stanford University', 'author'),
(2, 'admin@med-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Johnson', 'Prof.', 'Johns Hopkins', 'admin'),
(3, 'admin@tech-journal.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Williams', 'Dr.', 'Google Research', 'admin');

-- Insert sample volumes
INSERT INTO volumes (tenant_id, volume_number, year, is_published) VALUES
(1, 1, 2023, TRUE),
(1, 2, 2024, TRUE),
(2, 1, 2024, TRUE),
(3, 1, 2024, TRUE);

-- Insert sample issues
INSERT INTO issues (tenant_id, volume_id, issue_number, month, is_published) VALUES
(1, 1, 1, 'January', TRUE),
(1, 1, 2, 'June', TRUE),
(1, 2, 1, 'January', TRUE),
(2, 3, 1, 'March', TRUE),
(3, 4, 1, 'February', TRUE);

-- Insert sample articles
INSERT INTO articles (tenant_id, volume_id, issue_id, title, slug, abstract, keywords, status, published_at, view_count) VALUES
(1, 1, 1, 'Machine Learning in Climate Science: A Comprehensive Review', 'ml-climate-science-review', 'This paper provides a comprehensive review of machine learning applications in climate science, covering prediction models, data analysis techniques, and future directions.', 'machine learning, climate science, artificial intelligence, prediction models', 'published', '2023-01-15', 1250),
(1, 1, 1, 'Quantum Computing: Current State and Future Prospects', 'quantum-computing-prospects', 'An analysis of the current state of quantum computing technology and its potential impact on various scientific fields.', 'quantum computing, quantum mechanics, technology', 'published', '2023-01-20', 890),
(1, 1, 2, 'Sustainable Energy Solutions for Urban Development', 'sustainable-energy-urban', 'This research explores innovative sustainable energy solutions tailored for urban environments, addressing challenges and opportunities.', 'sustainable energy, urban development, renewable energy', 'published', '2023-06-10', 650),
(2, 3, 4, 'Advances in Cancer Immunotherapy: A Clinical Perspective', 'cancer-immunotherapy-advances', 'A clinical review of recent advances in cancer immunotherapy, including checkpoint inhibitors and CAR-T cell therapy.', 'cancer, immunotherapy, oncology, clinical research', 'published', '2024-03-01', 2100),
(3, 4, 5, 'Blockchain Technology in Supply Chain Management', 'blockchain-supply-chain', 'Examining the transformative potential of blockchain technology in modernizing supply chain management systems.', 'blockchain, supply chain, technology, logistics', 'published', '2024-02-15', 780);

-- Insert article authors
INSERT INTO article_authors (tenant_id, article_id, author_name, author_affiliation, author_order, is_corresponding) VALUES
(1, 1, 'John Smith', 'MIT', 1, TRUE),
(1, 1, 'Jane Doe', 'Stanford University', 2, FALSE),
(1, 2, 'Michael Chen', 'Caltech', 1, TRUE),
(1, 3, 'Emily Brown', 'UC Berkeley', 1, TRUE),
(2, 4, 'Robert Johnson', 'Johns Hopkins', 1, TRUE),
(2, 4, 'Lisa Anderson', 'Mayo Clinic', 2, FALSE),
(3, 5, 'Sarah Williams', 'Google Research', 1, TRUE);

-- Insert editorial board
INSERT INTO editorial_board (tenant_id, name, title, affiliation, country, position, display_order) VALUES
(1, 'Prof. John Smith', 'Professor', 'MIT', 'United States', 'editor_in_chief', 1),
(1, 'Dr. Maria Garcia', 'Associate Professor', 'Cambridge University', 'United Kingdom', 'associate_editor', 2),
(1, 'Prof. Hiroshi Tanaka', 'Professor', 'University of Tokyo', 'Japan', 'editorial_board', 3),
(2, 'Prof. Robert Johnson', 'Professor', 'Johns Hopkins', 'United States', 'editor_in_chief', 1),
(3, 'Dr. Sarah Williams', 'Research Director', 'Google Research', 'United States', 'editor_in_chief', 1);

-- Insert sample pages
INSERT INTO pages (tenant_id, slug, title, content, menu_order, show_in_menu) VALUES
(1, 'about', 'About the Journal', '<h2>About International Journal of Science</h2><p>The International Journal of Science is a peer-reviewed academic journal dedicated to publishing high-quality research across all scientific disciplines.</p><h3>Scope</h3><p>We welcome original research articles, reviews, and perspectives that contribute to the advancement of scientific knowledge.</p>', 1, TRUE),
(1, 'author-guidelines', 'Author Guidelines', '<h2>Author Guidelines</h2><p>Authors are encouraged to submit original research that has not been published elsewhere.</p><h3>Submission Process</h3><ul><li>Prepare your manuscript according to our template</li><li>Submit through our online system</li><li>Track your submission status</li></ul>', 2, TRUE),
(1, 'contact', 'Contact Us', '<h2>Contact Information</h2><p>For inquiries, please email: editor@science-journal.org</p>', 3, TRUE),
(2, 'about', 'About the Journal', '<h2>About Medical Research Journal</h2><p>Medical Research Journal publishes cutting-edge research in medicine and healthcare.</p>', 1, TRUE),
(3, 'about', 'About the Journal', '<h2>About Technology Innovation Review</h2><p>Technology Innovation Review bridges the gap between technological advancement and societal impact.</p>', 1, TRUE);

-- Insert sample announcements
INSERT INTO announcements (tenant_id, title, content, published_at) VALUES
(1, 'Call for Papers: Special Issue on AI', 'We invite submissions for our upcoming special issue on Artificial Intelligence in Science. Deadline: March 31, 2025.', NOW()),
(1, 'New Impact Factor Released', 'We are pleased to announce our latest impact factor of 4.5, reflecting the quality of research published in our journal.', NOW()),
(2, 'COVID-19 Research Fast-Track', 'All COVID-19 related research will be fast-tracked for publication. No publication fees for qualifying submissions.', NOW());

-- ============================================
-- DATABASE: iran_war_db
-- Charset: utf8mb4
-- ============================================
DROP DATABASE IF EXISTS iran_war_db;
CREATE DATABASE IF NOT EXISTS iran_war_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE iran_war_db;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#C62828',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS authors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    bio TEXT,
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_authors_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    cover_alt VARCHAR(255) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT UNSIGNED DEFAULT 0,
    reading_time INT UNSIGNED NOT NULL DEFAULT 1,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT,
    CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES authors (id) ON DELETE RESTRICT,
    INDEX idx_articles_slug (slug),
    INDEX idx_articles_status_published (status, published_at),
    INDEX idx_articles_category (category_id),
    INDEX idx_articles_author (author_id),
    INDEX idx_articles_status_category_published (status, category_id, published_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS article_tags (
    article_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    country VARCHAR(80) DEFAULT NULL,
    city VARCHAR(120) DEFAULT NULL,
    interest_area VARCHAR(120) NOT NULL DEFAULT 'geopolitique',
    bio TEXT,
    newsletter_optin TINYINT(1) NOT NULL DEFAULT 1,
    points INT UNSIGNED NOT NULL DEFAULT 0,
    avatar_url VARCHAR(255) DEFAULT NULL,
    plan ENUM('free', 'premium') NOT NULL DEFAULT 'free',
    is_subscribed TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subscribers_email (email),
    INDEX idx_subscribers_plan_active (plan, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS article_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    subscriber_id INT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    author_email VARCHAR(190) NOT NULL,
    content TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 3,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_comments_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    CONSTRAINT fk_article_comments_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE SET NULL,
    INDEX idx_article_comments_article_status (article_id, status),
    INDEX idx_article_comments_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS article_shares (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    channel ENUM('copy', 'x', 'facebook', 'linkedin', 'whatsapp', 'email') NOT NULL,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_shares_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    INDEX idx_article_shares_article_channel (article_id, channel),
    INDEX idx_article_shares_shared_at (shared_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS debates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    body LONGTEXT NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_debates_slug (slug),
    INDEX idx_debates_status_pinned (status, is_pinned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS debate_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    debate_id INT UNSIGNED NOT NULL,
    subscriber_id INT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_debate_comments_debate FOREIGN KEY (debate_id) REFERENCES debates (id) ON DELETE CASCADE,
    CONSTRAINT fk_debate_comments_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE SET NULL,
    INDEX idx_debate_comments_debate_status (debate_id, status),
    INDEX idx_debate_comments_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pinned_journals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    pinned_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pinned_journals_status_order (status, pinned_order, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_messages_status_created (status, created_at),
    INDEX idx_contact_messages_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id INT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 4,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_site_reviews_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE SET NULL,
    INDEX idx_site_reviews_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('militaire', 'politique', 'diplomatique', 'bombardement', 'manifestation') NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    city VARCHAR(120) NOT NULL,
    event_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_events_type_date (type, event_date),
    INDEX idx_events_city (city),
    INDEX idx_events_geo (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timeline_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('militaire', 'politique', 'diplomatique') NOT NULL,
    event_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timeline_category_date (category, event_date),
    INDEX idx_timeline_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('pertes', 'deplacements', 'sanctions') NOT NULL,
    value BIGINT UNSIGNED NOT NULL DEFAULT 0,
    stat_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_stats_type_date (type, stat_date),
    INDEX idx_stats_type_date (type, stat_date),
    INDEX idx_stats_date (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS article_favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    article_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_favorites_user FOREIGN KEY (user_id) REFERENCES subscribers (id) ON DELETE CASCADE,
    CONSTRAINT fk_article_favorites_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    UNIQUE KEY uq_article_favorites_user_article (user_id, article_id),
    INDEX idx_article_favorites_user_created (user_id, created_at),
    INDEX idx_article_favorites_article (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('article', 'commentaire', 'debat') NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES subscribers (id) ON DELETE CASCADE,
    INDEX idx_notifications_user_read_created (user_id, is_read, created_at),
    INDEX idx_notifications_type_created (type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS analytics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(190) NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    duration INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_analytics_user FOREIGN KEY (user_id) REFERENCES subscribers (id) ON DELETE SET NULL,
    INDEX idx_analytics_page_created (page, created_at),
    INDEX idx_analytics_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS security_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    action VARCHAR(120) NOT NULL,
    status ENUM('success', 'failed', 'blocked') NOT NULL DEFAULT 'failed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_security_logs_ip_created (ip, created_at),
    INDEX idx_security_logs_action_created (action, created_at),
    INDEX idx_security_logs_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_analysis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    title_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    keyword_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    readability_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_seo_analysis_article FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    UNIQUE KEY uq_seo_analysis_article (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cache (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(191) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    INDEX idx_cache_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS article_comment_replies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id INT UNSIGNED NOT NULL,
    subscriber_id INT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_comment_replies_comment FOREIGN KEY (comment_id) REFERENCES article_comments (id) ON DELETE CASCADE,
    CONSTRAINT fk_article_comment_replies_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE SET NULL,
    INDEX idx_article_comment_replies_comment_status (comment_id, status),
    INDEX idx_article_comment_replies_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS debate_comment_replies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id INT UNSIGNED NOT NULL,
    subscriber_id INT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_debate_comment_replies_comment FOREIGN KEY (comment_id) REFERENCES debate_comments (id) ON DELETE CASCADE,
    CONSTRAINT fk_debate_comment_replies_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE SET NULL,
    INDEX idx_debate_comment_replies_comment_status (comment_id, status),
    INDEX idx_debate_comment_replies_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comment_votes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_type ENUM('article', 'debat') NOT NULL,
    comment_id INT UNSIGNED NOT NULL,
    subscriber_id INT UNSIGNED NOT NULL,
    vote TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_votes_subscriber FOREIGN KEY (subscriber_id) REFERENCES subscribers (id) ON DELETE CASCADE,
    UNIQUE KEY uq_comment_votes_unique (comment_type, comment_id, subscriber_id),
    INDEX idx_comment_votes_lookup (comment_type, comment_id),
    INDEX idx_comment_votes_subscriber (subscriber_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS data_exchange_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT UNSIGNED DEFAULT NULL,
    exchange_type ENUM('export', 'import') NOT NULL,
    dataset ENUM(
        'articles',
        'subscribers',
        'contacts',
        'comments',
        'reviews',
        'events',
        'timeline',
        'stats',
        'analytics',
        'security',
        'favorites',
        'notifications'
    ) NOT NULL,
    file_name VARCHAR(255) DEFAULT NULL,
    rows_count INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('success', 'failed') NOT NULL DEFAULT 'success',
    notes VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_data_exchange_logs_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users (id) ON DELETE SET NULL,
    INDEX idx_data_exchange_logs_type_dataset_created (exchange_type, dataset, created_at),
    INDEX idx_data_exchange_logs_admin_created (admin_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

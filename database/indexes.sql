USE iran_war_db;

-- Data coherence hardening.
UPDATE subscribers
SET is_subscribed = 0
WHERE plan = 'free' AND is_subscribed <> 0;

-- Helper: create an index only if missing (safe re-run).
DROP PROCEDURE IF EXISTS add_index_if_missing;
DELIMITER $$
CREATE PROCEDURE add_index_if_missing(
    IN in_table_name VARCHAR(64),
    IN in_index_name VARCHAR(64),
    IN in_index_definition VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = in_table_name
          AND index_name = in_index_name
    ) THEN
        SET @create_sql = CONCAT(
            'CREATE INDEX ',
            in_index_name,
            ' ON ',
            in_table_name,
            ' ',
            in_index_definition
        );
        PREPARE stmt FROM @create_sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- Additional optimization indexes used by backoffice filtering and sorting.
CALL add_index_if_missing('articles', 'idx_articles_status_views', '(status, views)');
CALL add_index_if_missing('articles', 'idx_articles_updated_at', '(updated_at)');
CALL add_index_if_missing('articles', 'idx_articles_title', '(title)');
CALL add_index_if_missing('categories', 'idx_categories_name', '(name)');
CALL add_index_if_missing('authors', 'idx_authors_name', '(name)');
CALL add_index_if_missing('admin_users', 'idx_admin_users_role', '(role)');
CALL add_index_if_missing('subscribers', 'idx_subscribers_is_subscribed', '(is_subscribed, is_active)');
CALL add_index_if_missing('subscribers', 'idx_subscribers_points', '(points)');
CALL add_index_if_missing('subscribers', 'idx_subscribers_interest_area', '(interest_area)');
CALL add_index_if_missing('subscribers', 'idx_subscribers_last_login', '(last_login)');
CALL add_index_if_missing('article_comments', 'idx_article_comments_rating', '(rating)');
CALL add_index_if_missing('debates', 'idx_debates_created_at', '(created_at)');
CALL add_index_if_missing('pinned_journals', 'idx_pinned_journals_created_at', '(created_at)');
CALL add_index_if_missing('contact_messages', 'idx_contact_messages_created_at', '(created_at)');
CALL add_index_if_missing('site_reviews', 'idx_site_reviews_rating', '(rating)');
CALL add_index_if_missing('data_exchange_logs', 'idx_data_exchange_logs_created_at', '(created_at)');
CALL add_index_if_missing('events', 'idx_events_date_city', '(event_date, city)');
CALL add_index_if_missing('timeline_events', 'idx_timeline_date_category', '(event_date, category)');
CALL add_index_if_missing('stats', 'idx_stats_date_value', '(stat_date, value)');
CALL add_index_if_missing('article_favorites', 'idx_article_favorites_created', '(created_at)');
CALL add_index_if_missing('notifications', 'idx_notifications_read_created', '(is_read, created_at)');
CALL add_index_if_missing('analytics', 'idx_analytics_duration_page', '(duration, page)');
CALL add_index_if_missing('security_logs', 'idx_security_logs_ip_status_created', '(ip, status, created_at)');
CALL add_index_if_missing('seo_analysis', 'idx_seo_analysis_scores', '(title_score, keyword_score, readability_score)');
CALL add_index_if_missing('cache', 'idx_cache_key_created', '(key_name, created_at)');
CALL add_index_if_missing('article_comment_replies', 'idx_article_comment_replies_status_created', '(status, created_at)');
CALL add_index_if_missing('debate_comment_replies', 'idx_debate_comment_replies_status_created', '(status, created_at)');
CALL add_index_if_missing('comment_votes', 'idx_comment_votes_vote', '(vote)');

DROP PROCEDURE IF EXISTS add_index_if_missing;

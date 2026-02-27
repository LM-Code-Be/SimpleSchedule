-- Script SQL complet LM-Code SimpleSchedule
-- Recommande: utiliser plutot les migrations via `php bin/migrate.php`

CREATE TABLE IF NOT EXISTS events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    color CHAR(7) NOT NULL DEFAULT '#2463eb',
    is_task TINYINT(1) NOT NULL DEFAULT 0,
    is_done TINYINT(1) NOT NULL DEFAULT 0,
    priority ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
    status ENUM('pending','current','past') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_events_date (event_date),
    INDEX idx_events_task_done (is_task, is_done),
    INDEX idx_events_status (status),
    INDEX idx_events_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    color CHAR(7) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_tags (
    event_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (event_id, tag_id),
    CONSTRAINT fk_event_tags_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_event_tags_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tags (name, color)
VALUES
    ('Travail', '#0ea5e9'),
    ('Personnel', '#f97316'),
    ('Sante', '#22c55e'),
    ('Urgent', '#ef4444'),
    ('Etudes', '#8b5cf6'),
    ('Famille', '#ec4899'),
    ('Sport', '#14b8a6'),
    ('Administratif', '#64748b'),
    ('Projet', '#2563eb'),
    ('Finance', '#f59e0b')
ON DUPLICATE KEY UPDATE color = VALUES(color);

INSERT INTO app_settings (setting_key, setting_value)
VALUES
    ('ui_theme', 'light'),
    ('ui_density', 'comfortable'),
    ('timezone', 'Europe/Paris'),
    ('first_day_of_week', 'monday'),
    ('default_calendar_view', 'dayGridMonth'),
    ('show_weekends', '1'),
    ('notifications_email', '0'),
    ('notifications_push', '0'),
    ('tasks_auto_archive_days', '30'),
    ('dashboard_scope', 'week')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

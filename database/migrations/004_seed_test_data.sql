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

INSERT INTO events (title, description, event_date, start_time, end_time, color, is_task, is_done, priority, status)
VALUES
    ('Sprint planning equipe', 'Planification du sprint et repartition des taches.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', '#2563eb', 0, 0, 'high', 'pending'),
    ('Revue budget mensuel', 'Validation des depenses du mois courant.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', '#f59e0b', 0, 0, 'normal', 'pending'),
    ('Session sport cardio', 'Entrainement personnel en salle.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:30:00', '19:30:00', '#14b8a6', 0, 0, 'low', 'pending'),
    ('Dentiste controle annuel', 'Visite de suivi.', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:15:00', '12:00:00', '#22c55e', 0, 0, 'normal', 'pending'),
    ('Declaration administrative', 'Envoi dossier administratif.', DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, NULL, '#64748b', 1, 0, 'high', 'pending'),
    ('Cours SQL avance', 'Finaliser les exercices indexes et jointures.', DATE_ADD(CURDATE(), INTERVAL 6 DAY), NULL, NULL, '#8b5cf6', 1, 0, 'normal', 'pending'),
    ('Appel famille weekend', 'Organiser la visite du dimanche.', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:30:00', '11:00:00', '#ec4899', 0, 0, 'low', 'pending'),
    ('Suivi projet client A', 'Point d avancement avec le client.', DATE_ADD(CURDATE(), INTERVAL 8 DAY), '16:00:00', '17:00:00', '#2563eb', 0, 0, 'high', 'pending'),
    ('Paiement factures', 'Regler eau, electricite, internet.', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, NULL, '#f97316', 1, 0, 'high', 'pending'),
    ('Lecture technique API', 'Revue architecture et tests.', DATE_ADD(CURDATE(), INTERVAL 9 DAY), NULL, NULL, '#0ea5e9', 1, 0, 'normal', 'pending');

INSERT INTO event_tags (event_id, tag_id)
SELECT e.id, t.id
FROM events e
INNER JOIN tags t ON (
    (e.title = 'Sprint planning equipe' AND t.name IN ('Travail', 'Projet', 'Urgent')) OR
    (e.title = 'Revue budget mensuel' AND t.name IN ('Finance', 'Travail')) OR
    (e.title = 'Session sport cardio' AND t.name IN ('Sport', 'Personnel')) OR
    (e.title = 'Dentiste controle annuel' AND t.name IN ('Sante', 'Personnel')) OR
    (e.title = 'Declaration administrative' AND t.name IN ('Administratif', 'Urgent')) OR
    (e.title = 'Cours SQL avance' AND t.name IN ('Etudes', 'Projet')) OR
    (e.title = 'Appel famille weekend' AND t.name IN ('Famille', 'Personnel')) OR
    (e.title = 'Suivi projet client A' AND t.name IN ('Projet', 'Travail')) OR
    (e.title = 'Paiement factures' AND t.name IN ('Finance', 'Administratif')) OR
    (e.title = 'Lecture technique API' AND t.name IN ('Etudes', 'Travail'))
)
ON DUPLICATE KEY UPDATE tag_id = VALUES(tag_id);

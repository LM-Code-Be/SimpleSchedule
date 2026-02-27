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

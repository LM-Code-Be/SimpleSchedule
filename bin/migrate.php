<?php

declare(strict_types=1);

use App\Infrastructure\Database\ConnectionFactory;

// Runner de migrations SQL ordonnees.
// Usage: php bin/migrate.php
require_once __DIR__ . '/../src/Shared/Autoloader.php';

$config = require __DIR__ . '/../config/database.php';
$db = $config['db'];

$bootstrapDsn = sprintf(
    'mysql:host=%s;port=%d;charset=%s',
    $db['host'],
    $db['port'] ?? 3306,
    $db['charset'] ?? 'utf8mb4'
);
$bootstrapPdo = new PDO($bootstrapDsn, (string) $db['user'], (string) $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$databaseName = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $db['name']);
// Creation defensive de la base pour simplifier l'onboarding local.
$bootstrapPdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$pdo = ConnectionFactory::make($config['db']);

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(255) PRIMARY KEY,
    executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$appliedMap = array_fill_keys($applied, true);

$files = glob(__DIR__ . '/../database/migrations/*.sql');
sort($files);

if ($files === []) {
    fwrite(STDOUT, "No migration files found.\n");
    exit(0);
}

foreach ($files as $file) {
    $version = basename($file);
    if (isset($appliedMap[$version])) {
        fwrite(STDOUT, "- Skipped {$version}\n");
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Cannot read {$version}\n");
        exit(1);
    }

    try {
        // Chaque fichier SQL est applique comme une unite versionnee.
        $pdo->exec($sql);
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (version) VALUES (:version)');
        $stmt->execute(['version' => $version]);
        fwrite(STDOUT, "+ Applied {$version}\n");
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "Migration failed on {$version}: {$e->getMessage()}\n");
        exit(1);
    }
}

fwrite(STDOUT, "Done.\n");

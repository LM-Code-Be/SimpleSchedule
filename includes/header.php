<?php

declare(strict_types=1);

if (!isset($pageTitle)) {
    $pageTitle = 'LM-Code SimpleSchedule';
}

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '/') {
    $basePath = '';
}

$isMoreActive = is_current_page('settings.php') || is_current_page('help.php');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | LM-Code SimpleSchedule</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $basePath ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-bg" aria-hidden="true"></div>
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="container-xl">
        <a class="navbar-brand fw-bold" href="<?= $basePath ?>/index.php">
            <span class="brand-mark"><i class="bi bi-calendar2-week-fill"></i></span>
            <span>LM-Code SimpleSchedule</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Afficher le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav nav-main me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= is_current_page('index.php') ? 'active' : '' ?>" href="<?= $basePath ?>/index.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= is_current_page('calendar.php') ? 'active' : '' ?>" href="<?= $basePath ?>/calendar.php"><i class="bi bi-calendar3"></i>Calendrier</a></li>
                <li class="nav-item"><a class="nav-link <?= is_current_page('events.php') ? 'active' : '' ?>" href="<?= $basePath ?>/events.php"><i class="bi bi-calendar-event"></i>Événements</a></li>
                <li class="nav-item"><a class="nav-link <?= is_current_page('tasks.php') ? 'active' : '' ?>" href="<?= $basePath ?>/tasks.php"><i class="bi bi-list-check"></i>Tâches</a></li>
                <li class="nav-item"><a class="nav-link <?= is_current_page('tags.php') ? 'active' : '' ?>" href="<?= $basePath ?>/tags.php"><i class="bi bi-tags"></i>Tags</a></li>
                <li class="nav-item"><a class="nav-link <?= is_current_page('stats.php') ? 'active' : '' ?>" href="<?= $basePath ?>/stats.php"><i class="bi bi-bar-chart-line"></i>Stats</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isMoreActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>Plus
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $basePath ?>/settings.php"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li><a class="dropdown-item" href="<?= $basePath ?>/help.php"><i class="bi bi-question-circle me-2"></i>Aide</a></li>
                    </ul>
                </li>
            </ul>
            <div class="d-flex gap-2 flex-wrap nav-actions">
                <a class="btn btn-outline-light btn-sm" href="<?= $basePath ?>/export.php?format=json"><i class="bi bi-download me-1"></i>Exporter</a>
                <a class="btn btn-primary btn-sm" href="<?= $basePath ?>/events.php?action=create"><i class="bi bi-plus-lg me-1"></i>Nouveau</a>
            </div>
        </div>
    </div>
</nav>
<main class="container-xl py-4">

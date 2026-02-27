<?php

declare(strict_types=1);

use App\Application\Service\DashboardService;

$container = require __DIR__ . '/bootstrap/app.php';
$dashboard = $container->get(DashboardService::class)->data();

$todayEvents = $dashboard['today_events'];
$upcoming = $dashboard['upcoming'];
$tasks = $dashboard['tasks'];
$taskStats = $dashboard['task_stats'];
$overdue = $dashboard['overdue'];
$tags = $dashboard['tags'];

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<section class="hero-card mb-4">
    <div>
        <p class="eyebrow mb-2">Vue d'ensemble</p>
        <h1 class="h3 mb-2">Bonjour, voici votre planning du <?= e(date('d/m/Y')) ?></h1>
        <p class="text-body-secondary mb-0">Architecture clean: vues légères, services applicatifs, repositories MySQL.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-light" href="events.php?action=create"><i class="bi bi-plus-circle me-1"></i>Ajouter un événement</a>
        <a class="btn btn-outline-light" href="calendar.php"><i class="bi bi-calendar3 me-1"></i>Ouvrir le calendrier</a>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <article class="metric-card">
            <div class="metric-icon bg-primary-subtle text-primary"><i class="bi bi-list-check"></i></div>
            <div>
                <div class="metric-label">Tâches terminées</div>
                <div class="metric-value"><?= (int) $taskStats['done'] ?> / <?= (int) $taskStats['total'] ?></div>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="metric-card">
            <div class="metric-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="metric-label">Progression</div>
                <div class="metric-value"><?= (int) $taskStats['percent'] ?>%</div>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="metric-card">
            <div class="metric-icon bg-warning-subtle text-warning"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="metric-label">Événements aujourd'hui</div>
                <div class="metric-value"><?= count($todayEvents) ?></div>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="metric-card">
            <div class="metric-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-octagon"></i></div>
            <div>
                <div class="metric-label">Tâches en retard</div>
                <div class="metric-value"><?= count($overdue) ?></div>
            </div>
        </article>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <section class="panel-card mb-4">
            <div class="panel-header">
                <h2 class="h5 mb-0"><i class="bi bi-calendar-day me-2"></i>Aujourd'hui</h2>
                <a class="btn btn-sm btn-outline-primary" href="events.php">Gérer</a>
            </div>
            <?php if ($todayEvents === []): ?>
                <p class="text-body-secondary mb-0">Aucun événement planifié aujourd'hui.</p>
            <?php else: ?>
                <div class="stack-list">
                    <?php foreach ($todayEvents as $event): ?>
                        <article class="event-row" style="--event-color: <?= e($event->color) ?>">
                            <div>
                                <h3 class="h6 mb-1"><?= e($event->title) ?></h3>
                                <p class="text-body-secondary mb-1 small"><?= e($event->description ?: 'Aucune description') ?></p>
                                <div class="small text-body-secondary">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= $event->startTime ? e(substr($event->startTime, 0, 5)) : 'Journée entière' ?>
                                    <?= $event->endTime ? ' - ' . e(substr($event->endTime, 0, 5)) : '' ?>
                                </div>
                            </div>
                            <a class="btn btn-sm btn-outline-primary" href="events.php?action=edit&id=<?= (int) $event->id ?>">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel-card">
            <div class="panel-header">
                <h2 class="h5 mb-0"><i class="bi bi-clock-history me-2"></i>Prochains éléments</h2>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Priorité</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($upcoming as $event): ?>
                        <tr>
                            <td>
                                <strong><?= e($event->title) ?></strong>
                                <div class="small text-body-secondary"><?= e($event->description ?: 'Sans description') ?></div>
                            </td>
                            <td><?= e(date('d/m/Y', strtotime($event->eventDate))) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $event->isTask ? 'warning' : 'primary' ?>"><?= $event->isTask ? 'Tâche' : 'Événement' ?></span>
                            </td>
                            <td><span class="badge bg-priority-<?= e($event->priority) ?>"><?= e(strtoupper($event->priority)) ?></span></td>
                            <td class="text-end"><a href="events.php?action=edit&id=<?= (int) $event->id ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="panel-card mb-4">
            <div class="panel-header">
                <h2 class="h5 mb-0"><i class="bi bi-list-task me-2"></i>Checklist</h2>
            </div>
            <div class="progress mb-3" role="progressbar" aria-label="task progress" aria-valuenow="<?= (int) $taskStats['percent'] ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: <?= (int) $taskStats['percent'] ?>%"></div>
            </div>
            <div class="stack-list compact">
                <?php foreach ($tasks as $task): ?>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold <?= $task->isDone ? 'text-decoration-line-through text-body-secondary' : '' ?>"><?= e($task->title) ?></div>
                            <div class="small text-body-secondary"><?= e(date('d/m/Y', strtotime($task->eventDate))) ?></div>
                        </div>
                        <span class="badge text-bg-<?= $task->isDone ? 'success' : 'secondary' ?>"><?= $task->isDone ? 'Fait' : 'Ouvert' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel-card">
            <div class="panel-header">
                <h2 class="h5 mb-0"><i class="bi bi-tags me-2"></i>Tags actifs</h2>
                <a href="tags.php" class="btn btn-sm btn-outline-primary">Modifier</a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <span class="tag-chip" style="--tag-color: <?= e($tag->color) ?>;"><?= e($tag->name) ?></span>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

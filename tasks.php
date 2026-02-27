<?php

declare(strict_types=1);

use App\Application\DTO\EventFilters;
use App\Application\Service\EventService;

$container = require __DIR__ . '/bootstrap/app.php';
$eventService = $container->get(EventService::class);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Session expirée.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_task'])) {
        $eventService->toggleTask((int) $_POST['id'], (bool) (int) $_POST['is_done']);
        header('Location: tasks.php?updated=1');
        exit;
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

$tasks = $eventService->list(new EventFilters(isTask: true, limit: 300));
$taskStats = $eventService->taskStats();

$pageTitle = 'Tâches';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-list-check me-2"></i>Tâches</h1>
        <p class="text-body-secondary mb-0">Vue focalisée sur les tâches, avec suivi d’avancement.</p>
    </div>
    <a href="events.php?action=create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Nouvelle tâche</a>
</div>

<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Statut de tâche mis à jour.</div>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger"><?= e($errorMessage) ?></div>
<?php endif; ?>

<section class="panel-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Progression globale</h2>
        <span class="fw-semibold"><?= (int) $taskStats['done'] ?> / <?= (int) $taskStats['total'] ?></span>
    </div>
    <div class="progress" role="progressbar" aria-label="task progress" aria-valuenow="<?= (int) $taskStats['percent'] ?>" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar" style="width: <?= (int) $taskStats['percent'] ?>%"></div>
    </div>
</section>

<section class="panel-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th width="80">Statut</th>
                <th>Tâche</th>
                <th>Date</th>
                <th>Priorité</th>
                <th class="text-end">Édition</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($tasks === []): ?>
                <tr><td colspan="5" class="text-center text-body-secondary py-4">Aucune tâche trouvée.</td></tr>
            <?php endif; ?>
            <?php foreach ($tasks as $task): ?>
                <tr class="<?= $task->isDone ? 'table-success-subtle' : '' ?>">
                    <td>
                        <form method="post">
                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="toggle_task" value="1">
                            <input type="hidden" name="id" value="<?= (int) $task->id ?>">
                            <input type="hidden" name="is_done" value="<?= $task->isDone ? '0' : '1' ?>">
                            <button class="btn btn-sm btn-<?= $task->isDone ? 'success' : 'outline-secondary' ?>">
                                <i class="bi <?= $task->isDone ? 'bi-check2-circle' : 'bi-circle' ?>"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="fw-semibold <?= $task->isDone ? 'text-decoration-line-through text-body-secondary' : '' ?>"><?= e($task->title) ?></div>
                        <div class="small text-body-secondary"><?= e($task->description ?: 'Sans description') ?></div>
                    </td>
                    <td><?= e(date('d/m/Y', strtotime($task->eventDate))) ?></td>
                    <td><span class="badge bg-priority-<?= e($task->priority) ?>"><?= e(strtoupper($task->priority)) ?></span></td>
                    <td class="text-end"><a href="events.php?action=edit&id=<?= (int) $task->id ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

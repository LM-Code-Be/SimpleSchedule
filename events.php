<?php

declare(strict_types=1);

use App\Application\DTO\EventFilters;
use App\Application\DTO\EventPayload;
use App\Application\Service\EventService;
use App\Application\Service\TagService;

$container = require __DIR__ . '/bootstrap/app.php';
$eventService = $container->get(EventService::class);
$tagService = $container->get(TagService::class);

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Session expirée. Rechargez la page.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
        $payload = EventPayload::fromArray($_POST);
        $eventService->save($payload);
        header('Location: events.php?saved=1');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
        $eventService->delete((int) $_POST['id']);
        header('Location: events.php?deleted=1');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_task'])) {
        $eventService->toggleTask((int) $_POST['id'], (bool) (int) $_POST['is_done']);
        header('Location: events.php?updated=1');
        exit;
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

$filters = EventFilters::fromArray($_GET);
$events = $eventService->list($filters);
$tags = $tagService->list();

$editingEvent = null;
if ($action === 'edit' && $id !== null) {
    $editingEvent = $eventService->get($id);
}

if ($action === 'create') {
    $editingEvent = null;
}

$pageTitle = 'Événements';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-calendar-event me-2"></i>Événements & tâches</h1>
        <p class="text-body-secondary mb-0">Gestion centralisée via les use-cases applicatifs.</p>
    </div>
    <a href="events.php?action=create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Créer</a>
</div>

<?php if (!empty($_GET['saved'])): ?>
    <div class="alert alert-success">Élément enregistré.</div>
<?php elseif (!empty($_GET['deleted'])): ?>
    <div class="alert alert-warning">Élément supprimé.</div>
<?php elseif (!empty($_GET['updated'])): ?>
    <div class="alert alert-info">Tâche mise à jour.</div>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger"><?= e($errorMessage) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-4">
        <section class="panel-card sticky-form">
            <div class="panel-header">
                <h2 class="h5 mb-0"><i class="bi bi-pencil-square me-2"></i><?= $editingEvent ? 'Modifier' : 'Créer' ?></h2>
            </div>
            <form method="post" class="vstack gap-3">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="save_event" value="1">
                <?php if ($editingEvent): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingEvent->id ?>">
                <?php endif; ?>

                <div>
                    <label class="form-label">Titre</label>
                    <input name="title" class="form-control" required value="<?= e($editingEvent?->title ?? '') ?>">
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($editingEvent?->description ?? '') ?></textarea>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Date</label>
                        <input type="date" name="event_date" class="form-control" required value="<?= e($editingEvent?->eventDate ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-3">
                        <label class="form-label">Début</label>
                        <input type="time" name="start_time" class="form-control" value="<?= e($editingEvent?->startTime ? substr($editingEvent->startTime, 0, 5) : '') ?>">
                    </div>
                    <div class="col-3">
                        <label class="form-label">Fin</label>
                        <input type="time" name="end_time" class="form-control" value="<?= e($editingEvent?->endTime ? substr($editingEvent->endTime, 0, 5) : '') ?>">
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Couleur</label>
                        <input type="color" name="color" class="form-control form-control-color" value="<?= e($editingEvent?->color ?? '#2463eb') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Priorité</label>
                        <select name="priority" class="form-select">
                            <?php $currentPriority = $editingEvent?->priority ?? 'normal'; ?>
                            <option value="low" <?= $currentPriority === 'low' ? 'selected' : '' ?>>Basse</option>
                            <option value="normal" <?= $currentPriority === 'normal' ? 'selected' : '' ?>>Normale</option>
                            <option value="high" <?= $currentPriority === 'high' ? 'selected' : '' ?>>Haute</option>
                        </select>
                    </div>
                </div>

                <div class="form-check form-switch">
                    <?php $isTask = $editingEvent?->isTask ?? false; ?>
                    <input class="form-check-input" type="checkbox" id="is_task" name="is_task" value="1" <?= $isTask ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_task">Créer comme tâche</label>
                </div>

                <div>
                    <label class="form-label">Tags</label>
                    <?php
                    $selectedTagIds = [];
                    if ($editingEvent) {
                        foreach ($editingEvent->tags as $tag) {
                            if ($tag->id !== null) {
                                $selectedTagIds[] = $tag->id;
                            }
                        }
                    }
                    ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <label class="tag-option" style="--tag-color: <?= e($tag->color) ?>">
                                <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag->id ?>" <?= in_array($tag->id, $selectedTagIds, true) ? 'checked' : '' ?>>
                                <span><?= e($tag->name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Enregistrer</button>
            </form>
        </section>
    </div>

    <div class="col-xl-8">
        <section class="panel-card mb-4">
            <form class="row g-2 align-items-end" method="get">
                <div class="col-md-4">
                    <label class="form-label">Recherche</label>
                    <input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Titre ou description">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="is_task">
                        <option value="">Tous</option>
                        <option value="0" <?= ($_GET['is_task'] ?? '') === '0' ? 'selected' : '' ?>>Événement</option>
                        <option value="1" <?= ($_GET['is_task'] ?? '') === '1' ? 'selected' : '' ?>>Tâche</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tag</label>
                    <select class="form-select" name="tag_id">
                        <option value="">Tous</option>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= (int) $tag->id ?>" <?= ($_GET['tag_id'] ?? '') === (string) $tag->id ? 'selected' : '' ?>><?= e($tag->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priorité</label>
                    <select class="form-select" name="priority">
                        <option value="">Toutes</option>
                        <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Basse</option>
                        <option value="normal" <?= ($_GET['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normale</option>
                        <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Haute</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </section>

        <section class="panel-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Priorité</th>
                        <th>Tags</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($events === []): ?>
                        <tr><td colspan="6" class="text-center text-body-secondary py-4">Aucun résultat.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($event->title) ?></div>
                                <div class="small text-body-secondary"><?= e($event->description ?: 'Sans description') ?></div>
                            </td>
                            <td>
                                <?= e(date('d/m/Y', strtotime($event->eventDate))) ?>
                                <?php if ($event->startTime): ?>
                                    <div class="small text-body-secondary"><?= e(substr($event->startTime, 0, 5)) ?><?= $event->endTime ? ' - ' . e(substr($event->endTime, 0, 5)) : '' ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $event->isTask ? 'warning' : 'primary' ?>"><?= $event->isTask ? 'Tâche' : 'Événement' ?></span>
                                <?php if ($event->isTask): ?>
                                    <div class="small mt-1">
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="toggle_task" value="1">
                                            <input type="hidden" name="id" value="<?= (int) $event->id ?>">
                                            <input type="hidden" name="is_done" value="<?= $event->isDone ? '0' : '1' ?>">
                                            <button class="btn btn-sm btn-outline-<?= $event->isDone ? 'success' : 'secondary' ?>"><?= $event->isDone ? 'Fait' : 'Marquer fait' ?></button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-priority-<?= e($event->priority) ?>"><?= e(strtoupper($event->priority)) ?></span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($event->tags as $tag): ?>
                                        <span class="tag-chip" style="--tag-color: <?= e($tag->color) ?>;"><?= e($tag->name) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="events.php?action=edit&id=<?= (int) $event->id ?>"><i class="bi bi-pencil"></i></a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cet élément ?');">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="delete_event" value="1">
                                    <input type="hidden" name="id" value="<?= (int) $event->id ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

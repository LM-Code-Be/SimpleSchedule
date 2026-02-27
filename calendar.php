<?php

declare(strict_types=1);

use App\Application\Service\TagService;

$container = require __DIR__ . '/bootstrap/app.php';
$tagService = $container->get(TagService::class);
$tags = $tagService->list();

$pageTitle = 'Calendrier';
require __DIR__ . '/includes/header.php';
?>

<link href="<?= $basePath ?>/assets/css/fullcalendar.css" rel="stylesheet">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-calendar3 me-2"></i>Calendrier</h1>
        <p class="text-body-secondary mb-0">Vue temps réel alimentée par l’API backend.</p>
    </div>
    <a class="btn btn-primary" href="events.php?action=create"><i class="bi bi-plus-circle me-1"></i>Ajouter</a>
</div>

<section class="panel-card mb-4">
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($tags as $tag): ?>
            <span class="tag-chip" style="--tag-color: <?= e($tag->color) ?>"><?= e($tag->name) ?></span>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel-card p-3">
    <div id="calendar"></div>
</section>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        return;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'fr',
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'api/get-events.php',
        eventClick(info) {
            window.location.href = `events.php?action=edit&id=${info.event.id}`;
        },
        eventDidMount(info) {
            const priority = info.event.extendedProps.priority || 'normal';
            info.el.classList.add(`fc-priority-${priority}`);
        }
    });

    calendar.render();
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>

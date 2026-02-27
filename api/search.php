<?php

declare(strict_types=1);

use App\Application\DTO\EventFilters;
use App\Application\Service\EventService;

$container = require __DIR__ . '/../bootstrap/app.php';
$eventService = $container->get(EventService::class);

header('Content-Type: application/json; charset=utf-8');

$query = trim((string) ($_GET['q'] ?? ''));
$events = $eventService->list(new EventFilters(search: $query, limit: 100));

echo json_encode(array_map(static function ($event): array {
    return [
        'id' => $event->id,
        'title' => $event->title,
        'description' => $event->description,
        'event_date' => $event->eventDate,
        'is_task' => $event->isTask,
        'priority' => $event->priority,
    ];
}, $events), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

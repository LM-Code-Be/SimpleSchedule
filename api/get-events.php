<?php

declare(strict_types=1);

use App\Application\DTO\EventFilters;
use App\Application\Service\EventService;

$container = require __DIR__ . '/../bootstrap/app.php';
$eventService = $container->get(EventService::class);

header('Content-Type: application/json; charset=utf-8');

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$events = $eventService->list(new EventFilters(fromDate: (string) $start, toDate: (string) $end, limit: 1200));

$data = array_map(static function ($event): array {
    $start = $event->eventDate . ($event->startTime ? 'T' . substr($event->startTime, 0, 8) : '');
    $end = $event->eventDate . ($event->endTime ? 'T' . substr($event->endTime, 0, 8) : '');

    return [
        'id' => $event->id,
        'title' => $event->title,
        'start' => $start,
        'end' => $event->endTime ? $end : null,
        'color' => $event->color,
        'extendedProps' => [
            'description' => $event->description,
            'is_task' => $event->isTask,
            'is_done' => $event->isDone,
            'priority' => $event->priority,
            'tags' => array_map(static fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ], $event->tags),
        ],
    ];
}, $events);

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

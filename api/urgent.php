<?php

declare(strict_types=1);

use App\Application\DTO\EventFilters;
use App\Application\Service\EventService;

$container = require __DIR__ . '/../bootstrap/app.php';
$eventService = $container->get(EventService::class);

header('Content-Type: application/json; charset=utf-8');

$hours = isset($_GET['hours']) ? max(1, min((int) $_GET['hours'], 48)) : 4;
$today = date('Y-m-d');
$events = $eventService->list(new EventFilters(fromDate: $today, toDate: $today, isTask: false, limit: 50));

$now = new DateTimeImmutable();
$limit = $now->modify('+' . $hours . ' hours');

$urgent = [];
foreach ($events as $event) {
    if ($event->startTime === null) {
        continue;
    }
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $event->eventDate . ' ' . $event->startTime);
    if ($dt instanceof DateTimeImmutable && $dt >= $now && $dt <= $limit) {
        $urgent[] = [
            'id' => $event->id,
            'title' => $event->title,
            'event_date' => $event->eventDate,
            'start_time' => $event->startTime,
        ];
    }
}

echo json_encode($urgent, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

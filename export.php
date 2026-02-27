<?php

declare(strict_types=1);

use App\Application\DTO\EventFilters;
use App\Application\Service\EventService;

$container = require __DIR__ . '/bootstrap/app.php';
$eventService = $container->get(EventService::class);
$events = $eventService->list(new EventFilters(limit: 1000));
$format = $_GET['format'] ?? 'json';

if ($format === 'ics') {
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="events.ics"');

    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//LM-Code SimpleSchedule//FR\r\n";

    foreach ($events as $event) {
        $uid = $event->id . '@simpleschedule.local';
        $dtStart = date('Ymd', strtotime($event->eventDate));
        $dtStart .= 'T' . ($event->startTime ? str_replace(':', '', substr($event->startTime, 0, 8)) : '000000');

        echo "BEGIN:VEVENT\r\n";
        echo "UID:$uid\r\n";
        echo "DTSTAMP:" . gmdate('Ymd\\THis\\Z') . "\r\n";
        echo "DTSTART:$dtStart\r\n";
        if ($event->endTime) {
            $dtEnd = date('Ymd', strtotime($event->eventDate)) . 'T' . str_replace(':', '', substr($event->endTime, 0, 8));
            echo "DTEND:$dtEnd\r\n";
        }
        echo 'SUMMARY:' . str_replace(["\r", "\n"], ' ', $event->title) . "\r\n";
        if ($event->description !== '') {
            echo 'DESCRIPTION:' . str_replace(["\r", "\n"], '\\n', $event->description) . "\r\n";
        }
        echo "END:VEVENT\r\n";
    }

    echo "END:VCALENDAR\r\n";
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="events.json"');

echo json_encode(array_map(static function ($event): array {
    return [
        'id' => $event->id,
        'title' => $event->title,
        'description' => $event->description,
        'event_date' => $event->eventDate,
        'start_time' => $event->startTime,
        'end_time' => $event->endTime,
        'color' => $event->color,
        'is_task' => $event->isTask,
        'is_done' => $event->isDone,
        'priority' => $event->priority,
        'status' => $event->status,
        'tags' => array_map(static fn ($tag): array => [
            'id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
        ], $event->tags),
    ];
}, $events), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

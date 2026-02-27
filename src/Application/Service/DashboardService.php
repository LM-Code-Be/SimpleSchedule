<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\EventFilters;

/**
 * Compose toutes les donnees de la home dashboard.
 * Evite de mettre de la logique metier dans index.php.
 */
final class DashboardService
{
    public function __construct(
        private readonly EventService $events,
        private readonly TagService $tags
    ) {
    }

    /** @return array<string, mixed> */
    public function data(): array
    {
        $today = date('Y-m-d');
        $todayEvents = $this->events->list(new EventFilters(fromDate: $today, toDate: $today, limit: 50));
        $upcoming = $this->events->list(new EventFilters(fromDate: $today, limit: 8));
        $tasks = $this->events->list(new EventFilters(isTask: true, limit: 12));

        $overdue = array_values(array_filter(
            $tasks,
            static fn ($event) => $event->isTask && !$event->isDone && $event->eventDate < date('Y-m-d')
        ));

        return [
            'today_events' => $todayEvents,
            'upcoming' => $upcoming,
            'tasks' => $tasks,
            'task_stats' => $this->events->taskStats(),
            'tags' => $this->tags->list(),
            'overdue' => $overdue,
        ];
    }
}

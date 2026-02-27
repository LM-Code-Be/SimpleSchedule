<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Repository\EventRepositoryInterface;

final class StatsService
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
        private readonly TagService $tags
    ) {
    }

    /** @return array<string, mixed> */
    public function data(): array
    {
        return [
            'weekly' => $this->events->weeklyStats(),
            'monthly' => $this->events->monthlyStats(6),
            'priority' => $this->events->priorityStats(),
            'tags' => $this->tags->usageStats(),
        ];
    }
}

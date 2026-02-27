<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Application\DTO\EventFilters;
use App\Domain\Entity\Event;

interface EventRepositoryInterface
{
    /** @return Event[] */
    public function findAll(EventFilters $filters): array;

    public function findById(int $id): ?Event;

    public function save(Event $event): Event;

    public function delete(int $id): void;

    public function updateTaskState(int $id, bool $isDone): void;

    public function refreshStatuses(): void;

    /** @return array<int, array<string, mixed>> */
    public function weeklyStats(): array;

    /** @return array<int, array<string, mixed>> */
    public function monthlyStats(int $months = 6): array;

    /** @return array<int, array<string, mixed>> */
    public function priorityStats(): array;

    /** @return array<string, int> */
    public function taskStats(): array;
}

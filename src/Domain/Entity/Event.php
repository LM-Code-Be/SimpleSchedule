<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final class Event
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $description,
        public string $eventDate,
        public ?string $startTime,
        public ?string $endTime,
        public string $color,
        public bool $isTask,
        public bool $isDone,
        public string $priority,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
        /** @var Tag[] */
        public array $tags = []
    ) {
    }
}

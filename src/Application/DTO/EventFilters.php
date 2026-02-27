<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class EventFilters
{
    public function __construct(
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?string $search = null,
        public ?bool $isTask = null,
        public ?bool $isDone = null,
        public ?int $tagId = null,
        public ?string $priority = null,
        public int $limit = 200
    ) {
    }

    /** @param array<string, mixed> $input */
    public static function fromArray(array $input): self
    {
        $isTask = array_key_exists('is_task', $input) && $input['is_task'] !== ''
            ? (bool) (int) $input['is_task']
            : null;

        $isDone = array_key_exists('is_done', $input) && $input['is_done'] !== ''
            ? (bool) (int) $input['is_done']
            : null;

        return new self(
            $input['from'] ?? null,
            $input['to'] ?? null,
            isset($input['q']) ? trim((string) $input['q']) : null,
            $isTask,
            $isDone,
            isset($input['tag_id']) && $input['tag_id'] !== '' ? (int) $input['tag_id'] : null,
            isset($input['priority']) && $input['priority'] !== '' ? (string) $input['priority'] : null,
            isset($input['limit']) ? max(1, min((int) $input['limit'], 500)) : 200
        );
    }
}

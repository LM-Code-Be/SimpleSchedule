<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO d'ecriture pour creation/mise a jour d'un evenement.
 * Ce DTO est volontairement proche des champs de formulaire/API.
 */
final class EventPayload
{
    /** @param int[] $tagIds */
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
        public array $tagIds
    ) {
    }

    /**
     * Construit un payload normalise depuis un formulaire ou JSON API.
     *
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input): self
    {
        $tagIds = array_map('intval', $input['tag_ids'] ?? $input['tags'] ?? []);

        return new self(
            isset($input['id']) && $input['id'] !== '' ? (int) $input['id'] : null,
            trim((string) ($input['title'] ?? '')),
            trim((string) ($input['description'] ?? '')),
            (string) ($input['event_date'] ?? $input['date'] ?? date('Y-m-d')),
            ($input['start_time'] ?? '') !== '' ? (string) $input['start_time'] : null,
            ($input['end_time'] ?? '') !== '' ? (string) $input['end_time'] : null,
            (string) ($input['color'] ?? '#2463eb'),
            isset($input['is_task']) ? (bool) (int) $input['is_task'] : isset($input['task']),
            isset($input['is_done']) ? (bool) (int) $input['is_done'] : false,
            (string) ($input['priority'] ?? 'normal'),
            array_values(array_filter($tagIds, static fn (int $v): bool => $v > 0))
        );
    }
}

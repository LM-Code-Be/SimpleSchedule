<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\EventFilters;
use App\Application\DTO\EventPayload;
use App\Domain\Entity\Event;
use App\Domain\Entity\Tag;
use App\Domain\Repository\EventRepositoryInterface;
use App\Domain\Repository\TagRepositoryInterface;
use InvalidArgumentException;

/**
 * Use-case principal pour le cycle de vie des evenements et taches.
 * Centralise validations metier et orchestration repository.
 */
final class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
        private readonly TagRepositoryInterface $tags
    ) {
    }

    /** @return Event[] */
    public function list(EventFilters $filters): array
    {
        // Le statut temporel est recalculé avant toute lecture utilisateur.
        $this->events->refreshStatuses();

        return $this->events->findAll($filters);
    }

    /** Lit un evenement complet (avec tags). */
    public function get(int $id): ?Event
    {
        return $this->events->findById($id);
    }

    /**
     * Valide les donnees d'entree et persiste l'evenement.
     * Les tags inconnus sont ignores pour eviter une erreur SQL.
     */
    public function save(EventPayload $payload): Event
    {
        $title = trim($payload->title);
        if ($title === '') {
            throw new InvalidArgumentException('Le titre est obligatoire.');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payload->eventDate)) {
            throw new InvalidArgumentException('Le format de date est invalide.');
        }

        if (!in_array($payload->priority, ['low', 'normal', 'high'], true)) {
            throw new InvalidArgumentException('La priorité est invalide.');
        }

        $knownTags = [];
        foreach ($this->tags->findAll() as $tag) {
            $knownTags[$tag->id ?? 0] = $tag;
        }

        $tagEntities = [];
        foreach ($payload->tagIds as $tagId) {
            if (!isset($knownTags[$tagId])) {
                continue;
            }
            /** @var Tag $tag */
            $tag = $knownTags[$tagId];
            $tagEntities[] = $tag;
        }

        $event = new Event(
            $payload->id,
            $title,
            $payload->description,
            $payload->eventDate,
            $payload->isTask ? null : $payload->startTime,
            $payload->isTask ? null : $payload->endTime,
            $payload->color,
            $payload->isTask,
            $payload->isDone,
            $payload->priority,
            'pending',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            $tagEntities
        );

        return $this->events->save($event);
    }

    public function delete(int $id): void
    {
        $this->events->delete($id);
    }

    /** Marque une tache comme faite/non faite. */
    public function toggleTask(int $id, bool $isDone): void
    {
        $this->events->updateTaskState($id, $isDone);
    }

    /**
     * Fournit les KPI taches pour dashboard et page tasks.
     *
     * @return array<string, int>
     */
    public function taskStats(): array
    {
        return $this->events->taskStats();
    }
}

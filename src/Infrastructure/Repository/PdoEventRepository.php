<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\DTO\EventFilters;
use App\Domain\Entity\Event;
use App\Domain\Entity\Tag;
use App\Domain\Repository\EventRepositoryInterface;
use PDO;

/**
 * Implementation SQL du repository Event.
 * Toute la logique de requetage est concentree ici.
 */
final class PdoEventRepository implements EventRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(EventFilters $filters): array
    {
        // Requete dynamique: seuls les filtres renseignes sont ajoutes.
        $sql = 'SELECT DISTINCT e.* FROM events e';
        $where = [];
        $params = [];

        if ($filters->tagId !== null) {
            $sql .= ' INNER JOIN event_tags etf ON etf.event_id = e.id';
            $where[] = 'etf.tag_id = :tag_id';
            $params['tag_id'] = $filters->tagId;
        }

        if ($filters->fromDate !== null) {
            $where[] = 'e.event_date >= :from_date';
            $params['from_date'] = $filters->fromDate;
        }

        if ($filters->toDate !== null) {
            $where[] = 'e.event_date <= :to_date';
            $params['to_date'] = $filters->toDate;
        }

        if ($filters->search !== null && $filters->search !== '') {
            $where[] = '(e.title LIKE :search OR e.description LIKE :search)';
            $params['search'] = '%' . $filters->search . '%';
        }

        if ($filters->isTask !== null) {
            $where[] = 'e.is_task = :is_task';
            $params['is_task'] = $filters->isTask ? 1 : 0;
        }

        if ($filters->isDone !== null) {
            $where[] = 'e.is_done = :is_done';
            $params['is_done'] = $filters->isDone ? 1 : 0;
        }

        if ($filters->priority !== null && in_array($filters->priority, ['low', 'normal', 'high'], true)) {
            $where[] = 'e.priority = :priority';
            $params['priority'] = $filters->priority;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY e.event_date ASC, COALESCE(e.start_time, "23:59:59") ASC, e.id DESC LIMIT :row_limit';

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':row_limit', $filters->limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if ($rows === []) {
            return [];
        }

        $tagsByEvent = $this->loadTagsForEvents(array_map(static fn (array $row): int => (int) $row['id'], $rows));

        return array_map(function (array $row) use ($tagsByEvent): Event {
            $eventId = (int) $row['id'];
            return $this->hydrateEvent($row, $tagsByEvent[$eventId] ?? []);
        }, $rows);
    }

    public function findById(int $id): ?Event
    {
        $stmt = $this->pdo->prepare('SELECT * FROM events WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $tagsByEvent = $this->loadTagsForEvents([$id]);

        return $this->hydrateEvent($row, $tagsByEvent[$id] ?? []);
    }

    public function save(Event $event): Event
    {
        // Transaction unique: event + table pivot event_tags.
        $this->pdo->beginTransaction();
        try {
            if ($event->id === null) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO events (title, description, event_date, start_time, end_time, color, is_task, is_done, priority)
                     VALUES (:title, :description, :event_date, :start_time, :end_time, :color, :is_task, :is_done, :priority)'
                );

                $stmt->execute([
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->eventDate,
                    'start_time' => $event->startTime,
                    'end_time' => $event->endTime,
                    'color' => $event->color,
                    'is_task' => $event->isTask ? 1 : 0,
                    'is_done' => $event->isDone ? 1 : 0,
                    'priority' => $event->priority,
                ]);

                $eventId = (int) $this->pdo->lastInsertId();
            } else {
                $eventId = $event->id;
                $stmt = $this->pdo->prepare(
                    'UPDATE events
                     SET title = :title,
                         description = :description,
                         event_date = :event_date,
                         start_time = :start_time,
                         end_time = :end_time,
                         color = :color,
                         is_task = :is_task,
                         is_done = :is_done,
                         priority = :priority
                     WHERE id = :id'
                );

                $stmt->execute([
                    'id' => $eventId,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->eventDate,
                    'start_time' => $event->startTime,
                    'end_time' => $event->endTime,
                    'color' => $event->color,
                    'is_task' => $event->isTask ? 1 : 0,
                    'is_done' => $event->isDone ? 1 : 0,
                    'priority' => $event->priority,
                ]);

                $this->pdo->prepare('DELETE FROM event_tags WHERE event_id = :event_id')->execute([
                    'event_id' => $eventId,
                ]);
            }

            if ($event->tags !== []) {
                $insertTag = $this->pdo->prepare('INSERT INTO event_tags (event_id, tag_id) VALUES (:event_id, :tag_id)');
                foreach ($event->tags as $tag) {
                    if ($tag->id === null) {
                        continue;
                    }
                    $insertTag->execute([
                        'event_id' => $eventId,
                        'tag_id' => $tag->id,
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $saved = $this->findById($eventId);

        if ($saved === null) {
            throw new \RuntimeException('Event save failed.');
        }

        return $saved;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM events WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function updateTaskState(int $id, bool $isDone): void
    {
        $stmt = $this->pdo->prepare('UPDATE events SET is_done = :is_done WHERE id = :id AND is_task = 1');
        $stmt->execute([
            'id' => $id,
            'is_done' => $isDone ? 1 : 0,
        ]);
    }

    public function refreshStatuses(): void
    {
        // Le statut est derive des bornes temporelles de l'evenement.
        $this->pdo->exec(
            "UPDATE events SET status = CASE
                WHEN CONCAT(event_date, ' ', COALESCE(end_time, '23:59:59')) < NOW() THEN 'past'
                WHEN CONCAT(event_date, ' ', COALESCE(start_time, '00:00:00')) <= NOW()
                     AND CONCAT(event_date, ' ', COALESCE(end_time, '23:59:59')) >= NOW() THEN 'current'
                ELSE 'pending'
            END"
        );
    }

    public function weeklyStats(): array
    {
        return $this->pdo->query(
            'SELECT DATE_FORMAT(event_date, "%Y-%m-%d") AS day_key, COUNT(*) AS total
             FROM events
             WHERE event_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
             GROUP BY day_key
             ORDER BY day_key ASC'
        )->fetchAll();
    }

    public function monthlyStats(int $months = 6): array
    {
        $months = max(1, min($months, 24));

        $stmt = $this->pdo->prepare(
            'SELECT DATE_FORMAT(event_date, "%Y-%m") AS month_key, COUNT(*) AS total
             FROM events
             WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY month_key
             ORDER BY month_key ASC'
        );
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function priorityStats(): array
    {
        return $this->pdo->query(
            'SELECT priority, COUNT(*) AS total
             FROM events
             GROUP BY priority'
        )->fetchAll();
    }

    public function taskStats(): array
    {
        $total = (int) $this->pdo->query('SELECT COUNT(*) FROM events WHERE is_task = 1')->fetchColumn();
        $done = (int) $this->pdo->query('SELECT COUNT(*) FROM events WHERE is_task = 1 AND is_done = 1')->fetchColumn();

        return [
            'total' => $total,
            'done' => $done,
            'remaining' => max(0, $total - $done),
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
        ];
    }

    /** @param int[] $eventIds
     *  @return array<int, Tag[]>
     */
    private function loadTagsForEvents(array $eventIds): array
    {
        // Evite N+1 queries en chargeant tous les tags d'un lot d'evenements.
        if ($eventIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT et.event_id, t.id, t.name, t.color
             FROM event_tags et
             INNER JOIN tags t ON t.id = et.tag_id
             WHERE et.event_id IN ($placeholders)
             ORDER BY t.name ASC"
        );
        $stmt->execute($eventIds);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $eventId = (int) $row['event_id'];
            $map[$eventId] ??= [];
            $map[$eventId][] = new Tag((int) $row['id'], (string) $row['name'], (string) $row['color']);
        }

        return $map;
    }

    /** @param Tag[] $tags */
    private function hydrateEvent(array $row, array $tags): Event
    {
        // Conversion ligne SQL -> entite domaine immutable.
        return new Event(
            id: (int) $row['id'],
            title: (string) $row['title'],
            description: (string) ($row['description'] ?? ''),
            eventDate: (string) $row['event_date'],
            startTime: $row['start_time'] !== null ? (string) $row['start_time'] : null,
            endTime: $row['end_time'] !== null ? (string) $row['end_time'] : null,
            color: (string) ($row['color'] ?? '#2463eb'),
            isTask: (bool) $row['is_task'],
            isDone: (bool) $row['is_done'],
            priority: (string) ($row['priority'] ?? 'normal'),
            status: (string) ($row['status'] ?? 'pending'),
            createdAt: (string) ($row['created_at'] ?? date('Y-m-d H:i:s')),
            updatedAt: (string) ($row['updated_at'] ?? date('Y-m-d H:i:s')),
            tags: $tags,
        );
    }
}

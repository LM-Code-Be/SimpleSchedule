<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use PDO;

final class PdoTagRepository implements TagRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $rows = $this->pdo->query('SELECT id, name, color FROM tags ORDER BY name ASC')->fetchAll();

        return array_map(
            static fn (array $row): Tag => new Tag((int) $row['id'], (string) $row['name'], (string) $row['color']),
            $rows
        );
    }

    public function findById(int $id): ?Tag
    {
        $stmt = $this->pdo->prepare('SELECT id, name, color FROM tags WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Tag((int) $row['id'], (string) $row['name'], (string) $row['color']);
    }

    public function save(Tag $tag): Tag
    {
        if ($tag->id === null) {
            $stmt = $this->pdo->prepare('INSERT INTO tags (name, color) VALUES (:name, :color)');
            $stmt->execute([
                'name' => $tag->name,
                'color' => $tag->color,
            ]);

            return new Tag((int) $this->pdo->lastInsertId(), $tag->name, $tag->color);
        }

        $stmt = $this->pdo->prepare('UPDATE tags SET name = :name, color = :color WHERE id = :id');
        $stmt->execute([
            'id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
        ]);

        return $tag;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM tags WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function usageStats(): array
    {
        return $this->pdo->query(
            'SELECT t.id, t.name, t.color, COUNT(et.event_id) AS usage_count
             FROM tags t
             LEFT JOIN event_tags et ON et.tag_id = t.id
             GROUP BY t.id, t.name, t.color
             ORDER BY usage_count DESC, t.name ASC'
        )->fetchAll();
    }
}

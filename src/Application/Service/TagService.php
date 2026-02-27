<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use InvalidArgumentException;

final class TagService
{
    public function __construct(private readonly TagRepositoryInterface $tags)
    {
    }

    /** @return Tag[] */
    public function list(): array
    {
        return $this->tags->findAll();
    }

    public function get(int $id): ?Tag
    {
        return $this->tags->findById($id);
    }

    public function save(?int $id, string $name, string $color): Tag
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Le nom du tag est obligatoire.');
        }

        $color = trim($color);
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            throw new InvalidArgumentException('La couleur du tag est invalide.');
        }

        return $this->tags->save(new Tag($id, $name, strtolower($color)));
    }

    public function delete(int $id): void
    {
        $this->tags->delete($id);
    }

    /** @return array<int, array<string, mixed>> */
    public function usageStats(): array
    {
        return $this->tags->usageStats();
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Tag;

interface TagRepositoryInterface
{
    /** @return Tag[] */
    public function findAll(): array;

    public function findById(int $id): ?Tag;

    public function save(Tag $tag): Tag;

    public function delete(int $id): void;

    /** @return array<int, array<string, mixed>> */
    public function usageStats(): array;
}

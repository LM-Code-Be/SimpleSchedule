<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Tag;

/**
 * Contrat d'acces aux tags.
 */
interface TagRepositoryInterface
{
    /** @return Tag[] Liste complete des tags tries par nom. */
    public function findAll(): array;

    /** Retourne un tag par id ou null si absent. */
    public function findById(int $id): ?Tag;

    /** Cree ou met a jour un tag. */
    public function save(Tag $tag): Tag;

    /** Supprime un tag par id. */
    public function delete(int $id): void;

    /**
     * Retourne le volume d'utilisation de chaque tag.
     *
     * @return array<int, array<string, mixed>>
     */
    public function usageStats(): array;
}

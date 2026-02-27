<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use InvalidArgumentException;

/**
 * Use-case de gestion des tags.
 */
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

    /** Retourne un tag pour l'ecran d'edition. */
    public function get(int $id): ?Tag
    {
        return $this->tags->findById($id);
    }

    /** Valide et persiste un tag. */
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

    /**
     * Retourne les stats d'usage de tags pour la page stats.
     *
     * @return array<int, array<string, mixed>>
     */
    public function usageStats(): array
    {
        return $this->tags->usageStats();
    }
}

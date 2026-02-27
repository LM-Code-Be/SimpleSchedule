<?php

declare(strict_types=1);

namespace App\Domain\Entity;

/**
 * Entite de categorisation des evenements/taches.
 */
final class Tag
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $color
    ) {
    }
}

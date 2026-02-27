<?php

declare(strict_types=1);

namespace App\Shared;

use RuntimeException;

/**
 * Mini container DI.
 * - set(): enregistre une factory
 * - get(): instancie en lazy puis met en cache
 */
final class Container
{
    /** @var array<string, mixed> */
    private array $entries = [];

    /** @var array<string, callable(self): mixed> */
    private array $factories = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        // Service deja instancie: retour direct.
        if (array_key_exists($id, $this->entries)) {
            return $this->entries[$id];
        }

        if (!array_key_exists($id, $this->factories)) {
            throw new RuntimeException("Service '$id' is not registered.");
        }

        $this->entries[$id] = ($this->factories[$id])($this);

        return $this->entries[$id];
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Application\DTO\EventFilters;
use App\Domain\Entity\Event;

/**
 * Contrat d'acces aux evenements persistes.
 * La couche Application depend de ce contrat, jamais d'une implementation SQL concrete.
 */
interface EventRepositoryInterface
{
    /**
     * Liste les evenements selon les filtres applicatifs.
     *
     * @return Event[]
     */
    public function findAll(EventFilters $filters): array;

    /** Retourne un evenement complet avec ses tags. */
    public function findById(int $id): ?Event;

    /** Cree ou met a jour un evenement et retourne l'etat persiste. */
    public function save(Event $event): Event;

    /** Supprime un evenement par son identifiant. */
    public function delete(int $id): void;

    /** Met a jour l'etat done/not-done d'une tache. */
    public function updateTaskState(int $id, bool $isDone): void;

    /** Recalcule le statut temporel pending/current/past. */
    public function refreshStatuses(): void;

    /**
     * Retourne la charge journaliere des 7 derniers jours.
     *
     * @return array<int, array<string, mixed>>
     */
    public function weeklyStats(): array;

    /**
     * Retourne la charge mensuelle sur N mois glissants.
     *
     * @return array<int, array<string, mixed>>
     */
    public function monthlyStats(int $months = 6): array;

    /**
     * Retourne la repartition des niveaux de priorite.
     *
     * @return array<int, array<string, mixed>>
     */
    public function priorityStats(): array;

    /**
     * Retourne les KPI taches (total/done/remaining/percent).
     *
     * @return array<string, int>
     */
    public function taskStats(): array;
}

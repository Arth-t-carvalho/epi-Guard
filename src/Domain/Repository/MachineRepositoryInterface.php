<?php

namespace epiGuard\Domain\Repository;

use epiGuard\Domain\Entity\Machine;

interface MachineRepositoryInterface
{
    public function findById(int $id): ?Machine;
    public function findBySectorId(int $sectorId): array;
    public function save(Machine $machine): void;
    public function delete(int $id): bool;
}

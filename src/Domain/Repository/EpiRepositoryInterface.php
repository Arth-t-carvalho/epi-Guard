<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Repository;

use Facchini\Domain\Entity\EpiItem;

interface EpiRepositoryInterface
{
    public function findById(int $id): ?EpiItem;

    /**
     * @return EpiItem[]
     */
    public function findAll(): array;

    public function save(EpiItem $epiItem): void;

    public function update(EpiItem $epiItem): void;

    public function delete(EpiItem $epiItem): void;
}

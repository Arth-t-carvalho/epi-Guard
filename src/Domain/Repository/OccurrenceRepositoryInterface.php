<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Repository;

use Facchini\Domain\Entity\Occurrence;

interface OccurrenceRepositoryInterface
{
    public function findById(int $id): ?Occurrence;

    /**
     * @return Occurrence[]
     */
    public function findAll(): array;

    /**
     * @param int $employeeId
     * @return Occurrence[]
     */
    public function findByEmployeeId(int $employeeId): array;

    /**
     * @param string $status
     * @return Occurrence[]
     */
    public function findByStatus(string $status): array;
    
    public function countDaily(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countWeekly(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countMonthly(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countUniqueStudentsDaily(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countUniqueStudentsWeekly(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countUniqueStudentsMonthly(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countUniqueStudentsYearly(\DateTimeInterface $date, ?array $sectorIds = null): int;
    
    public function countRange(\DateTimeInterface $start, \DateTimeInterface $end, ?array $sectorIds = null): int;
    
    public function countUniqueStudentsRange(\DateTimeInterface $start, \DateTimeInterface $end, ?array $sectorIds = null): int;

    /**
     * Retorna array com contagens por mês para o gráfico de barras
     * Format: ['capacete' => [val1, val2...], 'oculos' => [...], 'total' => [...]]
     */
    public function getMonthlyInfractionStats(int $year, ?array $sectorIds = null): array;

    /**
     * Retorna array com distribuição por EPI para o gráfico de rosca
     */
    public function getInfractionDistributionByEpi(?array $sectorIds = null): array;

    public function findInfractions(array $filters = []): array;

    public function save(Occurrence $occurrence): void;

    public function update(Occurrence $occurrence): void;

    public function delete(Occurrence $occurrence): void;
}

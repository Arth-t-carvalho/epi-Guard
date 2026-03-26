<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Application\DTO\Response\DashboardSummary;
use epiGuard\Domain\Repository\OccurrenceRepositoryInterface;
use epiGuard\Domain\Repository\EmployeeRepositoryInterface;
use epiGuard\Domain\ValueObject\OccurrenceStatus;

class DashboardService
{
    private EmployeeRepositoryInterface $employeeRepository;
    private OccurrenceRepositoryInterface $occurrenceRepository;

    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        OccurrenceRepositoryInterface $occurrenceRepository
        )
    {
        $this->employeeRepository = $employeeRepository;
        $this->occurrenceRepository = $occurrenceRepository;
    }

    public function getSummary(): DashboardSummary
    {
        $employees = $this->employeeRepository->findAll();
        $occurrences = $this->occurrenceRepository->findAll();

        $openOccurrencesCount = 0;
        $resolvedOccurrencesCount = 0;

        foreach ($occurrences as $occurrence) {
            // Ajustado para usar o tipo ou status real do banco
            if ($occurrence->getType()->getValue() === 'INFRACAO') {
                $openOccurrencesCount++;
            } else {
                $resolvedOccurrencesCount++;
            }
        }

        return new DashboardSummary(
            count($employees),
            count($occurrences),
            $openOccurrencesCount,
            $resolvedOccurrencesCount
            );
    }

    public function getChartData(null|int|array $sectorIds = null): array
    {
        $now = new \DateTimeImmutable();
        $year = (int)$now->format('Y');

        if (is_int($sectorIds)) {
            $sectorIds = [$sectorIds];
        }

        $barData = $this->occurrenceRepository->getMonthlyInfractionStats($year, $sectorIds);

        return [
            'status' => 'success',
            'summary' => [
                'today' => $this->occurrenceRepository->countDaily($now, $sectorIds),
                'week' => $this->occurrenceRepository->countWeekly($now, $sectorIds),
                'month' => $this->occurrenceRepository->countMonthly($now, $sectorIds),
                'total_students' => count($this->employeeRepository->findAll())
            ],
            'bar' => $barData['stats'],
            'allowed_epis' => $barData['allowed_epis'],
            'doughnut' => $this->occurrenceRepository->getInfractionDistributionByEpi($sectorIds)
        ];
    }
}

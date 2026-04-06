<?php
declare(strict_types = 1)
;

namespace Facchini\Application\Service;

use Facchini\Application\DTO\Response\DashboardSummary;
use Facchini\Domain\Repository\OccurrenceRepositoryInterface;
use Facchini\Domain\Repository\EmployeeRepositoryInterface;
use Facchini\Domain\ValueObject\OccurrenceStatus;

class DashboardService
{
    private EmployeeRepositoryInterface $employeeRepository;
    private OccurrenceRepositoryInterface $occurrenceRepository;
    private \Facchini\Domain\Repository\UserRepositoryInterface $userRepository;

    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        OccurrenceRepositoryInterface $occurrenceRepository,
        \Facchini\Domain\Repository\UserRepositoryInterface $userRepository
        )
    {
        $this->employeeRepository = $employeeRepository;
        $this->occurrenceRepository = $occurrenceRepository;
        $this->userRepository = $userRepository;
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
                'students_today' => $this->occurrenceRepository->countUniqueStudentsDaily($now, $sectorIds),
                'students_week' => $this->occurrenceRepository->countUniqueStudentsWeekly($now, $sectorIds),
                'students_month' => $this->occurrenceRepository->countUniqueStudentsMonthly($now, $sectorIds),
                'students_year' => $this->occurrenceRepository->countUniqueStudentsYearly($now, $sectorIds),
                'total_students' => $this->employeeRepository->countAll($sectorIds)
            ],
            'bar' => $barData['stats'],
            'allowed_epis' => $barData['allowed_epis'],
            'epi_colors' => $barData['epi_colors'],
            'doughnut' => $this->occurrenceRepository->getInfractionDistributionByEpi($sectorIds),
            'chart_style' => $this->getChartStyleForCurrentUser()
        ];
    }

    private function getChartStyleForCurrentUser(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $user = $this->userRepository->findById((int)$userId);
            if ($user) {
                return $user->getChartPreference();
            }
        }
        return 'bar';
    }
}

<?php
declare(strict_types=1)
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
    ) {
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

    public function getChartData(null|int|array $sectorIds = null, ?\DateTimeInterface $referenceDate = null): array
    {
        $ref = $referenceDate ? \DateTimeImmutable::createFromInterface($referenceDate) : new \DateTimeImmutable();
        $year = (int) $ref->format('Y');

        if (is_int($sectorIds)) {
            $sectorIds = [$sectorIds];
        }

        $barData = $this->occurrenceRepository->getMonthlyInfractionStats($year, $sectorIds);

        // --- Cálculos de Tendência ---

        // 1. Diário (vs Ontem)
        $todayCount = $this->occurrenceRepository->countDaily($ref, $sectorIds);
        $yesterday = $ref->modify('-1 day');
        $yesterdayCount = $this->occurrenceRepository->countDaily($yesterday, $sectorIds);
        $todayTrend = $this->calculateTrend($todayCount, $yesterdayCount);

        // 2. Semanal (vs Semana Passada)
        // YEARWEEK(ref, 1) pega a semana ISO
        $weekCount = $this->occurrenceRepository->countWeekly($ref, $sectorIds);
        $lastWeek = $ref->modify('-7 days');
        $lastWeekCount = $this->occurrenceRepository->countWeekly($lastWeek, $sectorIds);
        $weekTrend = $this->calculateTrend($weekCount, $lastWeekCount);

        // 3. Mensal (vs Mês Passado)
        $monthCount = $this->occurrenceRepository->countMonthly($ref, $sectorIds);
        $lastMonth = $ref->modify('-1 month');
        $lastMonthCount = $this->occurrenceRepository->countMonthly($lastMonth, $sectorIds);
        $monthTrend = $this->calculateTrend($monthCount, $lastMonthCount);

        return [
            'status' => 'success',
            'summary' => [
                'today' => $todayCount,
                'yesterday' => $yesterdayCount,
                'today_trend' => $todayTrend,
                'week' => $weekCount,
                'last_week' => $lastWeekCount,
                'week_trend' => $weekTrend,
                'month' => $monthCount,
                'last_month' => $lastMonthCount,
                'month_trend' => $monthTrend,
                'students_today' => $this->occurrenceRepository->countUniqueStudentsDaily($ref, $sectorIds),
                'previous_students_today' => $this->occurrenceRepository->countUniqueStudentsDaily($yesterday, $sectorIds),
                'students_week' => $this->occurrenceRepository->countUniqueStudentsWeekly($ref, $sectorIds),
                'previous_students_week' => $this->occurrenceRepository->countUniqueStudentsWeekly($lastWeek, $sectorIds),
                'students_month' => $this->occurrenceRepository->countUniqueStudentsMonthly($ref, $sectorIds),
                'previous_students_month' => $this->occurrenceRepository->countUniqueStudentsMonthly($lastMonth, $sectorIds),
                'students_year' => $this->occurrenceRepository->countUniqueStudentsYearly($ref, $sectorIds),
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
            $user = $this->userRepository->findById((int) $userId);
            if ($user) {
                return $user->getChartPreference();
            }
        }
        return 'bar';
    }

    private function calculateTrend(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
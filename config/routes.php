<?php

use Facchini\Presentation\Controller\AuthController;
use Facchini\Presentation\Controller\DashboardController;
use Facchini\Presentation\Controller\InfractionController;
use Facchini\Presentation\Controller\ManagementController;
use Facchini\Presentation\Controller\OccurrenceController;

return [
    '/login' => [AuthController::class, 'index'],
    '/dashboard' => [DashboardController::class, 'index'],
    '/api/charts' => [\Facchini\Presentation\Controller\Api\ChartApiController::class, 'index'],
    '/api/calendar' => [\Facchini\Presentation\Controller\Api\OccurrenceApiController::class, 'calendar'],
    '/api/modal_details' => [\Facchini\Presentation\Controller\Api\OccurrenceApiController::class, 'details'],
    '/api/check_notificacoes' => [\Facchini\Presentation\Controller\Api\NotificationApiController::class, 'check'],
    '/infractions' => [InfractionController::class, 'index'],
    '/occurrences' => [OccurrenceController::class, 'index'],
    '/management/departments' => [ManagementController::class, 'departments'],
    '/management/employees' => [ManagementController::class, 'employees'],
    '/management/instructors' => [ManagementController::class, 'instructors'],
    '/api/departments' => [\Facchini\Presentation\Controller\Api\DepartmentApiController::class, 'index'],
    '/api/departments/create' => [\Facchini\Presentation\Controller\Api\DepartmentApiController::class, 'create'],
    '/api/departments/update' => [\Facchini\Presentation\Controller\Api\DepartmentApiController::class, 'update'],
    '/api/departments/delete' => [\Facchini\Presentation\Controller\Api\DepartmentApiController::class, 'delete'],
    '/api/departments/employees' => [\Facchini\Presentation\Controller\Api\DepartmentApiController::class, 'employees'],
    '/api/machines/list' => [\Facchini\Presentation\Controller\Api\MachineApiController::class, 'list'],
    '/api/machines/create' => [\Facchini\Presentation\Controller\Api\MachineApiController::class, 'create'],
    '/api/machines/delete' => [\Facchini\Presentation\Controller\Api\MachineApiController::class, 'delete'],
    '/api/epis/list' => [\Facchini\Presentation\Controller\Api\MachineApiController::class, 'listEpis'],
    '/api/occurrence/store' => [\Facchini\Presentation\Controller\Api\OccurrenceStoreApiController::class, 'store'],
    '/api/occurrence/hide' => [\Facchini\Presentation\Controller\Api\OccurrenceApiController::class, 'hide'],
    '/api/occurrence/toggle-favorite' => [\Facchini\Presentation\Controller\Api\OccurrenceApiController::class, 'toggleFavorite'],
    '/api/simulate-occurrence' => [\Facchini\Presentation\Controller\Api\SimulationApiController::class, 'simulate'],
    '/api/export-insights' => [\Facchini\Presentation\Controller\Api\ExportApiController::class, 'insights'],
    '/api/export/infractions-report' => [\Facchini\Presentation\Controller\Api\ExportApiController::class, 'infractionsReport'],
    '/api/settings/epi-color' => [\Facchini\Presentation\Controller\Api\SettingsApiController::class, 'updateEpiColor'],
    '/api/settings/reset-colors' => [\Facchini\Presentation\Controller\Api\SettingsApiController::class, 'resetColors'],
    '/api/settings/chart-style' => [\Facchini\Presentation\Controller\Api\SettingsApiController::class, 'updateChartStyle'],
    '/api/branch/switch' => [\Facchini\Presentation\Controller\Api\BranchApiController::class, 'switch'],
    '/api/branch/list' => [\Facchini\Presentation\Controller\Api\BranchApiController::class, 'list'],
    '/api/employees' => [\Facchini\Presentation\Controller\Api\EmployeeApiController::class, 'index'],
    '/api/employees/create' => [\Facchini\Presentation\Controller\Api\EmployeeApiController::class, 'create'],
    '/api/employees/update' => [\Facchini\Presentation\Controller\Api\EmployeeApiController::class, 'update'],
    '/api/employees/delete' => [\Facchini\Presentation\Controller\Api\EmployeeApiController::class, 'delete'],
    '/settings' => [\Facchini\Presentation\Controller\SettingsController::class, 'index'],
];

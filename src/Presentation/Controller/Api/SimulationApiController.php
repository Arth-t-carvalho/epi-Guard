<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Persistence\MySQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;
use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\MySQLUserRepository;
use epiGuard\Infrastructure\Database\Connection;

class SimulationApiController
{
    private MySQLOccurrenceRepository $occurrenceRepository;
    private MySQLEmployeeRepository $employeeRepository;
    private MySQLEpiRepository $epiRepository;

    public function __construct()
    {
        $db = Connection::getInstance();
        $deptRepo = new MySQLDepartmentRepository();
        $this->employeeRepository = new MySQLEmployeeRepository($deptRepo);
        $userRepo = new MySQLUserRepository();
        $this->epiRepository = new MySQLEpiRepository();
        $this->occurrenceRepository = new MySQLOccurrenceRepository($this->employeeRepository, $userRepo, $this->epiRepository);
    }

    public function simulate()
    {
        header('Content-Type: application/json');

        try {
            $db = Connection::getInstance();

            // 1. Sortear um funcionário aleatório
            $employees = $this->employeeRepository->findAll();
            if (empty($employees)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum funcionário cadastrado.']);
                return;
            }
            $employee = $employees[array_rand($employees)];

            // 2. Sortear um EPI aleatório
            $epis = $this->epiRepository->findAll();
            if (empty($epis)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum EPI cadastrado.']);
                return;
            }
            $epi = $epis[array_rand($epis)];

            // 3. Inserir na tabela ocorrencias
            $stmt = $db->prepare("INSERT INTO ocorrencias (funcionario_id, tipo, data_hora) VALUES (?, 'INFRACAO', NOW())");
            $empId = $employee->getId();
            $stmt->bind_param('i', $empId);
            $stmt->execute();
            $occurrenceId = $db->insert_id;

            // 4. Inserir na tabela ocorrencia_epis
            $stmtEpi = $db->prepare("INSERT INTO ocorrencia_epis (ocorrencia_id, epi_id) VALUES (?, ?)");
            $epiId = $epi->getId();
            $stmtEpi->bind_param('ii', $occurrenceId, $epiId);
            $stmtEpi->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Ocorrência real simulada com sucesso!',
                'data' => [
                    'id' => $occurrenceId,
                    'funcionario' => $employee->getName(),
                    'epi' => $epi->getName()
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro na simulação: ' . $e->getMessage()]);
        }
    }
}

<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\PostgreSQLMachineRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository;
use Facchini\Domain\Entity\Machine;

class MachineApiController
{
    public function list(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $setorId = (int)($_GET['setor_id'] ?? 0);
            if ($setorId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID do setor inválido.']);
                return;
            }

            $repo = new PostgreSQLMachineRepository();
            $machines = $repo->findByDepartment($setorId);

            $data = array_map(function (Machine $m) {
                return [
                    'id' => $m->getId(),
                    'nome' => $m->getName(),
                    'epi_id' => $m->getEpiId()
                ];
            }, $machines);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['nome']) || empty($input['setor_id'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Nome e ID do setor são obrigatórios.']);
                return;
            }

            $machine = new Machine(
                name: trim($input['nome']),
                departmentId: (int)$input['setor_id'],
                epiId: !empty($input['epi_id']) ? (int)$input['epi_id'] : null
            );

            $repo = new PostgreSQLMachineRepository();
            $repo->save($machine);

            echo json_encode(['success' => true, 'data' => ['id' => $machine->getId()]]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);

            if ($id <= 0) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'ID inválido.']);
                return;
            }

            $repo = new PostgreSQLMachineRepository();
            $repo->delete($id);

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function listEpis(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $repo = new PostgreSQLEpiRepository();
            $epis = $repo->findAll();

            $data = array_map(function ($epi) {
                return [
                    'id' => $epi->getId(),
                    'nome' => $epi->getName()
                ];
            }, $epis);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

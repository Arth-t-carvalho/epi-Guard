<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Domain\Entity\Machine;
use epiGuard\Infrastructure\Persistence\PostgreSQLMachineRepository;

class MachineApiController
{
    private PostgreSQLMachineRepository $repository;

    public function __construct()
    {
        $this->repository = new PostgreSQLMachineRepository();
    }

    public function list()
    {
        $sectorId = (int) ($_GET['sector_id'] ?? 0);
        if (!$sectorId) {
            echo json_encode(['success' => false, 'message' => 'ID do setor não informado.']);
            return;
        }

        $machines = $this->repository->findBySectorId($sectorId);
        echo json_encode(['success' => true, 'data' => $machines]);
    }

    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['nome']) || empty($data['setor_id']) || empty($data['epi_id'])) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
            return;
        }

        $machine = new Machine(
            name: $data['nome'],
            sectorId: (int) $data['setor_id'],
            epiId: (int) $data['epi_id']
        );

        try {
            $this->repository->save($machine);
            echo json_encode(['success' => true, 'message' => 'Máquina adicionada com sucesso!', 'id' => $machine->getId()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID não informado.']);
            return;
        }

        if ($this->repository->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Máquina removida com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover máquina.']);
        }
    }
}

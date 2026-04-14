<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Machine;
use Facchini\Domain\Repository\MachineRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;

class MySQLMachineRepository implements MachineRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?Machine
    {
        $stmt = $this->db->prepare("SELECT id, nome, ip_camera, status, setor_id FROM maquinas WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare("SELECT id, nome, ip_camera, status, setor_id FROM maquinas WHERE setor_id = ? ORDER BY nome ASC");
        $stmt->execute([$departmentId]);
        
        $machines = [];
        while ($row = $stmt->fetch()) {
            $machines[] = $this->hydrate($row);
        }

        return $machines;
    }

    /** @return Machine[] */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, nome, ip_camera, status, setor_id FROM maquinas ORDER BY nome ASC");
        $machines = [];

        while ($row = $stmt->fetch()) {
            $machines[] = $this->hydrate($row);
        }

        return $machines;
    }

    public function save(Machine $machine): void
    {
        $stmt = $this->db->prepare("INSERT INTO maquinas (nome, ip_camera, status, setor_id) VALUES (?, ?, ?, ?)");
        $params = [
            $machine->getName(),
            $machine->getCameraIp(),
            $machine->getStatus(),
            $machine->getSectorId()
        ];
        $stmt->execute($params);

        $machine->setId((int)$this->db->lastInsertId());
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM maquinas WHERE id = ?");
        $stmt->execute([$id]);
    }

    private function hydrate(array $row): Machine
    {
        return new Machine(
            name: $row['nome'],
            cameraIp: $row['ip_camera'],
            status: $row['status'],
            sectorId: (int)$row['setor_id'],
            id: (int)$row['id']
        );
    }
}

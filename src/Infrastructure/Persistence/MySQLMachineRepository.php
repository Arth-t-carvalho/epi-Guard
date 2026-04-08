<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Machine;
use Facchini\Domain\Repository\MachineRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;
use DateTimeImmutable;

class MySQLMachineRepository implements MachineRepositoryInterface
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?Machine
    {
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, epi_id, criado_em, atualizado_em FROM maquinas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return Machine[] */
    public function findByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, epi_id, criado_em, atualizado_em FROM maquinas WHERE setor_id = ? ORDER BY nome ASC");
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $machines = [];
        while ($row = $result->fetch_assoc()) {
            $machines[] = $this->hydrate($row);
        }

        return $machines;
    }

    public function save(Machine $machine): void
    {
        $stmt = $this->db->prepare("INSERT INTO maquinas (nome, setor_id, epi_id) VALUES (?, ?, ?)");
        $name = $machine->getName();
        $deptId = $machine->getDepartmentId();
        $epiId = $machine->getEpiId();

        $stmt->bind_param('sii', $name, $deptId, $epiId);
        $stmt->execute();

        $machine->setId($this->db->insert_id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM maquinas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    private function hydrate(array $row): Machine
    {
        return new Machine(
            name: $row['nome'],
            departmentId: (int) $row['setor_id'],
            epiId: $row['epi_id'] ? (int) $row['epi_id'] : null,
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}

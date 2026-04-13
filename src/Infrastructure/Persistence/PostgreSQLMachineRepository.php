<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Machine;
use Facchini\Domain\Repository\MachineRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;
use DateTimeImmutable;

class PostgreSQLMachineRepository implements MachineRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?Machine
    {
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, epi_id, criado_em, atualizado_em FROM maquinas WHERE id = ? AND deletado_em IS NULL");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return Machine[] */
    public function findByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, epi_id, criado_em, atualizado_em FROM maquinas WHERE setor_id = ? AND deletado_em IS NULL ORDER BY nome ASC");
        $stmt->execute([$departmentId]);

        $machines = [];
        while ($row = $stmt->fetch()) {
            $machines[] = $this->hydrate($row);
        }

        return $machines;
    }

    public function save(Machine $machine): void
    {
        $stmt = $this->db->prepare("INSERT INTO maquinas (nome, setor_id, epi_id) VALUES (?, ?, ?)");
        $params = [
            $machine->getName(),
            $machine->getDepartmentId(),
            $machine->getEpiId()
        ];
        $stmt->execute($params);

        $machine->setId((int) $this->db->lastInsertId());
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE maquinas SET deletado_em = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
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

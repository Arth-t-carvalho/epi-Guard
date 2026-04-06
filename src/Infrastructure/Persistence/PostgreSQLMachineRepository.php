<?php

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\Machine;
use epiGuard\Domain\Repository\MachineRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
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
        $stmt = $this->db->prepare("SELECT * FROM maquinas WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findBySectorId(int $sectorId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, e.nome as epi_nome 
            FROM maquinas m
            LEFT JOIN epis e ON m.epi_id = e.id
            WHERE m.setor_id = ? 
            ORDER BY m.nome ASC
        ");
        $stmt->execute([$sectorId]);
        
        return $stmt->fetchAll();
    }

    public function save(Machine $machine): void
    {
        if ($machine->getId()) {
            $stmt = $this->db->prepare("UPDATE maquinas SET nome = ?, setor_id = ?, epi_id = ? WHERE id = ?");
            $stmt->execute([
                $machine->getName(),
                $machine->getSectorId(),
                $machine->getEpiId(),
                $machine->getId()
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO maquinas (nome, setor_id, epi_id) VALUES (?, ?, ?)");
            $stmt->execute([
                $machine->getName(),
                $machine->getSectorId(),
                $machine->getEpiId()
            ]);
            $machine->setId((int) $this->db->lastInsertId());
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM maquinas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function hydrate(array $row): Machine
    {
        return new Machine(
            name: $row['nome'],
            sectorId: (int) $row['setor_id'],
            epiId: (int) $row['epi_id'],
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em'])
        );
    }
}

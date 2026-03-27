<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\EpiItem;
use epiGuard\Domain\Repository\EpiRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;

class MySQLEpiRepository implements EpiRepositoryInterface
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?EpiItem
    {
        $stmt = $this->db->prepare("SELECT id, nome, descricao, status FROM epis WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return EpiItem[] */
    public function findAll(): array
    {
        $result = $this->db->query("SELECT id, nome, descricao, status FROM epis WHERE status = 'ATIVO' ORDER BY nome ASC");
        $epis = [];

        while ($row = $result->fetch_assoc()) {
            $epis[] = $this->hydrate($row);
        }

        return $epis;
    }

    public function save(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("INSERT INTO epis (nome, descricao, status) VALUES (?, ?, 'ATIVO')");
        $nome = $epiItem->getName();
        $descricao = $epiItem->getDescription();
        $stmt->bind_param('ss', $nome, $descricao);
        $stmt->execute();

        $epiItem->setId((int) $this->db->insert_id);
    }

    public function update(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET nome = ?, descricao = ? WHERE id = ?");
        $nome = $epiItem->getName();
        $descricao = $epiItem->getDescription();
        $id = $epiItem->getId();
        $stmt->bind_param('ssi', $nome, $descricao, $id);
        $stmt->execute();
    }

    public function delete(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET status = 'INATIVO' WHERE id = ?");
        $id = $epiItem->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    private function hydrate(array $row): EpiItem
    {
        return new EpiItem(
            name: $row['nome'],
            isRequired: true, // Padrão
            description: $row['descricao'],
            id: (int) $row['id']
        );
    }
}

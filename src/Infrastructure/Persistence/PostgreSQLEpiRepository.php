<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\EpiItem;
use epiGuard\Domain\Repository\EpiRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;

class PostgreSQLEpiRepository implements EpiRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?EpiItem
    {
        $stmt = $this->db->prepare("SELECT id, nome, descricao, cor, status FROM epis WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return EpiItem[] */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, nome, descricao, cor, status FROM epis WHERE status = 'ATIVO' ORDER BY nome ASC");
        $epis = [];

        while ($row = $stmt->fetch()) {
            $epis[] = $this->hydrate($row);
        }

        return $epis;
    }

    public function save(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("INSERT INTO epis (nome, descricao, cor, status) VALUES (?, ?, ?, 'ATIVO')");
        $params = [
            $epiItem->getName(),
            $epiItem->getDescription(),
            $epiItem->getColor()
        ];
        $stmt->execute($params);

        $epiItem->setId((int) $this->db->lastInsertId());
    }

    public function update(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET nome = ?, descricao = ?, cor = ? WHERE id = ?");
        $params = [
            $epiItem->getName(),
            $epiItem->getDescription(),
            $epiItem->getColor(),
            $epiItem->getId()
        ];
        $stmt->execute($params);
    }

    public function delete(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET status = 'INATIVO' WHERE id = ?");
        $stmt->execute([$epiItem->getId()]);
    }

    private function hydrate(array $row): EpiItem
    {
        return new EpiItem(
            name: $row['nome'],
            isRequired: true, // Padrão
            description: $row['descricao'],
            color: $row['cor'] ?? '#E30613',
            id: (int) $row['id']
        );
    }
}


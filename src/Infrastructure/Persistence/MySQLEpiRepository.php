<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\EpiItem;
use Facchini\Domain\Repository\EpiRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;
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
        $stmt = $this->db->prepare("SELECT id, nome, nome_en, descricao, cor, status FROM epis WHERE id = ?");
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
        $result = $this->db->query("SELECT id, nome, nome_en, descricao, cor, status FROM epis WHERE status = 'ATIVO' ORDER BY nome ASC");
        $epis = [];

        while ($row = $result->fetch_assoc()) {
            $epis[] = $this->hydrate($row);
        }

        return $epis;
    }

    /** @return EpiItem[] */
    public function findAllForSettings(): array
    {
        $result = $this->db->query("SELECT id, nome, nome_en, descricao, cor, status FROM epis WHERE status IN ('ATIVO', 'SISTEMA') ORDER BY CASE WHEN status = 'SISTEMA' THEN 1 ELSE 0 END DESC, nome ASC");
        $epis = [];

        while ($row = $result->fetch_assoc()) {
            $epis[] = $this->hydrate($row);
        }

        return $epis;
    }

    public function save(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("INSERT INTO epis (nome, nome_en, descricao, cor, status) VALUES (?, ?, ?, ?, 'ATIVO')");
        $nome = $epiItem->getName();
        $nomeEn = $epiItem->getNameEn();
        $descricao = $epiItem->getDescription();
        $cor = $epiItem->getColor();
        $stmt->bind_param('ssss', $nome, $nomeEn, $descricao, $cor);
        $stmt->execute();

        $epiItem->setId((int) $this->db->insert_id);
    }

    public function update(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET nome = ?, nome_en = ?, descricao = ?, cor = ? WHERE id = ?");
        $nome = $epiItem->getName();
        $nomeEn = $epiItem->getNameEn();
        $descricao = $epiItem->getDescription();
        $cor = $epiItem->getColor();
        $id = $epiItem->getId();
        $stmt->bind_param('ssssi', $nome, $nomeEn, $descricao, $cor, $id);
        $stmt->execute();
    }

    public function delete(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET status = 'INATIVO' WHERE id = ?");
        $id = $epiItem->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function updateColor(int $id, string $color): bool
    {
        $stmt = $this->db->prepare("UPDATE epis SET cor = ? WHERE id = ?");
        $stmt->bind_param('si', $color, $id);
        return $stmt->execute();
    }

    public function resetToDefaults(): bool
    {
        // 1. Reset 'Total' to original Facchini Red
        $this->db->query("UPDATE epis SET cor = '#E30613' WHERE nome = 'Total' AND status = 'SISTEMA'");

        // 2. Reset 'Capacete' to original Blue
        $this->db->query("UPDATE epis SET cor = '#06377c' WHERE nome LIKE '%Capacete%'");

        // 3. Reset 'Óculos' to original Gray
        $this->db->query("UPDATE epis SET cor = '#94a3af' WHERE (nome LIKE '%Oculos%' OR nome LIKE '%Óculos%')");

        // 4. Reset others to default gray-slate
        $this->db->query("UPDATE epis SET cor = '#94a3b8' WHERE nome NOT IN ('Total') AND nome NOT LIKE '%Capacete%' AND nome NOT LIKE '%Oculos%' AND nome NOT LIKE '%Óculos%'");

        return true;
    }

    private function hydrate(array $row): EpiItem
    {
        return new EpiItem(
            name: $row['nome'],
            color: $row['cor'] ?? '#E30613',
            isRequired: true, // Padrão
            description: $row['descricao'],
            nameEn: $row['nome_en'] ?? null,
            id: (int) $row['id']
        );
    }
}

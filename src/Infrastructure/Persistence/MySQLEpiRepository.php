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
    private bool $hasNomeEn = false;
    private bool $hasCor = false;

    public function __construct()
    {
        $this->db = Connection::getInstance();
        if ($this->db) {
            $this->detectSchema();
        }
    }

    private function detectSchema(): void
    {
        if (!$this->db) return;

        $res = $this->db->query("SHOW COLUMNS FROM epis LIKE 'nome_en'");
        $this->hasNomeEn = ($res && $res->num_rows > 0);

        $res2 = $this->db->query("SHOW COLUMNS FROM epis LIKE 'cor'");
        $this->hasCor = ($res2 && $res2->num_rows > 0);
    }

    public function findById(int $id): ?EpiItem
    {
        if (!$this->db) return null;

        $cols = "id, nome, descricao, status";
        if ($this->hasNomeEn) $cols .= ", nome_en";
        if ($this->hasCor) $cols .= ", cor";

        $stmt = $this->db->prepare("SELECT $cols FROM epis WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return EpiItem[] */
    public function findAll(): array
    {
        if (!$this->db) return [];

        $cols = "id, nome, descricao, status";
        if ($this->hasNomeEn) $cols .= ", nome_en";
        if ($this->hasCor) $cols .= ", cor";

        $result = $this->db->query("SELECT $cols FROM epis WHERE status = 'ATIVO' ORDER BY nome ASC");
        $epis = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $epis[] = $this->hydrate($row);
            }
        }

        return $epis;
    }

    /** @return EpiItem[] */
    public function findAllForSettings(): array
    {
        if (!$this->db) return [];

        $cols = "id, nome, descricao, status";
        if ($this->hasNomeEn) $cols .= ", nome_en";
        if ($this->hasCor) $cols .= ", cor";

        $result = $this->db->query("SELECT $cols FROM epis WHERE status IN ('ATIVO', 'SISTEMA') ORDER BY CASE WHEN status = 'SISTEMA' THEN 1 ELSE 0 END DESC, nome ASC");
        $epis = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $epis[] = $this->hydrate($row);
            }
        }

        return $epis;
    }

    public function save(EpiItem $epiItem): void
    {
        if (!$this->db) return;

        $nome = $epiItem->getName();
        $nomeEn = $epiItem->getNameEn();
        $descricao = $epiItem->getDescription();
        $cor = $epiItem->getColor();

        $cols = "nome, descricao, status";
        $vals = "?, ?, 'ATIVO'";
        $types = "ss";
        $params = [$nome, $descricao];

        if ($this->hasNomeEn) {
            $cols .= ", nome_en";
            $vals .= ", ?";
            $types .= "s";
            $params[] = $nomeEn;
        }

        if ($this->hasCor) {
            $cols .= ", cor";
            $vals .= ", ?";
            $types .= "s";
            $params[] = $cor;
        }

        $stmt = $this->db->prepare("INSERT INTO epis ($cols) VALUES ($vals)");
        if (!$stmt) {
            error_log("MySQL Prepare Error (save): " . $this->db->error);
            throw new \Exception("Erro ao preparar inserção (save): " . $this->db->error);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $epiItem->setId((int) $this->db->insert_id);
    }

    public function update(EpiItem $epiItem): void
    {
        if (!$this->db) return;

        $nome = $epiItem->getName();
        $nomeEn = $epiItem->getNameEn();
        $descricao = $epiItem->getDescription();
        $cor = $epiItem->getColor();
        $id = $epiItem->getId();

        $sets = "nome = ?, descricao = ?";
        $types = "ss";
        $params = [$nome, $descricao];

        if ($this->hasNomeEn) {
            $sets .= ", nome_en = ?";
            $types .= "s";
            $params[] = $nomeEn;
        }

        if ($this->hasCor) {
            $sets .= ", cor = ?";
            $types .= "s";
            $params[] = $cor;
        }

        $sets .= " WHERE id = ?";
        $types .= "i";
        $params[] = $id;

        $stmt = $this->db->prepare("UPDATE epis SET $sets");
        if (!$stmt) {
            error_log("MySQL Prepare Error (update): " . $this->db->error);
            throw new \Exception("Erro ao preparar atualização (update): " . $this->db->error);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    public function delete(EpiItem $epiItem): void
    {
        if (!$this->db) return;
        $stmt = $this->db->prepare("UPDATE epis SET status = 'INATIVO' WHERE id = ?");
        if (!$stmt) return;
        $id = $epiItem->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function updateColor(int $id, string $color): bool
    {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE epis SET cor = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('si', $color, $id);
        return $stmt->execute();
    }

    public function resetToDefaults(): bool
    {
        if (!$this->db) return false;
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
            description: $row['descricao'] ?? '',
            nameEn: $row['nome_en'] ?? null,
            id: (int) $row['id']
        );
    }
}

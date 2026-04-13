<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\EpiItem;
use Facchini\Domain\Repository\EpiRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;

class PostgreSQLEpiRepository implements EpiRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?EpiItem
    {
        $stmt = $this->db->prepare("SELECT id, nome, nome_en, descricao, cor, status FROM epis WHERE id = ? AND deletado_em IS NULL");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return EpiItem[] */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, nome, nome_en, descricao, cor, status FROM epis WHERE status = 'ATIVO' AND deletado_em IS NULL ORDER BY nome ASC");
        $epis = [];

        while ($row = $stmt->fetch()) {
            $epis[] = $this->hydrate($row);
        }

        return $epis;
    }

    /**
     * Retorna todos os EPIs ativos com foco em campos de configuração
     */
    public function findAllForSettings(): array
    {
        $stmt = $this->db->query("SELECT id, nome, nome_en, descricao, cor FROM epis WHERE status = 'ATIVO' AND deletado_em IS NULL ORDER BY nome ASC");
        $epis = [];
        while ($row = $stmt->fetch()) {
            $epis[] = $this->hydrate($row);
        }
        return $epis;
    }

    public function save(EpiItem $epiItem): void
    {
        // Se for um novo item (sem ID) e estiver com a cor padrão, rotaciona a paleta
        $currentColor = strtoupper(trim($epiItem->getColor()));
        if ($epiItem->getId() === null && ($currentColor === '#E30613' || empty($currentColor))) {
            try {
                $colors = [
                    '#E30613', // Vermelho Facchini
                    '#FFC107', // Amarelo
                    '#2196F3', // Azul
                    '#4CAF50', // Verde
                    '#FF9800', // Laranja
                    '#9C27B0', // Roxo
                    '#009688', // Teal
                    '#3F51B5'  // Indigo
                ];
                $stmtCount = $this->db->query("SELECT COUNT(*) FROM epis WHERE deletado_em IS NULL");
                $count = (int)$stmtCount->fetchColumn();
                $epiItem->setColor($colors[$count % count($colors)]);
            } catch (\Exception $e) {
                // Em caso de erro, mantém o padrão da entidade
            }
        }

        $stmt = $this->db->prepare("INSERT INTO epis (nome, nome_en, descricao, cor, status) VALUES (?, ?, ?, ?, 'ATIVO')");
        $params = [
            $epiItem->getName(),
            $epiItem->getNameEn(),
            $epiItem->getDescription(),
            $epiItem->getColor()
        ];
        $stmt->execute($params);

        $epiItem->setId((int) $this->db->lastInsertId());
    }

    public function update(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET nome = ?, nome_en = ?, descricao = ?, cor = ? WHERE id = ?");
        $params = [
            $epiItem->getName(),
            $epiItem->getNameEn(),
            $epiItem->getDescription(),
            $epiItem->getColor(),
            $epiItem->getId()
        ];
        $stmt->execute($params);
    }

    public function delete(EpiItem $epiItem): void
    {
        $stmt = $this->db->prepare("UPDATE epis SET deletado_em = CURRENT_TIMESTAMP, status = 'INATIVO' WHERE id = ?");
        $stmt->execute([$epiItem->getId()]);
    }

    public function resetToDefaults(): bool
    {
        try {
            // Paleta expandida para maior variedade visual
            $colors = [
                '#E30613', // Vermelho Facchini
                '#FFC107', // Amarelo
                '#2196F3', // Azul
                '#4CAF50', // Verde
                '#FF9800', // Laranja
                '#9C27B0', // Roxo
                '#009688', // Teal
                '#3F51B5'  // Indigo
            ];
            
            // Buscar IDs dos EPIs ativos ordenados para manter consistência
            $stmt = $this->db->query("SELECT id FROM epis WHERE status = 'ATIVO' AND deletado_em IS NULL ORDER BY nome ASC");
            $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            if (empty($ids)) {
                return true;
            }

            // Iniciar transação para performance e integridade
            $this->db->beginTransaction();
            
            $updateStmt = $this->db->prepare("UPDATE epis SET cor = ? WHERE id = ?");
            
            foreach ($ids as $index => $id) {
                $color = $colors[$index % count($colors)];
                $updateStmt->execute([$color, (int)$id]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao restaurar cores: " . $e->getMessage());
            return false;
        }
    }

    private function hydrate(array $row): EpiItem
    {
        return new EpiItem(
            name: $row['nome'],
            color: $row['cor'] ?? '#E30613',
            isRequired: true, // Padrao
            description: $row['descricao'],
            nameEn: $row['nome_en'],
            id: (int) $row['id']
        );
    }
}

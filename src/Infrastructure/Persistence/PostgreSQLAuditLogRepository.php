<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\AuditLog;
use epiGuard\Domain\Repository\AuditLogRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;
use PDO;

class PostgreSQLAuditLogRepository implements AuditLogRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?AuditLog
    {
        $stmt = $this->db->prepare("SELECT * FROM logs_auditoria WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM logs_auditoria ORDER BY criado_em DESC");
        $logs = [];
        while ($row = $stmt->fetch()) {
            $logs[] = $this->hydrate($row);
        }
        return $logs;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM logs_auditoria WHERE usuario_id = ? ORDER BY criado_em DESC");
        $stmt->execute([$userId]);
        
        $logs = [];
        while ($row = $stmt->fetch()) {
            $logs[] = $this->hydrate($row);
        }
        return $logs;
    }

    public function findByEntity(string $entityType, int $entityId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM logs_auditoria WHERE tipo_entidade = ? AND entidade_id = ? ORDER BY criado_em DESC");
        $stmt->execute([$entityType, $entityId]);
        
        $logs = [];
        while ($row = $stmt->fetch()) {
            $logs[] = $this->hydrate($row);
        }
        return $logs;
    }

    public function save(AuditLog $auditLog): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO logs_auditoria (usuario_id, acao, tipo_entidade, entidade_id, valores_antigos, valores_novos, endereco_ip, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $params = [
            $auditLog->getUserId(),
            $auditLog->getAction(),
            $auditLog->getEntityType(),
            $auditLog->getEntityId(),
            $auditLog->getOldValues(),
            $auditLog->getNewValues(),
            $auditLog->getIpAddress(),
            $auditLog->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        $stmt->execute($params);
    }

    private function hydrate(array $row): AuditLog
    {
        return new AuditLog(
            userId: (int) $row['usuario_id'],
            action: $row['acao'],
            entityType: $row['tipo_entidade'],
            entityId: (int) $row['entidade_id'],
            ipAddress: $row['endereco_ip'],
            oldValues: $row['valores_antigos'],
            newValues: $row['valores_novos'],
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em'])
        );
    }
}


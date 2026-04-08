<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Repository;

use Facchini\Domain\Entity\AuditLog;

interface AuditLogRepositoryInterface
{
    public function findById(int $id): ?AuditLog;

    /**
     * @return AuditLog[]
     */
    public function findAll(): array;

    /**
     * @param int $userId
     * @return AuditLog[]
     */
    public function findByUserId(int $userId): array;

    /**
     * @param string $entityType
     * @param int $entityId
     * @return AuditLog[]
     */
    public function findByEntity(string $entityType, int $entityId): array;

    public function save(AuditLog $auditLog): void;
}

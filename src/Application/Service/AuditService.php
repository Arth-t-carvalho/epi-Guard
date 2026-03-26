<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Domain\Entity\AuditLog;
use epiGuard\Domain\Repository\AuditLogRepositoryInterface;

class AuditService
{
    private AuditLogRepositoryInterface $auditLogRepository;

    public function __construct(AuditLogRepositoryInterface $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
    }

    public function log(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        string $ipAddress,
        ?string $oldValues = null,
        ?string $newValues = null
        ): void
    {
        $log = new AuditLog(
            $userId,
            $action,
            $entityType,
            $entityId,
            $ipAddress,
            $oldValues,
            $newValues
            );

        $this->auditLogRepository->save($log);
    }
}

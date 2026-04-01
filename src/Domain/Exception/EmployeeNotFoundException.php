<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Exception;

class EmployeeNotFoundException extends DomainException
{
    public static function withId(int $id): self
    {
        return new self("Employee with ID {$id} not found.");
    }

    public static function withCpf(string $cpf): self
    {
        return new self("Employee with CPF {$cpf} not found.");
    }
}

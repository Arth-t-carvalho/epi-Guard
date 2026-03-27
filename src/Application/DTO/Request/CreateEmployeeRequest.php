<?php
declare(strict_types = 1);

namespace epiGuard\Application\DTO\Request;

/**
 * Data Transfer Object para requisição de criação de novo funcionário.
 * Transporta os dados brutos da apresentação para a camada de aplicação.
 */
class CreateEmployeeRequest
{
    public string $name;
    public string $cpf;
    public string $enrollmentNumber;
    public int $departmentId;
    public ?string $email;

    public function __construct(
        string $name,
        string $cpf,
        string $enrollmentNumber,
        int $departmentId,
        ?string $email = null
        )
    {
        $this->name = $name;
        $this->cpf = $cpf;
        $this->enrollmentNumber = $enrollmentNumber;
        $this->departmentId = $departmentId;
        $this->email = $email;
    }
}

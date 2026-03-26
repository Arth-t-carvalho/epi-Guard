<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Exception;

class ValidationException extends DomainException
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

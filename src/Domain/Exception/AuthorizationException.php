<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Exception;

class AuthorizationException extends DomainException
{
    public static function forbidden(): self
    {
        return new self('You do not have permission to access this resource.');
    }
}

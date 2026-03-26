<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Request;

class LoginRequest
{
    public string $email;
    public string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}

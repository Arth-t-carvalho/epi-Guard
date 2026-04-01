<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Application\DTO\Request\LoginRequest;
use epiGuard\Domain\Entity\User;
use epiGuard\Domain\Exception\AuthenticationException;
use epiGuard\Domain\Repository\UserRepositoryInterface;
use epiGuard\Domain\ValueObject\Email;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    // We would ideally inject a PasswordHasher interface here
    // private PasswordHasherInterface $passwordHasher;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param LoginRequest $request
     * @return User
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): User
    {
        try {
            $email = new Email($request->email);
        }
        catch (\InvalidArgumentException $e) {
            throw AuthenticationException::invalidCredentials();
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw AuthenticationException::invalidCredentials();
        }

        // Mock verification for now until PasswordHasher infrastucture is ready
        if (!password_verify($request->password, $user->getPasswordHash())) {
            throw AuthenticationException::invalidCredentials();
        }

        return $user;
    }
}

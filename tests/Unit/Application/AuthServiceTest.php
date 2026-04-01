<?php
declare(strict_types = 1)
;

namespace Tests\Unit\Application;

use App\Application\DTO\Request\LoginRequest;
use App\Application\Service\AuthService;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function testLoginThrowsExceptionWhenUserNotFound(): void
    {
        $mockRepo = $this->createMock(UserRepositoryInterface::class);
        $mockRepo->method('findByEmail')->willReturn(null);

        $service = new AuthService($mockRepo);

        $this->expectException(AuthenticationException::class);
        $service->login(new LoginRequest('test@test.com', 'password'));
    }

    public function testLoginSucceedsWithCorrectCredentials(): void
    {
        $user = new User('Test User', new Email('test@test.com'), password_hash('password123', PASSWORD_BCRYPT), new UserRole('admin'));

        $mockRepo = $this->createMock(UserRepositoryInterface::class);
        $mockRepo->method('findByEmail')->willReturn($user);

        $service = new AuthService($mockRepo);

        $loggedInUser = $service->login(new LoginRequest('test@test.com', 'password123'));

        $this->assertSame($user, $loggedInUser);
    }
}

<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Repository;

use Facchini\Domain\Entity\User;
use Facchini\Domain\ValueObject\Email;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(Email $email): ?User;

    /**
     * @return User[]
     */
    public function findAll(): array;

    public function save(User $user): void;

    public function update(User $user): void;

    public function delete(User $user): void;
}

<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\User;
use epiGuard\Domain\ValueObject\Email;
use epiGuard\Domain\ValueObject\UserRole;
use epiGuard\Domain\Repository\UserRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;

class PostgreSQLUserRepository implements UserRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->db->prepare("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios WHERE usuario = ? AND status = 'ATIVO'");
        $stmt->execute([$email->getValue()]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios WHERE usuario = ? AND status = 'ATIVO'");
        $stmt->execute([$username]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return User[] */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios ORDER BY nome ASC");
        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = $this->hydrate($row);
        }
        return $users;
    }

    public function save(User $user): void
    {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, usuario, senha, cargo, criado_em) VALUES (?, ?, ?, ?, ?)");
        
        $roleVal = $user->getRole()->getValue();
        $dbRole = 'SUPERVISOR';
        if ($roleVal === UserRole::ADMIN) {
            $dbRole = 'SUPER_ADMIN';
        } elseif ($roleVal === UserRole::OPERATOR) {
            $dbRole = 'SUPERVISOR';
        } elseif ($roleVal === UserRole::MANAGER) {
            $dbRole = 'GERENTE_SEGURANCA';
        }

        $params = [
            $user->getName(),
            $user->getEmail()->getValue(),
            $user->getPasswordHash(),
            $dbRole,
            $user->getCreatedAt()->format('Y-m-d H:i:s')
        ];
        
        if ($stmt->execute($params)) {
            $user->setId((int)$this->db->lastInsertId());
        } else {
            $error = $stmt->errorInfo();
            throw new \Exception("DB Error on save: " . $error[2]);
        }
    }

    public function update(User $user): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, usuario = ?, senha = ?, cargo = ?, atualizado_em = ? WHERE id = ?");
        
        $roleVal = $user->getRole()->getValue();
        $dbRole = 'SUPERVISOR';
        if ($roleVal === UserRole::ADMIN) {
            $dbRole = 'SUPER_ADMIN';
        } elseif ($roleVal === UserRole::OPERATOR) {
            $dbRole = 'SUPERVISOR';
        } elseif ($roleVal === UserRole::MANAGER) {
            $dbRole = 'GERENTE_SEGURANCA';
        }

        $params = [
            $user->getName(),
            $user->getEmail()->getValue(),
            $user->getPasswordHash(),
            $dbRole,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            $user->getId()
        ];
        
        if (!$stmt->execute($params)) {
            $error = $stmt->errorInfo();
            throw new \Exception("DB Error on update: " . $error[2]);
        }
    }

    public function delete(User $user): void
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$user->getId()]);
    }

    private function hydrate(array $row): User
    {
        // Mapeamento de cargo para UserRole (suportando os cargos antigos)
        $cargo = strtolower($row['cargo']);
        if ($cargo === 'super_admin' || $cargo === 'admin') {
            $role = new UserRole(UserRole::ADMIN);
        } elseif ($cargo === 'supervisor' || $cargo === 'operator') {
            $role = new UserRole(UserRole::OPERATOR);
        } elseif ($cargo === 'gerente_seguranca') {
            $role = new UserRole(UserRole::MANAGER);
        } else {
            $role = new UserRole(UserRole::VIEWER);
        }

        return new User(
            name: $row['nome'],
            email: new Email($row['usuario']),
            passwordHash: $row['senha'],
            role: $role,
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}


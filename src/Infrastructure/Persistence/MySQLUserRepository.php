<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\User;
use epiGuard\Domain\ValueObject\Email;
use epiGuard\Domain\ValueObject\UserRole;
use epiGuard\Domain\Repository\UserRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;

class MySQLUserRepository implements UserRepositoryInterface
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->db->prepare("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios WHERE usuario = ? AND status = 'ATIVO'");
        $emailStr = $email->getValue();
        $stmt->bind_param('s', $emailStr);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios WHERE usuario = ? AND status = 'ATIVO'");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return User[] */
    public function findAll(): array
    {
        $result = $this->db->query("SELECT id, nome, usuario, senha, cargo, criado_em, atualizado_em FROM usuarios ORDER BY nome ASC");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $this->hydrate($row);
        }
        return $users;
    }

    public function save(User $user): void
    {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, usuario, senha, cargo, criado_em) VALUES (?, ?, ?, ?, ?)");
        
        $name = $user->getName();
        $username = $user->getEmail()->getValue();
        $password = $user->getPasswordHash();
        
        // Mapeamento reverso: UserRole -> DB ENUM
        $roleVal = $user->getRole()->getValue();
        $dbRole = 'SUPERVISOR';
        if ($roleVal === UserRole::ADMIN) {
            $dbRole = 'SUPER_ADMIN';
        } elseif ($roleVal === UserRole::OPERATOR) {
            $dbRole = 'SUPERVISOR';
        } elseif ($roleVal === UserRole::MANAGER) {
            $dbRole = 'GERENTE_SEGURANCA';
        }

        $createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');

        $stmt->bind_param('sssss', $name, $username, $password, $dbRole, $createdAt);
        
        if ($stmt->execute()) {
            $user->setId($this->db->insert_id);
        } else {
            error_log("DB Error on save: " . $stmt->error);
            throw new \Exception($stmt->error);
        }
    }

    public function update(User $user): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, usuario = ?, senha = ?, cargo = ?, atualizado_em = ? WHERE id = ?");
        
        $name = $user->getName();
        $username = $user->getEmail()->getValue();
        $password = $user->getPasswordHash();
        
        // Mapeamento reverso: UserRole -> DB ENUM
        $roleVal = $user->getRole()->getValue();
        $dbRole = 'SUPERVISOR';
        if ($roleVal === UserRole::ADMIN) {
            $dbRole = 'SUPER_ADMIN';
        } elseif ($roleVal === UserRole::OPERATOR) {
            $dbRole = 'SUPERVISOR';
        } elseif ($roleVal === UserRole::MANAGER) {
            $dbRole = 'GERENTE_SEGURANCA';
        }

        $updatedAt = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $id = $user->getId();

        $stmt->bind_param('sssssi', $name, $username, $password, $dbRole, $updatedAt, $id);
        
        if (!$stmt->execute()) {
            error_log("DB Error on update: " . $stmt->error);
            throw new \Exception($stmt->error);
        }
    }

    public function delete(User $user): void
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $id = $user->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
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

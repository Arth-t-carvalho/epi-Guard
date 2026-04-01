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
        $stmt = $this->db->prepare("
            SELECT u.id, u.nome, u.usuario, u.senha, u.cargo, u.preferencia_grafico, u.criado_em, u.atualizado_em, s.nome as setor_nome
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            WHERE u.id = ?
        ");
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
        $stmt = $this->db->prepare("
            SELECT u.id, u.nome, u.usuario, u.senha, u.cargo, u.preferencia_grafico, u.criado_em, u.atualizado_em, s.nome as setor_nome
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            WHERE u.usuario = ? AND u.status = 'ATIVO'
        ");
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
        $stmt = $this->db->prepare("
            SELECT u.id, u.nome, u.usuario, u.senha, u.cargo, u.preferencia_grafico, u.criado_em, u.atualizado_em, s.nome as setor_nome
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            WHERE u.usuario = ? AND u.status = 'ATIVO'
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findAll(): array
    {
        $result = $this->db->query("
            SELECT u.id, u.nome, u.usuario, u.senha, u.cargo, u.preferencia_grafico, u.criado_em, u.atualizado_em, s.nome as setor_nome
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            ORDER BY u.nome ASC
        ");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $this->hydrate($row);
        }
        return $users;
    }

    public function save(User $user): void
    {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, usuario, senha, cargo, preferencia_grafico, criado_em) VALUES (?, ?, ?, ?, ?, ?)");
        
        $name = $user->getName();
        $username = $user->getEmail()->getValue();
        $password = $user->getPasswordHash();
        $chartPref = $user->getChartPreference();
        
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

        $stmt->bind_param('ssssss', $name, $username, $password, $dbRole, $chartPref, $createdAt);
        
        if ($stmt->execute()) {
            $user->setId($this->db->insert_id);
        } else {
            error_log("DB Error on save: " . $stmt->error);
            throw new \Exception($stmt->error);
        }
    }

    public function update(User $user): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, usuario = ?, senha = ?, cargo = ?, preferencia_grafico = ?, atualizado_em = ? WHERE id = ?");
        
        $name = $user->getName();
        $username = $user->getEmail()->getValue();
        $password = $user->getPasswordHash();
        $chartPref = $user->getChartPreference();
        
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

        $stmt->bind_param('ssssssi', $name, $username, $password, $dbRole, $chartPref, $updatedAt, $id);
        
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

    public function updateChartPreference(int $id, string $style): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET preferencia_grafico = ? WHERE id = ?");
        $stmt->bind_param('si', $style, $id);
        return $stmt->execute();
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
            sectorName: $row['setor_nome'] ?? null,
            chartPreference: $row['preferencia_grafico'] ?? 'bar',
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}

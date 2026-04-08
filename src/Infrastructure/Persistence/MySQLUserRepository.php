<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\User;
use Facchini\Domain\ValueObject\Email;
use Facchini\Domain\ValueObject\UserRole;
use Facchini\Domain\Repository\UserRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;
use DateTimeImmutable;

class MySQLUserRepository implements UserRepositoryInterface
{
    private \mysqli $db;
    private bool $hasFilialId = false;
    private bool $hasPrefGrafico = false;
    private bool $hasStatus = false;

    public function __construct()
    {
        $this->db = Connection::getInstance();
        if ($this->db) {
            $this->detectSchema();
        }
    }

    private function detectSchema(): void
    {
        if (!$this->db) return;

        // Detect filial_id
        $res = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'filial_id'");
        $this->hasFilialId = ($res && $res->num_rows > 0);

        // Detect preferencia_grafico
        $res = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'preferencia_grafico'");
        $this->hasPrefGrafico = ($res && $res->num_rows > 0);

        // Detect status
        $res = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'status'");
        $this->hasStatus = ($res && $res->num_rows > 0);
    }

    private function getSelectFields(): string
    {
        $fields = "u.id, u.nome, u.usuario, u.senha, u.cargo, u.criado_em, u.atualizado_em";
        
        $fields .= $this->hasPrefGrafico ? ", u.preferencia_grafico" : ", 'bar' as preferencia_grafico";
        $fields .= $this->hasFilialId ? ", u.filial_id" : ", 1 as filial_id";
        $fields .= ", s.nome as setor_nome";
        
        return $fields;
    }

    public function findById(int $id): ?User
    {
        if (!$this->db) return null;
        $fields = $this->getSelectFields();
        $stmt = $this->db->prepare("
            SELECT $fields
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            WHERE u.id = ?
        ");
        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        if (!$this->db) return null;
        $fields = $this->getSelectFields();
        $statusClause = $this->hasStatus ? " AND u.status = 'ATIVO'" : "";
        $stmt = $this->db->prepare("
            SELECT $fields
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            WHERE u.usuario = ? $statusClause
        ");
        if (!$stmt) return null;

        $emailStr = $email->getValue();
        $stmt->bind_param('s', $emailStr);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByUsername(string $username): ?User
    {
        if (!$this->db) return null;
        $fields = $this->getSelectFields();
        $statusClause = $this->hasStatus ? " AND u.status = 'ATIVO'" : "";
        $stmt = $this->db->prepare("
            SELECT $fields
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            WHERE u.usuario = ? $statusClause
        ");
        if (!$stmt) return null;

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findAll(): array
    {
        if (!$this->db) return [];
        $fields = $this->getSelectFields();
        $result = $this->db->query("
            SELECT $fields
            FROM usuarios u
            LEFT JOIN setores s ON u.setor_id = s.id
            ORDER BY u.nome ASC
        ");
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $this->hydrate($row);
            }
        }
        return $users;
    }

    public function save(User $user): void
    {
        if (!$this->db) return;
        $cols = "nome, usuario, senha, cargo, criado_em, setor_id";
        $vals = "?, ?, ?, ?, ?, ?";
        $types = "sssssi";
        $params = [
            $user->getName(),
            $user->getEmail()->getValue(),
            $user->getPasswordHash(),
            $this->mapRoleToDb($user->getRole()),
            $user->getCreatedAt()->format('Y-m-d H:i:s'),
            $this->resolveSectorId($user->getSectorName())
        ];

        if ($this->hasPrefGrafico) {
            $cols .= ", preferencia_grafico";
            $vals .= ", ?";
            $types .= "s";
            $params[] = $user->getChartPreference();
        }
        if ($this->hasFilialId) {
            $cols .= ", filial_id";
            $vals .= ", ?";
            $types .= "i";
            $params[] = $user->getFilialId() ?? 1;
        }

        $stmt = $this->db->prepare("INSERT INTO usuarios ($cols) VALUES ($vals)");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $user->setId($this->db->insert_id);
        }
    }

    private function resolveSectorId(?string $sectorName): ?int
    {
        if (!$sectorName || !$this->db) return null;
        $stmt = $this->db->prepare("SELECT id FROM setores WHERE nome = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param('s', $sectorName);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['id'];
        }
        return null;
    }

    private function mapRoleToDb(\Facchini\Domain\ValueObject\UserRole $role): string
    {
        $roleVal = $role->getValue();
        if ($roleVal === \Facchini\Domain\ValueObject\UserRole::ADMIN) {
            return 'SUPER_ADMIN';
        } elseif ($roleVal === \Facchini\Domain\ValueObject\UserRole::MANAGER) {
            return 'GERENTE_SEGURANCA';
        }
        return 'SUPERVISOR';
    }

    public function update(User $user): void
    {
        if (!$this->db) return;
        $sets = "nome = ?, usuario = ?, senha = ?, cargo = ?, atualizado_em = ?";
        $types = "sssss";
        $params = [
            $user->getName(),
            $user->getEmail()->getValue(),
            $user->getPasswordHash(),
            $this->mapRoleToDb($user->getRole()),
            (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ];

        if ($this->hasPrefGrafico) {
            $sets .= ", preferencia_grafico = ?";
            $types .= "s";
            $params[] = $user->getChartPreference();
        }

        $params[] = $user->getId();
        $types .= "i";

        $stmt = $this->db->prepare("UPDATE usuarios SET $sets WHERE id = ?");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    public function delete(User $user): void
    {
        if (!$this->db) return;
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        if (!$stmt) return;
        $id = $user->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function updateChartPreference(int $id, string $style): bool
    {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE usuarios SET preferencia_grafico = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('si', $style, $id);
        return $stmt->execute();
    }

    private function hydrate(array $row): User
    {
        $cargo = strtolower($row['cargo'] ?? '');
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
            name: $row['nome'] ?? '',
            email: new Email($row['usuario'] ?? ''),
            passwordHash: $row['senha'] ?? '',
            role: $role,
            sectorName: $row['setor_nome'] ?? null,
            chartPreference: $row['preferencia_grafico'] ?? 'bar',
            id: (int) $row['id'],
            filialId: isset($row['filial_id']) ? (int) $row['filial_id'] : null,
            createdAt: new DateTimeImmutable($row['criado_em'] ?? 'now'),
            updatedAt: !empty($row['atualizado_em']) ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}

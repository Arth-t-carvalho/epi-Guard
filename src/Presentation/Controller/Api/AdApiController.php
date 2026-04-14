<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Auth\LdapService;

class AdApiController
{
    private array $config;
    private LdapService $ldapService;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../../../config/app.php';
        $this->ldapService = new LdapService($this->config['ldap']);
    }

    public function list(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['user_email'] ?? '') !== 'pietra.12@gmail.com') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            return;
        }
        try {
            $users = $this->ldapService->getMockUsers();
            echo json_encode(['success' => true, 'data' => $users]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function save(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['user_email'] ?? '') !== 'pietra.12@gmail.com') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            return;
        }
        try {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);

            // Limpar CPF (apenas números)
            $cpf = preg_replace('/\D/', '', $data['cpf'] ?? '');

            if (empty($data['email']) && empty($cpf)) {
                throw new \Exception("E-mail ou CPF são obrigatórios.");
            }

            // O username interno pode ser gerado a partir do nome se não fornecido
            $username = $data['username'] ?? $this->generateUsername($data['name'] ?? 'user');
            
            $userData = [
                'name' => trim($data['name'] ?? 'Usuário AD'),
                'email' => strtolower(trim($data['email'] ?? '')),
                'cpf' => $cpf,
                'department' => trim($data['department'] ?? 'TI'),
                'password' => $data['password'] ?? '123'
            ];

            $this->ldapService->saveUserMock($username, $userData);
            echo json_encode(['success' => true, 'message' => 'Usuário AD salvo com sucesso']);

        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['user_email'] ?? '') !== 'pietra.12@gmail.com') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            return;
        }
        try {
            $username = $_GET['username'] ?? null;
            if (!$username) throw new \Exception("Username não fornecido.");

            $this->ldapService->deleteUserMock($username);
            echo json_encode(['success' => true, 'message' => 'Usuário removido do AD']);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function generateUsername(string $name): string
    {
        $clean = strtolower(trim($name));
        $clean = preg_replace('/[^a-z0-9]/', '', $clean);
        return $clean . rand(100, 999);
    }
}


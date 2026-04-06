<?php

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Database\Connection;
use Facchini\Infrastructure\Persistence\MySQLUserRepository;
use Facchini\Domain\Entity\User;
use Facchini\Domain\ValueObject\Email;
use Facchini\Domain\ValueObject\UserRole;

class AuthController
{
    private MySQLUserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new MySQLUserRepository();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
            return;
        }
        require_once __DIR__ . '/../View/auth/login.php';
    }



    private function login()
    {
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($usuario) || empty($senha)) {
            $_SESSION['error'] = "Usuário e senha são obrigatórios.";
            header("Location: " . BASE_PATH . "/login");
            exit;
        }

        try {
            $user = $this->userRepository->findByUsername($usuario);

            if (!$user) {
                $_SESSION['error'] = "Usuário não encontrado ou inativo.";
                header("Location: " . BASE_PATH . "/login");
                exit;
            }

            if (password_verify($senha, $user->getPasswordHash())) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_nome'] = $user->getName();
                $_SESSION['user_cargo'] = $user->getRole()->getValue();
                $_SESSION['user_setor'] = $user->getSectorName();
                $_SESSION['user_filial_id'] = $user->getFilialId();
                $_SESSION['active_filial_id'] = $user->getFilialId() ?? 1; // Unidade padrão inicial
                
                header("Location: " . BASE_PATH . "/dashboard");
                exit;
            } else {
                $_SESSION['error'] = "Senha incorreta.";
                header("Location: " . BASE_PATH . "/login");
                exit;
            }
        } catch (\Exception $e) {
            error_log("Login Exception: " . $e->getMessage());
            $_SESSION['error'] = "Erro interno no servidor. Tente novamente mais tarde.";
            header("Location: " . BASE_PATH . "/login");
            exit;
        }
    }


    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: " . BASE_PATH . "/login");
        exit;
    }
}

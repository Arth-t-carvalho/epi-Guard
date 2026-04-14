<?php

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Database\Connection;
use Facchini\Infrastructure\Persistence\MySQLUserRepository;
use Facchini\Domain\Entity\User;
use Facchini\Domain\ValueObject\Email;
use Facchini\Domain\ValueObject\UserRole;

use Facchini\Infrastructure\Auth\LdapService;
use DateTimeImmutable;

class AuthController
{
    private MySQLUserRepository $userRepository;
    private array $config;

    public function __construct()
    {
        $this->userRepository = new MySQLUserRepository();
        $this->config = require __DIR__ . '/../../../config/app.php';
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
            error_log("Login Attempt Debug: User=" . print_r($usuario, true));
            error_log("LDAP Config Debug: " . print_r($this->config['ldap'], true));
            
            // 1. Tentar Autenticação via LDAP/AD se habilitado
            if ($this->config['ldap']['enabled']) {
                $ldapService = new LdapService($this->config['ldap']);
                $adUser = $ldapService->authenticate($usuario, $senha);


                if ($adUser) {
                    error_log("AD Authentication Successful for $usuario");
                    $user = $this->userRepository->findByUsername($usuario);

                    if (!$user) {
                        error_log("JIT Provisioning starting for $usuario");
                        try {
                            // Criar novo usuário com objetos de valor conforme a entidade User
                            $user = new User(
                                $adUser['name'] ?? $usuario,
                                new Email($usuario),
                                password_hash($senha, PASSWORD_DEFAULT),
                                new UserRole(UserRole::ADMIN), // Cargo padrão
                                $adUser['department'] ?? null,
                                'bar', // Preferência de gráfico padrão
                                null, // ID (será gerado)
                                1 // Filial padrão
                            );
                            
                            $this->userRepository->save($user);
                            error_log("JIT Provisioning successful for $usuario. User ID: " . $user->getId());
                        } catch (\Throwable $e) {
                            error_log("JIT Provisioning FAILED for $usuario: " . $e->getMessage());
                            throw $e;
                        }
                    }

                    $this->setSession($user);
                    error_log("Session set for AD User: " . $user->getEmail()->getValue());
                    header("Location: " . BASE_PATH . "/dashboard");
                    exit;
                }
            }

            // 2. Fallback: Autenticação Local (Banco de Dados)
            error_log("Attempting Local Fallback for $usuario");
            $user = $this->userRepository->findByUsername($usuario);
            
            if ($user && password_verify($senha, $user->getPasswordHash())) {
                error_log("Local Authentication Successful for $usuario");
                $this->setSession($user);
                header("Location: " . BASE_PATH . "/dashboard");
                exit;
            }

            error_log("Authentication Failed for $usuario: User not found or password mismatch");
            $_SESSION['error'] = "Usuário não encontrado ou senha incorreta.";
            header("Location: " . BASE_PATH . "/login");
            exit;

        } catch (\Throwable $e) {
            error_log("CRITICAL LOGIN ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $_SESSION['error'] = "Erro interno no servidor: " . $e->getMessage();
            header("Location: " . BASE_PATH . "/login");
            exit;
        }
    }




    private function setSession(User $user)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_nome'] = $user->getName();
        $_SESSION['user_email'] = $user->getEmail()->getValue();
        $_SESSION['user_cargo'] = $user->getRole()->getValue();
        $_SESSION['user_setor'] = $user->getSectorName();
        $_SESSION['user_filial_id'] = $user->getFilialId();
        $_SESSION['active_filial_id'] = $user->getFilialId() ?? 1;
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

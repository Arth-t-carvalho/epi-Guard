<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Persistence\PostgreSQLUserRepository;

class SettingsController
{
    public function index(): void
    {
        // 1. Garantir o início da sessão para resgatar dados do usuário
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 3. Busca de EPIs para configuração de cores
        $epiRepo = new \Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository();
        $episData = $epiRepo->findAllForSettings();

        // 4. Busca dados do Usuário logado
        $userRepo = new \Facchini\Infrastructure\Persistence\PostgreSQLUserRepository();
        $currentUser = null;
        if (isset($_SESSION['user_id'])) {
            $currentUser = $userRepo->findById((int)$_SESSION['user_id']);
        }

        // 4. Inject de Metadados da Página (Estilos, Títulos e Scripts)
        $pageTitle = 'Configurações - Facchini';
        
        $extraScripts = '';
        
        $extraHead = '<link rel="stylesheet" href="' . BASE_PATH . '/assets/css/settings.css">';

        // 4. Renderização (Embutindo a View dentro de um Layout base)
        ob_start();
        include __DIR__ . '/../View/settings/index.php';
        $content = ob_get_clean();

        // O main.php deve imprimir a variável $content para mostrar a tela
        include __DIR__ . '/../View/layout/main.php';
    }
}

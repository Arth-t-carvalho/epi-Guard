<?php
declare(strict_types=1);

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\MySQLUserRepository;

class SettingsController
{
    public function index(): void
    {
        // 1. Garantir o início da sessão para resgatar dados do usuário
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Busca do email do usuário caso não exista na sessão
        if (isset($_SESSION['user_id']) && !isset($_SESSION['user_email'])) {
            $repo = new MySQLUserRepository();
            $user = $repo->findById((int)$_SESSION['user_id']);
            if ($user) {
                $_SESSION['user_email'] = $user->getEmail()->getValue();
            }
        }

        // 3. Inject de Metadados da Página (Estilos, Títulos e Scripts)
        $pageTitle = 'Configurações - EPI Guard';
        
        $extraScripts = '<script>
            function toggleTheme() {
                const isDark = document.documentElement.classList.toggle("dark-theme");
                localStorage.setItem("epiguard-theme", isDark ? "dark" : "light");
                
                const themeIcon = document.getElementById("theme-icon-display");
                if (themeIcon) {
                    themeIcon.setAttribute("data-lucide", isDark ? "sun" : "moon");
                }
                const themeLabel = document.getElementById("theme-text-display");
                if (themeLabel) {
                    themeLabel.textContent = isDark ? "Tema Claro" : "Tema Escuro";
                }
                
                if (window.lucide) {
                    lucide.createIcons();
                }
            }
        </script>';
        
        $extraHead = '<link rel="stylesheet" href="' . BASE_PATH . '/assets/css/settings.css">';

        // 4. Renderização (Embutindo a View dentro de um Layout base)
        ob_start();
        include __DIR__ . '/../View/settings/index.php';
        $content = ob_get_clean();

        // O main.php deve imprimir a variável $content para mostrar a tela
        include __DIR__ . '/../View/layout/main.php';
    }
}

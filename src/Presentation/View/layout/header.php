<?php
$userNameHeader = $_SESSION['user_nome'] ?? 'Usuário';
$userFirstName = explode(' ', trim($userNameHeader))[0];
$userRoleHeader = $_SESSION['user_cargo'] ?? 'Membro';

// Iniciais para o avatar
$nameParts = explode(' ', trim($userNameHeader));
$initials = strtoupper(substr($nameParts[0], 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}

$routeTitles = [
    '/dashboard' => 'Dashboard',
    '/monitoring' => 'Monitoramento IA',
    '/infractions' => 'Gestão de Infrações',
    '/management/departments' => 'Gestão de Setores',
    '/management/employees' => 'Funcionários',
    '/management/history' => 'Histórico de EPIs',
    '/settings' => 'Configurações'
];

$headerTitle = $routeTitles[$currentRoute] ?? 'Seu Espaço';
?>
<header class="header">
    <div class="page-title">
        <h1><?= htmlspecialchars($headerTitle) ?></h1>
        <p>Olá <?= htmlspecialchars($userFirstName) ?>, bem-vindo de volta!</p>
    </div>

    <div class="header-actions">

        <!-- Notificações -->
        <button class="header-icon-btn notification-btn">
            <i data-lucide="bell"></i>
            <span class="notification-badge">3</span>
        </button>

        <!-- Configurações -->
        <a href="<?= BASE_PATH ?>/settings" class="header-icon-btn" style="display: inline-flex; text-decoration: none; align-items: center; justify-content: center;" onclick="<?= ($currentRoute === '/settings') ? 'event.preventDefault();' : '' ?>">
            <i data-lucide="settings"></i>
        </a>

        <button class="btn-export">
            <i class="fa-solid fa-download"></i> Exportar
        </button>
        
        <div class="user-profile-trigger">
            <div class="user-info-mini">
                <span class="user-name"><?= htmlspecialchars($userFirstName) ?></span>
                <span class="user-role"><?= htmlspecialchars(ucfirst(strtolower(str_replace('_', ' ', $userRoleHeader)))) ?></span>
            </div>
            <div class="user-avatar">
                <?= htmlspecialchars($initials) ?>
            </div>
        </div>
    </div>
</header>

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
    '/dashboard' => __('Dashboard'),
    '/monitoring' => __('Monitoramento IA'),
    '/infractions' => __('Gestão de Infrações'),
    '/management/departments' => __('Gestão de Setores'),
    '/management/employees' => __('Funcionários'),
    '/management/history' => __('Histórico de EPIs'),
    '/settings' => __('Configurações')
];

$headerTitle = $routeTitles[$currentRoute] ?? __('Seu Espaço');
?>
<header class="header">
    <div class="page-title">
        <h1><?= htmlspecialchars($headerTitle) ?></h1>
        <p><?= sprintf(__('Olá %s, bem-vindo de volta!'), htmlspecialchars($userFirstName)) ?></p>
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
            <i class="fa-solid fa-download"></i> <?= __('Exportar') ?>
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


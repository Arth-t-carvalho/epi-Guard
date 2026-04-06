<?php
$userName = $_SESSION['user_nome'] ?? 'Arthur';
$userRole = $_SESSION['user_cargo'] ?? 'Gestor de Segurança';
$userEmail = $_SESSION['user_email'] ?? 'arthur@facchini.com.br';

// Calcular iniciais para o avatar
$nameParts = explode(' ', trim($userName));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
} else {
    $initials = strtoupper(substr($userName, 0, 2));
}

// Mapeamento de cargos para exibição amigável (Setor)
$roleDisplay = [
    'ADMIN' => __('Administrador do Sistema'),
    'SUPER_ADMIN' => __('Administrador Master'),
    'OPERATOR' => __('Supervisor de Operações'),
    'SUPERVISOR' => __('Supervisor de Segurança'),
    'MANAGER' => __('Gerente de Segurança'),
    'GERENTE_SEGURANCA' => __('Gerente de Segurança'),
    'VIEWER' => __('Observador')
];
$displayRole = $roleDisplay[strtoupper($userRole)] ?? $userRole;
?>
<header class="header">
    <div id="epi-parade" class="epi-parade">
        <i class="fa-solid fa-helmet-safety"></i>
        <i class="fa-solid fa-glasses"></i>
        <i class="fa-solid fa-vest"></i>
        <i class="fa-solid fa-helmet-safety"></i>
        <i class="fa-solid fa-glasses"></i>
        <i class="fa-solid fa-vest"></i>
    </div>
    <div class="page-title">
        <div id="welcome-truck-container" class="welcome-container">
            <span class="truck-icon"><i class="fa-solid fa-truck-moving"></i></span>
            <p class="welcome-text"><?= __('Olá') ?>, <?= __('bem-vindo de volta!') ?>
            </p>
        </div>
    </div>

    <div class="header-actions">

        <!-- Export Button (Only for Dashboard) -->
        <button class="header-export-btn" id="headerExportDashboardBtn" style="display: none;"
            onclick="exportDashboardData && exportDashboardData()">
            <i class="fa-solid fa-download"></i> <span><?= __('Exportar') ?></span>
        </button>

        <!-- Notificações Dropdown -->
        <div style="position: relative; display: flex;">
            <!-- Botão principal do sino com o contador -->
            <button class="header-icon-btn notification-btn" id="notifBtn">
                <i data-lucide="bell"></i>
                <span class="notification-badge visible" id="notifBadge" style="display:none;">0</span>
            </button>

            <!-- Modal (Dropdown) -->
            <div class="notification-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <span><?= __('Notificações') ?></span>
                    <button class="notif-clear-btn" id="notifClearBtn" style="display:none;"><?= __('Lidas') ?></button>
                </div>

                <div class="notif-list" id="notifList">
                </div>

                <div class="notif-dropdown-footer">
                    <a href="<?= BASE_PATH ?>/infractions" class="notif-view-all">
                        <?= __('Ver todas as notificações') ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Idioma -->
        <?php $currentLang = $_COOKIE['facchini-lang'] ?? 'pt-br'; ?>
        <button class="header-lang-btn" onclick="changeLanguage('<?= $currentLang === 'en' ? 'pt-br' : 'en' ?>')"
            title="<?= $currentLang === 'en' ? 'Mudar para Português' : 'Switch to English' ?>">
            <?= $currentLang === 'en' ? 'EN' : 'BR' ?>
        </button>

        <!-- Filial Switcher -->
        <?php 
        $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
        $db = \Facchini\Infrastructure\Database\Connection::getInstance();
        $filiaisRes = $db->query("SELECT id, nome FROM filiais ORDER BY id ASC");
        $filiais = [];
        $activeFilialName = 'Aparecida do Taboado';
        while($f = $filiaisRes->fetch_assoc()) {
            $filiais[] = $f;
            if ($f['id'] == $activeFilialId) $activeFilialName = $f['nome'];
        }
        ?>
        <div style="position: relative;">
            <button class="header-branch-btn" onclick="document.getElementById('branchDropdown').classList.toggle('show')">
                <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i>
                <span style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= htmlspecialchars($activeFilialName) ?>
                </span>
                <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: auto;"></i>
            </button>
            <div id="branchDropdown" class="branch-dropdown">
                <?php foreach($filiais as $branch): ?>
                    <div class="branch-item <?= $branch['id'] == $activeFilialId ? 'active' : '' ?>" 
                         onclick="switchBranch(<?= $branch['id'] ?>)">
                        <i class="fa-solid fa-building" style="font-size: 12px; opacity: 0.7;"></i>
                        <?= htmlspecialchars($branch['nome']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
            function switchBranch(id) {
                fetch('<?= BASE_PATH ?>/api/branch/switch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ filial_id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
            }

            // Fechar ao clicar fora
            document.addEventListener('click', (e) => {
                const dropdown = document.getElementById('branchDropdown');
                const btn = dropdown.previousElementSibling;
                if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        </script>


        <div style="position: relative;" id="profileContainer">
            <div class="user-profile-trigger" id="profileTrigger">
                <div class="user-info-mini">
                    <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                    <span class="user-role"><?= htmlspecialchars($displayRole) ?></span>
                </div>
                <div class="user-avatar">
                    <?= $initials ?>
                </div>
            </div>

            <!-- Dropdown de Perfil (Posicionado abaixo do perfil) -->
            <div class="instructor-card" id="userProfileModal"
                style="position: absolute; top: calc(100% + 5px); right: 0; width: 100%; z-index: 100000; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: none;">
                <div
                    style="display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                    <div class="user-avatar"
                        style="width: 60px; height: 60px; font-size: 24px; background: var(--primary);">
                        <?= $initials ?>
                    </div>
                    <div style="text-align: center;">
                        <h4 style="margin: 0; font-size: 16px; color: var(--secondary);">
                            <?= htmlspecialchars($userName) ?></h4>
                        <span
                            style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($displayRole) ?></span>
                    </div>
                </div>

                <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                    <div class="detail-row"
                        style="padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
                        <span class="detail-label"><?= __('Setor') ?></span>
                        <span
                            class="detail-value"><?= htmlspecialchars($_SESSION['user_setor'] ?? __('Não atribuído')) ?></span>
                    </div>
                    <div class="detail-row" style="padding: 8px 0; border: none; font-size: 13px;">
                        <span class="detail-label"><?= __('E-mail') ?></span>
                        <span class="detail-value"
                            style="font-size: 11px; word-break: break-all;"><?= htmlspecialchars($userEmail) ?></span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="<?= BASE_PATH ?>/logout" class="btn-liquid"
                        style="padding: 10px; font-size: 12px; text-decoration: none; display: block; text-align: center;">
                        <span class="btn-text">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> <?= __('Sair') ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

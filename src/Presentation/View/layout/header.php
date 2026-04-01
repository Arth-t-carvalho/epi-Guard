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
            <p class="welcome-text"><?= __('Olá') ?> <?= $_SESSION['user_nome'] ?? 'Arthur' ?>, <?= __('bem-vindo de volta!') ?></p>
        </div>
    </div>

    <div class="header-actions">


        <!-- Notificações Dropdown -->
        <div style="position: relative; display: flex;">
            <!-- Botão principal do sino com o contador -->
            <button class="header-icon-btn notification-btn" id="notifBtn">
                <i data-lucide="bell"></i>
                <!-- O badge começa escondido (display:none). O JS controla a visibilidade -->
                <span class="notification-badge" id="notifBadge" style="display:none;">0</span>
            </button>
            
            <!-- Modal (Dropdown) que aparece ao clicar no sino -->
            <div class="notification-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <span><?= __('Notificações') ?></span>
                    <button class="notif-clear-btn" id="notifClearBtn" style="display:none;">Lidas</button>
                </div>
                
                <!-- Lista de notificações — preenchida pelo JavaScript -->
                <div class="notif-list" id="notifList">
                    <div class="notif-empty" id="notifEmpty">
                        <i data-lucide="bell-off"></i>
                        <span><?= __('Nenhuma infração nova') ?></span>
                    </div>
                </div>

                <!-- Footer com link para página completa -->
                <div class="notif-dropdown-footer">
                    <a href="<?= BASE_PATH ?>/infractions" class="notif-view-all">
                        <?= __('Ver todas as notificações') ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Configurações -->
        <button class="header-icon-btn">
            <i data-lucide="settings"></i>
        </button>

        <button class="btn-export" onclick="exportData()">
            <i class="fa-solid fa-download"></i> <?= __('Exportar') ?>
        </button>
        
        <div class="user-profile-container">
            <div class="user-profile-trigger" id="profileTrigger">
                <div class="user-info-mini">
                    <span class="user-name"><?= $_SESSION['user_nome'] ?? 'arthur' ?></span>
                    <span class="user-role"><?= $_SESSION['user_cargo'] ?? 'Super_admin' ?></span>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user_nome'] ?? 'AR', 0, 2)) ?>
                </div>
            </div>

<!-- Perfil do Usuário movido para main.php para garantir visibilidade global -->
        </div>
    </div>
</header>


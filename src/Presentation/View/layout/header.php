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
            <p class="welcome-text">Olá Arthur, bem-vindo de volta!</p>
        </div>
    </div>

    <div class="header-actions">

        <!-- Notificações -->
        <button class="header-icon-btn notification-btn" id="notificationBellBtn" onclick="toggleNotificationPanel()">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge hidden" id="bell-badge">0</span>
        </button>

        <!-- Painel de Notificações -->
        <div class="notification-panel" id="notificationPanel">
            <div class="notification-panel-header">
                <h3><i class="fa-solid fa-bell"></i> Notificações</h3>
                <button class="notification-panel-close" onclick="toggleNotificationPanel()">&times;</button>
            </div>
            <div class="notification-panel-body" id="notificationPanelBody">
                <div class="notification-empty">
                    <i class="fa-solid fa-check-circle"></i>
                    <p>Nenhuma notificação pendente</p>
                </div>
            </div>
        </div>

        <!-- Configurações -->
        <button class="header-icon-btn">
            <i data-lucide="settings"></i>
        </button>

        <button class="btn-export">
            <i class="fa-solid fa-download"></i> Exportar
        </button>
        
        <div class="user-profile-trigger">
            <div class="user-info-mini">
                <span class="user-name">arthur</span>
                <span class="user-role">Super_admin</span>
            </div>
            <div class="user-avatar">
                AR
            </div>
        </div>
    </div>
</header>

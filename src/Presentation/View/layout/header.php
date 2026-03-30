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

        <!-- Botão de Simulação Real -->
        <button class="header-icon-btn" onclick="testNotification()" style="color: #3b82f6;" title="Simular Detecção Real (Banco de Dados)">
            <i class="fa-solid fa-vial"></i>
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
                    <span>Notificações</span>
                    <button class="notif-clear-btn" id="notifClearBtn" style="display:none;">Lidas</button>
                </div>
                
                <div class="notif-list" id="notifList">
                    <div class="notif-empty" id="notifEmpty">
                        <i data-lucide="bell-off"></i>
                        <span>Nenhuma infração nova</span>
                    </div>
                </div>

                <div class="notif-dropdown-footer">
                    <a href="<?= BASE_PATH ?>/infractions" class="notif-view-all">
                        Ver todas as notificações <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Configurações -->
        <button class="header-icon-btn">
            <i data-lucide="settings"></i>
        </button>

        <button class="btn-export" onclick="exportData()">
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

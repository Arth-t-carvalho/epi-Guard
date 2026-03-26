<?php
// Garantir que a sessão ou variáveis necessárias existem
$userName = $_SESSION['user_nome'] ?? 'Administrador';
$userRole = $_SESSION['user_cargo'] ?? 'Gestor de Segurança';
$userEmail = $_SESSION['user_email'] ?? 'admin@epiguard.com';
?>

<div class="settings-container fade-in">
    <div class="settings-header">
        <div>
            <h1>Configurações do Sistema</h1>
            <p>Gerencie suas preferências, aparência e alertas do EPI Guard.</p>
        </div>
    </div>

    <div class="settings-grid">
        
        <!-- CARD 1: Aparência e Interface -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper">
                    <i data-lucide="palette"></i>
                </div>
                <h2>Aparência</h2>
            </div>
            <div class="settings-card-body">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Modo Escuro (Dark Mode)</h3>
                        <p>Alterne entre cores claras e escuras para preservar a visão.</p>
                    </div>
                    <div class="setting-action">
                        <button id="btnToggleTheme" class="btn-theme-toggle" onclick="toggleTheme()">
                            <i id="theme-icon-display" data-lucide="moon"></i>
                            <span id="theme-text-display">Mudar Tema</span>
                        </button>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Idioma do Sistema</h3>
                        <p>Preferência regional para alertas e datas.</p>
                    </div>
                    <div class="setting-action">
                        <select class="settings-select">
                            <option value="pt-br" selected>Português (Brasil)</option>
                            <option value="en">English (US)</option>
                            <option value="es">Español</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Notificações -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper alert-icon">
                    <i data-lucide="bell"></i>
                </div>
                <h2>Notificações</h2>
            </div>
            <div class="settings-card-body">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Alertas de Infração por E-mail</h3>
                        <p>Receba um e-mail imediato sempre que uma infração Grave for registrada.</p>
                    </div>
                    <div class="setting-action">
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Resumo Semanal</h3>
                        <p>Relatório de conformidade enviado toda sexta-feira.</p>
                    </div>
                    <div class="setting-action">
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Informações da Conta -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper account-icon">
                    <i data-lucide="user"></i>
                </div>
                <h2>Sua Conta</h2>
            </div>
            <div class="settings-card-body">
                <div class="profile-summary">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="profile-details">
                        <h4><?= htmlspecialchars($userName) ?></h4>
                        <span><?= htmlspecialchars($userRole) ?></span>
                        <span class="profile-email"><?= htmlspecialchars($userEmail) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 4: Segurança do Flow -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper shield-icon">
                    <i data-lucide="shield-check"></i>
                </div>
                <h2>Regras de Ocorrência</h2>
            </div>
            <div class="settings-card-body">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Exigir Foto (Evidência)</h3>
                        <p>Obriga o preenchimento de imagem fotográfica no registro de qualquer nova Infração.</p>
                    </div>
                    <div class="setting-action">
                        <label class="switch">
                            <input type="checkbox">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var isDark = document.documentElement.classList.contains('dark-theme');
    const themeIcon = document.getElementById("theme-icon-display");
    const themeLabel = document.getElementById("theme-text-display");
    if (themeIcon) {
        themeIcon.setAttribute("data-lucide", isDark ? "sun" : "moon");
    }
    if (themeLabel) {
        themeLabel.textContent = isDark ? "Tema Claro" : "Tema Escuro";
    }
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>

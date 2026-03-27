<?php
// Garantir que a sessão ou variáveis necessárias existem
$userName = $_SESSION['user_nome'] ?? 'Administrador';
$userRole = $_SESSION['user_cargo'] ?? 'Gestor de Segurança';
$userEmail = $_SESSION['user_email'] ?? 'admin@epiguard.com';
?>

<div class="settings-container fade-in">
    <div class="settings-header">
        <div>
            <h1><?= __('Configurações do Sistema') ?></h1>
            <p><?= __('Gerencie suas preferências, aparência e alertas do EPI Guard.') ?></p>
        </div>
    </div>

    <div class="settings-grid">
        
        <!-- CARD 1: Aparência e Interface -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper">
                    <i data-lucide="palette"></i>
                </div>
                <h2><?= __('Aparência') ?></h2>
            </div>
            <div class="settings-card-body">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3><?= __('Modo Escuro (Dark Mode)') ?></h3>
                        <p><?= __('Alterne entre cores claras e escuras para preservar a visão.') ?></p>
                    </div>
                    <div class="setting-action">
                        <button id="btnToggleTheme" class="btn-theme-toggle" onclick="toggleTheme()">
                            <i id="theme-icon-display" data-lucide="moon"></i>
                            <span id="theme-text-display"><?= __('Mudar Tema') ?></span>
                        </button>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3><?= __('Idioma do Sistema') ?></h3>
                        <p><?= __('Preferência regional para alertas e datas.') ?></p>
                    </div>
                    <div class="setting-action">
                        <select class="settings-select" id="languageSelect" onchange="changeLanguage(this.value)">
                            <option value="pt-br" <?= ($_COOKIE['epiguard-lang'] ?? 'pt-br') === 'pt-br' ? 'selected' : '' ?>>Português (Brasil)</option>
                            <option value="en" <?= ($_COOKIE['epiguard-lang'] ?? '') === 'en' ? 'selected' : '' ?>>English (US)</option>
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
                <h2><?= __('Notificações') ?></h2>
            </div>
            <div class="settings-card-body">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3><?= __('Alertas de Infração por E-mail') ?></h3>
                        <p><?= __('Receba um e-mail imediato sempre que uma infração Grave for registrada.') ?></p>
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
                        <h3><?= __('Resumo Semanal') ?></h3>
                        <p><?= __('Relatório de conformidade enviado toda sexta-feira.') ?></p>
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
                <h2><?= __('Sua Conta') ?></h2>
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
                <h2><?= __('Regras de Ocorrência') ?></h2>
            </div>
            <div class="settings-card-body">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3><?= __('Exigir Foto (Evidência)') ?></h3>
                        <p><?= __('Obriga o preenchimento de imagem fotográfica no registro de qualquer nova Infração.') ?></p>
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
function changeLanguage(lang) {
    // Set cookie so PHP can read it on next page load
    document.cookie = "epiguard-lang=" + lang + ";path=/;max-age=31536000;SameSite=Lax";
    // Also keep localStorage for backward compat
    localStorage.setItem('epiguard-lang', lang);
    // Reload page so PHP renders the new language
    window.location.reload();
}

document.addEventListener("DOMContentLoaded", function() {
    var isDark = document.documentElement.classList.contains('dark-theme');
    const themeIcon = document.getElementById("theme-icon-display");
    const themeLabel = document.getElementById("theme-text-display");
    if (themeIcon) {
        themeIcon.setAttribute("data-lucide", isDark ? "sun" : "moon");
    }
    if (themeLabel) {
        themeLabel.textContent = isDark ? "<?= __('Tema Claro') ?>" : "<?= __('Tema Escuro') ?>";
    }
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>

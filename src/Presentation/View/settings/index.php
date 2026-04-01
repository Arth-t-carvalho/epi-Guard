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
        <!-- CARD 1: Aparência e Interface (FULL WIDTH) -->
        <div class="settings-card full-width">
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
                            <option value="pt-br" <?= ($_COOKIE['epiguard-lang'] ?? 'pt-br') === 'pt-br' ? 'selected' : '' ?>><?= __('Português (Brasil)') ?></option>
                            <option value="en" <?= ($_COOKIE['epiguard-lang'] ?? '') === 'en' ? 'selected' : '' ?>><?= __('English (US)') ?></option>
                        </select>
                <div class="section-divider-mini"></div>

                <div class="setting-group-title">
                    <h3><?= __('Cores dos Gráficos') ?></h3>
                    <p><?= __('Personalize as cores utilizadas nos gráficos da Dashboard.') ?></p>
                </div>
                
                <div class="epi-color-grid">
                    <?php foreach ($epis as $epi): ?>
                        <div class="epi-color-item">
                            <div class="epi-info">
                                <?php
                                $iconClass = 'fa-shield';
                                $nameLower = strtolower($epi->getName());
                                if (strpos($nameLower, 'capacete') !== false) $iconClass = 'fa-hard-hat';
                                elseif (strpos($nameLower, 'oculos') !== false || strpos($nameLower, 'óculos') !== false) $iconClass = 'fa-glasses';
                                elseif (strpos($nameLower, 'luva') !== false) $iconClass = 'fa-mitten';
                                elseif (strpos($nameLower, 'avental') !== false) $iconClass = 'fa-shirt';
                                elseif (strpos($nameLower, 'mascara') !== false || strpos($nameLower, 'máscara') !== false) $iconClass = 'fa-mask-face';
                                elseif (strpos($nameLower, 'bota') !== false) $iconClass = 'fa-boot';
                                ?>
                                <div class="epi-icon-preview" style="background-color: <?= $epi->getColor() ?>">
                                    <i class="fa-solid <?= $iconClass ?>"></i>
                                </div>
                                <span class="epi-name"><?= htmlspecialchars($epi->getName()) ?></span>
                            </div>
                            <div class="epi-color-action">
                                <input type="color" class="epi-color-input" 
                                       id="epi-color-<?= $epi->getId() ?>"
                                       value="<?= $epi->getColor() ?>"
                                       onchange="updateEpiColorPreview(<?= $epi->getId() ?>, this.value)">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="settings-actions">
                    <button class="btn-save-colors" onclick="saveEpiColors()">
                        <i data-lucide="save"></i> <?= __('Salvar Cores') ?>
                    </button>
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

<style>
/* Estilos para a nova seção de cores de EPI */
.settings-grid .settings-card.full-width {
    grid-column: 1 / -1;
}

.section-divider-mini {
    height: 1px;
    background: #e2e8f0;
    margin: 24px 0;
}

html.dark-theme .section-divider-mini {
    background: #334155;
}

.setting-group-title {
    margin-bottom: 16px;
}

.setting-group-title h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 4px;
}

.setting-group-title p {
    font-size: 12px;
    color: #64748b;
}

html.dark-theme .setting-group-title p {
    color: #94a3b8;
}

.epi-color-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.epi-color-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: 0.2s;
}

html.dark-theme .epi-color-item {
    background: #1e293b;
    border-color: #334155;
}

.epi-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.epi-icon-preview {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.epi-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
}

.epi-color-input {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    background: transparent;
}

.epi-color-input::-webkit-color-swatch-wrapper {
    padding: 0;
}

.epi-color-input::-webkit-color-swatch {
    border: 2px solid white;
    border-radius: 8px;
    box-shadow: 0 0 0 1px #e2e8f0;
}

.settings-actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}

.btn-save-colors {
    padding: 10px 24px;
    background: #E30613;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: 0.2s;
}

.btn-save-colors:hover {
    background: #c40510;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(227, 6, 19, 0.3);
}
</style>

<script>
function changeLanguage(lang) {
    document.cookie = "epiguard-lang=" + lang + ";path=/;max-age=31536000;SameSite=Lax";
    localStorage.setItem('epiguard-lang', lang);
    window.location.reload();
}

function toggleTheme() {
    const isDark = document.documentElement.classList.toggle("dark-theme");
    localStorage.setItem("epiguard-theme", isDark ? "dark" : "light");
    
    const themeIcon = document.getElementById("theme-icon-display");
    if (themeIcon) {
        themeIcon.setAttribute("data-lucide", isDark ? "sun" : "moon");
    }
    const themeLabel = document.getElementById("theme-text-display");
    if (themeLabel) {
        themeLabel.textContent = isDark ? "<?= __('Tema Claro') ?>" : "<?= __('Tema Escuro') ?>";
    }
    
    if (window.lucide) {
        lucide.createIcons();
    }
}

function updateEpiColorPreview(id, color) {
    const input = document.querySelector(`#epi-color-${id}`);
    const preview = input.closest('.epi-color-item').querySelector('.epi-icon-preview');
    if (preview) {
        preview.style.backgroundColor = color;
    }
}

async function saveEpiColors() {
    const inputs = document.querySelectorAll('.epi-color-input');
    const colors = [];
    
    inputs.forEach(input => {
        colors.push({
            id: input.id.replace('epi-color-', ''),
            color: input.value
        });
    });

    const btn = document.querySelector('.btn-save-colors');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <?= __('Salvando...') ?>';

    try {
        const response = await fetch('<?= BASE_PATH ?>/api/epis/update-colors', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ colors })
        });
        
        if (response.ok) {
            alert('<?= __('Cores dos EPIs atualizadas com sucesso!') ?>');
        } else {
            throw new Error('Falha ao salvar cores');
        }
    } catch (error) {
        console.error(error);
        alert('<?= __('Não foi possível salvar as cores.') ?>');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (window.lucide) lucide.createIcons();
    }
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


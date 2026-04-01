<?php
// Garantir que a sessão ou variáveis necessárias existem
$userName = $_SESSION['user_nome'] ?? __('Administrador');
$userRole = $_SESSION['user_cargo'] ?? __('Gestor de Segurança');
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
                            <option value="pt-br" <?= ($_COOKIE['epiguard-lang'] ?? 'pt-br') === 'pt-br' ? 'selected' : '' ?>><?= __('Português (Brasil)') ?></option>
                            <option value="en" <?= ($_COOKIE['epiguard-lang'] ?? '') === 'en' ? 'selected' : '' ?>>
                                <?= __('English (US)') ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Estilo Visual dos Gráficos -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper alert-icon">
                    <i data-lucide="layout"></i>
                </div>
                <h2><?= __('Estilo Visual dos Gráficos') ?></h2>
            </div>
            <div class="settings-card-body">
                <p class="section-description">
                    <?= __('Escolha como você deseja visualizar os dados de tendência mensal no Dashboard.') ?></p>

                <?php
                $currentStyle = $currentUser ? $currentUser->getChartPreference() : 'bar';
                ?>

                <div class="chart-style-picker">
                    <div class="style-option <?= $currentStyle === 'bar' ? 'active' : '' ?>"
                        onclick="updateChartStyle('bar', this)">
                        <div class="style-icon">
                            <i data-lucide="bar-chart-3"></i>
                        </div>
                        <div class="style-info">
                            <span class="style-title"><?= __('Barras') ?></span>
                            <span class="style-desc"><?= __('Visão clássica por colunas') ?></span>
                        </div>
                        <div class="style-check">
                            <i data-lucide="check-circle-2"></i>
                        </div>
                    </div>

                    <div class="style-option <?= $currentStyle === 'line' ? 'active' : '' ?>"
                        onclick="updateChartStyle('line', this)">
                        <div class="style-icon">
                            <i data-lucide="trending-up"></i>
                        </div>
                        <div class="style-info">
                            <span class="style-title"><?= __('Linhas') ?></span>
                            <span class="style-desc"><?= __('Conecte os dados com precisão') ?></span>
                        </div>
                        <div class="style-check">
                            <i data-lucide="check-circle-2"></i>
                        </div>
                    </div>

                    <div class="style-option <?= $currentStyle === 'area' ? 'active' : '' ?>"
                        onclick="updateChartStyle('area', this)">
                        <div class="style-icon">
                            <i data-lucide="area-chart"></i>
                        </div>
                        <div class="style-info">
                            <span class="style-title"><?= __('Áreas') ?></span>
                            <span class="style-desc"><?= __('Volume e preenchimento visual') ?></span>
                        </div>
                        <div class="style-check">
                            <i data-lucide="check-circle-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Cores dos Equipamentos -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper epi-icon">
                    <i data-lucide="shield"></i>
                </div>
                <h2><?= __('Cores dos Equipamentos (EPIs)') ?></h2>
            </div>
            <div class="settings-card-body">
                <p class="section-description">
                    <?= __('Personalize as cores que representam cada EPI nos gráficos do sistema.') ?></p>
                <div class="epi-color-grid">
                    <?php
                    $iconMap = [
                        'capacete' => 'hard-hat',
                        'oculos' => 'glasses',
                        'óculos' => 'glasses',
                        'luvas' => 'hand',
                        'luva' => 'hand',
                        'jaqueta' => 'shirt',
                        'avental' => 'user-square',
                        'mascara' => 'mask',
                        'máscara' => 'mask',
                        'protetor' => 'ear',
                        'total' => 'sigma'
                    ];

                    foreach ($episData as $epi):
                        $lowerName = strtolower($epi->getName());
                        $icon = 'shield';
                        if ($lowerName === 'total') {
                            $displayName = __('Geral (Total)');
                            $icon = 'sigma';
                        } else {
                            $displayName = htmlspecialchars(__db($epi, 'name'));
                            foreach ($iconMap as $key => $val) {
                                if (strpos($lowerName, $key) !== false) {
                                    $icon = $val;
                                    break;
                                }
                            }
                        }
                        ?>
                        <div class="epi-color-item">
                            <div class="epi-info">
                                <div class="epi-mini-icon">
                                    <i data-lucide="<?= $icon ?>"></i>
                                </div>
                                <span class="epi-name" style="font-weight: 600; font-size: 15px;"><?= $displayName ?></span>
                            </div>
                            <div class="color-picker-wrapper">
                                <div class="color-preview" id="preview-<?= $epi->getId() ?>"
                                    style="background-color: <?= $epi->getColor() ?>"
                                    onclick="document.getElementById('picker-<?= $epi->getId() ?>').click()">
                                </div>
                                <input type="color" id="picker-<?= $epi->getId() ?>" value="<?= $epi->getColor() ?>"
                                    class="hidden-color-input"
                                    onchange="updateEpiSettings(<?= $epi->getId() ?>, this.value, null)">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="settings-footer-actions">
                    <button class="btn-restore-defaults" onclick="resetEpiColors()">
                        <i data-lucide="rotate-ccw"></i>
                        <?= __('Restaurar Padrões de Cores') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- CARD 4: Notificações -->
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

        <!-- CARD 5: Informações da Conta -->
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
                        <span><?= __($userRole) ?></span>
                        <span class="profile-email"><?= htmlspecialchars($userEmail) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .section-description {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border);
    }

    .epi-color-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 16px;
    }

    .epi-color-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        padding: 20px 24px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        gap: 20px;
        min-height: 85px;
    }

    html.dark-theme .epi-color-item {
        background: rgba(255, 255, 255, 0.02);
    }

    .epi-color-item:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .epi-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .epi-mini-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--bg-body);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
    }

    .epi-mini-icon i {
        width: 18px;
        height: 18px;
    }

    .epi-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--secondary);
        min-width: 120px;
    }

    .epi-name-en-input {
        background: var(--bg-secondary);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 14px;
        color: var(--text-primary);
        width: 100%;
        max-width: 160px;
        transition: all 0.2s ease;
    }

    .epi-name-en-input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 2px rgba(227, 6, 19, 0.05);
    }

    .color-preview {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 50%;
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 0 0 1.5px var(--border), 0 6px 12px rgba(0, 0, 0, 0.12);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .color-preview:hover {
        transform: scale(1.1);
    }

    .hidden-color-input {
        position: absolute;
        width: 0;
        height: 0;
        opacity: 0;
        pointer-events: none;
    }

    .settings-footer-actions {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: center;
    }

    .btn-restore-defaults {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: transparent;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-restore-defaults:hover {
        background: rgba(227, 6, 19, 0.05);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-restore-defaults i {
        width: 16px;
        height: 16px;
    }

    .icon-wrapper.epi-icon {
        background: rgba(227, 6, 19, 0.1);
        color: var(--primary);
    }

    /* Picker de Estilo de Gráfico */
    .chart-style-picker {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .style-option {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: rgba(0, 0, 0, 0.01);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    html.dark-theme .style-option {
        background: rgba(255, 255, 255, 0.01);
    }

    .style-option:hover {
        border-color: var(--primary-light);
        background: rgba(227, 6, 19, 0.02);
    }

    .style-option.active {
        border-color: var(--primary);
        background: rgba(227, 6, 19, 0.05);
        box-shadow: 0 4px 15px rgba(227, 6, 19, 0.08);
    }

    .style-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--bg-body);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: all 0.3s ease;
    }

    .style-option.active .style-icon {
        background: var(--primary);
        color: white;
    }

    .style-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .style-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--secondary);
    }

    .style-desc {
        font-size: 12px;
        color: var(--text-muted);
    }

    .style-check {
        opacity: 0;
        color: var(--primary);
        transform: scale(0.5);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .style-option.active .style-check {
        opacity: 1;
        transform: scale(1);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var isDark = document.documentElement.classList.contains('dark-theme');
        const themeIcon = document.getElementById("theme-icon-display");
        const themeLabel = document.getElementById("theme-text-display");

        // Atualiza botão de tema logo no carregamento com base na class tag HTML
        if (themeIcon) {
            themeIcon.setAttribute("data-lucide", isDark ? "sun" : "moon");
        }
        if (themeLabel) {
            themeLabel.textContent = isDark ? "<?= __('Tema Claro') ?>" : "<?= __('Tema Escuro') ?>";
        }

        // Inicia a renderização de ícones para o modo respectivo
        if (window.lucide) {
            lucide.createIcons();
        }
    });

    async function updateEpiSettings(id, color, nomeEn) {
        if (color) {
            const preview = document.getElementById(`preview-${id}`);
            if (preview) {
                preview.style.backgroundColor = color;
            }
        }

        try {
            const payload = { id };
            if (color !== null) payload.color = color;
            if (nomeEn !== null) payload.nome_en = nomeEn;

            const response = await fetch(`${window.BASE_PATH}/api/settings/epi-color`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (result.success) {
                showToast("<?= __('Configurações atualizadas!') ?>", "success");
            } else {
                showToast("<?= __('Erro ao atualizar.') ?>", "error");
            }
        } catch (error) {
            console.error('Erro ao atualizar configurações do EPI:', error);
            showToast("<?= __('Erro de conexão.') ?>", "error");
        }
    }

    async function resetEpiColors() {
        if (!confirm("<?= __('Tem certeza que deseja restaurar todas as cores para o padrão original?') ?>")) {
            return;
        }

        try {
            const response = await fetch(`${window.BASE_PATH}/api/settings/reset-colors`, {
                method: 'POST'
            });

            const result = await response.json();
            if (result.success) {
                showToast("<?= __('Cores restauradas com sucesso!') ?>", "success");
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast("<?= __('Erro ao restaurar cores.') ?>", "error");
            }
        } catch (error) {
            console.error('Erro ao restaurar cores:', error);
            showToast("<?= __('Erro de conexão.') ?>", "error");
        }
    }

    async function updateChartStyle(style, element) {
        // UI Feedback imediato
        document.querySelectorAll('.style-option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');

        try {
            const response = await fetch(`${window.BASE_PATH}/api/settings/chart-style`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ style })
            });

            const result = await response.json();
            if (result.success) {
                showToast("<?= __('Estilo de gráfico atualizado!') ?>", "success");
            } else {
                showToast("<?= __('Erro ao salvar preferência.') ?>", "error");
            }
        } catch (error) {
            console.error('Erro ao atualizar estilo de gráfico:', error);
            showToast("<?= __('Erro de conexão.') ?>", "error");
        }
    }

    function showToast(message, type) {
        const container = document.getElementById('notification-container') || document.body;
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} fade-in`;
        toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 24px;
        border-radius: 8px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
        toast.innerText = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
</script>
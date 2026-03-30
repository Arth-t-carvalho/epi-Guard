<?php
// Função Helper de Tradução Funcional
if (!function_exists('__')) {
    function __($str) {
        $lang = $_COOKIE['epiguard-lang'] ?? 'pt-br';
        
        // Dicionário ultra-rápido para a tela de configurações
        $dict = [
            'en' => [
                'Configurações do Sistema' => 'System Settings',
                'Gerencie suas preferências, aparência e alertas do EPI Guard.' => 'Manage your preferences, appearance, and EPI Guard alerts.',
                'Aparência' => 'Appearance',
                'Modo Escuro (Dark Mode)' => 'Dark Mode',
                'Alterne entre cores claras e escuras para preservar a visão.' => 'Switch between light and dark colors to preserve vision.',
                'Mudar Tema' => 'Change Theme',
                'Tema Claro' => 'Light Theme',
                'Tema Escuro' => 'Dark Theme',
                'Idioma do Sistema' => 'System Language',
                'Preferência regional para alertas e datas.' => 'Regional preference for alerts and dates.',
                'Português (Brasil)' => 'Portuguese (Brazil)',
                'English (US)' => 'English (US)',
                'Paleta de Cores dos Gráficos' => 'Chart Color Palette',
                'Personalize as cores utilizadas nos gráficos da Dashboard.' => 'Customize the colors used in the Dashboard charts.',
                'Padrão (Senai)' => 'Default (Senai)',
                'Azul Corporativo' => 'Corporate Blue',
                'Verde Sustentável' => 'Sustainable Green',
                'Roxo Vibrante' => 'Vibrant Purple',
                'Pôr do Sol' => 'Sunset',
                'Oceano' => 'Ocean',
                'Notificações' => 'Notifications',
                'Alertas de Infração por E-mail' => 'Email Infraction Alerts',
                'Receba um e-mail imediato sempre que uma infração Grave for registrada.' => 'Receive an immediate email whenever a Severe infraction is registered.',
                'Resumo Semanal' => 'Weekly Summary',
                'Relatório de conformidade enviado toda sexta-feira.' => 'Compliance report sent every Friday.',
                'Sua Conta' => 'Your Account',
                'Regras de Ocorrência' => 'Occurrence Rules',
                'Exigir Foto (Evidência)' => 'Require Photo (Evidence)',
                'Obriga o preenchimento de imagem fotográfica no registro de qualquer nova Infração.' => 'Forces photographic evidence input when registering any new Infraction.'
            ]
        ];

        if ($lang === 'en' && isset($dict['en'][$str])) {
            return $dict['en'][$str];
        }

        return $str; // Retorna PT-BR padrão
    }
}

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
                            <option value="pt-br" <?= ($_COOKIE['epiguard-lang'] ?? 'pt-br') === 'pt-br' ? 'selected' : '' ?>><?= __('Português (Brasil)') ?></option>
                            <option value="en" <?= ($_COOKIE['epiguard-lang'] ?? '') === 'en' ? 'selected' : '' ?>><?= __('English (US)') ?></option>
                        </select>
                    </div>
                </div>


            </div>
        </div>

        <!-- CARD EXTRA: Cores Individuais dos EPIs -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="icon-wrapper epi-icon tint-icon">
                    <i data-lucide="brush"></i>
                </div>
                <h2><?= __('Personalização por EPI') ?></h2>
                <button class="btn-reset-colors" onclick="resetEpiColors()" title="<?= __('Resetar para cores padrão') ?>">
                    <i data-lucide="rotate-ccw"></i>
                    <span><?= __('Resetar Padrão') ?></span>
                </button>
            </div>
            <div class="settings-card-body">
                <p class="setting-description-text"><?= __('Defina cores específicas para cada equipamento nos gráficos.') ?></p>
                <div class="epi-color-grid">
                    <?php 
                    $epis = [
                        ['id' => 'capacete', 'label' => 'Capacete'],
                        ['id' => 'oculos', 'label' => 'Óculos'],
                        ['id' => 'jaqueta', 'label' => 'Jaqueta'],
                        ['id' => 'avental', 'label' => 'Avental'],
                        ['id' => 'luvas', 'label' => 'Luvas'],
                        ['id' => 'mascara', 'label' => 'Máscara'],
                        ['id' => 'protetor', 'label' => 'Protetor'],
                        ['id' => 'total', 'label' => 'Total'],
                    ];
                    foreach ($epis as $epi): ?>
                    <div class="epi-color-item">
                        <div class="color-picker-wrapper">
                            <input type="color" id="color-<?= $epi['id'] ?>" 
                                   class="epi-color-picker" 
                                   onchange="changeEpiColor('<?= $epi['id'] ?>', this.value)">
                        </div>
                        <label for="color-<?= $epi['id'] ?>"><?= __($epi['label']) ?></label>
                    </div>
                    <?php endforeach; ?>
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
    // Altera o cookie para que o PHP possa entender a nova linguagem
    document.cookie = "epiguard-lang=" + lang + ";path=/;max-age=31536000;SameSite=Lax";
    // Guarda também no localStorage por consistência lateral do front
    localStorage.setItem('epiguard-lang', lang);
    // Recarrega a página para aplicar
    window.location.reload();
}

function resetEpiColors() {
    if (confirm("<?= __('Deseja resetar todas as cores dos EPIs para o padrão?') ?>")) {
        const epis = ['capacete', 'oculos', 'jaqueta', 'avental', 'luvas', 'mascara', 'protetor', 'total'];
        epis.forEach(epi => {
            localStorage.removeItem('epiguard-color-' + epi);
        });
        // Reseta também a paleta global para o padrão oficial Senai
        localStorage.setItem('epiguard-chart-palette', 'default');
        
        loadEpiColors();
        
        // Toast de feedback
        let existing = document.getElementById('paletteToast');
        if (existing) existing.remove();
        const checkSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
        const toast = document.createElement('div');
        toast.id = 'paletteToast';
        toast.className = 'palette-toast';
        toast.innerHTML = checkSVG + ` Cores resetadas para o padrão!`;
        document.body.appendChild(toast);
        requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }
}

function changeEpiColor(epi, color) {
    localStorage.setItem('epiguard-color-' + epi, color);
    showEpiColorToast(epi);
}

function loadEpiColors() {
    const epis = ['capacete', 'oculos', 'jaqueta', 'avental', 'luvas', 'mascara', 'protetor', 'total'];
    const defaults = {
        'capacete': '#1F2937',
        'oculos': '#9CA3AF',
        'jaqueta': '#f59e0b',
        'avental': '#3b82f6',
        'luvas': '#10b981',
        'mascara': '#4b5563', // Mudado de roxo para cinza escuro corporativo
        'protetor': '#6b7280', // Mudado de rosa para cinza médio
        'total': '#E30613'
    };

    epis.forEach(epi => {
        const savedColor = localStorage.getItem('epiguard-color-' + epi) || defaults[epi];
        const input = document.getElementById('color-' + epi);
        if (input) input.value = savedColor;
    });
}

function showEpiColorToast(epi) {
    let existing = document.getElementById('paletteToast');
    if (existing) existing.remove();

    const names = {
        capacete: 'Capacete',
        oculos: 'Óculos',
        jaqueta: 'Jaqueta',
        avental: 'Avental',
        luvas: 'Luvas',
        mascara: 'Máscara',
        protetor: 'Protetor',
        total: 'Total'
    };

    const checkSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;

    const toast = document.createElement('div');
    toast.id = 'paletteToast';
    toast.className = 'palette-toast';
    toast.innerHTML = checkSVG + ` Cor do <strong>${names[epi] || epi}</strong> atualizada!`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}



document.addEventListener("DOMContentLoaded", function() {
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

    loadEpiColors();
    
    // Inicia a renderização de ícones para o modo respectivo
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>

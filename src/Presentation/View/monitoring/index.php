<?php
$pageTitle = 'epiGuard - Monitoramento em Tempo Real';
$extraHead = '
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/monitoring.css">
';

ob_start();
?>

<div class="monitoring-container">
    <div class="monitoring-header">
        <div class="monitoring-title">
            <h1><?= __('Monitoramento em Tempo Real') ?></h1>
            <p style="color: #64748B; font-size: 13px; margin-top: 4px;">
                <?= __('Supervisão de segurança via câmeras inteligentes') ?>
            </p>
        </div>

        <div class="monitoring-actions" style="display: flex; align-items: center; gap: 15px;">
            <!-- Gatilho do Seletor Premium -->
            <div class="premium-select-trigger filter-trigger" id="filterViewTrigger" style="min-width: 240px;">
                <div class="trigger-text" style="display: flex; flex-direction: column; gap: 2px;">
                    <span
                        style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;"><?= __('Filtrar Câmeras') ?></span>
                    <span class="trigger-value"
                        style="font-weight: 800; color: var(--primary);"><?= __('Todas as Câmeras') ?></span>
                </div>
                <i data-lucide="chevron-down" class="lucide"></i>
            </div>

            <div class="prototype-badge">
                <i data-lucide="beaker"></i>
                <?= __('Protótipo') ?>
            </div>
        </div>
    </div>

    <!-- Estrutura do Modal Premium (Injetada Fora do Fluxo) -->
    <div class="premium-select-backdrop" id="monitoringFilterBackdrop">
        <div class="premium-select-modal">
            <div class="premium-select-header">
                <h3 class="premium-select-title"><?= __('Filtrar Visualização') ?></h3>
                <p class="premium-select-subtitle"><?= __('Escolha o setor ou câmera que deseja monitorar') ?></p>
                <button type="button" class="premium-select-close" id="closeFilterModal">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="premium-select-body">
                <!-- Opção: Todas as Câmeras -->
                <div class="premium-option-card selected" data-value="all">
                    <div class="premium-option-icon"><i data-lucide="video"></i></div>
                    <div class="premium-option-info">
                        <span class="premium-option-name"><?= __('Todas as Câmeras') ?></span>
                        <span class="premium-option-id"><?= __('Visão Geral de Planta') ?></span>
                    </div>
                </div>

                <!-- Opção: Soldagem -->
                <div class="premium-option-card" data-value="soldagem">
                    <div class="premium-option-icon"><i data-lucide="zap"></i></div>
                    <div class="premium-option-info">
                        <span class="premium-option-name"><?= __('Setor de Soldagem') ?></span>
                        <span class="premium-option-id">Planta A - Pavimento 01</span>
                    </div>
                </div>

                <!-- Opção: Montagem -->
                <div class="premium-option-card" data-value="montagem">
                    <div class="premium-option-icon"><i data-lucide="package"></i></div>
                    <div class="premium-option-info">
                        <span class="premium-option-name"><?= __('Setor de Montagem') ?></span>
                        <span class="premium-option-id">Planta A - Pavimento 01</span>
                    </div>
                </div>

                <!-- Opção: Almoxarifado -->
                <div class="premium-option-card" data-value="almoxarifado">
                    <div class="premium-option-icon"><i data-lucide="warehouse"></i></div>
                    <div class="premium-option-info">
                        <span class="premium-option-name"><?= __('Almoxarifado') ?></span>
                        <span class="premium-option-id">Planta B - Pavimento 02</span>
                    </div>
                </div>

                <!-- Opção: Logística -->
                <div class="premium-option-card" data-value="logistica">
                    <div class="premium-option-icon"><i data-lucide="truck"></i></div>
                    <div class="premium-option-info">
                        <span class="premium-option-name"><?= __('Carga e Descarga') ?></span>
                        <span class="premium-option-id">Pátio Externo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="camera-grid">
        <!-- Camera 01 -->
        <div class="camera-card" data-sector="soldagem">
            <img src="<?= BASE_PATH ?>/assets/img/monitoring/cam_welding.png" class="camera-feed" alt="Camera 01 Feed">
            <div class="camera-scanline"></div>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag">FEED-01</div>
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        <?= __('LIVE') ?>
                    </div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Setor de Soldagem') ?></h3>
                        <p><?= __('Planta A - Pavimento 01') ?></p>
                    </div>
                    <div class="timestamp" id="time1">--:--:--</div>
                </div>
            </div>
        </div>

        <!-- Camera 02 -->
        <div class="camera-card" data-sector="montagem">
            <img src="<?= BASE_PATH ?>/assets/img/monitoring/cam_assembly.png" class="camera-feed" alt="Camera 02 Feed">
            <div class="camera-scanline"></div>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag">FEED-02</div>
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        <?= __('LIVE') ?>
                    </div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Setor de Montagem') ?></h3>
                        <p><?= __('Planta A - Pavimento 01') ?></p>
                    </div>
                    <div class="timestamp" id="time2">--:--:--</div>
                </div>
            </div>
        </div>

        <!-- Camera 03 (Offline) -->
        <div class="camera-card camera-empty" data-sector="almoxarifado">
            <img src="<?= BASE_PATH ?>/assets/img/monitoring/cam_warehouse.png" class="camera-feed" alt="Camera 03 Feed" style="opacity: 0.3; filter: grayscale(1);">
            <div class="camera-scanline"></div>
            <div class="offline-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 5; text-align: center; color: white;">
                <i data-lucide="video-off" style="font-size: 32px; margin-bottom: 10px; opacity: 0.7;"></i>
                <span style="display: block; font-weight: 800; letter-spacing: 1px; font-size: 13px; opacity: 0.7;"><?= __('CÂMERA DESCONECTADA') ?></span>
            </div>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag" style="opacity: 0.5;">FEED-03</div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Almoxarifado') ?></h3>
                        <p><?= __('Planta B - Pavimento 02') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Camera 04 (Offline) -->
        <div class="camera-card camera-empty" data-sector="logistica">
            <img src="<?= BASE_PATH ?>/assets/img/monitoring/cam_loading.png" class="camera-feed" alt="Camera 04 Feed" style="opacity: 0.3; filter: grayscale(1);">
            <div class="camera-scanline"></div>
            <div class="offline-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 5; text-align: center; color: white;">
                <i data-lucide="video-off" style="font-size: 32px; margin-bottom: 10px; opacity: 0.7;"></i>
                <span style="display: block; font-weight: 800; letter-spacing: 1px; font-size: 13px; opacity: 0.7;"><?= __('AGUARDANDO SINAL') ?></span>
            </div>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag" style="opacity: 0.5;">FEED-04</div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Carga e Descarga') ?></h3>
                        <p><?= __('Pátio Externo') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTimestamps() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString();
        const dateStr = now.toLocaleDateString();
        const fullStr = dateStr + " - " + timeStr;

        const t1 = document.getElementById('time1');
        const t2 = document.getElementById('time2');
        if (t1) t1.textContent = fullStr;
        if (t2) t2.textContent = fullStr;
    }

    setInterval(updateTimestamps, 1000);
    updateTimestamps();

    // Lógica do Filtro Modal Premium
    document.addEventListener('DOMContentLoaded', () => {
        const trigger = document.getElementById('filterViewTrigger');
        const backdrop = document.getElementById('monitoringFilterBackdrop');
        const closeBtn = document.getElementById('closeFilterModal');
        const valueDisplay = trigger.querySelector('.trigger-value');
        const cards = document.querySelectorAll('.camera-card');
        const options = backdrop.querySelectorAll('.premium-option-card');

        // Abrir Modal
        trigger.addEventListener('click', () => {
            backdrop.classList.add('active');
            trigger.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Fechar Modal
        const closeModal = () => {
            backdrop.classList.remove('active');
            trigger.classList.remove('active');
            document.body.style.overflow = '';
        };

        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) closeModal();
        });

        // Filtragem
        options.forEach(option => {
            option.addEventListener('click', () => {
                const sector = option.dataset.value;
                const sectorName = option.querySelector('.premium-option-name').textContent;

                // Atualizar visual
                valueDisplay.textContent = sectorName;
                options.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');

                // Aplicar Filtro em Lote
                cards.forEach(card => {
                    const shouldShow = (sector === 'all' || card.dataset.sector === sector);
                    
                    if (shouldShow) {
                        card.style.display = 'block';
                        requestAnimationFrame(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        });
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.96)';
                        // Ocultar após o fim da transição otimizada (0.3s)
                        setTimeout(() => {
                            if (card.style.opacity === '0') {
                                card.style.display = 'none';
                            }
                        }, 300);
                    }
                });

                closeModal();
            });
        });
    });
</script>

<style>
    .camera-card {
        will-change: transform, opacity;
        transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                    transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                    display 0.3s allow-discrete;
    }

    .camera-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        justify-content: center;
    }

    .monitoring-header {
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media (max-width: 768px) {
        .monitoring-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .filter-select {
            width: 100%;
        }
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
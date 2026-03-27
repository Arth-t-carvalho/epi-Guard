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
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;"><?= __('Supervisão de segurança via câmeras inteligentes') ?></p>
        </div>
        <div class="prototype-badge">
            <i data-lucide="beaker"></i>
            <?= __('Protótipo Visual - Mockup Conceitual') ?>
        </div>
    </div>

    <div class="camera-grid">
        <!-- Camera 01 -->
        <div class="camera-card">
            <img src="<?= BASE_PATH ?>/../brain/23713476-8b2c-4219-b226-9d2c2bbde9da/industrial_camera_feed_mockup_1773507136337.png" class="camera-feed" alt="Camera 01 Feed">
            <div class="camera-scanline"></div>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag">FEED-01</div>
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        LIVE
                    </div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Setor de Soldagem') ?></h3>
                        <p>Planta A - Pavimento 01</p>
                    </div>
                    <div class="timestamp" id="time1">--:--:--</div>
                </div>
            </div>
        </div>

        <!-- Camera 02 -->
        <div class="camera-card">
            <img src="<?= BASE_PATH ?>/../brain/23713476-8b2c-4219-b226-9d2c2bbde9da/industrial_camera_feed_variety_2_1773507158765.png" class="camera-feed" alt="Camera 02 Feed">
            <div class="camera-scanline"></div>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag">FEED-02</div>
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        LIVE
                    </div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Setor de Montagem') ?></h3>
                        <p>Planta A - Pavimento 01</p>
                    </div>
                    <div class="timestamp" id="time2">--:--:--</div>
                </div>
            </div>
        </div>

        <!-- Camera 03 (Offline/Empty) -->
        <div class="camera-card camera-empty">
            <div class="camera-scanline"></div>
            <i data-lucide="video-off"></i>
            <span><?= __('CÂMERA DESCONECTADA') ?></span>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag" style="opacity: 0.5;">FEED-03</div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3><?= __('Almoxarifado') ?></h3>
                        <p>Planta B - Pavimento 02</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Camera 04 (Offline/Empty) -->
        <div class="camera-card camera-empty">
            <div class="camera-scanline"></div>
            <i data-lucide="video-off"></i>
            <span><?= __('AGUARDANDO SINAL') ?></span>
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
        if(t1) t1.textContent = fullStr;
        if(t2) t2.textContent = fullStr;
    }

    setInterval(updateTimestamps, 1000);
    updateTimestamps();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

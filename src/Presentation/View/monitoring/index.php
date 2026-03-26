<?php
$pageTitle = 'epiGuard - Monitoramento em Tempo Real';
$extraHead = '
    <style>
        .monitoring-container {
            padding: 20px;
        }

        .monitoring-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .monitoring-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #1F2937;
        }

        .prototype-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(227, 6, 19, 0.1);
            color: #E30613;
            border: 1px solid rgba(227, 6, 19, 0.2);
            border-radius: 100px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(227, 6, 19, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(227, 6, 19, 0); }
            100% { box-shadow: 0 0 0 0 rgba(227, 6, 19, 0); }
        }

        .camera-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .camera-card {
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            aspect-ratio: 16 / 9;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid #1f2937;
            transition: transform 0.3s, border-color 0.3s;
        }

        .camera-card:hover {
            transform: scale(1.02);
            border-color: #E30613;
        }

        .camera-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.85;
            filter: contrast(1.1) brightness(0.9);
        }

        .camera-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 40%, rgba(0,0,0,0) 60%, rgba(0,0,0,0.6) 100%);
            color: #fff;
            pointer-events: none;
        }

        .camera-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .camera-tag {
            background: rgba(0, 0, 0, 0.7);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.7);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            color: #10b981;
            backdrop-filter: blur(4px);
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% { opacity: 0.3; }
        }

        .camera-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .camera-info h3 {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 2px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .camera-info p {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 500;
        }

        .timestamp {
            font-family: monospace;
            font-size: 12px;
            opacity: 0.9;
        }

        .camera-scanline {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            background-size: 100% 2px, 3px 100%;
            pointer-events: none;
            opacity: 0.3;
        }

        .camera-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #475569;
            background: #111827;
        }

        .camera-empty i {
            font-size: 32px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .camera-empty span {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
';

ob_start();
?>

<div class="monitoring-container">
    <div class="monitoring-header">
        <div class="monitoring-title">
            <h1>Monitoramento em Tempo Real</h1>
            <p style="color: #64748B; font-size: 13px; margin-top: 4px;">Supervisão de segurança via câmeras inteligentes</p>
        </div>
        <div class="prototype-badge">
            <i data-lucide="beaker"></i>
            Protótipo Visual - Mockup Conceitual
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
                        <h3>Setor de Soldagem</h3>
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
                        <h3>Setor de Montagem</h3>
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
            <span>CÂMERA DESCONECTADA</span>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag" style="opacity: 0.5;">FEED-03</div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3>Almoxarifado</h3>
                        <p>Planta B - Pavimento 02</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Camera 04 (Offline/Empty) -->
        <div class="camera-card camera-empty">
            <div class="camera-scanline"></div>
            <i data-lucide="video-off"></i>
            <span>AGUARDANDO SINAL</span>
            <div class="camera-overlay">
                <div class="camera-top">
                    <div class="camera-tag" style="opacity: 0.5;">FEED-04</div>
                </div>
                <div class="camera-bottom">
                    <div class="camera-info">
                        <h3>Carga e Descarga</h3>
                        <p>Pátio Externo</p>
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

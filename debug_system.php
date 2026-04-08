<?php
require_once __DIR__ . '/src/Infrastructure/i18n.php';

// Detectar BASE_PATH
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('\\', '/', dirname($scriptName));
if ($basePath === '/') $basePath = '';

// Forçar American/Sao_Paulo (ou o que estiver no index)
date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Epi-Guard Nuclear Debug</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #e2e8f0; padding: 40px; line-height: 1.6; }
        h1 { color: #f43f5e; border-bottom: 2px solid #334155; padding-bottom: 20px; font-size: 24px; }
        .box { background: #1e293b; padding: 25px; border-radius: 16px; margin-bottom: 25px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .label { color: #38bdf8; font-weight: bold; width: 220px; display: inline-block; }
        .success { background: #064e3b; color: #34d399; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        .error { background: #7f1d1d; color: #f87171; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        code { background: #000; padding: 2px 6px; border-radius: 4px; color: #ff79c6; }
        pre { background: #000; padding: 15px; border-radius: 8px; font-size: 13px; color: #50fa7b; overflow-x: auto; }
        .version-badge { background: #3b82f6; color: white; padding: 4px 12px; border-radius: 99px; font-size: 14px; margin-left: 10px; }
    </style>
</head>
<body>
    <h1>Epi-Guard Nuclear Diagnostic <span class="version-badge"><?= defined('APP_VERSION') ? APP_VERSION : 'Legacy' ?></span></h1>

    <div class="box">
        <h3>1. PHP vs MySQL Time Sync</h3>
        <p><span class="label">PHP Current Time:</span> <?= date('Y-m-d H:i:s') ?></p>
        <?php
        try {
            $dbHost = '127.0.0.1'; $dbName = 'epi_guard'; $dbUser = 'root'; $dbPass = '';
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->exec("SET time_zone = '-03:00'");
            $row = $pdo->query("SELECT NOW() as db_now, @@session.time_zone as tz")->fetch(PDO::FETCH_ASSOC);
            echo "<p><span class='label'>MySQL NOW():</span> " . $row['db_now'] . "</p>";
            echo "<p><span class='label'>MySQL Session TZ:</span> " . $row['tz'] . "</p>";
            
            $diff = abs(strtotime(date('Y-m-d H:i:s')) - strtotime($row['db_now']));
            if ($diff < 5) {
                echo "<p><span class='label'>Time Sync:</span> <span class='success'>PERFECT ({$diff}s diff)</span></p>";
            } else {
                echo "<p><span class='label'>Time Sync:</span> <span class='error'>MISMATCHED ({$diff}s diff)</span></p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>Database Error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <div class="box">
        <h3>2. API Fetch Test (Real-time Browser Test)</h3>
        <p>Testing if browser can reach the Dashboard API at <code><?= $basePath ?>/api/dashboard/stats</code>...</p>
        <div id="api-result">
            <span class="label">API Status:</span> <span>Waiting for test...</span>
        </div>
        <script>
            async function testApi() {
                const el = document.getElementById('api-result');
                const start = Date.now();
                try {
                    const response = await fetch('<?= $basePath ?>/api/dashboard/stats?ref_date=<?= date('Y-m-d') ?>');
                    const duration = Date.now() - start;
                    if (response.ok) {
                        const data = await response.json();
                        el.innerHTML = `
                            <p><span class="label">API Status:</span> <span class="success">OK (200)</span> in ${duration}ms</p>
                            <p><span class="label">Data Received:</span></p>
                            <pre>${JSON.stringify(data.summary || data, null, 2)}</pre>
                        `;
                    } else {
                        throw new Error(`HTTP Error ${response.status}`);
                    }
                } catch (e) {
                    el.innerHTML = `<p><span class="label">API Status:</span> <span class="error">FAILED: ${e.message}</span></p>`;
                }
            }
            testApi();
        </script>
    </div>

    <div class="box">
        <h3>3. Full Path Verification</h3>
        <p><span class="label">Project Root:</span> <code><?= realpath(__DIR__) ?></code></p>
        <p><span class="label">Client Host:</span> <code><?= $_SERVER['HTTP_HOST'] ?></code></p>
        <p><span class="label">Requested via:</span> <code><?= $_SERVER['REQUEST_URI'] ?></code></p>
    </div>
</body>
</html>

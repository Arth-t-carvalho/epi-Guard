<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'EPI Guard' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/common.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/modal.css">
    
    <?php 
    // Carregar CSS específico da rota
    $path = defined('CURRENT_ROUTE') ? CURRENT_ROUTE : (str_replace(BASE_PATH, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    $path = rtrim($path, '/'); // Normalizar: remover barra final se existir
    
    if ($path === '/infractions' || strpos($path, '/management') === 0) {
        echo '<link rel="stylesheet" href="'.BASE_PATH.'/assets/css/management.css">';
    }
    if ($path === '/infractions') echo '<link rel="stylesheet" href="'.BASE_PATH.'/assets/css/infractions.css">';
    if ($path === '/monitoring') echo '<link rel="stylesheet" href="'.BASE_PATH.'/assets/css/monitoring.css">';
    if ($path === '/occurrences') echo '<link rel="stylesheet" href="'.BASE_PATH.'/assets/css/reports.css">';
    ?>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        (function() {
            var theme = localStorage.getItem("epiguard-theme");
            if (theme === "dark") {
                document.documentElement.classList.add("dark-theme");
            }
        })();
    </script>
    <script>
        window.BASE_PATH = '<?= BASE_PATH ?>';
    </script>
    <?= $extraHead ?? '' ?>
</head>
<body>
    <div class="app-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include __DIR__ . '/header.php'; ?>
            
            <div id="page-content-wrapper">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
    <?= $extraScripts ?? '' ?>
</body>
</html>

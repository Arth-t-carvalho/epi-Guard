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
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/management.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        (function() {
            var theme = localStorage.getItem("epiguard-theme");
            if (theme === "dark") {
                document.documentElement.classList.add("dark-theme");
            }
        })();
    </script>
    <?= $extraHead ?? '' ?>
</head>
<body>
    <div class="app-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include __DIR__ . '/header.php'; ?>
            
            <div id="page-content-wrapper" class="content-fade">
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

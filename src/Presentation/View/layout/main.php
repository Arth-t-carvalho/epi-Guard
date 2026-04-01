<!DOCTYPE html>
<html lang="<?= $_COOKIE['epiguard-lang'] ?? 'pt-br' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'EPI Guard' ?></title>
    <!-- Leitura imediata de tema (Evita FOUC - Flash of Unstyled Content) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('epiguard-theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark-theme');
            }
            window.BASE_PATH = '<?= BASE_PATH ?>';
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/modal/modalBase.css">
    <script src="https://unpkg.com/lucide@latest"></script>
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
    <script src="<?= BASE_PATH ?>/assets/js/notifications.js"></script>
    
    <!-- Modal de Perfil (Global) -->
    <div class="profile-dropdown" id="profileDropdown">
        <div class="profile-dropdown-header">
            <div class="profile-avatar-large">
                <?= strtoupper(substr($_SESSION['user_nome'] ?? 'AR', 0, 2)) ?>
            </div>
        </div>
        
        <div class="profile-info-content">
            <div class="profile-field">
                <label><?= __('ID do Usuário') ?></label>
                <span>#<?= $_SESSION['user_id'] ?? '1' ?></span>
            </div>
            <div class="profile-field">
                <label><?= __('Nome Completo') ?></label>
                <span><?= $_SESSION['user_nome'] ?? 'Arthur Carvalho' ?></span>
            </div>
            <div class="profile-field">
                <label><?= __('Email / Usuário') ?></label>
                <span><?= $_SESSION['user_email'] ?? 'arthur@gmail.com' ?></span>
            </div>
        </div>

        <div class="profile-dropdown-actions">
            <button class="btn-profile-cancel" id="btnProfileCancel"><?= __('Cancelar') ?></button>
            <a href="<?= BASE_PATH ?>/logout" class="btn-profile-logout">
                <i class="fa-solid fa-right-from-bracket"></i> <?= __('Sair') ?>
            </a>
        </div>
    </div>

    <?= $extraScripts ?? '' ?>
</body>

</html>

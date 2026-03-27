<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'EPI Guard' ?></title>
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
    <?= $extraScripts ?? '' ?>
    <script>
        // Animação de troca de slide ao navegar
        document.addEventListener("DOMContentLoaded", () => {
            const wrapper = document.querySelector('.main-content') || document.body;
            wrapper.style.animation = "slideFromRight 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) forwards";
            
            document.querySelectorAll('a[href]').forEach(link => {
                link.addEventListener('click', e => {
                    const href = link.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('javascript') && link.target !== '_blank') {
                        e.preventDefault();
                        wrapper.style.animation = "slideToLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards";
                        setTimeout(() => {
                            window.location.href = link.href;
                        }, 280);
                    }
                });
            });
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facchini - <?= __('Autenticação Facchini') ?></title>
    <script>
        if (sessionStorage.getItem('auth-transition') === 'true') {
            document.documentElement.classList.add('entering-transition');
            sessionStorage.removeItem('auth-transition');
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/auth.css">
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/images/logo.png">
</head>
<body>

    <div id="splash-screen" class="splash-screen hidden">
        <div class="splash-content">
            <h1 class="facchini-logo">FACCHINI</h1>
            <p class="splash-subtitle"><?= __('AUTENTICAR SISTEMA') ?></p>
            <div class="loading-container">
                <div id="progress-bar" class="progress-bar"></div>
            </div>
        </div>
    </div>

    <main class="login-container">
        <div class="login-wrapper">
            <div class="login-sidebar">
                <div class="sidebar-content">
                    <div class="brand-group">
                        <h1 class="facchini-title">FACCHINI</h1>
                        <p class="facchini-subtitle"><?= __('DIVISÃO DE SEGURANÇA') ?></p>
                    </div>

                    <div class="carousel-section">
                        <div class="carousel-container">
                            <div class="carousel-track" id="carousel-track">
                                <div class="carousel-slide active">
                                    <img src="<?= BASE_PATH ?>/assets/images/image1.png" alt="Caminhão Facchini">
                                </div>
                                <div class="carousel-slide">
                                    <img src="<?= BASE_PATH ?>/assets/images/image2.png" alt="Fábrica Facchini">
                                </div>
                                <div class="carousel-slide">
                                    <img src="<?= BASE_PATH ?>/assets/images/image3.png" alt="Logística Facchini">
                                </div>
                            </div>
                            <div class="carousel-dots">
                                <span class="dot active"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
                            </div>
                        </div>
                    </div>


                    <div class="quote-section">
                        <p class="main-quote"><?= __('O nosso maior patrimônio são as') ?> <strong><?= __('pessoas.') ?></strong></p>
                        <p class="sub-quote"><?= __('Com prevenção, o futuro avança, pois a Segurança é o melhor implemento da nossa vida.') ?></p>
                    </div>
                </div>
            </div>

            <div class="login-form-area">
                <div class="form-container">
                    <header class="form-header">
                        <h2><?= __('Bem-vindo') ?></h2>
                        <p><?= __('Acesse a sua conta Facchini introduzindo as suas credenciais abaixo.') ?></p>
                    </header>

                    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): 
                        $isError = isset($_SESSION['error']);
                        $msg = $isError ? $_SESSION['error'] : $_SESSION['success'];
                        $title = $isError ? __('Erro na Autenticação') : __('Sucesso');
                        $iconClass = $isError ? 'x-circle' : 'check-circle';
                        $iconColor = $isError ? '#ef4444' : '#10b981';
                        $iconBg = $isError ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)';
                        
                        if ($isError) unset($_SESSION['error']);
                        else unset($_SESSION['success']);
                    ?>
                        <!-- Modal Injetado do Login -->
                        <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/modal/modalBase.css">
                        <script src="<?= BASE_PATH ?>/assets/vendor/lucide.min.js"></script>
                        <div id="loginAlertModal" class="modal-premium active">
                            <div class="modal-confirmation-content">
                                <div style="width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; background-color: <?= $iconBg ?>;">
                                    <i data-lucide="<?= $iconClass ?>" style="width: 32px; height: 32px; color: <?= $iconColor ?>;"></i>
                                </div>
                                <h2><?= $title ?></h2>
                                <p><?= $msg ?></p>
                                <div class="confirmation-actions">
                                    <button class="btn-liquid" onclick="document.getElementById('loginAlertModal').classList.remove('active');">
                                        <span class="btn-text">
                                            <i data-lucide="check-circle"></i>
                                            <span><?= __('Continuar') ?></span>
                                        </span>
                                        <div class="liquid"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                if (window.lucide) { lucide.createIcons(); }
                            });
                        </script>
                    <?php endif; ?>

                    <form id="login-form" method="POST" action="<?= BASE_PATH ?>/login">
                        <div class="input-group">
                            <label for="username"><?= __('E-MAIL OU CPF') ?></label>
                            <div class="input-wrapper">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input type="text" id="username" name="usuario" placeholder="exemplo@facchini.com.br" required>
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="label-row">
                                <label for="password"><?= __('SENHA') ?></label>
                            </div>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" id="password" name="senha" placeholder="••••••••" required>
                                <button type="button" id="toggle-password" class="toggle-password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-login">
                            <?= __('ENTRAR NA PLATAFORMA') ?> <i class="fa-solid fa-chevron-right"></i>
                        </button>


                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="<?= BASE_PATH ?>/assets/js/auth.js"></script>
</body>
</html>

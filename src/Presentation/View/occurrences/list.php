<?php
$pageTitle = 'Ocorrências - EPI Guard';
ob_start();
?>

<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= __('Ocorrências') ?></h1>
            <p class="page-subtitle"><?= __('Listagem completa de ocorrências registradas no sistema.') ?></p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary">
                <i data-lucide="plus-circle"></i>
                <?= __('Nova Ocorrência') ?>
            </button>
        </div>
    </div>

    <div class="content-card">
        <p><?= __('Conteúdo das ocorrências em desenvolvimento...') ?></p>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

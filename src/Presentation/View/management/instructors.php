<?php
$pageTitle = 'epiGuard - Instrutores';
$extraHead = '
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/picker.css">
';
ob_start();
?>

<header class="page-header">
    <div class="page-title">
        <h1><?= __('Instrutores') ?></h1>
        <p><?= __('Gerencie os instrutores e supervisores') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" id="btnAddInstructor">
            <i class="fa-solid fa-user-plus"></i> <?= __('Novo Instrutor') ?>
        </button>
    </div>
</header>

<div class="page-content">
    <!-- Summary -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-icon blue">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Total Instrutores') ?></span>
                <span class="summary-value">11</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon green">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Super Admins') ?></span>
                <span class="summary-value">2</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon amber">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Supervisores') ?></span>
                <span class="summary-value">4</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon red">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Professores') ?></span>
                <span class="summary-value">5</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form action="<?= BASE_PATH ?>/management/instructors" method="GET" class="filter-bar" id="filterForm">
        <input type="text" name="search" placeholder="<?= __('Buscar instrutor...') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        
        <!-- Hidden Fields for Filters -->
        <input type="hidden" name="cargo" id="hiddenCargo" value="<?= htmlspecialchars($_GET['cargo'] ?? 'todos') ?>">
        <input type="hidden" name="setor" id="hiddenSetor" value="<?= htmlspecialchars($_GET['setor'] ?? 'todos') ?>">

        <!-- Modern Triggers -->
        <div class="modern-picker-trigger" onclick="openModernPicker('cargo')">
            <i class="fa-solid fa-user-tie"></i>
            <div class="trigger-info">
                <span class="trigger-label"><?= __('Cargo') ?></span>
                <span class="trigger-value" id="label-cargo">
                    <?php 
                    $cargoLabels = [
                        'todos' => __('Todos os Cargos'), 
                        'SUPER_ADMIN' => __('Super Admin'), 
                        'SUPERVISOR' => __('Supervisor'), 
                        'PROFESSOR' => __('Professor')
                    ];
                    echo $cargoLabels[$_GET['cargo'] ?? 'todos'] ?? 'Todos';
                    ?>
                </span>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="modern-picker-trigger" onclick="openModernPicker('setor')">
            <i class="fa-solid fa-building"></i>
            <div class="trigger-info">
                <span class="trigger-label"><?= __('Setor') ?></span>
                <span class="trigger-value" id="label-setor">
                    <?php 
                    $setorLabels = ['todos' => __('Todos os Setores'), 'TDS' => 'TDS', 'ELE' => 'ELE', 'MEC' => 'MEC', 'AUT' => 'AUT'];
                    echo $setorLabels[$_GET['setor'] ?? 'todos'] ?? 'Todos';
                    ?>
                </span>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        
        <button type="submit" style="display: none;"></button>
    </form>

    <!-- Table -->
    <div class="table-card">
        <div class="card-header">
            <h3><?= __('Lista de Instrutores') ?></h3>
            <span style="font-size: 12px; color: var(--text-muted);"><?= sprintf(__('%d registros'), 5) ?></span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th><?= __('Nome') ?></th>
                    <th><?= __('Usuário') ?></th>
                    <th><?= __('Cargo') ?></th>
                    <th><?= __('Setor') ?></th>
                    <th><?= __('Turno') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Ações') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:600;">Ricardo Mendes</td>
                    <td>ricardo.mendes</td>
                    <td><span style="background:#eff6ff; color:#3b82f6; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;"><?= __('Super Admin') ?></span></td>
                    <td>—</td>
                    <td><?= __('Integral') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Ativo') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Fernanda Costa</td>
                    <td>fernanda.costa</td>
                    <td><span style="background:#fefce8; color:#ca8a04; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;"><?= __('Supervisor') ?></span></td>
                    <td>TDS</td>
                    <td><?= __('Manhã') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Ativo') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" title="<?= __('Inativar') ?>"><i class="fa-solid fa-ban"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Carlos Pereira</td>
                    <td>carlos.pereira</td>
                    <td><span style="background:#fef2f2; color: var(--primary); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;"><?= __('Professor') ?></span></td>
                    <td>ELE</td>
                    <td><?= __('Tarde') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Ativo') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" title="<?= __('Inativar') ?>"><i class="fa-solid fa-ban"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Juliana Rocha</td>
                    <td>juliana.rocha</td>
                    <td><span style="background:#fefce8; color:#ca8a04; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;"><?= __('Supervisor') ?></span></td>
                    <td>TDS</td>
                    <td><?= __('Manhã') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Ativo') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" title="<?= __('Inativar') ?>"><i class="fa-solid fa-ban"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Marco Almeida</td>
                    <td>marco.almeida</td>
                    <td><span style="background:#fef2f2; color: var(--primary); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;"><?= __('Professor') ?></span></td>
                    <td>AUT</td>
                    <td><?= __('Noite') ?></td>
                    <td><span class="status-dot pending"></span> <?= __('Inativo') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action" title="<?= __('Reativar') ?>" style="color: #16a34a;"><i class="fa-solid fa-rotate-left"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modern Picker Modal (Apple Style) -->
<div class="modern-picker-modal" id="modernPicker">
    <div class="modern-picker-backdrop"></div>
    <div class="modern-picker-container">
        <div class="modern-picker-header">
            <h3 id="pickerTitle"><?= __('Selecionar') ?></h3>
            <p id="pickerSubtitle"><?= __('Escolha uma opção abaixo') ?></p>
        </div>
        <div class="modern-picker-options" id="pickerOptionsContainer"></div>
        <button class="modern-picker-close" onclick="closeModernPicker()"><?= __('Cancelar') ?></button>
    </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/picker.js"></script>

<script>
    // Opções para o Picker Moderno (Instrutores)
    window.PICKER_OPTIONS = {
        cargo: [
            { value: 'todos', label: '<?= __('Todos os Cargos') ?>' },
            { value: 'SUPER_ADMIN', label: '<?= __('Super Admin') ?>' },
            { value: 'SUPERVISOR', label: '<?= __('Supervisor') ?>' },
            { value: 'PROFESSOR', label: '<?= __('Professor') ?>' }
        ],
        setor: [
            { value: 'todos', label: '<?= __('Todos os Setores') ?>' },
            { value: 'TDS', label: 'TDS' },
            { value: 'ELE', label: 'ELE' },
            { value: 'MEC', label: 'MEC' },
            { value: 'AUT', label: 'AUT' }
        ]
    };
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

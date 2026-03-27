<?php
$pageTitle = 'epiGuard - Instrutores';
$extraHead = '
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/picker.css">
';
ob_start();
?>

<header class="header">
    <div class="page-title">
        <h1>Instrutores</h1>
        <p>Gerencie os instrutores e supervisores</p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" id="btnAddInstructor">
            <i class="fa-solid fa-user-plus"></i> Novo Instrutor
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
                <span class="summary-label">Total Instrutores</span>
                <span class="summary-value">11</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon green">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Super Admins</span>
                <span class="summary-value">2</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon amber">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Supervisores</span>
                <span class="summary-value">4</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon red">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label">Professores</span>
                <span class="summary-value">5</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form action="<?= BASE_PATH ?>/management/instructors" method="GET" class="filter-bar" id="filterForm">
        <input type="text" name="search" placeholder="🔍 Buscar instrutor..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        
        <!-- Hidden Fields for Filters -->
        <input type="hidden" name="cargo" id="hiddenCargo" value="<?= htmlspecialchars($_GET['cargo'] ?? 'todos') ?>">
        <input type="hidden" name="setor" id="hiddenSetor" value="<?= htmlspecialchars($_GET['setor'] ?? 'todos') ?>">

        <!-- Modern Triggers -->
        <div class="modern-picker-trigger" onclick="openModernPicker('cargo')">
            <i class="fa-solid fa-user-tie"></i>
            <div class="trigger-info">
                <span class="trigger-label">Cargo</span>
                <span class="trigger-value" id="label-cargo">
                    <?php 
                    $cargoLabels = ['todos' => 'Todos os Cargos', 'SUPER_ADMIN' => 'Super Admin', 'SUPERVISOR' => 'Supervisor', 'PROFESSOR' => 'Professor'];
                    echo $cargoLabels[$_GET['cargo'] ?? 'todos'] ?? 'Todos';
                    ?>
                </span>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="modern-picker-trigger" onclick="openModernPicker('setor')">
            <i class="fa-solid fa-building"></i>
            <div class="trigger-info">
                <span class="trigger-label">Setor</span>
                <span class="trigger-value" id="label-setor">
                    <?php 
                    $setorLabels = ['todos' => 'Todos os Setores', 'TDS' => 'TDS', 'ELE' => 'ELE', 'MEC' => 'MEC', 'AUT' => 'AUT'];
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
            <h3>Lista de Instrutores</h3>
            <span style="font-size: 12px; color: var(--text-muted);">5 registros</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Usuário</th>
                    <th>Cargo</th>
                    <th>Setor</th>
                    <th>Turno</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:600;">Ricardo Mendes</td>
                    <td>ricardo.mendes</td>
                    <td><span style="background:#eff6ff; color:#3b82f6; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">Super Admin</span></td>
                    <td>—</td>
                    <td>Integral</td>
                    <td><span class="status-dot resolved"></span> Ativo</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="Editar"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Fernanda Costa</td>
                    <td>fernanda.costa</td>
                    <td><span style="background:#fefce8; color:#ca8a04; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">Supervisor</span></td>
                    <td>TDS</td>
                    <td>Manhã</td>
                    <td><span class="status-dot resolved"></span> Ativo</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" title="Inativar"><i class="fa-solid fa-ban"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Carlos Pereira</td>
                    <td>carlos.pereira</td>
                    <td><span style="background:#fef2f2; color: var(--primary); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">Professor</span></td>
                    <td>ELE</td>
                    <td>Tarde</td>
                    <td><span class="status-dot resolved"></span> Ativo</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" title="Inativar"><i class="fa-solid fa-ban"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Juliana Rocha</td>
                    <td>juliana.rocha</td>
                    <td><span style="background:#fefce8; color:#ca8a04; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">Supervisor</span></td>
                    <td>MEC</td>
                    <td>Manhã</td>
                    <td><span class="status-dot resolved"></span> Ativo</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" title="Inativar"><i class="fa-solid fa-ban"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Marco Almeida</td>
                    <td>marco.almeida</td>
                    <td><span style="background:#fef2f2; color: var(--primary); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">Professor</span></td>
                    <td>AUT</td>
                    <td>Noite</td>
                    <td><span class="status-dot pending"></span> Inativo</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action" title="Reativar" style="color: #16a34a;"><i class="fa-solid fa-rotate-left"></i></button>
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
            <h3 id="pickerTitle">Selecionar</h3>
            <p id="pickerSubtitle">Escolha uma opção abaixo</p>
        </div>
        <div class="modern-picker-options" id="pickerOptionsContainer"></div>
        <button class="modern-picker-close" onclick="closeModernPicker()">Cancelar</button>
    </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/picker.js"></script>

<script>
    // Opções para o Picker Moderno (Instrutores)
    window.PICKER_OPTIONS = {
        cargo: [
            { value: 'todos', label: 'Todos os Cargos' },
            { value: 'SUPER_ADMIN', label: 'Super Admin' },
            { value: 'SUPERVISOR', label: 'Supervisor' },
            { value: 'PROFESSOR', label: 'Professor' }
        ],
        setor: [
            { value: 'todos', label: 'Todos os Setores' },
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

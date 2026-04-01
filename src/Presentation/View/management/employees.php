<?php
$pageTitle = 'epiGuard - ' . __('Alunos');
$extraHead = '<link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">';
ob_start();
?>

<header class="page-header">
    <div class="page-title">
        <h1><?= __('Alunos') ?></h1>
        <p><?= __('Gerencie os alunos cadastrados na instituição') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" id="btnAddEmployee">
            <i class="fa-solid fa-user-plus"></i> <?= __('Novo Aluno') ?>
        </button>
    </div>
</header>

<div class="page-content">
    <!-- Summary -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-icon blue">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Total de Alunos') ?></span>
                <span class="summary-value">165</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Conformes') ?></span>
                <span class="summary-value">142</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon red">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Não Conformes') ?></span>
                <span class="summary-value">23</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <input type="text" placeholder="<?= __('Buscar aluno por nome...') ?>">
        <select>
            <option value=""><?= __('Todos os Cursos') ?></option>
            <option value="TDS">TDS</option>
            <option value="ELE">ELE</option>
            <option value="MEC">MEC</option>
            <option value="AUT">AUT</option>
        </select>
        <select>
            <option value=""><?= __('Todos os Turnos') ?></option>
            <option value="MANHA"><?= __('Manhã') ?></option>
            <option value="TARDE"><?= __('Tarde') ?></option>
            <option value="NOITE"><?= __('Noite') ?></option>
        </select>
        <select>
            <option value=""><?= __('Todos os Status') ?></option>
            <option value="CONFORME"><?= __('Conforme') ?></option>
            <option value="NAO_CONFORME"><?= __('Não Conforme') ?></option>
        </select>
        <button class="btn-filter"><i class="fa-solid fa-filter"></i> <?= __('Filtrar') ?></button>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="card-header">
            <h3><?= __('Lista de Alunos') ?></h3>
            <span style="font-size: 12px; color: var(--text-muted);"><?= sprintf(__('%d registros'), 5) ?></span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th><?= __('Nome') ?></th>
                    <th><?= __('Curso') ?></th>
                    <th><?= __('Turno') ?></th>
                    <th><?= __('Status EPI') ?></th>
                    <th><?= __('Infrações') ?></th>
                    <th><?= __('Ações') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:600;">João Silva</td>
                    <td>TDS</td>
                    <td><?= __('Manhã') ?></td>
                    <td><span class="status-dot pending"></span> <?= __('Não Conforme') ?></td>
                    <td style="font-weight:700; color: var(--primary);">3</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Ver perfil') ?>"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Maria Souza</td>
                    <td>TDS</td>
                    <td><?= __('Tarde') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Conforme') ?></td>
                    <td>0</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Ver perfil') ?>"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Pedro Santos</td>
                    <td>ELE</td>
                    <td><?= __('Manhã') ?></td>
                    <td><span class="status-dot pending"></span> <?= __('Não Conforme') ?></td>
                    <td style="font-weight:700; color: var(--primary);">5</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Ver perfil') ?>"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Ana Oliveira</td>
                    <td>MEC</td>
                    <td><?= __('Noite') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Conforme') ?></td>
                    <td>0</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Ver perfil') ?>"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Carlos Lima</td>
                    <td>AUT</td>
                    <td><?= __('Manhã') ?></td>
                    <td><span class="status-dot resolved"></span> <?= __('Conforme') ?></td>
                    <td>1</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" title="<?= __('Ver perfil') ?>"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
</body>
</html>

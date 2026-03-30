<?php
$pageTitle = 'epiGuard - Infrações';
$extraHead = '';
ob_start();
?>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
    window.I18N = {
        'Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.': '<?= __('Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.') ?>',
        'Carregando funcionários...': '<?= __('Carregando funcionários...') ?>',
        'Nenhum funcionário encontrado neste setor.': '<?= __('Nenhum funcionário encontrado neste setor.') ?>',
        'Erro ao carregar dados. Tente novamente.': '<?= __('Erro ao carregar dados. Tente novamente.') ?>',
        'de': '<?= __('de') ?>',
        'selecionados': '<?= __('selecionados') ?>',
        'Selecione pelo menos um funcionário para exportar.': '<?= __('Selecione pelo menos um funcionário para exportar.') ?>',
        'Gerando %s...': '<?= __('Gerando %s...') ?>',
        'Concluído!': '<?= __('Concluído!') ?>',
        'Relatório %s gerado com sucesso para %d funcionário(s).': '<?= __('Relatório %s gerado com sucesso para %d funcionário(s).') ?>',
        'Deseja realmente excluir este registro de infração?': '<?= __('Deseja realmente excluir este registro de infração?') ?>'
    };
</script>

<!-- Header -->
<header class="header">
    <div class="page-title">
        <h1><?= __('Infrações') ?></h1>
        <p><?= __('Gestão de ocorrências e infrações de EPI') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" onclick="openExportModal()">
            <i class="fa-solid fa-file-export"></i> <?= __('Exportar') ?>
        </button>
    </div>
</header>

<div class="page-content">

    <!-- Filters -->
    <form action="<?= BASE_PATH ?>/infractions" method="GET" class="filter-bar">
        <input type="text" name="search" id="searchInput" placeholder="<?= __('🔍 Buscar funcionário ou setor...') ?>" value="<?= htmlspecialchars($filters['search']) ?>">
        <select name="periodo" id="filterPeriodo">
            <option value="todos" <?= $filters['periodo'] === 'todos' ? 'selected' : '' ?>><?= __('Todos os períodos') ?></option>
            <option value="hoje" <?= $filters['periodo'] === 'hoje' ? 'selected' : '' ?>><?= __('Hoje') ?></option>
            <option value="semana" <?= $filters['periodo'] === 'semana' ? 'selected' : '' ?>><?= __('Esta Semana') ?></option>
            <option value="mes" <?= $filters['periodo'] === 'mes' ? 'selected' : '' ?>><?= __('Este Mês') ?></option>
        </select>
        <select name="status" id="filterStatus">
            <option value="todos" <?= $filters['status'] === 'todos' ? 'selected' : '' ?>><?= __('Todos os Status') ?></option>
            <option value="pendente" <?= $filters['status'] === 'pendente' ? 'selected' : '' ?>><?= __('Pendente') ?></option>
            <option value="resolvido" <?= $filters['status'] === 'resolvido' ? 'selected' : '' ?>><?= __('Resolvido') ?></option>
        </select>
        <select name="epi" id="filterEpi">
            <option value="todos" <?= $filters['epi'] === 'todos' ? 'selected' : '' ?>><?= __('Todos os EPIs') ?></option>
            <?php foreach ($episList as $epiItem): ?>
                <option value="<?= htmlspecialchars($epiItem->getName()) ?>" <?= $filters['epi'] === $epiItem->getName() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($epiItem->getName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="visualizacao" id="filterVisualizacao">
            <option value="nome" <?= $filters['visualizacao'] === 'nome' ? 'selected' : '' ?>><?= __('Exibir Nome') ?></option>
            <option value="cards" <?= $filters['visualizacao'] === 'cards' ? 'selected' : '' ?>><?= __('Exibir Cards') ?></option>
        </select>
        <button type="submit" class="btn-filter">
            <i class="fa-solid fa-filter"></i> <?= __('Filtrar') ?>
        </button>
    </form>

    <?php if (!empty($filters['id'])): ?>
        <div class="active-filter-alert" style="background: #fdf2f2; border: 1px solid #fee2e2; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-info" style="color: #ef4444;"></i>
                <span style="color: #991b1b; font-weight: 500;">Exibindo apenas uma infracao especifica vinda de notificacao.</span>
            </div>
            <a href="<?= BASE_PATH ?>/infractions" style="background: white; border: 1px solid #ef4444; color: #ef4444; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s;" onmouseover="this.style.background='#ef4444'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#ef4444'">Limpar Filtro</a>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-card">
        <div class="card-header">
            <h3>Registro de Infrações</h3>
            <span style="font-size: 12px; color: var(--text-muted);" id="tableCount">Mostrando <?= count($infractions) ?> registros</span>
        </div>

        <?php if ($filters['visualizacao'] === 'cards'): ?>
            <div class="cards-grid" id="infractionsCardsGrid">
                <?php foreach ($infractions as $infraction): ?>
                    <div class="infraction-card">
                        <div class="card-image-box">
                            <?php 
                                $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                            ?>
                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>" class="card-employee-photo" onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                            <span class="status-badge-premium <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>">
                                <?= __( ucfirst($infraction['status'] ?? 'Pendente') ) ?>
                            </span>
                        </div>
                        <div class="card-content-premium">
                            <h4 class="employee-name"><?= htmlspecialchars($infraction['funcionario_nome']) ?></h4>
                             <div class="info-row-premium">
                                <i class="fa-solid fa-briefcase"></i>
                                <span><?= __('Setor:') ?> <?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span><?= __('EPI:') ?> <?= htmlspecialchars($infraction['epi_nome'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-clock"></i>
                                <span><?= date('d/m/Y - H:i', strtotime($infraction['data_hora'])) ?></span>
                            </div>
                            <div class="card-footer-premium">
                                <button class="btn-card-action" title="<?= __('Ver detalhes') ?>"><i class="fa-solid fa-eye"></i></button>
                                <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                    <button class="btn-card-action success" title="<?= __('Resolver') ?>"><i class="fa-solid fa-check"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <table class="data-table" id="infractionsTable">
                <thead>
                    <tr>
                        <th><?= __('Data') ?></th>
                        <th><?= __('Funcionário') ?></th>
                        <th><?= __('Setor') ?></th>
                        <th><?= __('EPI') ?></th>
                        <th><?= __('Horário') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Ações') ?></th>
                    </tr>
                </thead>
                <tbody id="infractionsTableBody">
                    <?php if (empty($infractions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                <?= __('Nenhuma infração encontrada com os filtros selecionados.') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($infractions as $infraction): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($infraction['data_hora'])) ?></td>
                                <td class="employee-cell">
                                    <?php if ($filters['visualizacao'] === 'foto'): ?>
                                        <div class="employee-avatar-wrapper">
                                            <?php 
                                                $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                                            ?>
                                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>" class="employee-avatar" onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                                        </div>
                                    <?php else: ?>
                                        <span style="font-weight: 600;"><?= htmlspecialchars($infraction['funcionario_nome']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></td>
                                <td data-epi="<?= strtolower($infraction['epi_nome'] ?? '') ?>"><?= htmlspecialchars($infraction['epi_nome'] ?? 'N/A') ?></td>
                                <td><?= date('H:i', strtotime($infraction['data_hora'])) ?></td>
                                <td data-status="<?= htmlspecialchars($infraction['status'] ?? 'pendente') ?>">
                                    <span class="status-dot <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>"></span> 
                                    <?= __( ucfirst($infraction['status'] ?? 'Pendente') ) ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-action" title="<?= __('Ver detalhes') ?>"><i class="fa-solid fa-eye"></i></button>
                                        <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                            <button class="btn-action" title="<?= __('Resolver') ?>"><i class="fa-solid fa-check"></i></button>
                                        <?php endif; ?>
                                        <button class="btn-action danger" title="<?= __('Excluir') ?>" onclick="deleteInfraction(<?= $infraction['id'] ?>, this)"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL DE EXPORTAÇÃO AVANÇADO -->
<div id="exportModal" class="modal-premium">
    <div class="modal-premium-content export-modal-content">
        <div class="modal-premium-header">
            <div>
                <h2><?= __('Exportar Relatórios') ?></h2>
                <p><?= __('Selecione o setor, os funcionários e o formato desejado') ?></p>
            </div>
            <button class="close-premium" onclick="closeExportModal()">&times;</button>
        </div>
        
        <div class="modal-premium-body">
            <!-- Passo 1: Seleção de Setor via Select -->
            <div class="export-step">
                <label class="step-label"><i class="fa-solid fa-building-user"></i> <?= __('Selecionar Setor') ?></label>
                <div class="export-select-wrapper">
                    <select id="exportSectorSelect" onchange="onSectorSelectChange(this)">
                        <option value="" disabled selected><?= __('Escolha um setor...') ?></option>
                        <?php 
                            $deptRepo = new \epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository();
                            $sectors = $deptRepo->findAll();
                            foreach ($sectors as $sector): 
                        ?>
                            <option value="<?= $sector->getId() ?>"><?= htmlspecialchars($sector->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Passo 2: Seleção de Funcionários com Pesquisa -->
            <div class="export-step" id="employeeStep">
                <label class="step-label"><i class="fa-solid fa-users"></i> <?= __('Selecionar Funcionários') ?></label>
                <div class="employee-selection-wrapper">
                    <!-- Barra de Pesquisa -->
                    <div class="employee-search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="employeeSearchInput" placeholder="<?= __('Pesquisar funcionário...') ?>" oninput="filterExportEmployees(this.value)">
                    </div>
                    <!-- Selecionar Todos -->
                    <div class="selection-controls">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="selectAllEmployees" onchange="toggleAllExportEmployees(this.checked)">
                            <span class="checkmark"></span>
                            <span class="label-text"><?= __('Selecionar Todos') ?></span>
                        </label>
                        <span class="selected-count" id="selectedCount">0 <?= __('selecionados') ?></span>
                    </div>
                    <!-- Lista de Funcionários -->
                    <div class="employee-check-list" id="exportEmployeeList">
                        <div class="employee-empty info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span><?= __('Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passo 3: Botões de Exportação (Empilhados) -->
            <div class="export-step" id="formatStep" style="display: none;">
                <label class="step-label"><i class="fa-solid fa-file-circle-check"></i> <?= __('Escolher Formato') ?></label>
                <div class="export-actions-stack">
                    <button class="btn-liquid pdf" onclick="processExport('pdf')">
                        <span class="btn-text"><i class="fa-solid fa-file-pdf"></i> <?= __('Exportar para PDF') ?></span>
                        <div class="liquid"></div>
                    </button>
                    <button class="btn-liquid excel" onclick="processExport('excel')">
                        <span class="btn-text"><i class="fa-solid fa-file-excel"></i> <?= __('Exportar para Excel') ?></span>
                        <div class="liquid"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/infractions.js"></script>
<script>
    function deleteInfraction(id, btn) {
        if (confirm(window.I18N['Deseja realmente excluir este registro de infração?'])) {
            const row = btn.closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            
            setTimeout(() => {
                row.remove();
            }, 300);
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

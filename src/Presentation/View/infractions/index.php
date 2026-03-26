<?php
$pageTitle = 'epiGuard - Infrações';
ob_start();
?>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
</script>

<!-- Header -->
<header class="header">
    <div class="page-title">
        <h1>Infrações</h1>
        <p>Gestão de ocorrências e infrações de EPI</p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" onclick="openExportModal()">
            <i class="fa-solid fa-file-export"></i> Exportar
        </button>
    </div>
</header>

<div class="page-content">


    <!-- Filters -->
    <form action="<?= BASE_PATH ?>/infractions" method="GET" class="filter-bar">
        <div class="filter-group select-search">
            <input type="text" name="search" id="searchInput" placeholder="🔍 Buscar funcionário ou setor..." value="<?= htmlspecialchars($filters['search']) ?>">
        </div>
        <div class="filter-group">
            <select name="periodo" id="filterPeriodo" onchange="this.form.submit()">
                <option value="todos" <?= $filters['periodo'] === 'todos' ? 'selected' : '' ?>>Todos os períodos</option>
                <option value="hoje" <?= $filters['periodo'] === 'hoje' ? 'selected' : '' ?>>Hoje</option>
                <option value="semana" <?= $filters['periodo'] === 'semana' ? 'selected' : '' ?>>Esta Semana</option>
                <option value="mes" <?= $filters['periodo'] === 'mes' ? 'selected' : '' ?>>Este Mês</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="status" id="filterStatus" onchange="this.form.submit()">
                <option value="todos" <?= $filters['status'] === 'todos' ? 'selected' : '' ?>>Todos os Status</option>
                <option value="pendente" <?= $filters['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="resolvido" <?= $filters['status'] === 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="epi" id="filterEpi" onchange="this.form.submit()">
                <option value="todos" <?= $filters['epi'] === 'todos' ? 'selected' : '' ?>>Todos os EPIs</option>
                <?php foreach ($episList as $epiItem): ?>
                    <option value="<?= htmlspecialchars($epiItem->getName()) ?>" <?= $filters['epi'] === $epiItem->getName() ? 'selected' : '' ?>>
                        <?= htmlspecialchars($epiItem->getName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <select name="visualizacao" id="filterVisualizacao" onchange="this.form.submit()">
                <option value="nome" <?= $filters['visualizacao'] === 'nome' ? 'selected' : '' ?>>Exibir Nome</option>
                <option value="cards" <?= $filters['visualizacao'] === 'cards' ? 'selected' : '' ?>>Exibir Cards</option>
            </select>
        </div>
        <button type="submit" style="display: none;"></button> <!-- Hidden submit for Enter key -->
    </form>

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
                                <?= ucfirst($infraction['status'] ?? 'Pendente') ?>
                            </span>
                        </div>
                        <div class="card-content-premium">
                            <h4 class="employee-name"><?= htmlspecialchars($infraction['funcionario_nome']) ?></h4>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-briefcase"></i>
                                <span>Setor: <?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>EPI: <?= htmlspecialchars($infraction['epi_nome'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-clock"></i>
                                <span><?= date('d/m/Y - H:i', strtotime($infraction['data_hora'])) ?></span>
                            </div>
                            <div class="card-footer-premium">
                                <button class="btn-card-action" title="Ver detalhes"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn-card-action secondary" title="Salvar para revisão"><i class="fa-solid fa-bookmark"></i></button>
                                <button class="btn-card-action info" title="Assinar Ocorrência"><i class="fa-solid fa-signature"></i></button>
                                <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                    <button class="btn-card-action success" title="Resolver"><i class="fa-solid fa-check"></i></button>
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
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Setor</th>
                        <th>EPI</th>
                        <th>Horário</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="infractionsTableBody">
                    <?php if (empty($infractions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Nenhuma infração encontrada com os filtros selecionados.
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
                                    <?= ucfirst($infraction['status'] ?? 'Pendente') ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-action" title="Ver detalhes"><i class="fa-solid fa-eye"></i></button>
                                        <button class="btn-action secondary" title="Salvar para revisão"><i class="fa-solid fa-bookmark"></i></button>
                                        <button class="btn-action info" title="Assinar Ocorrência"><i class="fa-solid fa-signature"></i></button>
                                        <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                            <button class="btn-action success" title="Resolver"><i class="fa-solid fa-check"></i></button>
                                        <?php endif; ?>
                                        <button class="btn-action danger" title="Excluir" onclick="deleteInfraction(<?= $infraction['id'] ?>, this)"><i class="fa-solid fa-trash"></i></button>
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
                <h2>Exportar Relatórios</h2>
                <p>Selecione o setor, os funcionários e o formato desejado</p>
            </div>
            <button class="close-premium" onclick="closeExportModal()">&times;</button>
        </div>
        
        <div class="modal-premium-body">
            <!-- Passo 1: Seleção de Setor via Select -->
            <div class="export-step">
                <label class="step-label"><i class="fa-solid fa-building-user"></i> Selecionar Setor</label>
                <div class="export-select-wrapper">
                    <select id="exportSectorSelect" onchange="onSectorSelectChange(this)">
                        <option value="" disabled selected>Escolha um setor...</option>
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
                <label class="step-label"><i class="fa-solid fa-users"></i> Selecionar Funcionários</label>
                <div class="employee-selection-wrapper">
                    <!-- Barra de Pesquisa -->
                    <div class="employee-search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="employeeSearchInput" placeholder="Pesquisar funcionário..." oninput="filterExportEmployees(this.value)">
                    </div>
                    <!-- Selecionar Todos -->
                    <div class="selection-controls">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="selectAllEmployees" onchange="toggleAllExportEmployees(this.checked)">
                            <span class="checkmark"></span>
                            <span class="label-text">Selecionar Todos</span>
                        </label>
                        <span class="selected-count" id="selectedCount">0 selecionados</span>
                    </div>
                    <!-- Lista de Funcionários -->
                    <div class="employee-check-list" id="exportEmployeeList">
                        <div class="employee-empty info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passo 3: Botões de Exportação (Empilhados) -->
            <div class="export-step" id="formatStep" style="display: none;">
                <label class="step-label"><i class="fa-solid fa-file-circle-check"></i> Escolher Formato</label>
                <div class="export-actions-stack">
                    <button class="btn-liquid pdf" onclick="processExport('pdf')">
                        <span class="btn-text"><i class="fa-solid fa-file-pdf"></i> Exportar para PDF</span>
                        <div class="liquid"></div>
                    </button>
                    <button class="btn-liquid excel" onclick="processExport('excel')">
                        <span class="btn-text"><i class="fa-solid fa-file-excel"></i> Exportar para Excel</span>
                        <div class="liquid"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/infractions.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

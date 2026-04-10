<?php
$pageTitle = 'Facchini - Infrações';
$extraHead = '
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/infractions.css?v=' . @filemtime(BASE_DIR . '/assets/css/infractions.css') . '">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/picker.css?v=' . @filemtime(BASE_DIR . '/assets/css/picker.css') . '">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css?v=' . @filemtime(BASE_DIR . '/assets/css/management.css') . '">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/modal/modalInfractions.css?v=' . @filemtime(BASE_DIR . '/assets/css/modal/modalInfractions.css') . '">
    <!-- Dependencies for Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .highlight-notification {
            border: 2px solid var(--primary) !important;
            box-shadow: 0 0 15px rgba(227, 6, 19, 0.4) !important;
            position: relative;
            z-index: 10;
            animation: pulse-highlight 2s infinite;
        }
        @keyframes pulse-highlight {
            0% { box-shadow: 0 0 0px rgba(227, 6, 19, 0.4); }
            50% { box-shadow: 0 0 20px rgba(227, 6, 19, 0.6); }
            100% { box-shadow: 0 0 0px rgba(227, 6, 19, 0.4); }
        }
        tr.highlight-notification {
            background-color: rgba(227, 6, 19, 0.05) !important;
        }
    </style>
';
ob_start();

function formatInfractionDuration($start, $end = null) {
    try {
        $startDate = new DateTime($start);
        $endDate = $end ? new DateTime($end) : new DateTime();
        $interval = $startDate->diff($endDate);
        
        $parts = [];
        if ($interval->d > 0) $parts[] = $interval->d . 'd';
        if ($interval->h > 0) $parts[] = $interval->h . 'h';
        if ($interval->i > 0) $parts[] = $interval->i . 'm';
        
        return empty($parts) ? '1m' : implode(' ', $parts);
    } catch (Exception $e) {
        return '---';
    }
}
?>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
</script>

<!-- Header -->
<header class="page-header">
    <div class="page-title">
        <h1><?= __('Infrações') ?></h1>
        <p><?= __('Gestão de ocorrências e infrações de EPI') ?></p>
    </div>
    <div class="header-actions">
        <!-- Botão Novo Exportar (Vermelho / Destaque) -->
        <button class="btn-primary" onclick="openExportModal()"
            style="background: var(--primary); border-radius: 0px; font-weight: 800; padding: 12px 24px;">
            <i class="fa-solid fa-plus"></i> <?= __('Exportar') ?>
        </button>
    </div>
</header>

<div class="page-content">


    <!-- Filters (LINHA ÚNICA - FIDELIDADE TOTAL À IMAGEM) -->
    <form action="<?= BASE_PATH ?>/infractions" method="GET" class="filter-bar" id="filterForm">
        
        <div class="filter-group select-search">
            <div class="search-input-wrapper">
                <input type="text" name="search" id="searchInput"
                    placeholder="<?= __('Pesquisar funcionário ou setor...') ?>"
                    value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
        </div>

        <!-- Hidden Fields for Filters -->
        <input type="hidden" name="periodo" id="hiddenPeriodo" value="<?= htmlspecialchars($filters['periodo']) ?>">
        <input type="hidden" name="status" id="hiddenStatus" value="<?= htmlspecialchars($filters['status']) ?>">
        <input type="hidden" name="epi" id="hiddenEpi" value="<?= htmlspecialchars($filters['epi']) ?>">
        <input type="hidden" name="visualizacao" id="hiddenVisualizacao"
            value="<?= htmlspecialchars($filters['visualizacao']) ?>">
        <!-- Novos campos -->
        <input type="hidden" name="date_from" id="hiddenDate_from"
            value="<?= htmlspecialchars($filters['date_from']) ?>">
        <input type="hidden" name="date_to" id="hiddenDate_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
        <input type="hidden" name="setor_id" id="hiddenSetor" value="<?= htmlspecialchars($filters['setor_id'] ?? '') ?>">
        <input type="hidden" name="funcionario_id" id="hiddenFuncionario" value="<?= htmlspecialchars($filters['funcionario_id'] ?? '') ?>">
        <input type="hidden" id="highlightedOccurrenceId" value="<?= htmlspecialchars($highlightId ?? '') ?>">

        <!-- 1. PERÍODO -->
        <div class="filter-group">
            <div class="modern-picker-trigger" id="periodoTrigger" onclick="openModernPicker('periodo')">
                <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('PERÍODO') ?></span>
                    <span class="trigger-value" id="label-periodo">
                        <?php
                        $periodLabels = [
                            'todos' => __('Todos os períodos'),
                            'hoje' => __('Hoje'),
                            'semana' => __('Esta Semana'),
                            'mes' => __('Este Mês'),
                            'personalizado' => __('Personalizado')
                        ];
                        echo $periodLabels[$filters['periodo']] ?? __('Todos os períodos');
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>

            <!-- Barra de Data (Pill Compacto) -->
            <div class="date-input-wrapper <?= $filters['periodo'] === 'personalizado' ? 'active' : '' ?>" id="customDateBar">
                <input type="date" id="dateFromInput" value="<?= $filters['date_from'] ?>" onchange="checkAndApplyDate()">
                <span><?= __('PARA') ?></span>
                <input type="date" id="dateToInput" value="<?= $filters['date_to'] ?>" onchange="checkAndApplyDate()">
                <button type="button" class="btn-apply-date" onclick="applyCustomDate()">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- 2. STATUS -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('status')">
                <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('STATUS') ?></span>
                    <span class="trigger-value" id="label-status">
                        <?php
                        $statusLabels = [
                            'todos' => __('Todos os status'),
                            'pendente' => __('Pendente'),
                            'resolvido' => __('Resolvido'),
                            'inativo' => __('Inativo')
                        ];
                        echo $statusLabels[$filters['status']] ?? __('Todos os status');
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <!-- 3. EPI -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('epi')">
                <i class="fa-solid fa-mask-face" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('EPI') ?></span>
                    <span class="trigger-value" id="label-epi">
                        <?= $filters['epi'] === 'todos' ? __('Todos os EPIs') : htmlspecialchars($filters['epi']) ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <!-- 4. SETOR -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('setor')">
                <i class="fa-solid fa-building-user" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('SETOR') ?></span>
                    <span class="trigger-value" id="label-setor">
                        <?php
                        $selectedSectorName = __('Todos os setores');
                        foreach ($sectorsList as $s) {
                            if ($s->getId() == $filters['setor_id']) {
                                $selectedSectorName = htmlspecialchars(__db($s));
                                break;
                            }
                        }
                        echo $selectedSectorName;
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <?php if (!empty($filters['setor_id']) && $filters['setor_id'] !== 'todos' && !empty($employeesList)): ?>
        <!-- 5. FUNCIONÁRIO -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('funcionario')">
                <i class="fa-solid fa-user-tag" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('FUNCIONÁRIO') ?></span>
                    <span class="trigger-value" id="label-funcionario">
                        <?php
                        $selectedEmployeeName = __('Todos os funcionários');
                        foreach ($employeesList as $e) {
                            if ($e->getId() == $filters['funcionario_id']) {
                                $selectedEmployeeName = htmlspecialchars($e->getName());
                                break;
                            }
                        }
                        echo $selectedEmployeeName;
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>
        <?php endif; ?>


        <!-- 5. ORDENAR POR -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('order')">
                <i class="fa-solid fa-arrow-down-wide-short" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('ORDENAR POR') ?></span>
                    <span class="trigger-value" id="label-order">
                        <?php
                        $orderLabels = [
                            'recentes' => __('Mais recente'),
                            'alfabetica' => __('Ordem Alfabética'),
                            'frequentes' => __('Mais Frequentes')
                        ];
                        echo $orderLabels[$filters['order']] ?? __('Mais recente');
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <!-- 5. VISUALIZAÇÃO -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('visualizacao')">
                <i class="fa-solid fa-table-cells-large" style="color: var(--primary);"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('VISUALIZAÇÃO') ?></span>
                    <span class="trigger-value" id="label-visualizacao">
                        <?= $filters['visualizacao'] === 'nome' ? __('Exibir Nome') : __('Exibir Cards') ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>
    </form>


    <!-- Table -->
    <div class="table-card">
        <div class="card-header">
            <h3><?= __('Registro de Infrações') ?></h3>
            <span style="font-size: 12px; color: var(--text-muted);" id="tableCount"><?= __('Mostrando') ?>
                <?= count($infractions) ?> <?= __('registros') ?></span>
        </div>

        <?php if ($filters['visualizacao'] === 'cards'): ?>
            <div class="cards-grid" id="infractionsCardsGrid">
                <?php foreach ($infractions as $infraction): ?>
                    <div class="infraction-card<?= !empty($infraction['favorito']) ? ' is-bookmarked' : '' ?><?= ($highlightId == $infraction['id']) ? ' highlight-notification' : '' ?>"
                        id="card-infraction-<?= $infraction['id'] ?>">
                        <div class="card-image-box">
                            <?php
                            $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                            ?>
                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>"
                                class="card-employee-photo" onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                                <span
                                    class="status-badge-premium <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>">
                                    <?= __($infraction['status'] === 'pendente' ? 'Pendente' : 'Resolvido') ?>
                                </span>
                        </div>
                        <div class="card-content-premium">
                            <h4 class="employee-name"><?= htmlspecialchars($infraction['funcionario_nome']) ?></h4>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-briefcase"></i>
                                <span><?= __('Setor') ?>: <?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="epi-icon-badge"></i>
                                <span><?= __('EPI') ?>: <?= htmlspecialchars(__db($infraction, 'epi_nome')) ?></span>
                            </div>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-clock"></i>
                                <span><?= date('d/m/Y - H:i', strtotime($infraction['data_hora'])) ?></span>
                            </div>
                            <div class="info-row-premium" style="color: var(--primary); font-weight: 800;">
                                <?php $durationText = formatInfractionDuration($infraction['data_hora'], $infraction['resolvido_em'] ?? null); ?>
                                <i class="fa-solid fa-stopwatch"></i>
                                <span><?= __('Tempo s/ EPI') ?>: <?= $durationText ?></span>
                            </div>
                            <div class="card-footer-premium">
                                <?php
                                $args = json_encode([
                                    mb_check_encoding($infraction['funcionario_nome'], 'UTF-8') ? $infraction['funcionario_nome'] : utf8_encode($infraction['funcionario_nome']),
                                    mb_check_encoding($infraction['epi_nome'] ?? 'N/A', 'UTF-8') ? ($infraction['epi_nome'] ?? 'N/A') : utf8_encode($infraction['epi_nome'] ?? 'N/A'),
                                    date('d/m/Y H:i', strtotime($infraction['data_hora'])),
                                    mb_check_encoding($infraction['setor_sigla'] ?? 'N/A', 'UTF-8') ? ($infraction['setor_sigla'] ?? 'N/A') : utf8_encode($infraction['setor_sigla'] ?? 'N/A'),
                                    !empty($infraction['evidencia_foto']) ? BASE_PATH . '/' . ltrim(str_replace('\\', '/', $infraction['evidencia_foto']), '/') : '',
                                    $infraction['id'],
                                    $infraction['funcionario_id'],
                                    $infraction['setor_id'] ?: null,
                                    $infraction['epi_id'] ?: null,
                                    $durationText,
                                    $infraction['acao_tipo'] ?? '',
                                    mb_check_encoding($infraction['acao_obs'] ?? '', 'UTF-8') ? ($infraction['acao_obs'] ?? '') : utf8_encode($infraction['acao_obs'] ?? ''),
                                    mb_check_encoding($infraction['responsavel_nome'] ?? '', 'UTF-8') ? ($infraction['responsavel_nome'] ?? '') : utf8_encode($infraction['responsavel_nome'] ?? ''),
                                    mb_check_encoding($infraction['responsavel_cargo'] ?? '', 'UTF-8') ? ($infraction['responsavel_cargo'] ?? '') : utf8_encode($infraction['responsavel_cargo'] ?? '')
                                ]);
                                ?>
                                <?php if (($filters['status'] ?? '') !== 'inativo'): ?>
                                    <button class="btn-card-action-premium" title="<?= __('Ver detalhes') ?>"
                                        onclick="openEvidenceModal.apply(null, <?= htmlspecialchars($args, ENT_QUOTES, 'UTF-8') ?>)"><i
                                            class="fa-solid fa-eye"></i></button>
                                    <button
                                        class="btn-card-action-premium secondary<?= !empty($infraction['favorito']) ? ' active' : '' ?>"
                                        title="<?= __('Salvar para revisão') ?>"
                                        onclick="toggleBookmark(this, <?= $infraction['id'] ?>)"><i
                                            class="fa-solid fa-bookmark"></i></button>
                                    <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                        <button class="btn-card-action-premium success" title="<?= __('Resolver') ?>"><i
                                                class="fa-solid fa-check"></i></button>
                                    <?php endif; ?>
                                    <button class="btn-card-action-premium danger" title="<?= __('Excluir') ?>"
                                        onclick="confirmHideInfraction(<?= $infraction['id'] ?>, '<?= $infraction['funcionario_nome'] ?>')"><i
                                            class="fa-solid fa-trash"></i></button>
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
                        <th><?= __('DATA') ?></th>
                        <th><?= __('FUNCIONÁRIO') ?></th>
                        <th><?= __('DEPARTAMENTO') ?></th>
                        <th><?= __('EPI') ?></th>
                        <th><?= __('HORÁRIO') ?></th>
                        <th><?= __('STATUS') ?></th>
                        <th style="text-align: left; padding-left: 20px;"><?php if (($filters['status'] ?? '') !== 'inativo') echo __('AÇÕES'); ?></th>
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
                            <tr id="row-infraction-<?= $infraction['id'] ?>"
                                class="<?= !empty($infraction['favorito']) ? 'is-bookmarked' : '' ?><?= ($highlightId == $infraction['id']) ? ' highlight-notification' : '' ?>">
                                <td><?= date('d/m/Y', strtotime($infraction['data_hora'])) ?></td>
                                <td class="employee-cell">
                                    <?php if ($filters['visualizacao'] === 'foto'): ?>
                                        <div class="employee-avatar-wrapper">
                                            <?php
                                            $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                                            ?>
                                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>"
                                                class="employee-avatar"
                                                onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                                        </div>
                                    <?php else: ?>
                                        <span style="font-weight: 600;"><?= htmlspecialchars($infraction['funcionario_nome']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></td>
                                <td data-epi="<?= strtolower($infraction['epi_nome'] ?? '') ?>">
                                    <?= htmlspecialchars(__db($infraction, 'epi_nome')) ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;"><?= date('H:i', strtotime($infraction['data_hora'])) ?></span>
                                        <?php $durationTextTable = formatInfractionDuration($infraction['data_hora'], $infraction['resolvido_em'] ?? null); ?>
                                        <span style="font-size: 11px; color: var(--primary); font-weight: 800;"><i class="fa-solid fa-stopwatch"></i> <?= $durationTextTable ?></span>
                                    </div>
                                </td>
                                <td data-status="<?= htmlspecialchars($infraction['status'] ?? 'pendente') ?>">
                                    <div class="status-container-premium">
                                        <span
                                            class="status-dot-premium <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>"></span>
                                        <span class="status-text-premium"><?= __($infraction['status'] === 'pendente' ? 'Pendente' : 'Resolvido') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions-premium">
                                        <?php if (($filters['status'] ?? '') !== 'inativo'): ?>
                                            <?php
                                            $argsTable = json_encode([
                                                mb_check_encoding($infraction['funcionario_nome'], 'UTF-8') ? $infraction['funcionario_nome'] : utf8_encode($infraction['funcionario_nome']),
                                                mb_check_encoding($infraction['epi_nome'] ?? 'N/A', 'UTF-8') ? ($infraction['epi_nome'] ?? 'N/A') : utf8_encode($infraction['epi_nome'] ?? 'N/A'),
                                                date('d/m/Y H:i', strtotime($infraction['data_hora'])),
                                                mb_check_encoding($infraction['setor_sigla'] ?? 'N/A', 'UTF-8') ? ($infraction['setor_sigla'] ?? 'N/A') : utf8_encode($infraction['setor_sigla'] ?? 'N/A'),
                                                !empty($infraction['evidencia_foto']) ? BASE_PATH . '/' . ltrim(str_replace('\\', '/', $infraction['evidencia_foto']), '/') : '',
                                                $infraction['id'],
                                                $infraction['funcionario_id'],
                                                $infraction['setor_id'] ?: null,
                                                $infraction['epi_id'] ?: null,
                                                $durationTextTable,
                                                $infraction['acao_tipo'] ?? '',
                                                mb_check_encoding($infraction['acao_obs'] ?? '', 'UTF-8') ? ($infraction['acao_obs'] ?? '') : utf8_encode($infraction['acao_obs'] ?? ''),
                                                mb_check_encoding($infraction['responsavel_nome'] ?? '', 'UTF-8') ? ($infraction['responsavel_nome'] ?? '') : utf8_encode($infraction['responsavel_nome'] ?? ''),
                                                mb_check_encoding($infraction['responsavel_cargo'] ?? '', 'UTF-8') ? ($infraction['responsavel_cargo'] ?? '') : utf8_encode($infraction['responsavel_cargo'] ?? '')
                                            ]);
                                            ?>
                                            <button class="btn-action-premium" title="<?= __('Ver detalhes') ?>"
                                                onclick="openEvidenceModal.apply(null, <?= htmlspecialchars($argsTable, ENT_QUOTES, 'UTF-8') ?>)"><i
                                                    class="fa-solid fa-eye"></i></button>
                                            <button
                                                class="btn-action-premium secondary<?= !empty($infraction['favorito']) ? ' active' : '' ?>"
                                                title="<?= __('Salvar para revisão') ?>"
                                                onclick="toggleBookmark(this, <?= $infraction['id'] ?>)"><i
                                                    class="fa-solid fa-bookmark"></i></button>
                                            <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                                <button class="btn-action-premium success" title="<?= __('Resolver') ?>"><i
                                                        class="fa-solid fa-check"></i></button>
                                            <?php endif; ?>
                                            <button class="btn-action-premium danger" title="<?= __('Excluir') ?>"
                                                onclick="confirmHideInfraction(<?= $infraction['id'] ?>, '<?= $infraction['funcionario_nome'] ?>')"><i
                                                    class="fa-solid fa-trash"></i></button>
                                        <?php endif; ?>
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
                <label class="step-label"><i class="fa-solid fa-building-user"></i>
                    <?= __('Selecionar Setor') ?></label>
                <div class="export-select-wrapper">
                    <select id="exportSectorSelect" onchange="onSectorSelectChange(this)">
                        <option value="" disabled selected><?= __('Escolha um setor...') ?></option>
                        <?php
                        $deptRepo = new \Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository();
                        $sectors = $deptRepo->findAll();
                        foreach ($sectors as $sector):
                            ?>
                             <option value="<?= $sector->getId() ?>"><?= htmlspecialchars(__db($sector)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Passo 2: Seleção de Funcionários com Pesquisa -->
            <div class="export-step" id="employeeStep" style="display: none;">
                <label class="step-label"><i class="fa-solid fa-users"></i> <?= __('Selecionar Funcionários') ?></label>
                <div class="employee-selection-wrapper">
                    <!-- Barra de Pesquisa -->
                    <div class="employee-search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="employeeSearchInput" placeholder="<?= __('Pesquisar funcionário...') ?>"
                            oninput="filterExportEmployees(this.value)">
                    </div>
                    <!-- Selecionar Todos -->
                    <div class="selection-controls">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="selectAllEmployees"
                                onchange="toggleAllExportEmployees(this.checked)">
                            <span class="checkmark"></span>
                            <span class="label-text"> <?= __('Selecionar Todos') ?></span>
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
                <label class="step-label"><i class="fa-solid fa-file-circle-check"></i>
                    <?= __('Escolher Formato') ?></label>
                <div class="export-actions-stack">
                    <button class="btn-liquid pdf" onclick="processExport(event, 'pdf')">
                        <span class="btn-text"><i class="fa-solid fa-file-pdf"></i>
                            <?= __('Exportar para PDF') ?></span>
                    </button>
                    <button class="btn-liquid print-report" onclick="processExport(event, 'print')">
                        <span class="btn-text"><i class="fa-solid fa-print"></i>
                            <?= __('Imprimir Relatório') ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMAÇÃO PARA OCULTAR INFRAÇÃO -->
<div class="modal-premium" id="confirmHideModal">
    <div class="modal-confirmation-content">
        <i class="fa-solid fa-triangle-exclamation main-icon"
            style="color: #f59e0b; font-size: 48px; margin-bottom: 20px; display: block;"></i>
        <h2><?= __('Ocultar Registro') ?></h2>
        <p><?= __('O registro de') ?> <strong><span id="hideTargetName"></span></strong>
            <?= __('deixará de aparecer na listagem, mas continuará salvo no histórico do banco de dados.') ?></p>

        <div class="confirmation-actions" style="margin-top: 30px; display: flex; flex-direction: column; gap: 12px;">
            <button class="btn-liquid" id="btnDoHide" style="width: 100%;">
                <div class="btn-text">
                    <i class="fa-solid fa-check"></i>
                    <span><?= __('Ocultar Registro') ?></span>
                </div>
            </button>
            <button class="btn-light-shadow" onclick="closeConfirmHideModal()" style="width: 100%;">
                <?= __('Cancelar') ?>
            </button>
        </div>
    </div>
</div>

<script>
    // Fechar modais ao clicar fora
    window.addEventListener('click', (e) => {
        const confirmModal = document.getElementById('confirmHideModal');
        const evidenceModal = document.getElementById('evidenceModal');
        const exportModal = document.getElementById('exportModal');

        if (e.target === confirmModal) closeConfirmHideModal();
        if (e.target === evidenceModal) closeEvidenceModal();
        if (e.target === exportModal) closeExportModal();
    });
</script>

<!-- MODAL DE VISUALIZAÇÃO DE EVIDÊNCIA (PREMIUM) -->
<div id="evidenceModal" class="evidence-modal-overlay">
    <div class="evidence-modal-card">
        <div class="evidence-modal-image-box">
            <button class="evidence-modal-close" onclick="closeEvidenceModal()">&times;</button>
            <img id="evidenceModalImg" src="" alt="<?= __('Evidência') ?>">
            <div id="evidenceNoPhoto" class="evidence-no-photo" style="display:none;">
                <i class="fa-solid fa-camera-slash" style="font-size:48px; margin-bottom:16px; opacity:0.3;"></i>
                <p><?= __('Nenhuma evidência fotográfica registrada para esta ocorrência.') ?></p>
            </div>
        </div>
        <div class="evidence-modal-info">
            <h3 id="evidenceModalName" style="font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 20px;">-
            </h3>
            <div class="evidence-info-grid">
                <div class="evidence-info-item">
                    <div class="item-icon"><i class="fa-solid fa-helmet-safety"></i></div>
                    <span id="evidenceModalEpi">EPI: -</span>
                </div>
                <div class="evidence-info-item">
                    <div class="item-icon"><i class="fa-solid fa-building"></i></div>
                    <span id="evidenceModalSetor"><?= __('Setor') ?>: -</span>
                </div>
                <div class="evidence-info-item">
                    <div class="item-icon"><i class="fa-solid fa-clock"></i></div>
                    <span id="evidenceModalData">-</span>
                </div>
                <div class="evidence-info-item" style="color: var(--primary); font-weight: 800;">
                    <div class="item-icon" style="background: rgba(227, 6, 19, 0.1);"><i class="fa-solid fa-stopwatch"></i></div>
                    <span id="evidenceModalDuration"><?= __('Tempo s/ EPI') ?>: -</span>
                </div>
            </div>

            <div id="evidenceResolutionBox" class="resolution-summary-premium" style="display: none;">
                <div class="resolution-title">
                    <i class="fa-solid fa-file-signature"></i> <?= __('Resumo da Resolução') ?>
                </div>
                <div class="resolution-grid">
                    <div class="res-item">
                        <span class="res-label"><?= __('Ação Aplicada') ?></span>
                        <span id="resActionType" class="res-value">---</span>
                    </div>
                    <div class="res-item">
                        <span class="res-label"><?= __('Responsável') ?></span>
                        <span id="resSupervisorName" class="res-value">---</span>
                    </div>
                </div>
                <div class="res-notes">
                    <span class="res-label"><?= __('Observações') ?></span>
                    <p id="resObservations" class="res-text">---</p>
                </div>
            </div>
            <div class="evidence-modal-actions">
                <a id="evidenceDownloadBtn" href="" download class="evidence-btn download" title="<?= __('Baixar Foto') ?>">
                    <i class="fa-solid fa-image"></i> <?= __('Baixar Foto') ?>
                </a>
                <button class="evidence-btn pdf" onclick="downloadInfractionPdf()" title="<?= __('Baixar PDF') ?>">
                    <i class="fa-solid fa-file-pdf"></i> <?= __('Baixar PDF') ?>
                </button>
                <button class="evidence-btn print" onclick="printInfractionDetail()" title="<?= __('Imprimir') ?>">
                    <i class="fa-solid fa-print"></i> <?= __('Imprimir') ?>
                </button>
                <button id="evidenceSignBtn" class="evidence-btn sign" onclick="signOccurrence()" title="<?= __('Assinar') ?>">
                    <i class="fa-solid fa-signature"></i> <?= __('Assinar') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .evidence-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: none;
        align-items: center !important;
        /* Centralização vertical absoluta */
        justify-content: center !important;
        padding: 20px !important;
        overflow-y: auto !important;
        z-index: 999999999 !important;
        animation: fadeInOverlay 0.3s ease;
    }

    .evidence-modal-overlay.active {
        display: flex !important;
    }

    /* Força todos os modais premium (Exportação, Confirmação, Evidência) a se comportarem como globais */
    .modal-premium,
    #exportModal,
    #confirmHideModal,
    #evidenceModal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        overflow-y: auto !important;
        z-index: 10000 !important; /* Padronizado para 10 mil */
        background: rgba(15, 23, 42, 0.7) !important;
        display: none;
    }

    .modal-premium.active,
    #exportModal.active,
    #confirmHideModal.active,
    #evidenceModal.active {
        display: flex !important;
    }

    @keyframes fadeInOverlay {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .evidence-modal-card,
    .modal-confirmation-content,
    .export-modal-content {
        background: #fff;
        border-radius: 0px;
        width: 100%;
        max-width: 600px;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        position: relative;
        margin: auto;
        /* Garante centralização quando o conteúdo for menor que a tela */
        will-change: transform, opacity;
        animation: scaleInModal 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes scaleInModal {
        from {
            transform: scale(0.9) translateY(40px);
            opacity: 0;
        }

        to {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }

    .evidence-modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
        border-radius: 0px;
        border: none;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        font-size: 24px;
        color: #1e293b;
        cursor: pointer;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .evidence-modal-close:hover {
        background: #fff;
        color: var(--primary);
        transform: rotate(90deg) scale(1.1);
    }

    .evidence-modal-image-box {
        width: 100%;
        height: 380px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .evidence-modal-image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .evidence-modal-info {
        padding: 32px 40px;
    }

    .evidence-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .evidence-info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
    }

    .item-icon {
        width: 32px;
        height: 32px;
        border-radius: 0px;
        background: rgba(227, 6, 19, 0.05);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .evidence-modal-actions {
        display: flex;
        gap: 16px;
    }

    .evidence-btn {
        flex: 1;
        min-width: 100px;
        height: 48px;
        border-radius: 0px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        border: none;
    }

    .evidence-btn.print {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
    }
    
    .evidence-btn.print:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .evidence-btn.pdf {
        background: #fff;
        color: #E30613;
        border: 1px solid rgba(227, 6, 19, 0.2);
    }
    
    .evidence-btn.pdf:hover {
        background: rgba(227, 6, 19, 0.05);
        border-color: var(--primary);
    }

    /* Resolution Summary Styling */
    .resolution-summary-premium {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0px;
        padding: 20px;
        margin-bottom: 24px;
        animation: slideUp 0.4s ease;
    }

    .resolution-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        color: #1e293b;
        font-size: 13px;
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .resolution-title i {
        color: var(--primary);
    }

    .resolution-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 14px;
    }

    .res-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .res-label {
        font-size: 10px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .res-value {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }

    .res-notes {
        border-top: 1px dashed #cbd5e1;
        padding-top: 12px;
    }

    .res-text {
        font-size: 13px;
        line-height: 1.6;
        color: #475569;
        margin: 5px 0 0 0;
        font-style: italic;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .evidence-btn.download {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .evidence-btn.download:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .evidence-btn.sign {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 8px 24px rgba(227, 6, 19, 0.2);
    }

    .evidence-btn.sign:hover {
        background: #c40510;
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(227, 6, 19, 0.3);
    }

    /* Dark Theme Support for Evidence Modal */
    html.dark-theme .evidence-modal-card {
        background: #1e293b;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.8);
    }

    html.dark-theme .evidence-modal-image-box {
        background: #0f172a;
    }

    html.dark-theme .evidence-modal-close {
        background: rgba(30, 41, 59, 0.8);
        color: #f8fafc;
    }

    html.dark-theme .evidence-modal-close:hover {
        background: #334155;
    }

    html.dark-theme .evidence-modal-info h3 {
        color: #f8fafc !important;
    }

    html.dark-theme .evidence-info-item {
        color: #94a3b8;
    }

    html.dark-theme .item-icon {
        background: rgba(227, 6, 19, 0.15);
    }

    html.dark-theme .evidence-no-photo p {
        color: #94a3b8;
    }

    html.dark-theme .evidence-no-photo i {
        color: #475569;
        opacity: 0.8 !important;
    }

    html.dark-theme .resolution-summary-premium {
        background: #0f172a;
        border-color: #334155;
    }

    html.dark-theme .resolution-title {
        color: #f8fafc;
    }

    html.dark-theme .res-label {
        color: #64748b;
    }

    html.dark-theme .res-value {
        color: #f8fafc;
    }

    html.dark-theme .res-notes {
        border-top-color: #334155;
    }

    html.dark-theme .res-text {
        color: #94a3b8;
    }

    html.dark-theme .evidence-btn.print {
        background: #334155;
        border-color: #475569;
        color: #f8fafc;
    }

    html.dark-theme .evidence-btn.print:hover {
        background: #475569;
    }

    html.dark-theme .evidence-btn.pdf {
        background: #334155;
        border-color: rgba(227, 6, 19, 0.3);
        color: #ff4d5a;
    }

    html.dark-theme .evidence-btn.pdf:hover {
        background: rgba(227, 6, 19, 0.1);
        border-color: var(--primary);
    }

    html.dark-theme .evidence-btn.download {
        background: #334155;
        border-color: #475569;
        color: #f8fafc;
    }

    /* ESTILOS DA NOVA BARRA DE DATA WRAPPER */
    .date-input-wrapper {
        display: none;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin-left: 10px;
        height: 50px !important;
    }

    .date-input-wrapper.active {
        display: flex;
    }

    .date-input-wrapper:hover {
        border-color: var(--primary);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }

    .date-input-wrapper input[type="date"] {
        border: none;
        background: transparent;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
    }

    .date-input-wrapper span {
        font-size: 11px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .date-input-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .date-minimal {
        border: none;
        background: transparent;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: #1e293b;
        font-size: 14px;
        outline: none;
        width: 130px;
    }

    .date-input-group i {
        color: #64748b;
        font-size: 14px;
        cursor: pointer;
    }

    .date-divider {
        color: #94a3b8;
        font-weight: 800;
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .date-input-wrapper .btn-apply-date {
        width: 32px;
        height: 32px;
        border-radius: 0px;
        background: var(--primary);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .date-input-wrapper .btn-apply-date:hover {
        transform: scale(1.1);
        background: #c40510;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Dark Mode p/ Nova Barra de Data */
    html.dark-theme .date-input-wrapper {
        background: #1e293b;
        border-color: #334155;
    }

    html.dark-theme .date-input-wrapper input[type="date"] {
        color: #f8fafc;
    }

    html.dark-theme .date-input-wrapper span {
        color: #64748b;
    }

    /* Estilo para as opções do Picker de Ordenação (Imagem 2) */
    .modern-picker-option.selected i {
        color: var(--primary);
        display: block !important;
    }
</style>

<script>
    /**
     * PORTAL DE MODAIS
     * Move os modais para fora do container main-content para evitar que 
     * transformações de CSS (animações de página) quebrem o position: fixed.
     */
    function portalModals() {
        const modalsToMove = ['evidenceModal', 'confirmHideModal', 'exportModal', 'modernPicker'];
        modalsToMove.forEach(id => {
            const modal = document.getElementById(id);
            if (modal && modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', portalModals);
    window.addEventListener('spaPageLoaded', portalModals);

    let currentEvidenceOccId = null;
    let currentEmployeeId = null;
    let currentSectorId = null;
    let currentEpiId = null;
    let currentDateTime = null;

    function openEvidenceModal(nome, epi, data, setor, fotoUrl, occId, funcId, sectorId, epiId, duration, acaoTipo, acaoObs, respNome, respCargo) {
        currentEvidenceOccId = occId;
        currentEmployeeId = funcId;
        currentSectorId = sectorId;
        currentEpiId = epiId;
        currentDateTime = data;
        currentDuration = duration;

        const modal = document.getElementById('evidenceModal');
        const img = document.getElementById('evidenceModalImg');
        const noPhoto = document.getElementById('evidenceNoPhoto');
        const downloadBtn = document.getElementById('evidenceDownloadBtn');
        const resBox = document.getElementById('evidenceResolutionBox');
        const signBtn = document.getElementById('evidenceSignBtn');

        // Valores padrão se nulo
        document.getElementById('evidenceModalName').textContent = nome || '---';
        document.getElementById('evidenceModalEpi').textContent = 'EPI: ' + (epi || '---');
        document.getElementById('evidenceModalSetor').textContent = 'Setor: ' + (setor || '---');
        document.getElementById('evidenceModalData').textContent = data || '---';
        document.getElementById('evidenceModalDuration').textContent = '<?= __('Tempo s/ EPI') ?>: ' + (duration || '---');

        // Resolution Data
        if (acaoTipo) {
            resBox.style.display = 'block';
            if (signBtn) signBtn.style.display = 'none';
            document.getElementById('resActionType').textContent = formatActionType(acaoTipo);
            document.getElementById('resSupervisorName').textContent = respNome + (respCargo ? ' (' + respCargo + ')' : '');
            document.getElementById('resObservations').textContent = acaoObs || '<?= __('Nenhuma observação registrada.') ?>';
        } else {
            resBox.style.display = 'none';
            if (signBtn) signBtn.style.display = 'flex';
        }

        // Reset display
        img.style.display = 'none';
        noPhoto.style.display = 'none';

        if (fotoUrl && fotoUrl.trim() !== '' && !fotoUrl.includes('null')) {
            img.src = fotoUrl;
            img.onload = () => {
                img.style.display = 'block';
                noPhoto.style.display = 'none';
            };
            img.onerror = () => {
                img.style.display = 'none';
                noPhoto.style.display = 'flex';
            };
            downloadBtn.href = fotoUrl;
            downloadBtn.style.display = 'flex';
        } else {
            img.style.display = 'none';
            noPhoto.style.display = 'flex';
            downloadBtn.style.display = 'none';
        }

        modal.classList.add('active');
        if (typeof toggleScroll === 'function') toggleScroll(true);
    }

    function formatActionType(type) {
        const map = {
            'OBSERVACAO': '<?= __('Observação') ?>',
            'ADVERTENCIA_VERBAL': '<?= __('Advertência Verbal') ?>',
            'ADVERTENCIA_ESCRITA': '<?= __('Advertência Escrita') ?>',
            'SUSPENSAO': '<?= __('Suspensão') ?>'
        };
        return map[type] || type;
    }

    function closeEvidenceModal() {
        const modal = document.getElementById('evidenceModal');
        modal.classList.remove('active');
        if (typeof toggleScroll === 'function') toggleScroll(false);
    }

    function signOccurrence() {
        if (!currentEmployeeId || !currentDateTime) return;

        const parts = currentDateTime.split(' ');
        if (parts.length < 2) return;

        const dateParts = parts[0].split('/');
        const timePart = parts[1];
        const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}T${timePart}`;

        const params = new URLSearchParams({
            employee_id: currentEmployeeId,
            sector_id: currentSectorId || '',
            epi_id: currentEpiId || '',
            datetime: formattedDate,
            original_id: currentEvidenceOccId
        });

        window.location.href = window.BASE_PATH + '/occurrences?' + params.toString();
    }

    async function downloadInfractionPdf() {
        const nome = document.getElementById('evidenceModalName').textContent;
        const epi = document.getElementById('evidenceModalEpi').textContent;
        const setor = document.getElementById('evidenceModalSetor').textContent;
        const data = document.getElementById('evidenceModalData').textContent;
        const duration = document.getElementById('evidenceModalDuration').textContent;
        
        const resBox = document.getElementById('evidenceResolutionBox');
        const hasResolution = resBox.style.display !== 'none';
        
        let resolutionHtml = '';
        if (hasResolution) {
            const resAction = document.getElementById('resActionType').textContent;
            const resSupervisor = document.getElementById('resSupervisorName').textContent;
            const resObs = document.getElementById('resObservations').textContent;
            
            resolutionHtml = `
                <div style="margin-top: 30px; padding: 25px; background: #f8fafc; border-radius: 0px; border: 1px solid #e2e8f0;">
                    <div style="border-bottom: 2px solid #1e293b; margin-bottom: 20px; padding-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 18px; color: #1e293b;">REGISTRO DE RESOLUÇÃO</h2>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="margin-bottom: 20px;">
                            <span style="font-weight: 800; color: #64748b; text-transform: uppercase; font-size: 11px; display: block; margin-bottom: 4px;">Ação Aplicada</span>
                            <span style="font-weight: 600; color: #1e293b; font-size: 17px;">${resAction}</span>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <span style="font-weight: 800; color: #64748b; text-transform: uppercase; font-size: 11px; display: block; margin-bottom: 4px;">Responsável</span>
                            <span style="font-weight: 600; color: #1e293b; font-size: 17px;">${resSupervisor}</span>
                        </div>
                    </div>
                    <div>
                        <span style="font-weight: 800; color: #64748b; text-transform: uppercase; font-size: 11px; display: block; margin-bottom: 4px;">Observações disciplinares</span>
                        <p style="font-style: italic; font-weight: 400; font-size: 14px; color: #475569; margin: 0;">${resObs}</p>
                    </div>
                </div>
            `;
        }

        const evidenceImg = document.getElementById('evidenceModalImg');
        const hasImg = evidenceImg && evidenceImg.style.display !== 'none';
        
        const container = document.createElement('div');
        container.style.width = '750px';
        container.style.padding = '40px';
        container.style.background = '#ffffff';
        container.style.fontFamily = 'Arial, sans-serif';
        
        container.innerHTML = `
            <div style="border-bottom: 3px solid #E30613; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0; font-size: 24px; color: #1e293b;">RELATÓRIO DE INFRAÇÃO</h1>
                <span style="font-weight: 900; color: #E30613; font-size: 20px;">FACCHINI</span>
            </div>
            
            <div style="display: flex; gap: 40px; margin-bottom: 30px;">
                <div style="flex: 1;">
                    <div style="margin-bottom: 15px;"><span style="font-weight: 900; color: #64748b; font-size: 11px; display: block;">COLABORADOR</span><span style="font-weight: 600; font-size: 16px;">${nome}</span></div>
                    <div style="margin-bottom: 15px;"><span style="font-weight: 900; color: #64748b; font-size: 11px; display: block;">EQUIPAMENTO (EPI)</span><span style="font-weight: 600; font-size: 16px;">${epi}</span></div>
                    <div style="margin-bottom: 15px;"><span style="font-weight: 900; color: #64748b; font-size: 11px; display: block;">SETOR</span><span style="font-weight: 600; font-size: 16px;">${setor}</span></div>
                    <div style="margin-bottom: 15px;"><span style="font-weight: 900; color: #64748b; font-size: 11px; display: block;">DATA/HORA</span><span style="font-weight: 600; font-size: 16px;">${data}</span></div>
                    <div><span style="font-weight: 900; color: #E30613; font-size: 11px; display: block;">TEMPO SEM EPI</span><span style="font-weight: 800; font-size: 16px;">${duration}</span></div>
                </div>
                ${hasImg ? `
                <div style="flex: 1; text-align: right;">
                    <span style="font-weight: 900; color: #64748b; font-size: 11px; display: block; margin-bottom: 10px;">EVIDÊNCIA FOTOGRÁFICA</span>
                    <img src="${evidenceImg.src}" style="width: 100%; border-radius: 0px; border: 1px solid #e2e8f0;" />
                </div>
                ` : ''}
            </div>

            ${resolutionHtml}

            <div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 30px;">
                <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 40px;">
                    ${hasResolution ? 
                        'Este documento confirma a regularização da infração de segurança, garantindo que o colaborador foi orientado e a situação foi tratada conforme os protocolos internos da FACCHINI.' : 
                        'Este relatório documenta uma não conformidade de segurança e serve como registro para futuras ações preventivas. O retorno imediato às normas é obrigatório.'}
                </p>
                
                <div style="margin-top: 80px; display: flex; justify-content: space-between; gap: 40px;">
                    <div style="border-top: 1px solid #1e293b; flex: 1; text-align: center; padding-top: 10px; font-size: 11px; font-weight: 700;">Supervisor / Instrutor responsável</div>
                    <div style="border-top: 1px solid #1e293b; flex: 1; text-align: center; padding-top: 10px; font-size: 11px; font-weight: 700;">Assinatura do Colaborador</div>
                </div>
            </div>
        `;

        const opt = {
            margin: 0,
            filename: `Relatorio_Infracao_${nome.replace(/\s+/g, '_')}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(container).save();
    }

    function printInfractionDetail() {
        const nome = document.getElementById('evidenceModalName').textContent;
        const epi = document.getElementById('evidenceModalEpi').textContent;
        const setor = document.getElementById('evidenceModalSetor').textContent;
        const data = document.getElementById('evidenceModalData').textContent;
        const duration = document.getElementById('evidenceModalDuration').textContent;
        
        const resBox = document.getElementById('evidenceResolutionBox');
        const hasResolution = resBox.style.display !== 'none';
        
        let resolutionHtml = '';
        if (hasResolution) {
            const resAction = document.getElementById('resActionType').textContent;
            const resSupervisor = document.getElementById('resSupervisorName').textContent;
            const resObs = document.getElementById('resObservations').textContent;
            
            resolutionHtml = `
                <div class="resolution-section">
                    <div class="section-header">
                        <h2>REGISTRO DE RESOLUÇÃO</h2>
                    </div>
                    <div class="resolution-grid">
                        <div class="info-row"><span class="label">Ação Aplicada</span><span class="value">${resAction}</span></div>
                        <div class="info-row"><span class="label">Responsável pela assinatura</span><span class="value">${resSupervisor}</span></div>
                    </div>
                    <div class="info-row">
                        <span class="label">Observações disciplinares</span>
                        <p class="value" style="font-style: italic; font-weight: 400; font-size: 14px; color: #475569;">${resObs}</p>
                    </div>
                </div>
            `;
        }

        const evidenceImg = document.getElementById('evidenceModalImg');
        const fotoUrl = (evidenceImg && evidenceImg.style.display !== 'none') ? evidenceImg.src : null;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Facchini - Infração ${nome}</title>
                    <style>
                        body { font-family: 'Inter', sans-serif; padding: 40px; color: #1e293b; max-width: 800px; margin: 0 auto; }
                        .header { border-bottom: 3px solid #E30613; margin-bottom: 30px; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
                        .header h1 { margin: 0; color: #1e293b; font-size: 24px; }
                        .content { display: flex; gap: 40px; margin-top: 30px; align-items: flex-start; }
                        .info-section { flex: 1; }
                        .image-section { flex: 1; text-align: right; }
                        .evidence-photo { max-width: 100%; border-radius: 0px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                        .info-row { margin-bottom: 20px; font-size: 16px; }
                        .label { font-weight: 800; color: #64748b; text-transform: uppercase; font-size: 11px; display: block; margin-bottom: 4px; letter-spacing: 0.5px; }
                        .value { font-weight: 600; color: #1e293b; font-size: 17px; }
                        .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 30px; }
                        .signature-line { margin-top: 60px; display: flex; justify-content: space-between; }
                        .line { border-top: 1px solid #1e293b; width: 45%; text-align: center; padding-top: 10px; font-size: 12px; font-weight: 600; }
                        .duration-badge { color: #E30613; font-weight: 800; font-size: 16px; margin-top: 10px; display: block; }
                        
                        /* Resolution Section in Print */
                        .resolution-section { margin-top: 30px; padding: 25px; background: #f8fafc; border-radius: 0px; border: 1px solid #e2e8f0; }
                        .section-header { border-bottom: 2px solid #1e293b; margin-bottom: 20px; padding-bottom: 10px; }
                        .section-header h2 { margin: 0; font-size: 18px; color: #1e293b; }
                        .resolution-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>REGISTRO DE INFRAÇÃO</h1>
                        <span style="font-weight: 900; color: #E30613; font-size: 20px;">FACCHINI</span>
                    </div>
                    
                    <div class="content">
                        <div class="info-section">
                            <div class="info-row"><span class="label">Colaborador</span><span class="value">${nome}</span></div>
                            <div class="info-row"><span class="label">Equipamento de Proteção</span><span class="value">${epi}</span></div>
                            <div class="info-row"><span class="label">Departamento / Setor</span><span class="value">${setor}</span></div>
                            <div class="info-row"><span class="label">Data e Hora</span><span class="value">${data}</span></div>
                            <div class="info-row"><span class="duration-badge"><i class="fa-solid fa-stopwatch"></i> ${duration}</span></div>
                        </div>
                        
                        ${fotoUrl ? `
                            <div class="image-section">
                                <span class="label" style="text-align: right; margin-bottom: 8px;">Evidência Fotográfica</span>
                                <img src="${fotoUrl}" class="evidence-photo" />
                            </div>
                        ` : ''}
                    </div>

                    ${resolutionHtml}

                    <div class="footer">
                        <p style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 30px;">
                            ${hasResolution ? 
                                'Este documento registra a identificação e a devida resolução da infração de segurança, confirmando que as medidas disciplinares e orientações necessárias foram aplicadas conforme política da empresa.' : 
                                'O colaborador acima citado foi identificado em situação de risco por descumprimento das normas de segurança do trabalho, especificamente o uso obrigatório de EPIs. Este registro serve para fins de controle interno e orientação preventiva.'}
                        </p>
        `);
        printWindow.document.close();
        
        // Espera as imagens carregarem antes de abrir a caixa de diálogo de impressão
        const img = printWindow.document.querySelector('.evidence-photo');
        if (img) {
            img.onload = () => {
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            };
            img.onerror = () => {
                printWindow.print();
                printWindow.close();
            };
        } else {
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    }

    document.addEventListener('click', (e) => {
        const modal = document.getElementById('evidenceModal');
        if (modal && e.target === modal) closeEvidenceModal();
    });
</script>

<!-- Modern Picker Modal (Apple Style) -->
<div class="modern-picker-modal" id="modernPicker">
    <div class="modern-picker-backdrop" onclick="closeModernPicker()"></div>
    <div class="modern-picker-container">
        <div class="modern-picker-header">
            <h3 id="pickerTitle"><?= __('Selecionar') ?></h3>
            <p id="pickerSubtitle"><?= __('Escolha uma opção abaixo') ?></p>
        </div>

        <!-- Barra de Busca do Picker -->
        <div class="picker-search-wrapper" id="pickerSearchWrapper">
            <div class="picker-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="pickerSearchInput" placeholder="<?= __('Pesquisar...') ?>" onkeyup="filterPickerOptions(this.value)">
            </div>
        </div>

        <div class="modern-picker-options" id="pickerOptionsContainer">
            <!-- Opções injetadas via JS -->
        </div>
        <button class="modern-picker-close" onclick="closeModernPicker()"><?= __('Cancelar') ?></button>
    </div>
</div>

<script>
    // Opções para o Picker Moderno
    window.PICKER_OPTIONS = {
        periodo: [
            { value: 'todos', label: '<?= __('Todos os períodos') ?>' },
            { value: 'hoje', label: '<?= __('Hoje') ?>' },
            { value: 'semana', label: '<?= __('Esta Semana') ?>' },
            { value: 'mes', label: '<?= __('Este Mês') ?>' },
            { value: 'personalizado', label: '<?= __('Personalizado') ?>' }
        ],
        status: [
            { value: 'todos', label: '<?= __('Todos os Status') ?>' },
            { value: 'pendente', label: '<?= __('Pendente') ?>' },
            { value: 'resolvido', label: '<?= __('Resolvido') ?>' },
            { value: 'inativo', label: '<?= __('Inativo') ?>' }
        ],
        epi: [
            { value: 'todos', label: '<?= __('Todos os EPIs') ?>' },
            <?php foreach ($episList as $epiItem): ?>
                { value: '<?= htmlspecialchars($epiItem->getName()) ?>', label: '<?= htmlspecialchars(__($epiItem->getName())) ?>' },
            <?php endforeach; ?>
        ],
        visualizacao: [
            { value: 'nome', label: '<?= __('Exibir Nome') ?>' },
            { value: 'cards', label: '<?= __('Exibir Cards') ?>' }
        ],
        order: [
            { value: 'recentes', label: '<?= __('Mais Recentes') ?>' },
            { value: 'alfabetica', label: '<?= __('Ordem Alfabética') ?>' },
            { value: 'frequentes', label: '<?= __('Mais Frequentes') ?>' }
        ],
        setor: [
            { value: 'todos', label: '<?= __('Todos os setores') ?>' },
            <?php foreach ($sectorsList as $sector): ?>
                { value: '<?= $sector->getId() ?>', label: '<?= htmlspecialchars(__db($sector)) ?>' },
            <?php endforeach; ?>
        ],
        funcionario: [
            { value: 'todos', label: '<?= __('Todos os funcionários') ?>' },
            <?php foreach ($employeesList as $employee): ?>
                { value: '<?= $employee->getId() ?>', label: '<?= htmlspecialchars($employee->getName()) ?>' },
            <?php endforeach; ?>
        ]
    };

    /**
     * Aplica o filtro de data personalizada
     */
    function applyCustomDate(isManual = true) {
        const from = document.getElementById('dateFromInput').value;
        const to = document.getElementById('dateToInput').value;

        if (!from || !to) {
            if (isManual) showAlert('<?= __('Aviso') ?>', '<?= __('Por favor, selecione as duas datas.') ?>', 'warning');
            return;
        }

        document.getElementById('hiddenDate_from').value = from;
        document.getElementById('hiddenDate_to').value = to;
        document.getElementById('hiddenPeriodo').value = 'personalizado';

        document.getElementById('filterForm').submit();
    }

    /**
     * Verifica e aplica a data automaticamente se ambas estiverem preenchidas
     */
    function checkAndApplyDate() {
        const from = document.getElementById('dateFromInput').value;
        const to = document.getElementById('dateToInput').value;
        
        const isValid = (val) => {
            if (!val || val.length < 10) return false;
            const year = parseInt(val.split('-')[0]);
            return year > 1900 && year < 2100;
        };

        if (isValid(from) && isValid(to)) {
            applyCustomDate(false); // Silent apply
        }
    }

    /**
     * Atualiza o texto de exibição do input de data
     */
    function updateDateDisplay(input, displayId) {
        if (!input.value) return;
        const [year, month, day] = input.value.split('-');
        document.getElementById(displayId).value = `${day}/${month}/${year}`;
    }

    // Inicializar displays de data se já houver valores
    document.addEventListener('DOMContentLoaded', () => {
        const dFrom = document.getElementById('dateFromInput');
        const dTo = document.getElementById('dateToInput');
        if (dFrom && dFrom.value) updateDateDisplay(dFrom, 'dateFromInputDisplay');
        if (dTo && dTo.value) updateDateDisplay(dTo, 'dateToInputDisplay');
    });

    window.addEventListener('spaPageLoaded', () => {
        const dFrom = document.getElementById('dateFromInput');
        const dTo = document.getElementById('dateToInput');
        if (dFrom && dFrom.value) updateDateDisplay(dFrom, 'dateFromInputDisplay');
        if (dTo && dTo.value) updateDateDisplay(dTo, 'dateToInputDisplay');
    });
</script>

<script src="<?= BASE_PATH ?>/assets/js/picker.js"></script>
<script src="<?= BASE_PATH ?>/assets/js/infractions.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

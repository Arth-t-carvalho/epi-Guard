<?php
$pageTitle = 'epiGuard - ' . __('Infrações');
$extraHead = '
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/infractions.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/picker.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/modal/modalInfractions.css">
';
ob_start();
?>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
</script>

<!-- Custom Header for Infractions -->
<div class="page-header-custom">
    <div class="page-title">
        <h1><?= __('Infrações') ?></h1>
        <p><?= __('Gestão de ocorrências e infrações de EPI') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" onclick="openExportModal()">
            <i class="fa-solid fa-file-export"></i> <?= __('Exportar') ?>
        </button>
    </div>
</div>

<div class="page-content">


    <!-- Filters -->
    <form action="<?= BASE_PATH ?>/infractions" method="GET" class="filter-bar" id="filterForm">
        <div class="filter-group select-search">
            <input type="text" name="search" id="searchInput" placeholder="<?= __('Buscar funcionário ou setor...') ?>"
                value="<?= htmlspecialchars($filters['search']) ?>">
        </div>

        <!-- Hidden Fields for Filters -->
        <input type="hidden" name="periodo" id="hiddenPeriodo" value="<?= htmlspecialchars($filters['periodo']) ?>">
        <input type="hidden" name="data_inicio" id="hiddenDataInicio" value="<?= htmlspecialchars($filters['data_inicio'] ?? '') ?>">
        <input type="hidden" name="data_fim" id="hiddenDataFim" value="<?= htmlspecialchars($filters['data_fim'] ?? '') ?>">
        <input type="hidden" name="status" id="hiddenStatus" value="<?= htmlspecialchars($filters['status']) ?>">
        <input type="hidden" name="epi" id="hiddenEpi" value="<?= htmlspecialchars($filters['epi']) ?>">
        <input type="hidden" name="visualizacao" id="hiddenVisualizacao"
            value="<?= htmlspecialchars($filters['visualizacao']) ?>">
        <input type="hidden" name="ordenacao" id="hiddenOrdenacao"
            value="<?= htmlspecialchars($filters['ordenacao']) ?>">

        <!-- Modern Triggers -->
        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('periodo')">
                <i class="fa-solid fa-calendar-days"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('Período') ?></span>
                    <span class="trigger-value" id="label-periodo">
                        <?php
                        $periodLabels = [
                            'todos' => __('Todos os períodos'),
                            'hoje' => __('Hoje'),
                            'semana' => __('Esta Semana'),
                            'mes' => __('Este Mês'),
                            'personalizado' => __('Personalizado')
                        ];
                        echo $periodLabels[$filters['periodo']] ?? 'Todos';
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <!-- Custom Date Range Inputs (Only visible if periodo is personalizado) -->
        <div id="customDateRangeBox" class="filter-group custom-date-box" style="<?= $filters['periodo'] === 'personalizado' ? 'display: flex;' : 'display: none;' ?>">
            <div class="date-input-wrapper">
                <input type="date" id="uiDateInicio" value="<?= htmlspecialchars($filters['data_inicio'] ?? '') ?>" title="Data Início">
                <span><?= __('até') ?></span>
                <input type="date" id="uiDateFim" value="<?= htmlspecialchars($filters['data_fim'] ?? '') ?>" title="Data Fim">
                <button type="button" class="btn-mini-apply" onclick="applyCustomDateRange()">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('status')">
                <i class="fa-solid fa-list-check"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('Status') ?></span>
                    <span class="trigger-value" id="label-status">
                        <?php
                        $statusLabels = [
                            'todos' => __('Todos'), 
                            'pendente' => __('Pendente'), 
                            'resolvido' => __('Resolvido'),
                            'inativo' => __('Inativo')
                        ];
                        echo $statusLabels[$filters['status']] ?? 'Todos';
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('epi')">
                <i class="fa-solid fa-mask-face"></i>
                <div class="trigger-info">
                    <span class="trigger-label">EPI</span>
                    <span class="trigger-value" id="label-epi">
                        <?= $filters['epi'] === 'todos' ? __('Todos os EPIs') : htmlspecialchars($filters['epi']) ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('ordenacao')">
                <i class="fa-solid fa-arrow-down-wide-short"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('Ordenar por') ?></span>
                    <span class="trigger-value" id="label-ordenacao">
                        <?php
                        $orderLabels = [
                            'tempo' => __('Mais Recentes'),
                            'alfabetica' => __('Ordem Alfabética'),
                            'frequente' => __('Mais Frequentes')
                        ];
                        echo $orderLabels[$filters['ordenacao']] ?? 'Mais Recentes';
                        ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <div class="filter-group">
            <div class="modern-picker-trigger" onclick="openModernPicker('visualizacao')">
                <i class="fa-solid fa-table-columns"></i>
                <div class="trigger-info">
                    <span class="trigger-label"><?= __('Visualização') ?></span>
                    <span class="trigger-value" id="label-visualizacao">
                        <?= $filters['visualizacao'] === 'nome' ? 'Exibir Nome' : 'Exibir Cards' ?>
                    </span>
                </div>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </div>

        <button type="submit" style="display: none;"></button>
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
                    <div class="infraction-card<?= !empty($infraction['favorito']) ? ' is-bookmarked' : '' ?>"
                        id="card-infraction-<?= $infraction['id'] ?>">
                        <div class="card-image-box">
                            <?php
                            $photoPath = !empty($infraction['funcionario_foto']) ? BASE_PATH . '/' . $infraction['funcionario_foto'] : BASE_PATH . '/assets/img/default-avatar.png';
                            ?>
                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($infraction['funcionario_nome']) ?>"
                                class="card-employee-photo" onerror="this.src='<?= BASE_PATH ?>/assets/img/default-avatar.png'">
                            <span
                                class="status-badge-premium <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>">
                                <?= __($infraction['status'] === 'resolvido' ? 'Resolvido' : 'Pendente') ?>
                            </span>
                        </div>
                        <div class="card-content-premium">
                            <h4 class="employee-name"><?= htmlspecialchars($infraction['funcionario_nome']) ?></h4>
                            <div class="info-row-premium">
                                <i class="fa-solid fa-briefcase"></i>
                                <span><?= __('Setor') ?>: <?= htmlspecialchars($infraction['setor_sigla'] ?? 'N/A') ?></span>
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
                                <?php
                                $args = json_encode([
                                    $infraction['funcionario_nome'],
                                    $infraction['epi_nome'] ?? 'N/A',
                                    date('d/m/Y H:i', strtotime($infraction['data_hora'])),
                                    $infraction['setor_sigla'] ?? 'N/A',
                                    !empty($infraction['evidencia_foto']) ? BASE_PATH . '/' . ltrim(str_replace('\\', '/', $infraction['evidencia_foto']), '/') : '',
                                    $infraction['id'],
                                    $infraction['funcionario_id'],
                                    $infraction['setor_id'] ?: null,
                                    $infraction['epi_id'] ?: null
                                ]);
                                ?>
                                <button class="btn-card-action" title="<?= __('Ver detalhes') ?>"
                                    onclick="openEvidenceModal.apply(null, <?= htmlspecialchars($args, ENT_QUOTES, 'UTF-8') ?>)"><i
                                        class="fa-solid fa-eye"></i></button>
                                <button class="btn-card-action secondary<?= !empty($infraction['favorito']) ? ' active' : '' ?>"
                                    title="<?= __('Salvar para revisão') ?>" onclick="toggleBookmark(this, <?= $infraction['id'] ?>)"><i
                                        class="fa-solid fa-bookmark"></i></button>
                                <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                    <button class="btn-card-action success" title="<?= __('Resolver') ?>"><i
                                            class="fa-solid fa-check"></i></button>
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
                        <th>EPI</th>
                        <th><?= __('Horário') ?></th>
                        <th>Status</th>
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
                            <tr id="row-infraction-<?= $infraction['id'] ?>"
                                class="<?= !empty($infraction['favorito']) ? 'is-bookmarked' : '' ?>">
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
                                    <?= htmlspecialchars($infraction['epi_nome'] ?? 'N/A') ?>
                                </td>
                                <td><?= date('H:i', strtotime($infraction['data_hora'])) ?></td>
                                <td data-status="<?= htmlspecialchars($infraction['status'] ?? 'pendente') ?>">
                                    <span
                                        class="status-dot <?= ($infraction['status'] ?? 'pendente') === 'resolvido' ? 'resolved' : 'pending' ?>"></span>
                                    <?= __($infraction['status'] === 'resolvido' ? 'Resolvido' : 'Pendente') ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <?php
                                        $argsTable = json_encode([
                                            $infraction['funcionario_nome'],
                                            $infraction['epi_nome'] ?? 'N/A',
                                            date('d/m/Y H:i', strtotime($infraction['data_hora'])),
                                            $infraction['setor_sigla'] ?? 'N/A',
                                            !empty($infraction['evidencia_foto']) ? BASE_PATH . '/' . ltrim(str_replace('\\', '/', $infraction['evidencia_foto']), '/') : '',
                                            $infraction['id'],
                                            $infraction['funcionario_id'],
                                            $infraction['setor_id'] ?: null,
                                            $infraction['epi_id'] ?: null
                                        ]);
                                        ?>
                                        <button class="btn-action" title="<?= __('Ver detalhes') ?>"
                                            onclick="openEvidenceModal.apply(null, <?= htmlspecialchars($argsTable, ENT_QUOTES, 'UTF-8') ?>)"><i
                                                class="fa-solid fa-eye"></i></button>
                                        <button class="btn-action secondary<?= !empty($infraction['favorito']) ? ' active' : '' ?>"
                                            title="<?= __('Salvar para revisão') ?>" onclick="toggleBookmark(this, <?= $infraction['id'] ?>)"><i
                                                class="fa-solid fa-bookmark"></i></button>
                                        <?php if (($infraction['status'] ?? 'pendente') !== 'resolvido'): ?>
                                            <button class="btn-action success" title="<?= __('Resolver') ?>"><i
                                                    class="fa-solid fa-check"></i></button>
                                        <?php endif; ?>
                                        <button class="btn-action danger" title="<?= __('Excluir') ?>"
                                            onclick="confirmHideInfraction(<?= $infraction['id'] ?>, '<?= $infraction['funcionario_nome'] ?>')"><i
                                                class="fa-solid fa-trash"></i></button>
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
                        $deptRepo = new \epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository();
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
                        <input type="text" id="employeeSearchInput" placeholder="Pesquisar funcionário..."
                            oninput="filterExportEmployees(this.value)">
                    </div>
                    <!-- Selecionar Todos -->
                    <div class="selection-controls">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="selectAllEmployees"
                                onchange="toggleAllExportEmployees(this.checked)">
                            <span class="checkmark"></span>
                            <span class="label-text"> Selecionar Todos</span>
                        </label>
                        <span class="selected-count" id="selectedCount">0 selecionados</span>
                    </div>
                    <!-- Lista de Funcionários -->
                    <div class="employee-check-list" id="exportEmployeeList">
                        <div class="employee-empty info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a
                                lista.</span>
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

<!-- MODAL DE CONFIRMAÇÃO PARA OCULTAR INFRAÇÃO -->
<div id="confirmHideModal" class="modal-premium confirmation-modal">
    <div class="modal-premium-content confirm-modal-content" style="max-width: 400px;">
        <div class="modal-premium-header">
            <div>
                <h2>Ocultar Registro</h2>
                <p>Tem certeza que deseja remover este registro da visualização?</p>
            </div>
            <button class="close-premium" onclick="closeConfirmHideModal()">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding: 24px; text-align: center;">
            <div class="warning-icon" style="font-size: 48px; color: #f59e0b; margin-bottom: 16px;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5;">
                O registro de <strong><span id="hideTargetName"></span></strong> deixará de aparecer na listagem, mas
                continuará salvo no histórico do banco de dados.
            </p>
        </div>
        <div class="modal-premium-footer"
            style="padding: 16px 24px; display: flex; gap: 12px; justify-content: center; background: #fafafa; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px;">
            <button class="btn-cancel-premium" onclick="closeConfirmHideModal()"
                style="padding: 10px 20px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; font-weight: 600;">Cancelar</button>
            <button class="btn-confirm-hide" id="btnDoHide"
                style="padding: 10px 24px; border-radius: 10px; border: none; background: #E30613; color: #fff; cursor: pointer; font-weight: 700; box-shadow: 0 4px 12px rgba(227, 6, 19, 0.2);">Ocultar
                Registro</button>
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

<!-- MODAL DE VISUALIZAÇÃO DE EVIDÊNCIA -->
<div id="evidenceModal" class="evidence-modal-overlay">
    <div class="evidence-modal-card">
        <button class="evidence-modal-close" onclick="closeEvidenceModal()">&times;</button>
        <div class="evidence-modal-image-box">
            <img id="evidenceModalImg" src="" alt="Evidência">
            <div id="evidenceNoPhoto" class="evidence-no-photo" style="display:none;">
                <i class="fa-solid fa-camera-slash" style="font-size:48px; margin-bottom:16px; opacity:0.3;"></i>
                <p>Nenhuma evidência fotográfica registrada para esta ocorrência.</p>
            </div>
        </div>
        <div class="evidence-modal-info">
            <h3 id="evidenceModalName">-</h3>
            <div class="evidence-info-grid">
                <div class="evidence-info-item">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span id="evidenceModalEpi">-</span>
                </div>
                <div class="evidence-info-item">
                    <i class="fa-solid fa-building"></i>
                    <span id="evidenceModalSetor">-</span>
                </div>
                <div class="evidence-info-item">
                    <i class="fa-solid fa-clock"></i>
                    <span id="evidenceModalData">-</span>
                </div>
            </div>
            <div class="evidence-modal-actions">
                <a id="evidenceDownloadBtn" href="" download class="evidence-btn download">
                    <i class="fa-solid fa-download"></i> Baixar Evidência
                </a>
                <button class="evidence-btn sign" onclick="signOccurrence()">
                    <i class="fa-solid fa-signature"></i> Assinar Ocorrência
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== MODAL DE EVIDÊNCIA ===== */
    .evidence-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        animation: fadeInOverlay 0.3s ease;
    }

    .evidence-modal-overlay.active {
        display: flex;
    }

    @keyframes fadeInOverlay {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .evidence-modal-card {
        background: #fff;
        border-radius: 24px;
        width: 90%;
        max-width: 560px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        position: relative;
        animation: scaleIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes scaleIn {
        from {
            transform: scale(0.85);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .evidence-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: none;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
        font-size: 22px;
        color: #64748b;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .evidence-modal-close:hover {
        background: #fee2e2;
        color: #E30613;
        transform: rotate(90deg);
    }

    .evidence-modal-image-box {
        width: 100%;
        aspect-ratio: 16/10;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .evidence-modal-image-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .evidence-no-photo {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        padding: 20px;
    }

    .evidence-modal-info {
        padding: 24px 28px;
    }

    .evidence-modal-info h3 {
        font-size: 18px;
        font-weight: 800;
        color: #1F2937;
        margin: 0 0 16px 0;
    }

    .evidence-info-grid {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .evidence-info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
    }

    .evidence-info-item i {
        color: #E30613;
        font-size: 14px;
    }

    .evidence-modal-actions {
        display: flex;
        gap: 12px;
    }

    .evidence-btn {
        flex: 1;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        border: none;
        font-family: 'Inter', sans-serif;
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
        background: #E30613;
        color: #fff;
        box-shadow: 0 4px 14px rgba(227, 6, 19, 0.25);
    }

    .evidence-btn.sign:hover {
        background: #c40510;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(227, 6, 19, 0.35);
    }
</style>

<script>
    let currentEvidenceOccId = null;
    let currentEmployeeId = null;
    let currentSectorId = null;
    let currentEpiId = null;
    let currentDateTime = null;

    function openEvidenceModal(nome, epi, data, setor, fotoUrl, occId, funcId, sectorId, epiId) {
        currentEvidenceOccId = occId;
        currentEmployeeId = funcId;
        currentSectorId = sectorId;
        currentEpiId = epiId;
        currentDateTime = data;

        const modal = document.getElementById('evidenceModal');
        const img = document.getElementById('evidenceModalImg');
        const noPhoto = document.getElementById('evidenceNoPhoto');
        const downloadBtn = document.getElementById('evidenceDownloadBtn');

        document.getElementById('evidenceModalName').textContent = nome;
        document.getElementById('evidenceModalEpi').textContent = 'EPI: ' + epi;
        document.getElementById('evidenceModalSetor').textContent = 'Setor: ' + setor;
        document.getElementById('evidenceModalData').textContent = data;

        if (fotoUrl && fotoUrl.trim() !== '') {
            img.src = fotoUrl;
            img.style.display = 'block';
            noPhoto.style.display = 'none';
            downloadBtn.href = fotoUrl;
            downloadBtn.style.display = 'flex';
        } else {
            img.style.display = 'none';
            noPhoto.style.display = 'flex';
            downloadBtn.style.display = 'none';
        }

        document.body.appendChild(modal);
        modal.classList.add('active');
    }

    function closeEvidenceModal() {
        const modal = document.getElementById('evidenceModal');
        modal.classList.remove('active');
    }

    function signOccurrence() {
        if (!currentEmployeeId) return;

        // Formatar data para o input datetime-local (YYYY-MM-DDTHH:MM)
        // A data vem no formato dd/mm/yyyy hh:ii
        const parts = currentDateTime.split(' ');
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

    // Fechar ao clicar fora
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
            <h3 id="pickerTitle">Selecionar</h3>
            <p id="pickerSubtitle">Escolha uma opção abaixo</p>
        </div>
        <div class="modern-picker-options" id="pickerOptionsContainer">
            <!-- Opções injetadas via JS -->
        </div>
        <button class="modern-picker-close" onclick="closeModernPicker()">Cancelar</button>
    </div>
</div>

<script>
    // Opções para o Picker Moderno
    window.PICKER_OPTIONS = {
        periodo: [
            { value: 'todos', label: 'Todos os períodos' },
            { value: 'hoje', label: 'Hoje' },
            { value: 'semana', label: 'Esta Semana' },
            { value: 'mes', label: 'Este Mês' },
            { value: 'personalizado', label: 'Personalizado' }
        ],
        status: [
            { value: 'todos', label: 'Todos os Status' },
            { value: 'pendente', label: 'Pendente' },
            { value: 'resolvido', label: 'Resolvido' },
            { value: 'inativo', label: 'Inativo (Oculto)' }
        ],
        epi: [
            { value: 'todos', label: 'Todos os EPIs' },
            <?php foreach ($episList as $epiItem): ?>
                { value: '<?= htmlspecialchars($epiItem->getName()) ?>', label: '<?= htmlspecialchars($epiItem->getName()) ?>' },
            <?php endforeach; ?>
        ],
        visualizacao: [
            { value: 'nome', label: 'Exibir Nome' },
            { value: 'cards', label: 'Exibir Cards' }
        ],
        ordenacao: [
            { value: 'tempo', label: 'Mais Recentes' },
            { value: 'alfabetica', label: 'Ordem Alfabética' },
            { value: 'frequente', label: 'Mais Frequentes' }
        ]
    };
</script>

<script src="<?= BASE_PATH ?>/assets/js/picker.js"></script>
<script src="<?= BASE_PATH ?>/assets/js/infractions.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

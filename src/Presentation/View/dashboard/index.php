<?php
$pageTitle = 'epiGuard - ' . __('Painel Geral');
$extraHead = '
    <!-- Page CSS -->
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/dashboard.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/modal/modalDashboard.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/modal/modalGestaoSetor.css">
';
$extraScripts = '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="' . BASE_PATH . '/assets/js/dashboard.js"></script>
';
$extraHead .= '<style>.main-content { overflow-y: hidden !important; }</style>';

ob_start();
?>

<style>
    .modal-search-wrapper {
        padding: 0 4px 16px 4px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 16px;
    }

    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        width: 18px;
        height: 18px;
        color: #94a3b8;
        pointer-events: none;
    }

    #sectorSearchInput {
        width: 100%;
        padding: 12px 12px 12px 40px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s ease;
    }

    #sectorSearchInput:focus {
        outline: none;
        border-color: #E30613;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(227, 6, 19, 0.05);
    }

    .selection-row.hidden {
        display: none;
    }

    /* DARK MODE RELACIONADO AO BUSCADOR INTERNO */
    html.dark-theme .modal-search-wrapper {
        border-bottom-color: var(--border);
    }

    html.dark-theme #sectorSearchInput {
        background: #0f172a;
        border-color: var(--border);
        color: var(--text-main);
    }

    html.dark-theme #sectorSearchInput:focus {
        border-color: var(--primary);
        background: #1e293b;
    }
</style>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
    window.userRole = '<?= $_SESSION['user_cargo'] ?? 'instrutor' ?>';
    window.totalStudents = 100; // Mock total students
    
    // EPI Color Mappings from Database
    window.epiColorMappings = {
        <?php foreach ($epis as $epi): ?>
            '<?= $epi->getId() ?>': '<?= $epi->getColor() ?>',
            '<?= strtolower($epi->getName()) ?>': '<?= $epi->getColor() ?>',
        <?php endforeach; ?>
    };
</script>

<!-- KPI CARDS -->
<div class="kpi-grid">
    <div class="kpi-card card" id="cardKpiHoje">
        <span class="kpi-header"><?= __('INFRAÇÕES HOJE') ?></span>
        <div class="kpi-value">
            <span id="kpiDia">0</span>
            <span class="badge" id="badgeDia" style="display:none;">0%</span>
        </div>
    </div>

    <div class="kpi-card card" id="cardKpiSemana">
        <span class="kpi-header"><?= __('INFRAÇÕES SEMANA') ?></span>
        <div class="kpi-value">
            <span id="kpiSemana">0</span>
            <span class="badge" id="badgeSemana" style="display:none;">0%</span>
        </div>
    </div>

    <div class="kpi-card card" id="cardKpiMes">
        <span class="kpi-header"><?= __('INFRAÇÕES MÊS') ?></span>
        <div class="kpi-value">
            <span id="kpiMes">0</span>
        </div>
    </div>

    <div class="kpi-card card" id="cardKpiMedia" onclick="openConformityModal()" style="cursor: pointer;">
        <span class="kpi-header" id="kpiMediaHeader"><?= __('CONFORMIDADE (DIÁRIA)') ?></span>
        <div class="kpi-value">
            <span id="kpiMedia">0%</span>
        </div>
    </div>
</div>

<!-- MAIN CHART -->
<div class="chart-card card">
    <div class="chart-header-actions">
        <h3 class="chart-title"><?= __('Visão Geral Mensal') ?></h3>

        <div class="header-controls">
            <div class="active-filters-info" id="activeFiltersContainer" style="display: none;">
                <span class="active-count" id="selectedSectorsCount">0</span> <?= __('setores selecionados') ?>
            </div>
            <button class="btn-premium-filter" onclick="openCourseModal()">
                <i data-lucide="layers"></i>
                <span><?= __('Filtrar por Setor') ?></span>
            </button>
        </div>
    </div>
    <div class="chart-container" style="height: 240px;">
        <canvas id="mainChart"></canvas>
    </div>
</div>

<!-- BOTTOM GRID -->
<div class="chart-grid">
    <!-- Registro Diário -->
    <div class="card">
        <div class="section-header">
            <h3 class="section-title"><?= __('Registro Diário') ?></h3>
        </div>
        <div class="calendar-nav" onclick="toggleCalendar()" onmouseover="this.style.transform='scale(1.01)'"
            onmouseout="this.style.transform='scale(1)'">

            <button class="nav-btn" onclick="event.stopPropagation(); changeDay(-1)">❮</button>

            <div class="date-display"
                style="text-align: center; display: flex; flex-direction: column; align-items: center;">
                <div id="displayDayNum" style="font-size: 28px; font-weight: 800; line-height: 1;">
                    --
                </div>
                <div id="displayMonthStr" style="font-size: 13px; font-weight: 600;">
                    --
                </div>

                <div
                    style="font-size: 10px; color: #E30613; font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                    <span style="font-size: 8px;"></span> <?= __('Clique para expandir') ?>
                </div>
            </div>

            <button class="nav-btn" onclick="event.stopPropagation(); changeDay(1)">❯</button>
        </div>
        <div class="occurrences-list" id="occurrenceList">
            <!-- Filled by JS -->
        </div>
    </div>

    <!-- Donut Chart -->
    <div class="card">
        <div class="section-header">
            <h3 class="section-title"><?= __('Distribuição de EPIs') ?></h3>
        </div>
        <div class="chart-container" style="height: 200px;">
            <canvas id="doughnutChart"></canvas>
        </div>
    </div>

    <!-- Top Infrações (Placeholder) -->
    <div class="card">
        <div class="section-header">
            <h3 class="section-title"><?= __('Top Ocorrências') ?></h3>
        </div>
        <div class="infraction-list" id="topInfractions">
            <div class="list-item">
                <span class="occ-name">Placeholder</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 50%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notification-container" class="notification-container"></div>

<!-- Calendar Modal -->
<div id="calendarModal" class="modal-calendar">
    <div class="modal-content">
        <div class="calendar-header">
            <button id="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="month-selector">
                <span id="calMonthDisplay">Janeiro</span>
                <span id="calYearDisplay">2026</span>
            </div>
            <button id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <ul class="weeks">
            <li>Dom</li>
            <li>Seg</li>
            <li>Ter</li>
            <li>Qua</li>
            <li>Qui</li>
            <li>Sex</li>
            <li>Sáb</li>
        </ul>
        <ul class="days" id="calendarDays"></ul>
        <div class="manual-input">
            <div class="input-wrapper">
                <input type="text" id="manualDateInput" placeholder="DD/MM/AAAA">
                <button onclick="commitManualDate()"><i data-lucide="check"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h2 id="modalMonthTitle">Detalhes</h2>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <table class="custom-table">
                <thead>
                    <tr></tr>
                </thead>
                <tbody id="modalTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE SELEÇÃO MINIMALISTA -->
<div id="courseModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="modal-premium-header">
            <div>
                <h2><?= __('Selecione o Setor') ?></h2>
                <p><?= __('Filtre os dados do dashboard por área específica') ?></p>
            </div>
            <button class="close-premium" onclick="closeCourseModal()">&times;</button>
        </div>
        <div class="modal-premium-body">
            <!-- Barra de Pesquisa -->
            <div class="modal-search-wrapper">
                <div class="search-input-group">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="text" id="sectorSearchInput" placeholder="Pesquisar setor..."
                        onkeyup="filterSectors(this.value)">
                </div>
            </div>

            <div class="modal-selection-list">
                <div class="selection-row global-row" onclick="toggleSectorSelect('all')">
                    <div class="selection-main">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="check-all" checked
                                onchange="toggleAllSectors(this.checked); event.stopPropagation();">
                            <span class="checkmark"></span>
                        </label>
                        <div class="sector-cell">
                            <div class="sector-dot global"></div>
                            <span>Toda a Empresa</span>
                        </div>
                    </div>
                    <span class="status-tag">Visão Global</span>
                </div>

                <?php
                $deptRepo = new \epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository();
                $sectors = $deptRepo->findAll();
                foreach ($sectors as $sector):
                    ?>
                    <div class="selection-row" onclick="toggleSectorSelect('<?= $sector->getId() ?>')">
                        <div class="selection-main">
                            <label class="custom-checkbox">
                                <input type="checkbox" class="sector-check" value="<?= $sector->getId() ?>"
                                    data-name="<?= htmlspecialchars($sector->getName()) ?>"
                                    onchange="updateSelectionState(); event.stopPropagation();">
                                <span class="checkmark"></span>
                            </label>
                            <div class="sector-cell">
                                <div class="sector-dot"></div>
                                <span><?= htmlspecialchars($sector->getName()) ?></span>
                            </div>
                        </div>
                        <span class="status-tag active">Monitorado</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="modal-premium-footer">
            <button class="btn-apply-filter" onclick="applySectorsFilter()">
                <span><?= __('Aplicar Filtros') ?></span>
                <i data-lucide="check"></i>
            </button>
        </div>
    </div>
</div>

<!-- MODAL DE CONFIRMAÇÃO DE REDIRECIONAMENTO -->
<div id="confirmInfractionsModal" class="modal-premium">
    <div class="modal-confirmation-content">
        <i data-lucide="help-circle" class="main-icon"></i>
        <h2><?= __('Deseja ir para Infrações?') ?></h2>
        <p><?= __('Você será redirecionado para a página de detalhes com o filtro de período selecionado.') ?></p>

        <div class="confirmation-actions">
            <button class="btn-liquid" onclick="goToInfractions()">
                <span class="btn-text">
                    <i data-lucide="check-circle"></i>
                    <?= __('Confirmar') ?>
                </span>
                <div class="liquid"></div>
            </button>
            <button class="btn-light-shadow" onclick="closeConfirmModal()">
                <?= __('Cancelar') ?>
            </button>
        </div>
    </div>
</div>

<!-- MODAL DE ESCOLHA DE PERÍODO DA CONFORMIDADE -->
<div id="conformityModal" class="modal-premium">
    <div class="modal-premium-content" style="max-width: 400px;">
        <div class="modal-premium-header">
            <div>
                <h2><?= __('Período de Conformidade') ?></h2>
                <p><?= __('Você deseja ver a conformidade de qual período?') ?></p>
            </div>
            <button class="close-premium" onclick="closeConformityModal()">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding-top: 24px;">
            <div class="modal-selection-list" style="display: flex; flex-direction: column; gap: 8px;">
                <div class="selection-row" onclick="selectConformityPeriod('diaria')"
                    style="cursor: pointer; padding: 16px;">
                    <div class="selection-main">
                        <div class="sector-cell" style="display:flex; align-items:center;">
                            <i data-lucide="calendar" style="color:var(--primary);"></i>
                            <span style="margin-left:12px; font-weight: 600;">Diária</span>
                        </div>
                    </div>
                    <span class="status-tag"><?= __('Selecionar') ?></span>
                </div>
                <div class="selection-row" onclick="selectConformityPeriod('semanal')"
                    style="cursor: pointer; padding: 16px;">
                    <div class="selection-main">
                        <div class="sector-cell" style="display:flex; align-items:center;">
                            <i data-lucide="calendar-days" style="color:var(--primary);"></i>
                            <span style="margin-left:12px; font-weight: 600;">Semanal</span>
                        </div>
                    </div>
                    <span class="status-tag"><?= __('Selecionar') ?></span>
                </div>
                <div class="selection-row" onclick="selectConformityPeriod('mensal')"
                    style="cursor: pointer; padding: 16px;">
                    <div class="selection-main">
                        <div class="sector-cell" style="display:flex; align-items:center;">
                            <i data-lucide="calendar-range" style="color:var(--primary);"></i>
                            <span style="margin-left:12px; font-weight: 600;">Mensal</span>
                        </div>
                    </div>
                    <span class="status-tag"><?= __('Selecionar') ?></span>
                </div>
                <div class="selection-row" onclick="selectConformityPeriod('anual')"
                    style="cursor: pointer; padding: 16px;">
                    <div class="selection-main">
                        <div class="sector-cell" style="display:flex; align-items:center;">
                            <i data-lucide="calendar-clock" style="color:var(--primary);"></i>
                            <span style="margin-left:12px; font-weight: 600;">Anual</span>
                        </div>
                    </div>
                    <span class="status-tag"><?= __('Selecionar') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

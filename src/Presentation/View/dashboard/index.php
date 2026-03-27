<?php
$pageTitle = 'epiGuard - Painel Geral';
$extraHead = '
    <!-- Dependencies -->
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/dashboard-main.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/calendar.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
';
$extraScripts = '<script src="' . BASE_PATH . '/assets/js/dashboard.js"></script>';

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
    
    /* Dark Mode Overrides */
    html.dark-theme #sectorSearchInput {
        background: var(--bg-card);
        border-color: var(--border);
        color: var(--text-main);
    }
    
    html.dark-theme #sectorSearchInput:focus {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--primary);
    }

    .selection-row.hidden {
        display: none;
    }
</style>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
    window.userRole = '<?= $_SESSION['user_cargo'] ?? 'instrutor' ?>';
    window.totalStudents = 100; // Mock total students
    window.I18N = {
        'Janeiro': '<?= __('Janeiro') ?>', 'Fevereiro': '<?= __('Fevereiro') ?>', 'Março': '<?= __('Março') ?>', 'Abril': '<?= __('Abril') ?>',
        'Maio': '<?= __('Maio') ?>', 'Junho': '<?= __('Junho') ?>', 'Julho': '<?= __('Julho') ?>', 'Agosto': '<?= __('Agosto') ?>',
        'Setembro': '<?= __('Setembro') ?>', 'Outubro': '<?= __('Outubro') ?>', 'Novembro': '<?= __('Novembro') ?>', 'Dezembro': '<?= __('Dezembro') ?>',
        'ocorrência': '<?= __('ocorrência') ?>', 'ocorrências': '<?= __('ocorrências') ?>', 'encontrada': '<?= __('encontrada') ?>', 'encontradas': '<?= __('encontradas') ?>',
        'Clique para detalhes deste setor': '<?= __('Clique para detalhes deste setor') ?>',
        '🚨 CRÍTICO': '<?= __('🚨 CRÍTICO') ?>', '🟠 ALTO RISCO': '<?= __('🟠 ALTO RISCO') ?>', '🟡 MODERADO': '<?= __('🟡 MODERADO') ?>', '🟢 CONTROLADO': '<?= __('🟢 CONTROLADO') ?>',
        'Gerando PDF...': '<?= __('Gerando PDF...') ?>', 'Relatório de Ocorrências - EPI Guard': '<?= __('Relatório de Ocorrências - EPI Guard') ?>',
        'Data de Geração:': '<?= __('Data de Geração:') ?>', 'Nenhuma ocorrência encontrada para exportar neste mês.': '<?= __('Nenhuma ocorrência encontrada para exportar neste mês.') ?>',
        'Data': '<?= __('Data') ?>', 'Aluno': '<?= __('Aluno') ?>', 'EPI': '<?= __('EPI') ?>', 'Hora': '<?= __('Hora') ?>', 'Status': '<?= __('Status') ?>',
        'Erro ao gerar PDF.': '<?= __('Erro ao gerar PDF.') ?>', 'de': '<?= __('de') ?>', 'Filtro:': '<?= __('Filtro:') ?>',
        'Rank': '<?= __('Rank') ?>', 'Curso': '<?= __('Curso') ?>', 'Infrações': '<?= __('Infrações') ?>', 'Conformidade': '<?= __('Conformidade') ?>', 'Risco': '<?= __('Risco') ?>',
        'Infração (EPI)': '<?= __('Infração (EPI)') ?>', 'Horário': '<?= __('Horário') ?>', 'Nenhum registro encontrado.': '<?= __('Nenhum registro encontrado.') ?>',
        'Erro na conexão.': '<?= __('Erro na conexão.') ?>', 'Sem Infrações': '<?= __('Sem Infrações') ?>',
        'Capacete': '<?= __('Capacete') ?>', 'Óculos': '<?= __('Óculos') ?>', 'Jaqueta': '<?= __('Jaqueta') ?>', 'Avental': '<?= __('Avental') ?>', 'Luvas': '<?= __('Luvas') ?>', 'Máscara': '<?= __('Máscara') ?>', 'Protetor': '<?= __('Protetor') ?>', 'Total': '<?= __('Total') ?>',
        'Pendente': '<?= __('Pendente') ?>', 'Resolvido': '<?= __('Resolvido') ?>'
    };
</script>

<!-- KPI CARDS -->
<div class="kpi-grid">
    <div class="kpi-card card">
        <span class="kpi-header"><?= __('INFRAÇÕES HOJE') ?></span>
        <div class="kpi-value">
            <span id="kpiDia">0</span>
            <span class="badge" id="badgeDia">0%</span>
        </div>
    </div>

    <div class="kpi-card card">
        <span class="kpi-header"><?= __('INFRAÇÕES SEMANA') ?></span>
        <div class="kpi-value">
            <span id="kpiSemana">0</span>
            <span class="badge" id="badgeSemana">0%</span>
        </div>
    </div>

    <div class="kpi-card card">
        <span class="kpi-header"><?= __('INFRAÇÕES MÊS') ?></span>
        <div class="kpi-value">
            <span id="kpiMes">0</span>
        </div>
    </div>

    <div class="kpi-card card">
        <span class="kpi-header"><?= __('CONFORMIDADE') ?></span>
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
    <div class="chart-container" style="height: 300px;">
        <canvas id="mainChart"></canvas>
    </div>
</div>

<!-- BOTTOM GRID -->
<div class="chart-grid">
    <!-- Registro Diário -->
    <div class="card">
        <div class="section-header">
            <h3 class="section-title"><?= __('Registro Diário') ?></h3>
            <button class="calendar-trigger" onclick="toggleCalendar()">
                <i data-lucide="calendar"></i>
            </button>
        </div>
        <div class="calendar-nav" onclick="toggleCalendar()"
            onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">

            <button class="nav-btn" onclick="event.stopPropagation(); changeDay(-1)">❮</button>

            <div class="date-display"
                style="text-align: center; display: flex; flex-direction: column; align-items: center;">
                <div id="displayDayNum"
                    style="color: #E30613; font-size: 28px; font-weight: 800; line-height: 1;">
                    --
                </div>
                <div id="displayMonthStr" style="color: #64748B; font-size: 13px; font-weight: 600;">
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
        <div class="chart-container" style="height: 250px;">
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
                <span id="calMonthDisplay"><?= __('Janeiro') ?></span>
                <span id="calYearDisplay">2026</span>
            </div>
            <button id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <ul class="weeks">
            <li><?= __('Dom') ?></li><li><?= __('Seg') ?></li><li><?= __('Ter') ?></li><li><?= __('Qua') ?></li><li><?= __('Qui') ?></li><li><?= __('Sex') ?></li><li><?= __('Sáb') ?></li>
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
                    <input type="text" id="sectorSearchInput" placeholder="<?= __('Pesquisar setor...') ?>" onkeyup="filterSectors(this.value)">
                </div>
            </div>

            <div class="modal-selection-list">
                <div class="selection-row global-row" onclick="toggleSectorSelect('all')">
                    <div class="selection-main">
                        <label class="custom-checkbox">
                            <input type="checkbox" id="check-all" checked onchange="toggleAllSectors(this.checked); event.stopPropagation();">
                            <span class="checkmark"></span>
                        </label>
                        <div class="sector-cell">
                            <div class="sector-dot global"></div>
                            <span><?= __('Toda a Empresa') ?></span>
                        </div>
                    </div>
                    <span class="status-tag"><?= __('Visão Global') ?></span>
                </div>
                
                <?php 
                    $deptRepo = new \epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository();
                    $sectors = $deptRepo->findAll();
                    foreach ($sectors as $sector): 
                ?>
                    <div class="selection-row" onclick="toggleSectorSelect('<?= $sector->getId() ?>')">
                        <div class="selection-main">
                            <label class="custom-checkbox">
                                <input type="checkbox" class="sector-check" value="<?= $sector->getId() ?>" data-name="<?= htmlspecialchars($sector->getName()) ?>" onchange="updateSelectionState(); event.stopPropagation();">
                                <span class="checkmark"></span>
                            </label>
                            <div class="sector-cell">
                                <div class="sector-dot"></div>
                                <span><?= htmlspecialchars($sector->getName()) ?></span>
                            </div>
                        </div>
                        <span class="status-tag active"><?= __('Monitorado') ?></span>
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

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

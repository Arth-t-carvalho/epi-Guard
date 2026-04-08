// =============================================================
// DASHBOARD.JS - VERSÃO UNIFICADA (SUA LÓGICA + CALENDÁRIO VISUAL)
// =============================================================

// --- VARIÁVEIS GLOBAIS ---
// Usa 'var' para permitir re-declaração durante transições SPA
var selectedDate = new Date();
var currCalYear = new Date().getFullYear();
var currCalMonth = new Date().getMonth();
var allOccurrences = [];
var mainChartInstance = null;
var doughnutChartInstance = null;
var selectedCourseId = 'all';
var selectedSectorId = 'all';
var selectedCompliancePeriod = localStorage.getItem('Facchini_compliancePeriod') || 'hoje'; // 'hoje', 'semana', 'mes', 'anual'
var pendingRedirectPeriod = 'todos';

// Arrays auxiliares para internacionalização (i18n)
var monthsFull = [];

function refreshI18n() {
    monthsFull = (window.I18N && window.I18N.months && Array.isArray(window.I18N.months) && window.I18N.months.length === 12)
        ? window.I18N.months
        : ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
}

function destroyCharts() {
    if (mainChartInstance) {
        mainChartInstance.destroy();
        mainChartInstance = null;
    }
    if (doughnutChartInstance) {
        doughnutChartInstance.destroy();
        doughnutChartInstance = null;
    }
}

// ===============================
// EXPORTAÇÃO NATIVA PARA PDF
// ===============================
window.exportDashboardData = async function () {
    toggleScroll(true);

    try {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            throw new Error("Biblioteca jsPDF não carregada. Pressione F5 ou verifique a conexão.");
        }

        // 1. Buscar Dados Consolidados do Backend (Insights)
        const response = await fetch(`${window.BASE_PATH}/api/export/insights`);
        const result = await response.json();

        if (!result || result.status !== 'success') {
            const errorMsg = result ? (result.message || result.error) : "Sem resposta do servidor";
            throw new Error(`Falha ao carregar dados do Dashboard: ${errorMsg}`);
        }

        const data = result;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'p',
            unit: 'mm',
            format: 'a4'
        });

        // Configurações Globais
        const primaryColor = [227, 6, 19];
        const textColor = [31, 41, 55];
        const mutedColor = [107, 114, 128];
        const grayBg = [248, 250, 252];
        const pageW = doc.internal.pageSize.getWidth();
        const pageH = doc.internal.pageSize.getHeight();

        // Helper para desenhar cabeçalho premium
        const drawHeader = (title) => {
            doc.setFillColor(...primaryColor);
            doc.rect(0, 0, pageW, 45, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(22);
            doc.text(title, 14, 25);

            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            const gerado = new Date().toLocaleString('pt-BR');
            doc.text(`Gerado em: ${gerado}`, 14, 35);
            doc.text(`Ano de Referência: ${data.year || currCalYear}`, 14, 41);
        };

        const drawSectionTitle = (y, text) => {
            doc.setTextColor(...textColor);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(16);
            doc.text(text, 14, y);
            const textWidth = doc.getTextWidth(text);
            doc.setDrawColor(...primaryColor);
            doc.setLineWidth(1);
            doc.line(14, y + 2, 14 + textWidth, y + 2);
            return y + 10;
        };

        const drawCard = (y, rTitle, rValue, line1, line2) => {
            const h = 25;
            doc.setDrawColor(226, 232, 240);
            doc.setFillColor(255, 255, 255);
            doc.roundedRect(14, y, pageW - 28, h, 2, 2, 'FD');
            doc.setFillColor(...primaryColor);
            doc.rect(14, y, 3, h, 'F');

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.setTextColor(15, 23, 42);
            doc.text(rTitle, 22, y + 8);

            doc.setFontSize(14);
            doc.setTextColor(...primaryColor);
            doc.text(rValue, 22, y + 15);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(...mutedColor);
            doc.text(line1, 22, y + 20);
            if (line2) doc.text(line2, 22, y + 24);
            return y + h + 8;
        };

        // =============================
        // PÁGINA 1: RESUMO EXECUTIVO (PORTUGUÊS)
        // =============================
        drawHeader('Relatório de Desempenho EPI');

        let cursorY = 55;
        cursorY = drawSectionTitle(cursorY, 'Resumo Executivo Anual');
        cursorY += 5;

        // Cards Baseados nos Dados do Backend
        cursorY = drawCard(cursorY, 'Setor com Mais Infrações', data.worst_sector.nome,
            `Total de ${data.worst_sector.total} infrações detectadas este ano.`,
            'Este setor apresenta a maior taxa de não conformidade no uso de EPIs.'
        );

        const primaryEpi = data.worst_epis[0] ? data.worst_epis[0].nome : 'Nenhum dado';
        const primaryEpiTotal = data.worst_epis[0] ? data.worst_epis[0].total : 0;

        cursorY = drawCard(cursorY, 'EPI Mais Negligenciado', primaryEpi,
            `Total: ${primaryEpiTotal} ocorrências de não uso.`,
            'Equipamento com o maior índice de ausência detectado pelo sistema.'
        );

        cursorY = drawCard(cursorY, 'Mês Crítico do Ano', data.worst_month.nome,
            `Total de ${data.worst_month.total} infrações no mês.`,
            'Período do ano com a maior concentração histórica de alertas.'
        );

        cursorY = drawCard(cursorY, 'Dia de Maior Ocorrência', data.worst_day_of_week.nome,
            `Dia da semana com pico de alertas (${data.worst_day_of_week.total}).`,
            'Tendência semanal identificada para reforço de fiscalização.'
        );

        // =============================
        // PÁGINA 2: RANKINGS (PORTUGUÊS)
        // =============================
        doc.addPage();
        drawHeader('Análise de Rankings');
        cursorY = 55;

        cursorY = drawSectionTitle(cursorY, 'Ranking de Infrações por Setor');
        cursorY += 5;

        const sectorTableData = data.sectors_ranking.map((s, i) => {
            let risk = 'CONTROLADO';
            if (s.total >= 50) risk = 'CRÍTICO';
            else if (s.total >= 20) risk = 'ALTO';
            else if (s.total >= 10) risk = 'MODERADO';
            return [i + 1, s.nome, s.total, risk];
        });

        doc.autoTable({
            startY: cursorY,
            head: [['#', 'Setor Fabril', 'Total de Infrações', 'Nível de Risco']],
            body: sectorTableData,
            theme: 'grid',
            headStyles: { fillColor: primaryColor, textColor: 255, fontStyle: 'bold' },
            styles: { fontSize: 9 },
            alternateRowStyles: { fillColor: grayBg },
            margin: { left: 14, right: 14 }
        });

        cursorY = doc.lastAutoTable.finalY + 15;

        cursorY = drawSectionTitle(cursorY, 'Ranking de EPIs Ausentes');
        cursorY += 5;

        const epiTableData = data.epis_ranking.map((e, i) => [i + 1, e.nome, e.total]);

        doc.autoTable({
            startY: cursorY,
            head: [['#', 'Equipamento (EPI)', 'Total de Ocorrências']],
            body: epiTableData,
            theme: 'grid',
            headStyles: { fillColor: primaryColor, textColor: 255, fontStyle: 'bold' },
            styles: { fontSize: 9 },
            alternateRowStyles: { fillColor: grayBg },
            margin: { left: 14, right: 14 }
        });

        // =============================
        // PÁGINA 3: ANÁLISE GRÁFICA
        // =============================
        if (mainChartInstance || doughnutChartInstance) {
            doc.addPage();
            drawHeader('Análise Visual de Tendências');
            cursorY = 55;

            if (mainChartInstance && mainChartInstance.canvas) {
                doc.setFont('helvetica', 'bold'); doc.setFontSize(11);
                doc.setTextColor(...textColor);
                doc.text('Distribuição Mensal (Consolidado)', 14, cursorY);
                cursorY += 8;

                // Usando canvas.toDataURL para máxima compatibilidade
                const img1 = mainChartInstance.canvas.toDataURL('image/png', 1.0);
                const contentW = pageW - 28;
                doc.addImage(img1, 'PNG', 14, cursorY, contentW, contentW * 0.4);
                cursorY += (contentW * 0.4) + 20;
            }

            if (doughnutChartInstance && doughnutChartInstance.canvas && cursorY < pageH - 100) {
                doc.setFont('helvetica', 'bold'); doc.setFontSize(11);
                doc.setTextColor(...textColor);
                doc.text('Distribuição por Tipo de EPI', 14, cursorY);
                cursorY += 8;

                const img2 = doughnutChartInstance.canvas.toDataURL('image/png', 1.0);
                const dSize = 85;
                doc.addImage(img2, 'PNG', (pageW / 2) - (dSize / 2), cursorY, dSize, dSize);
            }
        }

        // Rodapé em todas as páginas
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text(`Página ${i} de ${pageCount}`, pageW / 2, pageH - 10, { align: 'center' });
            doc.text("Facchini © 2026 - Proteção e Tecnologia", 14, pageH - 10);
        }

        doc.save(`Relatorio_Dashboard_${data.year}_${new Date().toISOString().split('T')[0]}.pdf`);

    } catch (e) {
        console.error("Erro na Exportação PDF:", e);
        showAlert(window.I18N?.labels?.error || 'Erro', "Erro ao tentar gerar o PDF do Dashboard. Por favor, tente novamente.", 'error');
    } finally {
        toggleScroll(false);
    }
};

// --- INICIALIZAÇÃO ---
function initDashboard() {
    refreshI18n();      // Sincroniza traduções
    destroyCharts();    // Limpa instâncias antigas

    // Pequeno delay para garantir que o container saia do 'display:none' ou animação inicial
    setTimeout(() => {
        loadCalendarData(); // Carrega dados da API
        loadCharts();       // Carrega Gráficos
    }, 50);

    // --- Atualização Automática (Polling de 30 segundos) ---
    if (window._dashRefreshInterval) clearInterval(window._dashRefreshInterval);
    window._dashRefreshInterval = setInterval(() => {
        // Só atualiza se ainda estiver na página do dashboard
        if (document.getElementById('kpiSemana')) {
            loadCalendarData();
            loadCharts();
        } else {
            clearInterval(window._dashRefreshInterval);
        }
    }, 30000);

    // Listeners do Modal de ESCOLHA DE DATA (Calendário Visual)
    const btnPrev = document.getElementById('prevMonth');
    const btnNext = document.getElementById('nextMonth');
    if (btnPrev) btnPrev.addEventListener('click', () => changeCalMonth(-1));
    if (btnNext) btnNext.addEventListener('click', () => changeCalMonth(1));

    // Input Manual
    const input = document.getElementById('manualDateInput');
    if (input) {
        input.addEventListener('keydown', (e) => { if (e.key === 'Enter') commitManualDate(); });
        input.addEventListener('input', maskDateInput);
    }

    // Fechar modais ao clicar fora (Listener global de documento)
    if (!window._dashboardClickHandled) {
        document.addEventListener('click', (e) => {
            // Modal Calendário
            const calModal = document.getElementById('calendarModal');
            if (calModal && e.target === calModal) toggleCalendar();

            // Modal Detalhes (Gráfico)
            const detModal = document.getElementById('detailModal');
            if (detModal && e.target === detModal) {
                detModal.classList.remove('open');
                const mc = document.querySelector('.main-content');
                if (mc) mc.style.overflow = '';
            }

            // Modal Conformidade
            const compModal = document.getElementById('complianceModal');
            if (compModal && e.target === compModal) closeComplianceModal();

            // Card Instrutor
            const card = document.getElementById('userProfileModal');
            const trigger = document.getElementById('profileTrigger');
            if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
                card.classList.remove('active');
                card.style.display = 'none'; // Sincroniza com notifications.js
            }
        });
        window._dashboardClickHandled = true;
    }

    // Re-render markers if lucide is available
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- Welcome Animation ---
    const welcomeContainer = document.getElementById('welcome-truck-container');
    const epiParade = document.getElementById('epi-parade');

    if (welcomeContainer && !sessionStorage.getItem('welcomeAnimated')) {
        const welcomeText = welcomeContainer.querySelector('.welcome-text');

        welcomeContainer.classList.add('animating');
        if (epiParade) epiParade.classList.add('active');

        setTimeout(() => {
            if (welcomeText) welcomeText.classList.add('delivered');
        }, 2470);

        setTimeout(() => {
            welcomeContainer.classList.remove('animating');
            if (epiParade) epiParade.classList.remove('active');
            sessionStorage.setItem('welcomeAnimated', 'true');
        }, 5500);
    }
}

// --- GESTÃO DE EVENTOS DE CARREGAMENTO ---

// Se o script rodar via SPA, o DOMContentLoaded já disparou.
// Mas o navigation.js disparará o 'spaPageLoaded' após a transição.
document.addEventListener('spaPageLoaded', initDashboard);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    // Se já estiver pronto e NÃO fomos carregados via SPA (primeira carga), roda agora
    if (!window._spaEngineLoaded || document.getElementById('mainChart')) {
        initDashboard();
    }
}

// ===============================
// 1. LÓGICA DE DADOS (API & UPDATE)
// ===============================

function loadCalendarData() {
    const month = currCalMonth + 1;
    const year = currCalYear;

    fetch(`${window.BASE_PATH}/api/calendar?month=${month}&year=${year}&sector_id=${selectedSectorId}`)
        .then(res => res.json())
        .then(data => {
            console.log('API CALENDAR DATA:', data);
            if (data && data.occurrences) {
                allOccurrences = data.occurrences;
                if (data.summary) {
                    updateKPIElements(data.summary);
                }
            } else {
                allOccurrences = Array.isArray(data) ? data : [];
            }
            console.log('ALL OCCURRENCES SET:', allOccurrences.length);
            renderInterface(); // Atualiza tela
        })
        .catch(err => {
            console.error('Erro calendário API:', err);
            allOccurrences = [];
            renderInterface();
        });
}

function renderInterface() {
    try {
        const day = String(selectedDate.getDate()).padStart(2, '0');
        const monthFullStr = (monthsFull && monthsFull[selectedDate.getMonth()]) ? monthsFull[selectedDate.getMonth()] : '';
        const yearStr = selectedDate.getFullYear();

        const elNum = document.getElementById('displayDayNum');
        const elStr = document.getElementById('displayMonthStr');

        if (elNum) elNum.innerText = day;
        if (elStr) elStr.innerText = `${monthFullStr} ${yearStr}`;

        const list = document.getElementById('occurrenceList');
        if (list) {
            list.innerHTML = '';

            if (!Array.isArray(allOccurrences)) {
                console.warn('allOccurrences is not an array:', allOccurrences);
                allOccurrences = [];
            }

            const selYear = selectedDate.getFullYear();
            const selMonth = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const selDay = String(selectedDate.getDate()).padStart(2, '0');
            const selDateStr = `${selYear}-${selMonth}-${selDay}`;

            const dailyData = allOccurrences.filter(item => {
                const dbDateString = item.full_date || item.data_hora || item.date;
                if (!dbDateString) return false;
                // Compara apenas a parte YYYY-MM-DD
                return dbDateString.startsWith(selDateStr);
            });

            if (dailyData.length > 0) {
                const sectorsMap = {};
                dailyData.forEach(item => {
                    const sName = item.name || 'Setor Desconhecido';
                    const sId = item.sector_id || 0;
                    const mapKey = sId || sName;

                    if (!sectorsMap[mapKey]) {
                        sectorsMap[mapKey] = { id: sId, name: sName, count: 0, initials: sName.substring(0, 2).toUpperCase() };
                    }
                    sectorsMap[mapKey].count++;
                });

                let htmlBuffer = '';
                Object.values(sectorsMap).forEach(s => {
                    const countText = s.count === 1 ? '1 ocorrência encontrada' : `${s.count} ocorrências encontradas`;
                    htmlBuffer += `
                        <div class="occurrence-item" onclick="redirectToInfractions('${s.id}', '${s.name}')" style="cursor:pointer;">
                            <div class="occ-avatar">${s.initials}</div>
                            <div class="occ-info">
                                <span class="occ-name">${s.name}</span>
                                <span class="occ-desc">${countText}</span>
                            </div>
                            <div class="occ-time">❯</div>
                        </div>`;
                });
                list.innerHTML = htmlBuffer;
            } else {
                list.innerHTML = `
                    <div class="empty-state" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; height: 100%; color: var(--text-muted); padding: 40px 20px;">
                        <i data-lucide="calendar-check" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.4;"></i>
                        <p style="font-size: 14px; font-weight: 500;">${window.I18N?.labels?.no_records || 'Nenhuma ocorrência registrada para este dia.'}</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons({ root: list });
            }
        }

        updatePercentagesDinamicamente();

        if (typeof applyGlobalSettings === 'function') {
            applyGlobalSettings();
        }
    } catch (err) {
        console.error('CRITICAL RENDER ERROR:', err);
    }
}

let selectedSectorIds = []; // Novo: Array de setores selecionados

function openCourseModal() {
    const modal = document.getElementById('courseModal');
    if (modal) {
        modal.classList.add('active');
        document.querySelector('.main-content').style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: modal });
        updateSelectionUI(); // Sincroniza checks com o estado
    }
}

function closeCourseModal() {
    const modal = document.getElementById('courseModal');
    if (modal) {
        modal.classList.remove('active');
        document.querySelector('.main-content').style.overflow = '';
    }
}

function toggleAllSectors(checked) {
    const checks = document.querySelectorAll('.sector-check');
    checks.forEach(c => c.checked = checked);
    updateSelectionState();
}

function toggleSectorSelect(id) {
    if (id === 'all') {
        const checkAll = document.getElementById('check-all');
        checkAll.checked = !checkAll.checked;
        toggleAllSectors(checkAll.checked);
        return;
    }
    const check = document.querySelector(`.sector-check[value="${id}"]`);
    if (check) {
        check.checked = !check.checked;
        updateSelectionState();
    }
}

function updateSelectionState() {
    const checks = document.querySelectorAll('.sector-check');
    const checked = Array.from(checks).filter(c => c.checked);
    const checkAll = document.getElementById('check-all');

    if (checked.length === checks.length) {
        checkAll.checked = true;
        checkAll.indeterminate = false;
    } else if (checked.length === 0) {
        checkAll.checked = false;
        checkAll.indeterminate = false;
    } else {
        checkAll.checked = false;
        checkAll.indeterminate = true;
    }
}

function updateSelectionUI() {
    const checks = document.querySelectorAll('.sector-check');
    checks.forEach(c => {
        c.checked = selectedSectorIds.includes(parseInt(c.value));
    });
    updateSelectionState();
}

function applySectorsFilter() {
    const checks = document.querySelectorAll('.sector-check:checked');
    selectedSectorIds = Array.from(checks).map(c => parseInt(c.value));

    const container = document.getElementById('activeFiltersContainer');
    const countLabel = document.getElementById('selectedSectorsCount');

    const allCount = document.querySelectorAll('.sector-check').length;

    if (selectedSectorIds.length === 0 || selectedSectorIds.length === allCount) {
        selectedSectorId = 'all'; // Compatibilidade
        if (container) container.style.display = 'none';
    } else {
        selectedSectorId = selectedSectorIds.join(','); // Compatibilidade/Novo formato
        if (container) {
            container.style.display = 'flex';
            if (countLabel) countLabel.innerText = selectedSectorIds.length;
        }
    }

    closeCourseModal();
    loadCalendarData();
    loadCharts();
}

// Mantendo para compatibilidade caso algo ainda use
function selectSectorRecord(id, name) {
    if (id === 'all') {
        selectedSectorIds = [];
    } else {
        selectedSectorIds = [parseInt(id)];
    }
    applySectorsFilter();
}

function applyCourseFilterByName(name) {
    // Procura na lista do modal pelo nome
    const rows = document.querySelectorAll('.selection-row');
    let foundId = null;

    rows.forEach(row => {
        const span = row.querySelector('.sector-cell span');
        if (span && span.innerText.trim().toLowerCase() === name.toLowerCase()) {
            const input = row.querySelector('.sector-check');
            if (input) {
                foundId = input.value;
            }
        }
    });

    if (foundId) {
        // Seleciona apenas este e aplica
        const checks = document.querySelectorAll('.sector-check');
        checks.forEach(c => c.checked = (c.value == foundId));
        applySectorsFilter();
    } else {
        openCourseModal();
    }
}

function filterSectors(query) {
    const filter = query.toLowerCase();
    const rows = document.querySelectorAll('.selection-row');

    rows.forEach(row => {
        if (row.classList.contains('global-row')) return;

        const span = row.querySelector('.sector-cell span');
        const text = span ? span.innerText.toLowerCase() : '';

        if (text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function irParaInfracoes(nome) {
    if (!nome) return;
    const url = `infracoes.php?periodo=todos&busca=${encodeURIComponent(nome)}`;
    window.location.href = url;
}

// --- LÓGICA DE CONFIRMAÇÃO DE REDIRECIONAMENTO ---
window.confirmRedirect = function (period) {
    window.location.href = `${window.BASE_PATH}/infractions?periodo=${period}`;
}

window.closeConfirmModal = function () {
    const modal = document.getElementById('confirmInfractionsModal');
    if (modal) {
        modal.classList.remove('active');
        document.querySelector('.main-content').style.overflow = '';
    }
}

window.goToInfractions = function () {
    const periodMap = {
        'hoje': 'hoje',
        'semana': 'semana',
        'mes': 'mes'
    };
    const period = periodMap[pendingRedirectPeriod] || 'todos';
    window.location.href = `${window.BASE_PATH}/infractions?periodo=${period}`;
}

function changeDay(delta) {
    const oldMonth = selectedDate.getMonth();
    selectedDate.setDate(selectedDate.getDate() + delta);
    const newMonth = selectedDate.getMonth();

    if (oldMonth !== newMonth) {
        loadCalendarData();
    } else {
        renderInterface();
    }
}

// ===============================
// 2. HELPERS (DATA & KPI)
// ===============================

function isSameDay(d1, d2) {
    if (!d1 || !d2) return false;
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

function isSameWeek(d1, d2) {
    if (!d1 || !d2) return false;
    const date1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate());
    const date2 = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate());
    const start1 = new Date(date1);
    start1.setDate(date1.getDate() - date1.getDay());
    const start2 = new Date(date2);
    start2.setDate(date2.getDate() - date2.getDay());
    return start1.getTime() === start2.getTime();
}

/**
 * Atualiza o status visual (badge) do card de conformidade.
 */
function updateConformityStatus(valor) {
    const card = document.getElementById('cardKpiMedia');
    if (!card) return;

    let badge = card.querySelector('.status-badge');
    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'status-badge';
        card.appendChild(badge);
    }

    const getClass = (v) => {
        if (v < 70) return 'status-critico';
        if (v < 85) return 'status-alto';
        if (v < 95) return 'status-moderado';
        return 'status-controlado';
    };

    badge.className = 'status-badge ' + getClass(valor);

    let label = '';
    if (valor < 70) label = window.I18N?.labels?.critical || 'CRÍTICO';
    else if (valor < 85) label = window.I18N?.labels?.high_risk || 'ALTO RISCO';
    else if (valor < 95) label = window.I18N?.labels?.moderate || 'MODERADO';
    else label = window.I18N?.labels?.controlled || 'CONTROLADO';

    badge.innerHTML = `<span class="status-dot"></span> ${label}`;
}

// --- MODAIS DE NAVEGAÇÃO E SELEÇÃO ---

function openComplianceModal() {
    const modal = document.getElementById('complianceModal');
    if (modal) {
        modal.classList.add('active');
        document.querySelector('.main-content').style.overflow = 'hidden';
        document.querySelectorAll('.period-option').forEach(opt => opt.classList.remove('active'));
        const activeOpt = document.getElementById(`opt-period-${selectedCompliancePeriod}`);
        if (activeOpt) activeOpt.classList.add('active');
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: modal });
    }
}

function closeComplianceModal() {
    const modal = document.getElementById('complianceModal');
    if (modal) {
        modal.classList.remove('active');
        document.querySelector('.main-content').style.overflow = '';
    }
}

function selectCompliancePeriod(period) {
    selectedCompliancePeriod = period;
    localStorage.setItem('Facchini_compliancePeriod', period);
    closeComplianceModal();
    loadCharts();
}

function toggleCalendar() {
    const modal = document.getElementById('calendarModal');
    if (!modal) return;

    if (!modal.classList.contains('active')) {
        currCalYear = selectedDate.getFullYear();
        currCalMonth = selectedDate.getMonth();
        renderCalendarGrid();
        modal.classList.add('active');
        document.querySelector('.main-content').style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } else {
        modal.classList.remove('active');
        document.querySelector('.main-content').style.overflow = '';
    }
}

function renderCalendarGrid() {
    const daysTag = document.getElementById("calendarDays");
    const monthTxt = document.getElementById("calMonthDisplay");
    const yearTxt = document.getElementById("calYearDisplay");
    if (!daysTag) return;

    let firstDayofMonth = new Date(currCalYear, currCalMonth, 1).getDay();
    let lastDateofMonth = new Date(currCalYear, currCalMonth + 1, 0).getDate();
    let lastDayofMonthIndex = new Date(currCalYear, currCalMonth, lastDateofMonth).getDay();
    let liTag = "";

    for (let i = firstDayofMonth; i > 0; i--) {
        liTag += `<li class="inactive">${new Date(currCalYear, currCalMonth, 0).getDate() - i + 1}</li>`;
    }
    for (let i = 1; i <= lastDateofMonth; i++) {
        let isToday = i === new Date().getDate() && currCalMonth === new Date().getMonth() && currCalYear === new Date().getFullYear() ? "today" : "";
        let isSelected = i === selectedDate.getDate() && currCalMonth === selectedDate.getMonth() && currCalYear === selectedDate.getFullYear() ? "active" : "";
        if (isSelected) isToday = "";
        liTag += `<li class="${isToday} ${isSelected}" onclick="selectDayAndClose(${i})">${i}</li>`;
    }
    for (let i = lastDayofMonthIndex; i < 6; i++) {
        liTag += `<li class="inactive">${i - lastDayofMonthIndex + 1}</li>`;
    }

    if (monthTxt) monthTxt.innerText = monthsFull[currCalMonth];
    if (yearTxt) yearTxt.innerText = currCalYear;
    daysTag.innerHTML = liTag;
}

function changeCalMonth(delta) {
    currCalMonth += delta;
    if (currCalMonth < 0 || currCalMonth > 11) {
        const d = new Date(currCalYear, currCalMonth, 1);
        currCalMonth = d.getMonth();
        currCalYear = d.getFullYear();
    }
    renderCalendarGrid();
}

function selectDayAndClose(day) {
    selectedDate = new Date(currCalYear, currCalMonth, day);
    loadCalendarData();
    toggleCalendar();
}

function maskDateInput(e) {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2);
    if (v.length > 5) v = v.slice(0, 5) + '/' + v.slice(5);
    e.target.value = v;
}

function commitManualDate() {
    const input = document.getElementById('manualDateInput');
    const v = input.value;
    if (v.length < 10) {
        triggerInputError();
        return;
    }
    const day = parseInt(v.slice(0, 2), 10);
    const monthIndex = parseInt(v.slice(3, 5), 10) - 1;
    const year = parseInt(v.slice(6, 10), 10);

    if (monthIndex < 0 || monthIndex > 11 || isNaN(monthIndex)) {
        triggerInputError();
        return;
    }
    const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
    if (day < 1 || day > daysInMonth || isNaN(day)) {
        triggerInputError();
        return;
    }
    currCalMonth = monthIndex;
    currCalYear = year;
    selectDayAndClose(day);
    input.value = "";
}

function triggerInputError() {
    const wrapper = document.querySelector('.input-wrapper');
    if (!wrapper) return;
    wrapper.classList.add('error-shake');
    setTimeout(() => { wrapper.classList.remove('error-shake'); }, 400);
}

// ===============================
// 4. INTERFACE E GRÁFICOS
// ===============================

function toggleInstructorCard() {
    const card = document.getElementById('instructorCard');
    if (card) card.classList.toggle('active');
}

function exportData() {
    // 1. Instanciar o jsPDF de forma segura
    const jsPDFLib = window.jspdf ? window.jspdf.jsPDF : window.jsPDF;
    if (!jsPDFLib) return showAlert(window.I18N?.labels?.error || 'Erro', 'Biblioteca jsPDF não carregada adequadamente.', 'error');
    const doc = new jsPDFLib('p', 'mm', 'a4');
    const btn = document.querySelector('.btn-export');

    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando PDF...';
    btn.disabled = true;

    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = 14;
    const contentWidth = pageWidth - margin * 2;
    const primaryColor = [227, 6, 19];
    const darkColor = [31, 41, 55];
    const grayBorder = [226, 232, 240];
    const grayText = [100, 116, 139];

    // Helper: Draw Premium Card
    const drawCard = (x, y, title, mainText, subText) => {
        const h = 26;
        // Fundo com borda
        doc.setDrawColor(...grayBorder);
        doc.setFillColor(250, 250, 252);
        doc.roundedRect(x, y, contentWidth, h, 2, 2, 'FD');
        // Faixa vermelha lateral
        doc.setFillColor(...primaryColor);
        doc.rect(x, y, 3, h, 'F');
        // Textos
        doc.setFontSize(10);
        doc.setTextColor(...darkColor);
        doc.setFont(undefined, 'bold');
        doc.text(title, x + 8, y + 7);
        doc.setFontSize(14);
        doc.setTextColor(...primaryColor);
        doc.text(mainText, x + 8, y + 15);
        doc.setFontSize(8);
        doc.setTextColor(...grayText);
        doc.setFont(undefined, 'normal');
        doc.text(subText, x + 8, y + 21);
    };

    let mainChartImg = null;
    let doughnutChartImg = null;
    try {
        if (typeof mainChartInstance !== 'undefined' && mainChartInstance) {
            mainChartImg = mainChartInstance.toBase64Image('image/png', 1);
        }
        if (typeof doughnutChartInstance !== 'undefined' && doughnutChartInstance) {
            doughnutChartImg = doughnutChartInstance.toBase64Image('image/png', 1);
        }
    } catch (e) { console.warn('Falha captura graficos', e); }

    fetch(`${window.BASE_PATH}/api/export-insights`)
        .then(res => res.json())
        .then(data => {
            // ================= PÁGINA 1: CAPA =================
            doc.setFillColor(...primaryColor);
            doc.rect(0, 0, pageWidth, 45, 'F');

            doc.setTextColor(255, 255, 255);
            doc.setFontSize(22);
            doc.setFont(undefined, 'bold');
            doc.text('Relatorio de Seguranca EPI', margin, 20);

            doc.setFontSize(10);
            doc.setFont(undefined, 'normal');
            doc.text(`Facchini - Gerado em ${data.generated_at}`, margin, 32);
            doc.text(`Ano de Referencia: ${data.year}`, margin, 38);

            let yCursor = 60;

            // Subtítulo
            doc.setTextColor(...darkColor);
            doc.setFontSize(16);
            doc.setFont(undefined, 'bold');
            doc.text('Resumo Executivo', margin, yCursor);

            // Underline Red
            doc.setDrawColor(...primaryColor);
            doc.setLineWidth(0.8);
            doc.line(margin, yCursor + 2, margin + 45, yCursor + 2);

            yCursor += 12;

            // Cards
            const worstSectorNome = data.worst_sector ? data.worst_sector.nome : 'Nenhum';
            const worstSectorTotal = data.worst_sector ? data.worst_sector.total : 0;
            drawCard(margin, yCursor, 'Setor com Mais Infracoes', worstSectorNome, `${worstSectorTotal} infracao(oes) registrada(s) no ano\nEste setor apresenta o maior numero de ocorrencias de nao conformidade com EPIs.`);

            yCursor += 32;
            const worstEpisStr = (data.worst_epis && data.worst_epis.length > 0) ? data.worst_epis.map(e => e.nome).join(', ') : 'Nenhum';
            const firstEpiTotal = (data.worst_epis && data.worst_epis.length > 0) ? data.worst_epis[0].total : 0;
            drawCard(margin, yCursor, 'EPIs Menos Utilizados', worstEpisStr, `${data.worst_epis[0] ? data.worst_epis[0].nome : 'EPI'}: ${firstEpiTotal} infracao(oes)\nEquipamentos de protecao com maior indice de nao utilizacao pelos colaboradores.`);

            yCursor += 32;
            const worstMonthNome = data.worst_month ? data.worst_month.nome : 'Nenhum';
            const worstMonthTotal = data.worst_month ? data.worst_month.total : 0;
            drawCard(margin, yCursor, 'Mes Critico', worstMonthNome, `${worstMonthTotal} infracao(oes) registrada(s)\nMes do ano com maior concentracao de infracoes relacionadas a EPIs.`);

            yCursor += 32;
            const worstDayNome = data.worst_day_of_week ? data.worst_day_of_week.nome : 'Nenhum';
            const worstDayTotal = data.worst_day_of_week ? data.worst_day_of_week.total : 0;
            drawCard(margin, yCursor, 'Dia da Semana Critico', worstDayNome, `${worstDayTotal} infracao(oes) registrada(s)\nDia da semana em que ocorre o maior numero de infracoes.`);


            // ================= PÁGINA 2: RANKINGS =================
            doc.addPage();
            doc.setFillColor(...primaryColor);
            doc.rect(0, 0, pageWidth, 30, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(18);
            doc.setFont(undefined, 'bold');
            doc.text('Rankings Detalhados', margin, 20);

            yCursor = 45;
            doc.setTextColor(...darkColor);
            doc.setFontSize(14);
            doc.text('Ranking de Setores', margin, yCursor);
            doc.setDrawColor(...primaryColor);
            doc.setLineWidth(0.8);
            doc.line(margin, yCursor + 2, margin + 40, yCursor + 2);

            if (data.sectors_ranking && data.sectors_ranking.length > 0) {
                doc.autoTable({
                    startY: yCursor + 6,
                    head: [['#', 'Setor', 'Infracoes', 'Nivel de Risco']],
                    body: data.sectors_ranking.map((s, i) => {
                        let risk = 'Baixo';
                        if (s.total > 20) risk = 'Critico';
                        else if (s.total > 10) risk = 'Medio';
                        return [i + 1, s.nome, s.total, risk];
                    }),
                    theme: 'striped',
                    headStyles: { fillColor: primaryColor }
                });
            }

            yCursor = doc.lastAutoTable ? doc.lastAutoTable.finalY + 15 : yCursor + 30;

            doc.setTextColor(...darkColor);
            doc.setFontSize(14);
            doc.text('Ranking de EPIs Menos Utilizados', margin, yCursor);
            doc.setDrawColor(...primaryColor);
            doc.line(margin, yCursor + 2, margin + 55, yCursor + 2);

            if (data.epis_ranking && data.epis_ranking.length > 0) {
                doc.autoTable({
                    startY: yCursor + 6,
                    head: [['#', 'Equipamento (EPI)', 'Total de Infracoes']],
                    body: data.epis_ranking.map((e, i) => [i + 1, e.nome, e.total]),
                    theme: 'striped',
                    headStyles: { fillColor: primaryColor }
                });
            }

            // ================= PÁGINA 3: GRÁFICOS =================
            if (mainChartImg || doughnutChartImg) {
                doc.addPage();
                doc.setFillColor(...primaryColor);
                doc.rect(0, 0, pageWidth, 30, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(18);
                doc.setFont(undefined, 'bold');
                doc.text('Analise Grafica', margin, 20);

                let gCursor = 45;

                if (mainChartImg) {
                    doc.setTextColor(...darkColor);
                    doc.setFontSize(12);
                    doc.text('Infracoes por Mes (Geral vs EPIs)', margin, gCursor);
                    const chartHeight = 75;
                    doc.addImage(mainChartImg, 'PNG', margin, gCursor + 5, contentWidth, chartHeight);
                    gCursor += chartHeight + 20;
                }

                if (doughnutChartImg && gCursor < 250) {
                    doc.setTextColor(...darkColor);
                    doc.setFontSize(12);
                    doc.text('Distribuicao Total de EPIs Ausentes', margin, gCursor);
                    const dogHeight = 70;
                    // Doughnut usually proportional
                    doc.addImage(doughnutChartImg, 'PNG', margin + (contentWidth / 2) - 35, gCursor + 5, 70, dogHeight);
                }
            }

            // 4. Concluir e Baixar o Arquivo
            doc.save(`relatorio_${data.year}.pdf`);

            btn.innerHTML = originalHTML;
            btn.disabled = false;
        })
        .catch(err => {
            showAlert(window.I18N?.labels?.error || 'Erro', 'Erro ao exportar PDF: ' + err.message, 'error');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}

function openDetailModal(monthIndex, monthName, epiName = '', filterSectorName = '') {
    const modal = document.getElementById('detailModal');
    const title = document.getElementById('modalMonthTitle');
    const tbody = document.getElementById('modalTableBody');
    const thead = document.querySelector('.custom-table thead tr');

    if (!modal) return;
    const realMonth = monthIndex + 1;
    const currentYear = currCalYear;
    const isGlobal = (selectedSectorId === 'all');

    let displayTitle = `${monthName} de ${currentYear}`;
    if (epiName) displayTitle += ` - ${window.I18N?.labels?.filter || 'Filtro'}: ${epiName}`;
    if (filterSectorName) displayTitle += ` - ${filterSectorName}`;
    title.innerText = displayTitle;

    modal.classList.add('active');
    document.querySelector('.main-content').style.overflow = 'hidden';

    if (isGlobal) {
        thead.innerHTML = `<th>${window.I18N?.labels?.rank || 'Rank'}</th><th>${window.I18N?.labels?.course || 'Curso'}</th><th>${window.I18N?.labels?.infractions || 'Infrações'}</th><th>${window.I18N?.labels?.conformity || 'Conformidade'}</th><th>${window.I18N?.labels?.risk || 'Risco'}</th>`;
    } else {
        thead.innerHTML = `<th>${window.I18N?.labels?.date || 'Data'}</th><th>${window.I18N?.labels?.student || 'Aluno'}</th><th>${window.I18N?.labels?.infraction_epi || 'Infração (EPI)'}</th><th>${window.I18N?.labels?.time || 'Horário'}</th><th>${window.I18N?.labels?.status || 'Status'}</th>`;
    }

    let url = `${window.BASE_PATH}/api/modal_details?month=${realMonth}&year=${currentYear}&sector_id=${selectedSectorId}`;
    if (epiName) url += `&epi=${encodeURIComponent(epiName)}`;
    if (filterSectorName) url += `&sector_name=${encodeURIComponent(filterSectorName)}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px;">${window.I18N?.labels?.no_records_found || 'Nenhum registro encontrado.'}</td></tr>`;
                return;
            }

            if (isGlobal) {
                data.forEach((row, index) => {
                    const totalAlunos = parseInt(row.total_alunos) || 1;
                    const alunosComInfracao = parseInt(row.alunos_com_infracao) || 0;
                    const conformidade = Math.round(((totalAlunos - alunosComInfracao) / totalAlunos) * 100);
                    let riskIcon = '';
                    if (conformidade < 50) riskIcon = '<span class="risk-triangle red">▲</span>';
                    else if (conformidade < 70) riskIcon = '<span class="risk-triangle orange">▲</span>';
                    else if (conformidade < 90) riskIcon = '<span class="risk-triangle yellow">▲</span>';

                    tbody.innerHTML += `
                        <tr onclick="selectSectorRecord(${row.curso_id}, '${row.curso_nome.replace(/'/g, "\\'")}')" style="cursor: pointer;">
                            <td>#${index + 1}</td>
                            <td style="font-weight:600;">${row.curso_nome}</td>
                            <td>${row.total_infracoes}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="mini-progress-bar"><div class="mini-progress-fill" style="width:${conformidade}%"></div></div>
                                    <span>${conformidade}%</span>
                                </div>
                            </td>
                            <td style="text-align:center;">${riskIcon}</td>
                        </tr>`;
                });
            } else {
                data.forEach(row => {
                    const statusTexto = row.status_formatado || row.status;
                    const translatedStatus = statusTexto === 'Pendente' ? (window.I18N?.labels?.pending || 'Pendente') : (statusTexto === 'Resolvido' ? (window.I18N?.labels?.resolved || 'Resolvido') : statusTexto);
                    let classeStatus = statusTexto === 'Pendente' ? 'status-pendente' : 'status-resolvido';
                    tbody.innerHTML += `
                        <tr>
                            <td>${row.data}</td>
                            <td style="font-weight:500;">${row.aluno}</td>
                            <td>${row.epis}</td>
                            <td>${row.hora}</td>
                            <td><span class="status-badge ${classeStatus}">${translatedStatus}</span></td>
                        </tr>`;
                });
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center">${window.I18N?.labels?.connection_error || 'Erro na conexão.'}</td></tr>`;
        });
}

function closeModal() {
    const modals = document.querySelectorAll('.modal-premium, .modal-calendar, .modal-overlay-calendar');
    modals.forEach(m => m.classList.remove('active'));
    document.querySelector('.main-content').style.overflow = '';
}

function loadCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(loadCharts, 300);
        return;
    }

    fetch(`${window.BASE_PATH}/api/charts?sector_id=${selectedSectorId}`)
        .then(res => res.json())
        .then(response => {
            try {
                if (response.summary) updateKPIElements(response.summary);

                if (mainChartInstance) { mainChartInstance.destroy(); mainChartInstance = null; }
                if (doughnutChartInstance) { doughnutChartInstance.destroy(); doughnutChartInstance = null; }

                const epiColorsMap = response.epi_colors || {};
                const datasets = [];
                const allowedEpis = response.allowed_epis || [];
                const chartStyle = response.chart_style || 'bar';
                const isLine = (chartStyle === 'line' || chartStyle === 'area');
                const isArea = (chartStyle === 'area');
                const chartType = isLine ? 'line' : 'bar';

                // Usa as chaves do próprio response.bar para garantir que nada fique de fora
                const availableSeries = Object.keys(response.bar || {}).filter(k => k !== 'total');
                
                availableSeries.forEach(fullName => {
                    const baseColor = epiColorsMap[fullName] || '#94a3b8';
                    const data = response.bar[fullName];
                    
                    // Somente adiciona se houver algum dado no ano (opcional, mas limpa o gráfico)
                    const hasData = data.some(v => v > 0);
                    if (!hasData) return;

                    datasets.push({
                        label: fullName,
                        data: data,
                        backgroundColor: isArea ? `${baseColor}66` : baseColor,
                        borderColor: baseColor,
                        borderWidth: isLine ? 2 : 1,
                        tension: isArea ? 0 : 0.4,
                        fill: isArea,
                        pointRadius: isLine ? 4 : 0,
                        pointHoverRadius: isLine ? 6 : 0
                    });
                });

                if (response.bar && response.bar.total) {
                    const totalColor = epiColorsMap['Total'] || '#E30613';
                    datasets.push({
                        label: window.I18N?.labels?.total || 'Total',
                        data: response.bar.total,
                        backgroundColor: isArea ? `${totalColor}44` : totalColor,
                        borderColor: totalColor,
                        borderWidth: isLine ? 3 : 1,
                        tension: isArea ? 0 : 0.4,
                        fill: isArea,
                        pointRadius: isLine ? 5 : 0, // Restaurado os pontos (ligeiramente maiores no total)
                        pointHoverRadius: isLine ? 8 : 0,
                        order: 0
                    });
                }

                const canvasMain = document.getElementById('mainChart');
                if (canvasMain) {
                    mainChartInstance = new Chart(canvasMain.getContext('2d'), {
                        type: chartType,
                        data: { labels: monthsFull, datasets: datasets },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            tension: 0.4,
                            onClick: (evt, active, chart) => {
                                const points = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                                if (points.length > 0) {
                                    const monthIndex = points[0].index;
                                    const exactPoints = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                                    let filterEPI = '';
                                    if (exactPoints.length > 0) {
                                        filterEPI = chart.data.datasets[exactPoints[0].datasetIndex]?.label || '';
                                    }
                                    openDetailModal(monthIndex, monthsFull[monthIndex], filterEPI);
                                }
                            },
                            plugins: { legend: { labels: { usePointStyle: true, padding: 20 } } },
                            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
                        }
                    });
                }

                const canvasDoughnut = document.getElementById('doughnutChart');
                if (canvasDoughnut) {
                    const isDoughnutEmpty = !response.doughnut || response.doughnut.total === 0;
                    doughnutChartInstance = new Chart(canvasDoughnut.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: isDoughnutEmpty ? ['Sem Infrações'] : response.doughnut.labels,
                            datasets: [{
                                data: isDoughnutEmpty ? [1] : response.doughnut.data,
                                backgroundColor: isDoughnutEmpty ? ['#f1f5f9'] : response.doughnut.colors
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '75%',
                            onClick: (evt, active, chart) => {
                                if (active.length > 0) {
                                    const label = chart.data.labels[active[0].index];
                                    openDetailModal(selectedDate.getMonth(), monthsFull[selectedDate.getMonth()], label);
                                }
                            }
                        }
                    });
                }

                const topList = document.getElementById('topInfractions');
                if (topList && response.doughnut && response.doughnut.total > 0) {
                    topList.innerHTML = '';
                    const max = Math.max(...response.doughnut.data);
                    response.doughnut.labels.forEach((label, i) => {
                        if (response.doughnut.data[i] > 0) {
                            const pct = Math.round((response.doughnut.data[i] / max) * 100);
                            topList.innerHTML += `
                                <div class="list-item">
                                    <span class="occ-name">${label}</span>
                                    <div class="progress-bar"><div class="progress-fill" style="width: ${pct}%; background-color: ${response.doughnut.colors[i]};"></div></div>
                                </div>`;
                        }
                    });
                }
            } catch (e) { console.error('Erro render grf:', e); }
        });
}

function selectMonth(index) {
    currCalMonth = index;
    renderCalendarGrid();
    document.getElementById('monthDropdown').classList.remove('active');
}

function selectYear(year) {
    currCalYear = year;
    renderCalendarGrid();
    document.getElementById('yearDropdown').classList.remove('active');
}

function refreshBadgesJS(currentVal, previousVal, elementId) {
    const badge = document.getElementById(elementId);
    if (!badge) return;
    let percent = 0;
    if (previousVal > 0) percent = Math.round(((currentVal - previousVal) / previousVal) * 100);
    else percent = currentVal * 100;
    const isUp = percent >= 0;
    badge.className = `badge ${isUp ? 'up' : 'down'}`;
    badge.innerHTML = `${isUp ? '↗' : '↘'} ${Math.abs(percent)}%`;
}

function updatePercentagesDinamicamente() {
    const datePrevDay = new Date(selectedDate);
    datePrevDay.setDate(selectedDate.getDate() - 1);
    const startOfSelectedWeek = new Date(selectedDate);
    startOfSelectedWeek.setDate(selectedDate.getDate() - selectedDate.getDay());
    const datePrevWeek = new Date(startOfSelectedWeek);
    datePrevWeek.setDate(datePrevWeek.getDate() - 7);

    let totalOntem = 0, totalSemanaPassada = 0;
    allOccurrences.forEach(item => {
        const dateStr = item.full_date || item.data_hora || item.date;
        if (!dateStr) return;
        const itemDate = new Date(dateStr.replace(/-/g, '/'));
        if (isSameDay(datePrevDay, itemDate)) totalOntem++;
        if (isSameWeek(datePrevWeek, itemDate)) totalSemanaPassada++;
    });

    const elDia = document.getElementById('kpiDia');
    const elSemana = document.getElementById('kpiSemana');
    if (elDia && elSemana) {
        refreshBadgesJS(parseInt(elDia.innerText) || 0, totalOntem, 'badgeDia');
        refreshBadgesJS(parseInt(elSemana.innerText) || 0, totalSemanaPassada, 'badgeSemana');
    }
}

/**
 * Função central para atualizar todos os elementos de KPI no topo do dashboard.
 */
function updateKPIElements(summary) {
    if (!summary) return;
    window.totalStudents = summary.total_students || 20;

    const elDia = document.getElementById('kpiDia');
    const elSemana = document.getElementById('kpiSemana');
    const elMes = document.getElementById('kpiMes');
    const elMedia = document.getElementById('kpiMedia');

    if (elDia) elDia.innerText = summary.today ?? 0;
    if (elSemana) elSemana.innerText = summary.week ?? 0;
    if (elMes) elMes.innerText = summary.month ?? 0;

    // Tornar cards clicáveis (Infrações) - Navegação Direta
    const cardHoje = document.getElementById('cardKpiHoje');
    if (cardHoje) {
        cardHoje.onclick = () => window.location.href = `${window.BASE_PATH}/infractions?periodo=hoje`;
        cardHoje.style.cursor = 'pointer';
    }

    const cardSemana = document.getElementById('cardKpiSemana');
    if (cardSemana) {
        cardSemana.onclick = () => window.location.href = `${window.BASE_PATH}/infractions?periodo=semana`;
        cardSemana.style.cursor = 'pointer';
    }

    const cardMes = document.getElementById('cardKpiMes');
    if (cardMes) {
        cardMes.onclick = () => window.location.href = `${window.BASE_PATH}/infractions?periodo=mes`;
        cardMes.style.cursor = 'pointer';
    }

    if (elMedia && window.totalStudents > 0) {
        let infraCount = 0;
        let periodLabel = '';

        if (selectedCompliancePeriod === 'hoje') {
            infraCount = summary.students_today || 0;
            periodLabel = (window.I18N?.labels?.daily || 'DIÁRIA').toUpperCase();
        } else if (selectedCompliancePeriod === 'semana') {
            infraCount = summary.students_week || 0;
            periodLabel = (window.I18N?.labels?.weekly || 'SEMANAL').toUpperCase();
        } else if (selectedCompliancePeriod === 'mes') {
            infraCount = summary.students_month || 0;
            periodLabel = (window.I18N?.labels?.monthly || 'MENSAL').toUpperCase();
        } else if (selectedCompliancePeriod === 'anual') {
            infraCount = summary.students_year || 0;
            periodLabel = (window.I18N?.labels?.annual || 'ANUAL').toUpperCase();
        }

        const conformidade = Math.max(0, Math.round(((window.totalStudents - infraCount) / window.totalStudents) * 100));
        elMedia.innerText = `${conformidade}%`;

        const header = document.getElementById('complianceHeader');
        if (header) {
            const conformityLabel = window.I18N?.labels?.conformity || 'CONFORMIDADE';
            header.innerText = `${conformityLabel.toUpperCase()} (${periodLabel})`;
        }
        updateConformityStatus(conformidade);
    }
}

// --- REDIRECIONAMENTOS ---

window.confirmRedirect = function (period) {
    window.location.href = `${window.BASE_PATH}/infractions?periodo=${period}`;
}

window.closeConfirmModal = function () {
    const modal = document.getElementById('confirmInfractionsModal');
    if (modal) {
        modal.classList.remove('active');
        document.querySelector('.main-content').style.overflow = '';
    }
}

window.goToInfractions = function () {
    const period = pendingRedirectPeriod || 'todos';
    window.location.href = `${window.BASE_PATH}/infractions?periodo=${period}`;
}

window.redirectToInfractions = function (sectorId, sectorName) {
    const y = selectedDate.getFullYear();
    const m = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const d = String(selectedDate.getDate()).padStart(2, '0');
    const dStr = `${y}-${m}-${d}`;

    const url = `${window.BASE_PATH}/infractions?setor_id=${sectorId}&periodo=personalizado&date_from=${dStr}&date_to=${dStr}`;
    window.location.href = url;
}

// Desbloqueia o áudio automaticamente no primeiro clique
document.addEventListener('click', function unlockAudio() {
    const dummyAudio = new Audio(`${window.BASE_PATH}/assets/som/notificacao.mp3`);
    dummyAudio.volume = 0;
    dummyAudio.play().then(() => {
        document.removeEventListener('click', unlockAudio);
    }).catch(e => console.error("Erro áudio:", e));
}, { once: true });

// Observer para disparar atualizações de badges
const observer = new MutationObserver(() => updatePercentagesDinamicamente());
const obsConfig = { childList: true, characterData: true, subtree: true };
['kpiDia', 'kpiSemana', 'kpiMes'].forEach(id => {
    const el = document.getElementById(id);
    if (el) observer.observe(el, obsConfig);
});

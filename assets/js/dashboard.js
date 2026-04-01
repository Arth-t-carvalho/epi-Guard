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
var selectedCompliancePeriod = 'hoje'; // 'hoje', 'semana', 'mes', 'anual'
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
        const pageW = doc.internal.pageSize.getWidth();
        const pageH = doc.internal.pageSize.getHeight();

        // Extrair dados visíveis de allOccurrences ignorando os filtrados se necessário.
        // Vamos usar a variável global `allOccurrences` do app.
        const exportData = allOccurrences || [];

        // --- Agregar Dados ---
        let countBySector = {};
        let countByEpi = {};
        let countByMonth = {};
        let countByDay = {};

        const weekDays = (window.I18N && window.I18N.labels) ? [window.I18N.labels.sun, window.I18N.labels.mon, window.I18N.labels.tue, window.I18N.labels.wed, window.I18N.labels.thu, window.I18N.labels.fri, window.I18N.labels.sat] : ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const monthsNames = monthsFull;

        exportData.forEach(item => {
            const rowDateRaw = item.full_date || item.data_hora || item.date;
            if (!rowDateRaw) return;
            const d = new Date(rowDateRaw.replace(/-/g, '/'));
            if (isNaN(d.getTime())) return;

            // Filtros de tela ativados (mes/ano)
            // Se exportData foi filtrado, iteramos. Se não, pegamos o selecionado.
            // Pelo mockup, "Ano de referência: 2026" foi usado.

            const sector = item.setor_nome || 'Desconhecido';
            countBySector[sector] = (countBySector[sector] || 0) + 1;

            const epi = item.epi || 'Não especificado';
            countByEpi[epi] = (countByEpi[epi] || 0) + 1;

            const m = d.getMonth();
            countByMonth[m] = (countByMonth[m] || 0) + 1;

            const wd = d.getDay();
            countByDay[wd] = (countByDay[wd] || 0) + 1;
        });

        // Helper para ordenar Dicionários > Arrays {name, count}
        const sortDict = (dict) => Object.entries(dict)
            .map(([name, count]) => ({ name, count }))
            .sort((a, b) => b.count - a.count);

        const sortedSectors = sortDict(countBySector);
        const sortedEpis = sortDict(countByEpi);
        const sortedMonths = sortDict(countByMonth);
        const sortedDays = sortDict(countByDay);

        const worstSector = sortedSectors[0] || { name: 'Sem dados', count: 0 };
        const worstEpi = sortedEpis[0] || { name: 'Sem dados', count: 0 };
        const worstMonthNum = sortedMonths[0] ? parseInt(sortedMonths[0].name) : 0;
        const worstMonthName = sortedMonths[0] ? monthsNames[worstMonthNum] : 'Sem dados';
        const worstMonthCount = sortedMonths[0] ? sortedMonths[0].count : 0;
        const worstDayNum = sortedDays[0] ? parseInt(sortedDays[0].name) : 0;
        const worstDayName = sortedDays[0] ? weekDays[worstDayNum] : 'Sem dados';
        const worstDayCount = sortedDays[0] ? sortedDays[0].count : 0;

        // Formatação de EPI (Ex: capacete -> Capacete de Segurança)
        const formatEPI = (epi) => {
            const m = { 'capacete': 'Capacete de Segurança', 'oculos': 'Óculos de Proteção', 'luvas': 'Luvas de Proteção' };
            return m[epi.toLowerCase()] || epi.charAt(0).toUpperCase() + epi.slice(1);
        };

        const topEpiName = worstEpi.name !== 'Sem dados' ? formatEPI(worstEpi.name) : 'Sem dados';

        const drawHeader = (title) => {
            doc.setFillColor(...primaryColor);
            doc.rect(0, 0, pageW, 45, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(22);
            doc.text(title, 14, 25);

            // Se for P1
            if (title.includes('EPI')) {
                doc.setFontSize(10);
                doc.setFont('helvetica', 'normal');
                const gerado = new Date().toLocaleString();
                const reportTitle = (window.I18N && window.I18N.labels && window.I18N.labels.report_title) || "PPE Safety Report";
                const refYearLabel = (window.I18N && window.I18N.labels && window.I18N.labels.ref_year) || "Reference Year";
                doc.text(`EPI Guard - ${gerado}`, 14, 35);
                doc.text(`${refYearLabel}: ${currCalYear}`, 14, 41);
            }
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

        // =============================
        // PÁGINA 1: RESUMO EXECUTIVO
        // =============================
        drawHeader('Relatório de Segurança EPI');

        let cursorY = 55;
        cursorY = drawSectionTitle(cursorY, 'Resumo Executivo');
        cursorY += 5;

        const drawCard = (y, rTitle, rValue, line1, line2) => {
            const h = 25;
            // Fundo Branco + Borda
            doc.setDrawColor(220, 220, 220);
            doc.setFillColor(255, 255, 255);
            doc.roundedRect(14, y, pageW - 28, h, 2, 2, 'FD');

            // Linha Vermelha lateral
            doc.setFillColor(...primaryColor);
            // jsPDF roundedRect edge bleeding hack: Desenha um retangulo pequeno sobre a ponta esquerda
            doc.rect(14, y, 3, h, 'F');

            // Textos
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10);
            doc.setTextColor(15, 23, 42);
            doc.text(rTitle, 20, y + 6); // Titulo Card

            doc.setFontSize(14);
            doc.setTextColor(...primaryColor);
            doc.text(rValue, 20, y + 14); // Valor Destaque (Vermelho)

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(...mutedColor);
            doc.text(line1, 20, y + 19);
            if (line2) doc.text(line2, 20, y + 23);

            return y + h + 8;
        };

        cursorY = drawCard(cursorY, window.I18N?.labels?.sector_more_infractions || 'Department with Most Infractions', worstSector.name,
            `${worstSector.count} ${worstSector.count > 1 ? (window.I18N?.labels?.infractions || 'infractions') : (window.I18N?.labels?.infraction || 'infraction')}`,
            window.I18N?.labels?.sector_more_infractions_desc || 'This department has the highest number of PPE non-compliance occurrences.'
        );

        cursorY = drawCard(cursorY, window.I18N?.labels?.epis_less_used || 'Least Used PPEs', topEpiName,
            `${topEpiName}: ${worstEpi.count} ${worstEpi.count > 1 ? (window.I18N?.labels?.infractions || 'infractions') : (window.I18N?.labels?.infraction || 'infraction')}`,
            window.I18N?.labels?.epis_less_used_desc || 'Protective equipment with the highest rate of non-use by employees.'
        );

        cursorY = drawCard(cursorY, window.I18N?.labels?.critical_month || 'Critical Month', worstMonthName,
            `${worstMonthCount} ${worstMonthCount > 1 ? (window.I18N?.labels?.infractions || 'infractions') : (window.I18N?.labels?.infraction || 'infraction')}`,
            window.I18N?.labels?.critical_month_desc || 'Month of the year with the highest concentration of PPE-related infractions.'
        );

        cursorY = drawCard(cursorY, window.I18N?.labels?.critical_weekday || 'Critical Day of the Week', worstDayName,
            `${worstDayCount} ${worstDayCount > 1 ? (window.I18N?.labels?.infractions || 'infractions') : (window.I18N?.labels?.infraction || 'infraction')}`,
            window.I18N?.labels?.critical_weekday_desc || 'Day of the week with the highest number of infractions.'
        );


        // =============================
        // PÁGINA 2: RANKINGS
        // =============================
        doc.addPage();
        drawHeader('Rankings Detalhados');
        cursorY = 55;

        cursorY = drawSectionTitle(cursorY, 'Ranking de Setores');
        cursorY += 5;

        const sectorTableData = sortedSectors.map((s, i) => {
            let risk = window.I18N?.labels?.low || 'Low';
            if (s.count >= 10) risk = window.I18N?.labels?.critical || 'Critical';
            else if (s.count >= 5) risk = window.I18N?.labels?.moderate || 'Moderate';
            return [i + 1, s.name, s.count, risk];
        });

        doc.autoTable({
            startY: cursorY,
            head: [['#', 'Setor', 'Infrações', 'Nível de Risco']],
            body: sectorTableData,
            theme: 'grid',
            headStyles: { fillColor: primaryColor, textColor: 255, halign: 'left', fontStyle: 'bold' },
            styles: { font: 'helvetica', fontSize: 10, textColor: 30 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: 14, right: 14 }
        });

        cursorY = doc.lastAutoTable.finalY + 15;

        cursorY = drawSectionTitle(cursorY, 'Ranking de EPIs Menos Utilizados');
        cursorY += 5;

        const epiTableData = sortedEpis.map((e, i) => {
            return [i + 1, formatEPI(e.name), e.count];
        });

        doc.autoTable({
            startY: cursorY,
            head: [['#', 'Equipamento (EPI)', 'Total de Infrações']],
            body: epiTableData,
            theme: 'grid',
            headStyles: { fillColor: primaryColor, textColor: 255, halign: 'left', fontStyle: 'bold' },
            styles: { font: 'helvetica', fontSize: 10, textColor: 30 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: 14, right: 14 }
        });


        // =============================
        // PÁGINA 3: ANÁLISE GRÁFICA
        // =============================
        if (mainChartInstance || doughnutChartInstance) {
            doc.addPage();
            drawHeader('Análise Gráfica');
            cursorY = 55;

            // Extrair Imagens em Base64 nativamente do canvas
            if (mainChartInstance) {
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.setTextColor(...textColor);
                doc.text('Infrações por Mês (Geral vs EPIs)', 14, cursorY);
                cursorY += 8;

                const img1 = mainChartInstance.toBase64Image('image/png', 1);
                // ChartJS image is wide, adjust aspect ratio. A4 width is 210, margins 14
                const contentW = pageW - 28;
                // typically chartjs is 2:1 aspect ratio
                doc.addImage(img1, 'PNG', 14, cursorY, contentW, contentW * 0.4);
                cursorY += (contentW * 0.4) + 15;
            }

            if (doughnutChartInstance && cursorY < pageH - 80) {
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.setTextColor(...textColor);
                doc.text('Distribuição Total de EPIs Ausentes', 14, cursorY);
                cursorY += 8;

                const img2 = doughnutChartInstance.toBase64Image('image/png', 1);
                // Doughnut is typically 1:1 or 4:3
                const chartSize = 80;
                doc.addImage(img2, 'PNG', (pageW / 2) - (chartSize / 2), cursorY, chartSize, chartSize);
            }
        }

        // --- SALVAR PDF ---
        doc.save(`Relatorio_EPI_Guard_${currCalYear}.pdf`);

    } catch (e) {
        console.error("Erro na Exportação PDF:", e);
        alert("Erro ao tentar gerar o PDF. Acesse o console log para detalhes.");
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
    const month = selectedDate.getMonth() + 1;
    const year = selectedDate.getFullYear();

    fetch(`${window.BASE_PATH}/api/calendar?month=${month}&year=${year}&sector_id=${selectedSectorId}`)
        .then(res => res.json())
        .then(data => {
            allOccurrences = Array.isArray(data) ? data : [];
            renderInterface(); // Atualiza tela
        })
        .catch(err => {
            console.error('Erro calendário:', err);
            allOccurrences = [];
            renderInterface();
        });
}

function renderInterface() {
    const day = String(selectedDate.getDate()).padStart(2, '0');
    const monthFullStr = monthsFull[selectedDate.getMonth()];
    const yearStr = selectedDate.getFullYear();

    const elNum = document.getElementById('displayDayNum');
    const elStr = document.getElementById('displayMonthStr');

    if (elNum) elNum.innerText = day;
    if (elStr) elStr.innerText = `${monthFullStr} ${yearStr}`;

    const list = document.getElementById('occurrenceList');
    if (list) {
        list.innerHTML = '';
        const dailyData = allOccurrences.filter(item => {
            const dbDateString = item.full_date || item.data_hora || item.date;
            const itemDate = new Date(dbDateString.replace(/-/g, '/'));
            return isSameDay(selectedDate, itemDate);
        });

        if (dailyData.length > 0) {
            const grouped = {};
            dailyData.forEach(item => {
                const key = item.name || 'Desconhecido';
                if (!grouped[key]) grouped[key] = { count: 0, items: [] };
                grouped[key].count++;
                grouped[key].items.push(item);
            });

            let htmlBuffer = '';
            Object.keys(grouped).forEach(name => {
                const data = grouped[name];
                const initials = name.substring(0, 2).toUpperCase();
                const foundText = data.count > 1 ? (window.I18N?.labels?.foundPlural || 'encontradas') : (window.I18N?.labels?.found || 'encontrada');
                const occurrenceText = data.count > 1 ? (window.I18N?.labels?.occurrences || 'ocorrências') : (window.I18N?.labels?.occurrence || 'ocorrência');

                htmlBuffer += `
                    <div class="occurrence-item" onclick="applyCourseFilterByName('${name.replace(/'/g, "\\'")}')" style="cursor:pointer;" title="Clique para detalhes deste setor">
                        <div class="occ-avatar">${initials}</div>
                        <div class="occ-info">
                            <span class="occ-name" style="font-weight:700;">${name}</span>
                            <span class="occ-desc" style="color:var(--primary); font-weight:600;">${data.count} ${occurrenceText} ${foundText}</span>
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

    updateKPICards();
    updatePercentagesDinamicamente();

    if (typeof applyGlobalSettings === 'function') {
        applyGlobalSettings();
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
function confirmRedirect(period) {
    pendingRedirectPeriod = period;
    const modal = document.getElementById('confirmInfractionsModal');
    if (modal) {
        modal.classList.add('active');
        document.querySelector('.main-content').style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: modal });
    }
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmInfractionsModal');
    if (modal) {
        modal.classList.remove('active');
        document.querySelector('.main-content').style.overflow = '';
    }
}

function goToInfractions() {
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
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

function isSameWeek(d1, d2) {
    const date1 = new Date(d1.getFullYear(), d1.getMonth(), d1.getDate());
    const date2 = new Date(d2.getFullYear(), d2.getMonth(), d2.getDate());
    const start1 = new Date(date1);
    start1.setDate(date1.getDate() - date1.getDay());
    const start2 = new Date(date2);
    start2.setDate(date2.getDate() - date2.getDay());
    return start1.getTime() === start2.getTime();
}

function updateKPICards() {
    let countDay = 0, countWeek = 0, countMonth = 0;
    const selMonth = selectedDate.getMonth();
    const selYear = selectedDate.getFullYear();

    // Sets para alunos únicos em cada período
    const uniqueStudentsToday = new Set();
    const uniqueStudentsWeek = new Set();
    const uniqueStudentsMonth = new Set();
    const uniqueStudentsYear = new Set();

    allOccurrences.forEach(item => {
        const dbDateString = item.full_date || item.data_hora || item.date;
        if (!dbDateString) return;
        const itemDate = new Date(dbDateString.replace(/-/g, '/'));
        if (isNaN(itemDate.getTime())) return;

        // Coleta dados para os KPIs tradicionais (contagem)
        if (isSameDay(selectedDate, itemDate)) {
            countDay++;
            if (item.aluno_id || item.student_id) uniqueStudentsToday.add(item.aluno_id || item.student_id);
        }
        if (isSameWeek(selectedDate, itemDate)) {
            countWeek++;
            if (item.aluno_id || item.student_id) uniqueStudentsWeek.add(item.aluno_id || item.student_id);
        }
        if (itemDate.getMonth() === selMonth && itemDate.getFullYear() === selYear) {
            countMonth++;
            if (item.aluno_id || item.student_id) uniqueStudentsMonth.add(item.aluno_id || item.student_id);
        }
        if (itemDate.getFullYear() === selYear) {
            if (item.aluno_id || item.student_id) uniqueStudentsYear.add(item.aluno_id || item.student_id);
        }
    });

    // Atualiza elementos visuais básicaos
    const elDia = document.getElementById('kpiDia');
    const elSemana = document.getElementById('kpiSemana');
    const elMes = document.getElementById('kpiMes');
    const elMedia = document.getElementById('kpiMedia');

    if (elDia) elDia.innerText = countDay;
    if (elSemana) elSemana.innerText = countWeek;
    if (elMes) elMes.innerText = countMonth;

    const total = window.totalStudents || 20;

    // --- Atualização do Modal de Conformidade ---
    const periods = {
        'hoje': uniqueStudentsToday.size,
        'semana': uniqueStudentsWeek.size,
        'mes': uniqueStudentsMonth.size,
        'anual': uniqueStudentsYear.size
    };

    // --- Configuração do Card de Conformidade Dinâmico (Card 4) ---
    if (elMedia) {
        let infraCount = periods[selectedCompliancePeriod] || 0;
        let periodLabel = '';

        if (selectedCompliancePeriod === 'hoje') {
            periodLabel = (window.I18N?.labels?.daily || 'DIÁRIA').toUpperCase();
        } else if (selectedCompliancePeriod === 'semana') {
            periodLabel = (window.I18N?.labels?.weekly || 'SEMANAL').toUpperCase();
        } else if (selectedCompliancePeriod === 'mes') {
            periodLabel = (window.I18N?.labels?.monthly || 'MENSAL').toUpperCase();
        } else if (selectedCompliancePeriod === 'anual') {
            periodLabel = (window.I18N?.labels?.annual || 'ANUAL').toUpperCase();
        }

        const conformidade = Math.max(0, Math.round(((total - infraCount) / total) * 100));
        elMedia.innerText = `${conformidade}%`;

        const header = document.getElementById('complianceHeader');
        if (header) {
            const conformityLabel = window.I18N?.labels?.conformity || 'CONFORMIDADE';
            header.innerText = `${conformityLabel.toUpperCase()} (${periodLabel})`;
        }

        updateConformityStatus(conformidade);
    }

    // Tornar cards clicáveis (Infrações)
    const cardHoje = document.getElementById('cardKpiHoje');
    if (cardHoje) {
        if (countDay > 0) {
            cardHoje.onclick = () => confirmRedirect('hoje');
            cardHoje.style.cursor = 'pointer';
        } else {
            cardHoje.onclick = null;
            cardHoje.style.cursor = 'default';
        }
    }

    const cardSemana = document.getElementById('cardKpiSemana');
    if (cardSemana) {
        if (countWeek > 0) {
            cardSemana.onclick = () => confirmRedirect('semana');
            cardSemana.style.cursor = 'pointer';
        } else {
            cardSemana.onclick = null;
            cardSemana.style.cursor = 'default';
        }
    }

    const cardMes = document.getElementById('cardKpiMes');
    if (cardMes) {
        if (countMonth > 0) {
            cardMes.onclick = () => confirmRedirect('mes');
            cardMes.style.cursor = 'pointer';
        } else {
            cardMes.onclick = null;
            cardMes.style.cursor = 'default';
        }
    }
}

function getStatusClass(valor) {
    if (valor < 70) return 'status-critico';
    if (valor < 85) return 'status-alto';
    if (valor < 95) return 'status-moderado';
    return 'status-controlado';
}

function updateConformityStatus(valor) {
    const card = document.getElementById('kpiMedia')?.parentElement;
    if (!card) return;

    let badge = card.querySelector('.status-badge');
    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'status-badge';
        card.appendChild(badge);
    }

    badge.className = 'status-badge ' + getStatusClass(valor);

    let label = '';
    if (valor < 70) label = window.I18N?.labels?.critical || 'CRÍTICO';
    else if (valor < 85) label = window.I18N?.labels?.high_risk || 'ALTO RISCO';
    else if (valor < 95) label = window.I18N?.labels?.moderate || 'MODERADO';
    else label = window.I18N?.labels?.controlled || 'CONTROLADO';

    badge.innerHTML = `<span class="status-dot"></span> ${label}`;
}

// --- MODAL DE CONFORMIDADE ---
function openComplianceModal() {
    const modal = document.getElementById('complianceModal');
    if (modal) {
        modal.classList.add('active');
        document.querySelector('.main-content').style.overflow = 'hidden';

        // Marca a opção ativa
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
    updateKPICards();
    closeComplianceModal();
}


// ===============================
// 3. MODAL VISUAL (SELETOR DE DATA)
// ===============================

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
    if (!jsPDFLib) return alert('Biblioteca jsPDF não carregada adequadamente.');
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
            doc.text(`EPI Guard - Gerado em ${data.generated_at}`, margin, 32);
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
            alert('Erro ao exportar PDF: ' + err.message);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}

function openDetailModal(monthIndex, monthName, epiName = '') {
    const modal = document.getElementById('detailModal');
    const title = document.getElementById('modalMonthTitle');
    const tbody = document.getElementById('modalTableBody');
    const thead = document.querySelector('.custom-table thead tr');

    if (!modal) return;
    const realMonth = monthIndex + 1;
    const currentYear = new Date().getFullYear();
    const isGlobal = (selectedSectorId === 'all');

    let displayTitle = `${monthName} de ${currentYear}`;
    if (epiName) displayTitle += ` - ${window.I18N?.labels?.filter || 'Filtro'}: ${epiName}`;
    title.innerText = displayTitle;
    modal.style.display = '';
    modal.classList.add('open');
    document.querySelector('.main-content').style.overflow = 'hidden';

    if (isGlobal) {
        thead.innerHTML = `<th>${window.I18N?.labels?.rank || 'Rank'}</th><th>${window.I18N?.labels?.course || 'Curso'}</th><th>${window.I18N?.labels?.infractions || 'Infrações'}</th><th>${window.I18N?.labels?.conformity || 'Conformidade'}</th><th>${window.I18N?.labels?.risk || 'Risco'}</th>`;
    } else {
        thead.innerHTML = `<th>${window.I18N?.labels?.date || 'Data'}</th><th>${window.I18N?.labels?.student || 'Aluno'}</th><th>${window.I18N?.labels?.infraction_epi || 'Infração (EPI)'}</th><th>${window.I18N?.labels?.time || 'Horário'}</th><th>${window.I18N?.labels?.status || 'Status'}</th>`;
    }

    let url = `${window.BASE_PATH}/api/modal_details?month=${realMonth}&year=${currentYear}&sector_id=${selectedSectorId}`;
    if (epiName) url += `&epi=${encodeURIComponent(epiName)}`;

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
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.classList.remove('open');
        document.querySelector('.main-content').style.overflow = '';
    }
}

function loadCharts() {
    // Garante que o Chart.js está disponível antes de renderizar
    if (typeof Chart === 'undefined') {
        console.warn('[epiGuard] Chart.js ainda não carregou. Tentando novamente em 300ms...');
        setTimeout(loadCharts, 300);
        return;
    }

    fetch(`${window.BASE_PATH}/api/charts?sector_id=${selectedSectorId}`)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            return res.json();
        })
        .then(response => {
            try {
                if (response.summary) {
                    window.totalStudents = response.summary.total_students;
                    const elDia = document.getElementById('kpiDia');
                    const elSemana = document.getElementById('kpiSemana');
                    const elMes = document.getElementById('kpiMes');
                    const elMedia = document.getElementById('kpiMedia');
                    if (elDia) elDia.innerText = response.summary.today;
                    if (elSemana) elSemana.innerText = response.summary.week;
                    if (elMes) elMes.innerText = response.summary.month;

                    if (elMedia && response.summary.total_students > 0) {
                        const conformidade = Math.round(((response.summary.total_students - response.summary.today) / response.summary.total_students) * 100);
                        elMedia.innerText = `${Math.max(0, conformidade)}%`;
                        updateConformityStatus(conformidade);
                    }
                }

                if (mainChartInstance) { mainChartInstance.destroy(); mainChartInstance = null; }
                if (doughnutChartInstance) { doughnutChartInstance.destroy(); doughnutChartInstance = null; }

                const epiColorsMap = response.epi_colors || {};
                const datasets = [];
                const allowedEpis = response.allowed_epis || [];
                const chartStyle = response.chart_style || 'bar';
                const isArea = chartStyle === 'area';
                const chartType = chartStyle === 'bar' ? 'bar' : 'line';

                allowedEpis.forEach(fullName => {
                    const lowerName = fullName.toLowerCase();
                    const config = { bg: epiColorsMap[fullName] || '#94a3b8', label: fullName };
                    let dataKey = null;

                    if (lowerName.includes('capacete') || lowerName.includes('helmet')) { dataKey = 'capacete'; }
                    else if (lowerName.includes('oculos') || lowerName.includes('óculos') || lowerName.includes('glasses')) { dataKey = 'oculos'; }
                    else if (lowerName.includes('jaqueta') || lowerName.includes('jacket')) { dataKey = 'jaqueta'; }
                    else if (lowerName.includes('avental') || lowerName.includes('apron')) { dataKey = 'avental'; }
                    else if (lowerName.includes('luva') || lowerName.includes('glove')) { dataKey = 'luvas'; }
                    else if (lowerName.includes('mascara') || lowerName.includes('máscara') || lowerName.includes('mask')) { dataKey = 'mascara'; }
                    else if (lowerName.includes('protetor') || lowerName.includes('protector')) { dataKey = 'protetor'; }

                    if (config && response.bar && response.bar[dataKey]) {
                        const baseColor = config.bg;
                        const bgColor = isArea ? `${baseColor}33` : baseColor; // 20% opacity for area fill

                        datasets.push({
                            label: config.label,
                            data: response.bar[dataKey],
                            backgroundColor: bgColor,
                            borderColor: baseColor,
                            borderWidth: chartType === 'line' ? 3 : 1,
                            tension: 0.4, // Smooth lines
                            fill: isArea,
                            borderRadius: chartType === 'bar' ? 4 : 0
                        });
                    }
                });

                // Always add Total
                if (response.bar && response.bar.total) {
                    const totalColor = epiColorsMap['Total'] || '#E30613';
                    const bgColor = isArea ? `${totalColor}33` : totalColor;

                    datasets.push({
                        label: window.I18N?.labels?.total || 'Total',
                        data: response.bar.total,
                        backgroundColor: bgColor,
                        borderColor: totalColor,
                        borderWidth: chartType === 'line' ? 4 : 1,
                        tension: 0.4,
                        fill: isArea,
                        borderRadius: chartType === 'bar' ? 4 : 0
                    });
                }

                // --- GRÁFICO PRINCIPAL ---
                const canvasMain = document.getElementById('mainChart');
                if (canvasMain) {
                    const ctxMain = canvasMain.getContext('2d');
                    mainChartInstance = new Chart(ctxMain, {
                        type: chartType,
                        data: { labels: monthsFull, datasets: datasets },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            tension: 0.4,
                            animation: { duration: 400, easing: 'easeOutQuart' },
                            interaction: { mode: 'index', intersect: false },
                            onClick: (evt, active, chart) => {
                                const points = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                                if (points.length > 0) {
                                    const monthIndex = points[0].index;
                                    const exactPoints = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                                    let filterEPI = '';
                                    if (exactPoints.length > 0) {
                                        const dsIdx = exactPoints[0].datasetIndex;
                                        filterEPI = chart.data.datasets[dsIdx]?.label || '';
                                    }
                                    openDetailModal(monthIndex, monthsFull[monthIndex], filterEPI);
                                }
                            },
                            plugins: {
                                legend: { labels: { usePointStyle: true, padding: 20 } }
                            },
                            scales: {
                                y: { beginAtZero: true, suggestedMax: 10, ticks: { stepSize: 1 }, grid: { display: true, color: 'rgba(0,0,0,0.05)' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                // --- GRÁFICO DOUGHNUT ---
                const isDoughnutEmpty = !response.doughnut || response.doughnut.total === 0;
                const doughnutBgColor = isDoughnutEmpty ? ['#f1f5f9'] : (response.doughnut.colors || ['#E30613']);
                const doughnutHoverColor = isDoughnutEmpty ? ['#e2e8f0'] : undefined;

                const canvasDoughnut = document.getElementById('doughnutChart');
                if (canvasDoughnut) {
                    const ctxDoughnut = canvasDoughnut.getContext('2d');
                    doughnutChartInstance = new Chart(ctxDoughnut, {
                        type: 'doughnut',
                        data: {
                            labels: isDoughnutEmpty ? [(window.I18N?.labels?.no_records_simple || 'Sem Infrações')] : response.doughnut.labels,
                            datasets: [{
                                data: isDoughnutEmpty ? [1] : response.doughnut.data,
                                backgroundColor: doughnutBgColor,
                                hoverBackgroundColor: doughnutHoverColor,
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '75%',
                            animation: { duration: 400, easing: 'easeOutQuart' },
                            onClick: (evt, active, chart) => {
                                if (active.length > 0) {
                                    const index = active[0].index;
                                    const label = chart.data.labels[index];
                                    openDetailModal(selectedDate.getMonth(), monthsFull[selectedDate.getMonth()], label);
                                }
                            }
                        }
                    });
                }

                // --- TOP OCORRÊNCIAS ---
                const topList = document.getElementById('topInfractions');
                if (topList && response.doughnut && response.doughnut.total > 0) {
                    topList.innerHTML = '';
                    const dataLabels = response.doughnut.labels;
                    const dataValues = response.doughnut.data;
                    const max = Math.max(...dataValues);
                    dataLabels.forEach((label, i) => {
                        if (dataValues[i] > 0) {
                            const pct = Math.round((dataValues[i] / max) * 100);
                            const color = response.doughnut.colors[i] || '#E30613';
                            topList.innerHTML += `
                                <div class="list-item">
                                    <span class="occ-name">${label}</span>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${pct}%; background-color: ${color};"></div>
                                    </div>
                                </div>
                            `;
                        }
                    });
                }

            } catch (jsErr) {
                console.error('[epiGuard] Erro ao renderizar gráficos:', jsErr);
            }
        })
        .catch(err => {
            console.error('[epiGuard] Falha na requisição /api/charts:', err);
        });
}

function closeModal() {
    const detailModal = document.getElementById('detailModal');
    if (detailModal) {
        detailModal.classList.remove('open');
    }
    const mc = document.querySelector('.main-content');
    if (mc) mc.style.overflow = '';
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

    let totalOntem = 0;
    let totalSemanaPassada = 0;

    allOccurrences.forEach(item => {
        const dateStr = item.full_date || item.data_hora || item.date;
        if (!dateStr) return;
        const itemDate = new Date(dateStr.replace(/-/g, '/'));
        if (isNaN(itemDate.getTime())) return;

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

// O sistema de notificações agora é gerenciado globalmente pelo notifications.js
// para evitar duplicidade e erros de sincronização.


// Desbloqueia o áudio automaticamente no primeiro clique do instrutor
document.addEventListener('click', function unlockAudio() {
    const dummyAudio = new Audio(`${window.BASE_PATH}/assets/som/notificacao.mp3`);
    dummyAudio.volume = 0; // Toca mudo só para ganhar permissão
    dummyAudio.play().then(() => {
        console.log("🔊 Sistema de áudio ativado com sucesso!");
        document.removeEventListener('click', unlockAudio);
    }).catch(e => console.error("Erro ao ativar som:", e));
}, { once: true });

// Observer para disparar atualizações de badges quando o texto dos KPIs mudar
const observer = new MutationObserver(() => {
    updatePercentagesDinamicamente();
});

const config = { childList: true, characterData: true, subtree: true };
if (document.getElementById('kpiDia')) observer.observe(document.getElementById('kpiDia'), config);
if (document.getElementById('kpiSemana')) observer.observe(document.getElementById('kpiSemana'), config);
if (document.getElementById('kpiMes')) observer.observe(document.getElementById('kpiMes'), config);

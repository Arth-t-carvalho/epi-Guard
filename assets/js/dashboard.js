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
// EXPORTAÇÃO NATIVA PARA PDF (HD)
// ===============================

/**
 * Captura um gráfico em alta resolução para exportação.
 * Renderiza o gráfico em um canvas off-screen com escala aumentada.
 */
async function captureHighResChart(chartInstance, scale = 3) {
    if (!chartInstance || !chartInstance.canvas) return null;

    try {
        const originalCanvas = chartInstance.canvas;
        const offscreenCanvas = document.createElement('canvas');
        
        offscreenCanvas.width = originalCanvas.clientWidth * scale;
        offscreenCanvas.height = originalCanvas.clientHeight * scale;
        
        const ctx = offscreenCanvas.getContext('2d');
        const config = chartInstance.config;

        // Opções simplificadas para exportação (Evita referências circulares)
        const exportOptions = {
            ...config.options, // Cópia rasa dos níveis de topo
            animation: false,
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
                ...config.options.plugins,
                legend: {
                    ...config.options.plugins?.legend,
                    display: true,
                    labels: {
                        ...config.options.plugins?.legend?.labels,
                        font: { 
                            size: 13 * scale, 
                            weight: 'bold' 
                        }
                    }
                }
            }
        };

        // Escalas (Tratando manualmente para evitar erros de clonagem)
        if (config.options.scales) {
            exportOptions.scales = {};
            Object.keys(config.options.scales).forEach(key => {
                const axis = config.options.scales[key];
                exportOptions.scales[key] = {
                    ...axis,
                    ticks: {
                        ...(axis.ticks || {}),
                        font: { size: 10 * scale, weight: 'bold' }
                    }
                };
            });
        }

        return new Promise((resolve) => {
            try {
                const tempChart = new Chart(ctx, {
                    type: config.type,
                    data: config.data,
                    options: exportOptions,
                    plugins: [{
                        beforeDraw: (chart) => {
                            const { ctx } = chart;
                            ctx.save();
                            ctx.fillStyle = 'white';
                            ctx.fillRect(0, 0, chart.width, chart.height);
                            ctx.restore();
                        }
                    }]
                });

                setTimeout(() => {
                    const dataUrl = offscreenCanvas.toDataURL('image/png', 1.0);
                    tempChart.destroy();
                    resolve(dataUrl);
                }, 150);
            } catch (innerErr) {
                console.warn('Erro na renderização temporária HD:', innerErr);
                resolve(originalCanvas.toDataURL('image/png', 1.0)); // Fallback para captura de tela normal
            }
        });
    } catch (e) {
        console.warn('Falha no preparo do gráfico HD:', e);
        return chartInstance.canvas.toDataURL('image/png', 1.0); // Fallback absoluto
    }
}

window.exportDashboardData = async function () {
    toggleScroll(true);
    const btn = document.querySelector('.btn-premium-filter');
    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando HD...';
        btn.disabled = true;
    }

    try {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            throw new Error("Biblioteca jsPDF não carregada. Pressione F5 ou verifique a conexão.");
        }

        // 1. Buscar Dados Consolidados
        const response = await fetch(`${window.BASE_PATH}/api/export/insights`);
        if (!response.ok) throw new Error(`Erro no servidor: ${response.status}`);
        
        const data = await response.json();
        if (!data || (data.status && data.status === 'error')) {
            throw new Error(data.message || "Dados do relatório indisponíveis.");
        }

        // Verificação de segurança para os gráficos
        if (!mainChartInstance || !doughnutChartInstance) {
            console.warn("Gráficos não inicializados. Tentando capturar estado atual...");
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

        // Cores "Graphic Gorilla" / Facchini Premium
        const primaryColor = [227, 6, 19];
        const textColor = [15, 23, 42];
        const mutedColor = [100, 116, 139];
        const pageW = doc.internal.pageSize.getWidth();
        const pageH = doc.internal.pageSize.getHeight();
        const margin = 14;
        const contentW = pageW - (margin * 2);

        // Helper: Cabeçalho Limpo (Branco)
        const drawCleanHeader = (title) => {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(26);
            doc.setTextColor(...textColor);
            doc.text(title, pageW / 2, 25, { align: 'center' });
            
            doc.setDrawColor(...primaryColor);
            doc.setLineWidth(1.2);
            doc.line(margin, 30, pageW - margin, 30);
            
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(...mutedColor);
            const gerado = new Date().toLocaleString('pt-BR');
            doc.text(`Relatório Analítico de Segurança EPI | Facchini S/A | Gerado em: ${gerado}`, pageW / 2, 36, { align: 'center' });
        };

        // Captura HD Otimizada (Escala 3x é o equilíbrio ideal entre HD e Memória)
        let mainChartImg, doughnutChartImg;
        try {
            mainChartImg = await captureHighResChart(mainChartInstance, 3);
            doughnutChartImg = await captureHighResChart(doughnutChartInstance, 3);
        } catch (captureErr) {
            console.warn("Falha na captura HD, tentando captura padrão...", captureErr);
            mainChartImg = mainChartInstance?.canvas?.toDataURL('image/png', 1.0);
            doughnutChartImg = doughnutChartInstance?.canvas?.toDataURL('image/png', 1.0);
        }

        // =============================================
        // PÁGINA 1: DASHBOARD EXECUTIVO (IDENTIDADE ORIGINAL)
        // =============================================
        drawCleanHeader(`Análise Anual de Segurança - ${data.year}`);

        let y = 50;

        // Bloco de Resumo (Topo)
        doc.setFontSize(12);
        doc.setTextColor(...textColor);
        doc.setFont('helvetica', 'bold');
        doc.text('Visão Geral e Insights do Período', margin, y);
        y += 6;
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...mutedColor);
        const introText = `Este relatório compila os dados de conformidade capturados pelo sistema epi-Guard. Abaixo, apresentamos a evolução mensal das infrações, permitindo identificar picos sazonais e a eficácia das medidas preventivas adotadas pela Facchini S/A.`;
        const lines = doc.splitTextToSize(introText, contentW);
        doc.text(lines, margin, y);
        y += (lines.length * 5) + 8;

        // MÉTRICA PRINCIPAL (CARRINHO / KPI)
        doc.setDrawColor(240, 240, 240);
        doc.setFillColor(252, 252, 252);
        doc.roundedRect(margin, y, contentW, 25, 2, 2, 'FD');
        
        doc.setTextColor(...primaryColor);
        doc.setFontSize(28);
        doc.setFont('helvetica', 'bold');
        doc.text(`${data.total_year || 0}`, margin + 10, y + 15);
        
        doc.setFontSize(10);
        doc.setTextColor(...textColor);
        doc.text('TOTAL DE INFRAÇÕES REGISTRADAS NO ANO', margin + 10, y + 21);
        
        y += 35;

        // GRÁFICO PRINCIPAL EM LARGURA TOTAL (Padrão Dashboard Original)
        if (mainChartImg) {
            doc.setFontSize(13);
            doc.setFont('helvetica', 'bold');
            doc.text('1. Evolução Mensal de Ocorrências (Jan - Dez)', margin, y);
            y += 6;
            
            const chartW = contentW;
            const chartH = 75; // Altura generosa para legibilidade
            doc.addImage(mainChartImg, 'PNG', margin, y, chartW, chartH, undefined, 'SLOW');
            y += chartH + 15;
        }

        // Seção de Destaques Críticos (Cards)
        doc.setFontSize(13);
        doc.setFont('helvetica', 'bold');
        doc.text('2. Pontos Críticos Identificados', margin, y);
        y += 8;

        const cardW = (contentW - 6) / 2;
        const drawMiniCard = (cx, cy, label, value) => {
            doc.setFillColor(255, 255, 255);
            doc.setDrawColor(230, 230, 230);
            doc.roundedRect(cx, cy, cardW, 18, 1, 1, 'FD');
            doc.setFontSize(8);
            doc.setTextColor(...mutedColor);
            doc.text(label.toUpperCase(), cx + 5, cy + 6);
            doc.setFontSize(11);
            doc.setTextColor(...textColor);
            doc.setFont('helvetica', 'bold');
            doc.text(value, cx + 5, cy + 13);
        };

        const worstMonth = data.worst_month?.nome || '---';
        const worstSector = data.worst_sector?.nome || '---';

        drawMiniCard(margin, y, 'Mês mais crítico', worstMonth);
        drawMiniCard(margin + cardW + 6, y, 'Setor com mais alertas', worstSector);
        y += 24;

        // =============================================
        // PÁGINA 2: DISTRIBUIÇÃO E RANKINGS
        // =============================================
        doc.addPage();
        drawCleanHeader('Distribuição e Prevenção');
        y = 50;

        // Gráfico de Rosca (Centralizado e Grande)
        if (doughnutChartImg) {
            doc.setFontSize(13);
            doc.setFont('helvetica', 'bold');
            doc.text('3. Distribuição de Infrações por Tipo de EPI', pageW / 2, y, { align: 'center' });
            y += 8;
            
            const dogSize = 90;
            const dogX = (pageW - dogSize) / 2;
            doc.addImage(doughnutChartImg, 'PNG', dogX, y, dogSize, dogSize, undefined, 'SLOW');
            y += dogSize + 15;
        }

        doc.setFontSize(13);
        doc.setFont('helvetica', 'bold');
        doc.text('4. Ranking de Conformidade por Setor', margin, y);
        y += 8;

        const fullSectorData = (data.sectors_ranking || []).map((s, i) => {
            let risk = 'CONTROLADO';
            if (s.total >= 50) risk = 'CRÍTICO';
            else if (s.total >= 20) risk = 'ALTO';
            return [i + 1, s.nome, s.total, risk];
        });

        doc.autoTable({
            startY: y,
            head: [['#', 'Setor Fabril', 'Total de Infrações', 'Status de Risco']],
            body: fullSectorData,
            theme: 'grid',
            headStyles: { fillColor: primaryColor, textColor: 255 },
            styles: { fontSize: 9 },
            alternateRowStyles: { fillColor: [248, 250, 252] }
        });

        // Rodapé Global
        const totalPages = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(160, 160, 160);
            doc.text(`Facchini S/A - Segurança do Trabalho | Documento Gerado pelo Sistema epi-Guard | Página ${i} de ${totalPages}`, pageW / 2, pageH - 10, { align: 'center' });
        }

        doc.save(`Analise_Seguranca_Facchini_${data.year}.pdf`);

        if (btn) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }

    } catch (e) {
        console.error("Erro Exportação:", e);
        const detailedError = e.message || "Erro desconhecido";
        showAlert('Erro', `Falha ao gerar relatório: ${detailedError}. Verifique sua conexão e tente novamente.`, 'error');
        if (btn) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
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
                detModal.classList.remove('active');
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
// Usa guard flag para evitar múltiplos listeners ao re-injetar o script via SPA.

if (!window._dashboardListenerBound) {
    window._dashboardListenerBound = true;
    document.addEventListener('spaPageLoaded', () => {
        // Evita dupla chamada: se o setTimeout já chamou o init, não repete via evento
        if (!window._dashboardInitCalled) {
            initDashboard();
        }
        window._dashboardInitCalled = false; // Reseta para próxima navegação
    });
}

// Se o script foi carregado com a página já pronta (hard-refresh ou carga via SPA),
// chama o init diretamente.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    // Aguarda um tick para garantir que o DOM já foi injetado pelo SPA.
    // Marca como chamado para que o listener spaPageLoaded não duplique.
    window._dashboardInitCalled = true;
    setTimeout(initDashboard, 0);
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
                // Os cards KPI (hoje/semana/mês) são atualizados EXCLUSIVAMENTE pelo loadCharts().
                // Isso evita oscilação entre os dois fetches paralelos.
            } else {
                allOccurrences = Array.isArray(data) ? data : [];
            }
            console.log('ALL OCCURRENCES SET:', allOccurrences.length);
            renderInterface(); // Atualiza apenas a lista de eventos do dia
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
                let htmlBuffer = '';
                
                // Ordenar: mais recentes primeiro (assumindo que o ID ou data_hora cresce)
                dailyData.sort((a, b) => b.id - a.id);

                dailyData.forEach(item => {
                    const statusVal = item.status || 'Pendente';
                    const translatedStatus = statusVal === 'Pendente' ? (window.I18N?.labels?.pending || 'Pendente') : (statusVal === 'Resolvido' ? (window.I18N?.labels?.resolved || 'Resolvido') : statusVal);
                    const statusClass = statusVal === 'Pendente' ? 'status-pendente' : 'status-resolvido';
                    const initials = (item.employee || '??').substring(0, 2).toUpperCase();

                    htmlBuffer += `
                        <div class="occurrence-item" onclick="redirectToInfractions('', '', '${item.id}')" style="cursor:pointer; padding: 10px 14px;">
                            <div class="occ-avatar" style="background: var(--primary-light); color: var(--primary); font-weight: 800;">${initials}</div>
                            <div class="occ-info" style="flex: 1; margin-left: 12px;">
                                <span class="occ-name" style="font-size: 13px; font-weight: 700; color: var(--secondary); display: block;">${item.employee}</span>
                                <span class="occ-desc" style="font-size: 11px; color: var(--text-muted); display: block;">${item.name || 'Setor'}</span>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                                <span class="occ-time" style="font-size: 10px; font-weight: 700; color: var(--text-muted); opacity: 0.8;">${item.time}</span>
                                <span class="status-badge ${statusClass}" style="zoom: 0.85;">${translatedStatus}</span>
                            </div>
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
        loadCharts();
    } else {
        renderInterface();
        loadCharts(); // Ensure KPIs also update when day changes within the same month
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
    loadCharts();
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
    loadCharts();
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

                // ================= PÁGINA 3: ANÁLISE GRÁFICA (FULL PAGE FOCUS) =================
                doc.addPage();
                doc.setFillColor(...primaryColor);
                doc.rect(0, 0, pageWidth, 45, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(22);
                doc.setFont(undefined, 'bold');
                doc.text('Analise Grafica de Desempenho', margin, 25);
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                doc.text('Visualize abaixo a evolucao temporal e a distribuicao por tipo de EPI.', margin, 35);

                let gCursor = 65;

                if (mainChartImg) {
                    doc.setTextColor(...darkColor);
                    doc.setFontSize(16);
                    doc.setFont(undefined, 'bold');
                    doc.text('1. Evolucao Mensal de Infracoes', margin, gCursor);
                    
                    // Aumentado significativamente para legibilidade máxima (quase metade da página)
                    const chartHeight = 85; 
                    const chartWidth = contentWidth;
                    doc.addImage(mainChartImg, 'PNG', margin, gCursor + 8, chartWidth, chartHeight, undefined, 'SLOW');
                    gCursor += chartHeight + 35;
                }

                if (doughnutChartImg && gCursor < 200) {
                    doc.setTextColor(...darkColor);
                    doc.setFontSize(16);
                    doc.setFont(undefined, 'bold');
                    doc.text('2. Distribuicao por Equipamento (EPI)', margin, gCursor);
                    
                    const dogSize = 90; // Tamanho grande para legibilidade das legendas internas
                    const dogX = margin + (contentWidth - dogSize) / 2;
                    doc.addImage(doughnutChartImg, 'PNG', dogX, gCursor + 8, dogSize, dogSize, undefined, 'SLOW');
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

    // Resetar busca do modal de detalhes
    const searchInput = document.getElementById('detailSearchInput');
    if (searchInput) {
        searchInput.value = '';
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: modal });
    }

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
        .then(response => {
            const dataArr = response.data || [];
            tbody.innerHTML = '';
            
            if (!dataArr || dataArr.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px;">${window.I18N?.labels?.no_records_found || 'Nenhum registro encontrado.'}</td></tr>`;
                return;
            }

            // Sempre mostrar lista de funcionários (infrações) quando clicado em gráficos
            // Independente de ser Global ou não, pois o usuário quer ver os nomes.
            thead.innerHTML = `
                <th>${window.I18N?.labels?.date || 'Data'}</th>
                <th>${window.I18N?.labels?.student || 'Aluno'}</th>
                <th>${window.I18N?.labels?.infraction_epi || 'Infração (EPI)'}</th>
                <th>${window.I18N?.labels?.time || 'Horário'}</th>
                <th>${window.I18N?.labels?.status || 'Status'}</th>
            `;

            dataArr.forEach(row => {
                const statusTexto = row.status_formatado || row.status || 'Pendente';
                const translatedStatus = statusTexto === 'Pendente' ? (window.I18N?.labels?.pending || 'Pendente') : (statusTexto === 'Resolvido' ? (window.I18N?.labels?.resolved || 'Resolvido') : statusTexto);
                let classeStatus = statusTexto === 'Pendente' ? 'status-pendente' : 'status-resolvido';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${row.data}</td>
                        <td style="font-weight:600; color: var(--primary);">${row.aluno}</td>
                        <td>${row.epis}</td>
                        <td>${row.hora}</td>
                        <td><span class="status-badge ${classeStatus}">${translatedStatus}</span></td>
                    </tr>`;
            });
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center">${window.I18N?.labels?.connection_error || 'Erro na conexão.'}</td></tr>`;
        });
}

function closeModal() {
    const modals = document.querySelectorAll('.modal-premium, .modal-calendar, .modal-overlay-calendar, #detailModal');
    modals.forEach(m => m.classList.remove('active'));
    document.querySelector('.main-content').style.overflow = '';
}

/**
 * Filtra a tabela do modal de detalhes do gráfico (Dashboard)
 */
function filterDetailModalTable(query) {
    const term = query.toLowerCase().trim();
    const rows = document.querySelectorAll('#modalTableBody tr');
    
    rows.forEach(row => {
        // Pega todo o texto das células da linha para buscar o nome do funcionário ou setor
        const searchableText = row.textContent.toLowerCase();
        
        if (searchableText.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function loadCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(loadCharts, 300);
        return;
    }

    const y = selectedDate.getFullYear();
    const m = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const d = String(selectedDate.getDate()).padStart(2, '0');
    const refDateStr = `${y}-${m}-${d}`;

    fetch(`${window.BASE_PATH}/api/charts?sector_id=${selectedSectorId}&ref_date=${refDateStr}`)
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
                            onClick: (evt, elements, chart) => {
                                // Tenta primeiro pegar o elemento exato clicado (barra ou bolhinha)
                                const activeElements = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                                
                                // Tenta pegar a categoria (mês) clicada
                                const categoryElements = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                                
                                if (categoryElements.length > 0) {
                                    const monthIndex = categoryElements[0].index;
                                    let filterEPI = '';
                                    
                                    // Se clicou exatamente em uma barra/ponto, filtra por aquele EPI
                                    if (activeElements.length > 0) {
                                        const datasetIndex = activeElements[0].datasetIndex;
                                        const label = chart.data.datasets[datasetIndex]?.label || '';
                                        if (label !== 'Total' && label !== (window.I18N?.labels?.total || 'Total')) {
                                            filterEPI = label;
                                        }
                                    }
                                    
                                    openDetailModal(monthIndex, monthsFull[monthIndex], filterEPI);
                                }
                            },
                            onHover: (evt, active, chart) => {
                                // Muda o cursor se estiver sobre um elemento clicável (barra ou ponto)
                                // Usamos 'index' mode para o hover também para indicar que a coluna inteira é interativa
                                const hasItems = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, true).length > 0;
                                evt.native.target.style.cursor = hasItems ? 'pointer' : 'default';
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
                            },
                            onHover: (evt, active, chart) => {
                                evt.native.target.style.cursor = active.length > 0 ? 'pointer' : 'default';
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

/**
 * Calcula a variação percentual entre dois valores e retorna os metadados da tendência.
 */
function calculateTrend(current, previous) {
    let percent = 0;
    if (previous === 0) {
        percent = current > 0 ? 100 : 0;
    } else {
        percent = Math.round(((current - previous) / previous) * 100);
    }

    const absPercent = Math.abs(percent);
    let level = 'stable';
    if (absPercent > 50) level = 'critical';
    else if (absPercent > 25) level = 'warning';

    let direction = 'neutral';
    if (percent > 0) direction = 'up';
    else if (percent < 0) direction = 'down';

    return { percent, absPercent, direction, level };
}

function renderStateBadge(conformityValue, elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;

    let level = 'controlado';
    let label = window.I18N?.labels?.controlled || 'CONTROLADO';

    if (conformityValue < 70) {
        level = 'critico';
        label = window.I18N?.labels?.critical || 'CRÍTICO';
    } else if (conformityValue < 85) {
        level = 'alto';
        label = window.I18N?.labels?.high_risk || 'ALTO';
    } else if (conformityValue < 95) {
        level = 'moderado';
        label = window.I18N?.labels?.moderate || 'MODERADO';
    }

    el.className = `kpi-state-badge trend-${level}`;
    el.style.display = 'flex';
    el.innerHTML = `<span class="status-dot"></span> ${label}`;
}

function renderTrendBadge(currentValue, previousValue, elementId, periodLabel) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const trend = calculateTrend(currentValue, previousValue);
    const { percent, absPercent, direction, level } = trend;
    
    // Configuração visual
    let icon = 'minus';
    let arrow = '—';
    if (direction === 'up') { icon = 'arrow-up'; arrow = '↑'; }
    if (direction === 'down') { icon = 'arrow-down'; arrow = '↓'; }

    // Atualiza classes e conteúdo
    el.className = `kpi-trend-badge trend-${level} kpi-trend-badge-update`;
    
    // Tooltip nativo (UX Requirement)
    const trendText = direction === 'up' ? 'Aumentou' : (direction === 'down' ? 'Diminuiu' : 'Estável');
    el.title = `${trendText} ${absPercent}% em relação a ${periodLabel}`;

    el.style.display = 'flex';
    el.innerHTML = `<span>${arrow}</span> <span>${absPercent}%</span>`;
    
    // Remove a classe de animação após a execução para permitir re-trigger
    setTimeout(() => el.classList.remove('kpi-trend-badge-update'), 500);
}

/**
 * Função central para atualizar todos os elementos de KPI no topo do dashboard.
 */
function updateKPIElements(summary) {
    if (!summary) return;
    window.totalStudents = summary.total_students || 20;

    const kpiConfig = [
        { id: 'kpiDia', val: summary.today, prev: summary.yesterday, trendId: 'trendKpiDia', stateId: 'stateKpiDia', students: summary.students_today, label: 'ontem' },
        { id: 'kpiSemana', val: summary.week, prev: summary.last_week, trendId: 'trendKpiSemana', stateId: 'stateKpiSemana', students: summary.students_week, label: 'semana passada' },
        { id: 'kpiMes', val: summary.month, prev: summary.last_month, trendId: 'trendKpiMes', stateId: 'stateKpiMes', students: summary.students_month, label: 'mês passado' }
    ];

    kpiConfig.forEach(item => {
        const el = document.getElementById(item.id);
        if (el) {
            const oldVal = el.innerText;
            if (oldVal !== String(item.val)) {
                el.innerText = item.val ?? 0;
                el.parentElement.classList.add('kpi-value-update');
                setTimeout(() => el.parentElement.classList.remove('kpi-value-update'), 600);
            }
        }
        renderTrendBadge(item.val ?? 0, item.prev ?? 0, item.trendId, item.label);
        
        // Calcular conformidade para o estado
        if (window.totalStudents > 0) {
            const conf = Math.max(0, Math.round(((window.totalStudents - (item.students || 0)) / window.totalStudents) * 100));
            renderStateBadge(conf, item.stateId);
        }
    });

    const elMedia = document.getElementById('kpiMedia');
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
        
        // Calcular conformidade anterior para tendência
        let prevInfraCount = 0;
        let prevPeriodLabel = '';
        if (selectedCompliancePeriod === 'hoje') {
            prevInfraCount = summary.previous_students_today || 0;
            prevPeriodLabel = 'ontem';
        } else if (selectedCompliancePeriod === 'semana') {
            prevInfraCount = summary.previous_students_week || 0;
            prevPeriodLabel = 'semana passada';
        } else if (selectedCompliancePeriod === 'mes') {
            prevInfraCount = summary.previous_students_month || 0;
            prevPeriodLabel = 'mês passado';
        }

        const prevConformidade = Math.max(0, Math.round(((window.totalStudents - prevInfraCount) / window.totalStudents) * 100));
        renderTrendBadge(conformidade, prevConformidade, 'trendKpiConformidade', prevPeriodLabel);
        renderStateBadge(conformidade, 'stateKpiConformidade');

        if (elMedia.innerText !== `${conformidade}%`) {
            elMedia.innerText = `${conformidade}%`;
            elMedia.parentElement.classList.add('kpi-value-update');
            setTimeout(() => elMedia.parentElement.classList.remove('kpi-value-update'), 600);
        }

        const header = document.getElementById('complianceHeader');
        if (header) {
            const conformityLabel = window.I18N?.labels?.conformity || 'CONFORMIDADE';
            header.innerText = `${conformityLabel.toUpperCase()} (${periodLabel})`;
        }
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

window.redirectToInfractions = function (sectorId, sectorName, occurrenceId = null, employeeId = null) {
    const y = selectedDate.getFullYear();
    const m = String(selectedDate.getMonth() + 1).padStart(2, '0');
    const d = String(selectedDate.getDate()).padStart(2, '0');
    const dStr = `${y}-${m}-${d}`;

    let url = `${window.BASE_PATH}/infractions?periodo=personalizado&date_from=${dStr}&date_to=${dStr}`;
    
    if (sectorId && sectorId !== 'all') url += `&setor_id=${sectorId}`;
    if (employeeId) url += `&funcionario_id=${employeeId}`;
    if (occurrenceId) url += `&highlight=${occurrenceId}`;
    
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
// Removido pois agora as tendências vêm diretamente do servidor no updateKPIElements.

/**
 * =========================================
 * DETECÇÃO E ESCALONAMENTO DE FULLSCREEN (F11)
 * =========================================
 */
function handleFullscreenScaling() {
    const isFullscreen = window.innerHeight >= (screen.height - 10);
    const body = document.body;
    
    if (isFullscreen) {
        if (!body.classList.contains('is-fullscreen')) {
            body.classList.add('is-fullscreen');
            // Re-renderizar gráficos para ajustar ao novo zoom
            if (typeof mainChart !== 'undefined') mainChart.resize();
            if (typeof doughnutChart !== 'undefined') doughnutChart.resize();
        }
    } else {
        if (body.classList.contains('is-fullscreen')) {
            body.classList.remove('is-fullscreen');
             if (typeof mainChart !== 'undefined') mainChart.resize();
             if (typeof doughnutChart !== 'undefined') doughnutChart.resize();
        }
    }
}

// Escutar mudanças de redimensionamento (F11 altera altura da janela)
window.addEventListener('resize', handleFullscreenScaling);
// Chamar uma vez no carregamento
document.addEventListener('DOMContentLoaded', handleFullscreenScaling);
// Loop de verificação (opcional, para garantir detecção em navegadores teimosos)
setInterval(handleFullscreenScaling, 2000);

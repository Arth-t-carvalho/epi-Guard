// =============================================================
// DASHBOARD.JS - VERSÃO UNIFICADA (SUA LÓGICA + CALENDÁRIO VISUAL)
// =============================================================

// --- VARIÁVEIS GLOBAIS ---
let selectedDate = new Date(); // Data usada no Dashboard
let currCalYear = new Date().getFullYear(); // Ano visualizado no Modal de Escolha de Data
let currCalMonth = new Date().getMonth();   // Mês visualizado no Modal de Escolha de Data
let allOccurrences = []; // Dados do BD
let mainChartInstance = null;
let doughnutChartInstance = null;
let selectedCourseId = 'all'; // Legado, mantido para compatibilidade de funções
let selectedSectorId = 'all'; // Novo: Filtro de setor para visão empresarial

// Cores para os gráficos
const colorHelmet = '#1F2937';
const colorGlasses = '#9CA3AF';
const colorAll = '#E30613';

// Arrays auxiliares
const monthsFull = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

// --- INICIALIZAÇÃO ---
document.addEventListener("DOMContentLoaded", function () {
    loadCalendarData(); // Carrega dados da API
    loadCharts();       // Carrega Gráficos

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

    // Fechar modais ao clicar fora
    document.addEventListener('click', (e) => {
        // Modal Calendário
        const calModal = document.getElementById('calendarModal');
        if (calModal && e.target === calModal) toggleCalendar();

        // Modal Detalhes (Gráfico)
        const detModal = document.getElementById('detailModal');
        if (detModal && e.target === detModal) detModal.classList.remove('open');

        // Card Instrutor
        const card = document.getElementById('instructorCard');
        const trigger = document.getElementById('profileTrigger');
        if (card && trigger && !card.contains(e.target) && !trigger.contains(e.target)) {
            card.classList.remove('active');
        }
    });

    // Re-render markers if lucide is available
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

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
            // Modo Empresarial: Todos vêem o agrupamento por SETOR
            const grouped = {};
            dailyData.forEach(item => {
                const key = item.name || 'Desconhecido';
                if (!grouped[key]) grouped[key] = { count: 0, items: [] };
                grouped[key].count++;
                grouped[key].items.push(item);
            });

            Object.keys(grouped).forEach(name => {
                const data = grouped[name];
                const initials = name.substring(0, 2).toUpperCase();

                // Em modo Empresarial, a ação padrão é abrir o detalhamento do curso/setor
                list.innerHTML += `
                    <div class="occurrence-item" onclick="applyCourseFilterByName('${name.replace(/'/g, "\\'")}')" style="cursor:pointer;" title="Clique para detalhes deste setor">
                        <div class="occ-avatar">${initials}</div>
                        <div class="occ-info">
                            <span class="occ-name" style="font-weight:700;">${name}</span>
                            <span class="occ-desc" style="color:var(--primary); font-weight:600;">${data.count} ocorrência${data.count > 1 ? 's' : ''} encontrada${data.count > 1 ? 's' : ''}</span>
                        </div>
                        <div class="occ-time">❯</div>
                    </div>`;
            });
        } else {
            list.innerHTML = '';
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
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: modal });
        updateSelectionUI(); // Sincroniza checks com o estado
    }
}

function closeCourseModal() {
    const modal = document.getElementById('courseModal');
    if (modal) modal.classList.remove('active');
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
    const uniqueStudentsToday = new Set();

    allOccurrences.forEach(item => {
        const dbDateString = item.full_date || item.data_hora || item.date;
        const itemDate = new Date(dbDateString.replace(/-/g, '/'));

        if (isSameDay(selectedDate, itemDate)) {
            countDay++;
            if (item.aluno_id || item.student_id) {
                uniqueStudentsToday.add(item.aluno_id || item.student_id);
            }
        }
        if (isSameWeek(selectedDate, itemDate)) countWeek++;
        if (itemDate.getMonth() === selMonth && itemDate.getFullYear() === selYear) countMonth++;
    });

    const elDia = document.getElementById('kpiDia');
    const elSemana = document.getElementById('kpiSemana');
    const elMes = document.getElementById('kpiMes');
    const elMedia = document.getElementById('kpiMedia');

    if (elDia) elDia.innerText = countDay;
    if (elSemana) elSemana.innerText = countWeek;
    if (elMes) elMes.innerText = countMonth;

    if (elMedia) {
        const total = window.totalStudents || 20;
        const infra = uniqueStudentsToday.size;
        const conformidade = Math.round(((total - infra) / total) * 100);
        elMedia.innerText = `${Math.max(0, conformidade)}%`;
        updateConformityStatus(conformidade);
    }
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

    if (valor < 70) {
        badge.className = 'status-badge status-critico';
        badge.innerText = '🚨 CRÍTICO';
    } else if (valor < 85) {
        badge.className = 'status-badge status-alto';
        badge.innerText = '🟠 ALTO RISCO';
    } else if (valor < 95) {
        badge.className = 'status-badge status-moderado';
        badge.innerText = '🟡 MODERADO';
    } else {
        badge.className = 'status-badge status-baixo';
        badge.innerText = '🟢 CONTROLADO';
    }
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
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } else {
        modal.classList.remove('active');
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
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const btn = document.querySelector('.btn-export');
    if (!btn) return;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = 'Gerando PDF...';
    btn.style.color = '#E30613';

    doc.setFontSize(18);
    doc.text("Relatório de Ocorrências - EPI Guard", 14, 20);
    doc.setFontSize(11);
    doc.setTextColor(100);
    doc.text(`Data de Geração: ${new Date().toLocaleDateString()}`, 14, 30);

    const currentMonth = selectedDate.getMonth() + 1;
    const currentYear = selectedDate.getFullYear();

    fetch(`${window.BASE_PATH}/api/modal_details?month=${currentMonth}&year=${currentYear}`)
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) {
                alert("Nenhuma ocorrência encontrada para exportar neste mês.");
                btn.innerHTML = originalHTML;
                btn.style.color = '';
                return;
            }
            const head = [['Data', 'Aluno', 'EPI', 'Hora', 'Status']];
            const body = data.map(row => [row.data, row.aluno, row.epis, row.hora, row.status_formatado]);
            doc.autoTable({
                startY: 35,
                head: head,
                body: body,
                theme: 'striped',
                headStyles: { fillColor: [227, 6, 19] }
            });
            doc.save(`relatorio_epi_${currentMonth}_${currentYear}.pdf`);
            btn.innerHTML = originalHTML;
            btn.style.color = '';
        })
        .catch(err => {
            console.error(err);
            alert("Erro ao gerar PDF.");
            btn.innerHTML = originalHTML;
            btn.style.color = '';
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
    if (epiName) displayTitle += ` - Filtro: ${epiName}`;
    title.innerText = displayTitle;
    modal.style.display = '';
    modal.classList.add('open');

    if (isGlobal) {
        thead.innerHTML = `<th>Rank</th><th>Curso</th><th>Infrações</th><th>Conformidade</th><th>Risco</th>`;
    } else {
        thead.innerHTML = `<th>Data</th><th>Aluno</th><th>Infração (EPI)</th><th>Horário</th><th>Status</th>`;
    }

    let url = `${window.BASE_PATH}/api/modal_details?month=${realMonth}&year=${currentYear}&sector_id=${selectedSectorId}`;
    if (epiName) url += `&epi=${encodeURIComponent(epiName)}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 20px;">Nenhum registro encontrado.</td></tr>`;
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
                    let classeStatus = statusTexto === 'Pendente' ? 'status-pendente' : 'status-resolvido';
                    tbody.innerHTML += `
                        <tr>
                            <td>${row.data}</td>
                            <td style="font-weight:500;">${row.aluno}</td>
                            <td>${row.epis}</td>
                            <td>${row.hora}</td>
                            <td><span class="status-badge ${classeStatus}">${statusTexto}</span></td>
                        </tr>`;
                });
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center">Erro na conexão.</td></tr>`;
        });
}

function loadCharts() {
    fetch(`${window.BASE_PATH}/api/charts?sector_id=${selectedSectorId}`)
        .then(res => res.json())
        .then(response => {
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

            if (mainChartInstance) mainChartInstance.destroy();
            if (doughnutChartInstance) doughnutChartInstance.destroy();

            const epiColors = {
                'capacete': { bg: '#1F2937', label: 'Capacete' },
                'oculos': { bg: '#9CA3AF', label: 'Óculos' },
                'óculos': { bg: '#9CA3AF', label: 'Óculos' },
                'jaqueta': { bg: '#f59e0b', label: 'Jaqueta' },
                'avental': { bg: '#3b82f6', label: 'Avental' },
                'luvas': { bg: '#10b981', label: 'Luvas' },
                'luva': { bg: '#10b981', label: 'Luvas' },
                'mascara': { bg: '#6366f1', label: 'Máscara' },
                'máscara': { bg: '#6366f1', label: 'Máscara' },
                'protetor': { bg: '#ec4899', label: 'Protetor' }
            };

            const datasets = [];
            const allowedEpis = response.allowed_epis || [];
            
            // Build datasets dynamically based on allowed EPIs
            allowedEpis.forEach(fullName => {
                const lowerName = fullName.toLowerCase();
                let config = null;
                let dataKey = null;

                if (lowerName.includes('capacete')) { config = epiColors['capacete']; dataKey = 'capacete'; }
                else if (lowerName.includes('oculos') || lowerName.includes('óculos')) { config = epiColors['oculos']; dataKey = 'oculos'; }
                else if (lowerName.includes('jaqueta')) { config = epiColors['jaqueta']; dataKey = 'jaqueta'; }
                else if (lowerName.includes('avental')) { config = epiColors['avental']; dataKey = 'avental'; }
                else if (lowerName.includes('luva')) { config = epiColors['luvas']; dataKey = 'luvas'; }
                else if (lowerName.includes('mascara') || lowerName.includes('máscara')) { config = epiColors['mascara']; dataKey = 'mascara'; }
                else if (lowerName.includes('protetor')) { config = epiColors['protetor']; dataKey = 'protetor'; }
                
                if (config && response.bar[dataKey]) {
                    datasets.push({
                        label: config.label,
                        data: response.bar[dataKey],
                        backgroundColor: config.bg,
                        borderColor: config.bg,
                        borderRadius: 4
                    });
                }
            });

            // Always add Total
            datasets.push({
                label: 'Total',
                data: response.bar.total,
                backgroundColor: '#E30613',
                borderColor: '#E30613',
                borderRadius: 4
            });

            const ctxMain = document.getElementById('mainChart').getContext('2d');
            mainChartInstance = new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: monthsFull,
                    datasets: datasets
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    onClick: (evt, active, chart) => {
                        const points = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                        if (points.length > 0) {
                            const monthIndex = points[0].index;
                            const exactPoints = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                            let filterEPI = '';
                            if (exactPoints.length > 0) {
                                const datasetIndex = exactPoints[0].datasetIndex;
                                const label = chart.data.datasets[datasetIndex].label;
                                filterEPI = label === 'Total' ? '' : label;
                            }
                            openDetailModal(monthIndex, monthsFull[monthIndex], filterEPI);
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 10,
                            ticks: { stepSize: 1 },
                            grid: { display: true, color: 'rgba(0,0,0,0.05)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            const isDoughnutEmpty = response.doughnut.total === 0;
            const doughnutBgColor = isDoughnutEmpty ? ['#f1f5f9'] : [colorHelmet, colorGlasses, colorAll, '#f59e0b', '#3b82f6'];
            const doughnutHoverColor = isDoughnutEmpty ? ['#e2e8f0'] : undefined;

            const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
            doughnutChartInstance = new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: isDoughnutEmpty ? ['Sem Infrações'] : response.doughnut.labels,
                    datasets: [{
                        data: isDoughnutEmpty ? [1] : response.doughnut.data,
                        backgroundColor: doughnutBgColor,
                        hoverBackgroundColor: doughnutHoverColor,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '75%',
                    onClick: (evt, active, chart) => {
                        if (active.length > 0) {
                            const index = active[0].index;
                            const label = chart.data.labels[index];
                            openDetailModal(selectedDate.getMonth(), monthsFull[selectedDate.getMonth()], label);
                        }
                    }
                }
            });

            // Atualiza TOP OCORRÊNCIAS baseado no Doughnut Chart
            const topList = document.getElementById('topInfractions');
            if (topList) {
                topList.innerHTML = '';
                if (response.doughnut && response.doughnut.total > 0) {
                    const dataLabels = response.doughnut.labels;
                    const dataValues = response.doughnut.data;
                    const max = Math.max(...dataValues);

                    dataLabels.forEach((label, i) => {
                        if (dataValues[i] > 0) {
                            const pct = Math.round((dataValues[i] / max) * 100);
                            topList.innerHTML += `
                                <div class="list-item">
                                    <span class="occ-name">${label}</span>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${pct}%;"></div>
                                    </div>
                                </div>
                            `;
                        }
                    });
                }
            }

        })
        .catch(err => {
            console.error('Erro gráficos:', err);
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

    let totalOntem = 0;
    let totalSemanaPassada = 0;

    allOccurrences.forEach(item => {
        const itemDate = new Date((item.full_date || item.data_hora || item.date).replace(/-/g, '/'));
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

// =========================================================
// SISTEMA DE NOTIFICAÇÕES EM TEMPO REAL
// =========================================================

let ultimoIdNotificacao = 0;

function mostrarNotificacao(aluno, epi) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    const audio = new Audio(`${window.BASE_PATH}/assets/som/notificacao.mp3`);
    audio.play().catch(() => { });

    const agora = new Date();
    const horario = agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
        <div class="toast-icon"><i data-lucide="alert-triangle"></i></div>
        <div class="toast-content">
            <div class="toast-title">Infração Detectada</div>
            <div class="toast-message"><b>${aluno}</b> • Sem ${epi}</div>
            <span class="toast-time">${horario}</span>
        </div>
    `;
    container.appendChild(toast);
    if (typeof lucide !== 'undefined') lucide.createIcons({ root: toast });
    setTimeout(() => { toast.remove(); }, 5000);
}

function verificarNovasOcorrencias() {
    fetch(`${window.BASE_PATH}/api/check_notificacoes?last_id=${ultimoIdNotificacao}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'init') {
                ultimoIdNotificacao = data.last_id;
                return;
            }
            if (data.status === 'success' && data.dados.length > 0) {
                data.dados.forEach(ocorrencia => {
                    if (ocorrencia.id > ultimoIdNotificacao) {
                        mostrarNotificacao(ocorrencia.aluno, ocorrencia.epi_nome);
                        ultimoIdNotificacao = ocorrencia.id;
                    }
                });
                loadCalendarData();
            }
        })
        .catch(err => console.error("Erro:", err));
}

setInterval(verificarNovasOcorrencias, 5000);
verificarNovasOcorrencias();

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

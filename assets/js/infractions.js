/**
 * INFRACTIONS.JS
 * Lógica do modal de exportação: seleção de setor, pesquisa de funcionários e exportação
 */

let allLoadedEmployees = []; // Cache da lista completa para filtrar localmente

document.addEventListener('DOMContentLoaded', initInfractionsSearch);
window.addEventListener('spaPageLoaded', initInfractionsSearch);

function initInfractionsSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    // Prevenir múltiplos listeners se o script for re-executado
    if (searchInput._hasSearchListener) return;
    searchInput._hasSearchListener = true;

    let debounceTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const form = document.getElementById('filterForm');
            if (!form) return;

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = window.location.pathname + '?' + params.toString();

            if (typeof navigateViaSPA === 'function') {
                navigateViaSPA(url, { silent: true, replaceState: true });
            } else {
                form.submit();
            }
        }, 300);
    });

    // Manter o foco no input após a atualização do SPA (já que o innerHTML é resetado)
    // Se o searchInput foi o gatilho, ele será recriado. 
    const urlParams = new URLSearchParams(window.location.search);
    const hasSearchValue = urlParams.has('search') && urlParams.get('search').length > 0;
    
    // Só foca se houver valor de busca e não for um clique vindo do sidebar (que não teria o foco no input)
    if (hasSearchValue && document.activeElement.tagName === 'BODY') {
        searchInput.focus();
        // Colocar o cursor no final do texto
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const exportModal = document.getElementById('exportModal');
    if (exportModal) {
        exportModal.addEventListener('click', (e) => {
            if (e.target === exportModal) closeExportModal();
        });
    }
});

/* ==============================
   MODAL OPEN / CLOSE
   ============================== */

function openExportModal() {
    const modal = document.getElementById('exportModal');
    if (!modal) return;

    modal.classList.add('active');
    toggleScroll(true);

    // Reset completo ao abrir
    const select = document.getElementById('exportSectorSelect');
    if (select) select.selectedIndex = 0;

    // Estado Inicial: Nenhum setor selecionado
    const listContainer = document.getElementById('exportEmployeeList');
    listContainer.innerHTML = `
        <div class="employee-empty info">
            <i class="fa-solid fa-circle-info"></i>
            <span>${window.I18N?.labels?.no_sector_selected || 'Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.'}</span>
        </div>
    `;

    document.getElementById('formatStep').style.display = 'none';

    // Desativar controles de busca e seleção
    const searchInput = document.getElementById('employeeSearchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.disabled = true;
    }

    const selectAllCheck = document.getElementById('selectAllEmployees');
    if (selectAllCheck) {
        selectAllCheck.checked = false;
        selectAllCheck.disabled = true;
    }

    allLoadedEmployees = [];
    updateSelectedCount();
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('active');
        toggleScroll(false);
    }
}

/* ==============================
   SELEÇÃO DE SETOR (via <select>)
   ============================== */

function onSectorSelectChange(selectEl) {
    const sectorId = selectEl.value;
    if (!sectorId) return;
    loadEmployeesForExport(sectorId);
}

/* ==============================
   CARREGAMENTO DE FUNCIONÁRIOS
   ============================== */

async function loadEmployeesForExport(sectorId) {
    const listContainer = document.getElementById('exportEmployeeList');
    const formatStep = document.getElementById('formatStep');
    const searchInput = document.getElementById('employeeSearchInput');
    const selectAllCheck = document.getElementById('selectAllEmployees');

    // Loading state
    listContainer.innerHTML = `
        <div class="employee-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> ${window.I18N?.labels?.loading_employees || 'Carregando funcionários...'}
        </div>
    `;
    formatStep.style.display = 'none';

    // Preparar controles
    if (searchInput) {
        searchInput.value = '';
        searchInput.disabled = true;
    }
    if (selectAllCheck) {
        selectAllCheck.disabled = true;
    }

    try {
        const response = await fetch(`${window.BASE_PATH}/api/departments/employees?id=${sectorId}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            allLoadedEmployees = result.data;
            renderEmployeeList(allLoadedEmployees);
            formatStep.style.display = 'block';
            if (searchInput) searchInput.disabled = false;
            if (selectAllCheck) {
                selectAllCheck.disabled = false;
                selectAllCheck.checked = true;
            }
        } else {
            allLoadedEmployees = [];
            listContainer.innerHTML = `
                <div class="employee-empty">
                    <i class="fa-solid fa-user-slash"></i>
                    <span>${window.I18N?.labels?.no_employees_found || 'Nenhum funcionário encontrado neste setor.'}</span>
                </div>
            `;
            formatStep.style.display = 'none';
        }
        updateSelectedCount();
    } catch (error) {
        console.error('Erro ao carregar funcionários:', error);
        listContainer.innerHTML = `
            <div class="employee-empty error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>${window.I18N?.labels?.load_error || 'Erro ao carregar dados. Tente novamente.'}</span>
            </div>
        `;
    }
}

/**
 * Renderiza a lista de funcionários com checkboxes
 */
function renderEmployeeList(employees) {
    const listContainer = document.getElementById('exportEmployeeList');
    listContainer.innerHTML = '';

    employees.forEach((emp) => {
        const id = emp.id;
        const name = emp.nome;
        const domId = `emp_export_${id}`;
        
        const item = document.createElement('label');
        item.className = 'employee-check-item';
        item.setAttribute('data-name', name.toLowerCase());
        item.innerHTML = `
            <input type="checkbox" class="export-employee-check" value="${id}" id="${domId}" checked onchange="updateSelectedCount()">
            <span class="employee-check-custom"></span>
            <span class="employee-check-name">${name}</span>
        `;
        listContainer.appendChild(item);
    });
}

/* ==============================
   PESQUISA DE FUNCIONÁRIOS
   ============================== */

function filterExportEmployees(query) {
    const filter = query.toLowerCase().trim();
    const items = document.querySelectorAll('.employee-check-item');

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        if (name.includes(filter)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

/* ==============================
   SELECIONAR TODOS / CONTADOR
   ============================== */

function toggleAllExportEmployees(checked) {
    const checks = document.querySelectorAll('.export-employee-check');
    checks.forEach(c => {
        // Só altera os visíveis (respeitando o filtro de pesquisa)
        if (c.closest('.employee-check-item').style.display !== 'none') {
            c.checked = checked;
        }
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const total = document.querySelectorAll('.export-employee-check').length;
    const checked = document.querySelectorAll('.export-employee-check:checked').length;
    const el = document.getElementById('selectedCount');
    if (el) {
        el.textContent = `${checked} ${window.I18N?.labels?.of || 'de'} ${total} ${window.I18N?.labels?.selected || 'selecionados'}`;
    }

    // Sincronizar estado do "Selecionar Todos"
    const selectAll = document.getElementById('selectAllEmployees');
    if (selectAll) {
        selectAll.checked = (checked === total && total > 0);
        selectAll.indeterminate = (checked > 0 && checked < total);
    }
}

/* ==============================
   EXPORTAÇÃO (PDF / EXCEL)
   ============================== */

async function processExport(format) {
    const selectedIds = Array.from(
        document.querySelectorAll('.export-employee-check:checked')
    ).map(c => c.value);

    if (selectedIds.length === 0) {
        alert(window.I18N?.labels?.select_at_least_one || 'Selecione pelo menos um funcionário para exportar.');
        return;
    }

    const btn = event.currentTarget;
    const originalText = btn.querySelector('.btn-text').innerHTML;

    btn.querySelector('.btn-text').innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${window.I18N?.labels?.generating || 'Gerando'} ${format.toUpperCase()}...`;
    btn.disabled = true;

    try {
        const response = await fetch(`${window.BASE_PATH}/api/export/infractions-report?ids=${selectedIds.join(',')}`);
        const result = await response.json();

        if (result.success) {
            if (format === 'pdf') {
                await generatePremiumInfractionPDF(result.data);
            } else if (format === 'print') {
                await generatePremiumInfractionPDF(result.data, true);
            }

            btn.querySelector('.btn-text').innerHTML = `<i class="fa-solid fa-check"></i> ${window.I18N?.labels?.completed || 'Concluído!'}`;
            setTimeout(() => {
                btn.querySelector('.btn-text').innerHTML = originalText;
                btn.disabled = false;
                closeExportModal();
            }, 200);
        } else {
            alert('Erro ao gerar relatório: ' + result.error);
            btn.disabled = false;
            btn.querySelector('.btn-text').innerHTML = originalText;
        }
    } catch (error) {
        console.error('Erro na exportação:', error);
        alert('Erro ao processar solicitação.');
        btn.disabled = false;
        btn.querySelector('.btn-text').innerHTML = originalText;
    }
}

/**
 * GERAÇÃO DE PDF DECORADO (ESTILO DASHBOARD)
 */
async function generatePremiumInfractionPDF(data, shouldPrint = false) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    // Configurações Estéticas
    const primaryRed = [227, 6, 19];
    const darkBlue = [30, 41, 59];
    const grayText = [100, 116, 139];
    
    // --- Cabeçalho Decorado ---
    doc.setFillColor(...primaryRed);
    doc.rect(0, 0, 210, 40, 'F'); // Faixa de topo

    doc.setTextColor(255, 255, 255);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(22);
    doc.text("Facchini", 15, 25);
    
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text("SISTEMA DE GESTÃO DE SEGURANÇA", 15, 32);

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.text("RELATÓRIO DE INFRAÇÕES", 140, 25, { align: 'left' });
    
    // --- Meta Info ---
    doc.setTextColor(...darkBlue);
    doc.setFontSize(10);
    doc.setFont("helvetica", "bold");
    doc.text(`Gerado em: ${new Date().toLocaleString('pt-BR')}`, 15, 52);
    doc.text(`Total de Colaboradores: ${data.length}`, 15, 58);
    
    doc.setDrawColor(226, 232, 240);
    doc.line(15, 65, 195, 65);

    // --- Tabela AutoTable ---
    const tableBody = data.map(item => [
        item.nome,
        item.cpf || '---',
        item.departamento || '---',
        item.total_infracoes,
        item.natureza || '---'
    ]);

    doc.autoTable({
        startY: 75,
        head: [['Colaborador', 'CPF', 'Departamento', 'Qtd. Infrações', 'Natureza Principal']],
        body: tableBody,
        theme: 'striped',
        headStyles: {
            fillColor: primaryRed,
            textColor: [255, 255, 255],
            fontSize: 10,
            fontStyle: 'bold',
            halign: 'left'
        },
        bodyStyles: {
            fontSize: 9,
            textColor: darkBlue
        },
        alternateRowStyles: {
            fillColor: [248, 250, 252]
        },
        margin: { left: 15, right: 15 },
        columnStyles: {
            3: { halign: 'center' }
        }
    });

    // --- Rodapé ---
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(...grayText);
        doc.text(`Página ${i} de ${pageCount}`, 105, 285, { align: 'center' });
        doc.text("Facchini © 2026 - Todos os direitos reservados", 15, 285);
    }
    
    if (shouldPrint) {
        doc.autoPrint();
        window.open(doc.output('bloburl'), '_blank');
    } else {
        doc.save(`Relatorio_Infracoes_${new Date().toISOString().split('T')[0]}.pdf`);
    }
}

/**
 * GERAÇÃO DE EXCEL DECORADO
 */
function generatePremiumInfractionExcel(data) {
    const XLSX = window.XLSX;
    
    // Criar dados com cabeçalho amigável
    const formattedData = data.map(item => ({
        "Colaborador": item.nome,
        "CPF": item.cpf || '---',
        "Departamento": item.departamento || '---',
        "Qtd. Infrações": item.total_infracoes,
        "Natureza Principal": item.natureza || '---'
    }));

    const ws = XLSX.utils.json_to_sheet(formattedData);
    
    // Ajustar largura das colunas
    const wscols = [
        { wch: 30 }, // Nome
        { wch: 15 }, // CPF
        { wch: 20 }, // Dept
        { wch: 15 }, // Qtd
        { wch: 25 }, // Natureza
    ];
    ws['!cols'] = wscols;

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Infrações");

    // Salvar
    XLSX.writeFile(wb, `Relatorio_Infracoes_${new Date().toISOString().split('T')[0]}.xlsx`);
}

/* ==============================
   FAVORITOS (BOOKMARK)
   ============================== */
async function toggleBookmark(btn, occId) {
    try {
        const formData = new FormData();
        formData.append('id', occId);

        const response = await fetch(`${window.BASE_PATH}/api/occurrence/toggle-favorite`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            const card = document.getElementById(`card-infraction-${occId}`);
            const row = document.getElementById(`row-infraction-${occId}`);

            if (result.favorito) {
                if (card) {
                    card.classList.add('is-bookmarked');
                    // Mover para o topo da grid
                    if (card.parentNode) {
                        card.parentNode.prepend(card);
                    }
                }
                if (row) {
                    row.classList.add('is-bookmarked');
                    // Mover para o topo da tabela
                    if (row.parentNode) {
                        row.parentNode.prepend(row);
                    }
                }
                btn.classList.add('active');
            } else {
                if (card) card.classList.remove('is-bookmarked');
                if (row) row.classList.remove('is-bookmarked');
                btn.classList.remove('active');
            }
        }
    } catch (error) {
        console.error('Erro ao favoritar:', error);
    }
}

/* ==============================
   OCULTAR INFRAÇÃO (SOFT DELETE)
   ============================== */
let hideTargetId = null;

function confirmHideInfraction(id, name) {
    hideTargetId = id;
    const modal = document.getElementById('confirmHideModal');
    const nameEl = document.getElementById('hideTargetName');
    
    if (nameEl) nameEl.textContent = name;
    if (modal) {
        modal.classList.add('active');
        toggleScroll(true);
    }
    
    // Configurar o botão de confirmação
    const btnDoHide = document.getElementById('btnDoHide');
    if (btnDoHide) {
        btnDoHide.onclick = () => doHideInfraction(id);
    }
}

function closeConfirmHideModal() {
    const modal = document.getElementById('confirmHideModal');
    if (modal) {
        modal.classList.remove('active');
        toggleScroll(false);
    }
    hideTargetId = null;
}

async function doHideInfraction(id) {
    const btn = document.getElementById('btnDoHide');
    const originalText = btn.querySelector('.btn-text').innerHTML;
    
    btn.disabled = true;
    btn.querySelector('.btn-text').innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${window.I18N?.labels?.hiding || 'Ocultando...'}`;

    try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch(`${window.BASE_PATH}/api/occurrence/hide`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            // Fechar modal
            closeConfirmHideModal();
            
            // Remover da tela com animação
            const card = document.getElementById(`card-infraction-${id}`);
            const row = document.getElementById(`row-infraction-${id}`);
            
            if (card) {
                card.classList.add('fade-out-infraction');
                setTimeout(() => card.remove(), 500);
            }
            if (row) {
                row.classList.add('fade-out-infraction');
                setTimeout(() => row.remove(), 500);
            }
        } else {
            alert('Erro ao ocultar: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar solicitação.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.querySelector('.btn-text').innerHTML = originalText;
        }
    }
}

// As funções do Modern Picker (Apple Style) foram movidas para assets/js/picker.js

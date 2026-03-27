/**
 * INFRACTIONS.JS
 * Lógica do modal de exportação: seleção de setor, pesquisa de funcionários e exportação
 */

let allLoadedEmployees = []; // Cache da lista completa para filtrar localmente

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
    document.querySelector('.main-content').classList.add('no-main-scroll');

    // Reset completo ao abrir
    const select = document.getElementById('exportSectorSelect');
    if (select) select.selectedIndex = 0;

    // Estado Inicial: Nenhum setor selecionado
    const listContainer = document.getElementById('exportEmployeeList');
    listContainer.innerHTML = `
        <div class="employee-empty info">
            <i class="fa-solid fa-circle-info"></i>
            <span>Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.</span>
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
        document.querySelector('.main-content').classList.remove('no-main-scroll');
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
            <i class="fa-solid fa-spinner fa-spin"></i> Carregando funcionários...
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
                    <span>Nenhum funcionário encontrado neste setor.</span>
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
                <span>Erro ao carregar dados. Tente novamente.</span>
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

    employees.forEach((name, index) => {
        const id = `emp_export_${index}`;
        const item = document.createElement('label');
        item.className = 'employee-check-item';
        item.setAttribute('data-name', name.toLowerCase());
        item.innerHTML = `
            <input type="checkbox" class="export-employee-check" value="${name}" id="${id}" checked onchange="updateSelectedCount()">
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
        el.textContent = `${checked} de ${total} selecionados`;
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

function processExport(format) {
    const selectedEmployees = Array.from(
        document.querySelectorAll('.export-employee-check:checked')
    ).map(c => c.value);

    if (selectedEmployees.length === 0) {
        alert('Selecione pelo menos um funcionário para exportar.');
        return;
    }

    const btn = event.currentTarget;
    const originalText = btn.querySelector('.btn-text').innerHTML;

    btn.querySelector('.btn-text').innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Gerando ${format.toUpperCase()}...`;
    btn.disabled = true;
    btn.style.opacity = '0.7';

    // Simulação (substituir por chamada real ao backend)
    setTimeout(() => {
        btn.querySelector('.btn-text').innerHTML = '<i class="fa-solid fa-check"></i> Concluído!';
        btn.style.opacity = '1';

        setTimeout(() => {
            btn.querySelector('.btn-text').innerHTML = originalText;
            btn.disabled = false;
            alert(`Relatório ${format.toUpperCase()} gerado com sucesso para ${selectedEmployees.length} funcionário(s).`);
        }, 1500);
    }, 2500);
}

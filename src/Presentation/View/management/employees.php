<?php
$pageTitle = 'Facchini - ' . __('Alunos');
$extraHead = '
<link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css?v=' . @filemtime(BASE_DIR . '/assets/css/management.css') . '">
<style>
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }
    .modal-overlay.active { display: flex; }
    .modal-content-premium {
        background: var(--bg-card);
        width: 90%;
        max-width: 500px;
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--border);
        animation: modalScale 0.3s ease-out;
    }
    @keyframes modalScale {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .modal-header {
        padding: 20px;
        background: var(--primary);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-body { padding: 24px; }
    .modal-footer {
        padding: 16px 24px;
        background: var(--bg-body);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid var(--border);
    }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-main);
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        transition: 0.2s;
    }
    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px var(--primary-light);
    }
</style>
';
ob_start();
?>

<header class="page-header">
    <div class="page-title">
        <h1><?= __('Alunos') ?></h1>
        <p><?= __('Gerencie os alunos cadastrados na instituição') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn-primary" onclick="openAddEmployeeModal()">
            <i class="fa-solid fa-user-plus"></i> <?= __('Novo Aluno') ?>
        </button>
    </div>
</header>

<div class="page-content">
    <!-- Summary -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-icon blue">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Total de Alunos') ?></span>
                <span class="summary-value" id="summaryTotal">0</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon green">
                <i class="fa-solid fa-building"></i>
            </div>
            <div class="summary-info">
                <span class="summary-label"><?= __('Setores Ativos') ?></span>
                <span class="summary-value"><?= count($setores) ?></span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <input type="text" id="employeeSearch" placeholder="<?= __('Buscar aluno por nome...') ?>" oninput="filterEmployeeTable()">
        <select id="sectorFilter" onchange="filterEmployeeTable()">
            <option value=""><?= __('Todos os Setores') ?></option>
            <?php foreach ($setores as $s): ?>
                <option value="<?= $s->getId() ?>"><?= htmlspecialchars($s->getName()) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-filter" onclick="loadEmployeesTable()"><i class="fa-solid fa-rotate"></i> <?= __('Atualizar') ?></button>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="card-header">
            <h3><?= __('Lista de Alunos') ?></h3>
            <span id="recordCount" style="font-size: 12px; color: var(--text-muted);">0 <?= __('registros') ?></span>
        </div>

        <table class="data-table" id="employeesTable">
            <thead>
                <tr>
                    <th><?= __('Nome') ?></th>
                    <th><?= __('Setor') ?></th>
                    <th><?= __('Cadastrado em') ?></th>
                    <th><?= __('Ações') ?></th>
                </tr>
            </thead>
            <tbody id="employeesTableBody">
                <!-- Dynamic Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Novo Aluno -->
<div class="modal-overlay" id="employeeModal">
    <div class="modal-content-premium">
        <div class="modal-header">
            <h3 id="modalTitle"><?= __('Novo Aluno') ?></h3>
            <button onclick="closeEmployeeModal()" style="background:none; border:none; color:white; cursor:pointer; font-size:20px;">&times;</button>
        </div>
        <div class="modal-body">
            <form id="employeeForm">
                <input type="hidden" id="employeeId">
                <div class="form-group">
                    <label><?= __('Nome Completo') ?></label>
                    <input type="text" id="empNome" class="form-control" placeholder="<?= __('Ex: João Silva') ?>" required>
                </div>
                <div class="form-group">
                    <label><?= __('Setor / Curso') ?></label>
                    <select id="empSetor" class="form-control" required>
                        <option value=""><?= __('Selecione um setor...') ?></option>
                        <?php foreach ($setores as $s): ?>
                            <option value="<?= $s->getId() ?>"><?= htmlspecialchars($s->getName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-action" onclick="closeEmployeeModal()"><?= __('Cancelar') ?></button>
            <button class="btn-primary" id="btnSaveEmployee" onclick="saveEmployee()">
                <span class="btn-text"><?= __('Salvar') ?></span>
            </button>
        </div>
    </div>
</div>

<script>
    let allEmployees = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadEmployeesTable();
    });

    async function loadEmployeesTable() {
        const tbody = document.getElementById('employeesTableBody');
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px;"><i class="fa-solid fa-spinner fa-spin"></i> <?= __('Carregando...') ?></td></tr>';
        
        try {
            const response = await fetch('<?= BASE_PATH ?>/api/employees');
            const result = await response.json();
            
            if (result.success) {
                allEmployees = result.data;
                renderEmployees(allEmployees);
                document.getElementById('summaryTotal').textContent = allEmployees.length;
            }
        } catch (error) {
            console.error('Erro ao carregar alunos:', error);
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red; padding: 20px;"><?= __('Erro ao carregar dados.') ?></td></tr>';
        }
    }

    function renderEmployees(data) {
        const tbody = document.getElementById('employeesTableBody');
        const countSpan = document.getElementById('recordCount');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);"><?= __('Nenhum aluno encontrado.') ?></td></tr>';
            countSpan.textContent = '0 <?= __('registros') ?>';
            return;
        }

        data.forEach(emp => {
            const date = new Date(emp.created_at).toLocaleDateString('pt-BR');
            tbody.innerHTML += `
                <tr>
                    <td style="font-weight:600;">${emp.nome}</td>
                    <td><span class="sigla" style="background:var(--primary-light); color:var(--primary); padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700;">${emp.setor_nome}</span></td>
                    <td>${date}</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-action" onclick="editEmployee(${emp.id})" title="<?= __('Editar') ?>"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action danger" onclick="deleteEmployee(${emp.id})" title="<?= __('Excluir') ?>"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
        countSpan.textContent = `${data.length} <?= __('registros') ?>`;
    }

    function filterEmployeeTable() {
        const search = document.getElementById('employeeSearch').value.toLowerCase();
        const sector = document.getElementById('sectorFilter').value;
        
        const filtered = allEmployees.filter(emp => {
            const matchSearch = emp.nome.toLowerCase().includes(search);
            const matchSector = sector === '' || emp.setor_id == sector;
            return matchSearch && matchSector;
        });
        
        renderEmployees(filtered);
    }

    function openAddEmployeeModal() {
        document.getElementById('modalTitle').textContent = '<?= __('Novo Aluno') ?>';
        document.getElementById('employeeId').value = '';
        document.getElementById('employeeForm').reset();
        document.getElementById('employeeModal').classList.add('active');
    }

    function closeEmployeeModal() {
        document.getElementById('employeeModal').classList.remove('active');
    }

    function editEmployee(id) {
        const emp = allEmployees.find(e => e.id === id);
        if (!emp) return;

        document.getElementById('modalTitle').textContent = '<?= __('Editar Aluno') ?>';
        document.getElementById('employeeId').value = emp.id;
        document.getElementById('empNome').value = emp.nome;
        document.getElementById('empSetor').value = emp.setor_id;
        document.getElementById('employeeModal').classList.add('active');
    }

    async function saveEmployee() {
        const id = document.getElementById('employeeId').value;
        const nome = document.getElementById('empNome').value;
        const setor_id = document.getElementById('empSetor').value;

        if (!nome || !setor_id) return alert('<?= __('Preencha todos os campos.') ?>');

        const btn = document.getElementById('btnSaveEmployee');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        const endpoint = id ? '<?= BASE_PATH ?>/api/employees/update' : '<?= BASE_PATH ?>/api/employees/create';
        
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: JSON.stringify({ id, nome, setor_id })
            });
            const result = await response.json();
            
            if (result.success) {
                closeEmployeeModal();
                loadEmployeesTable();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            alert('Erro na conexão.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<?= __('Salvar') ?>';
        }
    }

    async function deleteEmployee(id) {
        if (!confirm('<?= __('Deseja realmente excluir este aluno?') ?>')) return;

        try {
            const response = await fetch('<?= BASE_PATH ?>/api/employees/delete', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            const result = await response.json();
            if (result.success) {
                loadEmployeesTable();
            }
        } catch (error) {
            console.error('Erro ao excluir:', error);
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

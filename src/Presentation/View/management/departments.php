<?php
$pageTitle = 'epiGuard - Gestão de Setor';
$extraHead = '
    <!-- Bibliotecas de Processamento de Arquivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js";</script>
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/departments.css">
';

ob_start();
?>

<!-- Header -->
<div class="setor-header">
    <div class="page-title">
        <h1><?= __('Gestão de Setor') ?></h1>
        <p><?= __('Gerencie as áreas e os respectivos EPIs obrigatórios') ?></p>
    </div>
    <button class="btn-add-setor" onclick="openModal()">
        <i class="fa-solid fa-plus"></i> <?= __('Adicionar Setor') ?>
    </button>
</div>

<!-- Filtros -->
<form action="<?= BASE_PATH ?>/management/departments" method="GET" class="setor-filters">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInputSettings" name="search" placeholder="<?= __('Pesquisar setores...') ?>" oninput="filterSetores()" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <select name="status" onchange="this.form.submit()" class="status-filter">
        <option value="todos" <?= ($filters['status'] ?? 'todos') === 'todos' ? 'selected' : '' ?>><?= __('Filtrar Status (Todos)') ?></option>
        <option value="ativo" <?= ($filters['status'] ?? 'todos') === 'ativo' ? 'selected' : '' ?>><?= __('Ativos') ?></option>
        <option value="inativo" <?= ($filters['status'] ?? 'todos') === 'inativo' ? 'selected' : '' ?>><?= __('Inativos') ?></option>
    </select>
    <select name="risk" onchange="this.form.submit()" class="risk-filter">
        <option value="todos" <?= ($filters['risk'] ?? 'todos') === 'todos' ? 'selected' : '' ?>><?= __('Filtrar Risco (Todos)') ?></option>
        <option value="baixo" <?= ($filters['risk'] ?? 'todos') === 'baixo' ? 'selected' : '' ?>><?= __('Baixo') ?> (< 5%)</option>
        <option value="medio" <?= ($filters['risk'] ?? 'todos') === 'medio' ? 'selected' : '' ?>><?= __('Médio') ?> (5% - 10%)</option>
        <option value="alto" <?= ($filters['risk'] ?? 'todos') === 'alto' ? 'selected' : '' ?>><?= __('Alto') ?> (>= 10%)</option>
    </select>
    <button type="submit" style="display: none;"></button>
</form>

<!-- Tabela -->
<div class="setor-table-wrapper">
    <table class="setor-table">
        <thead>
            <tr>
                <th><?= __('Nome do Setor') ?></th>
                <th><?= __('Funcionários Ativos') ?></th>
                <th><?= __('EPIs Obrigatórios') ?></th>
                <th><?= __('Risco') ?></th>
                <th style="text-align: right;"><?= __('Ações') ?></th>
            </tr>
        </thead>
        <tbody id="setoresTableBody">
            <?php if (empty($setores)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="fa-solid fa-folder-open" style="font-size: 24px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                        <?= __('Nenhum setor encontrado no banco de dados.') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($setores as $setor): ?>
                    <tr>
                        <td>
                            <div class="setor-nome"><?= __(htmlspecialchars($setor['nome'])) ?></div>
                            <div class="setor-desc"><?= __($setor['sigla'] ?: 'Sem sigla') ?></div>
                        </td>
                        <td><span class="setor-count"><?= $setor['total_funcionarios'] ?></span></td>
                        <td>
                            <div class="epi-icons">
                                <?php 
                                $epiIconsMap = [
                                    'capacete' => 'fa-hard-hat',
                                    'avental' => 'fa-shirt',
                                    'jaqueta' => 'fa-vest-patches',
                                    'oculos' => 'fa-glasses',
                                    'luvas' => 'fa-mitten',
                                    'mascara' => 'fa-mask-face',
                                    'protetor_auricular' => 'fa-head-side-virus'
                                ];
                                
                                $episSetor = [];
                                if (!empty($setor['epis_json'])) {
                                    $episSetor = json_decode($setor['epis_json'], true) ?: [];
                                }

                                if (empty($episSetor)): ?>
                                    <span class="epi-icon-badge" title="<?= __('Nenhum EPI') ?>" style="opacity: 0.3;"><i class="fa-solid fa-shield-slash"></i></span>
                                <?php else:
                                    foreach ($episSetor as $epiSlug): 
                                        $iconClass = $epiIconsMap[$epiSlug] ?? 'fa-shield';
                                        $label = __(ucwords(str_replace('_', ' ', $epiSlug)));
                                ?>
                                        <span class="epi-icon-badge" title="<?= $label ?>" data-epi="<?= $epiSlug ?>"><i class="fa-solid <?= $iconClass ?>"></i></span>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $risk = $setor['risk_p'] ?? 0;
                            $riskClass = 'baixo';
                            $riskLabel = __('Baixo');
                            
                            if ($risk >= 10) {
                                $riskClass = 'alto';
                                $riskLabel = __('Alto');
                            } elseif ($risk >= 5) {
                                $riskClass = 'medio';
                                $riskLabel = __('Médio');
                            }
                            ?>
                            <span class="risk-badge <?= $riskClass ?>" title="<?= sprintf(__('%s%% de funcionários com infrações'), number_format((float)$risk, 1)) ?>">
                                <?= $riskLabel ?> (<?= number_format((float)$risk, 1) ?>%)
                            </span>
                        </td>
                        <td>
                            <div class="setor-actions">
                                <span class="status-indicator" title="<?= $setor['status'] === 'ATIVO' ? __('Ativo') : __('Inativo') ?>" style="background: <?= $setor['status'] === 'ATIVO' ? '#10b981' : '#ef4444' ?>;"></span>
                                <button class="btn-edit" title="<?= __('Editar') ?>" onclick="editSetor(this)" data-id="<?= $setor['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-delete" title="<?= __('Excluir') ?>" onclick="deleteSetor(this)" data-id="<?= $setor['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ==================== MODAL ADICIONAR SETOR ==================== -->
<div class="modal-setor-overlay" id="modalSetor">
    <div class="modal-setor">
        <div class="modal-setor-header">
            <h2><?= __('Adicionar Setor') ?></h2>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>

        <!-- Nome do Setor -->
        <div class="form-group">
            <label class="form-label"><?= __('Nome do Setor') ?></label>
            <input class="form-input" type="text" id="inputNomeSetor" placeholder="<?= __('Ex: Soldagem TIG') ?>">
        </div>

        <!-- Funcionários -->
        <div class="form-group">
            <label class="form-label"><?= __('Funcionários') ?></label>
            <div class="upload-area" onclick="document.getElementById('fileUpload').click()">
                <i class="fa-solid fa-file-arrow-up"></i>
                <?= __('Adicionar funcionários via Excel / PDF') ?>
            </div>
            <input type="file" id="fileUpload" accept=".xlsx,.xls,.pdf,.csv" style="display: none;">
            <div id="uploadFeedback" style="margin-top: 8px; font-size: 13px; color: #10b981; display: none;">
                <i class="fa-solid fa-check-circle"></i> <span id="uploadCount">0</span> <?= __('funcionários detectados.') ?>
            </div>
            
            <!-- Lista de Funcionários -->
            <div class="employees-list-container" id="employeesListContainer">
                <div id="employeesListItems"></div>
            </div>
        </div>

        <!-- EPIs Obrigatórios -->
        <div class="form-group">
            <label class="form-label"><?= __('EPIs Obrigatórios (Marcas Permitidas)') ?></label>
            <div class="epi-grid" id="epiGrid">
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="capacete">
                    <div class="epi-card-icon"><i class="fa-solid fa-hard-hat"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name"><?= __('Capacete de Proteção') ?></div>
                        <div class="epi-card-brands">3M, MSA</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="avental">
                    <div class="epi-card-icon"><i class="fa-solid fa-shirt"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name"><?= __('Avental') ?></div>
                        <div class="epi-card-brands">Vivel, PVC</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="jaqueta">
                    <div class="epi-card-icon"><i class="fa-solid fa-vest-patches"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name"><?= __('Jaqueta') ?></div>
                        <div class="epi-card-brands"><?= __('Térmica, Impermeável') ?></div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="oculos">
                    <div class="epi-card-icon"><i class="fa-solid fa-glasses"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name"><?= __('Óculos de Proteção') ?></div>
                        <div class="epi-card-brands">3M, Danny</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="luvas">
                    <div class="epi-card-icon"><i class="fa-solid fa-mitten"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name"><?= __('Luvas de Raspa') ?></div>
                        <div class="epi-card-brands">Marluvas, Volk</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-setor-footer">
            <button class="btn-cancel" onclick="closeModal()"><?= __('Cancelar') ?></button>
            <button class="btn-create" id="btnCriarSetor" onclick="criarSetor()"><?= __('Criar Setor') ?></button>
        </div>
    </div>
</div>

<script>
    const BASE_PATH_LOCAL = '<?= BASE_PATH ?>';
    window.I18N = {
        'Nenhum funcionário encontrado no arquivo. Verifique a estrutura.': '<?= __('Nenhum funcionário encontrado no arquivo. Verifique a estrutura.') ?>',
        'Erro ao processar arquivo: ': '<?= __('Erro ao processar arquivo: ') ?>',
        'Editar Setor': '<?= __('Editar Setor') ?>',
        'Salvar Alterações': '<?= __('Salvar Alterações') ?>',
        'Adicionar Setor': '<?= __('Adicionar Setor') ?>',
        'Criar Setor': '<?= __('Criar Setor') ?>',
        'Por favor, informe o nome do setor.': '<?= __('Por favor, informe o nome do setor.') ?>',
        'Setor atualizado!': '<?= __('Setor atualizado!') ?>',
        'Setor criado com sucesso!': '<?= __('Setor criado com sucesso!') ?>',
        'Erro na comunicação com o servidor.': '<?= __('Erro na comunicação com o servidor.') ?>',
        'Deseja desativar este setor?': '<?= __('Deseja desativar este setor?') ?>'
    };
    let editingRow = null;
    let currentSectorId = null; // Armazena o ID do setor sendo editado
    let importedEmployees = []; // Armazena nomes extraídos do arquivo

    // --- File Upload Handling ---
    document.getElementById('fileUpload').addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        importedEmployees = [];
        const feedback = document.getElementById('uploadFeedback');
        const countSpan = document.getElementById('uploadCount');

        try {
            if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv')) {
                await parseExcel(file);
            } else if (file.name.endsWith('.pdf')) {
                await parsePDF(file);
            }

            if (importedEmployees.length > 0) {
                feedback.style.display = 'block';
                countSpan.textContent = importedEmployees.length;
                renderEmployeeList(true);
            } else {
                alert(window.I18N['Nenhum funcionário encontrado no arquivo. Verifique a estrutura.']);
                feedback.style.display = 'none';
            }
        } catch (err) {
            console.error(err);
            alert(window.I18N['Erro ao processar arquivo: '] + err.message);
        }
    });

    function renderEmployeeList(isImport = false) {
        const container = document.getElementById('employeesListContainer');
        const listBody = document.getElementById('employeesListItems');
        listBody.innerHTML = '';
        
        if (importedEmployees.length > 0) {
            container.style.display = 'block';
            importedEmployees.forEach((name, index) => {
                const item = document.createElement('div');
                item.className = 'employee-item';
                item.innerHTML = `
                    <span><i class="fa-solid fa-user" style="margin-right: 10px;"></i> ${name}</span>
                    ${isImport ? `<button class="btn-remove-employee" style="display: block;" onclick="removeImported(${index})"><i class="fa-solid fa-xmark"></i></button>` : ''}
                `;
                listBody.appendChild(item);
            });
        } else {
            container.style.display = 'none';
        }
    }

    function removeImported(index) {
        importedEmployees.splice(index, 1);
        document.getElementById('uploadCount').textContent = importedEmployees.length;
        renderEmployeeList(true);
    }

    async function parseExcel(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const rows = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
                    
                    const headerTerms = ['nome', 'funcionario', 'funcionário', 'aluno', 'estudante', 'colaborador'];

                    rows.forEach((row, index) => {
                        // Look through columns to find a likely name
                        for (let col = 0; col < Math.min(row.length, 3); col++) {
                            let value = row[col];
                            if (value && typeof value === 'string' && value.trim().length > 2) {
                                let trimmed = value.trim();
                                
                                // Skip obvious headers in the first few rows
                                if (index < 3 && headerTerms.some(term => trimmed.toLowerCase() === term)) {
                                    continue;
                                }

                                // Avoid numeric strings (like CPFs or IDs)
                                if (/^\d+$/.test(trimmed.replace(/[-.]/g, ''))) {
                                    continue;
                                }

                                importedEmployees.push(trimmed);
                                break; // Found name in this row
                            }
                        }
                    });
                    
                    // Deduplicate
                    importedEmployees = [...new Set(importedEmployees)];
                    resolve();
                } catch (err) { reject(err); }
            };
            reader.onerror = reject;
            reader.readAsArrayBuffer(file);
        });
    }

    async function parsePDF(file) {
        const arrayBuffer = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            const strings = textContent.items.map(item => item.str.trim()).filter(s => s.length > 2);
            
            // Lógica simples: cada string considerável é tratada como um potencial nome
            importedEmployees.push(...strings);
        }
        importedEmployees = [...new Set(importedEmployees)];
    }

    // --- Modal ---
    function openModal(isEdit = false, row = null) {
        const modal = document.getElementById('modalSetor');
        const title = modal.querySelector('.modal-setor-header h2');
        const btn = document.getElementById('btnCriarSetor');

        modal.classList.add('active');

        if (isEdit && row) {
            editingRow = row;
            currentSectorId = row.querySelector('.btn-edit').getAttribute('data-id');
            title.textContent = window.I18N['Editar Setor'];
            btn.textContent = window.I18N['Salvar Alterações'];

            // Preencher campos
            const nomeContainer = row.querySelector('.setor-nome');
            const nome = nomeContainer ? nomeContainer.textContent : '';
            document.getElementById('inputNomeSetor').value = nome;

            // Marcar EPIs
            const rowEpis = row.querySelectorAll('.epi-icon-badge');
            rowEpis.forEach(badge => {
                const epiSlug = badge.getAttribute('data-epi');
                const epiCard = document.querySelector(`.epi-card[data-epi="${epiSlug}"]`);
                if (epiCard) epiCard.classList.add('selected');
            });

            // Buscar funcionários atuais
            fetch(`${BASE_PATH_LOCAL}/api/departments/employees?id=${currentSectorId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        importedEmployees = res.data;
                        renderEmployeeList(false);
                    }
                });
        } else {
            editingRow = null;
            title.textContent = window.I18N['Adicionar Setor'];
            btn.textContent = window.I18N['Criar Setor'];
        }
    }

    function closeModal() {
        const modal = document.getElementById('modalSetor');
        modal.classList.remove('active');
        document.getElementById('inputNomeSetor').value = '';
        document.getElementById('uploadFeedback').style.display = 'none';
        document.getElementById('employeesListContainer').style.display = 'none';
        importedEmployees = [];
        currentSectorId = null;
        editingRow = null;
        document.querySelectorAll('.epi-card').forEach(c => c.classList.remove('selected'));
    }

    function toggleEpi(card) {
        card.classList.toggle('selected');
    }

    async function criarSetor() {
        const nome = document.getElementById('inputNomeSetor').value;
        const selectedEpis = Array.from(document.querySelectorAll('.epi-card.selected')).map(c => c.getAttribute('data-epi'));

        if (!nome) {
            alert(window.I18N['Por favor, informe o nome do setor.']);
            return;
        }

        const formData = {
            nome: nome,
            epis: selectedEpis,
            funcionarios: importedEmployees
        };

        try {
            const endpoint = currentSectorId ? `${BASE_PATH_LOCAL}/api/departments/update` : `${BASE_PATH_LOCAL}/api/departments/create`;
            if (currentSectorId) formData.id = currentSectorId;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            if (result.success) {
                alert(currentSectorId ? window.I18N['Setor atualizado!'] : window.I18N['Setor criado com sucesso!']);
                location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert(window.I18N['Erro na comunicação com o servidor.']);
        }
    }

    function filterSetores() {
        const term = document.getElementById('searchInputSettings').value.toLowerCase();
        const rows = document.querySelectorAll('#setoresTableBody tr');
        rows.forEach(row => {
            const nomeContainer = row.querySelector('.setor-nome');
            if (nomeContainer) {
                const nome = nomeContainer.textContent.toLowerCase();
                row.style.display = nome.includes(term) ? '' : 'none';
            }
        });
    }

    async function deleteSetor(btn) {
        if (!confirm(window.I18N['Deseja desativar este setor?'])) return;
        const id = btn.getAttribute('data-id');
        try {
            const response = await fetch(`${BASE_PATH_LOCAL}/api/departments/delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await response.json();
            if (result.success) {
                // Ao desativar, recarregamos para atualizar status e filtragem
                location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

    function editSetor(btn) {
        const row = btn.closest('tr');
        openModal(true, row);
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

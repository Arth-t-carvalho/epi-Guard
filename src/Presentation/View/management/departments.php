<?php
$pageTitle = 'epiGuard - Gestão de Setor';
$extraHead = '
    <!-- Bibliotecas de Processamento de Arquivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js";</script>
    <style>
        /* === PAGE STYLES === */
        .setor-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        .setor-header .page-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #1F2937;
        }

        .setor-header .page-title p {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .btn-add-setor {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: #E30613;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: "Inter", sans-serif;
            transition: 0.2s;
            box-shadow: 0 4px 14px rgba(227, 6, 19, 0.25);
        }

        .btn-add-setor:hover {
            background: #c40510;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(227, 6, 19, 0.35);
        }

        /* Filtros */
        .setor-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 16px;
            min-width: 280px;
            transition: 0.2s;
        }

        .search-box:focus-within {
            border-color: #E30613;
            box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.08);
        }

        .search-box i {
            color: #94a3b8;
            font-size: 14px;
        }

        .search-box input {
            border: none;
            outline: none;
            font-size: 13px;
            font-family: "Inter", sans-serif;
            color: #1F2937;
            width: 100%;
            background: transparent;
        }

        .search-box input::placeholder {
            color: #94a3b8;
        }

        .setor-filters select {
            padding: 10px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            font-family: "Inter", sans-serif;
            color: #1F2937;
            background: #fff;
            cursor: pointer;
            outline: none;
            transition: 0.2s;
        }

        .setor-filters select:focus {
            border-color: #E30613;
            box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.08);
        }

        /* Tabela */
        .setor-table-wrapper {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #f0f0f5;
            overflow: hidden;
        }

        .setor-table {
            width: 100%;
            border-collapse: collapse;
        }

        .setor-table thead th {
            text-align: left;
            padding: 16px 24px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            border-bottom: 1px solid #f0f0f5;
        }

        .setor-table tbody tr {
            transition: background 0.15s;
        }

        .setor-table tbody tr:hover {
            background: #fafbfc;
        }

        .setor-table tbody td {
            padding: 18px 24px;
            border-bottom: 1px solid #f5f5f8;
            vertical-align: middle;
        }

        .setor-table tbody tr:last-child td {
            border-bottom: none;
        }

        .setor-nome {
            font-size: 14px;
            font-weight: 700;
            color: #1F2937;
        }

        .setor-desc {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .setor-count {
            font-size: 15px;
            font-weight: 700;
            color: #1F2937;
        }

        .epi-icons {
            display: flex;
            gap: 8px;
        }

        .epi-icon-badge {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #475569;
            transition: 0.2s;
        }

        .epi-icon-badge:hover {
            background: rgba(227, 6, 19, 0.08);
            color: #E30613;
        }

        .setor-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            align-items: center;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid #d1fae5;
        }

        .btn-edit {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #94a3b8;
            font-size: 12px;
            transition: 0.2s;
        }

        .btn-edit:hover {
            border-color: #E30613;
            color: #E30613;
            background: rgba(227, 6, 19, 0.04);
        }

        .btn-delete {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid #fee2e2;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #ef4444;
            font-size: 12px;
            transition: 0.2s;
        }

        .btn-delete:hover {
            border-color: #ef4444;
            background: #fef2f2;
        }

        /* Risk Badges */
        .risk-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .risk-badge.baixo {
            background: #d1fae5;
            color: #065f46;
        }

        .risk-badge.medio {
            background: #fef3c7;
            color: #92400e;
        }

        .risk-badge.alto {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ============ MODAL ============ */
        .modal-setor-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-setor-overlay.active {
            display: flex;
        }

        .modal-setor {
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            width: 520px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            animation: dropIn 0.3s ease;
        }

        @keyframes dropIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-setor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .modal-setor-header h2 {
            font-size: 18px;
            font-weight: 800;
            color: #1F2937;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #94a3b8;
            cursor: pointer;
            transition: 0.2s;
            padding: 4px;
        }

        .modal-close-btn:hover {
            color: #E30613;
        }

        /* Form fields */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: "Inter", sans-serif;
            color: #1F2937;
            outline: none;
            transition: 0.2s;
            background: #fff;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            border-color: #E30613;
            box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.08);
        }

        /* Upload area */
        .upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .upload-area:hover {
            border-color: #E30613;
            color: #E30613;
            background: rgba(227, 6, 19, 0.02);
        }

        .upload-area i {
            font-size: 16px;
        }

        /* EPIs grid */
        .epi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .epi-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s;
            background: #fff;
        }

        .epi-card:hover {
            border-color: #E30613;
            background: rgba(227, 6, 19, 0.02);
        }

        .epi-card.selected {
            border-color: #E30613;
            background: rgba(227, 6, 19, 0.06);
        }

        .epi-card-icon {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #475569;
        }

        .epi-card.selected .epi-card-icon {
            background: rgba(227, 6, 19, 0.1);
            color: #E30613;
        }

        .epi-card-info .epi-card-name {
            font-size: 13px;
            font-weight: 700;
            color: #1F2937;
        }

        .epi-card-info .epi-card-brands {
            font-size: 11px;
            color: #94a3b8;
        }

        /* Footer */
        .modal-setor-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f5;
        }

        .btn-cancel {
            background: none;
            border: none;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 10px 20px;
            transition: 0.2s;
            font-family: "Inter", sans-serif;
        }

        .btn-cancel:hover {
            color: #1F2937;
        }

        .btn-create {
            padding: 10px 24px;
            background: #E30613;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: "Inter", sans-serif;
            transition: 0.2s;
        }

        .btn-create:hover {
            background: #c40510;
        }

        .btn-create:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Employee list in modal */
        .employees-list-container {
            margin-top: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }

        .employee-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            color: #475569;
        }

        .employee-item:last-child {
            border-bottom: none;
        }

        .employee-item i {
            color: #94a3b8;
            font-size: 14px;
        }

        .btn-remove-employee {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            transition: 0.2s;
            display: none; /* Only show for newly imported */
        }

        .btn-remove-employee:hover {
            color: #ef4444;
        }
    </style>
';

ob_start();
?>

<!-- Header -->
<div class="setor-header">
    <div class="page-title">
        <h1>Gestão de Setor</h1>
        <p>Gerencie as áreas e os respectivos EPIs obrigatórios</p>
    </div>
    <button class="btn-add-setor" onclick="openModal()">
        <i class="fa-solid fa-plus"></i> Adicionar Setor
    </button>
</div>

<!-- Filtros -->
<form action="<?= BASE_PATH ?>/management/departments" method="GET" class="setor-filters">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInputSettings" name="search" placeholder="Pesquisar setores..." oninput="filterSetores()" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <select name="status" onchange="this.form.submit()">
        <option value="todos" <?= ($filters['status'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>Filtrar Status (Todos)</option>
        <option value="ativo" <?= ($filters['status'] ?? 'todos') === 'ativo' ? 'selected' : '' ?>>Ativos</option>
        <option value="inativo" <?= ($filters['status'] ?? 'todos') === 'inativo' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <select name="risk" onchange="this.form.submit()">
        <option value="todos" <?= ($filters['risk'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>Filtrar Risco (Todos)</option>
        <option value="baixo" <?= ($filters['risk'] ?? 'todos') === 'baixo' ? 'selected' : '' ?>>Baixo (< 5%)</option>
        <option value="medio" <?= ($filters['risk'] ?? 'todos') === 'medio' ? 'selected' : '' ?>>Médio (5% - 10%)</option>
        <option value="alto" <?= ($filters['risk'] ?? 'todos') === 'alto' ? 'selected' : '' ?>>Alto (>= 10%)</option>
    </select>
    <button type="submit" style="display: none;"></button>
</form>

<!-- Tabela -->
<div class="setor-table-wrapper">
    <table class="setor-table">
        <thead>
            <tr>
                <th>Nome do Setor</th>
                <th>Funcionários Ativos</th>
                <th>EPIs Obrigatórios</th>
                <th>Risco</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody id="setoresTableBody">
            <?php if (empty($setores)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="fa-solid fa-folder-open" style="font-size: 24px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                        Nenhum setor encontrado no banco de dados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($setores as $setor): ?>
                    <tr>
                        <td>
                            <div class="setor-nome"><?= htmlspecialchars($setor['nome']) ?></div>
                            <div class="setor-desc"><?= htmlspecialchars($setor['sigla'] ?: 'Sem sigla') ?></div>
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
                                    <span class="epi-icon-badge" title="Nenhum EPI" style="opacity: 0.3;"><i class="fa-solid fa-shield-slash"></i></span>
                                <?php else:
                                    foreach ($episSetor as $epiSlug): 
                                        $iconClass = $epiIconsMap[$epiSlug] ?? 'fa-shield';
                                        $label = ucfirst(str_replace('_', ' ', $epiSlug));
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
                            $riskLabel = 'Baixo';
                            
                            if ($risk >= 10) {
                                $riskClass = 'alto';
                                $riskLabel = 'Alto';
                            } elseif ($risk >= 5) {
                                $riskClass = 'medio';
                                $riskLabel = 'Médio';
                            }
                            ?>
                            <span class="risk-badge <?= $riskClass ?>" title="<?= number_format((float)$risk, 1) ?>% de funcionários com infrações">
                                <?= $riskLabel ?> (<?= number_format((float)$risk, 1) ?>%)
                            </span>
                        </td>
                        <td>
                            <div class="setor-actions">
                                <span class="status-indicator" title="<?= $setor['status'] === 'ATIVO' ? 'Ativo' : 'Inativo' ?>" style="background: <?= $setor['status'] === 'ATIVO' ? '#10b981' : '#ef4444' ?>;"></span>
                                <button class="btn-edit" title="Editar" onclick="editSetor(this)" data-id="<?= $setor['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn-delete" title="Excluir" onclick="deleteSetor(this)" data-id="<?= $setor['id'] ?>"><i class="fa-solid fa-trash"></i></button>
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
            <h2>Adicionar Setor</h2>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>

        <!-- Nome do Setor -->
        <div class="form-group">
            <label class="form-label">Nome do Setor</label>
            <input class="form-input" type="text" id="inputNomeSetor" placeholder="Ex: Soldagem TIG">
        </div>

        <!-- Funcionários -->
        <div class="form-group">
            <label class="form-label">Funcionários</label>
            <div class="upload-area" onclick="document.getElementById('fileUpload').click()">
                <i class="fa-solid fa-file-arrow-up"></i>
                Adicionar funcionários via Excel / PDF
            </div>
            <input type="file" id="fileUpload" accept=".xlsx,.xls,.pdf,.csv" style="display: none;">
            <div id="uploadFeedback" style="margin-top: 8px; font-size: 13px; color: #10b981; display: none;">
                <i class="fa-solid fa-check-circle"></i> <span id="uploadCount">0</span> funcionários detectados.
            </div>
            
            <!-- Lista de Funcionários -->
            <div class="employees-list-container" id="employeesListContainer">
                <div id="employeesListItems"></div>
            </div>
        </div>

        <!-- EPIs Obrigatórios -->
        <div class="form-group">
            <label class="form-label">EPIs Obrigatórios (Marcas Permitidas)</label>
            <div class="epi-grid" id="epiGrid">
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="capacete">
                    <div class="epi-card-icon"><i class="fa-solid fa-hard-hat"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name">Capacete de Proteção</div>
                        <div class="epi-card-brands">3M, MSA</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="avental">
                    <div class="epi-card-icon"><i class="fa-solid fa-shirt"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name">Avental</div>
                        <div class="epi-card-brands">Vivel, PVC</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="jaqueta">
                    <div class="epi-card-icon"><i class="fa-solid fa-vest-patches"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name">Jaqueta</div>
                        <div class="epi-card-brands">Térmica, Impermeável</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="oculos">
                    <div class="epi-card-icon"><i class="fa-solid fa-glasses"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name">Óculos de Proteção</div>
                        <div class="epi-card-brands">3M, Danny</div>
                    </div>
                </div>
                <div class="epi-card" onclick="toggleEpi(this)" data-epi="luvas">
                    <div class="epi-card-icon"><i class="fa-solid fa-mitten"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name">Luvas de Raspa</div>
                        <div class="epi-card-brands">Marluvas, Volk</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-setor-footer">
            <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
            <button class="btn-create" id="btnCriarSetor" onclick="criarSetor()">Criar Setor</button>
        </div>
    </div>
</div>

<script>
    const BASE_PATH_LOCAL = '<?= BASE_PATH ?>';
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
                alert('Nenhum funcionário encontrado no arquivo. Verifique a estrutura.');
                feedback.style.display = 'none';
            }
        } catch (err) {
            console.error(err);
            alert('Erro ao processar arquivo: ' + err.message);
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
            title.textContent = 'Editar Setor';
            btn.textContent = 'Salvar Alterações';

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
            title.textContent = 'Adicionar Setor';
            btn.textContent = 'Criar Setor';
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
            alert('Por favor, informe o nome do setor.');
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
                alert(currentSectorId ? 'Setor atualizado!' : 'Setor criado com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('Erro na comunicação com o servidor.');
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
        if (!confirm('Deseja desativar este setor?')) return;
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

<?php
$pageTitle = 'epiGuard - Gestão de Setor';
$extraHead = '
    <!-- Page CSS -->
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/picker.css">
    <!-- Bibliotecas de Processamento de Arquivos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js";</script>
    <style>
        /* Desativar scroll da página e tornar a tabela flexível */
        .main-content { overflow-y: hidden !important; }
        #page-content-wrapper { display: flex; flex-direction: column; height: 100%; overflow: hidden; margin-top: -50px; }

        /* Remover cabeçalho de boas-vindas redundante */
        .welcome-container { display: none !important; }

        /* === PAGE STYLES === */
        .setor-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-shrink: 0;
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
            margin-left: auto; /* Empurra para o final da linha */
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
            flex-shrink: 0;
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
            overflow-y: auto;
            flex: 1;
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
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
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

        /* ============ DARK MODE OVERRIDES ============ */
        html.dark-theme .setor-header .page-title h1 {
            color: #f8fafc;
        }

        html.dark-theme .search-box {
            background: #1e293b;
            border-color: #334155;
        }

        html.dark-theme .search-box input {
            color: #f8fafc;
        }

        html.dark-theme .setor-filters select {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        html.dark-theme .setor-table-wrapper {
            background: #1e293b;
            border-color: #334155;
        }

        html.dark-theme .setor-table thead th {
            background: #0f172a;
            border-bottom-color: #334155;
            color: #94a3b8;
        }

        html.dark-theme .setor-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        html.dark-theme .setor-table tbody td {
            border-bottom-color: #334155;
            color: #cbd5e1;
        }

        html.dark-theme .setor-nome,
        html.dark-theme .setor-count {
            color: #f8fafc;
        }

        html.dark-theme .epi-icon-badge {
            background: #334155;
            color: #cbd5e1;
        }

        html.dark-theme .btn-edit {
            background: #334155;
            border-color: #475569;
        }

        html.dark-theme .btn-edit:hover {
            background: rgba(227, 6, 19, 0.1);
        }

        html.dark-theme .btn-delete {
            background: #334155;
            border-color: #7f1d1d;
        }

        html.dark-theme .btn-delete:hover {
            background: #7f1d1d;
        }

        /* Risk Badges in Dark Mode */
        html.dark-theme .risk-badge.baixo {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        html.dark-theme .risk-badge.medio {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }

        html.dark-theme .risk-badge.alto {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        /* Modal Dark Mode */
        html.dark-theme .modal-setor {
            background: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }

        html.dark-theme .modal-setor-header h2 {
            color: #f8fafc;
        }

        html.dark-theme .form-label {
            color: #cbd5e1;
        }

        html.dark-theme .form-input {
            background: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }

        html.dark-theme .upload-area {
            border-color: #334155;
            background: rgba(255, 255, 255, 0.02);
        }

        html.dark-theme .epi-card {
            background: #0f172a;
            border-color: #334155;
        }

        html.dark-theme .epi-card-icon {
            background: #1e293b;
            color: #94a3b8;
        }

        html.dark-theme .epi-card-info .epi-card-name {
            color: #f8fafc;
        }

        html.dark-theme .epi-card.selected {
            background: rgba(227, 6, 19, 0.08);
            border-color: var(--primary);
        }

        html.dark-theme .epi-card.selected .epi-card-icon {
            background: rgba(227, 6, 19, 0.15);
            color: var(--primary);
        }

        html.dark-theme .modal-setor-footer {
            border-top-color: #334155;
        }

        html.dark-theme .btn-cancel {
            color: #94a3b8;
        }

        html.dark-theme .btn-cancel:hover {
            color: #f8fafc;
        }

        html.dark-theme .employees-list-container {
            background: #0f172a;
            border-color: #334155;
        }

        html.dark-theme .employee-item {
            border-bottom-color: #334155;
            color: #cbd5e1;
        }
    </style>
';

ob_start();
?>

<!-- Header -->
<div class="setor-header">
    <div class="page-title">
        <h1><?= __('Gestão de Setor') ?></h1>
        <p><?= __('Gerencie as áreas e os respectivos EPIs obrigatórios') ?></p>
    </div>
</div>

<!-- Filtros -->
<form action="<?= BASE_PATH ?>/management/departments" method="GET" class="setor-filters" id="filterForm">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInputSettings" name="search" placeholder="<?= __('Pesquisar setores...') ?>" oninput="filterSetores()" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>

    <!-- Hidden Fields for Filters -->
    <input type="hidden" name="status" id="hiddenStatus" value="<?= htmlspecialchars($filters['status'] ?? 'todos') ?>">
    <input type="hidden" name="risk" id="hiddenRisk" value="<?= htmlspecialchars($filters['risk'] ?? 'todos') ?>">

    <!-- Modern Triggers -->
    <div class="modern-picker-trigger" onclick="openModernPicker('status')">
        <i class="fa-solid fa-circle-check"></i>
        <div class="trigger-info">
            <span class="trigger-label"><?= __('Status') ?></span>
            <span class="trigger-value" id="label-status">
                <?php 
                $statusLabels = [
                    'todos' => __('Todos os Status'), 
                    'ativo' => __('Ativos'), 
                    'inativo' => __('Inativos')
                ];
                echo $statusLabels[$filters['status'] ?? 'todos'] ?? 'Todos';
                ?>
            </span>
        </div>
        <i class="fa-solid fa-chevron-down"></i>
    </div>

    <div class="modern-picker-trigger" onclick="openModernPicker('risk')">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div class="trigger-info">
            <span class="trigger-label"><?= __('Risco') ?></span>
            <span class="trigger-value" id="label-risk">
                <?php 
                $riskLabels = [
                    'todos' => __('Todos os Riscos'), 
                    'baixo' => __('Baixo (< 5%)'), 
                    'medio' => __('Médio (5% - 10%)'), 
                    'alto' => __('Alto (>= 10%)')
                ];
                echo $riskLabels[$filters['risk'] ?? 'todos'] ?? 'Todos';
                ?>
            </span>
        </div>
        <i class="fa-solid fa-chevron-down"></i>
    </div>
    
    <!-- Botão Adicionar Ajustado para a mesma linha -->
    <button type="button" class="btn-add-setor" onclick="openModal()">
        <i class="fa-solid fa-plus"></i> <?= __('Adicionar Setor') ?>
    </button>

    <button type="submit" style="display: none;"></button>
</form>

<!-- Tabela -->
<div class="setor-table-wrapper">
    <table class="setor-table">
        <thead>
            <tr>
                <th><?= __('NOME DO SETOR') ?></th>
                <th><?= __('FUNCIONÁRIOS ATIVOS') ?></th>
                <th><?= __('EPIS OBRIGATÓRIOS') ?></th>
                <th><?= __('RISCO GERAL') ?></th>
                <th><?= __('STATUS') ?></th>
                <th style="text-align: right;"><?= __('AÇÕES') ?></th>
            </tr>
        </thead>
        <tbody id="setoresTableBody">
            <?php if (empty($setores)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                        <i class="fa-solid fa-folder-open" style="font-size: 24px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                        <?= __('Nenhum setor encontrado no banco de dados.') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($setores as $setor): ?>
                    <tr>
                        <td>
                            <div class="setor-nome"><?= htmlspecialchars(__db($setor, 'nome')) ?></div>
                            <div class="setor-desc"><?= htmlspecialchars($setor['sigla'] ?: __('Sem sigla')) ?></div>
                        </td>
                        <td><span class="setor-count"><?= $setor['total_funcionarios'] ?></span></td>
                        <td>
                            <div class="epi-icons">
                                <?php 
                                $epiIconsMap = [
                                    'capacete' => 'fa-hard-hat'
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
                            $riskLabel = __('Baixo');
                            $riskDesc = __('Risco controlado (0% - 5%). Indica que a vasta maioria dos colaboradores segue as normas de segurança.');
                            
                            if ($risk >= 10) {
                                $riskClass = 'alto';
                                $riskLabel = __('Alto');
                                $riskDesc = __('Risco crítico (> 10%). Exige intervenção imediata, novos treinamentos e fiscalização rigorosa.');
                            } elseif ($risk >= 5) {
                                $riskClass = 'medio';
                                $riskLabel = __('Médio');
                                $riskDesc = __('Atenção necessária (5% - 10%). Sinais de reincidência ou falta de uso ocasional de EPI.');
                            }
                            ?>
                            <span class="risk-badge <?= $riskClass ?>" title="<?= $riskDesc ?> (<?= number_format((float)$risk, 1) ?>%)">
                                <?= $riskLabel ?> (<?= number_format((float)$risk, 1) ?>%)
                            </span>
                        </td>
                        <td>
                            <div class="setor-actions">
                                <span class="status-indicator" title="<?= $setor['status'] === 'ATIVO' ? __('Ativo') : __('Inativo') ?>" style="background: <?= $setor['status'] === 'ATIVO' ? '#10b981' : '#ef4444' ?>;"></span>
                            </div>
                        </td>
                        <td>
                            <div class="setor-actions">
                                <button class="btn-edit" title="<?= __('Editar') ?>" onclick="editSetor(this)" data-id="<?= $setor['id'] ?>" data-nome="<?= htmlspecialchars($setor['nome']) ?>" data-nome-en="<?= htmlspecialchars($setor['nome_en'] ?? '') ?>"><i class="fa-solid fa-pen"></i></button>
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
            <h2 id="modalTitle"><?= __('Adicionar Setor') ?></h2>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>

        <!-- Nome do Setor -->
        <div class="form-group">
            <label class="form-label"><?= __('Nome do Setor (Português)') ?></label>
            <input class="form-input" type="text" id="inputNomeSetor" placeholder="Ex: Soldagem TIG">
        </div>

        <!-- Nome do Setor (English) - REMOVIDO PARA AUTOMAÇÃO -->
        <input type="hidden" id="inputNomeEnSetor">

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
                <div class="epi-card selected" onclick="toggleEpi(this)" data-epi="capacete">
                    <div class="epi-card-icon"><i class="fa-solid fa-hard-hat"></i></div>
                    <div class="epi-card-info">
                        <div class="epi-card-name"><?= __('Capacete de Segurança') ?></div>
                        <div class="epi-card-brands">3M, MSA</div>
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
(function() {
    const BASE_PATH_LOCAL = '<?= BASE_PATH ?>';
    let editingRow = null;
    let currentSectorId = null;
    let importedEmployees = [];

    function initPage() {
        const fileInput = document.getElementById('fileUpload');
        if (fileInput) {
            // Remover listener antigo se houver para evitar duplicatas
            fileInput.removeEventListener('change', handleFileUpload);
            fileInput.addEventListener('change', handleFileUpload);
        }
        
        // Re-renderizar ícones se necessário
        if (window.lucide) lucide.createIcons();
    }

    async function handleFileUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

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
                alert('<?= __('Nenhum funcionário encontrado no arquivo. Verifique a estrutura.') ?>');
                feedback.style.display = 'none';
            }
        } catch (err) {
            console.error(err);
            alert('<?= __('Erro ao processar arquivo:') ?> ' + err.message);
        }
    }

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
            const nome = row.querySelector('.btn-edit').getAttribute('data-nome');
            const nomeEn = row.querySelector('.btn-edit').getAttribute('data-nome-en');
            document.getElementById('inputNomeSetor').value = nome;
            document.getElementById('inputNomeEnSetor').value = nomeEn || '';
            
            // Marcar EPIs já selecionados
            const rowEpis = Array.from(row.querySelectorAll('.epi-icon-badge')).map(b => b.getAttribute('data-epi'));
            document.querySelectorAll('.epi-card').forEach(card => {
                if (rowEpis.includes(card.getAttribute('data-epi'))) {
                    card.classList.add('selected');
                }
            });

            editingRow = row;
            title.textContent = '<?= __('Editar Setor') ?>';
            btn.textContent = '<?= __('Atualizar Setor') ?>';

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
            title.textContent = '<?= __('Adicionar Setor') ?>';
            btn.textContent = '<?= __('Criar Setor') ?>';
        }
    }

    function closeModal() {
        const modal = document.getElementById('modalSetor');
        if (!modal) return;
        modal.classList.remove('active');
        document.getElementById('inputNomeSetor').value = '';
        document.getElementById('inputNomeEnSetor').value = '';
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

        // Inteligência de tradução automática para setores comuns
        const translationMap = {
            'soldagem': 'Welding',
            'montagem': 'Assembly',
            'logística': 'Logistics',
            'logistica': 'Logistics',
            'pintura': 'Painting',
            'administração': 'Administration',
            'administracao': 'Administration',
            'financeiro': 'Financial',
            'rh': 'HR',
            'recursos humanos': 'Human Resources',
            'ti': 'IT',
            'tecnologia': 'Technology',
            'qualidade': 'Quality',
            'produção': 'Production',
            'producao': 'Production',
            'estoque': 'Stock',
            'manutenção': 'Maintenance',
            'manutencao': 'Maintenance',
            'recepção': 'Reception',
            'recepcao': 'Reception',
            'expedição': 'Expedition',
            'expedicao': 'Expedition'
        };

        // Tenta traduzir ou usa o original se não encontrar no mapa
        const lowerNome = nome.toLowerCase().trim();
        let nomeEn = translationMap[lowerNome] || nome;
        
        // Pequena lógica extra: se terminar com " TIG" ou " MIG", mantém o sufixo no inglês
        if (lowerNome.includes('soldagem tig')) nomeEn = 'TIG Welding';
        if (lowerNome.includes('soldagem mig')) nomeEn = 'MIG Welding';

        if (!nome) {
            alert('<?= __('Por favor, informe o nome do setor.') ?>');
            return;
        }

        const formData = {
            nome: nome,
            nome_en: nomeEn,
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
                alert(currentSectorId ? '<?= __('Setor atualizado!') ?>' : '<?= __('Setor criado com sucesso!') ?>');
                location.reload();
            } else {
                alert('<?= __('Erro') ?>: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('<?= __('Erro na comunicação com o servidor.') ?>');
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

    // --- Deletar Setor (Premium Modal) ---
    let deleteTargetId = null;

    function deleteSetor(btn) {
        deleteTargetId = btn.getAttribute('data-id');
        const modal = document.getElementById('confirmDeleteModal');
        if (modal) {
            modal.classList.add('active');
            toggleScroll(true);
        }
    }

    function closeDeleteModal() {
        const modal = document.getElementById('confirmDeleteModal');
        if (modal) {
            modal.classList.remove('active');
            toggleScroll(false);
        }
        deleteTargetId = null;
    }

    async function doDeleteSetor() {
        if (!deleteTargetId) return;
        
        const btn = document.getElementById('btnConfirmDelete');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.querySelector('.btn-text').innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <?= __('Excluindo...') ?>`;

        try {
            const response = await fetch(`${BASE_PATH_LOCAL}/api/departments/delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: deleteTargetId })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Erro: ' + result.message);
                closeDeleteModal();
            }
        } catch (err) {
            console.error(err);
            closeDeleteModal();
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    function editSetor(btn) {
        const row = btn.closest('tr');
        openModal(true, row);
    }

    // Exposição Global para onclick do HTML
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.toggleEpi = toggleEpi;
    window.criarSetor = criarSetor;
    window.filterSetores = filterSetores;
    window.deleteSetor = deleteSetor;
    window.closeDeleteModal = closeDeleteModal;
    window.doDeleteSetor = doDeleteSetor;
    window.editSetor = editSetor;
    window.removeImported = removeImported;

    // Listeners de inicialização
    document.addEventListener('DOMContentLoaded', initPage);
    document.addEventListener('spaPageLoaded', initPage);
    initPage();
})();
</script>

<!-- Modern Picker Modal (Apple Style) -->
<div class="modern-picker-modal" id="modernPicker">
    <div class="modern-picker-backdrop"></div>
    <div class="modern-picker-container">
        <div class="modern-picker-header">
            <h3 id="pickerTitle"><?= __('Selecionar') ?></h3>
            <p id="pickerSubtitle"><?= __('Escolha uma opção abaixo') ?></p>
        </div>
        <div class="modern-picker-options" id="pickerOptionsContainer"></div>
        <button class="modern-picker-close" onclick="closeModernPicker()"><?= __('Cancelar') ?></button>
    </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/picker.js"></script>

<script>
    // Opções para o Picker Moderno (Setores)
    window.PICKER_OPTIONS = {
        status: [
            { value: 'todos', label: '<?= __('Todos os Status') ?>' },
            { value: 'ativo', label: '<?= __('Ativos') ?>' },
            { value: 'inativo', label: '<?= __('Inativos') ?>' }
        ],
        risk: [
            { 
                value: 'todos', 
                label: '<?= __('Todos os Riscos') ?>',
                description: '<?= __('Exibe todos os setores independente do nível de risco.') ?>'
            },
            { 
                value: 'baixo', 
                label: '<?= __('Baixo (< 5%)') ?>',
                description: '<?= __('Risco controlado. Majoritária conformidade com as normas de segurança.') ?>'
            },
            { 
                value: 'medio', 
                label: '<?= __('Médio (5% - 10%)') ?>',
                description: '<?= __('Atenção necessária. Sinais de reincidência ou falta de uso de EPI.') ?>'
            },
            { 
                value: 'alto', 
                label: '<?= __('Alto (>= 10%)') ?>',
                description: '<?= __('Risco crítico. Exige intervenção imediata e novos treinamentos.') ?>'
            }
        ]
    };
</script>

<!-- Modal Confirmar Exclusão (Premium) -->
<div class="modal-premium" id="confirmDeleteModal">
    <div class="modal-confirmation-content">
        <i class="fa-solid fa-triangle-exclamation main-icon" style="color: #f59e0b;"></i>
        <h2><?= __('Deseja desativar este setor?') ?></h2>
        <p><?= __('O setor deixará de aparecer no monitoramento, mas os dados históricos serão preservados.') ?></p>
        
        <div class="confirmation-actions" style="margin-top: 30px;">
            <button class="btn-liquid" id="btnConfirmDelete" onclick="doDeleteSetor()">
                <div class="btn-text">
                    <i class="fa-solid fa-check"></i>
                    <span><?= __('Confirmar') ?></span>
                </div>
                <div class="liquid"></div>
            </button>
            <button class="btn-light-shadow" onclick="closeDeleteModal()" style="margin-top: 10px;">
                <?= __('Cancelar') ?>
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

<?php
$userName = $_SESSION['user_nome'] ?? 'Arthur';
$userRole = $_SESSION['user_cargo'] ?? 'Gestor de Segurança';
$userEmail = $_SESSION['user_email'] ?? 'arthur@facchini.com.br';

// Calcular iniciais para o avatar
$nameParts = explode(' ', trim($userName));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
} else {
    $initials = strtoupper(substr($userName, 0, 2));
}

// Mapeamento de cargos para exibição amigável (Setor)
$roleDisplay = [
    'ADMIN' => __('Administrador do Sistema'),
    'SUPER_ADMIN' => __('Administrador Master'),
    'OPERATOR' => __('Supervisor de Operações'),
    'SUPERVISOR' => __('Supervisor de Segurança'),
    'MANAGER' => __('Gerente de Segurança'),
    'GERENTE_SEGURANCA' => __('Gerente de Segurança'),
    'VIEWER' => __('Observador')
];
$displayRole = $roleDisplay[strtoupper($userRole)] ?? $userRole;
?>
<header class="header">
    <div id="epi-parade" class="epi-parade">
        <i class="fa-solid fa-helmet-safety"></i>
        <i class="fa-solid fa-glasses"></i>
        <i class="fa-solid fa-vest"></i>
        <i class="fa-solid fa-helmet-safety"></i>
        <i class="fa-solid fa-glasses"></i>
        <i class="fa-solid fa-vest"></i>
    </div>
    <div class="page-title">
        <div id="welcome-truck-container" class="welcome-container">
            <span class="truck-icon"><i class="fa-solid fa-truck-moving"></i></span>
            <p class="welcome-text"><?= __('Olá') ?>, <?= __('bem-vindo de volta!') ?>
            </p>
        </div>
    </div>

    <div class="header-actions">

        <!-- Export Button (Only for Dashboard) -->
        <button class="header-export-btn" id="headerExportDashboardBtn" style="display: none;"
            onclick="exportDashboardData && exportDashboardData()">
            <i class="fa-solid fa-download"></i> <span><?= __('Exportar') ?></span>
        </button>

        <!-- Notificações Dropdown -->
        <div style="position: relative; display: flex;">
            <!-- Botão principal do sino com o contador -->
            <button class="header-icon-btn notification-btn" id="notifBtn">
                <i data-lucide="bell"></i>
                <span class="notification-badge visible" id="notifBadge" style="display:none;">0</span>
            </button>

            <!-- Modal (Dropdown) -->
            <div class="notification-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <span><?= __('Notificações') ?></span>
                    <button class="notif-clear-btn" id="notifClearBtn" style="display:none;"><?= __('Lidas') ?></button>
                </div>

                <div class="notif-list" id="notifList">
                </div>

                <div class="notif-dropdown-footer">
                    <a href="<?= BASE_PATH ?>/infractions" class="notif-view-all">
                        <?= __('Ver todas as notificações') ?> <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>



        <!-- Filial Switcher -->
        <?php
        $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
        $db = \Facchini\Infrastructure\Database\Connection::getInstance();
        $filiaisRes = $db->query("SELECT id, nome FROM filiais ORDER BY id ASC");
        $filiais = [];
        $activeFilialName = 'Aparecida do Taboado';
        
        if ($filiaisRes) {
            while ($f = $filiaisRes->fetch_assoc()) {
                $filiais[] = $f;
                if ($f['id'] == $activeFilialId)
                    $activeFilialName = $f['nome'];
            }
        } else {
            // Fallback default if table missing
            $filiais = [['id' => 1, 'nome' => 'Aparecida do Taboado']];
        }
        ?>
        <div style="position: relative;">
            <button class="header-branch-btn" onclick="openBranchModal()">
                <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i>
                <span style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= htmlspecialchars($activeFilialName) ?>
                </span>
                <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: auto;"></i>
            </button>
        </div>

        <div style="position: relative;" id="profileContainer">
            <div class="user-profile-trigger" id="profileTrigger">
                <div class="user-info-mini">
                    <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                    <span class="user-role"><?= htmlspecialchars($displayRole) ?></span>
                </div>
                <div class="user-avatar">
                    <?= $initials ?>
                </div>
            </div>

            <!-- Dropdown de Perfil -->
            <div class="instructor-card" id="userProfileModal"
                style="position: absolute; top: calc(100% + 5px); right: 0; width: 100%; z-index: 100000; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: none;">
                <div
                    style="display: flex; flex-direction: column; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                    <div class="user-avatar"
                        style="width: 60px; height: 60px; font-size: 24px; background: var(--primary);">
                        <?= $initials ?>
                    </div>
                    <div style="text-align: center;">
                        <h4 style="margin: 0; font-size: 16px; color: var(--secondary);">
                            <?= htmlspecialchars($userName) ?>
                        </h4>
                        <span
                            style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($displayRole) ?></span>
                    </div>
                </div>

                <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                    <div class="detail-row"
                        style="padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
                        <span class="detail-label"><?= __('Setor') ?></span>
                        <span
                            class="detail-value"><?= htmlspecialchars($_SESSION['user_setor'] ?? __('Não atribuído')) ?></span>
                    </div>
                    <div class="detail-row" style="padding: 8px 0; border: none; font-size: 13px;">
                        <span class="detail-label"><?= __('E-mail') ?></span>
                        <span class="detail-value"
                            style="font-size: 11px; word-break: break-all;"><?= htmlspecialchars($userEmail) ?></span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="<?= BASE_PATH ?>/logout" class="btn-liquid"
                        style="padding: 10px; font-size: 12px; text-decoration: none; display: block; text-align: center;">
                        <span class="btn-text">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> <?= __('Sair') ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    function openBranchModal() {
        const modal = document.getElementById('branchSelectionModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }
    }

    function closeBranchModal() {
        const modal = document.getElementById('branchSelectionModal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 400);
        }
    }

    function filterBranches(val) {
        const term = val.toLowerCase();
        const rows = document.querySelectorAll('.branch-selection-row');
        rows.forEach(row => {
            const name = row.getAttribute('data-name').toLowerCase();
            row.style.display = name.includes(term) ? 'flex' : 'none';
        });
    }

    function switchBranch(id) {
        fetch('<?= BASE_PATH ?>/api/branch/switch', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filial_id: id })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
    }

    // Fechar ao clicar fora
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('branchSelectionModal');
        if (modal && e.target === modal) {
            closeBranchModal();
        }
    });
</script>

<!-- CSS Premium para o Modal de Filial (Extraído do Dashboard) -->
<style>
    .modal-premium {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 1000000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-premium.active {
        opacity: 1;
    }

    .modal-premium-content {
        background: white;
        width: 90%;
        max-width: 600px;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        border-top: 4px solid var(--primary);
        transform: translateY(20px);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .modal-premium.active .modal-premium-content {
        transform: translateY(0);
    }

    .modal-premium-header {
        padding: 24px 30px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        background: linear-gradient(to right, rgba(227, 6, 19, 0.03), transparent);
    }

    .modal-premium-header h2 {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .modal-premium-header p {
        font-size: 14px;
        color: #64748b;
        margin: 4px 0 0 0;
    }

    .close-premium {
        background: #f1f5f9;
        border: none;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-premium:hover {
        background: #fee2e2;
        color: var(--primary);
        transform: rotate(90deg);
    }

    .modal-premium-body {
        padding: 0;
    }

    .modal-search-wrapper {
        padding: 20px 30px 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 16px;
    }

    .search-input-group input {
        width: 100%;
        padding: 12px 12px 12px 40px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        background: #f8fafc;
        transition: all 0.2s;
    }

    .search-input-group input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(227, 6, 19, 0.05);
    }

    .modal-selection-list {
        max-height: 450px;
        overflow-y: auto;
    }

    .branch-selection-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 30px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .branch-selection-row:hover {
        background: #f8fafc;
    }
    .branch-selection-row.active {
        background: rgba(227, 6, 19, 0.02);
    }

    .selection-main {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .sector-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sector-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #cbd5e1;
        transition: all 0.3s;
    }

    .active .sector-dot, .branch-selection-row:hover .sector-dot {
        background: var(--primary);
        transform: scale(1.2);
    }

    .branch-name {
        font-size: 15px;
        font-weight: 700;
        color: #334155;
    }

    .active .branch-name {
        color: var(--secondary);
    }

    .status-tag {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #64748b;
    }

    .status-tag.active {
        background: #ecfdf5;
        color: #059669;
    }

    .modal-premium-footer {
        padding: 20px 30px;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
    }

    .btn-apply-filter {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-apply-filter:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(227, 6, 19, 0.2);
    }

    /* Dark Mode */
    html.dark-theme .modal-premium-content {
        background: #1e293b;
    }
    html.dark-theme .modal-premium-header, 
    html.dark-theme .modal-search-wrapper,
    html.dark-theme .branch-selection-row,
    html.dark-theme .modal-premium-footer {
        border-color: #334155;
    }
    html.dark-theme .modal-premium-header h2 { color: #f8fafc; }
    html.dark-theme .branch-name { color: #cbd5e1; }
    html.dark-theme .search-input-group input { background: #0f172a; border-color: #334155; color: white; }
    html.dark-theme .search-input-group input:focus { background: #1e293b; border-color: var(--primary); }
    html.dark-theme .status-tag { background: #334155; }
    html.dark-theme .modal-premium-footer { background: #0f172a; }
    html.dark-theme .branch-selection-row:hover { background: #0f172a; }
    html.dark-theme .branch-selection-row.active { background: rgba(227, 6, 19, 0.15); border-left: 4px solid var(--primary); }
    html.dark-theme .active .branch-name { color: white; }
</style>

<!-- Modal de Seleção de Filial PREMIUM -->
<div id="branchSelectionModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="modal-premium-header">
            <div>
                <h2><?= __('selecionar filial') ?></h2>
                <p><?= __('Filtre os dados do sistema por unidade fabril específica') ?></p>
            </div>
            <button class="close-premium" onclick="closeBranchModal()">×</button>
        </div>
        <div class="modal-premium-body">
            <!-- Barra de Pesquisa -->
            <div class="modal-search-wrapper">
                <div class="search-input-group">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="branchSearchInput" placeholder="<?= __('Pesquisar filial...') ?>"
                        onkeyup="filterBranches(this.value)">
                </div>
            </div>

            <div class="modal-selection-list">
                <?php foreach ($filiais as $branch): ?>
                    <div class="branch-selection-row <?= $branch['id'] == $activeFilialId ? 'active' : '' ?>" 
                         data-name="<?= htmlspecialchars($branch['nome']) ?>"
                         onclick="switchBranch(<?= $branch['id'] ?>)">
                        <div class="selection-main">
                            <div class="sector-cell">
                                <div class="sector-dot <?= $branch['id'] == $activeFilialId ? 'global' : '' ?>"></div>
                                <span class="branch-name"><?= htmlspecialchars($branch['nome']) ?></span>
                            </div>
                        </div>
                        <span class="status-tag <?= $branch['id'] == $activeFilialId ? 'active' : '' ?>">
                            <?= $branch['id'] == $activeFilialId ? __('Unidade Ativa') : __('Monitorado') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="modal-premium-footer">
            <button class="btn-apply-filter" onclick="closeBranchModal()">
                <span><?= __('Fechar') ?></span>
                <i class="fa-solid fa-check"></i>
            </button>
        </div>
    </div>
</div>
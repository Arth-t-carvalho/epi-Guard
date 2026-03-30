<?php
$pageTitle = 'epiGuard - Registrar Ocorrência';

// Função auxiliar para mapear ícones baseados no nome do EPI
function getEpiIcon($name) {
    $name = strtolower($name);
    if (strpos($name, 'capacete') !== false) return 'fa-helmet-safety';
    if (strpos($name, 'óculos') !== false || strpos($name, 'oculos') !== false) return 'fa-glasses';
    if (strpos($name, 'luva') !== false) return 'fa-mitten';
    if (strpos($name, 'bota') !== false || strpos($name, 'calçado') !== false || strpos($name, 'calcado') !== false) return 'fa-boot';
    if (strpos($name, 'auricular') !== false || strpos($name, 'abafador') !== false || strpos($name, 'ouvido') !== false) return 'fa-ear-deaf';
    if (strpos($name, 'colete') !== false) return 'fa-vest';
    if (strpos($name, 'máscara') !== false || strpos($name, 'mascara') !== false) return 'fa-mask-face';
    if (strpos($name, 'avental') !== false) return 'fa-shirt';
    if (strpos($name, 'cinto') !== false || strpos($name, 'talabarte') !== false) return 'fa-bezier-curve';
    if (strpos($name, 'perneira') !== false) return 'fa-shoe-prints';
    return 'fa-headset'; // Ícone padrão caso não encontre correspondência
}
$extraHead = '
    <link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">
<style>
    /* Ocultar boas-vindas e scroll da pagina */
    .welcome-container { display: none !important; }
    .main-content { overflow-y: auto !important; }
    #page-content-wrapper { margin-top: -50px; }

    .registration-container {
        display: flex;
        justify-content: center;
        padding: 20px 0;
        margin-top: 16px;
    }
    .registration-card {
        background: var(--bg-card, #ffffff);
        border-radius: var(--radius, 16px);
        width: 100%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border, #e5e7eb);
        overflow: hidden;
        animation: slideUp 0.4s ease-out;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card-header-premium {
        padding: 24px 32px;
        border-bottom: 1px solid var(--border, #e5e7eb);
        display: flex;
        align-items: center;
        gap: 16px;
        background: #E30613;
    }
    .card-header-premium div h2 {
        font-size: 18px;
        font-weight: 800;
        color: #fff;
        margin: 0;
    }
    .card-header-premium div p {
        font-size: 12px;
        color: #fff;
        margin: 2px 0 0 0;
    }
    .card-body-premium {
        padding: 32px;
    }
    .card-footer-premium {
        padding: 20px 32px;
        border-top: 1px solid var(--border, #e5e7eb);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #fafafa;
    }

    /* Form Styles */
    .occurrence-form .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    .occurrence-form .form-row.three-cols {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }
    .occurrence-form .form-group {
        display: flex;
        flex-direction: column;
    }
    .occurrence-form .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 8px;
    }
    .occurrence-form .form-input {
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
    .occurrence-form .form-input::placeholder { color: #94a3b8; }
    .occurrence-form .form-input:focus {
        border-color: #E30613;
        box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.08);
    }
    .occurrence-form textarea.form-input {
        resize: vertical;
        min-height: 100px;
    }
    .section-divider {
        margin: 24px 0 16px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }
    .section-divider h5 {
        font-size: 14px;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Upload Grid */
    .upload-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 12px;
    }
    .upload-btn {
        aspect-ratio: 1/1;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: 0.2s;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
    }
    .upload-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(227, 6, 19, 0.02);
    }
    .upload-btn i { width: 24px; height: 24px; }

    /* Buttons */
    .btn-cancel-occ {
        padding: 10px 22px;
        border: 1px solid var(--border, #e5e7eb);
        background: white;
        color: var(--text-muted, #94a3b8);
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        font-size: 13px;
        font-family: "Inter", sans-serif;
    }
    .btn-cancel-occ:hover { background: #fef2f2; color: var(--primary); border-color: var(--primary); }
    .btn-confirm {
        padding: 10px 24px;
        background: var(--primary, #E30613);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        font-size: 13px;
        font-family: "Inter", sans-serif;
    }
    .btn-confirm:hover { background: #c20510; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(227, 6, 19, 0.2); }
    .btn-confirm:disabled { opacity: 0.7; cursor: not-allowed; }

    /* Success Modal */
    #success-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    #success-overlay.active { display: flex; opacity: 1; }
    .success-card {
        background: white;
        padding: 40px;
        border-radius: 24px;
        text-align: center;
        max-width: 380px;
        width: 90%;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        transform: scale(0.8);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    #success-overlay.active .success-card { transform: scale(1); }
    .success-icon-wrapper {
        width: 72px; height: 72px;
        background: #f0fdf4; color: #16a34a;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px auto;
    }
    .success-card h3 { font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 8px; }
    .success-card p { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; line-height: 1.5; }
    .btn-success-ok {
        padding: 12px 32px;
        background: var(--secondary, #1F2937);
        color: white; border: none;
        border-radius: 12px; font-weight: 700;
        cursor: pointer; transition: 0.2s; width: 100%;
    }
    .btn-success-ok:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }

    /* Employee Details Table */
    .employee-details-container {
        margin-top: 15px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        display: none;
        animation: fadeInDetails 0.3s ease;
    }
    .employee-details-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .employee-details-table th {
        background: #f1f5f9;
        text-align: left;
        padding: 10px 16px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
    }
    .employee-details-table td {
        padding: 12px 16px;
        color: #1e293b;
        font-weight: 600;
        border-bottom: 1px solid #f1f5f9;
    }
    .employee-details-table tr:last-child td { border-bottom: none; }
    @keyframes fadeInDetails {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Evidence preview */
    .evidence-preview {
        aspect-ratio: 1/1;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        position: relative;
        transition: transform 0.2s;
    }
    .evidence-preview:hover {
        transform: scale(1.05);
        cursor: pointer;
        border-color: var(--primary);
    }
    .evidence-preview img {
        width: 100%; height: 100%; object-fit: cover;
    }

    /* Modern Picker Styles (Centered Modal Variation) */
    .modern-picker-trigger {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border, #e5e7eb);
        padding: 12px 18px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modern-picker-trigger:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(227, 6, 19, 0.08);
        transform: translateY(-1px);
    }
    .modern-picker-trigger.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f1f5f9;
        pointer-events: none;
    }
    .modern-picker-trigger i {
        font-size: 18px;
        color: var(--primary);
    }
    .modern-picker-trigger .trigger-info {
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .modern-picker-trigger .trigger-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #94a3b8;
        font-weight: 800;
        margin-bottom: 2px;
    }
    .modern-picker-trigger .trigger-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main, #1F2937);
    }
    .modern-picker-trigger i.fa-chevron-right {
        font-size: 12px;
        color: #cbd5e1;
    }

    /* Modal Picker Overrides */
    .modal-premium {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        animation: fadeInOverlay 0.3s ease;
    }
    .modal-premium.active { display: flex; }
    
    .modal-premium-content {
        background: var(--bg-card, #fff);
        width: 100%;
        max-width: 460px;
        border-radius: 28px;
        box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: modalSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes modalSlideUp {
        from { transform: scale(0.9) translateY(40px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .modal-search-wrapper {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .modal-search-input {
        width: 100%;
        padding: 12px 16px 12px 40px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        background: white;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Ccircle cx=\'11\' cy=\'11\' r=\'8\'%3E%3C/circle%3E%3Cline x1=\'21\' y1=\'21\' x2=\'16.65\' y2=\'16.65\'%3E%3C/line%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 14px center;
        outline: none;
        transition: 0.2s;
    }

    .modal-search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.08);
    }

    .grid-modal-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px 24px;
        max-height: 420px;
        overflow-y: auto;
    }

    /* Scrollbar minimalista */
    .grid-modal-list::-webkit-scrollbar { width: 5px; }
    .grid-modal-list::-webkit-scrollbar-track { background: transparent; }
    .grid-modal-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .picker-item-btn {
        background: #f8fafc;
        border: 1px solid transparent;
        padding: 14px 18px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .picker-item-btn:hover {
        border-color: var(--primary);
        background: var(--primary-light, #fef2f2);
        transform: translateX(4px);
    }

    .picker-icon-box {
        width: 36px; height: 36px;
        background: white;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #94a3b8;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
    }

    .picker-item-btn:hover .picker-icon-box { color: var(--primary); }

    .picker-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .picker-name {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }

    .picker-sub {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }

    .empty-search-state {
        padding: 40px 24px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 600;
    }

    /* Dark Mode Picker */
    html.dark-theme .modal-premium-content { background: #0f172a; border: 1px solid var(--border); }
    html.dark-theme .modal-search-wrapper { background: #1e293b; border-color: var(--border); }
    html.dark-theme .modal-search-input { background-color: #0f172a; border-color: var(--border); color: white; }
    html.dark-theme .picker-item-btn { background: #1e293b; border-color: transparent; }
    html.dark-theme .picker-name { color: var(--text-main); }
    html.dark-theme .picker-icon-box { background: #0f172a; border: 1px solid var(--border); }
    html.dark-theme .picker-item-btn:hover { background: rgba(227, 6, 19, 0.1); }
    html.dark-theme .grid-modal-list::-webkit-scrollbar-thumb { background: var(--border); }
</style>
';
ob_start();
?>

<!-- Global Variables for JS -->
<script>
    window.BASE_PATH = '<?= BASE_PATH ?>';
</script>

<div class="registration-container">
    <div class="registration-card">
        <div class="card-header-premium">
            <div>
                <h2>Registrar Ocorrência</h2>
                <p>Preencha os dados abaixo para registrar uma nova ocorrência de segurança.</p>
            </div>
        </div>

        <div class="card-body-premium">
            <form class="occurrence-form" id="occurrence-form">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Setor</label>
                        <div class="modern-picker-trigger" id="sector-trigger-btn" onclick="openSectorModal()">
                            <i class="fa-solid fa-building-circle-check"></i>
                            <div class="trigger-info">
                                <span class="trigger-label">Selecione o Local</span>
                                <span class="trigger-value" id="sector-display-value">Escolha um setor...</span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                        <input type="hidden" id="occ-setor" name="setor_id" value="">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Funcionário</label>
                        <div class="modern-picker-trigger disabled" id="employee-trigger-btn" onclick="openEmployeeModal()">
                            <i class="fa-solid fa-user-tag"></i>
                            <div class="trigger-info">
                                <span class="trigger-label">Responsável pela Ocorrência</span>
                                <span class="trigger-value" id="employee-display-value">Selecione um setor primeiro.</span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                        <input type="hidden" id="occ-funcionario" name="funcionario_id" value="">

                        <!-- Mini Tabela de Informações do Funcionário -->
                        <div id="employee-details-box" class="employee-details-container">
                            <table class="employee-details-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome Completo</th>
                                        <th>CPF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="det-id">-</td>
                                        <td id="det-nome">-</td>
                                        <td id="det-cpf">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-row three-cols">
                    <div class="form-group">
                        <label class="form-label">Motivo Principal</label>
                        <div class="modern-picker-trigger" id="motivo-trigger-btn" onclick="openMotivoModal()">
                            <i class="fa-solid fa-clipboard-question"></i>
                            <div class="trigger-info">
                                <span class="trigger-label">Selecione a Causa</span>
                                <span class="trigger-value" id="motivo-display-value">Falta de EPI</span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                        <input type="hidden" id="occ-motivo" name="motivo_principal" value="Falta de EPI">
                    </div>
                    <div class="form-group">
                        <label class="form-label">EPI Envolvido</label>
                        <div class="modern-picker-trigger" id="epi-trigger-btn" onclick="openEpiModal()">
                            <i class="fa-solid fa-headset"></i>
                            <div class="trigger-info">
                                <span class="trigger-label">Selecione o EPI</span>
                                <span class="trigger-value" id="epi-display-value">Nenhum</span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                        <input type="hidden" id="occ-epi" name="epi_id" value="none">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data e Hora</label>
                        <div class="modern-picker-trigger" id="date-trigger-btn" onclick="openDateModal()">
                            <i class="fa-solid fa-calendar-alt"></i>
                            <div class="trigger-info">
                                <span class="trigger-label">Horário do Registro</span>
                                <span class="trigger-value" id="date-display-value"><?= date('d/m/Y H:i') ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                        <input type="hidden" id="occ-data" name="data_hora" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>

                <div class="section-divider">
                    <h5>Ação Tomada</h5>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Tipo de Registro / Advertência</label>
                        <div class="modern-picker-trigger" id="action-trigger-btn" onclick="openActionModal()">
                            <i class="fa-solid fa-gavel"></i>
                            <div class="trigger-info">
                                <span class="trigger-label">Ação Corretiva</span>
                                <span class="trigger-value" id="action-display-value">Orientação Técnica</span>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                        <input type="hidden" id="occ-tipo" name="tipo_acao" value="Orientação Técnica">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Observações Adicionais</label>
                        <textarea class="form-input" rows="4" id="occ-obs"
                            placeholder="Descreva detalhes sobre a ocorrência..."></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Evidências</label>
                        <div class="upload-grid" id="upload-grid">
                            <div class="upload-btn" id="btn-add-evidence">
                                <i data-lucide="plus"></i>
                                <span>Adicionar</span>
                            </div>
                            <input type="file" id="evidence-input" multiple accept="image/*" style="display: none;">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer-premium">
            <button type="button" class="btn-cancel-occ"
                onclick="location.href='<?= BASE_PATH ?>/dashboard'">Cancelar</button>
            <button type="button" class="btn-confirm" id="btnConfirmOcc">Confirmar Ocorrência</button>
        </div>
    </div>
</div>

<!-- Modal Centralizado para Escolha de Setor -->
<div id="sectorPickerModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="card-header-premium" style="padding: 20px 24px;">
            <div>
                <h2 style="font-size: 16px;">Selecionar Setor</h2>
                <p style="font-size: 11px;">Escolha o local onde a ocorrência foi detectada</p>
            </div>
            <button class="close-premium" onclick="closeSectorModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding: 0;">
            <div class="grid-modal-list" id="sectorModalList">
                <?php foreach ($departments as $dept): ?>
                    <div class="picker-item-btn" onclick="selectSectorModal('<?= $dept->getId() ?>', '<?= htmlspecialchars($dept->getName()) ?>')">
                        <div class="picker-icon-box">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div class="picker-info">
                            <span class="picker-name"><?= htmlspecialchars($dept->getName()) ?></span>
                            <span class="picker-sub">ID: #<?= $dept->getId() ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Centralizado para Escolha de Funcionário -->
<div id="employeePickerModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="card-header-premium" style="padding: 20px 24px;">
            <div>
                <h2 style="font-size: 16px;">Selecionar Funcionário</h2>
                <p style="font-size: 11px;" id="employeeModalSectorSubtitle">Escolha o funcionário do setor</p>
            </div>
            <button class="close-premium" onclick="closeEmployeeModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        
        <div class="modal-search-wrapper">
            <input type="text" 
                   id="employeeSearchInput" 
                   class="modal-search-input" 
                   placeholder="Pesquisar por nome ou CPF..."
                   oninput="filterEmployees(this.value)">
        </div>

        <div class="modal-premium-body" style="padding: 0;">
            <div class="grid-modal-list" id="employeeModalList">
                 <div class="empty-search-state">Selecione o setor para carregar os funcionários.</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Centralizado para Escolha de Motivo -->
<div id="motivoPickerModal" class="modal-premium">
    <div class="modal-premium-content" style="max-width: 400px;">
        <div class="card-header-premium" style="padding: 20px 24px;">
            <div>
                <h2 style="font-size: 16px;">Motivo Principal</h2>
                <p style="font-size: 11px;">Qual a causa principal desta ocorrência?</p>
            </div>
            <button class="close-premium" onclick="closeMotivoModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding: 0;">
            <div class="grid-modal-list">
                <?php 
                $motivos = [
                    'Falta de EPI', 'EPI Danificado / Desgastado', 'Recusa de Uso', 
                    'Uso Incorreto do EPI', 'EPI Fora da Validade', 'Perda de EPI', 'Outros'
                ];
                foreach ($motivos as $m): ?>
                    <div class="picker-item-btn" onclick="selectMotivoModal('<?= $m ?>')">
                        <div class="picker-icon-box">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                        <div class="picker-info">
                            <span class="picker-name"><?= $m ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Centralizado para Escolha de EPI -->
<div id="epiPickerModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="card-header-premium" style="padding: 20px 24px;">
            <div>
                <h2 style="font-size: 16px;">Selecionar EPI</h2>
                <p style="font-size: 11px;">Qual equipamento está envolvido?</p>
            </div>
            <button class="close-premium" onclick="closeEpiModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding: 0;">
            <div class="grid-modal-list">
                <div class="picker-item-btn" onclick="selectEpiModal('none', 'Nenhum')">
                    <div class="picker-icon-box"><i class="fa-solid fa-ban"></i></div>
                    <div class="picker-info"><span class="picker-name">Nenhum</span></div>
                </div>
                <?php foreach ($epis as $epi): ?>
                    <div class="picker-item-btn" onclick="selectEpiModal('<?= $epi->getId() ?>', '<?= htmlspecialchars($epi->getName()) ?>')">
                        <div class="picker-icon-box">
                            <i class="fa-solid <?= getEpiIcon($epi->getName()) ?>"></i>
                        </div>
                        <div class="picker-info">
                            <span class="picker-name"><?= htmlspecialchars($epi->getName()) ?></span>
                            <span class="picker-sub">ID: #<?= $epi->getId() ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Centralizado para Escolha de Data e Hora -->
<div id="datePickerModal" class="modal-premium">
    <div class="modal-premium-content" style="max-width: 360px;">
        <div class="card-header-premium" style="padding: 20px 24px;">
            <div>
                <h2 style="font-size: 16px;">Data e Hora</h2>
                <p style="font-size: 11px;">Quando ocorreu a infração?</p>
            </div>
            <button class="close-premium" onclick="closeDateModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding: 24px;">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Data</label>
                <input type="date" id="modal-date-input" class="form-input" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Hora</label>
                <input type="time" id="modal-time-input" class="form-input" value="<?= date('H:i') ?>">
            </div>
            <button class="btn-confirm" style="width: 100%; padding: 12px;" onclick="confirmDateModal()">Confirmar Horário</button>
        </div>
    </div>
</div>

<!-- Modal Centralizado para Tipo de Ação -->
<div id="actionPickerModal" class="modal-premium">
    <div class="modal-premium-content">
        <div class="card-header-premium" style="padding: 20px 24px;">
            <div>
                <h2 style="font-size: 16px;">Ação / Registro</h2>
                <p style="font-size: 11px;">Como esta ocorrência será registrada?</p>
            </div>
            <button class="close-premium" onclick="closeActionModal()" style="background: none; border: none; color: #fff; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-premium-body" style="padding: 0;">
            <div class="grid-modal-list">
                <?php 
                $acoes = [
                    'Orientação Técnica', 'Advertência Verbal', 'Advertência Escrita (1ª via)',
                    'Advertência Escrita (2ª via)', 'Suspensão (1 dia)', 'Suspensão (3 dias)', 'Treinamento de Reciclagem'
                ];
                foreach ($acoes as $a): ?>
                    <div class="picker-item-btn" onclick="selectActionModal('<?= $a ?>')">
                        <div class="picker-icon-box"><i class="fa-solid fa-gavel"></i></div>
                        <div class="picker-info"><span class="picker-name"><?= $a ?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sucesso Customizado -->
<div id="success-overlay">
    <div class="success-card">
        <div class="success-icon-wrapper">
            <i data-lucide="check-circle" style="width: 36px; height: 36px;"></i>
        </div>
        <h3>Ocorrência Registrada!</h3>
        <p>A ocorrência foi salva no sistema com sucesso e as evidências foram processadas.</p>
        <button class="btn-success-ok" id="btn-success-ok">Entendido</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('[Occurrences] Script carregado e DOM pronto.');
        // Inicializar ícones do Lucide
        if (window.lucide) lucide.createIcons();

        // --- LÓGICA DO SELETOR DE SETOR (CENTRALIZED MODAL) ---
        const sectorModal = document.getElementById('sectorPickerModal');
        const sectorValueDisplay = document.getElementById('sector-display-value');
        const sectorInputId = document.getElementById('occ-setor');
        const selectFunc = document.getElementById('occ-funcionario');

        window.openSectorModal = function() {
            console.log('[Occurrences] Abrindo modal de setor...');
            if (!sectorModal) return console.error('Erro: sectorPickerModal não encontrado!');
            sectorModal.classList.add('active');
            document.body.classList.add('modal-open');
        };

        window.closeSectorModal = function() {
            sectorModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        };

        window.selectSectorModal = function(id, name) {
            sectorInputId.value = id;
            sectorValueDisplay.textContent = name;
            window.closeSectorModal();
            loadEmployeesBySector(id);
        };

        // --- LÓGICA DO SELETOR DE FUNCIONÁRIO (MODAL + BUSCA) ---
        const employeeModal = document.getElementById('employeePickerModal');
        const employeeTriggerBtn = document.getElementById('employee-trigger-btn');
        const employeeValueDisplay = document.getElementById('employee-display-value');
        const employeeInputId = document.getElementById('occ-funcionario');
        const employeeListContainer = document.getElementById('employeeModalList');
        const employeeModalSubtitle = document.getElementById('employeeModalSectorSubtitle');
        const detailsBox = document.getElementById('employee-details-box');
        const detId = document.getElementById('det-id');
        const detNome = document.getElementById('det-nome');
        const detCpf = document.getElementById('det-cpf');
        
        let currentSectorEmployees = [];

        window.openEmployeeModal = function() {
            console.log('[Occurrences] Abrindo modal de funcionário...');
            if (sectorInputId.value === '') {
                alert('Por favor, selecione um setor primeiro.');
                return;
            }
            if (!employeeModal) return console.error('Erro: employeePickerModal não encontrado!');
            employeeModal.classList.add('active');
            document.body.classList.add('modal-open');
            document.getElementById('employeeSearchInput').value = '';
            renderEmployeeList(currentSectorEmployees);
        };

        window.closeEmployeeModal = function() {
            employeeModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        };

        window.selectEmployeeModal = function(id, name, cpf) {
            employeeInputId.value = id;
            employeeValueDisplay.textContent = name;
            window.closeEmployeeModal();
            
            detId.textContent = '#' + id;
            detNome.textContent = name;
            detCpf.textContent = cpf;
            detailsBox.style.display = 'block';
        };

        window.filterEmployees = function(term) {
            const normalizedTerm = term.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const filtered = currentSectorEmployees.filter(emp => {
                const nameMatch = emp.nome.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(normalizedTerm);
                const cpfMatch = emp.cpf.replace(/\D/g, '').includes(normalizedTerm.replace(/\D/g, ''));
                return nameMatch || cpfMatch;
            });
            renderEmployeeList(filtered);
        };

        function renderEmployeeList(employees) {
            employeeListContainer.innerHTML = '';
            
            if (employees.length === 0) {
                employeeListContainer.innerHTML = '<div class="empty-search-state">Nenhum funcionário encontrado.</div>';
                return;
            }

            employees.forEach(emp => {
                const item = document.createElement('div');
                item.className = 'picker-item-btn';
                item.onclick = () => selectEmployeeModal(emp.id, emp.nome, emp.cpf);
                
                item.innerHTML = `
                    <div class="picker-icon-box">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="picker-info">
                        <span class="picker-name">${emp.nome}</span>
                        <span class="picker-sub">CPF: ${emp.cpf} | ID: #${emp.id}</span>
                    </div>
                `;
                employeeListContainer.appendChild(item);
            });
        }

        async function loadEmployeesBySector(sectorId) {
            employeeTriggerBtn.classList.add('disabled');
            employeeValueDisplay.textContent = 'Carregando funcionários...';
            employeeInputId.value = '';
            detailsBox.style.display = 'none';
            
            try {
                const response = await fetch(`${window.BASE_PATH}/api/departments/employees?id=${sectorId}`);
                const result = await response.json();
                
                if (result.success) {
                    currentSectorEmployees = result.data;
                    employeeTriggerBtn.classList.remove('disabled');
                    employeeValueDisplay.textContent = 'Escolha um funcionário...';
                    employeeModalSubtitle.textContent = `Setor: ${sectorValueDisplay.textContent}`;
                } else {
                    employeeValueDisplay.textContent = 'Erro ao carregar setor.';
                }
            } catch (error) {
                console.error('Erro ao carregar funcionários:', error);
                employeeValueDisplay.textContent = 'Erro de conexão.';
            }
        }

        sectorModal.addEventListener('click', (e) => {
            if (e.target === sectorModal) window.closeSectorModal();
        });

        employeeModal.addEventListener('click', (e) => {
            if (e.target === employeeModal) window.closeEmployeeModal();
        });

        // --- LÓGICA DO SELETOR DE MOTIVO (MODAL) ---
        const motivoModal = document.getElementById('motivoPickerModal');
        const motivoValueDisplay = document.getElementById('motivo-display-value');
        const motivoInputId = document.getElementById('occ-motivo');

        window.openMotivoModal = function() {
            console.log('[Occurrences] Abrindo modal de motivo...');
            if (!motivoModal) return console.error('Erro: motivoPickerModal não encontrado!');
            motivoModal.classList.add('active');
            document.body.classList.add('modal-open');
        };

        window.closeMotivoModal = function() {
            motivoModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        };

        window.selectMotivoModal = function(motivo) {
            motivoInputId.value = motivo;
            motivoValueDisplay.textContent = motivo;
            window.closeMotivoModal();
        };

        // --- LÓGICA DO SELETOR DE EPI (CENTRALIZED MODAL) ---
        const epiModal = document.getElementById('epiPickerModal');
        const epiValueDisplay = document.getElementById('epi-display-value');
        const epiInputId = document.getElementById('occ-epi');

        window.openEpiModal = function() {
            console.log('[Occurrences] Abrindo modal de EPI...');
            if (!epiModal) return console.error('Erro: epiPickerModal não encontrado!');
            epiModal.classList.add('active');
            document.body.classList.add('modal-open');
        };

        window.closeEpiModal = function() {
            epiModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        };

        window.selectEpiModal = function(id, name) {
            epiInputId.value = id;
            epiValueDisplay.textContent = name;
            window.closeEpiModal();
        };

        // --- LÓGICA DO SELETOR DE DATA/HORA (CENTRALIZED MODAL) ---
        const dateModal = document.getElementById('datePickerModal');
        const dateValueDisplay = document.getElementById('date-display-value');
        const dateInputId = document.getElementById('occ-data');
        const modalDateInput = document.getElementById('modal-date-input');
        const modalTimeInput = document.getElementById('modal-time-input');

        window.openDateModal = function() {
            console.log('[Occurrences] Abrindo modal de data...');
            if (!dateModal) return console.error('Erro: datePickerModal não encontrado!');
            dateModal.classList.add('active');
            document.body.classList.add('modal-open');
        };

        window.closeDateModal = function() {
            dateModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        };

        window.confirmDateModal = function() {
            const date = modalDateInput.value;
            const time = modalTimeInput.value;

            if (!date || !time) {
                alert('Por favor, selecione data e hora.');
                return;
            }

            // Atualiza input oculto (Formato ISO para o backend)
            dateInputId.value = `${date}T${time}`;
            
            // Atualiza exibição visual (Formato BR)
            const [y, m, d] = date.split('-');
            dateValueDisplay.textContent = `${d}/${m}/${y} ${time}`;
            
            window.closeDateModal();
        };

        // --- LÓGICA DO SELETOR DE TIPO DE AÇÃO (CENTRALIZED MODAL) ---
        const actionModal = document.getElementById('actionPickerModal');
        const actionValueDisplay = document.getElementById('action-display-value');
        const actionInputId = document.getElementById('occ-tipo');

        window.openActionModal = function() {
            console.log('[Occurrences] Abrindo modal de ação...');
            if (!actionModal) return console.error('Erro: actionPickerModal não encontrado!');
            actionModal.classList.add('active');
            document.body.classList.add('modal-open');
        };

        window.closeActionModal = function() {
            actionModal.classList.remove('active');
            document.body.classList.remove('modal-open');
        };

        window.selectActionModal = function(action) {
            actionInputId.value = action;
            actionValueDisplay.textContent = action;
            window.closeActionModal();
        };

        // Fechar ao clicar no backdrop do modal de motivos
        motivoModal.addEventListener('click', (e) => {
            if (e.target === motivoModal) window.closeMotivoModal();
        });

        // Fechar ao clicar no backdrop dos novos modais
        epiModal.addEventListener('click', (e) => {
            if (e.target === epiModal) window.closeEpiModal();
        });
        dateModal.addEventListener('click', (e) => {
            if (e.target === dateModal) window.closeDateModal();
        });
        actionModal.addEventListener('click', (e) => {
            if (e.target === actionModal) window.closeActionModal();
        });

        const successOverlay = document.getElementById('success-overlay');
        document.getElementById('btn-success-ok').addEventListener('click', () => location.reload());

        const btnAddEvidence = document.getElementById('btn-add-evidence');
        const evidenceInput = document.getElementById('evidence-input');
        const uploadGrid = document.getElementById('upload-grid');

        btnAddEvidence.addEventListener('click', () => evidenceInput.click());

        evidenceInput.addEventListener('change', function () {
            uploadGrid.querySelectorAll('.evidence-preview').forEach(p => p.remove());
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'evidence-preview';
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewDiv.appendChild(img);
                        uploadGrid.insertBefore(previewDiv, btnAddEvidence);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        const btnConfirm = document.getElementById('btnConfirmOcc');
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('sector_id')) {
            const sectorId = urlParams.get('sector_id');
            const employeeId = urlParams.get('employee_id');
            const epiId = urlParams.get('epi_id');
            const datetime = urlParams.get('datetime');

            const sectorItems = document.querySelectorAll('.sector-item-btn');
            sectorItems.forEach(item => {
                if (item.getAttribute('onclick').includes(`'${sectorId}'`)) {
                    sectorInputId.value = sectorId;
                    sectorValueDisplay.textContent = item.querySelector('span').textContent;
                }
            });
            
            loadEmployeesBySector(sectorId).then(() => {
                if (employeeId) {
                    const emp = currentSectorEmployees.find(e => e.id == employeeId);
                    if (emp) selectEmployeeModal(emp.id, emp.nome, emp.cpf);
                }
            });
            
            if (epiId) document.getElementById('occ-epi').value = epiId;
            if (datetime) document.getElementById('occ-data').value = datetime;
        }

        btnConfirm.addEventListener('click', async function () {
            const formData = new FormData();
            const funcionarioId = employeeInputId.value;
            const epiId = document.getElementById('occ-epi').value;
            const motivoPrincipal = motivoInputId.value;
            const dataHora = document.getElementById('occ-data').value;
            const tipoAcao = document.getElementById('occ-tipo').value;
            const observacao = document.getElementById('occ-obs').value;

            if (!funcionarioId) {
                alert('Por favor, selecione um funcionário.');
                return;
            }

            formData.append('funcionario_id', funcionarioId);
            formData.append('epi_id', epiId);
            formData.append('motivo_principal', motivoPrincipal);
            formData.append('data_hora', dataHora);
            formData.append('tipo_acao', tipoAcao);
            formData.append('observacao', observacao);

            // Se veio da página de infrações, vincular à ocorrência original
            const originalId = urlParams.get('original_id');
            if (originalId) {
                formData.append('original_occurrence_id', originalId);
            }

            if (evidenceInput.files.length > 0) {
                for (let i = 0; i < evidenceInput.files.length; i++) {
                    formData.append('evidencias[]', evidenceInput.files[i]);
                }
            }

            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Salvando...';

            try {
                const response = await fetch(window.BASE_PATH + '/api/occurrence/store', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // Avisa outras abas para buscarem a novidade (Sincronização Multi-aba)
                    localStorage.setItem('epi-new-registration-trigger', Date.now());
                    
                    document.body.appendChild(successOverlay);
                    successOverlay.classList.add('active');
                    if (window.lucide) lucide.createIcons();
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocorreu um erro ao enviar os dados.');
            } finally {
                btnConfirm.disabled = false;
                btnConfirm.textContent = 'Confirmar Ocorrência';
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>
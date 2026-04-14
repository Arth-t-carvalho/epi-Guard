<?php
$pageTitle = 'Facchini - ' . __('Gestão AD');
$extraHead = '
<link rel="stylesheet" href="' . BASE_PATH . '/assets/css/management.css">
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-blue: #3b82f6;
        --accent-purple: #8b5cf6;
    }

    .premium-container {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .glass-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        margin-bottom: 24px;
        transition: transform 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-2px);
    }

    .ad-header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        margin-right: 16px;
        box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
    }

    .table-header-premium {
        display: flex;
        align-items: center;
        padding: 24px;
        border-bottom: 1px solid var(--border);
    }

    .status-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pill.ad {
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
    }

    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10001; /* Maior que o global */
        backdrop-filter: blur(12px);
        opacity: 0;
        transition: all 0.4s ease;
    }
    .modal-overlay.open { 
        display: flex; 
        opacity: 1;
    }
    .modal-content-premium {
        background: var(--bg-card);
        width: 90%;
        max-width: 550px;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border: 1px solid var(--border);
        transform: translateY(20px);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .modal-overlay.open .modal-content-premium {
        transform: translateY(0);
    }

    .modal-header {
        background: linear-gradient(to right, var(--accent-blue), var(--accent-purple));
        padding: 24px;
        border: none;
    }

    .form-control-premium {
        background: rgba(0,0,0,0.1);
        border: 1px solid var(--border);
        color: var(--text-main);
        border-radius: 12px;
        padding: 12px 16px;
        width: 100%;
        transition: all 0.3s ease;
    }

    .form-control-premium:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        outline: none;
    }

    .btn-premium {
        background: linear-gradient(to right, var(--accent-blue), var(--accent-purple));
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-premium:hover {
        filter: brightness(1.1);
        transform: scale(1.02);
    }

    .btn-premium:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Toast Container */
    #toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
    }

    .toast {
        padding: 16px 24px;
        border-radius: 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        color: var(--text-main);
        box-shadow: var(--shadow-xl);
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>
';
ob_start();
?>

<div id="toast-container"></div>

<div class="premium-container">
    <header class="page-header" style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center;">
            <div class="ad-header-icon">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div class="page-title">
                <h1 style="font-size: 28px; font-weight: 800;"><?= __('Gestão de Active Directory') ?></h1>
                <p style="color: var(--text-muted);"><?= __('Simulação de diretório ativo corporativo para autenticação centralizada') ?></p>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-premium" onclick="openAddAdModal()">
                <i class="fa-solid fa-user-plus"></i> <?= __('Cadastrar Usuário AD') ?>
            </button>
        </div>
    </header>

    <div class="glass-card">
        <div class="table-header-premium">
            <h2 style="font-size: 18px; font-weight: 600; margin: 0;"><?= __('Lista de Usuários') ?> 
                <span id="userCount" style="font-weight: 400; color: var(--text-muted); margin-left: 8px;">(0)</span>
            </h2>
        </div>
        <div class="table-container" style="padding: 0;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;"><?= __('Usuário') ?></th>
                        <th><?= __('E-mail') ?></th>
                        <th><?= __('CPF') ?></th>
                        <th><?= __('Departamento') ?></th>
                        <th style="text-align: right; padding-right: 24px;"><?= __('Ações') ?></th>
                    </tr>
                </thead>
                <tbody id="adUsersTableBody">
                    <!-- Dynamic Content -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="adModal">
    <div class="modal-content-premium">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-shield-halved" style="font-size: 24px;"></i>
                <h3 style="margin: 0; color: white;" id="modalTitle"><?= __('Novo Cadastro AD') ?></h3>
            </div>
            <button onclick="closeAdModal()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 32px;">
            <form id="adForm" onsubmit="event.preventDefault(); saveAdUser();">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label><i class="fa-solid fa-user"></i> <?= __('Nome Completo') ?></label>
                        <input type="text" name="name" class="form-control-premium" required placeholder="Ex: João da Silva">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-envelope"></i> <?= __('E-mail Corporativo') ?></label>
                        <input type="email" name="email" class="form-control-premium" required placeholder="joao@facchini.com.br">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-id-card"></i> <?= __('CPF') ?></label>
                        <input type="text" name="cpf" class="form-control-premium" maxlength="14" oninput="maskCpf(this)" placeholder="000.000.000-00">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-building"></i> <?= __('Departamento') ?></label>
                        <select name="department" class="form-control-premium">
                            <option value="TI">TI</option>
                            <option value="Administração">Administração</option>
                            <option value="Produção">Produção</option>
                            <option value="Segurança">Segurança</option>
                            <option value="RH">RH</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-lock"></i> <?= __('Senha de Acesso') ?></label>
                        <input type="password" name="password" class="form-control-premium" value="123" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="padding: 24px 32px; background: rgba(0,0,0,0.1); display: flex; justify-content: flex-end; gap: 12px;">
            <button class="btn-action" onclick="closeAdModal()" style="padding: 12px 24px; border-radius: 12px;"><?= __('Cancelar') ?></button>
            <button class="btn-premium" id="btnSave" onclick="saveAdUser()">
                <i class="fa-solid fa-check"></i> <span id="btnSaveText"><?= __('Cadastrar Agora') ?></span>
            </button>
        </div>
    </div>
</div>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'circle-check' : 'circle-exclamation';
    const color = type === 'success' ? '#10b981' : '#f43f5e';
    
    toast.innerHTML = `
        <i class="fa-solid fa-${icon}" style="color: ${color}; font-size: 20px;"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function maskCpf(v) {
    v.value = v.value.replace(/\D/g, "");
    if (v.value.length <= 11) {
        v.value = v.value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, "$1.$2.$3-$4");
    }
}

async function loadAdUsers() {
    const tbody = document.getElementById('adUsersTableBody');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 48px;"><i class="fa-solid fa-spinner fa-spin"></i> Sincronizando dados...</td></tr>';
    
    try {
        const response = await fetch('<?= BASE_PATH ?>/api/ad-users');
        const result = await response.json();
        
        if (result.success) {
            tbody.innerHTML = '';
            document.getElementById('userCount').textContent = `(${result.data.length})`;
            
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 48px; color: var(--text-muted);">Nenhum usuário no simulador de AD.</td></tr>';
                return;
            }

            result.data.forEach((user, index) => {
                const tr = document.createElement('tr');
                tr.style.animation = `fadeIn 0.3s ease-out ${index * 0.05}s both`;
                tr.innerHTML = `
                    <td style="padding-left: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--accent-blue); font-weight: 700;">
                                ${user.name.charAt(0)}
                            </div>
                            <div>
                                <div style="font-weight: 600;">${user.name}</div>
                                <small style="color: var(--text-muted); font-family: monospace;">${user.username}</small>
                            </div>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">${user.email}</td>
                    <td><code style="background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px;">${user.cpf || '-'}</code></td>
                    <td><span class="status-pill ad">${user.department}</span></td>
                    <td style="text-align: right; padding-right: 24px;">
                        <button class="btn-icon delete" onclick="deleteAdUser('${user.username}', this)" style="opacity: 0.6; transition: 0.3s;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" style="color:var(--danger); text-align:center; padding: 48px;">Falha na conexão com o servidor.</td></tr>';
    }
}

function openAddAdModal() {
    document.getElementById('adForm').reset();
    document.getElementById('adModal').classList.add('open');
}

function closeAdModal() {
    document.getElementById('adModal').classList.remove('open');
}


async function saveAdUser() {
    const form = document.getElementById('adForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btn = document.getElementById('btnSave');
    const btnText = document.getElementById('btnSaveText');
    const originalText = btnText.textContent;
    
    btn.disabled = true;
    btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cadastrando...';

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('<?= BASE_PATH ?>/api/ad-users/save', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'Content-Type': 'application/json' }
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message);
            closeAdModal();
            loadAdUsers();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Erro interno ao processar cadastro.', 'error');
    } finally {
        btn.disabled = false;
        btnText.textContent = originalText;
    }
}

async function deleteAdUser(username, btn) {
    const confirmed = await showConfirm(
        '<?= __('Remover Acesso AD') ?>',
        '<?= __('Esta ação removerá permanentemente o acesso deste usuário via AD. Continuar?') ?>',
        'error'
    );
    if (!confirmed) return;
    
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.disabled = true;

    try {
        const response = await fetch(`<?= BASE_PATH ?>/api/ad-users/delete?username=${username}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        if (result.success) {
            showToast(result.message);
            loadAdUsers();
        } else {
            showToast(result.message, 'error');
            btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
            btn.disabled = false;
        }
    } catch (error) {
        showToast('Erro ao excluir usuário.', 'error');
        btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', loadAdUsers);
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout/main.php';
?>

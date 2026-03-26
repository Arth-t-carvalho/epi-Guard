<?php
/**
 * Header do layout principal.
 * Exibe o nome do usuário logado, setor e iniciais dinamicamente.
 */
$userName = $_SESSION['user_nome'] ?? 'Visitante';
$userSetor = $_SESSION['user_setor'] ?? 'Sem setor';

// Lógica para extração de iniciais (Primeiro Nome + Último Nome)
$nameParts = explode(' ', trim($userName));
$initials = '';
if (count($nameParts) >= 2) {
    $initials = mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1);
} else {
    $initials = mb_substr($userName, 0, 2);
}
$initials = mb_strtoupper($initials);
?>

<style>
/* Dropdown/Modal Perfil do Usuário */
.profile-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 260px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1000;
}

.profile-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 24px 20px 20px;
    border-bottom: 1px solid #f1f5f9;
    text-align: center;
}

.dropdown-avatar {
    width: 64px;
    height: 64px;
    background: #1F2937;
    color: white;
    border-radius: 50%;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
}

.dropdown-name {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
}

.dropdown-sector {
    font-size: 13px;
    color: #64748B;
    font-weight: 500;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}

.dropdown-sector svg {
    width: 14px;
    height: 14px;
}

.dropdown-body {
    padding: 8px;
}

.btn-logout-dropdown {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 16px;
    border-radius: 10px;
    color: #E30613;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-logout-dropdown:hover {
    background: #fef2f2;
}

.btn-logout-dropdown svg {
    width: 18px;
    height: 18px;
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 320px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(0, 0, 0, 0.05);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 9999;
    overflow: hidden;
}

.notification-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.notif-dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f1f5f9;
}

.notif-dropdown-header span {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.notif-clear-btn {
    background: none;
    border: none;
    font-size: 12px;
    color: #E30613;
    font-weight: 600;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background 0.2s;
}

.notif-clear-btn:hover {
    background: #fef2f2;
}

.notif-list {
    max-height: 320px;
    overflow-y: auto;
}

.notif-item {
    display: flex;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid #f8fafc;
    align-items: flex-start;
    transition: background 0.15s;
    animation: notifSlideIn 0.3s ease;
}

@keyframes notifSlideIn {
    from { opacity: 0; transform: translateX(10px); }
    to { opacity: 1; transform: translateX(0); }
}

.notif-item:hover { background: #fafafa; }

.notif-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    background: #fef2f2;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #E30613;
}

.notif-icon svg { width: 18px; height: 18px; }

.notif-content { flex: 1; }

.notif-content strong {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 2px;
}

.notif-content span {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
}

.notif-time {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
}

.notif-empty {
    padding: 32px 20px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}

.notif-empty i {
    display: block;
    margin-bottom: 8px;
    width: 32px;
    height: 32px;
    margin-left: auto;
    margin-right: auto;
}

.notification-badge { display: none !important; }
.notification-badge.visible { display: flex !important; }

</style>

<header class="header">
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Olá <?= htmlspecialchars($userName) ?>, bem-vindo de volta!</p>
    </div>

    <div class="header-actions">
        <!-- Configurações -->
        <div style="position: relative; display: flex;">
            <button class="header-icon-btn">
                <i data-lucide="settings"></i>
            </button>
        </div>

        <!-- Notificações -->
        <div style="position: relative; display: flex;">
            <button class="header-icon-btn notification-btn" id="notifBtn">
                <i data-lucide="bell"></i>
                <span class="notification-badge" id="notifBadge">0</span>
            </button>

            <!-- Menu de notificações -->
            <div class="notification-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <span>Notificações</span>
                    <button class="notif-clear-btn" id="notifClearBtn">Marcar como lidas</button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty" id="notifEmpty">
                        <i data-lucide="bell-off"></i>
                        Nenhuma infração nova
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($currentRoute) && ($currentRoute === '/dashboard' || $currentRoute === '/')): ?>
        <button class="btn-export" onclick="exportData()">
            <i class="fa-solid fa-download"></i> Exportar
        </button>
        <?php endif; ?>
        
        <div class="user-profile-container" style="position: relative;">
            <div class="user-profile-trigger" id="profileTrigger">
                <div class="user-info-mini">
                    <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                    <span class="user-role"><?= htmlspecialchars($userSetor) ?></span>
                </div>
                <div class="user-avatar">
                    <?= htmlspecialchars($initials) ?>
                </div>
            </div>

            <!-- Dropdown Discreto -->
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div class="dropdown-name"><?= htmlspecialchars($userName) ?></div>
                    <div class="dropdown-sector">
                        <i data-lucide="building-2"></i>
                        <?= htmlspecialchars($userSetor) ?>
                    </div>
                </div>
                
                <div class="dropdown-body">
                    <a href="<?= BASE_PATH ?>/logout" class="btn-logout-dropdown">
                        <i data-lucide="log-out"></i>
                        <span>Sair da Conta</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Profile Dropdown ---
    const trigger = document.getElementById('profileTrigger');
    const dropdown = document.getElementById('profileDropdown');

    if (trigger && dropdown) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
            notifDropdown?.classList.remove('active');
            if (window.lucide) lucide.createIcons();
        });

        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('active');
                notifDropdown?.classList.remove('active');
            }
        });
    }

    // --- Notification Dropdown ---
    const notifBtn     = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifBadge   = document.getElementById('notifBadge');
    const notifList    = document.getElementById('notifList');
    const notifEmpty   = document.getElementById('notifEmpty');
    const notifClearBtn = document.getElementById('notifClearBtn');

    let pendingNotifs = [];
    const BASE = window.BASE_PATH || '';

    function renderNotification(n) {
        const div = document.createElement('div');
        div.className = 'notif-item';
        div.dataset.id = n.id;
        div.style.cursor = 'pointer';
        div.innerHTML = `
            <div class="notif-icon"><i data-lucide="alert-triangle"></i></div>
            <div class="notif-content">
                <strong>${n.funcionario}</strong>
                <span>⚠️ ${n.epis} &mdash; ${n.setor}</span>
                <div class="notif-time">${n.tempo}</div>
            </div>
        `;
        div.onclick = function() {
            // Marcar como lida no servidor antes de ir para a página
            fetch(`${BASE}/api/marcar_lida?id=${n.id}`)
                .then(() => {
                    window.location.href = `${BASE}/infractions?id=${n.id}`;
                })
                .catch(() => {
                    window.location.href = `${BASE}/infractions?id=${n.id}`;
                });
        };
        return div;
    }

    function refreshList() {
        if (pendingNotifs.length === 0) {
            notifEmpty.style.display = 'block';
            notifList.querySelectorAll('.notif-item').forEach(el => el.remove());
        } else {
            notifEmpty.style.display = 'none';
            // Clear and re-render
            notifList.querySelectorAll('.notif-item').forEach(el => el.remove());
            pendingNotifs.forEach(n => {
                notifList.insertBefore(renderNotification(n), notifEmpty);
            });
            if (window.lucide) lucide.createIcons();
        }
        // Update badge
        const count = pendingNotifs.length;
        if (count > 0) {
            notifBadge.textContent = count > 99 ? '99+' : count;
            notifBadge.classList.add('visible');
        } else {
            notifBadge.classList.remove('visible');
        }
    }

    function poll() {
        // Agora buscamos todas as não lidas, o servidor já limita
        fetch(`${BASE}/api/check_notificacoes`)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    pendingNotifs = data.dados || [];
                    refreshList();
                }
            })
            .catch(() => {}); // fail silently
    }

    // Open / close notification dropdown
    if (notifBtn) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('active');
            dropdown?.classList.remove('active');
        });

        document.addEventListener('click', function(e) {
            const container = notifBtn.parentElement;
            if (notifDropdown.classList.contains('active') && !container.contains(e.target)) {
                notifDropdown.classList.remove('active');
            }
        });
    }

    // Clear/mark as read (Opcional: você pode manter ou remover o limpar tudo)
    if (notifClearBtn) {
        notifClearBtn.style.display = 'none'; // Escondemos o limpar tudo para seguir regra "só some se clicar"
    }

    // Start polling: immediate, then every 10s
    poll();
    setInterval(poll, 10000);
});
</script>

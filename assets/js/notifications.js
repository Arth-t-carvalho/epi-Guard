/**
 * NOTIFICATIONS.JS - Sistema de monitoramento em tempo real (Polling 10s) + Toasts
 * Gerencia alertas visuais, toast system e dropdown moderno.
 */

document.addEventListener('DOMContentLoaded', function() {
    const notifBtn     = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifBadge   = document.getElementById('notifBadge');
    const notifList    = document.getElementById('notifList');
    const notifEmpty   = document.getElementById('notifEmpty');
    const notifClearBtn = document.getElementById('notifClearBtn');
    
    let pendingNotifs = []; 
    let lastOccurrenceId = 0; // Para Toast Notifications passivas

    // Inicialização do container de notificações (Toasts)
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        document.body.appendChild(container);
    }

    // --- FUNÇÃO DE ABRIR/FECHAR MODAL ---
    if (notifBtn) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (notifDropdown) notifDropdown.classList.toggle('active');
        });
    }

    // Fecha ao clicar do lado de fora do Modal
    document.addEventListener('click', function(e) {
        if (notifDropdown && notifBtn && notifDropdown.classList.contains('active') && !notifBtn.parentElement.contains(e.target)) {
            notifDropdown.classList.remove('active');
        }
    });

    // --- FUNÇÃO PARA CRIAR ELEMENTOS DE RENDERIZAÇÃO ---
    function renderNotification(notif) {
        const div = document.createElement('div');
        div.className = 'notif-item';
        div.style.cursor = 'pointer';
        
        // Estrutura de card moderno
        div.innerHTML = `
            <div style="background:#fef2f2; color:#E30613; padding:8px; border-radius:10px;">
               <i data-lucide="alert-triangle"></i>
            </div>
            <div style="flex:1;">
                <strong style="display:block; font-size:13px; color:#111827;">${notif.funcionario_nome || notif.funcionario || 'Desconhecido'}</strong>
                <span style="display:block; font-size:12px; color:#64748b;">⚠️ ${notif.epi_nome || notif.epis || 'EPI'}</span>
                <span style="font-size:11px; color:#94a3b8;">${notif.timeStr || (notif.data_hora ? new Date(notif.data_hora).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Novo')}</span>
            </div>
        `;
        
        // Clicar redireciona para a página da infração
        div.onclick = function() {
            window.location.href = `${window.BASE_PATH || ''}/infractions`;
        };
        return div;
    }

    // --- FUNÇÃO PARA ATUALIZAR O MODAL ---
    function refreshList() {
        if (!notifList || !notifEmpty) return;

        if (pendingNotifs.length === 0) {
            notifEmpty.style.display = 'block';
            notifList.querySelectorAll('.notif-item').forEach(el => el.remove());
            notifBadge.classList.remove('visible');
            if (notifClearBtn) notifClearBtn.style.display = 'none';
        } else {
            notifEmpty.style.display = 'none';
            notifBadge.classList.add('visible');
            notifBadge.textContent = pendingNotifs.length > 99 ? '99+' : pendingNotifs.length;
            if (notifClearBtn) notifClearBtn.style.display = 'block';
            
            // Recria a lista
            notifList.querySelectorAll('.notif-item').forEach(el => el.remove());
            pendingNotifs.forEach(n => {
                notifList.insertBefore(renderNotification(n), notifEmpty);
            });
            
            if (window.lucide) lucide.createIcons();
        }
    }

    /**
     * Renderiza o card (Toast flutuante) animado
     */
    function showEpiNotification(data) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'epi-alert-toast';
        
        toast.innerHTML = `
            <div class="toast-icon-wrapper"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="toast-content">
                <span class="toast-title">Alerta de EPI</span>
                <div class="toast-details">
                    <div class="toast-detail">Nome: <span>${data.funcionario_nome || data.funcionario || 'Desc'}</span></div>
                    <div class="toast-detail">Setor: <span>${data.setor_sigla || 'Geral'}</span></div>
                    <div class="toast-detail">EPI ausente: <span>${data.epi_nome || data.epis}</span></div>
                </div>
            </div>
            <div class="toast-progress" style="animation: progressShrink 3000ms linear forwards"></div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('leaving');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // --- SISTEMA DE POLLING ---
    function pollServer() {
        fetch(`${window.BASE_PATH || ''}/api/check_notificacoes?last_id=${lastOccurrenceId}`)
            .then(r => r.json())
            .then(result => {
                // Compatibilidade com o backend de polling que retorna { status, dados, last_id }
                const newData = result.dados || [];
                
                if (result.status === 'success' || result.status === 'init') {
                    if (result.status === 'success' && newData.length > 0) {
                        // Novas notificações para disparar Toasts!
                        newData.forEach((occ, index) => {
                            setTimeout(() => showEpiNotification(occ), index * 400);
                            pendingNotifs.unshift(occ); // Adiciona no início da lista do dropdown
                        });
                    } else if (result.status === 'init') {
                        // Carga inicial
                        pendingNotifs = newData;
                    }
                    
                    lastOccurrenceId = result.last_id || lastOccurrenceId;
                    
                    // Mantem no máximo as últimas 50 para não pesar
                    if (pendingNotifs.length > 50) pendingNotifs = pendingNotifs.slice(0, 50);
                    
                    refreshList();
                }
            }).catch(() => {});
    }

    if (notifClearBtn) {
        notifClearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            pendingNotifs = [];
            refreshList();
        });
    }

    // Executa a primeira vez e depois a cada 10000ms (10 segundos)
    pollServer();
    setInterval(pollServer, 10000);
});

/**
 * Função de Simulação Real (Insere no Banco e Atualiza o Polling)
 */
window.testNotification = async function() {
    try {
        const response = await fetch(`${window.BASE_PATH || ''}/api/simulate-occurrence`);
        const result = await response.json();
        if (!result.success) {
            alert('Erro na simulação: ' + result.message);
        }
    } catch (error) {
        console.error('[Simulation]', error);
    }
};

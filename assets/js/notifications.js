/**
 * NOTIFICATIONS.JS - Sistema de monitoramento em tempo real (Polling 5s)
 * Gerencia alertas visuais e o contador acumulativo no sino de notificações.
 */

document.addEventListener('DOMContentLoaded', () => {
    let lastOccurrenceId = 0;
    const POLLING_INTERVAL = 5000;
    const DISPLAY_DURATION = 3000;

    // Elementos da UI
    const badge = document.getElementById('bell-badge');
    const panel = document.getElementById('notificationPanel');
    const panelBody = document.getElementById('notificationPanelBody');

    // Inicialização do container de notificações (Toasts)
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        document.body.appendChild(container);
    }

    /**
     * Alternar Visibilidade do Painel
     */
    window.toggleNotificationPanel = function() {
        if (!panel) return;
        const isActive = panel.classList.toggle('active');
        if (isActive) {
            setUnseenCount(0); // Zera o badge ao abrir o painel
        }
    };

    /**
     * Gerenciamento de Contador Não Lido (localStorage)
     */
    function getUnseenCount() {
        return parseInt(localStorage.getItem('epi_unseen_count') || '0');
    }

    function setUnseenCount(count) {
        localStorage.setItem('epi_unseen_count', count);
        updateBadgeUI(count);
    }

    function updateBadgeUI(count) {
        if (!badge) return;
        badge.textContent = count > 0 ? '+' : '';
        
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    /**
     * Adicionar Notificação ao Painel
     */
    function addNotificationToPanel(data, isInitial = false) {
        if (!panelBody) return;

        // Remover mensagem de "vazio" se existir
        const emptyMsg = panelBody.querySelector('.notification-empty');
        if (emptyMsg) emptyMsg.remove();

        const item = document.createElement('div');
        item.className = 'notification-item';
        
        const timeStr = data.data_hora ? new Date(data.data_hora).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Agora';

        item.innerHTML = `
            <div class="notification-item-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="notification-item-content">
                <span class="notification-item-title">EPI Ausente: ${data.epi_nome || 'EPI'}</span>
                <span class="notification-item-desc"><strong>${data.funcionario_nome}</strong> no setor <strong>${data.setor_sigla || 'Geral'}</strong>.</span>
                <span class="notification-item-time">${timeStr}</span>
            </div>
        `;

        if (isInitial) {
            panelBody.appendChild(item);
        } else {
            panelBody.insertBefore(item, panelBody.firstChild);
        }
    }

    /**
     * Inicia o monitoramento
     */
    async function initPolling() {
        if (window.lucide) lucide.createIcons();

        updateBadgeUI(getUnseenCount());

        try {
            const response = await fetch(`${window.BASE_PATH}/api/check_notificacoes?last_id=0`);
            const result = await response.json();
            
            if (result.status === 'init' || result.status === 'success') {
                lastOccurrenceId = result.last_id;
                
                if (result.dados && result.dados.length > 0) {
                    result.dados.forEach(occ => addNotificationToPanel(occ, true));
                }

                setInterval(checkNewOccurrences, POLLING_INTERVAL);
            }
        } catch (error) {
            console.error('[Notifications] Falha no polling:', error);
        }
    }

    /**
     * Busca novas ocorrências
     */
    async function checkNewOccurrences() {
        try {
            const response = await fetch(`${window.BASE_PATH}/api/check_notificacoes?last_id=${lastOccurrenceId}`);
            const result = await response.json();

            if (result.status === 'success' && result.dados && result.dados.length > 0) {
                lastOccurrenceId = result.last_id;

                // Incrementar contador se o painel estiver fechado
                if (!panel.classList.contains('active')) {
                    const newCount = getUnseenCount() + result.dados.length;
                    setUnseenCount(newCount);
                }

                // Mostrar Toasts e adicionar ao painel
                result.dados.forEach((occ, index) => {
                    addNotificationToPanel(occ);
                    setTimeout(() => showEpiNotification(occ), index * 400);
                });
            }
        } catch (error) {
            console.error('[Notifications] Erro na verificação:', error);
        }
    }

    /**
     * Renderiza o card (Toast flutuante)
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
                    <div class="toast-detail">Nome: <span>${data.funcionario_nome}</span></div>
                    <div class="toast-detail">Setor: <span>${data.setor_sigla || 'Geral'}</span></div>
                    <div class="toast-detail">EPI ausente: <span>${data.epi_nome}</span></div>
                </div>
            </div>
            <div class="toast-progress" style="animation: progressShrink ${DISPLAY_DURATION}ms linear forwards"></div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('leaving');
            setTimeout(() => toast.remove(), 500);
        }, DISPLAY_DURATION);
    }

    // Inicializar sistema
    initPolling();

    // Fechar painel ao clicar fora
    document.addEventListener('click', (e) => {
        const bellBtn = document.getElementById('notificationBellBtn');
        if (panel && panel.classList.contains('active') && 
            !panel.contains(e.target) && 
            !bellBtn.contains(e.target)) {
            panel.classList.remove('active');
        }
    });
});

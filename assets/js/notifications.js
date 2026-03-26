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

    // Inicialização do container de notificações
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        document.body.appendChild(container);
    }

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
        badge.textContent = count > 99 ? '99+' : count;
        
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    /**
     * Verifica se deve resetar o contador (se estiver na página de infrações)
     */
    function checkAndResetIfOnInfractions() {
        // Verifica tanto a URL atual quanto o estado do sistema de navegação AJAX
        const isInfractionsPage = window.location.pathname.includes('/infractions');
        if (isInfractionsPage) {
            setUnseenCount(0);
        }
    }

    /**
     * Inicia o monitoramento
     */
    async function initPolling() {
        // Garantir que os ícones (se houver Lucide) sejam processados
        if (window.lucide) lucide.createIcons();

        checkAndResetIfOnInfractions();
        updateBadgeUI(getUnseenCount());

        try {
            // Buscamos o estado atual + infrações recentes (24h)
            const response = await fetch(`${window.BASE_PATH}/api/check_notificacoes?last_id=0`);
            const result = await response.json();
            
            if (result.status === 'init' || result.status === 'success') {
                lastOccurrenceId = result.last_id;
                
                // Se houver dados pendentes (24h), atualizamos o contador inicial
                if (result.dados && result.dados.length > 0) {
                    const currentUnseen = getUnseenCount();
                    // Se o usuário já tem contagem, somamos apenas se não estiver na página de infrações
                    if (!window.location.pathname.includes('/infractions')) {
                        setUnseenCount(Math.max(currentUnseen, result.dados.length));
                    }
                }

                console.log(`[Notifications] Monitoramento iniciado (ID: ${lastOccurrenceId})`);
                setInterval(checkNewOccurrences, POLLING_INTERVAL);
            }
        } catch (error) {
            console.error('[Notifications] Falha no roteiro de polling:', error);
        }
    }

    /**
     * Busca novas ocorrências
     */
    async function checkNewOccurrences() {
        // Verifica reset a cada ciclo (útil para navegação parcial)
        checkAndResetIfOnInfractions();

        try {
            const response = await fetch(`${window.BASE_PATH}/api/check_notificacoes?last_id=${lastOccurrenceId}`);
            const result = await response.json();

            if (result.status === 'success' && result.dados && result.dados.length > 0) {
                lastOccurrenceId = result.last_id;

                // Se NÃO estivermos na página de infrações, incrementamos o contador
                if (!window.location.pathname.includes('/infractions')) {
                    const newCount = getUnseenCount() + result.dados.length;
                    setUnseenCount(newCount);
                }

                // Mostrar os toasts (cards flutuantes)
                result.dados.forEach((occ, index) => {
                    setTimeout(() => showEpiNotification(occ), index * 400);
                });
            }
        } catch (error) {
            console.error('[Notifications] Erro na verificação:', error);
        }
    }

    /**
     * Renderiza o card (Toast)
     */
    function showEpiNotification(data) {
        const container = document.getElementById('notification-container');
        const toast = document.createElement('div');
        toast.className = 'epi-alert-toast';
        
        toast.innerHTML = `
            <div class="toast-icon-wrapper"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="toast-content">
                <span class="toast-title">Alerta de EPI</span>
                <div class="toast-details">
                    <div class="toast-detail">Nome: <span>${data.funcionario_nome}</span></div>
                    <div class="toast-detail">Setor: <span>${data.setor_sigla || 'Geral'}</span></div>
                    <div class="toast-detail">EPI ausente: <span>${data.epi_nome || 'Não identificado'}</span></div>
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

    // Listener para o sistema de navegação AJAX (se existir)
    document.addEventListener('pageChanged', () => {
        checkAndResetIfOnInfractions();
    });
});

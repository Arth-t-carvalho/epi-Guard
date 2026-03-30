/**
 * NOTIFICATIONS.JS — Sistema de notificações em tempo real
 * Polling a cada 5s + Toast visual + Modal (Sino)
 */

document.addEventListener('DOMContentLoaded', function () {
    const notifBtn      = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifBadge    = document.getElementById('notifBadge');
    const notifList     = document.getElementById('notifList');
    const notifEmpty    = document.getElementById('notifEmpty');
    const notifClearBtn = document.getElementById('notifClearBtn');

    let pendingNotifs   = [];
    let lastOccurrenceId = 0;

    console.log('[Notif] Sistema iniciado. Elementos:', {
        btn: !!notifBtn, dropdown: !!notifDropdown, badge: !!notifBadge,
        list: !!notifList, empty: !!notifEmpty
    });

    // Container dos Toasts flutuantes
    if (!document.getElementById('notification-container')) {
        const c = document.createElement('div');
        c.id = 'notification-container';
        document.body.appendChild(c);
    }

    // ========== ABRIR / FECHAR DROPDOWN ==========
    if (notifBtn) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (notifDropdown) notifDropdown.classList.toggle('active');
        });
    }

    document.addEventListener('click', function (e) {
        if (notifDropdown && notifBtn &&
            notifDropdown.classList.contains('active') &&
            !notifBtn.parentElement.contains(e.target)) {
            notifDropdown.classList.remove('active');
        }
    });

    // ========== RENDERIZAR UM ITEM NO MODAL ==========
    function renderNotification(notif) {
        const div = document.createElement('div');
        div.className = 'notif-item';
        div.style.cursor = 'pointer';

        const nome = notif.funcionario_nome || 'Funcionário';
        const epi  = notif.epi_nome || 'EPI';
        const setor = notif.setor_sigla || 'Geral';
        const hora = notif.data_hora
            ? new Date(notif.data_hora).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            : 'Agora';
        const data = notif.data_hora
            ? new Date(notif.data_hora).toLocaleDateString('pt-BR')
            : '';

        div.innerHTML = `
            <div style="background:#fef2f2; color:#E30613; padding:8px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
               <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div style="flex:1;">
                <strong style="display:block; font-size:13px; color:#111827;">${nome}</strong>
                <span style="display:block; font-size:12px; color:#64748b;">⚠️ ${epi}</span>
                <span style="font-size:11px; color:#94a3b8;">${hora}</span>
            </div>
        `;

        // Ao clicar, isola essa notificação e mostra os detalhes
        div.onclick = function (e) {
            e.stopPropagation();
            showNotifDetail(notif);
        };
        return div;
    }

    // ========== VISUALIZAÇÃO ISOLADA (DETALHE) ==========
    function showNotifDetail(notif) {
        if (!notifList) return;

        const nome  = notif.funcionario_nome || 'Funcionário';
        const epi   = notif.epi_nome || 'EPI não identificado';
        const setor = notif.setor_sigla || 'Geral';
        const hora  = notif.data_hora
            ? new Date(notif.data_hora).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            : '--:--';
        const data  = notif.data_hora
            ? new Date(notif.data_hora).toLocaleDateString('pt-BR')
            : '--/--/----';

        // Esconde tudo no modal e mostra só o detalhe
        notifList.innerHTML = `
            <div class="notif-detail-view" style="padding: 16px;">
                <button id="notifBackBtn" style="
                    background: none; border: none; cursor: pointer; color: var(--primary, #E30613);
                    font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px;
                    margin-bottom: 16px; padding: 0;
                ">
                    <i class="fa-solid fa-arrow-left"></i> Voltar
                </button>

                <div style="
                    background: linear-gradient(135deg, #fef2f2, #fff5f5);
                    border: 1px solid #fecaca;
                    border-radius: 14px;
                    padding: 20px;
                    text-align: center;
                ">
                    <div style="
                        width: 48px; height: 48px; background: #E30613; color: white;
                        border-radius: 50%; display: flex; align-items: center; justify-content: center;
                        margin: 0 auto 14px auto; font-size: 20px;
                    ">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <h4 style="font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 4px 0;">
                        ${nome}
                    </h4>
                    <span style="font-size: 12px; color: #E30613; font-weight: 600;">INFRAÇÃO</span>
                </div>

                <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:#64748b; font-weight:600;">Setor</span>
                        <span style="color:#111827; font-weight:700;">${setor}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:#64748b; font-weight:600;">EPI Ausente</span>
                        <span style="color:#E30613; font-weight:700;">${epi}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:#64748b; font-weight:600;">Data</span>
                        <span style="color:#111827; font-weight:700;">${data}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:#64748b; font-weight:600;">Hora</span>
                        <span style="color:#111827; font-weight:700;">${hora}</span>
                    </div>
                </div>

                <a href="${(window.BASE_PATH || '') + '/infractions'}" style="
                    display: block; text-align: center; margin-top: 18px;
                    padding: 10px; background: #E30613; color: white;
                    border-radius: 10px; font-weight: 700; font-size: 13px;
                    text-decoration: none; transition: 0.2s;
                ">
                    Ver na página de Infrações →
                </a>
            </div>
        `;

        // Botão Voltar — recarrega a lista completa
        document.getElementById('notifBackBtn').addEventListener('click', function (e) {
            e.stopPropagation();
            refreshList();
        });
    }

    // ========== ATUALIZAR O MODAL (SINO) ==========
    function refreshList() {
        if (!notifList || !notifEmpty || !notifBadge) return;

        // Limpa tudo (itens e view de detalhe)
        notifList.innerHTML = '';

        // Re-insere o placeholder de "vazio"
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'notif-empty';
        emptyDiv.id = 'notifEmpty';
        emptyDiv.innerHTML = '<i class="fa-solid fa-bell-slash"></i><span>Nenhuma infração nova</span>';
        notifList.appendChild(emptyDiv);

        if (pendingNotifs.length === 0) {
            emptyDiv.style.display = 'block';
            notifBadge.style.display = 'none';
            if (notifClearBtn) notifClearBtn.style.display = 'none';
        } else {
            emptyDiv.style.display = 'none';
            notifBadge.style.display = 'flex';
            notifBadge.textContent = pendingNotifs.length > 99 ? '99+' : pendingNotifs.length;
            if (notifClearBtn) notifClearBtn.style.display = 'block';

            pendingNotifs.forEach(n => {
                notifList.insertBefore(renderNotification(n), emptyDiv);
            });
        }

        console.log('[Notif] Modal atualizado. Total:', pendingNotifs.length);
    }

    // ========== TOAST FLUTUANTE ==========
    function showToast(data) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'epi-alert-toast';
        toast.innerHTML = `
            <div class="toast-icon-wrapper"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="toast-content">
                <span class="toast-title">Alerta de EPI</span>
                <div class="toast-details">
                    <div class="toast-detail">Nome: <span>${data.funcionario_nome || 'Funcionário'}</span></div>
                    <div class="toast-detail">Setor: <span>${data.setor_sigla || 'Geral'}</span></div>
                    <div class="toast-detail">EPI ausente: <span>${data.epi_nome || 'EPI'}</span></div>
                </div>
            </div>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('leaving');
            setTimeout(() => toast.remove(), 500);
        }, 3500);
    }

    // ========== SOM DE PING ==========
    function playPing() {
        try {
            const a = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            a.volume = 0.5;
            a.play().catch(() => {});
        } catch (e) {}
    }

    // ========== POLLING DO SERVIDOR ==========
    function pollServer() {
        const url = (window.BASE_PATH || '') + '/api/check_notificacoes?last_id=' + lastOccurrenceId;

        fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(result => {
                console.log('[Notif] API respondeu:', result.status, 'dados:', (result.dados || []).length, 'last_id:', result.last_id);

                const dados = result.dados || [];

                if (result.status === 'init') {
                    // Primeira carga — preenche o modal com o histórico
                    pendingNotifs = dados;
                    lastOccurrenceId = result.last_id || 0;
                    refreshList();

                } else if (result.status === 'success' && dados.length > 0) {
                    // Novas infrações detectadas!
                    playPing();

                    dados.forEach((occ, i) => {
                        pendingNotifs.unshift(occ);
                        setTimeout(() => {
                            showToast(occ);
                            window.dispatchEvent(new CustomEvent('epi-new-notification', { detail: occ }));
                        }, i * 800);
                    });

                    if (pendingNotifs.length > 50) pendingNotifs = pendingNotifs.slice(0, 50);
                    lastOccurrenceId = result.last_id || lastOccurrenceId;
                    refreshList();
                }
            })
            .catch(err => {
                console.error('[Notif] Erro no polling:', err.message);
            });
    }

    // ========== SYNC ENTRE ABAS ==========
    window.addEventListener('storage', (e) => {
        if (e.key === 'epi-new-registration-trigger') {
            pollServer();
        }
    });

    // ========== LIMPAR NOTIFICAÇÕES ==========
    if (notifClearBtn) {
        notifClearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            pendingNotifs = [];
            refreshList();
        });
    }

    // Expõe para chamada manual (ex: após simulação)
    window.triggerNotificationPoll = pollServer;

    // Inicia o polling
    pollServer();
    setInterval(pollServer, 5000);
});

/**
 * Simulação de ocorrência real (botão azul)
 */
window.testNotification = async function () {
    try {
        const res = await fetch((window.BASE_PATH || '') + '/api/simulate-occurrence');
        const data = await res.json();
        if (data.success) {
            console.log('[Notif] Simulação OK:', data.data);
            if (window.triggerNotificationPoll) window.triggerNotificationPoll();
            localStorage.setItem('epi-new-registration-trigger', Date.now());
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (e) {
        console.error('[Notif] Erro simulação:', e);
    }
};

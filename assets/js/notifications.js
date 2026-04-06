/**
 * NOTIFICATIONS.JS - Sistema de monitoramento em tempo real (Polling 10s) + Toasts
 * Gerencia alertas visuais, toast system e dropdown moderno.
 */

if (!window._notificationsGlobalsInitialized) {
    window._notificationsGlobalsInitialized = true;

    let pendingNotifs = []; 
    let lastOccurrenceId = 0; // Para Toast Notifications passivas

    // O container de notificações agora reside de forma estática no main.php
    // Para garantir que esteja no topo absoluto do DOM.


    // --- DELEGAÇÃO DE EVENTOS CLIQUE (Notificações, Perfil, Lidas) ---
    document.addEventListener('click', function(e) {
        // 1. Notificações Toggle
        const notifBtn = e.target.closest('#notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        
        if (notifBtn) {
            e.stopPropagation();
            if (notifDropdown) notifDropdown.classList.toggle('active');
            return;
        }

        // 2. Limpar Lidas
        const clearBtn = e.target.closest('#notifClearBtn');
        if (clearBtn) {
            e.stopPropagation();
            if (pendingNotifs.length > 0) {
                // Ao invés de esvaziar a lista fisicamente (o que destroi o histórico),
                // gravamos o maior ID como 'Lido' no computador do usuário.
                window.localStorage.setItem('Facchini_lastSeenNotifId', pendingNotifs[0].id);
            }
            refreshSPA();
            // Optional: Close dropdown automatically?
            // const notifDropdown = document.getElementById('notifDropdown');
            // if (notifDropdown) notifDropdown.classList.remove('active');
            return;
        }

        // 3. Fechar Notificações clicando fora
        if (notifDropdown && notifDropdown.classList.contains('active') && !notifDropdown.contains(e.target)) {
            notifDropdown.classList.remove('active');
        }

        // 3b. "Ver todas as notificações" no footer
        const viewAllLink = e.target.closest('.notif-view-all');
        if (viewAllLink) {
            e.preventDefault();
            e.stopPropagation();
            if (notifDropdown) notifDropdown.classList.remove('active');
            if (typeof window.navigateTo === 'function') {
                window.navigateTo('/infractions');
            } else {
                window.location.href = viewAllLink.getAttribute('href');
            }
            return;
        }

        // 4. Perfil Toggle
        const profileTrigger = e.target.closest('#profileTrigger');
        const profileModal = document.getElementById('userProfileModal');
        
        if (profileTrigger && profileModal) {
            e.stopPropagation();
            const isOpen = profileModal.classList.contains('active');
            if (isOpen) {
                closeProfileDropdown(profileModal);
            } else {
                openProfileDropdown(profileModal);
            }
            return;
        }

        // 5. Fechar Perfil clicando fora
        const activeProfileModal = document.querySelector('#userProfileModal.active');
        if (activeProfileModal && !activeProfileModal.contains(e.target)) {
            closeProfileDropdown(activeProfileModal);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('#userProfileModal.active');
            if (activeModal) closeProfileDropdown(activeModal);
            
            const notifDropdown = document.getElementById('notifDropdown');
            if (notifDropdown && notifDropdown.classList.contains('active')) {
                notifDropdown.classList.remove('active');
            }
        }
    });

    // --- FUNÇÕES DE CONTROLE DE PERFIL ---
    function openProfileDropdown(modal) {
        modal.style.display = 'block';
        modal.style.opacity = '0';
        modal.style.transform = 'translateY(-10px)';
        modal.classList.add('active');
        
        requestAnimationFrame(() => {
            modal.style.transition = 'all 0.3s ease';
            modal.style.opacity = '1';
            modal.style.transform = 'translateY(0)';
        });
    }

    function closeProfileDropdown(modal) {
        modal.style.opacity = '0';
        modal.style.transform = 'translateY(-10px)';
        modal.classList.remove('active');
        
        setTimeout(() => {
            if (!modal.classList.contains('active')) {
                modal.style.display = 'none';
            }
        }, 300);
    }

    // --- FUNÇÃO DE DETALHE DE UMA NOTIFICAÇÃO ---
    function renderNotificationDetail(notif) {
        const notifList = document.getElementById('notifList');
        if (!notifList) return;

        notifList.innerHTML = '';
        
        const nome    = notif.funcionario_nome || notif.funcionario || (window.I18N?.labels?.unknown || 'Desconhecido');
        const setor   = notif.setor_sigla || '-';
        const epi     = notif.epi_nome || notif.epis || (window.I18N?.labels?.ppe || 'PPE');
        
        let dataStr = '-', horaStr = '-';
        if (notif.data_hora) {
            const d = new Date(notif.data_hora.replace(/-/g, '/'));
            dataStr = d.toLocaleDateString('pt-BR');
            horaStr = d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        }

        const detailDiv = document.createElement('div');
        detailDiv.className = 'notif-detail-view';
        Object.assign(detailDiv.style, {
            display: 'flex', flexDirection: 'column', gap: '16px', padding: '8px 4px'
        });

        detailDiv.innerHTML = `
            <div class="notif-back-btn" style="color:#ef4444;font-weight:700;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;width:fit-content;padding:4px 0;user-select:none;">
                <i class="fa-solid fa-arrow-left"></i> ${window.I18N?.labels?.back || 'Back'}
            </div>
            
            <div style="background:#fff1f2;border:1px solid #fecaca;border-radius:12px;padding:20px;display:flex;flex-direction:column;align-items:center;gap:8px;">
                <div style="background:#ef4444;color:white;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="margin:4px 0 0 0;color:#0f172a;font-size:17px;font-weight:800;">${nome}</h3>
                <span style="color:#ef4444;font-weight:700;font-size:12px;letter-spacing:0.5px;">${window.I18N?.labels?.infraction_upper || 'INFRACTION'}</span>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding-bottom:8px;">
                    <span style="color:#64748b;font-weight:600;font-size:13px;">${window.I18N?.labels?.sector || 'Department'}</span>
                    <span style="color:#0f172a;font-weight:800;font-size:13px;">${setor}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding-bottom:8px;">
                    <span style="color:#64748b;font-weight:600;font-size:13px;">${window.I18N?.labels?.missing_ppe || 'Missing PPE'}</span>
                    <span style="color:#ef4444;font-weight:800;font-size:13px;">${epi}</span>
                </div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding-bottom:8px;">
                    <span style="color:#64748b;font-weight:600;font-size:13px;">${window.I18N?.labels?.date || 'Date'}</span>
                    <span style="color:#0f172a;font-weight:800;font-size:13px;">${dataStr}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#64748b;font-weight:600;font-size:13px;">${window.I18N?.labels?.time || 'Time'}</span>
                    <span style="color:#0f172a;font-weight:800;font-size:13px;">${horaStr}</span>
                </div>
            </div>

            <button class="notif-goto-infractions" style="margin-top:8px;width:100%;background:#e50914;color:white;padding:12px;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;display:flex;justify-content:center;align-items:center;gap:6px;transition:background 0.2s;">
                ${window.I18N?.labels?.view_in_infractions || 'View in Infractions &rarr;'}
            </button>
        `;

        notifList.appendChild(detailDiv);

        // Botão VOLTAR — stopPropagation para não fechar o dropdown
        detailDiv.querySelector('.notif-back-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            refreshSPA();
        });

        // Hover no botão principal
        const gotoBtn = detailDiv.querySelector('.notif-goto-infractions');
        gotoBtn.addEventListener('mouseenter', () => gotoBtn.style.background = '#dc2626');
        gotoBtn.addEventListener('mouseleave', () => gotoBtn.style.background = '#e50914');
        
        // Botão IR PARA INFRAÇÕES — navega via SPA se possível, senão via href
        gotoBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('notifDropdown');
            if (dropdown) dropdown.classList.remove('active');
            // Tenta usar o sistema de navegação SPA se disponível
            if (typeof window.navigateTo === 'function') {
                window.navigateTo('/infractions');
            } else {
                window.location.href = (window.BASE_PATH || '') + '/infractions';
            }
        });
    }


    function renderNotification(notif) {
        const div = document.createElement('div');
        div.className = 'notif-item';
        div.style.cursor = 'pointer';
        
        const timeStr = notif.timeStr || (notif.data_hora ? new Date(notif.data_hora.replace(/-/g, '/')).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '');
        const ppeRecord = window.I18N?.labels?.ppe_record || 'PPE - RECORD';
        const headerText = notif.epi_nome ? `${notif.epi_nome} - ${notif.setor_sigla || 'REGISTRO'}` : (notif.epis || ppeRecord);
        const nome = notif.funcionario_nome || notif.funcionario || (window.I18N?.labels?.unknown || 'Desconhecido');

        div.innerHTML = `
            <div style="background:#fff1f2;color:#ef4444;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
               <i class="fa-solid fa-triangle-exclamation" style="font-size:17px;"></i>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;gap:3px;min-width:0;padding-right:20px;">
                <strong style="font-size:13px;color:#1e293b;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${nome}</strong>
                <div style="display:flex;align-items:center;gap:4px;color:#64748b;font-size:12px;font-weight:600;">
                    <span style="color:#f59e0b;"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${headerText.toUpperCase()}</span>
                </div>
                <span style="font-size:11px;color:#94a3b8;font-weight:600;">${timeStr}</span>
            </div>
            <button class="notif-read-btn" title="${window.I18N?.labels?.mark_as_read || 'Mark as read'}">
                <i class="fa-solid fa-check"></i>
            </button>
        `;
        
        // Clique no item → abre detalhe
        div.onclick = function(e) {
            e.stopPropagation();
            renderNotificationDetail(notif);
        };

        // Botão ✓ → marcar lida e remover com animação
        const readBtn = div.querySelector('.notif-read-btn');
        readBtn.addEventListener('click', function(e) {
            e.stopPropagation();

            // Remove do array em memória
            const idx = pendingNotifs.findIndex(n => n.id == notif.id);
            if (idx !== -1) pendingNotifs.splice(idx, 1);

            // Atualiza localStorage
            const currentSeen = parseInt(window.localStorage.getItem('Facchini_lastSeenNotifId')) || 0;
            const newSeen = Math.max(currentSeen, parseInt(notif.id));
            window.localStorage.setItem('Facchini_lastSeenNotifId', newSeen);

            // Animação de saída
            div.classList.add('removing');
            div.addEventListener('animationend', () => {
                div.remove();
                refreshSPA(); // Atualiza badge e exibe "vazio" se necessário
            }, { once: true });
        });

        return div;
    }

    // --- FUNÇÃO PARA ATUALIZAR O MODAL (LISTA PRINCIPAL) ---
    function refreshSPA() {
        const notifList    = document.getElementById('notifList');
        const notifBadge   = document.getElementById('notifBadge');
        const notifClearBtn = document.getElementById('notifClearBtn');
        
        if (!notifList) return;

        // Sempre limpar tudo que estiver na lista (items, detail views, etc.)
        notifList.innerHTML = '';

        // Recriar sempre o elemento notifEmpty para garantir que existe
        const notifEmpty = document.createElement('div');
        notifEmpty.id = 'notifEmpty';
        notifEmpty.className = 'notif-empty';
        notifEmpty.innerHTML = `
            <i class="fa-solid fa-bell-slash" style="font-size:28px;opacity:0.25;"></i>
            <span>${window.I18N?.labels?.no_new_infractions || 'Nenhuma infração nova'}</span>
        `;
        notifList.appendChild(notifEmpty);

        let lastSeenId = parseInt(window.localStorage.getItem('Facchini_lastSeenNotifId')) || 0;
        
        // Apenas as NÃO lidas aparecem na lista
        const unreadNotifs = pendingNotifs.filter(n => parseInt(n.id) > lastSeenId);
        const unreadCount  = unreadNotifs.length;

        if (unreadCount === 0) {
            notifEmpty.style.display = 'flex';
            if (notifBadge) notifBadge.classList.remove('visible');
            if (notifClearBtn) notifClearBtn.style.display = 'none';
        } else {
            notifEmpty.style.display = 'none';

            // Badge
            if (notifBadge) {
                notifBadge.classList.add('visible');
                notifBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            }

            // Botão 'Lidas' visível quando há não lidas
            if (notifClearBtn) notifClearBtn.style.display = 'block';

            // Insere SOMENTE as não lidas
            unreadNotifs.forEach(n => {
                notifList.insertBefore(renderNotification(n), notifEmpty);
            });
        }
    }
    
    // Tornar público para injetar nas re-renderezições do SPA
    window._refreshNotifSPA = refreshSPA;

    /**
     * Renderiza o card (Toast flutuante) animado
     */
    function showEpiNotification(data) {
        const container = document.getElementById('notification-container');    
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'epi-alert-toast';
        toast.style.pointerEvents = 'all';
        
        toast.innerHTML = `
            <div class="toast-icon-wrapper"><i class="epi-icon-badge"></i></div>
            <div class="toast-content">
                <span class="toast-title">${window.I18N?.labels?.epi_alert || 'Alerta de EPI'}</span>
                <div class="toast-details">
                    <div class="toast-detail">${window.I18N?.labels?.name || 'Nome'}: <span>${data.funcionario_nome || data.funcionario || 'Desc'}</span></div>
                    <div class="toast-detail">${window.I18N?.labels?.sector || 'Setor'}: <span>${data.setor_sigla || 'Geral'}</span></div>
                    <div class="toast-detail">${window.I18N?.labels?.missing_epi || 'EPI ausente'}: <span>${data.epi_nome || data.epis}</span></div>
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
                const newData = result.dados || [];
                
                if (result.status === 'success' || result.status === 'init') {
                    if (result.status === 'success' && newData.length > 0) {
                        newData.forEach((occ, index) => {
                            setTimeout(() => showEpiNotification(occ), index * 400);
                            pendingNotifs.unshift(occ); 
                        });

                        // Tocar som de notificação
                        const audio = new Audio((window.BASE_PATH || '') + '/assets/som/notificacao.mp3');
                        audio.play().catch(() => {});

                        // Se estivermos no dashboard, atualizar os dados
                        if (typeof window.loadCalendarData === 'function') {
                            window.loadCalendarData();
                        }
                    } else if (result.status === 'init') {
                        pendingNotifs = newData;
                    }
                    
                    lastOccurrenceId = result.last_id || lastOccurrenceId;
                    
                    if (pendingNotifs.length > 50) pendingNotifs = pendingNotifs.slice(0, 50);
                    
                    refreshSPA();
                }
            }).catch(() => {});
    }

    // Iniciamos apenas um polling
    pollServer();
    setInterval(pollServer, 10000);

    /**
     * Função Global para Testes de Inserção
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
}

// O motor SPA despacha este evento ao carregar cada página localmente.
// Então hidratamos os elementos com os dados em cache!
document.addEventListener('DOMContentLoaded', function() {
    if (window._refreshNotifSPA) {
        window._refreshNotifSPA();
    }
});

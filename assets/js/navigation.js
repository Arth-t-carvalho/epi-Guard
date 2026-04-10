/**
 * NAVIGATION.JS - SPA Engine (Single Page Application Transition)
 * Garante transições de página 100% fluidas sem recarregar o Sidebar.
 */

document.addEventListener("DOMContentLoaded", () => {
    // Evita loop se o DOMContentLoaded for disparado manualmente pelo nosso SPA
    if (window._spaEngineLoaded) return;
    window._spaEngineLoaded = true;

    // Inicializa a navegação
    setupLinks();
    updateSidebarActiveItem();
    
    // Animação de entrada inicial (Hard Refresh)
    const wrapper = document.querySelector('.main-content');
    if (wrapper) {
        wrapper.style.animation = "slideFromRight 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) forwards";
    }

    window.addEventListener("popstate", () => {
        // Fallback simples para botões de Voltar/Avançar do navegador
        window.location.reload(); 
    });
});

function setupLinks() {
    // Captura links, previne múltiplos bindings clonando-os
    const links = document.querySelectorAll('a[href]');
    links.forEach(link => {
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript') && link.target !== '_blank') {
            const newLink = link.cloneNode(true);
            link.replaceWith(newLink);
            
            newLink.addEventListener('click', evento => {
                evento.preventDefault();
                navigateViaSPA(href);
            });
        }
    });

    // Se estiver usando lucide, re-renderizar ícones nos novos links
    if (window.lucide) {
        lucide.createIcons();
    }
}

async function navigateViaSPA(destino, options = {}) {
    const wrapper = document.querySelector('.main-content');
    const rootBody = document.body;
    const silent = options.silent || false;

    // 0. LIMPEZA NUCLEAR: Remove modais órfãos do body que persistem entre páginas
    const stickySelectors = [
        '#evidenceModal', '#exportModal', '#modernPicker', '#confirmHideModal',
        '.evidence-modal-overlay', '.modal-premium:not(#globalAlertModal)', '.modern-picker-backdrop', '.ai-backdrop'
    ];
    
    // Only clean modals if not silent 
    if (!silent) {
        stickySelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.remove();
            });
        });
        toggleScroll(false); 
    }

    // 1. Inicia a animação de saída (se não for silent)
    if (wrapper && !silent) {
        wrapper.style.animation = 'none'; // reset
        void wrapper.offsetWidth;         // force reflow
        wrapper.style.animation = "slideToLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards";
    }

    try {
        // 2. Busca o HTML da próxima página silenciosamente
        const response = await fetch(destino);
        if (!response.ok) throw new Error('Falha na resposta: ' + response.status);
        const html = await response.text();
        
        // 3. Realiza o parse do novo documento
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const performUpdate = () => {
            // A. Atualizar Header e Título
            document.title = doc.title;
            
            if (options.replaceState || silent) {
                window.history.replaceState({}, '', destino);
            } else {
                window.history.pushState({}, '', destino);
            }
            
            // B. Muta o miolo 
            const newMain = doc.querySelector('.main-content');
            if (newMain && wrapper) {
                wrapper.style.animation = 'none';
                wrapper.innerHTML = newMain.innerHTML;
                wrapper.className = newMain.className;
            }

            // C. Injeta CSS novos e Remove CSS antigos
            const newStylesHrefs = Array.from(doc.querySelectorAll('link[rel="stylesheet"]')).map(l => l.getAttribute('href'));
            document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
                const href = link.getAttribute('href');
                if (href && !newStylesHrefs.includes(href)) {
                    link.remove();
                }
            });
            doc.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
                const href = link.getAttribute('href');
                if (href && !document.querySelector(`link[href="${href}"]`)) {
                    const newLink = document.createElement('link');
                    newLink.rel = 'stylesheet';
                    newLink.href = href;
                    document.head.appendChild(newLink);
                }
            });

            // 3. Limpa e atualiza os blocos <style> inline
            document.querySelectorAll('head style').forEach(s => s.remove());
            doc.querySelectorAll('head style').forEach(s => {
                const newStyle = document.createElement('style');
                newStyle.innerHTML = s.innerHTML;
                document.head.appendChild(newStyle);
            });

            // D. Sincroniza Estados Globais (I18N e BASE_PATH)
            const newI18nScript = doc.getElementById('i18n-bridge');
            if (newI18nScript) {
                try {
                    eval(newI18nScript.innerHTML);
                } catch(e) { console.error('[SPA] Erro ao sincronizar I18N:', e); }
            }
            const newBasePathScript = doc.getElementById('base-path-bridge');
            if (newBasePathScript) {
                try {
                    eval(newBasePathScript.innerHTML);
                } catch(e) { console.error('[SPA] Erro ao sincronizar BASE_PATH:', e); }
            }

            // E. Injeta Scripts do <head>
            const newHeadScripts = doc.querySelectorAll('head script[src]');
            const headScriptPromises = [];
            newHeadScripts.forEach(script => {
                const src = script.getAttribute('src');
                if (!src || src.includes('lucide')) return;
                if (document.querySelector(`script[src="${src}"]`)) return;
                
                const newScript = document.createElement('script');
                newScript.src = src;
                newScript.async = false;
                const p = new Promise(resolve => {
                    newScript.onload = resolve;
                    newScript.onerror = resolve;
                });
                headScriptPromises.push(p);
                document.head.appendChild(newScript);
            });

            // Aguarda CDNs do head carregarem antes de injetar scripts do body
            Promise.all(headScriptPromises).then(() => {
                // F. Injeta Scripts do <body>
                // Reseta flags de guard de páginas específicas para que cada navegação
                // SPA registre corretamente os listeners da nova página.
                window._dashboardListenerBound = false;
                window._dashboardClickHandled = false;
                window._dashboardInitCalled = false;

                const newScripts = doc.querySelectorAll('body script');
                newScripts.forEach(script => {
                    const src = script.getAttribute('src');
                    if (src && (src.includes('lucide') || src.includes('navigation.js') || src.includes('notifications.js'))) return;
                    if (script.id === 'i18n-bridge' || script.id === 'base-path-bridge') return;

                    if (src && document.querySelector(`script[src="${src}"]`)) {
                        document.querySelector(`script[src="${src}"]`).remove();
                    }

                    const newScript = document.createElement('script');
                    newScript.async = false;
                    if (src) {
                        newScript.src = src;
                    } else {
                        newScript.innerHTML = script.innerHTML;
                    }
                    document.body.appendChild(newScript);
                });

                // G. Eventos de Inicialização
                window.document.dispatchEvent(new Event("DOMContentLoaded", { bubbles: true, cancelable: true }));
                window.document.dispatchEvent(new Event("spaPageLoaded", { bubbles: true, cancelable: true }));

                setupLinks();
                
                if (wrapper && !silent) {
                    void wrapper.offsetWidth; 
                    wrapper.style.animation = "slideFromRight 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) forwards";
                    wrapper.addEventListener('animationend', () => {
                        wrapper.style.animation = '';
                    }, { once: true });
                }

                updateSidebarActiveItem();
            });
        };

        if (silent) {
            performUpdate();
        } else {
            setTimeout(performUpdate, 280);
        }
        
    } catch (err) {
        console.warn("[SPA] Fallback disparado devido a erro:", err);
        window.location.href = destino; 
    }
}

function updateSidebarActiveItem() {
    const currentPath = window.location.pathname;
    const items = document.querySelectorAll('.nav-item');
    items.forEach(item => {
        item.classList.remove('active');
        const itemHref = item.getAttribute('href');
        
        // Verifica se o caminho atual é igual ao link ou se começa por ele (para subpáginas)
        if (itemHref === currentPath || (itemHref !== '/' && currentPath.startsWith(itemHref))) {
            item.classList.add('active');
        }
    });
    
    // Gerencia botões que dependem da página atual (Ex: Exportar no cabeçalho)
    const exportBtn = document.getElementById('headerExportDashboardBtn');
    if (exportBtn) {
        if (currentPath === '/' || currentPath === '/dashboard' || currentPath.endsWith('/dashboard')) {
            exportBtn.style.display = 'flex';
        } else {
            exportBtn.style.display = 'none';
        }
    }
}



/**
 * Bloqueia ou libera o scroll da página (body, html e main-content).
 * @param {boolean} lock 
 */
function toggleScroll(lock) {
    const action = lock ? 'add' : 'remove';
    document.body.classList[action]('no-scroll');
    document.documentElement.classList[action]('no-scroll');
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.classList[action]('no-scroll');
    }
}

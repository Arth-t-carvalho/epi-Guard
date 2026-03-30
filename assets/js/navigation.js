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

async function navigateViaSPA(destino) {
    const wrapper = document.querySelector('.main-content');
    const rootBody = document.body;

    // 1. Inicia a animação de saída de forma imediata!
    if (wrapper) {
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
        
        // PRELOAD DOS  NOVOS CSS
        // Evita FOUC (Flash of Unstyled Content) fazendo o preloading antes de inserir no DOM
        const newStylesHrefs = Array.from(doc.querySelectorAll('link[rel="stylesheet"]')).map(l => l.getAttribute('href'));
        const cssPromises = [];
        
        doc.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
            const href = link.getAttribute('href');
            if (href && !document.querySelector(`link[href="${href}"]`)) {
                const newLink = document.createElement('link');
                newLink.rel = 'stylesheet';
                newLink.href = href;
                
                // Mapeia uma promise para aguardar o recarregamento assíncrono do arquivo CSS do apache
                cssPromises.push(new Promise(resolve => {
                    newLink.onload = resolve;
                    newLink.onerror = resolve; // Não travar o fluxo se o CSS não existir
                }));
                
                document.head.appendChild(newLink);
            }
        });

        // Aguarda os CSS terminarem de carregar E o timeout de animação em paralelo
        await Promise.all([
            ...cssPromises,
            new Promise(r => setTimeout(r, 280))
        ]);

        // A. Atualizar Header e Título
        document.title = doc.title;
        window.history.pushState({}, '', destino);
        
        // B. Muta o miolo 
        const newMain = doc.querySelector('.main-content');
        if (newMain && wrapper) {
            // Remove as animações pendentes antes da troca
            wrapper.style.animation = 'none';
            wrapper.innerHTML = newMain.innerHTML;
            wrapper.className = newMain.className; // Copiar classes exclusivas
        }

        // C. Remove CSS antigos vazados (Prevenção de Bug Visual)
        document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
            const href = link.getAttribute('href');
            if (href && !newStylesHrefs.includes(href)) {
                link.remove();
            }
        });

        // 3. Limpa e atualiza os blocos <style> inline do <head> (ex: overrides via PHP)
        document.querySelectorAll('head style').forEach(s => s.remove());
        doc.querySelectorAll('head style').forEach(s => {
            const newStyle = document.createElement('style');
            newStyle.innerHTML = s.innerHTML;
            document.head.appendChild(newStyle);
        });

        // D. Injeta Scripts Isolados (e re-executa JS de tela)
        const newScripts = doc.querySelectorAll('body script');
        newScripts.forEach(script => {
            const src = script.getAttribute('src');
            
            // Evitar duplicação ou travamento de bibliotecas do sidebar/globais
            if (src && (
                src.includes('lucide') || 
                src.includes('navigation.js') || 
                src.includes('notifications.js')
            )) return;

            // Remove a versão antiga do JS da página para não acavalar
            if (src && document.querySelector(`script[src="${src}"]`)) {
                document.querySelector(`script[src="${src}"]`).remove();
            }

            // Cria o nodo real do Script para forçar a avaliação (o innerHTML bruto no DOM não executa)
            const newScript = document.createElement('script');
            if (src) {
                newScript.src = src;
            } else {
                // Evita redeclarar variáveis globais usando const
                if (script.innerHTML.includes('window.BASE_PATH')) return; 
                newScript.innerHTML = script.innerHTML;
            }
            
            document.body.appendChild(newScript);
        });
        
        // E. Dispara evento DOMContentLoaded Fake para Inicializar a view
        window.document.dispatchEvent(new Event("DOMContentLoaded", {
            bubbles: true,
            cancelable: true
        }));

        // F. Re-inicia os links internos da nova Main
        setupLinks();
        
        // G. Animação de Entrada
        if (wrapper) {
            void wrapper.offsetWidth; // Reflow
            wrapper.style.animation = "slideFromRight 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) forwards";
        }

        // H. Ajustar marcações visuais na Sidebar (Ativo/Inativo)
        updateSidebarActiveItem();
        
    } catch (err) {
        console.warn("[SPA] Fallback disparado devido a erro:", err);
        // Se der problema no JS ou API, força navegação normal
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
}

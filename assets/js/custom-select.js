/**
 * ==========================================================
 * CUSTOM SELECT PREMIUM MODALS (JS ENGINE)
 * ==========================================================
 * Procura todos os seletores dropdown comuns (<select>) e 
 * os substitui por uma interface modal premium global.
 */

(function() {
    // Evita duplicidade de execução do script Global
    if (window._premiumSelectEngineLoaded) return;
    window._premiumSelectEngineLoaded = true;

    let globalModalInjected = false;
    let currentActiveSelect = null; // Guarda a referência do select nativo que acionou o modal

    // Dicionário visual para gerar títulos e ícones bons baseados no ID/Name do seletor
    const visualDictionary = {
        'setor': { 
            title: window.I18N?.labels?.select_department || 'Selecionar Setor', 
            icon: '<i class="fa-solid fa-building"></i>', 
            subtitle: window.I18N?.labels?.choose_area || 'Escolha a área da empresa' 
        },
        'funcionario': { 
            title: window.I18N?.labels?.select_employee || 'Selecionar Colaborador', 
            icon: '<i class="fa-solid fa-user-tag"></i>', 
            subtitle: window.I18N?.labels?.choose_person || 'Escolha a pessoa alvo' 
        },
        'cargo': { 
            title: window.I18N?.labels?.select_position || 'Selecionar Cargo', 
            icon: '<i class="fa-solid fa-briefcase"></i>', 
            subtitle: window.I18N?.labels?.choose_hierarchy || 'Escolha a função hierárquica' 
        },
        'epi': { 
            title: window.I18N?.labels?.select_ppe || 'Selecionar EPI', 
            icon: '<i class="fa-solid fa-helmet-safety"></i>', 
            subtitle: window.I18N?.labels?.choose_ppe || 'Escolha o equipamento de proteção' 
        },
        'motivo': { 
            title: window.I18N?.labels?.select_reason || 'Motivo / Infração', 
            icon: '<i class="fa-solid fa-clipboard-list"></i>', 
            subtitle: window.I18N?.labels?.choose_justification || 'Escolha a justificativa' 
        },
        'tipo': { 
            title: window.I18N?.labels?.select_type || 'Selecionar Tipo', 
            icon: '<i class="fa-solid fa-layer-group"></i>', 
            subtitle: window.I18N?.labels?.choose_type_desc || 'Selecione uma das opções disponíveis' 
        },
        'language': { 
            title: window.I18N?.labels?.platform_language || 'Idioma da Plataforma', 
            icon: '<i class="fa-solid fa-language"></i>', 
            subtitle: window.I18N?.labels?.change_display_lang || 'Altere o idioma de exibição' 
        },
        'default': { 
            title: window.I18N?.labels?.select_an_option || 'Selecione uma Opção', 
            icon: '<i class="fa-solid fa-check-circle"></i>', 
            subtitle: window.I18N?.labels?.tap_item_below || 'Toque em um item abaixo' 
        }
    };

    /**
     * Tenta inferir as propriedades visuais baseado nos atributos do select
     */
    function getVisualProps(selectEl) {
        const id = (selectEl.id || '').toLowerCase();
        const name = (selectEl.name || '').toLowerCase();
        const classNames = (selectEl.className || '').toLowerCase();
        
        const combined = id + ' ' + name + ' ' + classNames;
        
        let match = 'default';
        for (const key in visualDictionary) {
            if (key !== 'default' && combined.includes(key)) {
                match = key;
                break;
            }
        }
        
        return {
            title: selectEl.getAttribute('data-title') || visualDictionary[match].title,
            subtitle: selectEl.getAttribute('data-subtitle') || visualDictionary[match].subtitle,
            icon: selectEl.getAttribute('data-icon') || visualDictionary[match].icon
        };
    }

    /**
     * Injeta a estrutura do Modal Global HTML no <body>
     */
    function injectGlobalModal() {
        if (globalModalInjected || document.getElementById('globalPremiumSelectModal')) return;
        
        const markup = `
            <div id="globalPremiumSelectModal" class="premium-select-backdrop">
                <div class="premium-select-modal">
                    <div class="premium-select-header">
                        <h3 class="premium-select-title" id="psModalTitle">${window.I18N?.labels?.select_an_option || 'Selecionar Opção'}</h3>
                        <p class="premium-select-subtitle" id="psModalSubtitle">${window.I18N?.labels?.tap_item_below || 'Toque em um item abaixo'}</p>
                        <button type="button" class="premium-select-close" onclick="closePremiumModal()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Barra de Busca Premium -->
                    <div class="ps-search-wrapper" id="psSearchWrapper" style="display: none;">
                        <div class="ps-search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="psSearchInput" placeholder="${window.I18N?.labels?.search_placeholder || 'Pesquisar...'}" onkeyup="filterPremiumOptions(this.value)">
                        </div>
                    </div>

                    <div class="premium-select-body" id="psModalBody">
                        <!-- Opções renderizadas aqui via JS -->
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', markup);

        // Permitir fechar ao clicar no backdrop escuro
        document.getElementById('globalPremiumSelectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePremiumModal();
            }
        });

        globalModalInjected = true;
    }

    /**
     * Função Global para fechar
     */
    window.closePremiumModal = function() {
        const modal = document.getElementById('globalPremiumSelectModal');
        if (modal) {
            modal.classList.remove('active');
            
            // Remove a classe active também do botão que invocou (setinha muda)
            document.querySelectorAll('.premium-select-trigger.active').forEach(btn => btn.classList.remove('active'));
            
            // Religar barras de rolagem
            document.body.style.overflow = '';
        }
    };

    /**
     * Analisa o Select Nativo, gera o HTML das opções e abre o modal
     */
    function openPremiumModalForSelect(selectEl, props) {
        const modal = document.getElementById('globalPremiumSelectModal');
        const titleEl = document.getElementById('psModalTitle');
        const subEl = document.getElementById('psModalSubtitle');
        const bodyEl = document.getElementById('psModalBody');

        if (!modal) return;

        currentActiveSelect = selectEl; // Contexto Global Ativo

        // Preenche info do cabeçalho
        titleEl.textContent = props.title;
        subEl.textContent = props.subtitle;

        // Resetar e Configurar Busca
        const searchWrapper = document.getElementById('psSearchWrapper');
        const searchInput = document.getElementById('psSearchInput');
        if (searchInput) searchInput.value = '';

        // Só mostrar busca para Setor, Funcionário e EPI
        if (searchWrapper) {
            const titleLower = props.title.toLowerCase();
            const subtitleLower = props.subtitle.toLowerCase();
            if (titleLower.includes('setor') || 
                titleLower.includes('colaborador') || 
                titleLower.includes('funcionário') || 
                titleLower.includes('funcionario') || 
                titleLower.includes('epi') ||
                subtitleLower.includes('área') ||
                subtitleLower.includes('pessoa')) {
                searchWrapper.style.display = 'block';
            } else {
                searchWrapper.style.display = 'none';
            }
        }

        // Limpa opções antigas
        bodyEl.innerHTML = '';

        // Cria os Cartões (Cards) das <option>
        const options = Array.from(selectEl.options);
        
        options.forEach(opt => {
            // Ignorar opções placeholders com valor vazio contendo apenas "selecione"
            if (!opt.value && opt.text.toLowerCase().includes('selecione')) return;

            const card = document.createElement('div');
            
            // Marcar como selected visualmente
            const isSelected = (opt.value === selectEl.value);
            card.className = `premium-option-card ${isSelected ? 'selected' : ''}`;
            
            // Cria Subtítulo interno da opção baseado no VALUE se for numérico (Ex: ID: #4)
            let optSubtitleHTML = '';
            if (opt.value && !isNaN(opt.value)) {
                optSubtitleHTML = `<span class="premium-option-id">ID: #${opt.value}</span>`;
            } else if (opt.value) {
                // Caso não numérico (pode ser o alias da linguagem ou afins)
                optSubtitleHTML = `<span class="premium-option-id">Ref: ${opt.value.toUpperCase()}</span>`;
            } else {
                optSubtitleHTML = `<span class="premium-option-id">-</span>`;
            }

            // Detecta ícone específico se for um seletor de EPI
            let finalIcon = props.icon;
            if (props.title.toLowerCase().includes('epi') || props.subtitle.toLowerCase().includes('equipamento')) {
                const optText = opt.text.toLowerCase();
                if (optText.includes('capacete')) finalIcon = '<i class="fa-solid fa-helmet-safety"></i>';
                else if (optText.includes('luva')) finalIcon = '<i class="fa-solid fa-hand-dots"></i>';
                else if (optText.includes('óculo') || optText.includes('oculo')) finalIcon = '<i class="fa-solid fa-glasses"></i>';
                else if (optText.includes('máscara') || optText.includes('mascara')) finalIcon = '<i class="fa-solid fa-mask-face"></i>';
                else if (optText.includes('protetor')) finalIcon = '<i class="fa-solid fa-ear-deaf"></i>';
                else if (optText.includes('bota') || optText.includes('sapato')) finalIcon = '<i class="fa-solid fa-shoe-prints"></i>';
                else if (optText.includes('avental') || optText.includes('capa')) finalIcon = '<i class="fa-solid fa-vest"></i>';
            }

            card.innerHTML = `
                <div class="premium-option-icon">
                    ${finalIcon}
                </div>
                <div class="premium-option-info">
                    <span class="premium-option-name">${opt.text}</span>
                    ${optSubtitleHTML}
                </div>
            `;

            // Ação de clique do Card Option
            card.addEventListener('click', () => {
                commitSelection(opt.value, opt.text);
            });

            bodyEl.appendChild(card);
        });

        // Exibir e Travar Scroll Global
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Filtra os cartões de opções no modal premium
     */
    window.filterPremiumOptions = function(query) {
        const term = query.toLowerCase().trim();
        const cards = document.querySelectorAll('.premium-option-card');
        
        cards.forEach(card => {
            const name = card.querySelector('.premium-option-name').textContent.toLowerCase();
            // Também busca no ID/Subtitle se houver
            const sub = card.querySelector('.premium-option-id')?.textContent.toLowerCase() || '';
            
            if (name.includes(term) || sub.includes(term)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    };

    /**
     * Aplica a seleção
     */
    function commitSelection(value, text) {
        if (!currentActiveSelect) return;

        // Atualiza nativamente o Select
        currentActiveSelect.value = value;
        
        // Atualiza visualmente o Botão Customizado Irmão do Select
        const triggerBtn = currentActiveSelect.nextElementSibling;
        if (triggerBtn && triggerBtn.classList.contains('premium-select-trigger')) {
            const spanText = triggerBtn.querySelector('.trigger-value');
            if (spanText) spanText.textContent = text;
        }

        // Dispara evento 'change' nativo para que outras libs (filtros, backend) sintam a edição
        currentActiveSelect.dispatchEvent(new Event('change', { bubbles: true }));

        closePremiumModal();
    }

    /**
     * Inicializa o binding das tags <select> não processadas
     */
    function initPremiumSelects() {
        injectGlobalModal();

        const allSelects = document.querySelectorAll('select');
        
        allSelects.forEach(select => {
            // Pule se já foi convertido
            if (select.dataset.premiumBound) return;
            select.dataset.premiumBound = 'true';

            // Pule selects que possuam intenção exata de ser UI de picker própria (fallback)
            if (select.classList.contains('no-premium-modal')) return;

            const visualProps = getVisualProps(select);

            // Determina valor inicial a mostrar
            const defaultText = select.options[select.selectedIndex]?.text || visualProps.title;

            // Cria Botão customizado que substituirá a presença visual
            const trigger = document.createElement('div');
            // Copia as margens e afins do select original (simplificado)
            trigger.className = 'premium-select-trigger ' + select.className.replace('form-input', '').replace('settings-select', '');
            trigger.innerHTML = `
                <span class="trigger-value">${defaultText}</span>
                <i data-lucide="chevron-down" class="lucide"></i>
            `;

            // Estilos Inlines Críticos do Original transferidos
            if (select.style.marginTop) trigger.style.marginTop = select.style.marginTop;
            if (select.style.marginBottom) trigger.style.marginBottom = select.style.marginBottom;

            // Insere o Botão depois do Select original
            select.parentNode.insertBefore(trigger, select.nextSibling);

            // Oculta Select Original visualmente, mas mantém no DOM para validações (required)
            select.style.position = 'absolute';
            select.style.opacity = '0';
            select.style.pointerEvents = 'none';
            select.style.width = '1px';
            select.style.height = '1px';
            select.style.zIndex = '-1';

            // Abre o modal ao clicar (e anima a setinha)
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                trigger.classList.add('active');
                openPremiumModalForSelect(select, visualProps);
            });
            
            // Monitora mudanças externas que afetem o select original (Ex: Limpeza de formulário)
            select.addEventListener('change', () => {
                const refreshedText = select.options[select.selectedIndex]?.text || '';
                const span = trigger.querySelector('.trigger-value');
                if (span && refreshedText !== span.textContent) {
                    span.textContent = refreshedText;
                }
            });
        });

        // Re-injetar Icones (pois o JS pode gerar botões antes do lucide passar)
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // --- ATIVAÇÃO ---
    // Executa no load original
    document.addEventListener('DOMContentLoaded', initPremiumSelects);
    
    // Executa em cada navegação de página do SPA
    document.addEventListener('spaPageLoaded', initPremiumSelects);
    
    // Fallback: Timeout e/ou MutationObserver simples se itens renderizam por API depois do DOMContentLoaded
    setTimeout(initPremiumSelects, 200);

})();

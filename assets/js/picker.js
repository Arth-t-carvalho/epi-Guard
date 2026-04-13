/**
 * PICKER.JS - Lógica compartilhada do seletor moderno (Apple Style)
 */

let currentPickerType = null;

function openModernPicker(type) {
    currentPickerType = type;
    const modal = document.getElementById('modernPicker');
    const container = document.getElementById('pickerOptionsContainer');
    const title = document.getElementById('pickerTitle');
    const subtitle = document.getElementById('pickerSubtitle');

    if (!modal || !container || !window.PICKER_OPTIONS || !window.PICKER_OPTIONS[type]) return;

    // Configurar títulos e subtítulos personalizados
    const titles = {
        periodo: 'Selecionar Período',
        status: 'Selecionar Status',
        epi: 'Selecionar EPI',
        visualizacao: 'Tipo de Visualização',
        risk: 'Nível de Risco',
        cargo: 'Selecionar Cargo',
        setor: 'Selecionar Setor',
        funcionario: 'Selecionar Funcionário'
    };
    
    const subtitles = {
        periodo: 'Filtre as informações por data',
        status: 'Veja itens arquivados, pendentes ou resolvidos',
        epi: 'Escolha um equipamento específico',
        visualizacao: 'Escolha como os dados serão exibidos',
        risk: 'Filtre setores pelo nível de infrações',
        cargo: 'Filtre instrutores por nível hierárquico',
        setor: 'Filtre instrutores por área de atuação',
        funcionario: 'Filtre por um colaborador específico'
    };

    title.textContent = titles[type] || 'Selecionar';
    subtitle.textContent = subtitles[type] || 'Escolha uma opção abaixo';

    // Resetar busca
    const searchWrapper = document.getElementById('pickerSearchWrapper');
    const searchInput = document.getElementById('pickerSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }

    // Só mostrar busca para Setor, Funcionário e EPI
    if (searchWrapper) {
        if (['setor', 'funcionario', 'epi'].includes(type)) {
            searchWrapper.style.display = 'block';
        } else {
            searchWrapper.style.display = 'none';
        }
    }

    // Capturar valor atual do campo oculto
    const inputId = `hidden${type.charAt(0).toUpperCase() + type.slice(1)}`;
    const hiddenInput = document.getElementById(inputId);
    const currentValue = hiddenInput ? hiddenInput.value : '';

    // Renderizar opções
    container.innerHTML = '';
    window.PICKER_OPTIONS[type].forEach(opt => {
        const isSelected = String(opt.value) === String(currentValue);
        const div = document.createElement('div');
        div.className = `modern-picker-option ${isSelected ? 'selected' : ''}`;
        div.onclick = () => selectModernOption(type, opt.value, opt.label);
        
        div.innerHTML = `
            <div class="option-content">
                <span class="option-label">${opt.label}</span>
                ${opt.description ? `<span class="option-desc">${opt.description}</span>` : ''}
            </div>
            <i class="fa-solid fa-check"></i>
        `;
        container.appendChild(div);
    });

    modal.classList.add('active');
    toggleScroll(true);
}

function closeModernPicker() {
    const modal = document.getElementById('modernPicker');
    if (modal) {
        modal.classList.remove('active');
        toggleScroll(false);
    }
}

/**
 * Filtra as opções do picker em tempo real
 */
function filterPickerOptions(query) {
    const term = query.toLowerCase().trim();
    const options = document.querySelectorAll('.modern-picker-option');
    
    options.forEach(opt => {
        const label = opt.querySelector('.option-label').textContent.toLowerCase();
        if (label.includes(term)) {
            opt.style.display = 'flex';
        } else {
            opt.style.display = 'none';
        }
    });
}

function selectModernOption(type, value, label) {
    const inputId = `hidden${type.charAt(0).toUpperCase() + type.slice(1)}`;
    const hiddenInput = document.getElementById(inputId);
    
    if (hiddenInput) {
        hiddenInput.value = value;
    }

    // Resetar funcionário se o setor mudar
    if (type === 'setor') {
        const funcInput = document.getElementById('hiddenFuncionario');
        if (funcInput) funcInput.value = 'todos';
    }

    const labelId = `label-${type}`;
    const labelEl = document.getElementById(labelId);
    if (labelEl) {
        labelEl.textContent = label;
    }

    closeModernPicker();

    // ESPECIAL: Se for período personalizado, mostrar a barra ao lado
    if (type === 'periodo' && value === 'personalizado') {
        const bar = document.getElementById('customDateBar');
        if (bar) bar.classList.add('active');
        return; 
    }
    
    // Pequeno delay para animação fluida antes do submit
    setTimeout(() => {
        const form = document.querySelector('.filter-bar') || document.getElementById('filterForm');
        
        if (form && typeof navigateViaSPA === 'function') {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = window.location.pathname + '?' + params.toString();
            navigateViaSPA(url); // Aqui usamos animação normal (não-silent) para troca de filtros grandes
        } else if (form && typeof form.submit === 'function') {
            form.submit();
        } else {
            // Se não houver form, usar SPA se disponível
            const url = new URL(window.location.href);
            url.searchParams.set(type, value);
            if (typeof navigateViaSPA === 'function') {
                navigateViaSPA(url.toString());
            } else {
                window.location.href = url.toString();
            }
        }
    }, 150);
}

// Fechar ao clicar fora
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modernPicker');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target.classList.contains('modern-picker-backdrop')) {
                closeModernPicker();
            }
        });
    }
});

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
        setor: 'Selecionar Setor'
    };
    
    const subtitles = {
        periodo: 'Filtre as informações por data',
        status: 'Veja itens ativos, pendentes ou resolvidos',
        epi: 'Escolha um equipamento específico',
        visualizacao: 'Escolha como os dados serão exibidos',
        risk: 'Filtre setores pelo nível de infrações',
        cargo: 'Filtre instrutores por nível hierárquico',
        setor: 'Filtre instrutores por área de atuação'
    };

    title.textContent = titles[type] || 'Selecionar';
    subtitle.textContent = subtitles[type] || 'Escolha uma opção abaixo';

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
            <span>${opt.label}</span>
            <i class="fa-solid fa-check"></i>
        `;
        container.appendChild(div);
    });

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModernPicker() {
    const modal = document.getElementById('modernPicker');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function selectModernOption(type, value, label) {
    const inputId = `hidden${type.charAt(0).toUpperCase() + type.slice(1)}`;
    const hiddenInput = document.getElementById(inputId);
    
    if (hiddenInput) {
        hiddenInput.value = value;
    }

    const labelId = `label-${type}`;
    const labelEl = document.getElementById(labelId);
    if (labelEl) {
        labelEl.textContent = label;
    }

    closeModernPicker();
    
    // Pequeno delay para animação fluida antes do submit
    setTimeout(() => {
        const form = document.querySelector('.filter-bar') || document.getElementById('filterForm');
        if (form && typeof form.submit === 'function') {
            form.submit();
        } else {
            // Se não houver form, recarregar com parâmetros
            const url = new URL(window.location.href);
            url.searchParams.set(type, value);
            window.location.href = url.toString();
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

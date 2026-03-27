<?php
/**
 * View: Ocorrências (Registro)
 * Exibe o formulário de registro de novas ocorrências de segurança.
 */
$pageTitle = 'Registrar Ocorrência - EPI Guard';
ob_start();
?>
<script>
    window.I18N = {
        'Por favor, selecione um funcionário.': '<?= __('Por favor, selecione um funcionário.') ?>',
        'Salvando...': '<?= __('Salvando...') ?>',
        'Confirmar Ocorrência': '<?= __('Confirmar Ocorrência') ?>',
        'Ocorrreu um erro ao enviar os dados.': '<?= __('Ocorreu um erro ao enviar os dados.') ?>',
        'Adicionar': '<?= __('Adicionar') ?>'
    };
</script>

<div class="registration-container">
    <div class="registration-card">
        <div class="card-header-premium">
            <div class="header-text-content">
                <h2><?= __('Registrar Ocorrência') ?></h2>
                <p><?= __('Preencha os dados abaixo para registrar uma nova ocorrência de segurança.') ?></p>
            </div>
        </div>
        
        <div class="card-body-premium">
            <form class="occurrence-form" id="occurrence-form">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label"><?= __('Setor') ?></label>
                        <select class="form-input" id="occ-setor">
                            <option value=""><?= __('Selecione um setor...') ?></option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept->getId() ?>"><?= htmlspecialchars($dept->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label"><?= __('Funcionário') ?></label>
                        <select class="form-input" id="occ-funcionario">
                            <option value=""><?= __('Selecione um funcionário...') ?></option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp->getId() ?>" 
                                        data-cpf="<?= htmlspecialchars($emp->getCpf()->getFormatted() ?? '') ?>" 
                                        data-id="<?= $emp->getId() ?>">
                                    <?= htmlspecialchars($emp->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Mini Tabela de Informações do Funcionário -->
                        <div id="employee-details-box" class="employee-details-container">
                            <table class="employee-details-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th><?= __('Nome Completo') ?></th>
                                        <th>CPF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="det-id">-</td>
                                        <td id="det-nome">-</td>
                                        <td id="det-cpf">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-row three-cols">
                    <div class="form-group">
                        <label class="form-label"><?= __('Motivo Principal') ?></label>
                        <select class="form-input" id="occ-motivo">
                            <option value="Falta de EPI"><?= __('Falta de EPI') ?></option>
                            <option value="EPI Danificado / Desgastado"><?= __('EPI Danificado / Desgastado') ?></option>
                            <option value="Recusa de Uso"><?= __('Recusa de Uso') ?></option>
                            <option value="Uso Incorreto do EPI"><?= __('Uso Incorreto do EPI') ?></option>
                            <option value="EPI Fora da Validade"><?= __('EPI Fora da Validade') ?></option>
                            <option value="Perda de EPI"><?= __('Perda de EPI') ?></option>
                            <option value="Outros"><?= __('Outros') ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('EPI Envolvido') ?></label>
                        <select class="form-input" id="occ-epi">
                            <option value="none"><?= __('Nenhum') ?></option>
                            <?php foreach ($epis as $epi): ?>
                                <option value="<?= $epi->getId() ?>"><?= htmlspecialchars($epi->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __('Data e Hora') ?></label>
                        <input type="datetime-local" class="form-input" id="occ-data" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>

                <div class="section-divider">
                    <h5><?= __('Ação Tomada') ?></h5>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label"><?= __('Tipo de Registro / Advertência') ?></label>
                        <select class="form-input" id="occ-tipo">
                            <option value="Orientação Técnica"><?= __('Orientação Técnica') ?></option>
                            <option value="Advertência Verbal"><?= __('Advertência Verbal') ?></option>
                            <option value="Advertência Escrita (1ª via)"><?= __('Advertência Escrita (1ª via)') ?></option>
                            <option value="Advertência Escrita (2ª via)"><?= __('Advertência Escrita (2ª via)') ?></option>
                            <option value="Suspensão (1 dia)"><?= __('Suspensão (1 dia)') ?></option>
                            <option value="Suspensão (3 dias)"><?= __('Suspensão (3 dias)') ?></option>
                            <option value="Treinamento de Reciclagem"><?= __('Treinamento de Reciclagem') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label"><?= __('Observações Adicionais') ?></label>
                        <textarea class="form-input" rows="4" id="occ-obs" placeholder="<?= __('Descreva detalhes sobre a ocorrência...') ?>"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label"><?= __('Evidências') ?></label>
                        <div class="upload-grid" id="upload-grid">
                            <div class="upload-btn" id="btn-add-evidence">
                                <i data-lucide="plus"></i>
                                <span><?= __('Adicionar') ?></span>
                            </div>
                            <input type="file" id="evidence-input" multiple accept="image/*" style="display: none;">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer-premium">
            <button type="button" class="btn-cancel" onclick="location.href='<?= BASE_PATH ?>/dashboard'"><?= __('Cancelar') ?></button>
            <button type="submit" class="btn-confirm"><?= __('Confirmar Ocorrência') ?></button>
        </div>
    </div>
</div>

<!-- Modal de Sucesso Customizado -->
<div class="success-overlay" id="success-overlay">
    <div class="success-card">
        <div class="success-icon-wrapper">
            <i data-lucide="check-circle" style="width: 36px; height: 36px;"></i>
        </div>
        <h3><?= __('Ocorrência Registrada!') ?></h3>
        <p><?= __('A ocorrência foi salva no sistema com sucesso e as evidências foram processadas.') ?></p>
        <button class="btn-success-ok" id="btn-success-ok"><?= __('Entendido') ?></button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Re-inicializar ícones do Lucide
    const initializeIcons = () => {
        if (window.lucide) {
            lucide.createIcons();
        }
    };
    initializeIcons();

    // Lógica para mostrar detalhes do funcionário
    const selectFunc = document.getElementById('occ-funcionario');
    const detailsBox = document.getElementById('employee-details-box');
    const detId = document.getElementById('det-id');
    const detNome = document.getElementById('det-nome');
    const detCpf = document.getElementById('det-cpf');

    selectFunc.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const id = selectedOption.getAttribute('data-id');
            const cpf = selectedOption.getAttribute('data-cpf');
            const nome = selectedOption.text.trim();

            detId.textContent = '#' + id;
            detNome.textContent = nome;
            detCpf.textContent = cpf;

            detailsBox.style.display = 'block';
        } else {
            detailsBox.style.display = 'none';
        }
    });

    const successOverlay = document.getElementById('success-overlay');
    const btnSuccessOk = document.getElementById('btn-success-ok');

    btnSuccessOk.addEventListener('click', () => {
        location.reload(); 
    });

    // EVIDENCE UPLOAD LOGIC
    const btnAddEvidence = document.getElementById('btn-add-evidence');
    const evidenceInput = document.getElementById('evidence-input');
    const uploadGrid = document.getElementById('upload-grid');

    btnAddEvidence.addEventListener('click', () => {
        evidenceInput.click();
    });

    evidenceInput.addEventListener('change', function() {
        const previousPreviews = uploadGrid.querySelectorAll('.evidence-preview');
        previousPreviews.forEach(p => p.remove());

        if (this.files) {
            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'evidence-preview';
                    previewDiv.style.cssText = `
                        aspect-ratio: 1/1;
                        border-radius: 12px;
                        overflow: hidden;
                        border: 1px solid var(--border);
                        position: relative;
                    `;

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';

                    previewDiv.appendChild(img);
                    uploadGrid.insertBefore(previewDiv, btnAddEvidence);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // FORM SUBMISSION
    const btnConfirm = document.querySelector('.btn-confirm');
    btnConfirm.addEventListener('click', async function() {
        const formData = new FormData();
        
        const funcionarioId = document.getElementById('occ-funcionario').value;
        const epiId = document.getElementById('occ-epi').value;
        const dataHora = document.getElementById('occ-data').value;
        const tipoAcao = document.getElementById('occ-tipo').value;
        const observacao = document.getElementById('occ-obs').value;

        if (!funcionarioId) {
            alert(window.I18N['Por favor, selecione um funcionário.']);
            return;
        }

        formData.append('funcionario_id', funcionarioId);
        formData.append('epi_id', epiId);
        formData.append('data_hora', dataHora);
        formData.append('tipo_acao', tipoAcao);
        formData.append('observacao', observacao);

        if (evidenceInput.files.length > 0) {
            for (let i = 0; i < evidenceInput.files.length; i++) {
                formData.append('evidencias[]', evidenceInput.files[i]);
            }
        }

        btnConfirm.disabled = true;
        btnConfirm.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${window.I18N['Salvando...']}`;

        try {
            const response = await fetch(window.BASE_PATH + '/api/occurrence/store', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.body.appendChild(successOverlay);
                document.body.classList.add('modal-open');
                successOverlay.classList.add('active');
                
                if (window.lucide) {
                    lucide.createIcons();
                }
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('<?= __('Ocorrreu um erro ao enviar os dados.') ?>');
        } finally {
            btnConfirm.disabled = false;
            btnConfirm.textContent = window.I18N['Confirmar Ocorrência'];
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

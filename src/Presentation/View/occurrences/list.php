<?php
ob_start();
$pageTitle = 'epiGuard - Registrar Ocorrência';
$extraHead = '
<style>
    .registration-container {
        display: flex;
        justify-content: center;
        padding: 20px 48px;
        min-height: calc(100vh - 120px);
    }
    .registration-card {
        background: var(--bg-card, #ffffff);
        border-radius: var(--radius, 16px);
        width: 100%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border, #e5e7eb);
        overflow: hidden;
        animation: slideUp 0.4s ease-out;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card-header-premium {
        padding: 24px 32px;
        border-bottom: 1px solid var(--border, #e5e7eb);
        display: flex;
        align-items: center;
        gap: 16px;
        background: #E30613;

    }
    .card-header-premium i {
        background: var(--primary, #E30613);
        color: white;
        padding: 10px;
        border-radius: 12px;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-header-premium div h2 {
        font-size: 18px;
        font-weight: 800;
        color: #fff;
        margin: 0;
    }
    .card-header-premium div p {
        font-size: 12px;
        color:  #fff;
        margin: 2px 0 0 0;
    }
    .card-body-premium {
        padding: 32px;
    }
    .card-footer-premium {
        padding: 20px 32px;
        border-top: 1px solid var(--border, #e5e7eb);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #fafafa;
    }
    .btn-cancel {
        padding: 10px 22px;
        border: 1px solid var(--border, #e5e7eb);
        background: white;
        color: var(--text-muted, #94a3b8);
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        font-size: 13px;
    }
    .btn-cancel:hover { background: #fef2f2; color: var(--primary, #E30613); border-color: var(--primary); }
    .btn-confirm {
        padding: 10px 24px;
        background: var(--primary, #E30613);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        font-size: 13px;
    }
    .btn-confirm:hover { background: #c20510; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(227, 6, 19, 0.2); }
    .btn-confirm:disabled { opacity: 0.7; cursor: not-allowed; }

    /* Success Modal Styles */
    #success-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.75); /* Mais escuro para foco total */
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    #success-overlay.active { display: flex; opacity: 1; }
    
    body.modal-open {
        overflow: hidden !important;
        height: 100vh !important;
    }
    .success-card {
        background: white;
        padding: 40px;
        border-radius: 24px;
        text-align: center;
        max-width: 380px;
        width: 90%;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        transform: scale(0.8);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .success-overlay.active .success-card { transform: scale(1); }
    .success-icon-wrapper {
        width: 72px;
        height: 72px;
        background: #f0fdf4;
        color: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px auto;
    }
    .success-card h3 { font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 8px; }
    .success-card p { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; line-height: 1.5; }
    .btn-success-ok {
        padding: 12px 32px;
        background: var(--secondary, #1F2937);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        width: 100%;
    }
    .btn-success-ok:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }

    /* Estilos para a Tabela de Detalhes do Funcionário */
    .employee-details-container {
        margin-top: 15px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .employee-details-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .employee-details-table th {
        background: #f1f5f9;
        text-align: left;
        padding: 10px 16px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
    }
    .employee-details-table td {
        padding: 12px 16px;
        color: #1e293b;
        font-weight: 600;
        border-bottom: 1px solid #f1f5f9;
    }
    .employee-details-table tr:last-child td {
        border-bottom: none;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
';
?>
<div class="registration-container">
    <div class="registration-card">
        <div class="card-header-premium">
            <div>
                <h2>Registrar Ocorrência</h2>
                <p>Preencha os dados abaixo para registrar uma nova ocorrência de segurança.</p>
            </div>
        </div>
        
        <div class="card-body-premium">
            <form class="occurrence-form" id="occurrence-form">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Setor</label>
                        <select class="form-input" id="occ-setor">
                            <option value="">Selecione um setor...</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept->getId() ?>"><?= htmlspecialchars($dept->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Funcionário</label>
                        <select class="form-input" id="occ-funcionario">
                            <option value="">Selecione um funcionário...</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp->getId() ?>" 
                                        data-cpf="<?= htmlspecialchars($emp->getCpf()->getFormatted()) ?>" 
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
                                        <th>Nome Completo</th>
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
                        <label class="form-label">Motivo Principal</label>
                        <select class="form-input" id="occ-motivo">
                            <option value="">Falta de EPI</option>
                            <option value="EPI Danificado / Desgastado">EPI Danificado / Desgastado</option>
                            <option value="Recusa de Uso">Recusa de Uso</option>
                            <option value="Uso Incorreto do EPI">Uso Incorreto do EPI</option>
                            <option value="EPI Fora da Validade">EPI Fora da Validade</option>
                            <option value="Perda de EPI">Perda de EPI</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">EPI Envolvido</label>
                        <select class="form-input" id="occ-epi">
                            <option value="none">Nenhum</option>
                            <?php foreach ($epis as $epi): ?>
                                <option value="<?= $epi->getId() ?>"><?= htmlspecialchars($epi->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data e Hora</label>
                        <input type="datetime-local" class="form-input" id="occ-data" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>

                <div class="section-divider">
                    <h5>Ação Tomada</h5>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Tipo de Registro / Advertência</label>
                        <select class="form-input" id="occ-tipo">
                            <option value="Orientação Técnica">Orientação Técnica</option>
                            <option value="Advertência Verbal">Advertência Verbal</option>
                            <option value="Advertência Escrita (1ª via)">Advertência Escrita (1ª via)</option>
                            <option value="Advertência Escrita (2ª via)">Advertência Escrita (2ª via)</option>
                            <option value="Suspensão (1 dia)">Suspensão (1 dia)</option>
                            <option value="Suspensão (3 dias)">Suspensão (3 dias)</option>
                            <option value="Treinamento de Reciclagem">Treinamento de Reciclagem</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Observações Adicionais</label>
                        <textarea class="form-input" rows="4" id="occ-obs" placeholder="Descreva detalhes sobre a ocorrência..."></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Evidências</label>
                        <div class="upload-grid" id="upload-grid">
                            <div class="upload-btn" id="btn-add-evidence">
                                <i data-lucide="plus"></i>
                                <span>Adicionar</span>
                            </div>
                            <input type="file" id="evidence-input" multiple accept="image/*" style="display: none;">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer-premium">
            <button type="button" class="btn-cancel" onclick="location.href='<?= BASE_PATH ?>/dashboard'">Cancelar</button>
            <button type="submit" class="btn-confirm">Confirmar Ocorrência</button>
        </div>
    </div>
</div>

<!-- Modal de Sucesso Customizado -->
<div class="success-overlay" id="success-overlay">
    <div class="success-card">
        <div class="success-icon-wrapper">
            <i data-lucide="check-circle" style="width: 36px; height: 36px;"></i>
        </div>
        <h3>Ocorrência Registrada!</h3>
        <p>A ocorrência foi salva no sistema com sucesso e as evidências foram processadas.</p>
        <button class="btn-success-ok" id="btn-success-ok">Entendido</button>
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
            const nome = selectedOption.text;

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
            alert('Por favor, selecione um funcionário.');
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
        btnConfirm.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Salvando...';

        try {
            const response = await fetch(window.BASE_PATH + '/api/occurrence/store', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // CORREÇÃO MÁXIMA: Move o modal para o root do body para ignorar qualquer transform dos pais
                document.body.appendChild(successOverlay);
                
                document.body.classList.add('modal-open');
                successOverlay.classList.add('active');
                
                // Re-inicializa ícones dentro do novo contexto do body
                if (window.lucide) {
                    lucide.createIcons();
                }
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ocorreu um erro ao enviar os dados.');
        } finally {
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'Confirmar Ocorrência';
        }
    });

    // Dark Mode Support styles for previews
    const style = document.createElement('style');
    style.textContent = `
        .evidence-preview:hover { transform: scale(1.05); transition: transform 0.2s; cursor: pointer; border-color: var(--primary); }
    `;
    document.head.appendChild(style);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>

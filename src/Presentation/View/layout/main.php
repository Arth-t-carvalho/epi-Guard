<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Facchini' ?></title>
    <meta name="google" content="notranslate">
    <!-- Leitura imediata de tema (Sempre Light conforme solicitado) -->
    <script>
        (function () {
            // Removendo auto-leitura de tema escuro para manter conforme Foto 2 (Modo Claro)
            document.documentElement.classList.remove('dark-theme');
            localStorage.setItem('Facchini-theme', 'light');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
        href="<?= BASE_PATH ?>/assets/css/global.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet"
        href="<?= BASE_PATH ?>/assets/css/sidebar.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/modal/modalBase.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/custom-select.css?v=<?= APP_VERSION ?>">
    <script src="<?= BASE_PATH ?>/assets/vendor/lucide.min.js?v=<?= APP_VERSION ?>"></script>
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/images/logo.png?v=<?= APP_VERSION ?>">
    <?= $extraHead ?? '' ?>
</head>

<body data-base-path="<?= BASE_PATH ?>">
    <div class="app-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <?php include __DIR__ . '/header.php'; ?>

            <div id="page-content-wrapper" class="content-fade">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <!-- Global Variables for JS -->
    <script id="base-path-bridge">window.BASE_PATH = '<?= BASE_PATH ?>';</script>
    <!-- Global I18N Bridge for JS -->
    <script id="i18n-bridge">
        window.I18N = {
            months: [
                <?= json_encode(__('Janeiro')) ?>, <?= json_encode(__('Fevereiro')) ?>, <?= json_encode(__('Março')) ?>,
                <?= json_encode(__('Abril')) ?>, <?= json_encode(__('Maio')) ?>, <?= json_encode(__('Junho')) ?>,
                <?= json_encode(__('Julho')) ?>, <?= json_encode(__('Agosto')) ?>, <?= json_encode(__('Setembro')) ?>,
                <?= json_encode(__('Outubro')) ?>, <?= json_encode(__('Novembro')) ?>, <?= json_encode(__('Dezembro')) ?>
            ],
            labels: {
                occurrence: <?= json_encode(__('ocorrência')) ?>,
                occurrences: <?= json_encode(__('ocorrências')) ?>,
                found: <?= json_encode(__('encontrada')) ?>,
                foundPlural: <?= json_encode(__('encontradas')) ?>,
                no_records: <?= json_encode(__('Nenhuma ocorrência registrada para este dia.')) ?>,
                no_records_simple: <?= json_encode(__('Sem registros')) ?>,
                total: <?= json_encode(__('Total')) ?>,
                critical: <?= json_encode(__('🚨 CRÍTICO')) ?>,
                high_risk: <?= json_encode(__('🟠 ALTO RISCO')) ?>,
                moderate: <?= json_encode(__('🟡 MODERADO')) ?>,
                controlled: <?= json_encode(__('🟢 CONTROLADO')) ?>,
                infraction_detected: <?= json_encode(__('Infração Detectada')) ?>,
                no_new_infractions: <?= json_encode(__('Nenhuma infração nova')) ?>,
                select_option: <?= json_encode(__('Selecionar')) ?>,
                choose_option: <?= json_encode(__('Escolha uma opção abaixo')) ?>,
                cancel: <?= json_encode(__('Cancelar')) ?>,
                rank: <?= json_encode(__('Posição')) ?>,
                course: <?= json_encode(__('Curso/Setor')) ?>,
                infractions: <?= json_encode(__('Infrações')) ?>,
                conformity: <?= json_encode(__('Conformidade')) ?>,
                risk: <?= json_encode(__('Risco')) ?>,
                date: <?= json_encode(__('Data')) ?>,
                student: <?= json_encode(__('Aluno/Colaborador')) ?>,
                infraction_epi: <?= json_encode(__('Infração (EPI)')) ?>,
                time: <?= json_encode(__('Horário')) ?>,
                status: <?= json_encode(__('Status')) ?>,
                no_records_found: <?= json_encode(__('Nenhum registro encontrado.')) ?>,
                pending: <?= json_encode(__('Pendente')) ?>,
                resolved: <?= json_encode(__('Resolvido')) ?>,
                connection_error: <?= json_encode(__('Erro na conexão.')) ?>,
                loading: <?= json_encode(__('Carregando...')) ?>,
                success: <?= json_encode(__('Sucesso!')) ?>,
                error: <?= json_encode(__('Erro!')) ?>,
                confirm: <?= json_encode(__('Confirmar')) ?>,
                filter: <?= json_encode(__('Filtro')) ?>,
                records: <?= json_encode(__('registros')) ?>,
                selected: <?= json_encode(__('selecionados')) ?>,
                of: <?= json_encode(__('de')) ?>,
                no_epi: <?= json_encode(__('Sem')) ?>,
                unknown: <?= json_encode(__('Desconhecido')) ?>,
                epi: <?= json_encode(__('EPI')) ?>,
                new: <?= json_encode(__('Novo')) ?>,
                epi_alert: <?= json_encode(__('Alerta de EPI')) ?>,
                name: <?= json_encode(__('Nome')) ?>,
                sector: <?= json_encode(__('Setor')) ?>,
                missing_epi: <?= json_encode(__('EPI ausente')) ?>,
                missing_ppe: <?= json_encode(__('EPI Ausente')) ?>,
                back: <?= json_encode(__('Voltar')) ?>,
                infraction_upper: <?= json_encode(__('INFRAÇÃO')) ?>,
                view_in_infractions: <?= json_encode(__('Ver na página de Infrações &rarr;')) ?>,
                mark_as_read: <?= json_encode(__('Marcar como lida')) ?>,
                ppe_record: <?= json_encode(__('EPI - REGISTRO')) ?>,
                no_sector_selected: <?= json_encode(__('Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.')) ?>,
                loading_employees: <?= json_encode(__('Carregando funcionários...')) ?>,
                no_employees_found: <?= json_encode(__('Nenhum funcionário encontrado neste setor.')) ?>,
                load_error: <?= json_encode(__('Erro ao carregar dados. Tente novamente.')) ?>,
                select_at_least_one: <?= json_encode(__('Selecione pelo menos um funcionário para exportar.')) ?>,
                generating: <?= json_encode(__('Gerando')) ?>,
                completed: <?= json_encode(__('Concluído!')) ?>,
                hiding: <?= json_encode(__('Ocultando...')) ?>,
                daily: <?= json_encode(__('Diária')) ?>,
                weekly: <?= json_encode(__('Semanal')) ?>,
                monthly: <?= json_encode(__('Mensal')) ?>,
                annual: <?= json_encode(__('Anual')) ?>,
                click_to_expand: <?= json_encode(__('Clique para expandir')) ?>,
                daily_registry: <?= json_encode(__('Registro Diário')) ?>,
                ppe_distribution: <?= json_encode(__('Distribuição de EPIs')) ?>,
                top_occurrences: <?= json_encode(__('Top Ocorrências')) ?>,
                placeholder_date: <?= json_encode(__('DD/MM/AAAA')) ?>,
                occurrence_found: <?= json_encode(__('ocorrência encontrada')) ?>,
                occurrence_found_plural: <?= json_encode(__('ocorrências encontradas')) ?>,
                sun: <?= json_encode(__('Dom')) ?>,
                mon: <?= json_encode(__('Seg')) ?>,
                tue: <?= json_encode(__('Ter')) ?>,
                wed: <?= json_encode(__('Qua')) ?>,
                thu: <?= json_encode(__('Qui')) ?>,
                fri: <?= json_encode(__('Sex')) ?>,
                sat: <?= json_encode(__('Sáb')) ?>,
                year: <?= json_encode(__('ano')) ?>,
                select_an_option: <?= json_encode(__('Selecione uma Opção')) ?>,
                tap_item_below: <?= json_encode(__('Toque em um item abaixo')) ?>,
                select_department: <?= json_encode(__('Selecionar Setor')) ?>,
                choose_area: <?= json_encode(__('Escolha a área da empresa')) ?>,
                select_employee: <?= json_encode(__('Selecionar Funcionário')) ?>,
                choose_person: <?= json_encode(__('Escolha a pessoa alvo')) ?>,
                select_position: <?= json_encode(__('Selecionar Cargo')) ?>,
                choose_hierarchy: <?= json_encode(__('Escolha a função hierárquica')) ?>,
                select_ppe: <?= json_encode(__('Selecionar EPI')) ?>,
                choose_ppe: <?= json_encode(__('Escolha o equipamento de proteção')) ?>,
                select_reason: <?= json_encode(__('Motivo / Infração')) ?>,
                choose_justification: <?= json_encode(__('Escolha a justificativa')) ?>,
                select_type: <?= json_encode(__('Selecionar Tipo')) ?>,
                choose_type_desc: <?= json_encode(__('Selecione uma das opções disponíveis')) ?>,
                platform_language: <?= json_encode(__('Idioma da Plataforma')) ?>,
                change_display_lang: <?= json_encode(__('Mudar idioma de exibição')) ?>,
                report_title: <?= json_encode(__('RELATÓRIO DE INFRAÇÕES')) ?>,
                colaborador: <?= json_encode(__('Colaborador')) ?>,
                departamento: <?= json_encode(__('Departamento')) ?>,
                qtd_infracoes: <?= json_encode(__('Qtd. Infrações')) ?>,
                natureza_principal: <?= json_encode(__('Natureza Principal')) ?>,
                generated_at: <?= json_encode(__('Gerado em')) ?>,
                total_colaboradores: <?= json_encode(__('Total de Colaboradores')) ?>
            },
            epi_colors: {
                'capacete': <?= json_encode(__('Capacete')) ?>,
                'oculos': <?= json_encode(__('Óculos de Proteção')) ?>,
                'jaqueta': <?= json_encode(__('Jaqueta')) ?>,
                'avental': <?= json_encode(__('Avental')) ?>,
                'luvas': <?= json_encode(__('Luvas')) ?>,
                'mascara': <?= json_encode(__('Máscara')) ?>,
                'protetor': <?= json_encode(__('Protetor')) ?>
            }
        };
    </script>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
    <script src="<?= BASE_PATH ?>/assets/js/custom-select.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_PATH ?>/assets/js/notifications.js?v=<?= APP_VERSION ?>"></script>
    <?= $extraScripts ?? '' ?>

    <!-- Container Global de Notificações (Toasts) (Z-index máx para sobrepor header/perfil) -->
    <div id="notification-container" class="notification-container"
        style="position:fixed;top:20px;right:20px;z-index:2147483647 !important;display:flex;flex-direction:column;gap:15px;max-width:380px;pointer-events:none;">
    </div>

    <!-- Modal de Alerta Global (Substitui o alert nativo) -->
    <div id="globalAlertModal" class="modal-premium">
        <div class="modal-confirmation-content">
            <div id="globalAlertIconWrapper" style="width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                <i id="globalAlertIcon" data-lucide="help-circle" style="width: 32px; height: 32px;"></i>
            </div>
            <h2 id="globalAlertTitle">Alerta</h2>
            <p id="globalAlertMessage">Mensagem do sistema.</p>

            <div class="confirmation-actions">
                <button id="globalAlertConfirmBtn" class="btn-liquid" onclick="closeGlobalAlert(true)">
                    <span class="btn-text">
                        <i data-lucide="check-circle"></i>
                        <span id="globalAlertConfirmText"><?= __('Continuar') ?></span>
                    </span>
                    <div class="liquid"></div>
                </button>
                <button id="globalAlertCancelBtn" class="btn-light-shadow" onclick="closeGlobalAlert(false)" style="display: none;">
                    <?= __('Cancelar') ?>
                </button>
            </div>
        </div>
    </div>
</body>

</html>

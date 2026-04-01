<?php
declare(strict_types=1);

if (!function_exists('__')) {
    /**
     * Função global de tradução (i18n)
     * @param string $str Texto em PT-BR
     * @return string Texto traduzido para o idioma do cookie
     */
    function __($str) {
        $fullLang = $_COOKIE['epiguard-lang'] ?? 'pt-br';
        $lang = strtolower(substr($fullLang, 0, 2)); // Pega apenas 'en' ou 'pt'
        
        static $dict = [
            'en' => [
                // Dashboard
                'Painel Geral' => 'General Dashboard',
                'INFRAÇÕES HOJE' => 'INFRACTIONS TODAY',
                'INFRAÇÕES SEMANA' => 'INFRACTIONS WEEK',
                'INFRAÇÕES MÊS' => 'INFRACTIONS MONTH',
                'CONFORMIDADE (DIÁRIA)' => 'COMPLIANCE (DAILY)',
                'Visão Geral Mensal' => 'Monthly Overview',
                'setores selecionados' => 'sectors selected',
                'Filtrar por Setor' => 'Filter by Sector',
                'Registro Diário' => 'Daily Register',
                'Clique para expandir' => 'Click to expand',
                'Distribuição de EPIs' => 'EPI Distribution',
                'Top Ocorrências' => 'Top Occurrences',
                'Selecione o Setor' => 'Select Sector',
                'Filtre os dados do dashboard por área específica' => 'Filter dashboard data by specific area',
                'Pesquisar setor...' => 'Search sector...',
                'Toda a Empresa' => 'Whole Company',
                'Visão Global' => 'Global View',
                'Monitorado' => 'Monitored',
                'Aplicar Filtros' => 'Apply Filters',
                'Deseja ir para Infrações?' => 'Go to Infractions?',
                'Você será redirecionado para a página de detalhes com o filtro de período selecionado.' => 'You will be redirected to the details page with the selected period filter.',
                'Período de Conformidade' => 'Compliance Period',
                'Você deseja ver a conformidade de qual período?' => 'Which period compliance do you want to see?',
                'Diária' => 'Daily',
                'Semanal' => 'Weekly',
                'Mensal' => 'Monthly',
                'Anual' => 'Yearly',
                'Selecionar' => 'Select',
                'CONFORMIDADE' => 'COMPLIANCE',

                // Infractions
                'Gestão de ocorrências e infrações de EPI' => 'EPI occurrences and infractions management',
                'Exportar' => 'Export',
                'Buscar funcionário ou setor...' => 'Search employee or sector...',
                'Período' => 'Period',
                'Todos os períodos' => 'All periods',
                'Hoje' => 'Today',
                'Esta Semana' => 'This Week',
                'Este Mês' => 'This Month',
                'Personalizado' => 'Custom',
                'até' => 'to',
                'Todos os Status' => 'All Status',
                'Pendente' => 'Pending',
                'Resolvido' => 'Resolved',
                'Todos os EPIs' => 'All EPIs',
                'Ordenar por' => 'Order by',
                'Mais Recentes' => 'Most Recent',
                'Ordem Alfabética' => 'Alphabetical Order',
                'Mais Frequentes' => 'Most Frequent',
                'Visualização' => 'Visualization',
                'Exibir Nome' => 'Show Name',
                'Exibir Cards' => 'Show Cards',
                'Registro de Infrações' => 'Infractions Register',
                'Mostrando' => 'Showing',
                'registros' => 'records',
                'Setor' => 'Sector',
                'Data' => 'Date',
                'Ver detalhes' => 'View details',
                'Salvar para revisão' => 'Save for review',
                'Resolver' => 'Resolve',
                'Excluir' => 'Delete',
                'Nenhuma infração encontrada com os filtros selecionados.' => 'No infractions found with the selected filters.',

                // Departments
                'Gestão de Setor' => 'Department Management',
                'Gerencie as áreas e os respectivos EPIs obrigatórios' => 'Manage areas and respective mandatory EPIs',
                'Adicionar Setor' => 'Add Department',
                'Editar Setor' => 'Edit Department',
                'Salvar Alterações' => 'Save Changes',
                'Cancelar' => 'Cancel',
                'Confirmar' => 'Confirm',
                'Nome do Setor' => 'Department Name',
                'EPIs Obrigatórios' => 'Mandatory EPIs',
                'EPIs Obrigatórios (Marcas Permitidas)' => 'Mandatory EPIs (Allowed Brands)',
                'Status' => 'Status',
                'Ações' => 'Actions',
                'Funcionários' => 'Employees',
                'Risco' => 'Risk',
                'Código / Sigla' => 'Code / Initials',
                'Descrição do Setor' => 'Department Description',
                'Ativos' => 'Active',
                'Inativos' => 'Inactive',
                'Todos os Riscos' => 'All Risks',
                'Baixo' => 'Low',
                'Médio' => 'Medium',
                'Alto' => 'High',
                'Nenhum setor encontrado no banco de dados.' => 'No sectors found in the database.',
                'Nenhum EPI ativo encontrado no sistema. Cadastre EPIs primeiro.' => 'No active EPIs found in the system. Register EPIs first.',
                'EPI Geral' => 'General EPI',

                // Occurrences
                'Registrar Ocorrência' => 'Register Occurrence',
                'Preencha os dados abaixo para registrar uma nova ocorrência de segurança.' => 'Fill in the data below to register a new safety occurrence.',
                'Selecione o Local' => 'Select Location',
                'Escolha um setor...' => 'Choose a sector...',
                'Responsável pela Ocorrência' => 'Responsible for Occurrence',
                'Selecione um setor primeiro.' => 'Select a sector first.',
                'Nome Completo' => 'Full Name',
                'Motivo Principal' => 'Main Reason',
                'Selecione a Causa' => 'Select Cause',
                'Falta de EPI' => 'Missing EPI',
                'EPI Envolvido' => 'EPI Involved',
                'Selecione o EPI' => 'Select EPI',
                'Nenhum' => 'None',
                'Data e Hora' => 'Date and Time',
                'Horário do Registro' => 'Registration Time',
                'Ação Tomada' => 'Action Taken',
                'Tipo de Registro / Advertência' => 'Registration / Warning Type',
                'Ação Corretiva' => 'Corrective Action',
                'Orientação Técnica' => 'Technical Guidance',
                'Observações Adicionais' => 'Additional Observations',
                'Descreva detalhes sobre a ocorrência...' => 'Describe details about the occurrence...',
                'Evidências' => 'Evidence',
                'Adicionar' => 'Add',
                'Confirmar Ocorrência' => 'Confirm Occurrence',
                'Selecionar Setor' => 'Select Sector',
                'Escolha o local onde a ocorrência foi detectada' => 'Choose the location where the occurrence was detected',
                'Selecionar Funcionário' => 'Select Employee',
                'Escolha o funcionário do setor' => 'Choose the sector employee',
                'Pesquisar por nome ou CPF...' => 'Search by name or CPF...',
                'Selecione o setor para carregar os funcionários.' => 'Select the sector to load employees.',
                'Qual a causa principal desta ocorrência?' => 'What is the main cause of this occurrence?',
                'Selecionar EPI' => 'Select EPI',
                'Qual equipamento está envolvido?' => 'Which equipment is involved?',
                'Quando ocorreu a infração?' => 'When did the infraction occur?',
                'Como esta ocorrência será registrada?' => 'How will this occurrence be registered?',

                // Common / Sidebar / Profile
                'Olá' => 'Hello',
                'bem-vindo de volta!' => 'welcome back!',
                'Notificações' => 'Notifications',
                'Nenhuma infração nova' => 'No new infractions',
                'Ver todas as notificações' => 'View all notifications',
                'ID do Usuário' => 'User ID',
                'Email / Usuário' => 'Email / User',
                'Sair' => 'Logout',
                'Dashboard' => 'Dashboard',
                'Monitoramento' => 'Monitoring',
                'Infrações' => 'Infractions',
                'Configurações' => 'Settings',
                'Assistente IA' => 'AI Assistant',
                'Digite sua pergunta...' => 'Type your question...',
                'Olá! Como posso ajudar você com o EPI Guard hoje?' => 'Hello! How can I help you with EPI Guard today?',
                'Tema Claro' => 'Light Theme',
                'Tema Escuro' => 'Dark Theme',
                'Cores dos Gráficos' => 'Chart Colors',
                'Gerenciamento de Cores de EPI' => 'EPI Color Management',
                'Personalize as cores dos ícones para cada equipamento de proteção.' => 'Customize icon colors for each protection equipment.',
                'Salvar Cores' => 'Save Colors',
                'Salvando...' => 'Saving...',
                'Cores dos EPIs atualizadas com sucesso!' => 'EPI colors updated successfully!',
                'Não foi possível salvar as cores.' => 'Could not save colors.',
            ]
        ];

        if ($lang === 'en' && isset($dict['en'][$str])) {
            return $dict['en'][$str];
        }

        return $str;
    }
}


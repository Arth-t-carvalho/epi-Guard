<?php
// Detectar página atual para marcar como ativa
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$currentRoute = str_replace($basePath, '', $currentPath);
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-main">FACCHINI</div>
        <div class="brand-sub">MONITOR DE SEGURANÇA</div>
    </div>

    <nav class="nav-menu">
        <a href="<?= BASE_PATH ?>/dashboard" class="nav-item <?= ($currentRoute === '/dashboard') ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span><?= __('Dashboard') ?></span>
        </a>

        <a href="<?= BASE_PATH ?>/infractions"
            class="nav-item <?= ($currentRoute === '/infractions') ? 'active' : '' ?>">
            <i data-lucide="alert-triangle"></i>
            <span><?= __('Infrações') ?></span>
        </a>
        <a href="<?= BASE_PATH ?>/management/departments"
            class="nav-item <?= ($currentRoute === '/management/departments') ? 'active' : '' ?>">
            <i data-lucide="building-2"></i>
            <span><?= __('Gestão de Setor') ?></span>
        </a>
        <?php if (($_SESSION['user_email'] ?? '') === 'pietra.12@gmail.com'): ?>
        <a href="<?= BASE_PATH ?>/management/ad"
            class="nav-item <?= ($currentRoute === '/management/ad') ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <span><?= __('Usuários AD') ?></span>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_PATH ?>/occurrences"
            class="nav-item <?= ($currentRoute === '/occurrences') ? 'active' : '' ?>">
            <i data-lucide="file-text"></i>
            <span><?= __('Ocorrências') ?></span>
        </a>
        <a href="<?= BASE_PATH ?>/settings" class="nav-item <?= ($currentRoute === '/settings') ? 'active' : '' ?>">
            <i data-lucide="settings"></i>
            <span><?= __('Configurações') ?></span>
        </a>
    </nav>

    <div class="ai-assistant-container">
        <button class="ai-toggle-btn" onclick="toggleAiChat()">
            <i data-lucide="sparkles"></i>
            <span><?= __('Assistente IA') ?></span>
        </button>

        <div class="ai-chat-window" id="aiChatWindow">
            <div class="ai-chat-header">
                <span><?= __('🤖 Assistente IA') ?></span>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button onclick="toggleExpandAiChat()" id="expandAiBtn" title="<?= __('Expandir/Reduzir') ?>">
                        <i data-lucide="maximize-2"></i>
                    </button>
                    <button onclick="toggleAiChat()" title="<?= __('Fechar Chat') ?>">&times;</button>
                </div>
            </div>
            <div class="ai-chat-messages" id="aiChatMessages">
                <div class="ai-message bot"><?= __('Olá! Como posso ajudar você com o Facchini hoje?') ?></div>
            </div>
            <div class="ai-chat-input">
                <input type="text" id="aiChatInput" placeholder="<?= __('Digite sua pergunta...') ?>"
                    onkeypress="if(event.key==='Enter') sendAiMessage()">
                <button onclick="sendAiMessage()">
                    <i data-lucide="send"></i>
                </button>
            </div>
        </div>
    </div>


</aside>

<script src="<?= BASE_PATH ?>/assets/js/navigation.js?v=<?= APP_VERSION ?>"></script>

<script>
    // --- AI Chat Toggle ---
    function toggleAiChat() {
        const chatWindow = document.getElementById('aiChatWindow');
        chatWindow.classList.toggle('open');
    }

    function toggleExpandAiChat() {
        const chatWindow = document.getElementById('aiChatWindow');
        chatWindow.classList.toggle('expanded');

        let backdrop = document.getElementById('ai-backdrop-overlay');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'ai-backdrop-overlay';
            backdrop.className = 'ai-backdrop';
            backdrop.onclick = () => {
                if (chatWindow.classList.contains('expanded')) {
                    toggleExpandAiChat();
                } else {
                    toggleAiChat();
                }
            };
            chatWindow.parentNode.appendChild(backdrop);
        }

        const isExpanded = chatWindow.classList.contains('expanded');
        backdrop.classList.toggle('active', isExpanded);

        const btn = document.getElementById('expandAiBtn');
        if (isExpanded) {
            btn.innerHTML = '<i data-lucide="minimize-2"></i>';
        } else {
            btn.innerHTML = '<i data-lucide="maximize-2"></i>';
        }
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function sendAiMessage() {
        const input = document.getElementById('aiChatInput');
        const msg = input.value.trim();
        if (!msg) return;

        const messagesDiv = document.getElementById('aiChatMessages');

        // User message
        const userMsg = document.createElement('div');
        userMsg.className = 'ai-message user';
        userMsg.textContent = msg;
        messagesDiv.appendChild(userMsg);

        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Bot response (simulated)
        setTimeout(() => {
            const botMsg = document.createElement('div');
            botMsg.className = 'ai-message bot';
            botMsg.textContent = '<?= __('Entendido! Estou processando sua solicitação...') ?>';
            messagesDiv.appendChild(botMsg);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }, 800);
    }
</script>

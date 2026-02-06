/**
 * MENTTA - Chat Application
 * Main chat functionality for patient interface
 */

// Debug mode based on environment (DEV-009)
const DEBUG_MODE = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
function debugLog(...args) {
    if (DEBUG_MODE) console.log('[MENTTA Debug]', ...args);
}
function debugError(...args) {
    if (DEBUG_MODE) console.error('[MENTTA Error]', ...args);
}

// State
let conversationHistory = [];
let isLoading = false;
let isInitialized = false;
let loadingTextInterval = null; // UX-005: Para rotar texto de carga

// DOM Elements
const elements = {
    messagesContainer: null,
    messageInput: null,
    sendButton: null,
    loadingIndicator: null,
    welcomeMessage: null,
    sentimentIndicator: null,
    logoutModal: null
};

/**
 * Initialize chat on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    initializeElements();
    loadChatHistory();
    setupEventListeners();
    isInitialized = true;
});

/**
 * Cache DOM elements
 */
function initializeElements() {
    elements.messagesContainer = document.getElementById('messagesContainer');
    elements.messageInput = document.getElementById('messageInput');
    elements.sendButton = document.getElementById('sendButton');
    elements.loadingIndicator = document.getElementById('loadingIndicator');
    elements.welcomeMessage = document.getElementById('welcomeMessage');
    elements.sentimentIndicator = document.getElementById('sentimentIndicator');
    elements.logoutModal = document.getElementById('logoutModal');
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Enable/disable send button based on input
    elements.messageInput.addEventListener('input', () => {
        const hasContent = elements.messageInput.value.trim().length > 0;
        elements.sendButton.disabled = !hasContent || isLoading;
    });

    // Handle visibility change (restore focus)
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !isLoading) {
            elements.messageInput.focus();
        }
    });
}

/**
 * Load chat history from API
 */
async function loadChatHistory() {
    try {
        // Solo cargar historial si hay una sesión activa
        const sessionId = (typeof Menu !== 'undefined' && Menu.getCurrentSessionId)
            ? Menu.getCurrentSessionId()
            : null;

        if (!sessionId) {
            // Nueva sesión, no cargar historial
            return;
        }

        const response = await Utils.api(`api/chat/get-history.php?session_id=${encodeURIComponent(sessionId)}`);

        if (response.success && response.data.messages.length > 0) {
            conversationHistory = response.data.messages;
            renderAllMessages();
            hideWelcomeMessage();
            scrollToBottom(false);
        }
    } catch (error) {
        // DEV-009: Log en desarrollo, silencioso en producción
        debugError('Error cargando historial:', error);
    } finally {
        elements.messageInput.focus();
    }
}

/**
 * Render all messages in history
 */
function renderAllMessages() {
    // Keep welcome message in place but will be hidden
    const welcomeMsg = elements.welcomeMessage;
    elements.messagesContainer.innerHTML = '';
    elements.messagesContainer.appendChild(welcomeMsg);

    conversationHistory.forEach(msg => {
        appendMessageToDOM(msg.message, msg.sender, false);
    });
}

/**
 * Append a message to the DOM
 */
function appendMessageToDOM(text, sender, animate = true) {
    const wrapper = document.createElement('div');
    wrapper.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'}`;

    const bubble = document.createElement('div');
    bubble.className = `message-bubble ${sender === 'user' ? 'message-user' : 'message-ai'}`;
    bubble.innerHTML = Utils.formatText(text);

    if (!animate) {
        bubble.style.animation = 'none';
    }

    wrapper.appendChild(bubble);
    elements.messagesContainer.appendChild(wrapper);
}

/**
 * Send message to API
 */
async function sendMessage() {
    if (isLoading) return;

    const message = elements.messageInput.value.trim();
    if (!message) return;

    // Update UI state
    isLoading = true;
    elements.messageInput.value = '';
    elements.messageInput.style.height = 'auto';
    elements.sendButton.disabled = true;

    // Hide welcome message if visible
    hideWelcomeMessage();

    // Show user message immediately
    appendMessageToDOM(message, 'user');
    scrollToBottom();

    // Show loading indicator
    showLoading();

    try {
        const formData = new FormData();
        formData.append('message', message);

        // Include session_id if Menu module is available
        if (typeof Menu !== 'undefined' && Menu.getCurrentSessionId()) {
            formData.append('session_id', Menu.getCurrentSessionId());
        }

        const response = await fetch('api/chat/send-message.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Add AI response
            appendMessageToDOM(data.data.message, 'ai');
            scrollToBottom();

            // Update local history
            conversationHistory.push(
                { message: message, sender: 'user' },
                { message: data.data.message, sender: 'ai' }
            );

            // Update sentiment indicator
            if (data.data.sentiment) {
                updateSentimentIndicator(data.data.sentiment);
            }

            // Show panic button for high-risk situations (level 4-5)
            if (data.data.final_risk_level >= 4 || data.data.panic_button) {
                showPanicButton(data.data.panic_button || {
                    primary_line: '113',
                    secondary_line: '106',
                    message: '🆘 Si necesitas ayuda inmediata, puedes llamar a la línea de crisis.'
                });
            }

        } else {
            Utils.toast(data.error || 'Error al enviar el mensaje');
            // Remove the user message that failed to send
            const lastMessage = elements.messagesContainer.lastChild;
            if (lastMessage) lastMessage.remove();
        }
    } catch (error) {
        // DEV-009: Log en desarrollo, silencioso en producción
        debugError('Error enviando mensaje:', error);
        Utils.toast('No se pudo enviar el mensaje. Intenta de nuevo.');
    } finally {
        isLoading = false;
        elements.sendButton.disabled = elements.messageInput.value.trim().length === 0;
        hideLoading();
        elements.messageInput.focus();
    }
}

/**
 * Update sentiment indicator dots
 */
function updateSentimentIndicator(sentiment) {
    elements.sentimentIndicator.classList.remove('hidden');

    // Calculate overall mood (positive - negative emotions)
    const positive = sentiment.positive || 0;
    const negative = (sentiment.negative || 0) +
        (sentiment.anxiety || 0) +
        (sentiment.sadness || 0) +
        (sentiment.anger || 0);

    const avgNegative = negative / 4;
    const moodScore = (positive - avgNegative + 1) / 2; // 0 to 1

    // Light up dots based on mood score
    const activeDots = Math.round(moodScore * 5);

    for (let i = 1; i <= 5; i++) {
        const dot = document.getElementById(`moodDot${i}`);
        if (i <= activeDots) {
            if (moodScore > 0.6) {
                dot.className = 'w-2 h-2 rounded-full mood-positive transition-colors';
            } else if (moodScore > 0.4) {
                dot.className = 'w-2 h-2 rounded-full mood-neutral transition-colors';
            } else {
                dot.className = 'w-2 h-2 rounded-full mood-negative transition-colors';
            }
        } else {
            dot.className = 'w-2 h-2 rounded-full bg-gray-200 transition-colors';
        }
    }

    // UX-004 FIX: Aumentar tiempo a 20 segundos para que el usuario lo note
    setTimeout(() => {
        elements.sentimentIndicator.classList.add('hidden');
    }, 20000);
}

/**
 * Handle keyboard input
 */
function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (!elements.sendButton.disabled) {
            sendMessage();
        }
    }
}

/**
 * Auto-resize textarea
 */
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

/**
 * Scroll messages to bottom
 */
function scrollToBottom(smooth = true) {
    // Use window scroll since the layout relies on body scroll
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: smooth ? 'smooth' : 'auto'
    });
}

/**
 * Show/hide loading indicator
 */
function showLoading() {
    elements.loadingIndicator.classList.remove('hidden');
}

function hideLoading() {
    elements.loadingIndicator.classList.add('hidden');
}

/**
 * Hide welcome message
 */
function hideWelcomeMessage() {
    if (elements.welcomeMessage) {
        elements.welcomeMessage.style.display = 'none';
    }
}

/**
 * Logout functions
 */
function confirmLogout() {
    elements.logoutModal.classList.remove('hidden');
    elements.logoutModal.classList.add('flex');
}

function closeLogoutModal() {
    elements.logoutModal.classList.add('hidden');
    elements.logoutModal.classList.remove('flex');
}

function logout() {
    window.location.href = 'logout.php';
}

/**
 * Shows emergency panic button overlay
 * @param {Object} config - Configuration for panic button
 */
function showPanicButton(config = {}) {
    const primaryLine = config.primary_line || '113';
    const secondaryLine = config.secondary_line || '106';
    const message = config.message || '🆘 Si necesitas ayuda inmediata, puedes llamar a la línea de crisis.';

    // Remove existing panic button if any
    closePanicButton();

    const panicButton = document.createElement('div');
    panicButton.id = 'panic-button-overlay';
    panicButton.className = 'fixed bottom-4 right-4 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-2xl shadow-2xl p-4 z-50 max-w-sm animate-pulse';

    panicButton.innerHTML = `
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-lg font-bold">🆘 Ayuda Inmediata</span>
                <button onclick="closePanicButton()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>
            <p class="text-sm text-white/90">${message}</p>
            <div class="flex gap-2">
                <a href="tel:${primaryLine}" 
                   class="flex-1 bg-white text-red-700 font-bold py-3 px-4 rounded-lg text-center hover:bg-red-50 transition-colors">
                    📞 Llamar ${primaryLine}
                </a>
                <a href="tel:${secondaryLine}" 
                   class="flex-1 bg-white/20 text-white font-bold py-3 px-4 rounded-lg text-center hover:bg-white/30 transition-colors">
                    🚑 ${secondaryLine}
                </a>
            </div>
            <p class="text-xs text-white/70 text-center">
                Línea 113 (Salud Mental) • SAMU 106 (Emergencias)
            </p>
        </div>
    `;

    document.body.appendChild(panicButton);
}

/**
 * Hides the panic button overlay
 */
function closePanicButton() {
    const existing = document.getElementById('panic-button-overlay');
    if (existing) {
        existing.remove();
    }
}

// Make functions globally available
window.sendMessage = sendMessage;
window.handleKeyDown = handleKeyDown;
window.autoResize = autoResize;
window.confirmLogout = confirmLogout;
window.closeLogoutModal = closeLogoutModal;
window.logout = logout;
window.showPanicButton = showPanicButton;
window.closePanicButton = closePanicButton;

/**
 * MENTTA - Chat Application
 * Main chat functionality for patient interface
 */

// State
let conversationHistory = [];
let isLoading = false;
let isInitialized = false;

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
        const response = await Utils.api('api/chat/get-history.php');

        if (response.success && response.data.messages.length > 0) {
            conversationHistory = response.data.messages;
            renderAllMessages();
            hideWelcomeMessage();
            scrollToBottom(false);
        }
    } catch (error) {
        console.error('Error loading history:', error);
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

        } else {
            Utils.toast(data.error || 'Error al enviar el mensaje');
            // Remove the user message that failed to send
            const lastMessage = elements.messagesContainer.lastChild;
            if (lastMessage) lastMessage.remove();
        }
    } catch (error) {
        console.error('Send error:', error);
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

    // Hide after 5 seconds
    setTimeout(() => {
        elements.sentimentIndicator.classList.add('hidden');
    }, 5000);
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
    Utils.scrollToBottom(elements.messagesContainer, smooth);
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

// Make functions globally available
window.sendMessage = sendMessage;
window.handleKeyDown = handleKeyDown;
window.autoResize = autoResize;
window.confirmLogout = confirmLogout;
window.closeLogoutModal = closeLogoutModal;
window.logout = logout;

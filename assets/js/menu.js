/**
 * MENTTA - Sidebar Menu Manager
 * Handles hamburger menu, modals, notifications, and chat history
 */

const Menu = {
    // Elements
    elements: {
        backdrop: null,
        sidebar: null,
        notificationBadge: null,
        chatHistoryList: null,
        notificationsList: null
    },

    // State
    state: {
        isOpen: false,
        currentSessionId: null,
        notificationCount: 0,
        chatHistory: [],
        notifications: []
    },

    /**
     * Initialize menu
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.loadNotificationCount();
        this.loadChatHistory();
        this.checkAnalysisPaused();

        // Generate session ID if not exists
        if (!this.state.currentSessionId) {
            this.state.currentSessionId = localStorage.getItem('mentta-session-id') || this.generateSessionId();
            localStorage.setItem('mentta-session-id', this.state.currentSessionId);
        }
    },

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements.backdrop = document.getElementById('sidebar-backdrop');
        this.elements.sidebar = document.getElementById('sidebar-menu');
        this.elements.notificationBadge = document.getElementById('notification-badge');
        this.elements.chatHistoryList = document.getElementById('chat-history-list');
        this.elements.notificationsList = document.getElementById('notifications-list');
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Close on backdrop click
        if (this.elements.backdrop) {
            this.elements.backdrop.addEventListener('click', () => this.close());
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close();
                this.closeAllModals();
            }
        });
    },

    /**
     * Generate unique session ID
     */
    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    },

    /**
     * Open sidebar menu
     */
    open() {
        this.state.isOpen = true;
        if (this.elements.backdrop) this.elements.backdrop.classList.add('active');
        if (this.elements.sidebar) this.elements.sidebar.classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    /**
     * Close sidebar menu
     */
    close() {
        this.state.isOpen = false;
        if (this.elements.backdrop) this.elements.backdrop.classList.remove('active');
        if (this.elements.sidebar) this.elements.sidebar.classList.remove('active');
        document.body.style.overflow = '';
    },

    /**
     * Toggle sidebar menu
     */
    toggle() {
        if (this.state.isOpen) {
            this.close();
        } else {
            this.open();
        }
    },

    /**
     * Start new chat session
     */
    async newChat() {
        // Generate new session ID
        const newSessionId = this.generateSessionId();

        try {
            // Save to server
            const formData = new FormData();
            formData.append('session_id', newSessionId);

            const response = await fetch('api/chat/new-session.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Update local state
                this.state.currentSessionId = newSessionId;
                localStorage.setItem('mentta-session-id', newSessionId);

                // Clear chat UI
                this.clearChatUI();

                // Close menu
                this.close();

                // Reload history
                this.loadChatHistory();

                // Show toast
                if (typeof Utils !== 'undefined' && Utils.toast) {
                    Utils.toast('Nueva conversación iniciada');
                }
            }
        } catch (error) {
            console.error('Error creating new chat:', error);
        }
    },

    /**
     * Clear chat UI for new conversation
     */
    clearChatUI() {
        const messagesContainer = document.getElementById('messagesContainer');
        const welcomeMessage = document.getElementById('welcomeMessage');

        if (messagesContainer) {
            messagesContainer.innerHTML = '';
            if (welcomeMessage) {
                welcomeMessage.style.display = 'block';
                messagesContainer.appendChild(welcomeMessage);
            }
        }

        // Reset conversation history in chat.js
        if (typeof conversationHistory !== 'undefined') {
            window.conversationHistory = [];
        }
    },

    /**
     * Load chat history from server
     */
    async loadChatHistory() {
        try {
            const response = await fetch('api/chat/get-chat-list.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success && data.data.sessions) {
                this.state.chatHistory = data.data.sessions;
                this.renderChatHistory();
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    },

    /**
     * Render chat history in sidebar
     */
    renderChatHistory() {
        if (!this.elements.chatHistoryList) return;

        if (this.state.chatHistory.length === 0) {
            this.elements.chatHistoryList.innerHTML = `
                <div class="px-4 py-3 text-center">
                    <p style="color: var(--text-tertiary); font-size: 0.8125rem;">
                        No hay conversaciones anteriores
                    </p>
                </div>
            `;
            return;
        }

        this.elements.chatHistoryList.innerHTML = this.state.chatHistory.map(session => {
            const moodClass = this.getMoodClass(session.mood);
            const dateLabel = this.formatDateLabel(session.date);

            return `
                <div class="chat-history-item" onclick="Menu.selectChat('${session.session_id}')">
                    <span class="chat-history-item-mood ${moodClass}"></span>
                    <span class="chat-history-item-title">${this.escapeHtml(session.title || 'Sin título')}</span>
                    <span class="chat-history-item-date">${dateLabel}</span>
                </div>
            `;
        }).join('');
    },

    /**
     * Get mood class from mood value
     */
    getMoodClass(mood) {
        if (mood >= 0.6) return 'positive';
        if (mood >= 0.4) return 'neutral';
        return 'negative';
    },

    /**
     * Format date for display
     */
    formatDateLabel(dateStr) {
        const date = new Date(dateStr);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Hoy';
        }
        if (date.toDateString() === yesterday.toDateString()) {
            return 'Ayer';
        }

        return date.toLocaleDateString('es', { day: 'numeric', month: 'short' });
    },

    /**
     * Select and load a chat session
     */
    async selectChat(sessionId) {
        try {
            const formData = new FormData();
            formData.append('session_id', sessionId);

            const response = await fetch('api/chat/load-session.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success && data.data.messages) {
                // Update session ID
                this.state.currentSessionId = sessionId;
                localStorage.setItem('mentta-session-id', sessionId);

                // Clear and render messages
                this.renderMessages(data.data.messages);

                // Close menu
                this.close();
            }
        } catch (error) {
            console.error('Error loading chat session:', error);
        }
    },

    /**
     * Render messages in chat UI
     */
    renderMessages(messages) {
        const messagesContainer = document.getElementById('messagesContainer');
        const welcomeMessage = document.getElementById('welcomeMessage');

        if (!messagesContainer) return;

        // Clear container
        messagesContainer.innerHTML = '';

        // Hide welcome message
        if (welcomeMessage) {
            welcomeMessage.style.display = 'none';
            messagesContainer.appendChild(welcomeMessage);
        }

        // Render each message
        messages.forEach(msg => {
            const wrapper = document.createElement('div');
            wrapper.className = `flex ${msg.sender === 'user' ? 'justify-end' : 'justify-start'}`;

            const bubble = document.createElement('div');
            bubble.className = `message-bubble ${msg.sender === 'user' ? 'message-user' : 'message-ai'}`;
            bubble.innerHTML = typeof Utils !== 'undefined' && Utils.formatText
                ? Utils.formatText(msg.message)
                : msg.message;
            bubble.style.animation = 'none';

            wrapper.appendChild(bubble);
            messagesContainer.appendChild(wrapper);
        });

        // Update global conversation history
        if (typeof window.conversationHistory !== 'undefined') {
            window.conversationHistory = messages;
        }

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    },

    /**
     * Load notification count
     */
    async loadNotificationCount() {
        try {
            const response = await fetch('api/patient/get-notifications.php?count_only=1', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.state.notificationCount = data.data.unread_count || 0;
                this.updateNotificationBadge();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },

    /**
     * Update notification badge
     */
    updateNotificationBadge() {
        if (!this.elements.notificationBadge) return;

        if (this.state.notificationCount > 0) {
            this.elements.notificationBadge.textContent = this.state.notificationCount > 99
                ? '99+'
                : this.state.notificationCount;
            this.elements.notificationBadge.classList.remove('hidden');
        } else {
            this.elements.notificationBadge.classList.add('hidden');
        }
    },

    /**
     * Load full notifications list
     */
    async loadNotifications() {
        try {
            const response = await fetch('api/patient/get-notifications.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.state.notifications = data.data.notifications || [];
                this.state.notificationCount = data.data.unread_count || 0;
                this.renderNotifications();
                this.updateNotificationBadge();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },

    /**
     * Render notifications in sidebar
     */
    renderNotifications() {
        if (!this.elements.notificationsList) return;

        if (this.state.notifications.length === 0) {
            this.elements.notificationsList.innerHTML = `
                <div class="px-4 py-3 text-center">
                    <p style="color: var(--text-tertiary); font-size: 0.8125rem;">
                        No hay notificaciones
                    </p>
                </div>
            `;
            return;
        }

        this.elements.notificationsList.innerHTML = this.state.notifications.slice(0, 5).map(notif => `
            <div class="notification-item ${notif.is_read ? 'read' : ''}" 
                 onclick="Menu.markNotificationRead(${notif.id})">
                <div class="notification-item-content">
                    <div class="notification-item-title">${this.escapeHtml(notif.title)}</div>
                    <div class="notification-item-time">${this.formatTimeAgo(notif.created_at)}</div>
                </div>
            </div>
        `).join('');
    },

    /**
     * Mark notification as read
     */
    async markNotificationRead(id) {
        try {
            const formData = new FormData();
            formData.append('notification_id', id);

            await fetch('api/patient/mark-notification-read.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Update local state
            const notif = this.state.notifications.find(n => n.id === id);
            if (notif && !notif.is_read) {
                notif.is_read = true;
                this.state.notificationCount = Math.max(0, this.state.notificationCount - 1);
                this.renderNotifications();
                this.updateNotificationBadge();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    },

    /**
     * Mark all notifications as read
     */
    async markAllNotificationsRead() {
        try {
            await fetch('api/patient/mark-all-notifications-read.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Update local state
            this.state.notifications.forEach(n => n.is_read = true);
            this.state.notificationCount = 0;
            this.renderNotifications();
            this.updateNotificationBadge();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    },

    /**
     * Check if analysis is paused
     */
    async checkAnalysisPaused() {
        try {
            const response = await fetch('api/patient/get-preferences.php', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success && data.data.analysis_paused) {
                this.showAnalysisPausedBanner(data.data.analysis_paused_until);
            }
        } catch (error) {
            console.error('Error checking analysis status:', error);
        }
    },

    /**
     * Show analysis paused banner
     */
    showAnalysisPausedBanner(pausedUntil) {
        const banner = document.getElementById('analysis-paused-banner');
        if (!banner) return;

        const pausedDate = new Date(pausedUntil);
        const timeStr = pausedDate.toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });

        banner.innerHTML = `
            ⏸️ Análisis emocional pausado hasta las ${timeStr}. 
            <button onclick="Menu.openProfile()">Reactivar</button>
        `;
        banner.classList.add('active');

        // Hide sentiment indicator
        const sentimentIndicator = document.getElementById('sentimentIndicator');
        if (sentimentIndicator) {
            sentimentIndicator.style.display = 'none';
        }
    },

    /**
     * Hide analysis paused banner
     */
    hideAnalysisPausedBanner() {
        const banner = document.getElementById('analysis-paused-banner');
        if (banner) {
            banner.classList.remove('active');
        }

        // Show sentiment indicator again
        const sentimentIndicator = document.getElementById('sentimentIndicator');
        if (sentimentIndicator) {
            sentimentIndicator.style.display = '';
        }
    },

    /**
     * Format time ago
     */
    formatTimeAgo(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);

        if (diff < 60) return 'Ahora';
        if (diff < 3600) return `Hace ${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `Hace ${Math.floor(diff / 3600)}h`;
        return `Hace ${Math.floor(diff / 86400)}d`;
    },

    /**
     * Escape HTML
     */
    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    /**
     * Close all modals
     */
    closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.classList.remove('active');
        });
    },

    // ========================================
    // Modal Handlers
    // ========================================

    /**
     * Open map placeholder modal
     */
    openMapModal() {
        const modal = document.getElementById('map-modal');
        if (modal) modal.classList.add('active');
        this.close();
    },

    /**
     * Close map modal
     */
    closeMapModal() {
        const modal = document.getElementById('map-modal');
        if (modal) modal.classList.remove('active');
    },

    /**
     * Open crisis modal
     */
    openCrisisModal() {
        const modal = document.getElementById('crisis-modal');
        if (modal) modal.classList.add('active');
        this.close();
    },

    /**
     * Close crisis modal
     */
    closeCrisisModal() {
        const modal = document.getElementById('crisis-modal');
        if (modal) modal.classList.remove('active');
    },

    /**
     * Handle crisis option selection
     */
    async selectCrisisOption(type) {
        try {
            const formData = new FormData();
            formData.append('type', type);

            const response = await fetch('api/patient/trigger-manual-crisis.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show confirmation
                this.showCrisisConfirmation(type);
            } else {
                if (typeof Utils !== 'undefined' && Utils.toast) {
                    Utils.toast(data.error || 'Error al procesar la solicitud');
                }
            }
        } catch (error) {
            console.error('Error triggering crisis alert:', error);
            if (typeof Utils !== 'undefined' && Utils.toast) {
                Utils.toast('Error de conexión');
            }
        }
    },

    /**
     * Show crisis confirmation
     */
    showCrisisConfirmation(type) {
        const modal = document.getElementById('crisis-modal');
        if (!modal) return;

        const body = modal.querySelector('.modal-body');
        if (!body) return;

        let message = '';
        switch (type) {
            case 'psychologist':
                message = 'Tu psicólogo ha sido notificado y se pondrá en contacto contigo pronto.';
                break;
            case 'emergency_contact':
                message = 'Tu contacto de emergencia ha sido notificado.';
                break;
            case 'crisis_line':
                message = 'Te estamos conectando con la línea de crisis.';
                break;
            case 'calming_exercises':
                this.closeCrisisModal();
                this.openResourcesModal();
                return;
        }

        body.innerHTML = `
            <div class="text-center py-6">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💜</div>
                <h3 style="color: var(--text-primary); font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
                    Ayuda en camino
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.9375rem; margin-bottom: 1.5rem;">
                    ${message}
                </p>
                <p style="color: var(--text-tertiary); font-size: 0.8125rem;">
                    Mientras tanto, ¿quieres seguir hablando conmigo?
                </p>
                <button class="resource-btn" style="margin-top: 1rem;" onclick="Menu.closeCrisisModal()">
                    Volver al chat
                </button>
            </div>
        `;
    },

    /**
     * Open resources modal
     */
    openResourcesModal() {
        const modal = document.getElementById('resources-modal');
        if (modal) modal.classList.add('active');
        this.close();
    },

    /**
     * Close resources modal
     */
    closeResourcesModal() {
        const modal = document.getElementById('resources-modal');
        if (modal) modal.classList.remove('active');
    },

    /**
     * Toggle resource card expansion
     */
    toggleResourceCard(cardId) {
        const card = document.getElementById(cardId);
        if (card) {
            card.classList.toggle('expanded');
        }
    },

    /**
     * Get random supportive phrase
     */
    getRandomPhrase() {
        const phrases = [
            "Esto que sientes es temporal. Has superado días difíciles antes y lo harás de nuevo.",
            "No estás solo. Hay personas que se preocupan por ti, aunque no lo parezca ahora.",
            "Está bien no estar bien. Tus sentimientos son válidos.",
            "Cada día que amaneces es una nueva oportunidad para empezar de nuevo.",
            "Tu bienestar importa. Mereces paz y felicidad.",
            "Los momentos difíciles nos hacen más fuertes. Confía en tu capacidad de superación.",
            "Recuerda: pedir ayuda es un acto de valentía, no de debilidad.",
            "Tus emociones son válidas, pero no definen quién eres.",
            "El hecho de que estés buscando ayuda ya es un gran paso.",
            "Mañana es un nuevo día con nuevas posibilidades."
        ];

        return phrases[Math.floor(Math.random() * phrases.length)];
    },

    /**
     * Show new random phrase
     */
    showNewPhrase() {
        const container = document.getElementById('supportive-phrase');
        if (container) {
            container.querySelector('p').textContent = `"${this.getRandomPhrase()}"`;
        }
    },

    /**
     * Open profile page
     */
    openProfile() {
        window.location.href = 'profile.php';
    },

    /**
     * Get current session ID
     */
    getCurrentSessionId() {
        return this.state.currentSessionId;
    }
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    Menu.init();
});

// Export for global access
window.Menu = Menu;
window.openMenu = () => Menu.open();
window.closeMenu = () => Menu.close();
window.toggleMenu = () => Menu.toggle();
window.newChat = () => Menu.newChat();
window.openMapModal = () => Menu.openMapModal();
window.closeMapModal = () => Menu.closeMapModal();
window.openCrisisModal = () => Menu.openCrisisModal();
window.closeCrisisModal = () => Menu.closeCrisisModal();
window.selectCrisisOption = (type) => Menu.selectCrisisOption(type);
window.openResourcesModal = () => Menu.openResourcesModal();
window.closeResourcesModal = () => Menu.closeResourcesModal();
window.toggleResourceCard = (cardId) => Menu.toggleResourceCard(cardId);
window.showNewPhrase = () => Menu.showNewPhrase();

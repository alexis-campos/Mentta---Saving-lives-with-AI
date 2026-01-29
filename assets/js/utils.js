/**
 * MENTTA - Utility Functions
 * General purpose JavaScript helpers
 */

const Utils = {
    /**
     * Show toast notification
     */
    toast(message, duration = 3000) {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), duration);
    },
    
    /**
     * Format date to relative time
     */
    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'ahora';
        if (seconds < 3600) return `hace ${Math.floor(seconds / 60)} min`;
        if (seconds < 86400) return `hace ${Math.floor(seconds / 3600)} h`;
        if (seconds < 604800) return `hace ${Math.floor(seconds / 86400)} días`;
        
        return date.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' });
    },
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    /**
     * Debounce function calls
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Make API request with error handling
     */
    async api(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Error en la solicitud');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    /**
     * Scroll element to bottom smoothly
     */
    scrollToBottom(element, smooth = true) {
        element.scrollTo({
            top: element.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    },
    
    /**
     * Check if element is scrolled to bottom
     */
    isScrolledToBottom(element, threshold = 100) {
        return element.scrollHeight - element.scrollTop - element.clientHeight < threshold;
    },
    
    /**
     * Format text with line breaks
     */
    formatText(text) {
        return this.escapeHtml(text).replace(/\n/g, '<br>');
    },
    
    /**
     * Get current timestamp
     */
    now() {
        return new Date().toISOString();
    },
    
    /**
     * Store data in localStorage
     */
    store(key, value) {
        try {
            localStorage.setItem(`mentta_${key}`, JSON.stringify(value));
        } catch (e) {
            console.warn('LocalStorage not available');
        }
    },
    
    /**
     * Retrieve data from localStorage
     */
    retrieve(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(`mentta_${key}`);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },
    
    /**
     * Play notification sound
     */
    playSound(soundName = 'notification') {
        try {
            const audio = new Audio(`assets/sounds/${soundName}.mp3`);
            audio.volume = 0.5;
            audio.play().catch(() => {});
        } catch (e) {
            // Silently fail
        }
    }
};

// Make Utils globally available
window.Utils = Utils;

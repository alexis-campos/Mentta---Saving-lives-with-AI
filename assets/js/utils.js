// ========================================
// Elite Animations & Navigation Flow
// ========================================

/**
 * Handle elegant page exit transitions
 */
function navigateTo(url) {
    document.body.classList.add('page-transitionING');
    document.body.classList.add('page-exit');

    setTimeout(() => {
        window.location.href = url;
    }, 500);
}

// Intercept internal links for cross-dissolve effect
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (link && link.href && link.href.includes(window.location.origin) && !link.target && !link.hasAttribute('download')) {
        const urlObj = new URL(link.href);
        if (urlObj.pathname !== window.location.pathname) {
            e.preventDefault();
            navigateTo(link.href);
        }
    }
});

// Polyfill for buttons that navigate via window.location (like goBack)
const originalGoBack = window.goBack;
if (typeof originalGoBack === 'function') {
    window.goBack = () => {
        document.body.classList.add('page-transitionING');
        document.body.classList.add('page-exit');
        setTimeout(() => originalGoBack(), 500);
    };
}

// ========================================
// Formateo de Fechas
// ========================================

/**
 * Formatear fecha en formato largo
 * @param {string} dateString - Fecha en formato ISO
 * @returns {string} Fecha formateada (ej: "15 de enero de 2026")
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

/**
 * Formatear fecha corta
 * @param {string} dateString - Fecha en formato ISO
 * @returns {string} Fecha formateada (ej: "15/01/2026")
 */
function formatDateShort(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Formatear hora
 * @param {string} dateString - Fecha en formato ISO
 * @returns {string} Hora formateada (ej: "14:30")
 */
function formatTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Tiempo relativo ("hace 2 horas")
 * @param {string} timestamp - Timestamp o fecha ISO
 * @returns {string} Tiempo relativo
 */
function timeAgo(timestamp) {
    if (!timestamp) return 'Nunca';

    const now = new Date();
    const past = new Date(timestamp);
    const diff = Math.floor((now - past) / 1000); // segundos

    if (diff < 0) return 'Ahora';
    if (diff < 60) return 'Hace ' + diff + ' segundos';
    if (diff < 3600) return 'Hace ' + Math.floor(diff / 60) + ' minutos';
    if (diff < 86400) return 'Hace ' + Math.floor(diff / 3600) + ' horas';
    if (diff < 604800) return 'Hace ' + Math.floor(diff / 86400) + ' días';
    return 'Hace ' + Math.floor(diff / 604800) + ' semanas';
}

// ========================================
// Utilidades de Texto
// ========================================

/**
 * Escape HTML para prevenir XSS
 * @param {string} text - Texto a escapar
 * @returns {string} Texto escapado
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Truncar texto con elipsis
 * @param {string} text - Texto a truncar
 * @param {number} maxLength - Longitud máxima
 * @returns {string} Texto truncado
 */
function truncateText(text, maxLength = 100) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
}

/**
 * Capitalizar primera letra
 * @param {string} text - Texto
 * @returns {string} Texto capitalizado
 */
function capitalize(text) {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
}

// ========================================
// Notificaciones Toast
// ========================================

/**
 * Mostrar notificación toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duración en ms
 */
function showToast(message, type = 'info', duration = 3000) {
    // Remover toasts anteriores
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();

    const bgColor = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-orange-500',
        'info': 'bg-blue-500'
    }[type] || 'bg-blue-500';

    const icon = {
        'success': '✓',
        'error': '✕',
        'warning': '⚠',
        'info': 'ℹ'
    }[type] || 'ℹ';

    const toast = document.createElement('div');
    toast.className = `toast-notification fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 animate-slideIn`;
    toast.innerHTML = `<span class="text-lg">${icon}</span><span>${escapeHtml(message)}</span>`;

    // Añadir animación CSS si no existe
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes slideIn {
                from { opacity: 0; transform: translateX(100px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100px); }
            }
            .animate-slideIn { animation: slideIn 0.3s ease-out; }
            .animate-slideOut { animation: slideOut 0.3s ease-in forwards; }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('animate-slideIn');
        toast.classList.add('animate-slideOut');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ========================================
// Utilidades de API
// ========================================

/**
 * Fetch con manejo de errores
 * @param {string} url - URL del endpoint
 * @param {object} options - Opciones de fetch
 * @returns {Promise<object>} Respuesta parseada
 */
async function apiFetch(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                ...options.headers
            }
        });

        const data = await response.json();

        if (!data.success && data.error) {
            throw new Error(data.error);
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * POST con FormData
 * @param {string} url - URL del endpoint
 * @param {object} data - Datos a enviar
 * @returns {Promise<object>} Respuesta parseada
 */
async function apiPost(url, data) {
    const formData = new FormData();
    for (const key in data) {
        formData.append(key, data[key]);
    }

    return apiFetch(url, {
        method: 'POST',
        body: formData
    });
}

// ========================================
// Utilidades de Sesión
// ========================================

/**
 * Verificar si el usuario está autenticado
 * @returns {Promise<object|null>} Datos del usuario o null
 */
async function checkSession() {
    try {
        const data = await apiFetch('api/auth/check-session.php');
        return data.success ? data.data : null;
    } catch (error) {
        return null;
    }
}

/**
 * Cerrar sesión
 */
function logout() {
    window.location.href = 'logout.php';
}

// ========================================
// Utilidades del DOM
// ========================================

/**
 * Crear elemento con clases y contenido
 * @param {string} tag - Tag HTML
 * @param {string} className - Clases CSS
 * @param {string} innerHTML - Contenido HTML
 * @returns {HTMLElement} Elemento creado
 */
function createElement(tag, className = '', innerHTML = '') {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (innerHTML) el.innerHTML = innerHTML;
    return el;
}

/**
 * Mostrar/ocultar elemento
 * @param {string|HTMLElement} selector - Selector o elemento
 * @param {boolean} show - true para mostrar, false para ocultar
 */
function toggleElement(selector, show) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (el) {
        if (show) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
}

// ========================================
// Debounce y Throttle
// ========================================

/**
 * Debounce function
 * @param {Function} func - Función a ejecutar
 * @param {number} wait - Tiempo de espera en ms
 * @returns {Function} Función con debounce
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 * @param {Function} func - Función a ejecutar
 * @param {number} limit - Límite de tiempo en ms
 * @returns {Function} Función con throttle
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ========================================
// Funciones adicionales para Chat
// ========================================

/**
 * Formatear texto para mostrar en chat (escape + line breaks)
 * @param {string} text - Texto a formatear
 * @returns {string} HTML seguro con saltos de línea
 */
function formatText(text) {
    if (!text) return '';
    // Escape HTML y convertir saltos de línea a <br>
    return escapeHtml(text).replace(/\n/g, '<br>');
}

/**
 * Scroll suave al fondo de un contenedor
 * @param {HTMLElement} container - Contenedor a scrollear
 * @param {boolean} smooth - Usar animación suave
 */
function scrollToBottom(container, smooth = true) {
    if (!container) return;
    container.scrollTo({
        top: container.scrollHeight,
        behavior: smooth ? 'smooth' : 'auto'
    });
}

// ========================================
// Objeto Utils (compatibilidad con chat.js)
// ========================================

/**
 * Objeto Utils que agrupa todas las utilidades
 * Permite usar Utils.method() o directamente method()
 */
const Utils = {
    // Fechas
    formatDate,
    formatDateShort,
    formatTime,
    timeAgo,

    // Texto
    escapeHtml,
    truncateText,
    capitalize,
    formatText,

    // Notificaciones
    toast: showToast,
    showToast,

    // API
    api: apiFetch,
    apiFetch,
    apiPost,

    // Sesión
    checkSession,
    logout,

    // DOM
    createElement,
    toggleElement,
    scrollToBottom,

    // Utilidades
    debounce,
    throttle
};

// Hacer Utils global
window.Utils = Utils;

// Export for modules (optional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
}


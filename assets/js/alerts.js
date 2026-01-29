/**
 * MENTTA - Alert System
 * Sistema de long polling para alertas en tiempo real
 */

class AlertSystem {
    constructor(psychologistId) {
        this.psychologistId = psychologistId;
        this.lastCheck = null;
        this.isPolling = false;
        this.audioAlert = new Audio('assets/sounds/alert.mp3');
    }

    // Iniciar long polling
    start() {
        this.isPolling = true;
        console.log('🛡️ Sistema de alertas iniciado');
        this.poll();
    }

    // Detener
    stop() {
        this.isPolling = false;
        console.log('Sistema de alertas detenido');
    }

    // Polling loop
    async poll() {
        if (!this.isPolling) return;

        try {
            const url = `api/psychologist/check-alerts.php${this.lastCheck ? '?last_check=' + this.lastCheck : ''}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.data.alerts.length > 0) {
                // Nuevas alertas recibidas
                this.handleNewAlerts(data.data.alerts);
                this.lastCheck = data.data.timestamp;
            } else if (data.success) {
                this.lastCheck = data.data.timestamp;
            }
        } catch (error) {
            console.error('Error en long polling:', error);
            // Esperar 5 segundos en caso de error antes de reintentar
            await this.sleep(5000);
        }

        // Continuar polling
        if (this.isPolling) {
            this.poll();
        }
    }

    // Manejar nuevas alertas
    handleNewAlerts(alerts) {
        console.log(`🚨 ${alerts.length} nuevas alertas recibidas`);

        alerts.forEach(alert => {
            // Mostrar notificación visual
            this.showNotification(alert);

            // Reproducir sonido
            this.playSound();

            // Actualizar contador de alertas
            this.updateBadge(alerts.length);

            // Agregar a lista de alertas en UI
            this.addToAlertList(alert);
        });
    }

    // Mostrar notificación
    showNotification(alert) {
        const bgColor = alert.severity === 'red' ? 'bg-red-600' : 'bg-orange-500';
        const levelText = alert.severity === 'red' ? 'CRÍTICO' : 'ALTO';

        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${bgColor} text-white p-4 rounded-lg shadow-xl z-50 max-w-sm`;
        notification.style.animation = 'slideIn 0.3s ease-out';
        notification.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="text-2xl animate-pulse">🆘</div>
                <div class="flex-1">
                    <div class="font-bold">Alerta de Riesgo ${levelText}</div>
                    <div class="text-sm font-medium mt-1">${alert.patient_name} (${alert.patient_age} años)</div>
                    <div class="text-xs mt-1 opacity-90 italic">"${alert.message_snapshot.substring(0, 60)}..."</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 ml-2">
                    ✕
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remover después de 10 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => notification.remove(), 300);
            }
        }, 10000);
    }

    // Reproducir sonido
    playSound() {
        this.audioAlert.currentTime = 0;
        this.audioAlert.play().catch(e => console.log('No se pudo reproducir audio:', e));
    }

    // Actualizar badge de contador
    updateBadge(count) {
        const badge = document.getElementById('alert-badge');
        if (badge) {
            const current = parseInt(badge.textContent) || 0;
            badge.textContent = current + count;
            badge.classList.remove('hidden');
            badge.classList.add('animate-bounce');
        }
    }

    // Agregar a lista de alertas en dashboard
    addToAlertList(alert) {
        const list = document.getElementById('alerts-list');
        if (!list) return;

        const borderColor = alert.severity === 'red' ? 'border-red-500' : 'border-orange-500';
        const badgeColor = alert.severity === 'red' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800';

        const card = document.createElement('div');
        card.className = `bg-white p-4 rounded-lg shadow-sm mb-3 border-l-4 ${borderColor}`;
        card.style.animation = 'fadeIn 0.3s ease-out';
        card.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <div>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold ${badgeColor} mb-1">
                        ${alert.severity === 'red' ? 'CRÍTICO' : 'ALTO'}
                    </span>
                    <h4 class="font-bold text-gray-800">${alert.patient_name}</h4>
                </div>
                <span class="text-xs text-gray-500">Ahora</span>
            </div>
            <p class="text-sm text-gray-600 italic mb-3">"${alert.message_snapshot}"</p>
            <div class="flex gap-2">
                <button onclick="acknowledgeAlert(${alert.id})" 
                    class="flex-1 bg-indigo-50 text-indigo-700 text-xs px-3 py-2 rounded hover:bg-indigo-100 transition">
                    Atender
                </button>
                <button onclick="viewPatient(${alert.patient_id})" 
                    class="flex-1 border border-gray-200 text-gray-600 text-xs px-3 py-2 rounded hover:bg-gray-50 transition">
                    Ver Perfil
                </button>
            </div>
        `;

        list.prepend(card);
    }

    // Sleep helper
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Función global para reconocer alertas
async function acknowledgeAlert(alertId) {
    try {
        const formData = new FormData();
        formData.append('alert_id', alertId);

        const response = await fetch('api/psychologist/acknowledge-alert.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar UI
            const badge = document.getElementById('alert-badge');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                badge.textContent = count > 0 ? count : '';
                if (count <= 0) badge.classList.add('hidden');
            }
            console.log('✅ Alerta reconocida');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Estilos para animaciones
const alertStyles = document.createElement('style');
alertStyles.textContent = `
    @keyframes slideIn { 
        from { transform: translateX(100%); opacity: 0; } 
        to { transform: translateX(0); opacity: 1; } 
    }
    @keyframes slideOut { 
        from { transform: translateX(0); opacity: 1; } 
        to { transform: translateX(100%); opacity: 0; } 
    }
    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(-10px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
`;
document.head.appendChild(alertStyles);

// Exportar para uso global
window.AlertSystem = AlertSystem;
window.acknowledgeAlert = acknowledgeAlert;

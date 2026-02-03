/**
 * MENTTA - Dashboard JavaScript (FIXED)
 * Maneja la interacción del panel de psicólogo
 * CORREGIDO: Sistema de alertas unificado
 */

let selectedPatientId = null;
let emotionChart = null;
let allPatients = [];
let lastAlertTimestamp = null;

// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', async () => {
    await loadPatients();
    startAlertPolling();
    setupEventListeners();
});

function setupEventListeners() {
    // Botón de refrescar pacientes
    document.getElementById('refresh-patients').addEventListener('click', loadPatients);

    // Búsqueda de pacientes
    document.getElementById('search-patients').addEventListener('input', filterPatients);
}

// ============================================
// CARGA DE PACIENTES
// ============================================

async function loadPatients() {
    const container = document.getElementById('patients-list');

    // Mostrar loading
    container.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p>Cargando pacientes...</p>
        </div>
    `;

    try {
        const response = await fetch('api/psychologist/get-patients.php');
        const data = await response.json();

        if (data.success) {
            allPatients = data.data;
            renderPatientsList(allPatients);
        } else {
            container.innerHTML = `<p class="text-red-500 text-center py-4">${data.error || 'Error al cargar'}</p>`;
        }
    } catch (error) {
        console.error('Error cargando pacientes:', error);
        container.innerHTML = `<p class="text-red-500 text-center py-4">Error de conexión</p>`;
    }
}

function filterPatients() {
    const searchTerm = document.getElementById('search-patients').value.toLowerCase();
    const filtered = allPatients.filter(p =>
        p.name.toLowerCase().includes(searchTerm) ||
        p.email.toLowerCase().includes(searchTerm)
    );
    renderPatientsList(filtered);
}

function renderPatientsList(patients) {
    const container = document.getElementById('patients-list');
    container.innerHTML = '';

    if (patients.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p>No hay pacientes vinculados</p>
            </div>
        `;
        return;
    }

    patients.forEach(patient => {
        const card = document.createElement('div');
        card.className = `patient-card p-4 rounded-xl border cursor-pointer transition-all duration-200 ${selectedPatientId === patient.id ? 'bg-blue-50 border-blue-500 shadow-md' : 'bg-white hover:bg-gray-50 border-gray-200'
            }`;
        card.dataset.patientId = patient.id;
        card.onclick = () => selectPatient(patient.id, card);

        const statusConfig = {
            'stable': { emoji: '🟢', text: 'Estable', class: 'bg-green-100 text-green-800' },
            'monitor': { emoji: '🟡', text: 'Monitorear', class: 'bg-yellow-100 text-yellow-800' },
            'risk': { emoji: '🔴', text: 'Riesgo', class: 'bg-red-100 text-red-800' }
        };

        const status = statusConfig[patient.status] || statusConfig['stable'];
        const initial = patient.name.charAt(0).toUpperCase();

        card.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                    ${initial}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800 truncate">${patient.name}</span>
                        <span class="text-lg">${status.emoji}</span>
                    </div>
                    <div class="text-sm text-gray-500">${patient.age} años</div>
                    <div class="text-xs text-gray-400 mt-1">${patient.last_activity_formatted}</div>
                </div>
                ${patient.unread_alerts > 0 ? `
                    <div class="bg-red-600 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center font-bold animate-pulse">
                        ${patient.unread_alerts}
                    </div>
                ` : ''}
            </div>
        `;

        container.appendChild(card);
    });
}

// ============================================
// SELECCIÓN DE PACIENTE
// ============================================

async function selectPatient(patientId, cardElement) {
    selectedPatientId = patientId;

    // Actualizar UI de selección
    document.querySelectorAll('.patient-card').forEach(card => {
        card.classList.remove('bg-blue-50', 'border-blue-500', 'shadow-md');
        card.classList.add('bg-white', 'border-gray-200');
    });

    if (cardElement) {
        cardElement.classList.remove('bg-white', 'border-gray-200');
        cardElement.classList.add('bg-blue-50', 'border-blue-500', 'shadow-md');
    }

    // Mostrar loading
    document.getElementById('no-patient-selected').classList.add('hidden');
    document.getElementById('patient-info').classList.add('hidden');
    document.getElementById('patient-loading').classList.remove('hidden');

    await loadPatientDetail(patientId);
}

// Función global para ver paciente (llamada desde alertas)
function viewPatient(patientId) {
    const card = document.querySelector(`[data-patient-id="${patientId}"]`);
    selectPatient(patientId, card);
}

async function loadPatientDetail(patientId) {
    try {
        const response = await fetch(`api/psychologist/get-patient-detail.php?patient_id=${patientId}`);
        const data = await response.json();

        document.getElementById('patient-loading').classList.add('hidden');

        if (data.success) {
            renderPatientDetail(data.data);
        } else {
            document.getElementById('no-patient-selected').classList.remove('hidden');
            showToast('Error al cargar detalles', 'error');
        }
    } catch (error) {
        console.error('Error cargando detalle:', error);
        document.getElementById('patient-loading').classList.add('hidden');
        document.getElementById('no-patient-selected').classList.remove('hidden');
        showToast('Error de conexión', 'error');
    }
}

function renderPatientDetail(detail) {
    document.getElementById('patient-info').classList.remove('hidden');

    const patient = detail.patient;
    const metrics = detail.metrics;

    // Avatar
    const initial = patient.name.charAt(0).toUpperCase();
    document.getElementById('patient-avatar').textContent = initial;

    // Header info
    document.getElementById('patient-name-detail').textContent = patient.name;
    document.getElementById('patient-age-detail').textContent = `${patient.age} años`;
    document.getElementById('patient-since-detail').textContent = `Paciente desde ${formatDate(patient.linked_since)}`;

    // Status
    const statusConfig = {
        'stable': { emoji: '🟢', text: 'Estable', class: 'bg-green-100 text-green-800' },
        'monitor': { emoji: '🟡', text: 'Monitorear', class: 'bg-yellow-100 text-yellow-800' },
        'risk': { emoji: '🔴', text: 'En Riesgo', class: 'bg-red-100 text-red-800' }
    };
    const status = statusConfig[patient.status] || statusConfig['stable'];

    document.getElementById('patient-status-emoji').textContent = status.emoji;
    document.getElementById('patient-status-badge').textContent = status.text;
    document.getElementById('patient-status-badge').className = `px-3 py-1 rounded-full text-xs font-medium ${status.class}`;

    // Métricas
    document.getElementById('metric-conversations').textContent = metrics.total_conversations;
    document.getElementById('metric-avg-messages').textContent = metrics.avg_messages_per_day;
    document.getElementById('metric-last-active').textContent = metrics.last_active_formatted;
    document.getElementById('metric-streak').innerHTML = `${metrics.streak_days} <span class="text-sm font-normal">días</span>`;

    // Gráfico
    renderEmotionChart(detail.emotion_history);

    // Alertas
    renderAlertsTimeline(detail.recent_alerts);

    // Temas
    renderTopTopics(detail.top_topics);
}

// ============================================
// GRÁFICO DE EMOCIONES
// ============================================

function renderEmotionChart(emotionHistory) {
    const canvas = document.getElementById('emotion-chart');
    const noDataDiv = document.getElementById('no-chart-data');

    if (!emotionHistory || emotionHistory.length === 0) {
        canvas.style.display = 'none';
        noDataDiv.classList.remove('hidden');
        return;
    }

    canvas.style.display = 'block';
    noDataDiv.classList.add('hidden');

    const ctx = canvas.getContext('2d');

    // Destruir gráfico anterior
    if (emotionChart) {
        emotionChart.destroy();
    }

    const labels = emotionHistory.map(item => formatDateShort(item.date));

    emotionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Positividad',
                    data: emotionHistory.map(item => item.positive),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Ansiedad',
                    data: emotionHistory.map(item => item.anxiety),
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Tristeza',
                    data: emotionHistory.map(item => item.sadness),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Negatividad',
                    data: emotionHistory.map(item => item.negative),
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    ticks: {
                        callback: function (value) {
                            return (value * 100).toFixed(0) + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + (context.raw * 100).toFixed(0) + '%';
                        }
                    }
                }
            }
        }
    });
}

// ============================================
// ALERTAS
// ============================================

function renderAlertsTimeline(alerts) {
    const container = document.getElementById('alerts-timeline');
    container.innerHTML = '';

    if (!alerts || alerts.length === 0) {
        container.innerHTML = `
            <div class="text-center py-6 text-gray-500">
                <svg class="w-10 h-10 mx-auto mb-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p>No hay alertas recientes</p>
            </div>
        `;
        return;
    }

    alerts.forEach(alert => {
        const item = document.createElement('div');
        item.className = 'alert-item flex gap-3 p-3 rounded-lg bg-gray-50 border-l-4 ' +
            (alert.severity === 'red' ? 'border-red-500' : 'border-orange-500');
        item.dataset.alertId = alert.id;

        const severityIcon = alert.severity === 'red' ? '🚨' : '⚠️';
        const severityText = alert.severity === 'red' ? 'Crítica' : 'Alerta';
        const severityColor = alert.severity === 'red' ? 'text-red-600' : 'text-orange-600';

        const messagePreview = alert.message_snapshot
            ? (alert.message_snapshot.length > 80
                ? alert.message_snapshot.substring(0, 80) + '...'
                : alert.message_snapshot)
            : 'Sin mensaje';

        item.innerHTML = `
            <div class="text-2xl">${severityIcon}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold ${severityColor}">${severityText}</span>
                    <span class="text-xs text-gray-400">${formatDateTime(alert.created_at)}</span>
                </div>
                <p class="text-sm text-gray-700 mt-1 break-words">"${messagePreview}"</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs px-2 py-1 rounded-full ${alert.status === 'pending'
                ? 'bg-red-100 text-red-700'
                : 'bg-gray-100 text-gray-600'
            }">
                        ${alert.status === 'pending' ? '● Pendiente' : '✓ Reconocida'}
                    </span>
                    ${alert.status === 'pending' ? `
                        <button onclick="acknowledgeAlert(${alert.id})" 
                            class="text-xs px-3 py-1 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">
                            Marcar atendida
                        </button>
                    ` : ''}
                </div>
            </div>
        `;

        container.appendChild(item);
    });
}

// ============================================
// TEMAS
// ============================================

function renderTopTopics(topics) {
    const container = document.getElementById('top-topics');
    container.innerHTML = '';

    if (!topics || topics.length === 0) {
        container.innerHTML = `
            <p class="text-gray-500 text-sm py-4">No hay suficientes datos para analizar temas</p>
        `;
        return;
    }

    // Colores para los tags
    const colors = [
        'bg-blue-100 text-blue-700',
        'bg-green-100 text-green-700',
        'bg-purple-100 text-purple-700',
        'bg-pink-100 text-pink-700',
        'bg-indigo-100 text-indigo-700',
        'bg-teal-100 text-teal-700',
        'bg-orange-100 text-orange-700',
        'bg-cyan-100 text-cyan-700'
    ];

    topics.forEach((topic, index) => {
        const tag = document.createElement('span');
        tag.className = `px-3 py-2 rounded-full text-sm font-medium ${colors[index % colors.length]}`;
        tag.textContent = `${topic.word} (${topic.frequency})`;
        container.appendChild(tag);
    });
}

// ============================================
// SISTEMA DE ALERTAS EN TIEMPO REAL (UNIFICADO)
// ============================================

let alertPollingInterval = null;

function startAlertPolling() {
    console.log('🛡️ Sistema de alertas iniciado');
    checkForAlerts();
    // Polling cada 5 segundos para alertas críticas
    alertPollingInterval = setInterval(checkForAlerts, 5000);
}

async function checkForAlerts() {
    try {
        const url = `api/psychologist/check-alerts.php?timeout=3${lastAlertTimestamp ? '&last_check=' + lastAlertTimestamp : ''}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.data) {
            // Actualizar badge
            updateAlertBadge(data.data.pending_count);

            // Guardar timestamp
            lastAlertTimestamp = data.data.timestamp;

            // Si hay nuevas alertas, mostrar notificación
            if (data.data.new_alerts && data.data.new_alerts.length > 0) {
                data.data.new_alerts.forEach(alert => {
                    showAlertPopup(alert);
                });
                playAlertSound();

                // Recargar lista de pacientes para actualizar badges
                loadPatients();
            }
        }
    } catch (error) {
        console.error('Error checking alerts:', error);
    }
}

function updateAlertBadge(count) {
    const badge = document.getElementById('alert-badge');
    if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function showAlertPopup(alert) {
    const container = document.getElementById('alert-popup-container');

    const popup = document.createElement('div');
    popup.className = 'bg-white rounded-lg shadow-xl border-l-4 border-red-500 p-4 max-w-sm animate-slideIn';
    popup.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="text-2xl animate-pulse">🚨</div>
            <div class="flex-1">
                <div class="font-semibold text-red-600">Nueva Alerta de Crisis</div>
                <div class="text-sm font-medium text-gray-800 mt-1">${alert.patient_name || 'Paciente'}</div>
                <div class="text-xs text-gray-500 mt-1">${alert.message_snapshot ? alert.message_snapshot.substring(0, 60) + '...' : ''}</div>
                <div class="flex gap-2 mt-3">
                    <button onclick="viewPatient(${alert.patient_id}); this.closest('.animate-slideIn').remove();" 
                        class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Ver paciente
                    </button>
                    <button onclick="acknowledgeAlert(${alert.id}); this.closest('.animate-slideIn').remove();" 
                        class="text-xs px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">
                        Marcar atendida
                    </button>
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    container.appendChild(popup);

    // Auto-remove after 15 seconds
    setTimeout(() => {
        if (popup.parentElement) {
            popup.remove();
        }
    }, 15000);
}

function playAlertSound() {
    const audio = document.getElementById('alert-sound');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(() => { }); // Ignore autoplay errors
    }
}

// ============================================
// RECONOCER ALERTAS
// ============================================

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
            showToast('Alerta marcada como atendida', 'success');

            // Actualizar UI - remover alerta de la lista
            const alertItem = document.querySelector(`[data-alert-id="${alertId}"]`);
            if (alertItem) {
                alertItem.classList.add('opacity-50');
                const statusBadge = alertItem.querySelector('.bg-red-100');
                if (statusBadge) {
                    statusBadge.className = 'text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600';
                    statusBadge.textContent = '✓ Reconocida';
                }
                // Remover botón
                const btn = alertItem.querySelector('button[onclick^="acknowledgeAlert"]');
                if (btn) btn.remove();
            }

            // Actualizar badge
            const badge = document.getElementById('alert-badge');
            if (badge && !badge.classList.contains('hidden')) {
                const count = parseInt(badge.textContent) - 1;
                if (count <= 0) {
                    badge.classList.add('hidden');
                } else {
                    badge.textContent = count > 9 ? '9+' : count;
                }
            }

            // Recargar pacientes para actualizar badges individuales
            loadPatients();

        } else {
            showToast('Error al marcar alerta', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    }
}

// ============================================
// UTILIDADES
// ============================================

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

function formatDateShort(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
        day: 'numeric',
        month: 'short'
    });
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };

    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slideIn`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Animaciones CSS
const dashboardStyles = document.createElement('style');
dashboardStyles.textContent = `
    @keyframes slideIn { 
        from { transform: translateX(100%); opacity: 0; } 
        to { transform: translateX(0); opacity: 1; } 
    }
    .animate-slideIn { animation: slideIn 0.3s ease-out; }
`;
document.head.appendChild(dashboardStyles);

/**
 * MENTTA - Dashboard JavaScript (FIXED)
 * Maneja la interacción del panel de psicólogo
 * CORREGIDO: Sistema de alertas unificado
 */

// Debug mode (DEV-021)
const DEBUG_MODE = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
function debugLog(...args) {
    if (DEBUG_MODE) console.log('[Dashboard Debug]', ...args);
}
function debugError(...args) {
    if (DEBUG_MODE) console.error('[Dashboard Error]', ...args);
}

let selectedPatientId = null;
let emotionChart = null;
let allPatients = [];
let lastAlertTimestamp = null;
let alertPollingDelay = 5000; // DEV-015: Base delay para exponential backoff

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
        debugError('Error cargando pacientes:', error);
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
        // UX-006: Estado vacío mejorado y atractivo
        container.innerHTML = `
            <div class="text-center py-10">
                <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">¡Bienvenido al Panel!</h3>
                <p class="text-gray-500 text-sm mb-4">Aún no tienes pacientes vinculados.</p>
                <p class="text-gray-400 text-xs">Los pacientes pueden vincularse desde su perfil.</p>
            </div>
        `;
        return;
    }

    patients.forEach((patient, index) => {
        const card = document.createElement('div');
        card.className = `patient-card p-6 group ${selectedPatientId === patient.id ? 'selected' : ''}`;
        card.dataset.patientId = patient.id;
        card.onclick = () => selectPatient(patient.id, card);

        const statusConfig = {
            'stable': { color: '#B5C9B5', label: 'Estable' }, // Soft Sage
            'monitor': { color: '#E8C07D', label: 'Monitorear' }, // Soft Amber
            'risk': { color: '#C8553D', label: 'En Riesgo' }   // Terracotta
        };

        const status = statusConfig[patient.status] || statusConfig['stable'];
        const initial = patient.name.charAt(0).toUpperCase();

        // Cycle through 4 soft premium gradients
        const gradientClass = `avatar-pastel-${(index % 4) + 1}`;

        card.innerHTML = `
            <div class="flex items-center gap-5">
                <div class="${gradientClass} w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold text-xl flex-shrink-0 shadow-sm">
                    ${initial}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-3 mb-1.5">
                        <span class="patient-name truncate text-lg">${patient.name}</span>
                        <div class="w-2.5 h-2.5 rounded-full status-pulse flex-shrink-0" style="background-color: ${status.color};"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="patient-meta font-bold uppercase tracking-[0.15em] text-[9px]">${patient.age} Años</span>
                        <span class="text-[8px] opacity-10 text-black">•</span>
                        <span class="patient-meta font-medium text-[9px] uppercase tracking-[0.1em] opacity-60">${patient.last_activity_formatted}</span>
                    </div>
                </div>
                ${patient.unread_alerts > 0 ? `
                    <div class="bg-red-500 text-white text-[10px] font-black rounded-full h-7 w-7 flex items-center justify-center shadow-lg animate-pulse ring-4 ring-red-500/5">
                        ${patient.unread_alerts}
                    </div>
                ` : `
                    <div class="opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                        <svg class="w-5 h-5 text-black/20 stroke-icon-fine" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                `}
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
        card.classList.remove('selected');
    });

    if (cardElement) {
        cardElement.classList.add('selected');
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
        debugError('Error cargando detalle:', error);
        document.getElementById('patient-loading').classList.add('hidden');
        document.getElementById('no-patient-selected').classList.remove('hidden');
        showToast('Error de conexión', 'error');
    }
}

function renderPatientDetail(detail) {
    document.getElementById('patient-info').classList.remove('hidden');

    const patient = detail.patient;
    const metrics = detail.metrics;

    // Avatar with pastel gradient based on ID
    const initial = patient.name.charAt(0).toUpperCase();
    const avatarEl = document.getElementById('patient-avatar');
    avatarEl.textContent = initial;

    // Cycle through 4 soft premium gradients
    const gradientId = (parseInt(patient.id) % 4) + 1;
    avatarEl.className = `avatar-pastel-${gradientId} w-20 h-20 rounded-[1.8rem] flex items-center justify-center text-white text-3xl font-bold shadow-lg transition-all duration-500`;

    // Header info
    document.getElementById('patient-name-detail').textContent = patient.name;
    document.getElementById('patient-age-detail').textContent = `${patient.age} años`;
    document.getElementById('patient-since-detail').textContent = `Desde ${formatDate(patient.linked_since)}`;

    // Status
    const statusConfig = {
        'stable': { emoji: '🟢', text: 'Estable', color: '#B5C9B5' },
        'monitor': { emoji: '🟡', text: 'Monitorear', color: '#E8C07D' },
        'risk': { emoji: '🔴', text: 'En Riesgo', color: '#C8553D' }
    };
    const status = statusConfig[patient.status] || statusConfig['stable'];

    document.getElementById('patient-status-emoji').textContent = status.emoji;
    document.getElementById('patient-status-badge').textContent = status.text;

    const pulseDot = document.querySelector('.status-pulse');
    if (pulseDot) pulseDot.style.backgroundColor = status.color;

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

    // DEV-016: Null safety - convertir valores null a 0
    const safeValue = (val) => (val === null || val === undefined || isNaN(val)) ? 0 : parseFloat(val);

    emotionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Positividad',
                    data: emotionHistory.map(item => safeValue(item.positive)),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Ansiedad',
                    data: emotionHistory.map(item => safeValue(item.anxiety)),
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Tristeza',
                    data: emotionHistory.map(item => safeValue(item.sadness)),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Negatividad',
                    data: emotionHistory.map(item => safeValue(item.negative)),
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
        item.className = 'alert-item p-4 rounded-2xl bg-[#FCFCFA] mb-3 border border-black/5 transition-all hover:bg-white hover:shadow-soft';
        item.dataset.alertId = alert.id;

        const messagePreview = alert.message_snapshot
            ? (alert.message_snapshot.length > 80
                ? alert.message_snapshot.substring(0, 80) + '...'
                : alert.message_snapshot)
            : 'Sin mensaje';

        const severityLabel = alert.severity === 'red' ? 'Crítica' : 'Prioritaria';
        const severityClass = alert.severity === 'red' ? 'text-red-500 bg-red-50' : 'text-orange-500 bg-orange-50';

        item.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl ${severityClass} flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 stroke-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] ${alert.severity === 'red' ? 'text-red-400' : 'text-orange-400'}">${severityLabel}</span>
                        <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest">${formatDateTime(alert.created_at)}</span>
                    </div>
                    <p class="text-sm font-medium text-[#4A4A4A] leading-relaxed mb-3">"${messagePreview}"</p>
                    <div class="flex items-center gap-3">
                        <span class="text-[9px] font-bold uppercase tracking-widest px-3 py-1 rounded-full ${alert.status === 'pending' ? 'bg-red-50 text-red-500' : 'bg-gray-50 text-gray-400'}">
                            ${alert.status === 'pending' ? '● Pendiente' : '✓ Atendida'}
                        </span>
                        ${alert.status === 'pending' ? `
                            <button onclick="acknowledgeAlert(${alert.id})" 
                                class="text-[9px] font-bold uppercase tracking-widest text-indigo-500 hover:text-indigo-700 transition-colors">
                                Marcar atendida
                            </button>
                        ` : ''}
                    </div>
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
            <p class="text-gray-400 text-xs font-medium uppercase tracking-widest py-8">Análisis insuficiente</p>
        `;
        return;
    }

    const colors = [
        'bg-blue-50 text-blue-600',
        'bg-green-50 text-green-600',
        'bg-purple-50 text-purple-600',
        'bg-pink-50 text-pink-600',
        'bg-indigo-50 text-indigo-600'
    ];

    topics.forEach((topic, index) => {
        const tag = document.createElement('span');
        tag.className = `px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest ${colors[index % colors.length]} border border-black/[0.03]`;
        tag.textContent = `${topic.word} (${topic.frequency})`;
        container.appendChild(tag);
    });
}

// ============================================
// SISTEMA DE ALERTAS EN TIEMPO REAL
// ============================================

let alertPollingInterval = null;

function startAlertPolling() {
    debugLog('🛡️ Sistema de alertas iniciado');
    checkForAlerts();
    scheduleNextPoll();
}

// DEV-015: Exponential backoff para polling
function scheduleNextPoll() {
    setTimeout(async () => {
        await checkForAlerts();
        scheduleNextPoll();
    }, alertPollingDelay);
}

async function checkForAlerts() {
    try {
        const url = `api/psychologist/check-alerts.php?timeout=3${lastAlertTimestamp ? '&last_check=' + lastAlertTimestamp : ''}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.data) {
            updateAlertBadge(data.data.pending_count);
            lastAlertTimestamp = data.data.timestamp;

            if (data.data.new_alerts && data.data.new_alerts.length > 0) {
                data.data.new_alerts.forEach(alert => {
                    showAlertPopup(alert);
                });
                playAlertSound();
                alertPollingDelay = 5000;
                loadPatients();
            } else {
                alertPollingDelay = Math.min(alertPollingDelay * 1.2, 30000);
            }
        }
    } catch (error) {
        debugError('Error checking alerts:', error);
        alertPollingDelay = 30000;
    }
}

function updateAlertBadge(count) {
    const badge = document.getElementById('alert-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function showAlertPopup(alert) {
    const container = document.getElementById('alert-popup-container');
    if (!container) return;

    const popup = document.createElement('div');
    popup.className = 'bg-white rounded-[2rem] shadow-[0_20px_60px_rgba(0,0,0,0.1)] border border-black/5 p-6 max-w-sm animate-slideIn relative overflow-hidden mb-3';
    popup.innerHTML = `
        <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500"></div>
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center flex-shrink-0 animate-pulse">
                <svg class="w-6 h-6 text-red-500 stroke-icon" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-red-400 mb-1">Alerta Crítica</div>
                <div class="text-base font-bold text-[#2A2A2A] mb-1">${alert.patient_name || 'Paciente'}</div>
                <p class="text-[10px] font-medium text-gray-400 leading-relaxed truncate">${alert.message_snapshot || ''}</p>
                <div class="flex gap-4 mt-5">
                    <button onclick="viewPatient(${alert.patient_id}); this.closest('.animate-slideIn').remove();" 
                        class="text-[9px] font-bold uppercase tracking-widest text-indigo-500 hover:text-indigo-700 transition-colors">
                        Ver Sesión
                    </button>
                    <button onclick="acknowledgeAlert(${alert.id}); this.closest('.animate-slideIn').remove();" 
                        class="text-[9px] font-bold uppercase tracking-widest text-[#AAA] hover:text-red-500 transition-all">
                        Ignorar
                    </button>
                </div>
            </div>
        </div>
    `;

    container.appendChild(popup);
    setTimeout(() => { if (popup.parentElement) popup.remove(); }, 15000);
}

function playAlertSound() {
    const audio = document.getElementById('alert-sound');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(() => { });
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
            const alertItem = document.querySelector(`[data-alert-id="${alertId}"]`);
            if (alertItem) {
                alertItem.classList.add('opacity-50');
                const badge = alertItem.querySelector('.bg-red-50');
                if (badge) {
                    badge.className = 'text-[9px] font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-gray-50 text-gray-400';
                    badge.textContent = '✓ Atendida';
                }
                const btn = alertItem.querySelector('button[onclick^="acknowledgeAlert"]');
                if (btn) btn.remove();
            }
            checkForAlerts();
            loadPatients();
        } else {
            showToast('Error al marcar alerta', 'error');
        }
    } catch (error) {
        debugError('Error:', error);
        showToast('Error de conexión', 'error');
    }
}

// ============================================
// UTILIDADES
// ============================================

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateShort(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' });
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function showToast(message, type = 'info') {
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-indigo-500' };
    const toast = document.createElement('div');
    toast.className = `fixed bottom-8 right-8 ${colors[type]} text-white px-8 py-4 rounded-2xl shadow-premium z-50 animate-slideUp font-bold text-xs uppercase tracking-widest`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 3000);
}

// Animaciones CSS
const dashboardStyles = document.createElement('style');
dashboardStyles.textContent = `
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .animate-slideIn { animation: slideIn 0.3s ease-out; }
        `;
document.head.appendChild(dashboardStyles);

const dashboardStylesFix = document.createElement('style');
dashboardStylesFix.textContent = `
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .animate-slideIn { animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
    .animate-slideUp { animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
    .shadow-soft { box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }

// ============================================
// MODAL VINCULAR PACIENTE
// ============================================

async function openConnectModal() {
    const modal = document.getElementById('connect-patient-modal');
    // const modalBody = modal.querySelector('div');
    const codeEl = document.getElementById('generated-code');
    const qrEl = document.getElementById('generated-qr');
    
    // Reset state
    codeEl.textContent = '...';
    qrEl.style.opacity = '0.5';
    
    modal.classList.remove('hidden');
    // Force reflow
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    // modalBody.classList.remove('scale-95');
    
    try {
        const response = await fetch('api/psychologist/generate-code.php');
        const data = await response.json();
        
        if (data.success) {
            codeEl.textContent = data.code;
            // Usar API pública para QR
            qrEl.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${data.code}&color=2d3a2d&bgcolor=f8f9fa`;
qrEl.onload = () => { qrEl.style.opacity = '1'; };
        } else {
    showToast('Error al generar código', 'error');
    closeConnectModal();
}
    } catch (error) {
    debugError('Error generating code:', error);
    showToast('Error de conexión', 'error');
    closeConnectModal();
}
}

function closeConnectModal() {
    const modal = document.getElementById('connect-patient-modal');
    // const modalBody = modal.querySelector('div');

    modal.classList.add('opacity-0');
    // modalBody.classList.add('scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}


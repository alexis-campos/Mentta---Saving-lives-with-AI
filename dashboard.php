<?php
/**
 * MENTTA - Dashboard del Psicólogo
 * Panel de monitoreo de pacientes con gráficos y alertas
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Verificar autenticación
$user = checkAuth();
if (!$user) {
    header('Location: login.php');
    exit;
}

// Solo psicólogos pueden acceder
if ($user['role'] !== 'psychologist') {
    header('Location: chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mentta Profesional</title>

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧠</text></svg>">
</head>

<body class="font-sans" style="background-color: var(--mentta-bg);">
    <!-- Header -->
    <header class="sticky top-0 z-30 px-8 py-6">
        <div class="max-w-[1600px] mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full overflow-hidden border border-white p-0.5 bg-white shadow-premium">
                        <img src="images/Menta_icono.jpg" alt="Mentta" class="w-full h-full object-cover rounded-full">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold font-serif"
                            style="color: var(--mentta-charcoal); letter-spacing: -0.01em;">Mentta Profesional</h1>
                        <p class="text-[8px] uppercase tracking-[0.3em] font-bold opacity-30">Mental Health Suite</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <!-- Profile Capsule -->
                <div class="nav-capsule flex items-center gap-4 py-2 pl-2 pr-6">
                    <div
                        class="avatar-soft-gradient w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-inner">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#AAA] font-sans">Especialista</span>
                        <span class="text-sm font-bold text-[#2A2A2A] leading-none font-sans">Dra.
                            <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
                    </div>
                </div>

                <!-- Notifications & Logout -->
                <div class="flex items-center gap-4">
                    <!-- New Patient Button -->
                    <button onclick="openConnectModal()" class="px-5 py-2.5 bg-[#2A2A2A] text-white rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-black transition-all shadow-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Nuevo Paciente</span>
                    </button>

                    <div class="relative">
                        <button id="alerts-button"
                            class="w-11 h-11 flex items-center justify-center rounded-full bg-white shadow-premium border border-black/5 text-[#888] hover:text-red-500 transition-all">
                            <svg class="w-5 h-5 stroke-icon-fine" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            <span id="alert-badge"
                                class="absolute top-0 right-0 h-4 min-w-[16px] px-1 bg-red-500 text-white text-[8px] font-black rounded-full flex items-center justify-center border-2 border-white hidden">0</span>
                        </button>
                    </div>

                    <a href="logout.php" class="logout-pill" title="Finalizar Sesión">
                        <svg class="w-4 h-4 stroke-icon-fine" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Connect Patient Modal -->
    <div id="connect-patient-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] p-8 max-w-md w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-6 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold font-serif text-[#2A2A2A] mb-2">Vincular Nuevo Paciente</h3>
                <p class="text-gray-500 text-sm mb-8">Comparte este código con tu paciente o pídele que escanee el QR.</p>
                
                <!-- Code Display -->
                <div class="bg-gray-50 rounded-2xl p-6 mb-6 border border-gray-100">
                    <div id="generated-code" class="text-4xl font-mono font-bold tracking-[0.2em] text-[#2A2A2A] mb-2 select-all">------</div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-[#AAA]">Código de Vinculación</div>
                </div>

                <!-- QR Display -->
                <div class="flex justify-center mb-6">
                    <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
                        <img id="generated-qr" src="" alt="QR Code" class="w-48 h-48 object-contain opacity-50">
                    </div>
                </div>

                <p class="text-xs text-orange-400 font-medium mb-8 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Válido por 24 horas
                </p>

                <button onclick="closeConnectModal()" class="w-full py-4 rounded-xl bg-gray-100 text-gray-600 font-bold text-sm uppercase tracking-widest hover:bg-gray-200 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <div class="flex h-[calc(100vh-100px)] px-6 pb-6 gap-6">
        <!-- Sidebar: Lista de pacientes -->
        <aside id="sidebar" class="w-96 flex flex-col h-full bg-transparent overflow-hidden">
            <div class="flex flex-col h-full rounded-[2rem] p-6" style="background-color: var(--mentta-bg);">
                <div class="flex items-center justify-between mb-8 px-2">
                    <h2 class="text-2xl font-bold font-serif text-[#2A2A2A]">Mis Pacientes</h2>
                    <button id="refresh-patients" class="text-black/20 hover:text-black transition-all"
                        title="Actualizar">
                        <svg class="w-5 h-5 stroke-icon" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Search Capsule -->
                <div class="mb-8 px-2">
                    <div class="search-capsule flex items-center px-5 py-3 h-14">
                        <svg class="w-5 h-5 text-black/20 stroke-icon mr-3" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M21 21l-4.35-4.35" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <input type="text" id="search-patients" placeholder="Buscar por nombre..."
                            class="bg-transparent border-none outline-none text-sm w-full font-sans font-medium text-gray-800 placeholder-black/20">
                    </div>
                </div>

                <!-- Patients List -->
                <div id="patients-list" class="flex-1 overflow-y-auto px-2 space-y-4 custom-scrollbar">
                    <!-- Cards are injected by JS -->
                </div>
            </div>
        </aside>

        <!-- Área principal: Detalle del paciente -->
        <main
            class="flex-1 h-full rounded-[2.5rem] bg-white shadow-premium overflow-y-auto custom-scrollbar border border-black/5">
            <div id="patient-detail" class="h-full p-10">
                <!-- Estado inicial: sin paciente seleccionado -->
                <div id="no-patient-selected" class="flex items-center justify-center h-full">
                    <div class="max-w-md w-full text-center animate-fadeIn px-8">
                        <div class="relative mb-12 flex justify-center">
                            <!-- Background decorative glow -->
                            <div class="absolute inset-0 bg-[#cbaa8e]/10 blur-[80px] rounded-full scale-150"></div>

                            <!-- Illustration/Image Container -->
                            <div
                                class="relative w-72 h-72 rounded-[3.5rem] overflow-hidden shadow-2xl border-4 border-white rotate-2 hover:rotate-0 transition-transform duration-700">
                                <img src="images/Mentta_post.jpg" alt="Mentta Care" class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent">
                                </div>
                                <div class="absolute bottom-6 left-6 text-left">
                                    <p
                                        class="text-white text-[10px] font-bold uppercase tracking-widest leading-none mb-1">
                                        Elite Suite</p>
                                    <p class="text-white text-lg font-serif font-bold">Cuidado Humano</p>
                                </div>
                            </div>

                            <!-- Floating badge -->
                            <div
                                class="absolute -top-4 -right-4 w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg animate-bounce duration-[3000ms]">
                                <img src="images/Menta_icono.jpg" alt="" class="w-12 h-12 rounded-full">
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold font-serif mb-4" style="color: var(--mentta-charcoal);">Portal de
                            Cuidado</h2>
                        <p class="text-sm font-sans font-medium text-[#999] leading-relaxed mb-8">
                            Gestiona tus consultas con la precisión del análisis de IA y la calidez de la atención
                            humana. Selecciona un paciente para iniciar.
                        </p>
                        <div
                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#F9F9F7] rounded-full border border-black/5 text-[#AAA] text-[10px] font-bold uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                            Selecciona una tarjeta
                        </div>
                    </div>
                </div>

                <!-- Loading state -->
                <div id="patient-loading" class="hidden flex items-center justify-center h-full">
                    <div class="text-center">
                        <svg class="animate-spin h-12 w-12 mx-auto mb-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-gray-600">Cargando información...</p>
                    </div>
                </div>

                <!-- Detalle del paciente (hidden inicialmente) -->
                <div id="patient-info" class="hidden animate-slideUp">
                    <!-- Header del paciente -->
                    <div
                        class="bg-white rounded-[2rem] p-8 mb-8 shadow-premium border border-black/5 relative overflow-hidden">
                        <!-- Watermark Isotype -->
                        <div
                            class="absolute -right-10 -bottom-10 w-48 h-48 opacity-[0.03] select-none pointer-events-none">
                            <img src="images/Menta_icono.jpg" alt="" class="w-full h-full grayscale">
                        </div>

                        <div class="flex items-start justify-between relative z-10">
                            <div class="flex items-center gap-6">
                                <div id="patient-avatar"
                                    class="w-20 h-20 rounded-[1.8rem] bg-gradient-to-br from-[#cbaa8e] to-[#2d3a2d]/40 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                                    A
                                </div>
                                <div>
                                    <h2 class="text-4xl font-bold font-serif text-[#2A2A2A] mb-2"
                                        id="patient-name-detail">Nombre del Paciente</h2>
                                    <div class="flex items-center gap-4 text-sm font-sans font-medium text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-green-500 status-pulse"></div>
                                            <span id="patient-status-badge">Estable</span>
                                        </div>
                                        <span class="opacity-30">•</span>
                                        <span id="patient-age-detail">0 años</span>
                                        <span class="opacity-30">•</span>
                                        <span id="patient-since-detail"
                                            class="text-[10px] uppercase tracking-widest text-[#AAA]">Desde...</span>
                                    </div>
                                </div>
                            </div>
                            <div id="patient-status-emoji" class="text-5xl opacity-20">🟢</div>
                        </div>
                    </div>

                    <!-- Métricas -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
                        <div class="metric-card p-8">
                            <div class="flex flex-col gap-5">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-[#FDF8F3] flex items-center justify-center border border-[#E8DED1]">
                                    <svg class="w-6 h-6 text-[#C4A484] stroke-icon-fine" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#AAA] mb-1.5">
                                        Sesiones</div>
                                    <div class="text-4xl font-bold text-[#2A2A2A] font-serif" id="metric-conversations">
                                        0</div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-card p-8">
                            <div class="flex flex-col gap-5">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-[#F5F9F5] flex items-center justify-center border border-[#D5E6D5]">
                                    <svg class="w-6 h-6 text-[#7FB07F] stroke-icon-fine" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#AAA] mb-1.5">
                                        Msj / Día</div>
                                    <div class="text-4xl font-bold text-[#2A2A2A] font-serif" id="metric-avg-messages">0
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-card p-8">
                            <div class="flex flex-col gap-5">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-[#F5F6F9] flex items-center justify-center border border-[#D5D9E6]">
                                    <svg class="w-6 h-6 text-[#7F8BB0] stroke-icon-fine" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#AAA] mb-1.5">
                                        Última Vez</div>
                                    <div class="text-sm font-bold text-[#4A4A4A] mt-2 leading-tight"
                                        id="metric-last-active">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-card p-8">
                            <div class="flex flex-col gap-5">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-[#FFF9F5] flex items-center justify-center border border-[#F9E6D5]">
                                    <svg class="w-6 h-6 text-[#B08B7F] stroke-icon-fine" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#AAA] mb-1.5">
                                        Racha</div>
                                    <div class="text-4xl font-bold text-[#2A2A2A] font-serif" id="metric-streak">0 <span
                                            class="text-xs font-sans font-medium opacity-30 tracking-normal capitalize">Días</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de evolución emocional -->
                    <div class="content-panel mb-10">
                        <h3
                            class="section-title mb-10 pb-6 border-b border-black/[0.03] flex justify-between items-center">
                            <span>Evolución del Bienestar</span>
                            <span class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#CCC]">Análisis 30
                                Días</span>
                        </h3>
                        <div class="h-80">
                            <canvas id="emotion-chart"></canvas>
                        </div>
                        <div id="no-chart-data" class="hidden text-center py-16 text-[#DDD]">
                            <p class="font-bold text-[10px] uppercase tracking-widest">Información en proceso de
                                análisis</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <!-- Timeline de alertas -->
                        <div class="content-panel flex flex-col min-h-[450px]">
                            <h3 class="section-title mb-8 flex items-center justify-between">
                                <span>Línea Crítica</span>
                                <span class="w-2 h-2 rounded-full bg-red-400 status-pulse"></span>
                            </h3>
                            <div id="alerts-timeline" class="flex-1 space-y-5 overflow-y-auto custom-scrollbar pr-3">
                                <!-- Se llenarán dinámicamente -->
                            </div>
                        </div>

                        <!-- Temas principales -->
                        <div class="content-panel flex flex-col min-h-[450px]">
                            <h3 class="section-title mb-8">Conceptos Recurrentes</h3>
                            <div id="top-topics" class="flex flex-wrap gap-4 content-start">
                                <!-- Tags de temas -->
                            </div>
                            <div class="mt-auto pt-8 opacity-20 text-[9px] font-bold uppercase tracking-[0.3em]">
                                Generado por Mentta AI Analytics
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Alert Popup Container -->
    <div id="alert-popup-container" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <!-- Audio para alertas -->
    <audio id="alert-sound" preload="auto">
        <source src="assets/sounds/alert.wav" type="audio/wav">
    </audio>


    <!-- Scripts -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/alerts.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>

</html>
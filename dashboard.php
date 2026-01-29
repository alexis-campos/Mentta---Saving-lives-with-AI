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
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧠</text></svg>">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-20 shadow-sm">
        <div class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-blue-600">Mentta</h1>
                <span class="text-sm text-gray-500 hidden sm:inline">Profesional</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Badge de alertas -->
                <div class="relative">
                    <button id="alerts-button" class="relative p-2 text-gray-600 hover:text-red-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span id="alert-badge" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold hidden">0</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <span class="text-sm text-gray-700 hidden sm:inline"><?= htmlspecialchars($user['name']) ?></span>
                    <a href="logout.php" class="text-sm text-blue-600 hover:text-blue-700 ml-2">Salir</a>
                </div>
            </div>
        </div>
    </header>

    <div class="flex h-[calc(100vh-73px)]">
        <!-- Sidebar: Lista de pacientes -->
        <aside id="sidebar" class="w-80 bg-white border-r overflow-y-auto flex-shrink-0">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Mis Pacientes</h2>
                    <button id="refresh-patients" class="text-gray-400 hover:text-blue-600 transition-colors" title="Actualizar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="search-patients" placeholder="Buscar paciente..." 
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <!-- Patients List -->
                <div id="patients-list" class="space-y-2">
                    <!-- Loading state -->
                    <div class="text-center py-8 text-gray-500">
                        <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Cargando pacientes...</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Área principal: Detalle del paciente -->
        <main class="flex-1 overflow-y-auto bg-gray-50">
            <div id="patient-detail" class="p-6">
                <!-- Estado inicial: sin paciente seleccionado -->
                <div id="no-patient-selected" class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-lg mb-2">Selecciona un paciente</p>
                        <p class="text-sm">Haz clic en un paciente de la lista para ver sus detalles</p>
                    </div>
                </div>

                <!-- Loading state -->
                <div id="patient-loading" class="hidden flex items-center justify-center h-full">
                    <div class="text-center">
                        <svg class="animate-spin h-12 w-12 mx-auto mb-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-600">Cargando información...</p>
                    </div>
                </div>

                <!-- Detalle del paciente (hidden inicialmente) -->
                <div id="patient-info" class="hidden">
                    <!-- Header del paciente -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div id="patient-avatar" class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                                    A
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800" id="patient-name-detail">Nombre del Paciente</h2>
                                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                                        <span id="patient-age-detail">0 años</span>
                                        <span class="text-gray-300">•</span>
                                        <span id="patient-since-detail">Paciente desde...</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span id="patient-status-badge" class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Estable
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="patient-status-emoji" class="text-4xl">🟢</div>
                        </div>
                    </div>

                    <!-- Métricas -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-xl p-5 shadow-sm border">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Mensajes</div>
                                    <div class="text-2xl font-bold text-gray-800" id="metric-conversations">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-5 shadow-sm border">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Promedio/día</div>
                                    <div class="text-2xl font-bold text-gray-800" id="metric-avg-messages">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-5 shadow-sm border">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Última actividad</div>
                                    <div class="text-sm font-semibold text-gray-800" id="metric-last-active">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-5 shadow-sm border">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Racha</div>
                                    <div class="text-2xl font-bold text-gray-800" id="metric-streak">0 <span class="text-sm font-normal">días</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de evolución emocional -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Evolución Emocional (Últimos 30 días)</h3>
                        <div class="h-64">
                            <canvas id="emotion-chart"></canvas>
                        </div>
                        <div id="no-chart-data" class="hidden text-center py-8 text-gray-500">
                            <p>No hay datos suficientes para mostrar el gráfico</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Timeline de alertas -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">🚨 Alertas Recientes</h3>
                            <div id="alerts-timeline" class="space-y-3 max-h-80 overflow-y-auto">
                                <!-- Se llenarán dinámicamente -->
                            </div>
                        </div>

                        <!-- Temas principales -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">💬 Temas Principales</h3>
                            <div id="top-topics" class="flex flex-wrap gap-2">
                                <!-- Tags de temas -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Alert Popup Container -->
    <div id="alert-popup-container" class="fixed top-20 right-4 z-50 space-y-2"></div>

    <!-- Audio para alertas (opcional - crear archivo assets/sounds/alert.mp3) -->
    <!-- <audio id="alert-sound" preload="auto">
        <source src="assets/sounds/alert.mp3" type="audio/mpeg">
    </audio> -->


    <!-- Scripts -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/alerts.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>

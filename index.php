<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentta - Plataforma Profesional de Salud Mental</title>
    <meta name="description" content="Plataforma certificada de apoyo emocional con inteligencia artificial y psicólogos profesionales.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'mentta-primary': '#2d3a2d',
                        'mentta-secondary': '#cbaa8e',
                        'mentta-light': '#f5f5f0',
                        'mentta-accent': '#8b9d8b',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }

        .trust-badge {
            transition: all 0.3s ease;
        }

        .trust-badge:hover {
            transform: translateY(-4px);
        }

        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
        }

        .stat-counter {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>

<body class="bg-white antialiased">

    <!-- Professional Header -->
    <header class="fixed top-0 w-full bg-white/95 backdrop-blur-sm border-b border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <img src="assets/img/icon-new.png" alt="Mentta" class="h-12 w-auto">
                    <span class="text-2xl font-semibold text-mentta-primary">Mentta</span>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#como-funciona" class="text-gray-700 hover:text-mentta-primary transition text-sm font-medium">Cómo Funciona</a>
                    <a href="#seguridad" class="text-gray-700 hover:text-mentta-primary transition text-sm font-medium">Seguridad</a>
                    <a href="#profesionales" class="text-gray-700 hover:text-mentta-primary transition text-sm font-medium">Para Profesionales</a>
                    <a href="login.php" class="text-gray-700 hover:text-mentta-primary transition text-sm font-medium">Iniciar Sesión</a>
                    <a href="register.php" class="bg-mentta-primary text-white px-6 py-2.5 rounded-lg hover:bg-mentta-primary/90 transition text-sm font-medium">
                        Comenzar Ahora
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 bg-gradient-to-br from-mentta-light via-white to-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Content -->
                <div class="space-y-8">
                    <div class="inline-flex items-center space-x-2 bg-green-50 text-green-700 px-4 py-2 rounded-full text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Plataforma Certificada y Segura</span>
                    </div>

                    <h1 class="text-5xl lg:text-6xl font-bold text-mentta-primary leading-tight">
                        Apoyo Profesional en Salud Mental
                    </h1>

                    <p class="text-xl text-gray-600 leading-relaxed">
                        Combinamos inteligencia artificial avanzada con atención psicológica profesional para ofrecerte 
                        un soporte integral, confidencial y disponible cuando lo necesites.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="register.php" class="inline-flex items-center justify-center bg-mentta-primary text-white px-8 py-4 rounded-lg hover:bg-mentta-primary/90 transition font-medium text-lg shadow-lg shadow-mentta-primary/20">
                            Comenzar Evaluación Gratuita
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="login.php?role=psychologist" class="inline-flex items-center justify-center border-2 border-mentta-primary text-mentta-primary px-8 py-4 rounded-lg hover:bg-mentta-primary hover:text-white transition font-medium text-lg">
                            Acceso Profesionales
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="pt-8 flex items-center space-x-8 border-t border-gray-200">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Datos Encriptados</span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            <span class="text-sm font-medium">Psicólogos Certificados</span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Disponible 24/7</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Image/Illustration -->
                <div class="relative">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <img src="assets/img/user-mint-bg.png" alt="Plataforma Mentta" class="w-full h-auto">
                        <div class="absolute inset-0 bg-gradient-to-t from-mentta-primary/20 to-transparent"></div>
                    </div>
                    <!-- Floating Stats -->
                    <div class="absolute -bottom-8 -left-8 bg-white rounded-xl shadow-xl p-6 trust-badge">
                        <div class="text-4xl font-bold text-mentta-primary stat-counter">98%</div>
                        <div class="text-sm text-gray-600 mt-1">Satisfacción</div>
                    </div>
                    <div class="absolute -top-8 -right-8 bg-white rounded-xl shadow-xl p-6 trust-badge">
                        <div class="text-4xl font-bold text-mentta-primary stat-counter">24/7</div>
                        <div class="text-sm text-gray-600 mt-1">Disponibilidad</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-mentta-primary">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-bold text-white mb-2 stat-counter">+5,000</div>
                    <div class="text-mentta-accent text-sm">Usuarios Activos</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-white mb-2 stat-counter">150+</div>
                    <div class="text-mentta-accent text-sm">Psicólogos Certificados</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-white mb-2 stat-counter">100%</div>
                    <div class="text-mentta-accent text-sm">Confidencialidad</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-white mb-2 stat-counter">4.9/5</div>
                    <div class="text-mentta-accent text-sm">Valoración Promedio</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="como-funciona" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-mentta-primary mb-4">Cómo Funciona</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Un proceso simple y profesional diseñado para tu bienestar
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 relative">
                    <div class="absolute -top-4 left-8 bg-mentta-secondary text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold shadow-lg">1</div>
                    <div class="mt-6">
                        <div class="w-16 h-16 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-mentta-primary mb-3">Evaluación Inicial</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Completa un breve cuestionario confidencial que nos ayuda a entender tu situación actual y necesidades específicas.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 relative">
                    <div class="absolute -top-4 left-8 bg-mentta-secondary text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold shadow-lg">2</div>
                    <div class="mt-6">
                        <div class="w-16 h-16 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-mentta-primary mb-3">Soporte IA 24/7</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Accede a conversaciones terapéuticas con IA que analiza patrones y te brinda apoyo inmediato cuando lo necesites.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="feature-card bg-gray-50 rounded-2xl p-8 relative">
                    <div class="absolute -top-4 left-8 bg-mentta-secondary text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold shadow-lg">3</div>
                    <div class="mt-6">
                        <div class="w-16 h-16 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-mentta-primary mb-3">Atención Profesional</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Tu psicólogo recibe insights detallados y puede intervenir cuando sea necesario, garantizando atención humana experta.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section id="seguridad" class="py-24 bg-gradient-to-br from-gray-50 to-mentta-light">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl lg:text-5xl font-bold text-mentta-primary mb-6">
                        Tu Seguridad es Nuestra Prioridad
                    </h2>
                    <p class="text-xl text-gray-600 mb-8">
                        Implementamos los más altos estándares de seguridad y privacidad para proteger tu información y garantizar un espacio seguro.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-mentta-primary mb-2">Encriptación de Extremo a Extremo</h3>
                                <p class="text-gray-600">Todas tus conversaciones y datos están protegidos con encriptación AES-256.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-mentta-primary mb-2">Cumplimiento Normativo</h3>
                                <p class="text-gray-600">Cumplimos con las regulaciones de protección de datos y privacidad médica.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-mentta-primary mb-2">Equipo Certificado</h3>
                                <p class="text-gray-600">Todos nuestros psicólogos están certificados y siguen estrictos códigos de ética.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-mentta-primary mb-2">Control Total</h3>
                                <p class="text-gray-600">Tú decides qué información compartir y puedes eliminar tus datos en cualquier momento.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="bg-white rounded-2xl shadow-2xl p-8">
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 pb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-medium text-gray-600">Nivel de Seguridad</span>
                                    <span class="text-sm font-bold text-green-600">Máximo</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Encriptación SSL/TLS</span>
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Autenticación de Dos Factores</span>
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Auditorías de Seguridad</span>
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Backup Automático</span>
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Professional Section -->
    <section id="profesionales" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-mentta-primary mb-4">Para Profesionales de la Salud Mental</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Herramientas avanzadas que potencian tu práctica profesional
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="feature-card bg-gray-50 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-mentta-primary mb-3">Dashboard Analítico</h3>
                    <p class="text-gray-600">Visualiza el progreso de tus pacientes con métricas y reportes detallados.</p>
                </div>

                <div class="feature-card bg-gray-50 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-mentta-primary mb-3">Alertas Inteligentes</h3>
                    <p class="text-gray-600">Recibe notificaciones ante situaciones que requieren atención inmediata.</p>
                </div>

                <div class="feature-card bg-gray-50 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-mentta-primary mb-3">Gestión de Casos</h3>
                    <p class="text-gray-600">Organiza y gestiona tus pacientes de manera eficiente y segura.</p>
                </div>

                <div class="feature-card bg-gray-50 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-mentta-primary mb-3">Historial Completo</h3>
                    <p class="text-gray-600">Accede al historial completo de conversaciones y evolución del paciente.</p>
                </div>

                <div class="feature-card bg-gray-50 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-mentta-primary mb-3">Agenda Digital</h3>
                    <p class="text-gray-600">Sistema de citas integrado con recordatorios automáticos.</p>
                </div>

                <div class="feature-card bg-gray-50 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-mentta-primary/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-mentta-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-mentta-primary mb-3">Privacidad Total</h3>
                    <p class="text-gray-600">Cumplimiento total con regulaciones de confidencialidad médica.</p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="login.php?role=psychologist" class="inline-flex items-center bg-mentta-primary text-white px-8 py-4 rounded-lg hover:bg-mentta-primary/90 transition font-medium text-lg shadow-lg">
                    Acceso Portal Profesional
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-24 bg-gradient-to-br from-mentta-light to-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-mentta-primary mb-4">Lo Que Dicen Nuestros Usuarios</h2>
                <p class="text-xl text-gray-600">Historias reales de transformación y bienestar</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Mentta me ayudó a encontrar apoyo en momentos difíciles. La disponibilidad 24/7 fue crucial para mí."
                    </p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-mentta-primary/10 rounded-full flex items-center justify-center text-mentta-primary font-semibold">M</div>
                        <div class="ml-3">
                            <p class="font-semibold text-mentta-primary">María S.</p>
                            <p class="text-sm text-gray-500">Paciente</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Como psicólogo, las herramientas de Mentta me permiten brindar mejor atención y detectar situaciones de riesgo."
                    </p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-mentta-primary/10 rounded-full flex items-center justify-center text-mentta-primary font-semibold">C</div>
                        <div class="ml-3">
                            <p class="font-semibold text-mentta-primary">Dr. Carlos R.</p>
                            <p class="text-sm text-gray-500">Psicólogo Clínico</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Finalmente encontré un espacio donde puedo hablar sin miedo al juicio. La IA es increíblemente comprensiva."
                    </p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-mentta-primary/10 rounded-full flex items-center justify-center text-mentta-primary font-semibold">A</div>
                        <div class="ml-3">
                            <p class="font-semibold text-mentta-primary">Andrea L.</p>
                            <p class="text-sm text-gray-500">Paciente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-br from-mentta-primary to-mentta-primary/90">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                Comienza Tu Camino Hacia el Bienestar
            </h2>
            <p class="text-xl text-white/90 mb-10 leading-relaxed">
                Únete a miles de personas que ya están priorizando su salud mental con Mentta
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="inline-flex items-center justify-center bg-white text-mentta-primary px-8 py-4 rounded-lg hover:bg-gray-100 transition font-medium text-lg shadow-xl">
                    Registrarse Gratis
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="#como-funciona" class="inline-flex items-center justify-center border-2 border-white text-white px-8 py-4 rounded-lg hover:bg-white/10 transition font-medium text-lg">
                    Conocer Más
                </a>
            </div>
            <p class="mt-8 text-white/70 text-sm">
                Sin tarjeta de crédito • Cancelación en cualquier momento • Soporte 24/7
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div class="md:col-span-1">
                    <div class="flex items-center space-x-2 mb-4">
                        <img src="assets/img/icon-new.png" alt="Mentta" class="h-10 w-auto">
                        <span class="text-2xl font-semibold text-white">Mentta</span>
                    </div>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Plataforma profesional de salud mental con IA y psicólogos certificados.
                    </p>
                </div>

                <!-- Platform -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Plataforma</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#como-funciona" class="hover:text-white transition">Cómo Funciona</a></li>
                        <li><a href="#seguridad" class="hover:text-white transition">Seguridad</a></li>
                        <li><a href="register.php" class="hover:text-white transition">Registrarse</a></li>
                        <li><a href="login.php" class="hover:text-white transition">Iniciar Sesión</a></li>
                    </ul>
                </div>

                <!-- Professionals -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Profesionales</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#profesionales" class="hover:text-white transition">Portal Profesional</a></li>
                        <li><a href="login.php?role=psychologist" class="hover:text-white transition">Acceso</a></li>
                        <li><a href="#" class="hover:text-white transition">Documentación</a></li>
                        <li><a href="#" class="hover:text-white transition">Recursos</a></li>
                    </ul>
                </div>

                <!-- Emergency -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Emergencias</h3>
                    <div class="bg-red-900/20 border border-red-800 rounded-lg p-4">
                        <p class="text-sm text-gray-300 mb-2">
                            Si estás en crisis:
                        </p>
                        <a href="tel:113" class="text-2xl font-bold text-white hover:text-red-400 transition">
                            113
                        </a>
                        <p class="text-xs text-gray-400 mt-1">
                            Línea 113 Salud - Perú
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <p class="text-sm text-gray-400">
                    © 2026 Mentta. Todos los derechos reservados.
                </p>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="hover:text-white transition">Privacidad</a>
                    <a href="#" class="hover:text-white transition">Términos</a>
                    <a href="#" class="hover:text-white transition">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Smooth Scroll Script -->
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentta - Apoyo Emocional con IA 24/7</title>
    <meta name="description" content="Plataforma de apoyo emocional con inteligencia artificial, disponible 24/7. Un espacio seguro donde puedes hablar sobre lo que sientes.">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gradiente suave de fondo */
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Animación suave */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .fade-in-up-delay-1 {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        .fade-in-up-delay-2 {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        /* Hover effects */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* Pulse animation for CTA */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .pulse-hover:hover {
            animation: pulse 1s infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-gradient text-white min-h-screen flex items-center relative overflow-hidden">
        <!-- Decorative circles -->
        <div class="absolute top-20 left-10 w-64 h-64 bg-white opacity-5 rounded-full"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-white opacity-5 rounded-full"></div>
        
        <div class="max-w-6xl mx-auto px-6 py-20 text-center relative z-10">
            <div class="fade-in-up">
                <h1 class="text-5xl md:text-7xl font-bold mb-6">
                    Mentta
                </h1>
                <p class="text-xl md:text-2xl mb-4 opacity-90">
                    Apoyo emocional con IA, disponible 24/7
                </p>
                <p class="text-lg mb-12 opacity-80 max-w-2xl mx-auto">
                    Un espacio seguro donde puedes hablar sobre lo que sientes.
                    <br>Nunca estás solo.
                </p>
                
                <div class="flex gap-4 justify-center flex-wrap fade-in-up-delay-1">
                    <a href="login.php" class="bg-white text-indigo-600 px-8 py-4 rounded-full text-lg font-semibold hover:bg-gray-100 transition shadow-lg pulse-hover">
                        Soy Paciente
                    </a>
                    <a href="login.php?role=psychologist" class="border-2 border-white text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white hover:text-indigo-600 transition">
                        Soy Profesional
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- El Problema -->
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold mb-4 text-gray-800">El Problema</h2>
            <p class="text-gray-600 mb-12 text-lg">La salud mental es una crisis silenciosa</p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 card-hover rounded-xl">
                    <div class="text-5xl font-bold text-red-600 mb-4">3</div>
                    <p class="text-gray-700 font-medium">personas se suicidan cada día en Perú</p>
                </div>
                <div class="p-6 card-hover rounded-xl">
                    <div class="text-5xl font-bold text-red-600 mb-4">80%</div>
                    <p class="text-gray-700 font-medium">NO recibe tratamiento de salud mental</p>
                </div>
                <div class="p-6 card-hover rounded-xl">
                    <div class="text-5xl font-bold text-red-600 mb-4">24/7</div>
                    <p class="text-gray-700 font-medium">Las crisis no respetan horarios</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Funciona -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-5xl mx-auto px-6">
            <h2 class="text-4xl font-bold mb-4 text-center text-gray-800">Cómo Funciona</h2>
            <p class="text-gray-600 mb-12 text-lg text-center">Tres pasos hacia tu bienestar</p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-sm text-center card-hover">
                    <div class="text-5xl mb-4">💬</div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">1. Conversas con IA</h3>
                    <p class="text-gray-600">Un espacio seguro disponible 24/7 donde puedes expresar lo que sientes sin juicios</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm text-center card-hover">
                    <div class="text-5xl mb-4">🧠</div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">2. Análisis en Tiempo Real</h3>
                    <p class="text-gray-600">Sistema inteligente detecta patrones y riesgos para actuar a tiempo</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm text-center card-hover">
                    <div class="text-5xl mb-4">👨‍⚕️</div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800">3. Apoyo Profesional</h3>
                    <p class="text-gray-600">Psicólogos reciben alertas y datos para brindarte mejor atención</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Características -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-6">
            <h2 class="text-4xl font-bold mb-12 text-center text-gray-800">¿Por qué Mentta?</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="flex items-start gap-4 p-6">
                    <div class="text-3xl">🔒</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2 text-gray-800">100% Confidencial</h3>
                        <p class="text-gray-600">Tu información está protegida. Solo tú y tu psicólogo tienen acceso.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-6">
                    <div class="text-3xl">⚡</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2 text-gray-800">Respuesta Inmediata</h3>
                        <p class="text-gray-600">No esperes días para una cita. Habla cuando lo necesites.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-6">
                    <div class="text-3xl">🎯</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2 text-gray-800">Detección Temprana</h3>
                        <p class="text-gray-600">La IA identifica señales de alerta antes de que escalen.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-6">
                    <div class="text-3xl">💚</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-2 text-gray-800">Sin Juicios</h3>
                        <p class="text-gray-600">Un espacio libre de estigma donde puedes ser tú mismo.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center">
        <div class="max-w-3xl mx-auto px-6">
            <h2 class="text-4xl font-bold mb-6">No estás solo</h2>
            <p class="text-xl mb-8 opacity-90">
                Da el primer paso hacia tu bienestar emocional
            </p>
            <a href="register.php" class="bg-white text-indigo-600 px-10 py-4 rounded-full text-lg font-semibold hover:bg-gray-100 transition shadow-lg inline-block pulse-hover">
                Comenzar Ahora - Es Gratis
            </a>
            <p class="mt-6 text-sm opacity-75">
                Registro en 30 segundos. Sin tarjeta de crédito.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Mentta</h3>
                    <p class="text-gray-400 text-sm">
                        Apoyo emocional con IA, construido con ❤️ para salvar vidas.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Enlaces</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="login.php" class="hover:text-white transition">Iniciar Sesión</a></li>
                        <li><a href="register.php" class="hover:text-white transition">Registrarse</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Emergencias</h4>
                    <p class="text-gray-400 text-sm">
                        En caso de emergencia, llama al:<br>
                        <span class="text-white font-bold text-lg">113</span> (Línea 113 Salud - Perú)
                    </p>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-8 text-center">
                <p class="text-sm text-gray-500">
                    © 2026 Mentta. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>

import React, { useState } from 'react';
import LiveSession from './components/LiveSession';
import { Heart, Shield, Zap, Video } from 'lucide-react';
import { MentalHealthState, RiskLevel } from './types';

const App: React.FC = () => {
  const [inSession, setInSession] = useState(false);

  const startSession = () => {
    setInSession(true);
  };

  const endSession = async (sessionData?: { maxRiskLevel: number; emotions: string[]; riskEvents: any[]; alertsTriggered: number }) => {
    setInSession(false);

    // Get session token from parent window or sessionStorage
    const sessionToken = window.parent?.sessionStorage?.getItem('liveSessionToken') ||
      sessionStorage.getItem('liveSessionToken');

    if (sessionToken && sessionData) {
      try {
        // Save session data to PHP backend
        const response = await fetch('http://localhost/Mentta---Saving-lives-with-AI/api/live/save-session.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            sessionToken,
            summary: `Sesión de videollamada. Emoción predominante: ${sessionData.emotions[0] || 'Neutral'}. Nivel de riesgo máximo: ${sessionData.maxRiskLevel}.`,
            maxRiskLevel: sessionData.maxRiskLevel,
            emotions: sessionData.emotions,
            riskEvents: sessionData.riskEvents,
            alertsTriggered: sessionData.alertsTriggered
          })
        });

        const result = await response.json();
        console.log('Sesión guardada:', result);
      } catch (error) {
        console.error('Error guardando sesión:', error);
      }
    }

    console.log("Sesión terminada");

    // Notify parent window to close overlay (when embedded in iframe)
    if (window.parent !== window) {
      window.parent.postMessage({ type: 'MENTTA_LIVE_END' }, '*');
    }

    // Close window if opened as popup (fallback)
    if (window.opener) {
      window.close();
    }
  };

  if (inSession) {
    return <LiveSession onEndSession={endSession} />;
  }

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center p-4">
      <div className="max-w-md w-full">
        {/* Header Logo */}
        <div className="flex justify-center mb-8">
          <div className="bg-gradient-to-r from-indigo-600 to-purple-600 p-4 rounded-2xl shadow-xl shadow-purple-900/30">
            <span className="text-3xl font-bold text-white tracking-tighter">Mentta</span>
          </div>
        </div>

        {/* Card */}
        <div className="bg-gray-800 rounded-3xl p-8 border border-gray-700 shadow-2xl">
          <h1 className="text-2xl font-bold text-white mb-2 text-center">Habla con Mentta</h1>
          <p className="text-gray-400 text-center mb-8">
            Apoyo emocional inmediato por voz. Confidencial, empático y disponible 24/7.
          </p>

          <div className="space-y-4 mb-8">
            <div className="flex items-center gap-4 bg-gray-700/50 p-4 rounded-xl border border-gray-700">
              <div className="bg-indigo-500/20 p-2 rounded-lg text-indigo-400"><Zap size={20} /></div>
              <div>
                <h3 className="text-white font-medium">Análisis en Tiempo Real</h3>
                <p className="text-xs text-gray-400">Detecta emociones en tu voz y expresiones</p>
              </div>
            </div>
            <div className="flex items-center gap-4 bg-gray-700/50 p-4 rounded-xl border border-gray-700">
              <div className="bg-purple-500/20 p-2 rounded-lg text-purple-400"><Shield size={20} /></div>
              <div>
                <h3 className="text-white font-medium">Espacio Seguro</h3>
                <p className="text-xs text-gray-400">Conexión privada y protegida</p>
              </div>
            </div>
            <div className="flex items-center gap-4 bg-gray-700/50 p-4 rounded-xl border border-gray-700">
              <div className="bg-pink-500/20 p-2 rounded-lg text-pink-400"><Heart size={20} /></div>
              <div>
                <h3 className="text-white font-medium">IA Empática</h3>
                <p className="text-xs text-gray-400">Entrenada para escuchar y apoyar</p>
              </div>
            </div>
          </div>

          <button
            onClick={startSession}
            className="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-lg py-4 rounded-xl shadow-lg shadow-purple-900/40 transition-all transform hover:scale-[1.02] flex items-center justify-center gap-3"
          >
            <Video size={24} />
            Iniciar Llamada
          </button>

          <p className="mt-4 text-center text-xs text-gray-500">
            Si estás en peligro inmediato, llama al <strong className="text-red-400">113</strong> (Línea de Salud Mental) o <strong className="text-orange-400">106</strong> (SAMU).
          </p>
        </div>
      </div>
    </div>
  );
};

export default App;


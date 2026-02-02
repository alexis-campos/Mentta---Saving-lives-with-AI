import React, { useState } from 'react';
import LiveSession from './components/LiveSession';
import { Heart, Shield, Zap, Video } from 'lucide-react';

const App: React.FC = () => {
  const [inSession, setInSession] = useState(false);

  const startSession = () => {
    setInSession(true);
  };

  const endSession = () => {
    setInSession(false);
    console.log("Sesión terminada, guardando datos...");
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


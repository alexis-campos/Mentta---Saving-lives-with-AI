import React, { useState, useEffect } from 'react';
import LiveSession from './components/LiveSession';
import { Heart, Shield, Zap, Video, ArrowLeft } from 'lucide-react';
import { MentalHealthState, RiskLevel } from './types';

// SECURITY: Allowed origins for postMessage communication
// PRODUCTION-READY: window.location.origin adapts to whatever domain the app is deployed on
const ALLOWED_ORIGINS = [
  window.location.origin,                    // Current app origin (works in prod & dev)
  'http://localhost',                        // XAMPP local
  'http://localhost:80',                     // XAMPP explicit port
  'http://127.0.0.1',                        // Local IP
  // Add your production parent domain here if different from this app's origin:
  // 'https://mentta.com',
].filter(Boolean);

const App: React.FC = () => {
  const [inSession, setInSession] = useState(false);
  const [sessionToken, setSessionToken] = useState<string | null>(null);
  const [sessionId, setSessionId] = useState<string | null>(null);

  // Listen for session token from parent window (cross-origin communication)
  useEffect(() => {
    const handleMessage = (event: MessageEvent) => {
      // SECURITY: Validate origin
      if (!ALLOWED_ORIGINS.some(origin => event.origin.startsWith(origin))) {
        console.warn('Blocked message from untrusted origin:', event.origin);
        return;
      }

      if (event.data.type === 'MENTTA_SESSION_TOKEN') {
        console.log('Received session token from parent');
        setSessionToken(event.data.sessionToken);
        setSessionId(event.data.sessionId);
        // Also store in local sessionStorage as backup
        sessionStorage.setItem('liveSessionToken', event.data.sessionToken);
        sessionStorage.setItem('liveSessionId', event.data.sessionId);
      }
    };

    window.addEventListener('message', handleMessage);

    // Request token from parent (fallback if we loaded before parent sent it)
    if (window.parent !== window) {
      // Try to determine parent origin
      const parentOrigin = document.referrer ? new URL(document.referrer).origin : '*';
      window.parent.postMessage({ type: 'MENTTA_REQUEST_TOKEN' }, parentOrigin);
    }

    return () => window.removeEventListener('message', handleMessage);
  }, []);

  const startSession = () => {
    setInSession(true);
  };

  const endSession = async (sessionData?: { maxRiskLevel: number; emotions: string[]; riskEvents: any[]; alertsTriggered: number }) => {
    setInSession(false);

    // Get session token from local sessionStorage (received via postMessage from parent)
    const token = sessionStorage.getItem('liveSessionToken');

    // Notify parent window to close overlay IMMEDIATELY for a snappy feel
    if (window.parent !== window) {
      const parentOrigin = document.referrer ? new URL(document.referrer).origin : '*';
      window.parent.postMessage({ type: 'MENTTA_LIVE_END' }, parentOrigin);
    }

    // Close window if opened as popup (fallback)
    if (window.opener) {
      window.close();
    }

    if (token && sessionData) {
      try {
        console.log("Guardando datos de sesión con token:", token.substring(0, 10) + "...");
        const response = await fetch(`${import.meta.env.VITE_API_URL || ''}/api/live/save-session.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            sessionToken: token,
            summary: `Sesión de videollamada. Emoción predominante: ${sessionData.emotions[0] || 'Neutral'}. Nivel de riesgo máximo: ${sessionData.maxRiskLevel}.`,
            maxRiskLevel: sessionData.maxRiskLevel,
            emotions: sessionData.emotions,
            riskEvents: sessionData.riskEvents,
            alertsTriggered: sessionData.alertsTriggered
          })
        });
        const result = await response.json();
        if (result.success) {
          console.log("✅ Sesión guardada exitosamente:", result);
        } else {
          console.error("❌ Error del servidor:", result.error);
        }
      } catch (error) {
        console.error('Error guardando sesión:', error);
      }
    } else {
      console.warn("⚠️ No se pudo guardar: token=", !!token, "sessionData=", !!sessionData);
    }

    console.log("Sesión terminada");
  };

  if (inSession) {
    return <LiveSession onEndSession={endSession} />;
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-6" style={{ background: 'linear-gradient(135deg, #F9F9F7 0%, #F2F2F0 100%)' }}>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap');
        
        .font-serif {
          font-family: 'Playfair Display', serif;
        }
        
        .font-sans {
          font-family: 'Inter', sans-serif;
        }
        
        .frost-card {
          background: rgba(255, 255, 255, 0.7);
          backdrop-filter: blur(20px);
          -webkit-backdrop-filter: blur(20px);
        }
        
        .shadow-bloom {
          box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08), 0 8px 20px rgba(0, 0, 0, 0.04);
        }
        
        .shadow-bloom-intense {
          box-shadow: 0 30px 80px rgba(0, 0, 0, 0.12), 0 12px 30px rgba(0, 0, 0, 0.06);
        }
        
        .glass-reflection {
          position: relative;
          overflow: hidden;
        }
        
        .glass-reflection::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 50%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
          transition: left 0.5s;
        }
        
        .glass-reflection:hover::before {
          left: 100%;
        }
      `}</style>

      <div className="max-w-2xl w-full relative">
        {/* Back Button */}
        <div className="absolute -top-16 left-0">
          <button
            onClick={() => endSession()}
            className="flex items-center gap-3 px-6 py-3 rounded-full bg-white/50 hover:bg-white transition-all border border-black/5 shadow-sm group"
          >
            <ArrowLeft size={16} className="text-black/40 group-hover:text-black transition-colors" />
            <span className="text-[9px] font-sans font-bold uppercase tracking-[0.2em] text-black/40 group-hover:text-black transition-colors">Regresar</span>
          </button>
        </div>

        {/* Header Logo */}
        <div className="flex justify-center mb-12">
          <div className="flex items-center gap-3">
            <div>
              <h1 className="text-2xl font-bold font-serif" style={{ color: '#111', letterSpacing: '-0.02em' }}>
                Mentta
              </h1>
              <p className="text-[8px] uppercase tracking-[0.3em] font-bold opacity-40">Elite Care</p>
            </div>
          </div>
        </div>

        {/* Main Card Container */}
        <div className="bg-white rounded-[3rem] p-10 shadow-bloom-intense border border-black/5">
          {/* Title Section */}
          <div className="text-center mb-10">
            <h1 className="text-4xl font-bold mb-4 font-serif" style={{ color: '#111', letterSpacing: '-0.02em' }}>
              Habla con Mentta
            </h1>
            <p className="text-base font-sans font-light" style={{ color: '#666', letterSpacing: '0.02em' }}>
              Apoyo emocional inmediato. Privado, empático y disponible siempre.
            </p>
          </div>

          {/* Top Tier Cards - Larger with Frost Background */}
          <div className="grid grid-cols-3 gap-4 mb-6">
            <div className="frost-card rounded-[2rem] p-5 border border-black/5 shadow-bloom transition-all hover:shadow-bloom-intense">
              <div className="flex flex-col items-center text-center gap-3">
                <div className="w-12 h-12 rounded-full flex items-center justify-center" style={{ backgroundColor: 'rgba(200, 85, 61, 0.1)' }}>
                  <Heart size={20} strokeWidth={1.5} style={{ color: '#C8553D' }} />
                </div>
                <div>
                  <h3 className="font-sans font-semibold text-sm mb-1" style={{ color: '#2A2A2A', letterSpacing: '0.01em' }}>
                    IA Empática
                  </h3>
                  <p className="text-[10px] font-sans" style={{ color: '#888', letterSpacing: '0.03em' }}>
                    Escucha activa
                  </p>
                </div>
              </div>
            </div>

            <div className="frost-card rounded-[2rem] p-5 border border-black/5 shadow-bloom transition-all hover:shadow-bloom-intense">
              <div className="flex flex-col items-center text-center gap-3">
                <div className="w-12 h-12 rounded-full flex items-center justify-center" style={{ backgroundColor: 'rgba(200, 85, 61, 0.1)' }}>
                  <Shield size={20} strokeWidth={1.5} style={{ color: '#C8553D' }} />
                </div>
                <div>
                  <h3 className="font-sans font-semibold text-sm mb-1" style={{ color: '#2A2A2A', letterSpacing: '0.01em' }}>
                    Privacidad
                  </h3>
                  <p className="text-[10px] font-sans" style={{ color: '#888', letterSpacing: '0.03em' }}>
                    100% Seguro
                  </p>
                </div>
              </div>
            </div>

            <div className="frost-card rounded-[2rem] p-5 border border-black/5 shadow-bloom transition-all hover:shadow-bloom-intense">
              <div className="flex flex-col items-center text-center gap-3">
                <div className="w-12 h-12 rounded-full flex items-center justify-center" style={{ backgroundColor: 'rgba(200, 85, 61, 0.1)' }}>
                  <Zap size={20} strokeWidth={1.5} style={{ color: '#C8553D' }} />
                </div>
                <div>
                  <h3 className="font-sans font-semibold text-sm mb-1" style={{ color: '#2A2A2A', letterSpacing: '0.01em' }}>
                    Análisis
                  </h3>
                  <p className="text-[10px] font-sans" style={{ color: '#888', letterSpacing: '0.03em' }}>
                    Tiempo real
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Call to Action Button */}
          <button
            onClick={startSession}
            className="w-full py-5 rounded-[2rem] text-white font-sans font-bold text-sm uppercase tracking-[0.15em] transition-all transform hover:scale-[1.02] glass-reflection relative"
            style={{
              backgroundColor: '#C8553D',
              boxShadow: '0 20px 50px rgba(200, 85, 61, 0.25), 0 10px 25px rgba(200, 85, 61, 0.15)',
            }}
          >
            <div className="flex items-center justify-center gap-3 relative z-10">
              <Video size={20} strokeWidth={2.5} />
              <span>Iniciar Llamada</span>
            </div>
          </button>

          {/* Footer disclaimer */}
          <div className="mt-8 text-center">
            <div className="flex justify-center gap-3 mb-3">
              <span className="text-[9px] font-sans font-bold uppercase tracking-[0.3em]" style={{ color: 'rgba(0,0,0,0.2)' }}>Secure</span>
              <span className="text-[9px] font-sans font-bold uppercase tracking-[0.3em]" style={{ color: 'rgba(0,0,0,0.2)' }}>•</span>
              <span className="text-[9px] font-sans font-bold uppercase tracking-[0.3em]" style={{ color: 'rgba(0,0,0,0.2)' }}>Private</span>
            </div>
            <p className="text-[10px] font-sans font-light" style={{ color: '#999' }}>
              En caso de emergencia, contacta la línea <strong style={{ color: '#C8553D' }}>113</strong>
            </p>
          </div>
        </div>
      </div>
    </div >
  );
};

export default App;


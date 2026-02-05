import React, { useEffect, useRef, useState, useCallback } from 'react';
import { GoogleGenAI, LiveServerMessage, Modality, FunctionDeclaration, Type } from "@google/genai";
import { RiskLevel, ConnectionStatus, MentalHealthState } from '../types';
import { base64ToUint8Array, createPcmBlob } from '../utils/audioUtils';
import RiskIndicator from './RiskIndicator';
import StatusIndicator, { AIStatus } from './StatusIndicator';
import Avatar3D from './Avatar3D';
import { ArrowLeft, Mic, MicOff, Video, VideoOff, PhoneOff } from 'lucide-react';

// --- Configuration ---
const MODEL_NAME = 'gemini-2.5-flash-native-audio-preview-12-2025';
const SAMPLE_RATE_INPUT = 16000;
const SAMPLE_RATE_OUTPUT = 24000;
const FRAME_RATE = 1;

// --- Tool Definitions (Risk Detection) ---
const reportRiskTool: FunctionDeclaration = {
  name: 'reportMentalHealthStatus',
  description: 'Call this function when you detect a change in the user\'s risk level based on their speech or appearance.',
  parameters: {
    type: Type.OBJECT,
    properties: {
      riskLevel: {
        type: Type.NUMBER,
        description: '0 for low/stable, 1 for moderate, 2 for high, 3 for critical/immediate danger.',
      },
      emotion: {
        type: Type.STRING,
        description: 'The primary emotion detected (e.g., anxiety, sadness, calm).',
      },
      reason: {
        type: Type.STRING,
        description: 'Brief reason for the assessment.',
      }
    },
    required: ['riskLevel', 'emotion']
  }
};

interface LiveSessionProps {
  onEndSession: (sessionData?: { maxRiskLevel: number; emotions: string[]; riskEvents: any[]; alertsTriggered: number }) => void;
}

const LiveSession: React.FC<LiveSessionProps> = ({ onEndSession }) => {
  // State
  const [status, setStatus] = useState<ConnectionStatus>('disconnected');
  const [isMicOn, setIsMicOn] = useState(true);
  const [isVideoOn, setIsVideoOn] = useState(true);
  const [volume, setVolume] = useState(0);
  const [aiState, setAiState] = useState<MentalHealthState>({
    riskLevel: RiskLevel.LOW,
    primaryEmotion: 'Calm',
    notes: []
  });
  const [emotionsHistory, setEmotionsHistory] = useState<string[]>([]);
  const [riskEvents, setRiskEvents] = useState<any[]>([]);
  const [alertsCount, setAlertsCount] = useState(0);

  // Refs for state machine timing
  const speakingTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const lastAudioChunkTimeRef = useRef<number>(0);
  const isPlayingAudioRef = useRef<boolean>(false);
  const [aiStatus, setAiStatus] = useState<AIStatus>('idle');

  // Refs for Media and Processing
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(document.createElement('canvas'));
  const streamRef = useRef<MediaStream | null>(null);

  // Refs for Audio Contexts
  const inputContextRef = useRef<AudioContext | null>(null);
  const outputContextRef = useRef<AudioContext | null>(null);
  const scriptProcessorRef = useRef<ScriptProcessorNode | null>(null);
  const audioQueueRef = useRef<AudioBufferSourceNode[]>([]);
  const nextStartTimeRef = useRef<number>(0);

  // Refs for Gemini Session
  const sessionPromiseRef = useRef<Promise<any> | null>(null);
  const videoIntervalRef = useRef<number | null>(null);

  // --- Audio Output Handling ---
  const playAudioChunk = useCallback(async (base64Audio: string) => {
    if (!outputContextRef.current) return;

    try {
      const audioData = base64ToUint8Array(base64Audio);
      const float32Data = new Int16Array(audioData.buffer);
      const audioBuffer = outputContextRef.current.createBuffer(1, float32Data.length, SAMPLE_RATE_OUTPUT);
      const channelData = audioBuffer.getChannelData(0);

      for (let i = 0; i < float32Data.length; i++) {
        channelData[i] = float32Data[i] / 32768.0;
      }

      const source = outputContextRef.current.createBufferSource();
      source.buffer = audioBuffer;
      source.connect(outputContextRef.current.destination);

      const currentTime = outputContextRef.current.currentTime;
      const startTime = Math.max(currentTime, nextStartTimeRef.current);
      source.start(startTime);
      nextStartTimeRef.current = startTime + audioBuffer.duration;

      audioQueueRef.current.push(source);

      source.onended = () => {
        const index = audioQueueRef.current.indexOf(source);
        if (index > -1) audioQueueRef.current.splice(index, 1);
      };

    } catch (e) {
      console.error("Error decoding audio", e);
    }
  }, []);

  // --- Connect to Gemini ---
  const connect = useCallback(async () => {
    setStatus('connecting');
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        audio: {
          sampleRate: SAMPLE_RATE_INPUT,
          channelCount: 1,
          echoCancellation: true,
          noiseSuppression: true
        },
        video: { width: 640, height: 480 }
      });
      streamRef.current = stream;
      if (videoRef.current) {
        videoRef.current.srcObject = stream;
      }

      inputContextRef.current = new AudioContext({ sampleRate: SAMPLE_RATE_INPUT });
      outputContextRef.current = new AudioContext({ sampleRate: SAMPLE_RATE_OUTPUT });

      const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

      sessionPromiseRef.current = ai.live.connect({
        model: MODEL_NAME,
        config: {
          responseModalities: [Modality.AUDIO],
          systemInstruction: {
            parts: [{
              text: `Eres Mentta, un AMIGO cercano especializado en apoyo emocional de emergencia en Perú. NO eres terapeuta formal, eres un compañero empático que sabe escuchar y ayudar.

PERSONALIDAD Y TONO:
- Hablas español peruano con tono cálido, pausado y genuino
- Usas "tú" (informal), eres cercano pero respetuoso
- Validas emociones sin juzgar ni minimizar
- NO uses tecnicismos psicológicos
- Respuestas CORTAS: máximo 3-4 oraciones
- SIEMPRE termina con pregunta abierta

CÓMO HABLA UN AMIGO (vs cómo NO hablar):
❌ "Entiendo tu dolor" (suena falso) → ✅ "Eso suena muy difícil. ¿Qué pasó?"
❌ "Es válido sentirse así" (repetitivo) → ✅ "Tiene sentido que te sientas así"
❌ "Todo estará bien" (promesa vacía) → ✅ "Estoy aquí contigo en esto"

PROTOCOLO DE DETECCIÓN DE RIESGO (CRÍTICO):
USA reportMentalHealthStatus INMEDIATAMENTE cuando detectes:

riskLevel=3 (CRÍTICO - Peligro inmediato):
- Mención de suicidio: "matarme", "acabar con todo", "no quiero vivir", "sería mejor si no existiera"
- Plan concreto de autolesión
- Despedida: "cuida a mi familia", "esto es un adiós"
→ Responde: "Me preocupa mucho lo que dices. Tu vida importa y hay personas que pueden ayudarte AHORA. ¿Puedes llamar al 113? Es la Línea de Salud Mental, disponible 24/7."

riskLevel=2 (ALTO - Necesita atención):
- Llanto intenso + desesperanza verbal
- "Ya no puedo más" + contexto de pérdida reciente
- Autolesiones pasadas mencionadas
→ Responde: "Lo que sientes es muy intenso. Creo que sería bueno que hables con un profesional. ¿Conoces la Línea 113?"

riskLevel=1 (MODERADO - Monitorear):
- Ansiedad intensa pero sin peligro
- Tristeza profunda sin ideación suicida
- Problemas de sueño, aislamiento
→ Continúa escuchando activamente

riskLevel=0 (ESTABLE):
- Conversación normal
- Busca consejo o desahogo
→ Sé un buen amigo, escucha

ANÁLISIS MULTIMODAL:
Si ves video del usuario, observa:
- Lágrimas, ojos rojos → aumenta nivel de preocupación
- Postura encogida, cabeza baja → posible depresión
- Ambiente oscuro → posible aislamiento
Combina lo que VES con lo que ESCUCHAS.

RECURSOS DE PERÚ (menciona solo cuando apropiado):
- Línea 113 opción 5: Salud Mental (24/7, gratis)
- SAMU 106: Emergencias médicas
- Mapa de centros en la app Mentta

NUNCA:
- Preguntes múltiples cosas a la vez
- Diagnostiques: "tienes depresión" ❌
- Recetes medicamentos
- Prometas cosas que no puedes cumplir
- Te despidas sin plan de seguimiento` }]
          },
          tools: [{ functionDeclarations: [reportRiskTool] }]
        },
        callbacks: {
          onopen: () => {
            setStatus('connected');
            if (!inputContextRef.current || !streamRef.current) return;

            const source = inputContextRef.current.createMediaStreamSource(streamRef.current);
            const processor = inputContextRef.current.createScriptProcessor(4096, 1, 1);
            scriptProcessorRef.current = processor;

            processor.onaudioprocess = (e) => {
              if (!isMicOn) return;
              const inputData = e.inputBuffer.getChannelData(0);

              let sum = 0;
              for (let i = 0; i < inputData.length; i++) sum += inputData[i] * inputData[i];
              const currentVolume = Math.sqrt(sum / inputData.length) * 100;
              setVolume(currentVolume);

              if (currentVolume > 5 && !isPlayingAudioRef.current) {
                setAiStatus('listening');
              } else if (currentVolume <= 5 && !isPlayingAudioRef.current) {
                setAiStatus('idle');
              }

              const blob = createPcmBlob(inputData);
              sessionPromiseRef.current?.then(session => {
                session.sendRealtimeInput({ media: blob });
              });
            };

            source.connect(processor);
            processor.connect(inputContextRef.current.destination);
          },
          onmessage: async (msg: LiveServerMessage) => {
            const audioData = msg.serverContent?.modelTurn?.parts?.[0]?.inlineData?.data;
            if (audioData) {
              isPlayingAudioRef.current = true;
              lastAudioChunkTimeRef.current = Date.now();
              setAiStatus('speaking');

              if (speakingTimeoutRef.current) {
                clearTimeout(speakingTimeoutRef.current);
              }

              await playAudioChunk(audioData);

              speakingTimeoutRef.current = setTimeout(() => {
                const timeSinceLastChunk = Date.now() - lastAudioChunkTimeRef.current;
                if (timeSinceLastChunk >= 1400) {
                  isPlayingAudioRef.current = false;
                  setAiStatus('idle');
                }
              }, 1500);
            }

            if (msg.serverContent?.turnComplete) {
              setTimeout(() => {
                isPlayingAudioRef.current = false;
                setAiStatus('idle');
              }, 800);
            }

            if (msg.serverContent && !audioData && !msg.serverContent.turnComplete) {
              if (!isPlayingAudioRef.current) {
                setAiStatus('processing');
              }
            }

            if (msg.serverContent?.interrupted) {
              audioQueueRef.current.forEach(source => source.stop());
              audioQueueRef.current = [];
              if (outputContextRef.current) nextStartTimeRef.current = outputContextRef.current.currentTime;
            }

            if (msg.toolCall) {
              for (const call of msg.toolCall.functionCalls) {
                if (call.name === 'reportMentalHealthStatus') {
                  const { riskLevel, emotion, reason } = call.args as any;

                  setAiState(prev => ({
                    riskLevel: riskLevel as RiskLevel,
                    primaryEmotion: emotion as string,
                    notes: [...prev.notes, reason as string]
                  }));

                  setEmotionsHistory(prev => [...prev, emotion as string]);

                  setRiskEvents(prev => [...prev, {
                    timestamp: new Date().toISOString(),
                    riskLevel,
                    emotion,
                    reason
                  }]);

                  if (riskLevel >= 2) {
                    const sessionToken = window.opener?.sessionStorage?.getItem('liveSessionToken') ||
                      sessionStorage.getItem('liveSessionToken');
                    if (sessionToken) {
                      fetch('http://localhost/Mentta---Saving-lives-with-AI/api/live/trigger-alert.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ sessionToken, riskLevel, emotion, reason })
                      }).then(() => {
                        setAlertsCount(prev => prev + 1);
                      }).catch(console.error);
                    }
                  }

                  sessionPromiseRef.current?.then(session => {
                    session.sendToolResponse({
                      functionResponses: {
                        id: call.id,
                        name: call.name,
                        response: { result: "Risk level recorded." }
                      }
                    });
                  });
                }
              }
            }
          },
          onclose: () => {
            setStatus('disconnected');
          },
          onerror: (e) => {
            console.error(e);
            setStatus('error');
          }
        }
      });

    } catch (err) {
      console.error("Connection failed", err);
      setStatus('error');
    }
  }, [isMicOn, playAudioChunk]);

  // --- Video Streaming Loop ---
  useEffect(() => {
    if (status !== 'connected' || !isVideoOn) {
      if (videoIntervalRef.current) window.clearInterval(videoIntervalRef.current);
      return;
    }

    videoIntervalRef.current = window.setInterval(() => {
      if (!videoRef.current || !canvasRef.current) return;

      const video = videoRef.current;
      const canvas = canvasRef.current;
      const ctx = canvas.getContext('2d');

      if (video.readyState === video.HAVE_ENOUGH_DATA && ctx) {
        canvas.width = video.videoWidth / 2;
        canvas.height = video.videoHeight / 2;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const base64 = canvas.toDataURL('image/jpeg', 0.5).split(',')[1];

        sessionPromiseRef.current?.then(session => {
          session.sendRealtimeInput({
            media: {
              mimeType: 'image/jpeg',
              data: base64
            }
          });
        });
      }
    }, 1000 / FRAME_RATE);

    return () => {
      if (videoIntervalRef.current) window.clearInterval(videoIntervalRef.current);
    };
  }, [status, isVideoOn]);

  // --- Cleanup ---
  useEffect(() => {
    connect();

    return () => {
      streamRef.current?.getTracks().forEach(t => t.stop());
      inputContextRef.current?.close();
      outputContextRef.current?.close();
      if (videoIntervalRef.current) window.clearInterval(videoIntervalRef.current);
      if (speakingTimeoutRef.current) clearTimeout(speakingTimeoutRef.current);
    };
  }, [connect]);


  // --- UI ---
  return (
    <div className="fixed inset-0 z-50 flex flex-col" style={{ background: '#FCFCFA' }}>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap');
        
        .font-serif { font-family: 'Playfair Display', serif; }
        .font-sans { font-family: 'Inter', sans-serif; }
        
        .card-elevated {
          background: #FFFFFF;
          border-radius: 32px;
          box-shadow: 0 40px 120px rgba(0, 0, 0, 0.03), 0 20px 60px rgba(0, 0, 0, 0.02);
        }
        
        .btn-clean {
          background: #FFFFFF;
          border-radius: 50%;
          box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
          border: 1px solid rgba(0, 0, 0, 0.03);
          transition: all 0.3s ease;
        }
        
        .btn-clean:hover {
          box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
          transform: translateY(-2px);
        }
      `}</style>

      {/* Header - Session Status */}
      <div className="absolute top-8 left-1/2 -translate-x-1/2 z-20">
        <div
          className="px-8 py-3 rounded-full font-serif text-sm font-medium tracking-wide"
          style={{
            background: 'rgba(255, 255, 255, 0.95)',
            boxShadow: '0 10px 40px rgba(0, 0, 0, 0.04)',
            border: '1px solid rgba(0, 0, 0, 0.03)',
            color: '#2A2A2A'
          }}
        >
          Sesión Encriptada
        </div>
      </div>

      {/* Back Button */}
      <div className="absolute top-8 left-8 z-20">
        <button
          onClick={() => onEndSession({
            maxRiskLevel: aiState.riskLevel,
            emotions: emotionsHistory,
            riskEvents: riskEvents,
            alertsTriggered: alertsCount
          })}
          className="btn-clean px-6 h-12 flex items-center justify-center gap-3 transition-all hover:pr-8"
          style={{
            color: '#2A2A2A',
            borderRadius: '24px',
            width: 'auto'
          }}
        >
          <ArrowLeft size={20} strokeWidth={1.5} />
          <span className="font-sans text-[10px] font-bold tracking-[0.2em] uppercase">Regresar</span>
        </button>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col md:flex-row relative overflow-hidden p-10 gap-8">

        {/* LEFT: User Camera Area (Now on Left/Secondary spot) */}
        <div className="flex-1 flex flex-col gap-6">
          <div className="flex-[2] relative card-elevated overflow-hidden">
            <video
              ref={videoRef}
              autoPlay
              playsInline
              muted
              className={`w-full h-full object-cover ${isVideoOn ? 'opacity-100' : 'opacity-0'}`}
              style={{ borderRadius: '32px' }}
            />
            {!isVideoOn && (
              <div className="absolute inset-0 flex items-center justify-center" style={{ background: '#FAFAFA' }}>
                <div className="text-center">
                  <VideoOff size={40} className="mx-auto mb-3" style={{ color: '#CCC' }} strokeWidth={1.5} />
                  <span className="text-xs font-sans" style={{ color: '#999', letterSpacing: '0.1em' }}>CÁMARA APAGADA</span>
                </div>
              </div>
            )}

            {/* Mute Indicator overlay */}
            {!isMicOn && (
              <div className="absolute inset-0 bg-red-500/5 backdrop-blur-[2px] flex items-center justify-center z-10 pointer-events-none transition-all duration-300">
                <div className="bg-white/90 px-8 py-5 rounded-[2rem] shadow-2xl border border-red-100 flex flex-col items-center gap-3 transform animate-in fade-in zoom-in duration-300">
                  <div className="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">
                    <MicOff size={24} className="text-[#C8553D]" strokeWidth={1.5} />
                  </div>
                  <span className="text-[10px] font-sans font-bold text-[#C8553D] tracking-[0.25em] uppercase">Micrófono Apagado</span>
                </div>
              </div>
            )}

            {/* Label */}
            <div className="absolute top-5 left-5 z-20">
              <span
                className="px-4 py-2 rounded-full text-[10px] font-sans font-medium uppercase"
                style={{
                  background: 'rgba(255, 255, 255, 0.95)',
                  color: '#666',
                  letterSpacing: '0.15em',
                  boxShadow: '0 4px 20px rgba(0, 0, 0, 0.04)'
                }}
              >
                Tú
              </span>
            </div>
          </div>

          {/* Analysis Panel */}
          <div className="flex-1 card-elevated p-6">
            <h4
              className="text-[9px] font-sans font-semibold uppercase mb-4"
              style={{ color: '#BBB', letterSpacing: '0.2em' }}
            >
              Análisis IA
            </h4>
            <div className="font-sans">
              <RiskIndicator level={aiState.riskLevel} emotion={aiState.primaryEmotion} />
            </div>
          </div>
        </div>

        {/* RIGHT: Avatar Area (Now Main Focus) */}
        <div className="flex-[2] relative card-elevated overflow-hidden">
          <Avatar3D aiStatus={aiStatus} className="w-full h-full" />

          {/* Label for Mentta */}
          <div className="absolute top-5 left-5 z-20">
            <span
              className="px-4 py-2 rounded-full text-[10px] font-sans font-medium uppercase"
              style={{
                background: 'rgba(255, 255, 255, 0.95)',
                color: '#2d3a2d',
                letterSpacing: '0.15em',
                boxShadow: '0 4px 20px rgba(0, 0, 0, 0.04)'
              }}
            >
              Mentta AI
            </span>
          </div>
        </div>

      </div>

      {/* Controls - Perfectly Centered */}
      <div className="py-8 flex flex-col items-center gap-6">
        <div className="flex justify-center items-center gap-5">
          <button
            onClick={() => setIsMicOn(!isMicOn)}
            className="btn-clean w-14 h-14 flex items-center justify-center"
            style={{ color: isMicOn ? '#2A2A2A' : '#C8553D' }}
          >
            {isMicOn ? <Mic size={22} strokeWidth={1.5} /> : <MicOff size={22} strokeWidth={1.5} />}
          </button>

          <button
            onClick={() => onEndSession({
              maxRiskLevel: aiState.riskLevel,
              emotions: emotionsHistory,
              riskEvents: riskEvents,
              alertsTriggered: alertsCount
            })}
            className="px-10 py-4 rounded-full font-sans font-medium text-xs flex items-center gap-3 transition-all hover:scale-105"
            style={{
              background: 'rgba(200, 85, 61, 0.08)',
              border: '1px solid rgba(200, 85, 61, 0.15)',
              color: '#C8553D',
              letterSpacing: '0.1em'
            }}
          >
            <PhoneOff size={18} strokeWidth={1.5} />
            <span>TERMINAR</span>
          </button>

          <button
            onClick={() => setIsVideoOn(!isVideoOn)}
            className="btn-clean w-14 h-14 flex items-center justify-center"
            style={{ color: isVideoOn ? '#2A2A2A' : '#C8553D' }}
          >
            {isVideoOn ? <Video size={22} strokeWidth={1.5} /> : <VideoOff size={22} strokeWidth={1.5} />}
          </button>
        </div>

        {/* Footer */}
        <p
          className="text-[9px] font-sans font-medium uppercase text-center"
          style={{ color: 'rgba(0, 0, 0, 0.15)', letterSpacing: '0.4em' }}
        >
          Espacio Privado &nbsp;•&nbsp; Encriptado &nbsp;•&nbsp; Seguro
        </p>
      </div>
    </div>
  );
};

export default LiveSession;

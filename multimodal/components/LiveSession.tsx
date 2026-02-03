import React, { useEffect, useRef, useState, useCallback } from 'react';
import { GoogleGenAI, LiveServerMessage, Modality, FunctionDeclaration, Type } from "@google/genai";
import { RiskLevel, ConnectionStatus, MentalHealthState } from '../types';
import { base64ToUint8Array, createPcmBlob } from '../utils/audioUtils';
import RiskIndicator from './RiskIndicator';
import StatusIndicator, { AIStatus } from './StatusIndicator';
import Avatar3D from './Avatar3D';
import { ArrowLeft, Mic, MicOff, Video, VideoOff, PhoneOff, AlertTriangle } from 'lucide-react';

// --- Configuration ---
const MODEL_NAME = 'gemini-2.5-flash-native-audio-preview-12-2025';
const SAMPLE_RATE_INPUT = 16000;
const SAMPLE_RATE_OUTPUT = 24000;
const FRAME_RATE = 1; // Frames per second for video to save bandwidth/tokens

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
      // Create a buffer
      const float32Data = new Int16Array(audioData.buffer);
      const audioBuffer = outputContextRef.current.createBuffer(1, float32Data.length, SAMPLE_RATE_OUTPUT);
      const channelData = audioBuffer.getChannelData(0);

      // Convert Int16 back to Float32
      for (let i = 0; i < float32Data.length; i++) {
        channelData[i] = float32Data[i] / 32768.0;
      }

      const source = outputContextRef.current.createBufferSource();
      source.buffer = audioBuffer;
      source.connect(outputContextRef.current.destination);

      const currentTime = outputContextRef.current.currentTime;
      // Schedule next chunk
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
      // 1. Get Media Stream
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

      // 2. Setup Audio Contexts
      inputContextRef.current = new AudioContext({ sampleRate: SAMPLE_RATE_INPUT });
      outputContextRef.current = new AudioContext({ sampleRate: SAMPLE_RATE_OUTPUT });

      // 3. Initialize Gemini
      const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

      // 4. Establish Connection
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
            // Start Audio Streaming
            if (!inputContextRef.current || !streamRef.current) return;

            const source = inputContextRef.current.createMediaStreamSource(streamRef.current);
            // Using ScriptProcessor for simplicity in this context, though AudioWorklet is better for prod
            const processor = inputContextRef.current.createScriptProcessor(4096, 1, 1);
            scriptProcessorRef.current = processor;

            processor.onaudioprocess = (e) => {
              if (!isMicOn) return;
              const inputData = e.inputBuffer.getChannelData(0);

              // Simple volume meter - also detect if user is speaking
              let sum = 0;
              for (let i = 0; i < inputData.length; i++) sum += inputData[i] * inputData[i];
              const currentVolume = Math.sqrt(sum / inputData.length) * 100;
              setVolume(currentVolume);

              // Only set to listening if NOT speaking and volume is high
              if (currentVolume > 5 && !isPlayingAudioRef.current) {
                setAiStatus('listening');
              } else if (currentVolume <= 5 && !isPlayingAudioRef.current) {
                // Low volume and not speaking - go idle after a moment
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
            // Handle Audio Output - IA is speaking
            const audioData = msg.serverContent?.modelTurn?.parts?.[0]?.inlineData?.data;
            if (audioData) {
              // Mark as speaking and track audio activity
              isPlayingAudioRef.current = true;
              lastAudioChunkTimeRef.current = Date.now();
              setAiStatus('speaking');

              // Clear any existing timeout
              if (speakingTimeoutRef.current) {
                clearTimeout(speakingTimeoutRef.current);
              }

              await playAudioChunk(audioData);

              // Set timeout to detect when audio stream ends
              // Only go idle if no new audio chunks arrive for 1.5 seconds
              speakingTimeoutRef.current = setTimeout(() => {
                const timeSinceLastChunk = Date.now() - lastAudioChunkTimeRef.current;
                if (timeSinceLastChunk >= 1400) {
                  isPlayingAudioRef.current = false;
                  setAiStatus('idle');
                }
              }, 1500);
            }

            // If model turn is complete, mark as done speaking after a short delay
            if (msg.serverContent?.turnComplete) {
              setTimeout(() => {
                isPlayingAudioRef.current = false;
                setAiStatus('idle');
              }, 800);
            }

            // If model is thinking (has serverContent but no audio yet), show processing
            if (msg.serverContent && !audioData && !msg.serverContent.turnComplete) {
              if (!isPlayingAudioRef.current) {
                setAiStatus('processing');
              }
            }

            // Handle Interruption
            if (msg.serverContent?.interrupted) {
              audioQueueRef.current.forEach(source => source.stop());
              audioQueueRef.current = [];
              if (outputContextRef.current) nextStartTimeRef.current = outputContextRef.current.currentTime;
            }

            // Handle Tool Calls (Risk Detection)
            if (msg.toolCall) {
              for (const call of msg.toolCall.functionCalls) {
                if (call.name === 'reportMentalHealthStatus') {
                  const { riskLevel, emotion, reason } = call.args as any;

                  // Update AI state
                  setAiState(prev => ({
                    riskLevel: riskLevel as RiskLevel,
                    primaryEmotion: emotion as string,
                    notes: [...prev.notes, reason as string]
                  }));

                  // Track emotions history
                  setEmotionsHistory(prev => [...prev, emotion as string]);

                  // Track risk events
                  setRiskEvents(prev => [...prev, {
                    timestamp: new Date().toISOString(),
                    riskLevel,
                    emotion,
                    reason
                  }]);

                  // If high risk, send alert to backend
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

                  // Respond to the tool call
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
  }, [isMicOn]);

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
        canvas.width = video.videoWidth / 2; // Downscale for bandwidth
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
    // Initial connect
    connect();

    return () => {
      // Deep cleanup
      streamRef.current?.getTracks().forEach(t => t.stop());
      inputContextRef.current?.close();
      outputContextRef.current?.close();
      if (videoIntervalRef.current) window.clearInterval(videoIntervalRef.current);
      if (speakingTimeoutRef.current) clearTimeout(speakingTimeoutRef.current);
      // We can't strictly "close" the Gemini session object directly via SDK yet in a clean way without keeping reference to close(), 
      // but breaking the stream connection handles it server-side eventually.
    };
  }, [connect]);


  // --- UI ---
  return (
    <div className="fixed inset-0 bg-slate-900 z-50 flex flex-col">
      {/* Header - minimal when embedded in iframe */}
      <div className="absolute top-16 left-4 z-10">
        <button onClick={() => onEndSession({
          maxRiskLevel: aiState.riskLevel,
          emotions: emotionsHistory,
          riskEvents: riskEvents,
          alertsTriggered: alertsCount
        })} className="flex items-center gap-2 text-slate-300 hover:text-white transition bg-slate-800/70 hover:bg-slate-700/90 backdrop-blur-sm px-4 py-2.5 rounded-xl shadow-lg border border-slate-700/50">
          <ArrowLeft size={18} />
          <span className="text-sm font-medium">Terminar llamada</span>
        </button>
      </div>

      {/* Main Content - Split Layout */}
      <div className="flex-1 flex flex-col md:flex-row relative overflow-hidden">

        {/* LEFT: Avatar 3D (70%) */}
        <div className="flex-[2] relative bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900">
          <Avatar3D aiStatus={aiStatus} className="w-full h-full" />

          {/* Status Indicator - centered at bottom */}
          <div className="absolute bottom-6 left-1/2 -translate-x-1/2 z-10">
            {status === 'connected' ? (
              <StatusIndicator status={aiStatus} volume={volume} />
            ) : (
              <div className="bg-yellow-500/20 text-yellow-200 px-4 py-2 rounded-lg backdrop-blur border border-yellow-500/30">
                Conectando línea segura...
              </div>
            )}
          </div>
        </div>

        {/* RIGHT: User Camera (30%) */}
        <div className="flex-1 relative bg-black flex flex-col">
          {/* Camera feed */}
          <div className={`flex-1 relative transition-all duration-300 ${aiStatus === 'listening' ? 'ring-4 ring-green-500/50 ring-inset' : ''}`}>
            <video
              ref={videoRef}
              autoPlay
              playsInline
              muted
              className={`w-full h-full object-cover ${isVideoOn ? 'opacity-100' : 'opacity-0'}`}
            />
            {!isVideoOn && (
              <div className="absolute inset-0 flex items-center justify-center text-slate-600 bg-slate-900">
                <div className="text-center">
                  <VideoOff size={48} className="mx-auto mb-2 opacity-50" />
                  <span className="text-sm">Cámara Apagada</span>
                </div>
              </div>
            )}

            {/* Your label */}
            <div className="absolute top-3 left-3">
              <span className="bg-slate-900/80 px-3 py-1 rounded-full text-xs text-slate-300 backdrop-blur">
                Tú {aiStatus === 'listening' && '🎙️'}
              </span>
            </div>
          </div>

          {/* Risk Indicator - below camera */}
          <div className="p-3 bg-slate-900/90 border-t border-slate-800">
            <RiskIndicator level={aiState.riskLevel} emotion={aiState.primaryEmotion} />
          </div>
        </div>
      </div>

      {/* Controls */}
      <div className="p-6 bg-slate-800 border-t border-slate-700 flex justify-center items-center gap-6">
        <button
          onClick={() => setIsMicOn(!isMicOn)}
          className={`p-4 rounded-full transition-all ${isMicOn ? 'bg-slate-700 hover:bg-slate-600 text-white' : 'bg-red-500/20 text-red-400 border border-red-500/50'}`}
        >
          {isMicOn ? <Mic size={24} /> : <MicOff size={24} />}
        </button>

        <button
          onClick={() => onEndSession({
            maxRiskLevel: aiState.riskLevel,
            emotions: emotionsHistory,
            riskEvents: riskEvents,
            alertsTriggered: alertsCount
          })}
          className="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-full font-bold shadow-lg shadow-red-900/20 flex items-center gap-2 transition-all transform hover:scale-105"
        >
          <PhoneOff size={24} />
          <span>Terminar Llamada</span>
        </button>

        <button
          onClick={() => setIsVideoOn(!isVideoOn)}
          className={`p-4 rounded-full transition-all ${isVideoOn ? 'bg-slate-700 hover:bg-slate-600 text-white' : 'bg-red-500/20 text-red-400 border border-red-500/50'}`}
        >
          {isVideoOn ? <Video size={24} /> : <VideoOff size={24} />}
        </button>
      </div>
    </div>
  );
};

export default LiveSession;

import React, { useEffect, useRef, useState, useCallback } from 'react';
import { GoogleGenAI, LiveServerMessage, Modality, FunctionDeclaration, Type } from "@google/genai";
import { RiskLevel, ConnectionStatus, MentalHealthState } from '../types';
import { base64ToUint8Array, createPcmBlob } from '../utils/audioUtils';
import RiskIndicator from './RiskIndicator';
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
  onEndSession: () => void;
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

              // Simple volume meter
              let sum = 0;
              for (let i = 0; i < inputData.length; i++) sum += inputData[i] * inputData[i];
              setVolume(Math.sqrt(sum / inputData.length) * 100);

              const blob = createPcmBlob(inputData);
              sessionPromiseRef.current?.then(session => {
                session.sendRealtimeInput({ media: blob });
              });
            };

            source.connect(processor);
            processor.connect(inputContextRef.current.destination);
          },
          onmessage: async (msg: LiveServerMessage) => {
            // Handle Audio Output
            const audioData = msg.serverContent?.modelTurn?.parts?.[0]?.inlineData?.data;
            if (audioData) {
              await playAudioChunk(audioData);
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
                  setAiState(prev => ({
                    riskLevel: riskLevel as RiskLevel,
                    primaryEmotion: emotion as string,
                    notes: [...prev.notes, reason as string]
                  }));

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
      // We can't strictly "close" the Gemini session object directly via SDK yet in a clean way without keeping reference to close(), 
      // but breaking the stream connection handles it server-side eventually.
    };
  }, [connect]);


  // --- UI ---
  return (
    <div className="fixed inset-0 bg-slate-900 z-50 flex flex-col">
      {/* Header */}
      <div className="p-4 bg-slate-900/50 backdrop-blur border-b border-slate-700 flex justify-between items-center">
        <div className="flex items-center gap-2">
          <div className="bg-blue-600 p-2 rounded-lg">
            <span className="font-bold text-white text-xl tracking-tight">Mentta</span>
          </div>
          <span className="text-gray-400 text-sm">Sesión Segura en Vivo</span>
        </div>
        <button onClick={onEndSession} className="text-slate-400 hover:text-white transition">
          <ArrowLeft size={24} />
        </button>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col md:flex-row relative overflow-hidden">

        {/* Video Area */}
        <div className="flex-1 relative bg-black flex items-center justify-center">
          <video
            ref={videoRef}
            autoPlay
            playsInline
            muted
            className={`w-full h-full object-cover ${isVideoOn ? 'opacity-100' : 'opacity-0'}`}
          />
          {!isVideoOn && (
            <div className="absolute inset-0 flex items-center justify-center text-slate-600">
              <span className="text-lg">Cámara Apagada</span>
            </div>
          )}

          {/* AI Overlay Info */}
          <div className="absolute top-6 right-6 left-6 flex justify-end">
            <RiskIndicator level={aiState.riskLevel} emotion={aiState.primaryEmotion} />
          </div>

          {/* AI Speaking Indicator */}
          <div className="absolute bottom-10 left-1/2 -translate-x-1/2">
            {status === 'connected' ? (
              <div className="flex items-center gap-2 bg-slate-900/70 backdrop-blur-md px-6 py-3 rounded-full border border-slate-700">
                <div className={`w-3 h-3 rounded-full ${volume > 5 ? 'bg-green-400 animate-pulse' : 'bg-slate-400'}`}></div>
                <span className="text-sm font-medium text-white">Mentta te escucha...</span>
              </div>
            ) : (
              <div className="bg-yellow-500/20 text-yellow-200 px-4 py-2 rounded-lg backdrop-blur border border-yellow-500/30">
                Conectando línea segura...
              </div>
            )}
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
          onClick={onEndSession}
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

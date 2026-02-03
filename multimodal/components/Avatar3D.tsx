import React, { useEffect, useState } from 'react';
import { AIStatus } from './StatusIndicator';

interface Avatar3DProps {
    aiStatus: AIStatus;
    className?: string;
}

/**
 * Animated Avatar Component
 * Uses CSS animations for a simple but effective avatar visualization
 * that works without external dependencies or 3D libraries
 */
const Avatar3D: React.FC<Avatar3DProps> = ({ aiStatus, className = '' }) => {
    const isSpeaking = aiStatus === 'speaking';
    const isListening = aiStatus === 'listening';
    const isProcessing = aiStatus === 'processing';

    // Blinking animation state
    const [isBlinking, setIsBlinking] = useState(false);

    // Random blinking every 3-5 seconds
    useEffect(() => {
        const blinkInterval = setInterval(() => {
            setIsBlinking(true);
            setTimeout(() => setIsBlinking(false), 150);
        }, 3000 + Math.random() * 2000);

        return () => clearInterval(blinkInterval);
    }, []);

    return (
        <div className={`relative flex items-center justify-center ${className}`}>
            {/* Background gradient */}
            <div className="absolute inset-0 bg-gradient-to-br from-indigo-900/80 via-purple-900/60 to-slate-900" />

            {/* Animated background circles */}
            <div className="absolute inset-0 overflow-hidden">
                <div className={`absolute top-1/4 left-1/4 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl ${isSpeaking ? 'animate-pulse' : ''}`} />
                <div className={`absolute bottom-1/4 right-1/4 w-48 h-48 rounded-full bg-purple-500/20 blur-3xl ${isProcessing ? 'animate-pulse' : ''}`} />
            </div>

            {/* Avatar Container */}
            <div className="relative z-10 flex flex-col items-center">
                {/* Avatar Circle */}
                <div className={`relative transition-transform duration-300 ${isSpeaking ? 'scale-105' : ''}`}>
                    {/* Outer glow ring */}
                    <div className={`absolute inset-0 rounded-full blur-md transition-all duration-300
            ${isSpeaking ? 'bg-indigo-400/50 scale-110' :
                            isListening ? 'bg-green-400/30 scale-105' :
                                isProcessing ? 'bg-amber-400/30 scale-105 animate-pulse' :
                                    'bg-slate-500/20'}`}
                    />

                    {/* Avatar face container */}
                    <div className="relative w-48 h-48 md:w-64 md:h-64 rounded-full bg-gradient-to-br from-indigo-600 to-purple-700 shadow-2xl shadow-purple-900/50 overflow-hidden">
                        {/* Face */}
                        <div className="absolute inset-0 flex flex-col items-center justify-center">

                            {/* Eyes */}
                            <div className="flex gap-8 md:gap-12 mb-4">
                                {/* Left eye */}
                                <div className={`relative w-6 h-6 md:w-8 md:h-8 transition-all duration-150 ${isBlinking ? 'scale-y-[0.1]' : ''}`}>
                                    <div className="absolute inset-0 bg-white rounded-full" />
                                    <div className={`absolute inset-1 bg-slate-800 rounded-full transition-all duration-300
                    ${isListening ? 'translate-x-1' : isSpeaking ? 'translate-y-0.5' : ''}`} />
                                    <div className="absolute top-1 left-2 w-1.5 h-1.5 bg-white rounded-full" />
                                </div>

                                {/* Right eye */}
                                <div className={`relative w-6 h-6 md:w-8 md:h-8 transition-all duration-150 ${isBlinking ? 'scale-y-[0.1]' : ''}`}>
                                    <div className="absolute inset-0 bg-white rounded-full" />
                                    <div className={`absolute inset-1 bg-slate-800 rounded-full transition-all duration-300
                    ${isListening ? 'translate-x-1' : isSpeaking ? 'translate-y-0.5' : ''}`} />
                                    <div className="absolute top-1 left-2 w-1.5 h-1.5 bg-white rounded-full" />
                                </div>
                            </div>

                            {/* Mouth */}
                            <div className="relative mt-2">
                                {isSpeaking ? (
                                    // Talking mouth animation
                                    <div className="flex gap-0.5">
                                        {[...Array(5)].map((_, i) => (
                                            <div
                                                key={i}
                                                className="w-2 md:w-3 bg-white rounded-full animate-bounce"
                                                style={{
                                                    height: `${12 + Math.sin(Date.now() / 100 + i) * 8}px`,
                                                    animationDelay: `${i * 0.1}s`,
                                                    animationDuration: '0.3s'
                                                }}
                                            />
                                        ))}
                                    </div>
                                ) : isProcessing ? (
                                    // Thinking mouth (small circle)
                                    <div className="w-6 h-6 md:w-8 md:h-8 border-4 border-white rounded-full animate-pulse" />
                                ) : (
                                    // Smiling mouth
                                    <div className="w-12 h-6 md:w-16 md:h-8 border-b-4 border-white rounded-b-full" />
                                )}
                            </div>
                        </div>

                        {/* Subtle shine effect */}
                        <div className="absolute top-0 left-0 right-0 h-1/3 bg-gradient-to-b from-white/10 to-transparent" />
                    </div>

                    {/* Audio waves when speaking */}
                    {isSpeaking && (
                        <div className="absolute -bottom-4 left-1/2 -translate-x-1/2 flex gap-1">
                            {[...Array(5)].map((_, i) => (
                                <div
                                    key={i}
                                    className="w-1.5 bg-indigo-400 rounded-full animate-bounce"
                                    style={{
                                        height: `${8 + Math.random() * 16}px`,
                                        animationDelay: `${i * 0.1}s`
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </div>

                {/* Name label */}
                <div className="mt-6 md:mt-8">
                    <div className={`px-6 py-2 rounded-full backdrop-blur-sm text-lg font-semibold transition-all duration-300
            ${isSpeaking
                            ? 'bg-indigo-500/40 text-white border border-indigo-400/50 shadow-lg shadow-indigo-500/30'
                            : isProcessing
                                ? 'bg-amber-500/30 text-amber-200 border border-amber-400/30'
                                : isListening
                                    ? 'bg-green-500/30 text-green-200 border border-green-400/30'
                                    : 'bg-slate-800/50 text-slate-300 border border-slate-600/30'
                        }`}>
                        {isSpeaking ? '💬 Mentta habla' :
                            isProcessing ? '🧠 Mentta piensa...' :
                                isListening ? '👂 Mentta escucha' :
                                    '🤖 Mentta'}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Avatar3D;

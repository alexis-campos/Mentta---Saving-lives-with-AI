import React, { useEffect, useState } from 'react';
import { AIStatus } from './StatusIndicator';

interface Avatar3DProps {
    aiStatus: AIStatus;
    className?: string;
}

/**
 * Animated Avatar Component
 * Friendly face design with premium Antigravity aesthetics
 * Warm colors, soft animations, and elegant styling
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
            {/* Clean cream background */}
            <div
                className="absolute inset-0"
                style={{
                    background: 'radial-gradient(circle at center, #FCFCFA 0%, #F5F5F3 100%)'
                }}
            />

            {/* Avatar Container */}
            <div className="relative z-10 flex flex-col items-center">
                {/* Avatar Circle */}
                <div className={`relative transition-transform duration-300 ${isSpeaking ? 'scale-105' : ''}`}>

                    {/* Outer ambient glow - warm terracotta tones */}
                    <div
                        className={`absolute inset-0 rounded-full blur-2xl transition-all duration-500`}
                        style={{
                            background: isSpeaking
                                ? 'radial-gradient(circle, rgba(200, 85, 61, 0.12) 0%, transparent 70%)'
                                : isListening
                                    ? 'radial-gradient(circle, rgba(139, 157, 139, 0.1) 0%, transparent 70%)'
                                    : 'radial-gradient(circle, rgba(0, 0, 0, 0.03) 0%, transparent 70%)',
                            transform: isSpeaking ? 'scale(1.3)' : 'scale(1.2)'
                        }}
                    />

                    {/* Avatar face container - soft gradient */}
                    <div
                        className="relative w-48 h-48 md:w-64 md:h-64 rounded-full overflow-hidden transition-all duration-300"
                        style={{
                            background: 'linear-gradient(145deg, #F5E6D8 0%, #E8D5C4 50%, #DCC5B0 100%)',
                            boxShadow: isSpeaking
                                ? '0 30px 80px rgba(200, 85, 61, 0.15), 0 15px 40px rgba(0, 0, 0, 0.08), inset 0 -10px 30px rgba(0,0,0,0.05)'
                                : '0 25px 60px rgba(0, 0, 0, 0.08), 0 12px 30px rgba(0, 0, 0, 0.04), inset 0 -10px 30px rgba(0,0,0,0.03)'
                        }}
                    >
                        {/* Face */}
                        <div className="absolute inset-0 flex flex-col items-center justify-center">

                            {/* Eyes */}
                            <div className="flex gap-10 md:gap-14 mb-4">
                                {/* Left eye */}
                                <div className={`relative w-5 h-5 md:w-7 md:h-7 transition-all duration-150 ${isBlinking ? 'scale-y-[0.1]' : ''}`}>
                                    <div
                                        className="absolute inset-0 rounded-full"
                                        style={{ backgroundColor: '#FFFFFF', boxShadow: 'inset 0 2px 4px rgba(0,0,0,0.1)' }}
                                    />
                                    <div
                                        className={`absolute inset-1 rounded-full transition-all duration-300 ${isListening ? 'translate-x-0.5' : isSpeaking ? 'translate-y-0.5' : ''}`}
                                        style={{ backgroundColor: '#3D3D3D' }}
                                    />
                                    <div
                                        className="absolute top-1 left-1.5 w-1.5 h-1.5 md:w-2 md:h-2 rounded-full"
                                        style={{ backgroundColor: '#FFFFFF' }}
                                    />
                                </div>

                                {/* Right eye */}
                                <div className={`relative w-5 h-5 md:w-7 md:h-7 transition-all duration-150 ${isBlinking ? 'scale-y-[0.1]' : ''}`}>
                                    <div
                                        className="absolute inset-0 rounded-full"
                                        style={{ backgroundColor: '#FFFFFF', boxShadow: 'inset 0 2px 4px rgba(0,0,0,0.1)' }}
                                    />
                                    <div
                                        className={`absolute inset-1 rounded-full transition-all duration-300 ${isListening ? 'translate-x-0.5' : isSpeaking ? 'translate-y-0.5' : ''}`}
                                        style={{ backgroundColor: '#3D3D3D' }}
                                    />
                                    <div
                                        className="absolute top-1 left-1.5 w-1.5 h-1.5 md:w-2 md:h-2 rounded-full"
                                        style={{ backgroundColor: '#FFFFFF' }}
                                    />
                                </div>
                            </div>

                            {/* Mouth */}
                            <div className="relative mt-3">
                                {isSpeaking ? (
                                    // Talking mouth animation - soft bars
                                    <div className="flex gap-1 items-end h-6">
                                        {[...Array(5)].map((_, i) => (
                                            <div
                                                key={i}
                                                className="w-2 md:w-2.5 rounded-full animate-bounce"
                                                style={{
                                                    backgroundColor: '#C8553D',
                                                    height: `${10 + Math.sin(Date.now() / 100 + i) * 6}px`,
                                                    animationDelay: `${i * 0.08}s`,
                                                    animationDuration: '0.35s'
                                                }}
                                            />
                                        ))}
                                    </div>
                                ) : isProcessing ? (
                                    // Thinking mouth - small circle
                                    <div
                                        className="w-6 h-6 md:w-8 md:h-8 rounded-full animate-pulse"
                                        style={{ border: '3px solid #C8553D' }}
                                    />
                                ) : (
                                    // Smiling mouth - warm color
                                    <div
                                        className="w-12 h-6 md:w-16 md:h-8 rounded-b-full"
                                        style={{ borderBottom: '4px solid #C8553D' }}
                                    />
                                )}
                            </div>
                        </div>

                        {/* Subtle shine effect on top */}
                        <div
                            className="absolute top-0 left-0 right-0 h-1/3"
                            style={{ background: 'linear-gradient(to bottom, rgba(255,255,255,0.25) 0%, transparent 100%)' }}
                        />
                    </div>

                    {/* Audio waves when speaking */}
                    {isSpeaking && (
                        <div className="absolute -bottom-5 left-1/2 -translate-x-1/2 flex gap-1.5">
                            {[...Array(5)].map((_, i) => (
                                <div
                                    key={i}
                                    className="w-1 rounded-full animate-bounce"
                                    style={{
                                        backgroundColor: '#C8553D',
                                        height: `${6 + Math.random() * 10}px`,
                                        animationDelay: `${i * 0.1}s`,
                                        animationDuration: '0.4s'
                                    }}
                                />
                            ))}
                        </div>
                    )}
                </div>

                {/* Status label */}
                <div className="mt-10 md:mt-12">
                    <div
                        className="px-6 py-2.5 rounded-full text-sm font-medium tracking-wide transition-all duration-300"
                        style={{
                            fontFamily: "'Inter', sans-serif",
                            background: 'rgba(255, 255, 255, 0.95)',
                            backdropFilter: 'blur(10px)',
                            boxShadow: '0 10px 40px rgba(0, 0, 0, 0.04)',
                            border: '1px solid rgba(0, 0, 0, 0.04)',
                            color: isSpeaking ? '#C8553D' : isListening ? '#2A2A2A' : '#888'
                        }}
                    >
                        {isSpeaking ? 'Mentta responde' :
                            isProcessing ? 'Pensando...' :
                                isListening ? 'Escuchando' :
                                    'Listo para ti'}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Avatar3D;

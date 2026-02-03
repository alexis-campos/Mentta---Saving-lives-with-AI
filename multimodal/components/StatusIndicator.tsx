import React from 'react';

export type AIStatus = 'idle' | 'listening' | 'processing' | 'speaking';

interface StatusIndicatorProps {
    status: AIStatus;
    volume?: number; // 0-100 for audio visualization
}

const StatusIndicator: React.FC<StatusIndicatorProps> = ({ status, volume = 0 }) => {
    const getStatusConfig = () => {
        switch (status) {
            case 'idle':
                return {
                    text: 'Listo para escucharte',
                    color: 'bg-slate-500',
                    icon: '🎙️',
                    animate: false
                };
            case 'listening':
                return {
                    text: 'Te estoy escuchando...',
                    color: 'bg-green-500',
                    icon: '👂',
                    animate: true
                };
            case 'processing':
                return {
                    text: 'Mentta está pensando...',
                    color: 'bg-amber-500',
                    icon: '🧠',
                    animate: true
                };
            case 'speaking':
                return {
                    text: 'Mentta está hablando...',
                    color: 'bg-indigo-500',
                    icon: '💬',
                    animate: true
                };
            default:
                return {
                    text: 'Conectando...',
                    color: 'bg-slate-400',
                    icon: '⏳',
                    animate: false
                };
        }
    };

    const config = getStatusConfig();

    // Audio wave bars for listening/speaking states
    const AudioWaves = () => (
        <div className="flex items-center gap-0.5 h-6">
            {[...Array(5)].map((_, i) => (
                <div
                    key={i}
                    className={`w-1 rounded-full transition-all duration-150 ${config.color}`}
                    style={{
                        height: status === 'listening' || status === 'speaking'
                            ? `${Math.max(8, Math.min(24, 8 + (volume / 100) * 16 + Math.sin(Date.now() / 200 + i) * 4))}px`
                            : '8px',
                        animationDelay: `${i * 0.1}s`
                    }}
                />
            ))}
        </div>
    );

    // Spinner for processing state
    const Spinner = () => (
        <div className="relative w-6 h-6">
            <div className="absolute inset-0 border-2 border-amber-500/30 rounded-full" />
            <div className="absolute inset-0 border-2 border-transparent border-t-amber-500 rounded-full animate-spin" />
        </div>
    );

    return (
        <div className="flex items-center gap-3 bg-slate-900/80 backdrop-blur-md px-5 py-3 rounded-full border border-slate-700 shadow-lg">
            {/* Status Icon/Animation */}
            <div className="flex items-center justify-center w-8">
                {status === 'processing' ? (
                    <Spinner />
                ) : status === 'listening' || status === 'speaking' ? (
                    <AudioWaves />
                ) : (
                    <span className="text-lg">{config.icon}</span>
                )}
            </div>

            {/* Status Indicator Dot */}
            <div className={`w-2.5 h-2.5 rounded-full ${config.color} ${config.animate ? 'animate-pulse' : ''}`} />

            {/* Status Text */}
            <span className="text-sm font-medium text-white whitespace-nowrap">
                {config.text}
            </span>
        </div>
    );
};

export default StatusIndicator;

import React from 'react';

export type AIStatus = 'idle' | 'listening' | 'processing' | 'speaking';

interface StatusIndicatorProps {
    status: AIStatus;
    volume?: number;
}

const StatusIndicator: React.FC<StatusIndicatorProps> = ({ status, volume = 0 }) => {
    const getStatusConfig = () => {
        switch (status) {
            case 'idle':
                return {
                    text: 'En espera',
                    color: '#999'
                };
            case 'listening':
                return {
                    text: 'Escuchando',
                    color: '#2A2A2A'
                };
            case 'processing':
                return {
                    text: 'Procesando',
                    color: '#999'
                };
            case 'speaking':
                return {
                    text: 'Respondiendo',
                    color: '#C8553D'
                };
            default:
                return {
                    text: 'Conectando',
                    color: '#CCC'
                };
        }
    };

    const config = getStatusConfig();

    return (
        <div
            className="flex items-center gap-3 px-6 py-3 rounded-full transition-all duration-300"
            style={{
                background: 'rgba(255, 255, 255, 0.95)',
                boxShadow: '0 10px 40px rgba(0, 0, 0, 0.04)',
                border: '1px solid rgba(0, 0, 0, 0.03)'
            }}
        >
            {/* Status Dot */}
            <div
                className={`w-2 h-2 rounded-full transition-all duration-300 ${status === 'speaking' || status === 'listening' ? 'animate-pulse' : ''}`}
                style={{ backgroundColor: config.color }}
            />

            {/* Status Text */}
            <span
                className="text-xs font-medium uppercase"
                style={{
                    color: config.color,
                    letterSpacing: '0.15em'
                }}
            >
                {config.text}
            </span>

            {/* Minimal audio wave for speaking */}
            {status === 'speaking' && (
                <div className="flex items-center gap-1 ml-1">
                    {[...Array(3)].map((_, i) => (
                        <div
                            key={i}
                            className="w-0.5 rounded-full animate-bounce"
                            style={{
                                height: `${6 + (i % 2) * 4}px`,
                                backgroundColor: '#C8553D',
                                animationDelay: `${i * 0.1}s`,
                                animationDuration: '0.5s'
                            }}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

export default StatusIndicator;

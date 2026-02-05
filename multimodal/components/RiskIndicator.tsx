import React from 'react';
import { RiskLevel } from '../types';

interface RiskIndicatorProps {
  level: RiskLevel;
  emotion: string;
}

const RiskIndicator: React.FC<RiskIndicatorProps> = ({ level, emotion }) => {
  const getColor = (lvl: RiskLevel) => {
    switch (lvl) {
      case RiskLevel.CRITICAL: return '#C8553D';
      case RiskLevel.HIGH: return '#C8553D';
      case RiskLevel.MODERATE: return '#D4A574';
      default: return '#8B9D8B';
    }
  };

  const getLabel = (lvl: RiskLevel) => {
    switch (lvl) {
      case RiskLevel.CRITICAL: return 'Crítico';
      case RiskLevel.HIGH: return 'Alto';
      case RiskLevel.MODERATE: return 'Moderado';
      default: return 'Estable';
    }
  };

  return (
    <div className="w-full">
      {/* Status Row */}
      <div className="flex items-center gap-4 mb-4">
        {/* Status Dot */}
        <div
          className={`w-2.5 h-2.5 rounded-full transition-all duration-500 ${level >= RiskLevel.HIGH ? 'animate-pulse' : ''}`}
          style={{ backgroundColor: getColor(level) }}
        />

        {/* Status Label */}
        <span
          className="text-xs font-medium uppercase"
          style={{
            color: getColor(level),
            letterSpacing: '0.15em'
          }}
        >
          {getLabel(level)}
        </span>
      </div>

      {/* Emotion Display */}
      <div className="flex items-baseline gap-2">
        <span
          className="text-[9px] font-medium uppercase"
          style={{ color: '#BBB', letterSpacing: '0.1em' }}
        >
          Emoción
        </span>
        <span
          className="text-sm font-medium capitalize"
          style={{ color: '#2A2A2A' }}
        >
          {emotion || 'Neutral'}
        </span>
      </div>

      {/* Alert for high risk */}
      {level >= RiskLevel.HIGH && (
        <div
          className="mt-4 p-3 rounded-2xl text-[10px] font-medium"
          style={{
            background: 'rgba(200, 85, 61, 0.06)',
            border: '1px solid rgba(200, 85, 61, 0.1)',
            color: '#C8553D',
            letterSpacing: '0.02em'
          }}
        >
          Protocolo activo • Línea 113
        </div>
      )}
    </div>
  );
};

export default RiskIndicator;

import React from 'react';
import { RiskLevel } from '../types';

interface RiskIndicatorProps {
  level: RiskLevel;
  emotion: string;
}

const RiskIndicator: React.FC<RiskIndicatorProps> = ({ level, emotion }) => {
  const getColor = (lvl: RiskLevel) => {
    switch (lvl) {
      case RiskLevel.CRITICAL: return 'bg-red-600 animate-pulse shadow-[0_0_20px_rgba(220,38,38,0.7)]';
      case RiskLevel.HIGH: return 'bg-orange-500';
      case RiskLevel.MODERATE: return 'bg-yellow-500';
      default: return 'bg-emerald-500';
    }
  };

  const getLabel = (lvl: RiskLevel) => {
    switch (lvl) {
      case RiskLevel.CRITICAL: return 'RIESGO CRÍTICO DETECTADO';
      case RiskLevel.HIGH: return 'Riesgo Alto';
      case RiskLevel.MODERATE: return 'Riesgo Moderado';
      default: return 'Estable';
    }
  };

  return (
    <div className="bg-gray-800/80 backdrop-blur-md rounded-xl p-4 border border-gray-700 w-full max-w-sm">
      <div className="flex items-center justify-between mb-2">
        <span className="text-gray-400 text-xs uppercase tracking-wider font-semibold">Análisis IA</span>
        <div className={`h-2 w-2 rounded-full ${level >= RiskLevel.HIGH ? 'animate-ping bg-red-500' : 'bg-green-500'}`}></div>
      </div>

      <div className="flex items-center gap-3">
        <div className={`h-12 w-1 rounded-full ${getColor(level)} transition-all duration-500`}></div>
        <div>
          <h3 className="text-lg font-bold text-white leading-tight">{getLabel(level)}</h3>
          <p className="text-sm text-gray-300 capitalize">Emoción detectada: <span className="text-white font-medium">{emotion || 'Neutral'}</span></p>
        </div>
      </div>

      {level >= RiskLevel.HIGH && (
        <div className="mt-3 bg-red-900/30 border border-red-500/30 rounded p-2 text-xs text-red-200">
          Protocolo activo: Psicólogo alertado. Llama al <strong>113</strong> si necesitas ayuda inmediata.
        </div>
      )}
    </div>
  );
};

export default RiskIndicator;

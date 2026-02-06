export enum RiskLevel {
  LOW = 0,
  MODERATE = 1,
  HIGH = 2,
  CRITICAL = 3
}

export interface MentalHealthState {
  riskLevel: RiskLevel;
  primaryEmotion: string;
  notes: string[];
}

export type ConnectionStatus = 'disconnected' | 'connecting' | 'connected' | 'error';

export interface AudioVisualizerData {
  volume: number;
}

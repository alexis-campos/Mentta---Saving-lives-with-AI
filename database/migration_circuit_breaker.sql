-- Migration: Circuit Breaker Configuration Table
-- Created: 2026-01-31

CREATE TABLE IF NOT EXISTS `system_config` (
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initialize default values
INSERT IGNORE INTO `system_config` (`config_key`, `config_value`) VALUES
('circuit_breaker_state', 'closed'),
('ai_failure_count', '0'),
('ai_last_failure', '0');

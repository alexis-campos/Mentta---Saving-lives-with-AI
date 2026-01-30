-- ============================================
-- MENTTA - Migration: Hamburger Menu Features
-- Version: 1.1
-- Date: 2026-01-29
-- Description: Adds tables and columns for hamburger menu functionality
-- ============================================

USE mentta;

-- ============================================
-- 1. Add session_id to conversations table
-- Groups messages into distinct chat sessions
-- ============================================
ALTER TABLE conversations ADD COLUMN session_id VARCHAR(50) NULL AFTER patient_id;
ALTER TABLE conversations ADD INDEX idx_session (session_id);

-- ============================================
-- 2. Add theme_preference to users table
-- Stores light/dark mode preference
-- ============================================
ALTER TABLE users ADD COLUMN theme_preference ENUM('light', 'dark') DEFAULT 'light';

-- ============================================
-- 3. Create user_preferences table
-- Stores user settings like pause analysis, notifications
-- ============================================
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    analysis_paused BOOLEAN DEFAULT FALSE,
    analysis_paused_until TIMESTAMP NULL,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    daily_reminder_time TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_user_preferences_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User preferences for analysis pause, notifications, reminders';

-- ============================================
-- 4. Create notifications table
-- Stores user notifications
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('psychologist_message', 'upcoming_appointment', 'new_resource', 'daily_reminder', 'crisis_followup') NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT,
    action_url VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_notifications_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User notifications for various events';

-- ============================================
-- 5. Update alerts table to support manual crisis
-- Add 'manual_request' type if not exists
-- ============================================
ALTER TABLE alerts MODIFY COLUMN alert_type ENUM('suicide', 'self_harm', 'crisis', 'anxiety', 'depression', 'manual_request') NOT NULL;

-- ============================================
-- END MIGRATION
-- ============================================

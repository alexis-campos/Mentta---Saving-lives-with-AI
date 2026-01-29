# Mentta - AI-Powered Mental Health Support

<p align="center">
  <img src="https://img.shields.io/badge/version-0.3.0-blue.svg" alt="Version 0.3.0">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg" alt="PHP 8.0+">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/AI-Google%20Gemini-FF6F00.svg" alt="Google Gemini">
</p>

A 24/7 emotional support platform combining AI-powered conversational therapy with professional psychologist supervision. Designed to prevent suicide and provide accessible mental health support.

## 🆕 What's New in v0.3.0

- **Complete Alert System** with notification chain (psychologist → emergency contacts → national line)
- **Safe Life Mode** - AI changes tone during crisis without alerting patient
- **Long Polling** for real-time psychologist alerts
- **Enhanced Risk Detection** with more patterns for sadness, stress, and crisis
- **Psychologist API** endpoints for alert management
- **Alert Testing** tools for verification

## ⚡ Features

| Feature | Description |
|---------|-------------|
| 🤖 **AI Chat** | Empathetic conversations powered by Google Gemini |
| 🔒 **Safe Life Mode** | Automatic silent alerts when crisis is detected |
| 🚨 **Alert System** | Real-time notifications to psychologists with sound |
| 📊 **Sentiment Tracking** | Real-time emotion analysis per message |
| 🧠 **Memory System** | AI remembers context from previous conversations |
| 👥 **Professional Dashboard** | Psychologists monitor and respond to alerts |
| 📱 **Mobile-First** | Beautiful responsive design for all devices |

## 📋 Requirements

- PHP 8.0+
- MySQL 8.0+ or MariaDB 10.3+
- Apache/Nginx with PHP support
- Google Gemini API key

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/yourusername/mentta.git
cd mentta
```

### 2. Setup environment configuration

```bash
# Copy the example environment file
cp .env.example .env

# Edit .env with your configuration
# - Database credentials
# - Google Gemini API key
```

### 3. Create the database

```bash
# Using MySQL CLI
mysql -u root -p < database/schema.sql
mysql -u root -p mentta < database/seed.sql
```

Or via phpMyAdmin:
1. Create database named `mentta`
2. Import `database/schema.sql`
3. Import `database/seed.sql` (optional - adds test data)

### 4. Configure your web server

Point your web server to the project directory. For XAMPP/WAMP, place in `htdocs`.

### 5. Access the application

```
http://localhost/mentta/login.php
```

## 🔐 Environment Variables

Create a `.env` file in the project root (copy from `.env.example`):

```env
# Database
DB_HOST=localhost
DB_NAME=mentta
DB_USER=root
DB_PASS=your_password

# Google Gemini AI
AI_API_KEY=your_gemini_api_key

# Application
APP_ENV=development
APP_URL=http://localhost/mentta
```

> ⚠️ **Never commit your `.env` file to version control!**

## 📁 Project Structure

```
mentta/
├── api/
│   ├── chat/                   # Chat API endpoints
│   │   ├── send-message.php
│   │   ├── get-history.php
│   │   └── get-sentiment-history.php
│   └── psychologist/           # Psychologist API endpoints
│       ├── check-alerts.php    # Long polling for alerts
│       └── acknowledge-alert.php
├── assets/
│   ├── css/                    # Stylesheets
│   ├── js/
│   │   ├── chat.js             # Chat interface logic
│   │   ├── alerts.js           # Real-time alert system
│   │   └── utils.js            # Utility functions
│   └── sounds/                 # Alert sounds
├── database/
│   ├── schema.sql              # Database structure
│   └── seed.sql                # Test data
├── includes/
│   ├── config.php              # Configuration (loads .env)
│   ├── db.php                  # Database connection
│   ├── auth.php                # Authentication system
│   ├── ai-client.php           # Gemini AI + Safe Life Mode
│   ├── sentiment-analyzer.php  # 5-emotion analysis
│   ├── risk-detector.php       # Risk level detection
│   ├── memory-parser.php       # Contextual memory
│   └── alert-system.php        # Alert management
├── logs/                       # Error logs
├── test/
│   ├── test-chat.php           # Chat system tests
│   └── test-alerts.php         # Alert system tests
├── chat.php                    # Patient chat interface
├── login.php                   # Login page
├── register.php                # Registration page
├── .env.example                # Environment template
└── README.md
```

## 🚨 Alert System Flow

```
Patient sends risky message
        ↓
   Risk Detection
        ↓
    High/Critical?
    /           \
  Yes            No
   ↓              ↓
Create Alert   Normal Response
   ↓
Has Psychologist?
    /        \
  Yes         No
   ↓           ↓
Notify      Emergency Contacts?
Psychologist   /        \
             Yes         No
              ↓           ↓
           Notify    National Line
           Contacts  (113 - Peru)
```

**Important:** The patient NEVER knows an alert was triggered. The AI simply responds with extra warmth (Safe Life Mode).

## 🧪 Testing

```bash
# Test chat components
http://localhost/mentta/test/test-chat.php

# Test alert system
http://localhost/mentta/test/test-alerts.php
```

## 🔄 Changelog

### v0.3.0 (Current)
- Added complete alert system with notification chain
- Implemented Safe Life Mode in AI responses
- Created long polling for real-time psychologist alerts
- Added psychologist API endpoints
- Enhanced risk detection with more patterns
- Created alert testing tools
- Added alerts.js for frontend notifications

### v0.2.0
- Added complete chat system with AI integration
- Implemented sentiment analysis (5 emotions)
- Added risk detection with Safe Life Mode
- Created contextual memory system
- Built mobile-first chat interface
- Added environment-based configuration (.env)

### v0.1.0
- Initial database schema
- User authentication system
- Basic project structure

## 🛡️ Security Notes

- All API keys stored in `.env` (excluded from git)
- Passwords hashed with bcrypt
- PDO prepared statements for SQL injection prevention
- Rate limiting on chat endpoints
- XSS protection via input sanitization
- Silent alerts protect patient privacy

## 📄 License

Private project - All rights reserved.

---

<p align="center">
  <strong>Mentta</strong> - Saving lives with AI 💜
</p>

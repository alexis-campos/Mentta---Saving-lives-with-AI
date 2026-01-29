# Mentta - AI-Powered Mental Health Support

<p align="center">
  <img src="https://img.shields.io/badge/version-0.3.1-blue.svg" alt="Version 0.3.1">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg" alt="PHP 8.0+">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/AI-Google%20Gemini%203-FF6F00.svg" alt="Google Gemini 3">
</p>

A 24/7 emotional support platform combining AI-powered conversational therapy with professional psychologist supervision. Designed to prevent suicide and provide accessible mental health support.

## 🆕 What's New in v0.3.1

### 🤖 AI-Powered Contextual Analysis
- **Every message analyzed by AI** - No more relying solely on keywords
- **Contextual understanding** - AI knows "me quiero morir de risa" is NOT a crisis
- **Semantic memory extraction** - AI extracts people, relationships, events, and places
- **Unified analyzer** - Single AI call handles risk, sentiment, and memory

### 🛠️ Technical Improvements
- Updated to **Gemini 3 Flash Preview** model
- API auth via `x-goog-api-key` header (per latest Google docs)
- Safety settings configured for mental health content analysis
- Comprehensive test suite with individual test buttons

## ⚡ Features

| Feature | Description |
|---------|-------------|
| 🤖 **AI Chat** | Empathetic conversations powered by Google Gemini 3 |
| 🧠 **Contextual Risk Detection** | AI understands intent, not just keywords |
| 🔒 **Safe Life Mode** | Automatic silent alerts when crisis is detected |
| 🚨 **Alert System** | Real-time notifications to psychologists with sound |
| 📊 **Deep Sentiment Analysis** | 5-emotion analysis (positive, negative, anxiety, sadness, anger) |
| 💾 **Memory System** | AI extracts and remembers people, places, events |
| 👥 **Professional Dashboard** | Psychologists monitor and respond to alerts |
| 📱 **Mobile-First** | Beautiful responsive design for all devices |

## 🧠 How AI Analysis Works

```
Patient sends message
         ↓
   AI Analyzer (single call)
         ↓
   ┌─────────────────────────────────────┐
   │  1. Risk Assessment                 │
   │     - Level: none/low/medium/high/critical
   │     - Is it REAL risk? (context check)
   │     - Trigger alert?               │
   │                                     │
   │  2. Deep Sentiment                  │
   │     - 5 emotions with scores        │
   │     - Dominant emotion              │
   │                                     │
   │  3. Memory Extraction               │
   │     - People mentioned              │
   │     - Relationships (Ana → hermana) │
   │     - Events (perdió su trabajo)    │
   │     - Places (Lima, parque)         │
   │     - Topics detected               │
   │                                     │
   │  4. Safe Life Mode Decision         │
   │     - Activate warm response?       │
   └─────────────────────────────────────┘
         ↓
   Response + Alert (if needed)
```

### Example: Contextual Understanding

| Message | Old (Keywords) | New (AI) |
|---------|---------------|----------|
| "Me quiero morir de risa" | � CRITICAL | ✅ None (colloquial expression) |
| "Todo es gris, sin sentido" | ✅ None | 🚨 High (implicit hopelessness) |
| "Mi amigo se cortó ayer" | 🚨 HIGH | ⚠️ Low (about someone else) |

## �📋 Requirements

- PHP 8.0+
- MySQL 8.0+ or MariaDB 10.3+
- Apache/Nginx with PHP support
- Google Gemini API key (get free at [aistudio.google.com](https://aistudio.google.com))

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
│   │   ├── send-message.php    # Main chat (uses AI analyzer)
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
│   ├── ai-client.php           # Gemini AI client + Safe Life Mode
│   ├── ai-analyzer.php         # 🆕 Unified AI analyzer
│   ├── sentiment-analyzer.php  # Legacy 5-emotion analysis (fallback)
│   ├── risk-detector.php       # Legacy risk detection (fallback)
│   ├── memory-parser.php       # Legacy memory parser (fallback)
│   └── alert-system.php        # Alert management
├── logs/                       # Error logs
├── test/
│   ├── test-api.php            # 🆕 API connection test
│   ├── test-ai-analyzer.php    # 🆕 AI analysis tests
│   ├── test-ai-memory.php      # 🆕 Memory extraction tests
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
   AI Analysis
        ↓
  Is it REAL risk?
  (not colloquial)
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
# Test API connection
http://localhost/mentta/test/test-api.php

# Test AI analyzer (risk/sentiment)
http://localhost/mentta/test/test-ai-analyzer.php

# Test memory extraction
http://localhost/mentta/test/test-ai-memory.php

# Test chat components
http://localhost/mentta/test/test-chat.php

# Test alert system
http://localhost/mentta/test/test-alerts.php
```

> 💡 **Note:** Google Gemini has rate limits (~15 requests/minute on free tier). Run tests one at a time.

## 🔄 Changelog

### v0.3.1 (Current)
**AI-Powered Analysis**
- Added unified AI analyzer (`ai-analyzer.php`)
- Every message now analyzed by AI for context
- Contextual risk detection (understands colloquial expressions)
- Deep sentiment analysis with 5 emotions
- Semantic memory extraction (people, relationships, events, places)
- Safe Life Mode auto-activation based on AI assessment
- Fallback to legacy keyword systems if AI fails

**Technical Updates**
- Updated to Gemini 3 Flash Preview model
- API auth changed to `x-goog-api-key` header
- Added safety settings for mental health content
- Created comprehensive test suite
- Added analysis logging table (`ai_analysis_logs`)

### v0.3.0
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
- Safety settings allow AI to analyze sensitive content

## 📄 License

Private project - All rights reserved.

---

<p align="center">
  <strong>Mentta</strong> - Saving lives with AI 💜
</p>

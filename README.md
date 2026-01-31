# Mentta - AI-Powered Mental Health Support

<p align="center">
  <img src="https://img.shields.io/badge/version-0.5.2-blue.svg" alt="Version 0.5.2">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg" alt="PHP 8.0+">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/AI-Google%20Gemini%203-FF6F00.svg" alt="Google Gemini 3">
  <img src="https://img.shields.io/badge/Google%20Maps-API-34A853.svg" alt="Google Maps">
</p>

A 24/7 emotional support platform combining AI-powered conversational therapy with professional psychologist supervision. Designed to prevent suicide and provide accessible mental health support.

## 🆕 What's New in v0.5.2

### 🗺️ Interactive Map of Mental Health Centers
- **Full-page map** at `map.php` with Google Maps integration
- **Geolocation** - Automatically centers on user's location (with Lima fallback)
- **20+ Mental Health Centers** in Lima with real data
- **Color-coded markers:**
  - 🔵 Blue: Your location
  - 🟢 Green: Centers using Mentta
  - 🟠 Orange: 24h Emergency centers
  - 🔴 Red: Other centers
- **Haversine distance** calculation for nearest centers
- **Filters:** All, Mentta-only, Emergency 24h
- **Search** by name, district, or services
- **Responsive panel:** Side panel (desktop) / Bottom swipeable panel (mobile)
- **Actions:** Call center, Get directions (Google Maps)

### 🍔 Hamburger Menu Enhancement
- "Mapa de Centros" button now opens full map page
- Removed placeholder modal

### Previous (v0.5.1): Bug Fixes & Improvements
- Fixed AI Model - Updated to `gemini-3-flash-preview`
- Increased Response Limits - AI responses up to 4000 tokens

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
│   │   ├── send-message.php
│   │   ├── get-history.php
│   │   └── get-chat-list.php   # Chat sessions for sidebar
│   ├── map/                    # 🆕 Map API endpoints (v0.5.2)
│   │   ├── get-nearby-centers.php  # Haversine distance search
│   │   └── search-centers.php  # Text search by name/district
│   ├── patient/                # Patient settings API
│   │   ├── get-preferences.php
│   │   └── update-theme.php
│   └── psychologist/           # Psychologist API endpoints
│       ├── check-alerts.php
│       └── get-patients.php
├── assets/
│   ├── css/
│   │   ├── chat.css
│   │   ├── theme.css           # Dark/Light mode theming
│   │   └── map.css             # 🆕 Map page styles (v0.5.2)
│   ├── js/
│   │   ├── chat.js
│   │   ├── menu.js             # Hamburger menu logic
│   │   ├── theme.js            # Theme switching
│   │   ├── map.js              # 🆕 Google Maps integration (v0.5.2)
│   │   └── utils.js
│   └── sounds/
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   ├── migration_hamburger_menu.sql
│   └── migration_map.sql       # 🆕 Mental health centers (v0.5.2)
├── includes/
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   ├── ai-client.php
│   └── ai-analyzer.php
├── chat.php                    # Patient chat interface
├── map.php                     # 🆕 Mental health centers map (v0.5.2)
├── dashboard.php               # Psychologist dashboard
├── profile.php                 # User profile/settings
├── login.php
├── register.php
├── .env.example
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

### v0.5.1 (Current)
**Bug Fixes & Improvements**
- Fixed AI model configuration (`gemini-3-flash-preview`)
- Increased AI response token limits (300 → 4000)
- Added `Utils` object wrapper in `utils.js` for backwards compatibility
- Removed duplicate `timeAgo()` function declarations
- Improved API error handling with clean JSON fallbacks
- Added error suppression for API endpoints
- Added `test-gemini-api.php` diagnostic tool

### v0.5.0
**Authentication & Landing Page**
- Modern landing page with hero, stats, and features
- Login/Register system with form validation
- API endpoints: `login.php`, `register.php`, `check-session.php`
- Session management with secure cookies
- Role-based page protection
- JavaScript utility library (`utils.js`)
- Complete flow test (`test-complete-flow.php`)

### v0.4.0
**Psychologist Dashboard**
- Added complete dashboard page (`dashboard.php`)
- Patient list with real-time status indicators (stable/monitor/risk)
- 30-day emotional evolution chart with Chart.js
- Alert timeline with severity indicators
- Topic word cloud from patient conversations
- Patient metrics (messages, streak, engagement)
- Real-time alert popup notifications
- Responsive design with mobile support

**New API Endpoints**
- `get-patients.php` - List linked patients with status
- `get-patient-detail.php` - Full patient analytics

### v0.3.1
**AI-Powered Analysis**
- Added unified AI analyzer (`ai-analyzer.php`)
- Every message now analyzed by AI for context
- Contextual risk detection (understands colloquial expressions)
- Semantic memory extraction (people, relationships, events, places)
- Updated to Gemini 3 Flash Preview model
- API auth via `x-goog-api-key` header

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
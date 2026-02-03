# Mentta - AI-Powered Mental Health Support 💜

<p align="center">
  <img src="https://img.shields.io/badge/version-0.5.3-blue.svg" alt="Version 0.5.3">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg" alt="PHP 8.0+">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/AI-Google%20Gemini%202.0-FF6F00.svg" alt="Google Gemini 2.0">
  <img src="https://img.shields.io/badge/Maps-Leaflet%20%2B%20Google-34A853.svg" alt="Maps">
  <img src="https://img.shields.io/badge/License-Private-red.svg" alt="License">
</p>

<p align="center">
  <strong>A 24/7 emotional support platform combining AI-powered conversational therapy with professional psychologist supervision.</strong>
</p>

<p align="center">
  Designed to prevent suicide and provide accessible mental health support.
</p>

---

## 🆕 What's New in v0.5.3

### 🔧 Major Bug Fixes & Improvements

| Category | Fix | Description |
|----------|-----|-------------|
| 🤖 **AI** | Circuit Breaker | Fallback to file storage when DB is unavailable |
| 🗺️ **Maps** | Leaflet Fallback | Works without Google Maps API key (OpenStreetMap) |
| ⚡ **Performance** | Rate Limiter | Probabilistic cleanup (1% of requests) |
| 🌐 **i18n** | Translations | 50+ strings in Spanish & English |
| 🔒 **Security** | Input Validation | Message length validation (5000 chars max) |
| 🎨 **UX** | Search History | Filter chat conversations by title |
| 🛡️ **Reliability** | Error Handling | Standardized error responses across API |

### 🎨 UI/UX Enhancements

- **🫁 Interactive Breathing Timer** - 4-7-8 technique with animated circle
- **🧠 Grounding Checklist** - Interactive 5-4-3-2-1 technique
- **👋 Personalized Welcome** - Time-based greeting (morning/afternoon/evening)
- **🔍 Chat Search** - Filter previous conversations in sidebar
- **💓 Panic Button** - Enhanced visibility with pulse animation
- **📊 Improved Loading** - Multi-state indicator (thinking → analyzing → writing)
- **📱 Sentiment Indicator** - Now visible for 20 seconds (was 8s)

### 🛡️ Security Hardening

- Session regeneration on role verification
- SQL whitelist for dynamic inserts
- Blocked `/test/` directory in production
- Proper IP validation
- Session invalidation on logout

---

## ⚡ Features

| Feature | Description |
|---------|-------------|
| 🤖 **AI Chat** | Empathetic 24/7 conversations powered by Google Gemini 2.0 Flash |
| 🧠 **Contextual Risk Detection** | AI understands intent, not just keywords |
| 🔒 **Safe Life Mode** | Automatic silent alerts when crisis is detected |
| 🚨 **Real-Time Alerts** | Instant notifications to psychologists with sound |
| 📊 **Deep Sentiment Analysis** | 5-emotion analysis (positive, negative, anxiety, sadness, anger) |
| 💾 **Memory System** | AI remembers people, places, events from conversations |
| 🗺️ **Mental Health Map** | Find nearby centers with Leaflet/Google Maps |
| 👥 **Professional Dashboard** | Psychologists monitor patients and respond to alerts |
| 📱 **Mobile-First** | Beautiful responsive design for all devices |
| 🌙 **Dark Mode** | Automatic theme based on system preference |
| 🇪🇸🇺🇸 **Bilingual** | Full Spanish & English support |

---

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
   │                                     │
   │  5. Circuit Breaker                 │
   │     - Fallback if AI fails          │
   └─────────────────────────────────────┘
         ↓
   Response + Alert (if needed)
```

### Contextual Understanding Examples

| Message | Old (Keywords) | New (AI) |
|---------|---------------|----------|
| "Me quiero morir de risa" | 🚨 CRITICAL | ✅ None (colloquial) |
| "Todo es gris, sin sentido" | ✅ None | 🚨 High (implicit hopelessness) |
| "Mi amigo se cortó ayer" | 🚨 HIGH | ⚠️ Low (about someone else) |

---

## 📋 Requirements

| Requirement | Version | Notes |
|-------------|---------|-------|
| PHP | 8.0+ | 8.4 recommended |
| MySQL | 8.0+ | Or MariaDB 10.3+ |
| Web Server | Apache/Nginx | With mod_rewrite |
| Node.js | 18+ | Only for Mentta Live (optional) |

### API Keys (Optional)

| Service | Purpose | Get Free Key |
|---------|---------|--------------|
| Google Gemini | AI responses | [aistudio.google.com](https://aistudio.google.com) |
| Google Maps | Map with directions | [console.cloud.google.com](https://console.cloud.google.com) |

> 💡 **Note:** Mentta works without API keys using fallback modes (AI dev mode + Leaflet maps)

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/alexis-campos/Mentta---Saving-lives-with-AI.git
cd Mentta---Saving-lives-with-AI
```

### 2. Setup environment

```bash
# Copy environment template
cp .env.example .env

# Edit with your configuration
nano .env
```

### 3. Create database

```bash
# Using MySQL CLI
mysql -u root -p < database/schema.sql
mysql -u root -p mentta < database/seed.sql
```

Or via **phpMyAdmin**:
1. Create database named `mentta`
2. Import `database/schema.sql`
3. Import `database/seed.sql` (optional - adds test data)

### 4. Configure web server

For **XAMPP/WAMP/LAMP**:
```bash
# Symlink or copy to htdocs
ln -s /path/to/Mentta---Saving-lives-with-AI /opt/lampp/htdocs/mentta
```

For **Apache** (vhost):
```apache
<VirtualHost *:80>
    ServerName mentta.local
    DocumentRoot /var/www/mentta
    <Directory /var/www/mentta>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Access the application

```
http://localhost/mentta/login.php
```

**Test Credentials:**
| Role | Email | Password |
|------|-------|----------|
| Patient | carlos@test.com | password123 |
| Psychologist | dra.martinez@mentta.com | password123 |

---

## 🔐 Environment Variables

Create a `.env` file in the project root:

```env
# ===========================================
# DATABASE CONFIGURATION
# ===========================================
DB_HOST=localhost
DB_NAME=mentta
DB_USER=root
DB_PASS=your_password

# ===========================================
# AI CONFIGURATION (Google Gemini)
# ===========================================
AI_API_KEY=your_gemini_api_key
AI_MODEL=gemini-2.0-flash
# Optional: AI_TIMEOUT=45

# ===========================================
# MAPS CONFIGURATION (Optional)
# ===========================================
# Leave empty to use Leaflet/OpenStreetMap fallback
GOOGLE_MAPS_API_KEY=

# ===========================================
# APPLICATION SETTINGS
# ===========================================
APP_ENV=development
APP_URL=http://localhost/mentta

# ===========================================
# SECURITY (Production)
# ===========================================
# APP_ENV=production
# (enables HTTPS cookies, stricter validation)
```

> ⚠️ **Never commit your `.env` file to version control!**

---

## 📁 Project Structure

```
mentta/
├── api/
│   ├── chat/                   # Chat API
│   │   ├── send-message.php    # Main chat endpoint
│   │   ├── get-history.php     # Message history
│   │   └── get-chat-list.php   # Sessions for sidebar
│   ├── crisis/                 # Crisis management
│   │   └── escalate.php        # Emergency notifications
│   ├── map/                    # Map API
│   │   ├── get-nearby-centers.php
│   │   └── search-centers.php
│   ├── patient/                # Patient settings
│   └── psychologist/           # Dashboard API
│       ├── check-alerts.php    # Long polling for alerts
│       └── get-patients.php
├── assets/
│   ├── css/
│   │   ├── theme.css           # Dark/Light mode + variables
│   │   ├── chat.css            # Chat interface
│   │   └── map.css             # Map styles
│   ├── js/
│   │   ├── chat.js             # Chat logic + indicators
│   │   ├── menu.js             # Hamburger menu + modals
│   │   ├── dashboard.js        # Psychologist dashboard
│   │   ├── map.js              # Google Maps integration
│   │   └── utils.js            # Shared utilities
│   └── sounds/
│       └── alert.mp3           # Alert notification
├── database/
│   ├── schema.sql              # Full database schema
│   ├── seed.sql                # Test data
│   └── migrations/             # Migration files
├── includes/
│   ├── config.php              # App configuration
│   ├── db.php                  # Database helper
│   ├── auth.php                # Authentication
│   ├── functions.php           # Utility functions
│   ├── ai-client.php           # Gemini API client
│   ├── ai-analyzer.php         # Risk/sentiment analysis
│   ├── circuit-breaker.php     # Fault tolerance
│   └── risk-detector.php       # Risk level mapping
├── multimodal/                 # 🆕 Mentta Live (React/TypeScript)
│   ├── App.tsx                 # Video call interface
│   └── components/
├── tests/                      # PHPUnit tests
│   └── MenttaTest.php
├── chat.php                    # Patient chat interface
├── map.php                     # Mental health centers map
├── dashboard.php               # Psychologist dashboard
├── profile.php                 # User settings
├── login.php                   # Authentication
├── register.php                # Registration
├── index.php                   # Landing page
├── .env.example                # Environment template
├── .htaccess                   # Apache rewrite rules
├── phpunit.xml                 # Test configuration
└── README.md
```

---

## 🚨 Alert System Flow

```
Patient sends risky message
        ↓
   AI Analysis (Gemini)
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

**Important:** The patient **NEVER** knows an alert was triggered. The AI responds with extra warmth (Safe Life Mode).

---

## 🗺️ Mental Health Map

The map page shows nearby mental health centers with two modes:

| Mode | Trigger | Features |
|------|---------|----------|
| **Google Maps** | `GOOGLE_MAPS_API_KEY` set | Full Google Maps, directions, search |
| **Leaflet** | No API key | OpenStreetMap tiles, basic markers |

### Marker Colors
- 🔵 **Blue:** Your location
- 🟢 **Green:** Centers using Mentta
- 🟠 **Orange:** 24h Emergency centers
- 🔴 **Red:** Other centers

---

## 🔄 Changelog

### v0.5.3 (Current)
**Major Bug Fixes & Stability**
- Circuit breaker with file fallback for AI failures
- Leaflet map fallback when no Google Maps API key
- Rate limiter optimization (probabilistic cleanup)
- 50+ translation strings for ES/EN
- Input validation for message length
- Chat history search functionality
- Interactive breathing exercises (4-7-8)
- Interactive grounding checklist (5-4-3-2-1)
- Personalized time-based welcome message
- Enhanced panic button visibility
- Session security improvements
- PHPUnit test structure

### v0.5.2
**Interactive Map of Mental Health Centers**
- Full-page map with Google Maps
- 20+ mental health centers in Lima
- Haversine distance calculation
- Filters and search functionality

### v0.5.1
**Bug Fixes**
- Fixed AI model configuration
- Increased AI response limits

### v0.5.0
**Authentication & Landing Page**
- Modern landing page
- Login/Register system
- Role-based protection

### v0.4.0
**Psychologist Dashboard**
- Patient list with status
- Emotional evolution charts
- Real-time alert popup

### v0.3.x
**AI-Powered Analysis**
- Contextual risk detection
- Semantic memory extraction
- Alert notification chain
- Safe Life Mode

---

## 🛡️ Security Notes

| Feature | Implementation |
|---------|----------------|
| API Keys | Stored in `.env` (git-ignored) |
| Passwords | bcrypt hashing |
| SQL | PDO prepared statements |
| XSS | Input sanitization |
| CSRF | Session tokens |
| Rate Limiting | Per-user limits with sliding window |
| Session | Secure cookies, regeneration on login |
| Alerts | Silent (patient never knows) |
| Test Directory | Blocked in production via `.htaccess` |

---

## 🧪 Testing

### Test API Connection
```
http://localhost/mentta/test/test-api.php
```

### PHPUnit Tests
```bash
# Install PHPUnit
composer require --dev phpunit/phpunit:^10

# Run tests
./vendor/bin/phpunit tests/
```

> 💡 **Note:** Tests require `php-xml` extension (`sudo apt install php8.4-xml`)

---

## 🤝 Contributing

This is a private project for academic purposes. For inquiries, contact the development team.

---

## 📄 License

Private project - All rights reserved.

**Universidad Nacional Mayor de San Marcos**  
Facultad de Ingeniería de Sistemas e Informática

---

<p align="center">
  <strong>Mentta</strong> - Saving lives with AI 💜
</p>

<p align="center">
  <sub>Built with ❤️ by the Mentta Team</sub>
</p>
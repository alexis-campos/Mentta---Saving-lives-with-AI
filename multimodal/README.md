# Mentta Live - Implementation Strategy & Architecture

## 1. Viability Analysis
**Is this possible in 7 days?** YES.
The Gemini Live API handles the heavy lifting (ASR, NLU, TTS). Your challenge is integration, not building models.

*   **Feasible:** Bidirectional Audio (High Priority), Risk Detection (via Function Calling), Basic Video Analysis (sending frames).
*   **Postpone:** Complex custom voice cloning (use preset voices), detailed frame-by-frame analysis (send 1fps for context instead), complex user auth migration (keep PHP auth).

## 2. Recommended Architecture
Since you are using PHP/Vanilla JS, the best path for **Low Latency** is a **Client-Side First** approach for the media stream. PHP cannot handle real-time WebSocket audio streaming efficiently.

**Hybrid Architecture:**
1.  **Backend (PHP - Existing):** Handles User Auth, Session Logging, Crisis Alerts (SMS/Email).
2.  **Frontend (React/Vite - New Module):** A dedicated "Live Room" SPA.
    *   Connects directly to Gemini Live API via WebSockets.
    *   Processes Microphone (AudioWorklet) and Camera (Canvas).
    *   Uses **Function Calling** to report risk levels back to your UI/Logic.
    *   On session end: POSTs a summary to your PHP endpoint (`/api/chat/save-session.php`).

## 3. Implementation Plan (7 Days)

*   **Day 1: Audio Core.** Set up React, integrate `GoogleGenAI` Live Client. Get "Hello World" bidirectional audio working.
*   **Day 2: Multimodal (Video).** Add `<video>` capture. Implement the `canvas` loop to send Base64 frames to Gemini.
*   **Day 3: Risk Logic (The "Brain").** Implement Gemini Function Calling (`setRiskLevel`, `triggerProtocol`). This allows the AI to "push" buttons in your code when it detects sadness/suicidal ideation.
*   **Day 4: UI/UX & Empathy.** Build the visualizer (don't show just a black screen). Polish the System Instruction to ensure the AI sounds like a psychologist, not a robot.
*   **Day 5: Integration.** Connect the "End Call" event to your PHP backend (sending the transcript/summary).
*   **Day 6: Fallbacks & Testing.** Handle network drops. Test on mobile (critical for permissions).
*   **Day 7: Polish & Demo.** Record the demo video. Ensure the "Wow" factor (visualizing the AI "seeing" user emotions).

## 4. Considerations & Edge Cases
*   **PHP Integration:** Host this React app on a subdomain (live.mentta.com) or a subfolder. Pass the user ID via URL query param or JWT.
*   **Privacy:** Gemini Live data is processed by Google. For HIPAA, ensure you have the correct BAA with Google Cloud if moving to production. For Hackathon, standard privacy policy applies.
*   **Mobile:** iOS requires user interaction to play audio. Ensure the "Start Call" button is prominent.

## 5. Usage
1.  Add `API_KEY` to your environment (handled via `process.env.API_KEY` in this codebase).
2.  `npm install`
3.  `npm start`

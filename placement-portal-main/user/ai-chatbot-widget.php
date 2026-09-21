<?php
// AI Chatbot Widget — include this file in any page to get the floating placement chatbot
// Usage: <?php include 'ai-chatbot-widget.php'; ?>
?>

<!-- ══════════════════════════════════════════════ -->
<!--   PlaceMentor AI Chatbot — Floating Widget    -->
<!-- ══════════════════════════════════════════════ -->

<style>
/* ── Pulse animation for chat button ── */
@keyframes pm-pulse {
  0%   { box-shadow: 0 0 0 0   rgba(79, 70, 229, 0.55); }
  70%  { box-shadow: 0 0 0 14px rgba(79, 70, 229, 0); }
  100% { box-shadow: 0 0 0 0   rgba(79, 70, 229, 0); }
}
@keyframes pm-bounce-in {
  0%   { transform: scale(0.6) translateY(30px); opacity: 0; }
  60%  { transform: scale(1.05) translateY(-4px); }
  100% { transform: scale(1) translateY(0); opacity: 1; }
}
@keyframes pm-slide-up {
  from { transform: translateY(20px); opacity: 0; }
  to   { transform: translateY(0);    opacity: 1; }
}
@keyframes pm-typing-dot {
  0%, 80%, 100% { transform: scale(0.7); opacity: 0.5; }
  40%            { transform: scale(1.1); opacity: 1; }
}

/* ── Launcher Bubble ── */
#pm-chat-launcher {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 99999;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  box-shadow: 0 8px 28px rgba(79, 70, 229, 0.4);
  animation: pm-pulse 2s infinite;
  transition: transform 0.25s, box-shadow 0.25s;
  outline: none;
}
#pm-chat-launcher:hover {
  transform: scale(1.1);
  box-shadow: 0 12px 36px rgba(79, 70, 229, 0.55);
}
#pm-chat-launcher .pm-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  width: 18px;
  height: 18px;
  background: #ef4444;
  border-radius: 50%;
  border: 2px solid #fff;
  font-size: 9px;
  color: #fff;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── Chat Window ── */
#pm-chat-window {
  position: fixed;
  bottom: 100px;
  right: 28px;
  z-index: 99998;
  width: 380px;
  max-height: 580px;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.18), 0 4px 20px rgba(79,70,229,0.12);
  display: none;
  flex-direction: column;
  overflow: hidden;
  font-family: 'Inter', 'Segoe UI', sans-serif;
}
#pm-chat-window.open {
  display: flex;
  animation: pm-bounce-in 0.35s cubic-bezier(.175,.885,.32,1.1) both;
}

/* Header */
#pm-chat-window .pm-header {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}
.pm-header .pm-avatar {
  width: 40px; height: 40px;
  background: rgba(255,255,255,0.18);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.pm-header .pm-info { flex: 1; }
.pm-header .pm-title {
  font-size: 14px; font-weight: 800; color: #fff; margin: 0; line-height: 1.2;
}
.pm-header .pm-subtitle {
  font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 2px;
  display: flex; align-items: center; gap: 5px;
}
.pm-header .pm-online-dot {
  width: 7px; height: 7px; background: #4ade80;
  border-radius: 50%; display: inline-block;
}
.pm-header-actions { display: flex; gap: 6px; }
.pm-header-btn {
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.25);
  color: #fff;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 11px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}
.pm-header-btn:hover { background: rgba(255,255,255,0.28); }

/* Chips bar */
.pm-chips-bar {
  padding: 8px 14px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  gap: 6px;
  overflow-x: auto;
  flex-shrink: 0;
  scrollbar-width: none;
}
.pm-chips-bar::-webkit-scrollbar { display: none; }
.pm-chip {
  background: #ffffff;
  border: 1.5px solid #c7d2fe;
  color: #4338ca;
  border-radius: 50px;
  padding: 5px 11px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
  flex-shrink: 0;
}
.pm-chip:hover {
  background: #4f46e5;
  color: #fff;
  border-color: #4f46e5;
}

/* Messages area */
#pm-messages {
  flex: 1;
  overflow-y: auto;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #f9fafc;
  scrollbar-width: thin;
  scrollbar-color: #e2e8f0 transparent;
}
#pm-messages::-webkit-scrollbar { width: 4px; }
#pm-messages::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

.pm-msg {
  display: flex;
  align-items: flex-end;
  gap: 7px;
  animation: pm-slide-up 0.25s ease;
}
.pm-msg.user { flex-direction: row-reverse; }

.pm-bubble {
  max-width: 84%;
  padding: 10px 14px;
  border-radius: 16px;
  font-size: 13px;
  line-height: 1.55;
  word-wrap: break-word;
}
.pm-msg.ai .pm-bubble {
  background: #ffffff;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.pm-msg.user .pm-bubble {
  background: linear-gradient(135deg, #4f46e5, #6d28d9);
  color: #ffffff;
  border-bottom-right-radius: 4px;
  box-shadow: 0 4px 12px rgba(79,70,229,0.25);
}
.pm-msg-icon {
  width: 28px; height: 28px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
  flex-shrink: 0;
  background: #ede9fe;
}
.pm-msg.user .pm-msg-icon { background: #c7d2fe; }

/* Typing indicator */
.pm-typing {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 10px 14px;
  background: #fff;
  border-radius: 16px;
  border-bottom-left-radius: 4px;
  border: 1px solid #e2e8f0;
  width: fit-content;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.pm-typing span {
  width: 7px; height: 7px;
  background: #94a3b8;
  border-radius: 50%;
  animation: pm-typing-dot 1.2s infinite ease-in-out;
}
.pm-typing span:nth-child(2) { animation-delay: 0.2s; }
.pm-typing span:nth-child(3) { animation-delay: 0.4s; }

/* Input area */
.pm-footer {
  padding: 12px 14px;
  background: #fff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 8px;
  align-items: flex-end;
  flex-shrink: 0;
}
#pm-input {
  flex: 1;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 9px 13px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  color: #1e293b;
  outline: none;
  resize: none;
  max-height: 100px;
  transition: border 0.2s;
  line-height: 1.4;
}
#pm-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
#pm-send-btn {
  width: 38px; height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: #fff;
  border: none;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
  transition: transform 0.2s, opacity 0.2s;
  flex-shrink: 0;
}
#pm-send-btn:hover { transform: scale(1.08); }
#pm-send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.pm-powered {
  text-align: center;
  font-size: 10px;
  color: #94a3b8;
  padding: 5px 0 8px;
  background: #fff;
}
.pm-powered a { color: #6366f1; text-decoration: none; font-weight: 600; }

/* Responsive */
@media (max-width: 440px) {
  #pm-chat-window { width: calc(100vw - 20px); right: 10px; bottom: 90px; }
  #pm-chat-launcher { right: 16px; bottom: 16px; }
}
</style>

<!-- Launcher Bubble -->
<button id="pm-chat-launcher" onclick="pmToggleChat()" title="Ask PlaceMentor AI">
  <span id="pm-launcher-icon">🤖</span>
  <span class="pm-badge" id="pm-badge">1</span>
</button>

<!-- Chat Window -->
<div id="pm-chat-window">

  <!-- Header -->
  <div class="pm-header">
    <div class="pm-avatar">🤖</div>
    <div class="pm-info">
      <div class="pm-title">PlaceMentor AI</div>
      <div class="pm-subtitle">
        <span class="pm-online-dot"></span> Placement Advisor · Always Online
      </div>
    </div>
    <div class="pm-header-actions">
      <button class="pm-header-btn" onclick="pmClearChat()" title="Clear">🗑</button>
      <button class="pm-header-btn" onclick="window.open('ai-assistant.php','_self')" title="Full screen">⛶</button>
      <button class="pm-header-btn" onclick="pmToggleChat()">✕</button>
    </div>
  </div>

  <!-- Preset Chips -->
  <div class="pm-chips-bar" id="pm-chips">
    <button class="pm-chip" onclick="pmSendPreset('Am I eligible for any active placement drives?')">🎯 Eligibility</button>
    <button class="pm-chip" onclick="pmSendPreset('What skills should I learn for a Software Developer role?')">💻 Skill Roadmap</button>
    <button class="pm-chip" onclick="pmSendPreset('How can I improve my resume ATS score?')">📄 Resume Tips</button>
    <button class="pm-chip" onclick="pmSendPreset('How should I prepare for my technical interview?')">🎤 Interview Prep</button>
    <button class="pm-chip" onclick="pmSendPreset('Which companies are hiring from campus right now?')">🏢 Companies</button>
    <button class="pm-chip" onclick="pmSendPreset('Give me a study plan for placement preparation in 30 days')">📅 Study Plan</button>
  </div>

  <!-- Messages -->
  <div id="pm-messages">
    <!-- Greeting injected by JS -->
  </div>

  <!-- Footer -->
  <div class="pm-footer">
    <textarea id="pm-input" rows="1" placeholder="Ask about placements, skills, companies..." 
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();pmSend();}"
      oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px';"></textarea>
    <button id="pm-send-btn" onclick="pmSend()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"></line>
        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
      </svg>
    </button>
  </div>
  <div class="pm-powered">⚡ Powered by <a href="ai-assistant.php">PlaceMentor AI</a> · Gemini</div>
</div>

<script>
(function() {
  // ── State ──
  let pmIsOpen   = false;
  let pmIsTyping = false;
  let pmHistory  = [];  // {role, content}

  const userName = <?= json_encode(htmlspecialchars($_SESSION['name'] ?? 'there')) ?>;

  // ── DOM refs ──
  const win    = document.getElementById('pm-chat-window');
  const msgs   = document.getElementById('pm-messages');
  const input  = document.getElementById('pm-input');
  const sendBtn= document.getElementById('pm-send-btn');
  const badge  = document.getElementById('pm-badge');
  const launchIcon = document.getElementById('pm-launcher-icon');

  // ── Init: show welcome message ──
  window.addEventListener('load', function() {
    pmAppendAI(`👋 Hi <strong>${userName}</strong>! I'm your <strong>PlaceMentor AI</strong> — your personal campus placement advisor.<br><br>Ask me about drive eligibility, skill roadmaps, resume tips, or interview prep. I'm context-aware and know your profile! 🚀`);
  });

  // ── Toggle ──
  window.pmToggleChat = function() {
    pmIsOpen = !pmIsOpen;
    win.classList.toggle('open', pmIsOpen);
    launchIcon.textContent = pmIsOpen ? '✕' : '🤖';
    if (pmIsOpen) {
      badge.style.display = 'none';
      setTimeout(() => { input.focus(); pmScrollBottom(); }, 300);
    } else {
      launchIcon.textContent = '🤖';
    }
  };

  // ── Clear ──
  window.pmClearChat = function() {
    pmHistory = [];
    msgs.innerHTML = '';
    pmAppendAI(`Chat cleared! How can I help you with your placement journey, <strong>${userName}</strong>? 😊`);
  };

  // ── Preset ──
  window.pmSendPreset = function(text) {
    input.value = text;
    pmSend();
  };

  // ── Send ──
  window.pmSend = function() {
    const text = input.value.trim();
    if (!text || pmIsTyping) return;

    pmAppendUser(text);
    input.value = '';
    input.style.height = 'auto';
    pmHistory.push({ role: 'user', content: text });

    pmShowTyping();
    pmIsTyping = true;
    sendBtn.disabled = true;

    fetch('../php/ai-chat-api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, history: pmHistory.slice(-8) })
    })
    .then(r => r.json())
    .then(data => {
      pmHideTyping();
      pmIsTyping = false;
      sendBtn.disabled = false;

      if (data.status === 'success') {
        const reply = data.reply || 'I could not generate a response. Please try again.';
        pmAppendAI(reply);
        pmHistory.push({ role: 'assistant', content: reply });

        // Show follow-up chips if returned
        if (data.suggested_followups && data.suggested_followups.length) {
          pmShowFollowups(data.suggested_followups);
        }
      } else {
        pmAppendAI(`⚠️ ${data.error || 'Something went wrong. Please try again.'}`);
        pmIsTyping = false;
        sendBtn.disabled = false;
      }
    })
    .catch(() => {
      pmHideTyping();
      pmIsTyping = false;
      sendBtn.disabled = false;
      pmAppendAI('⚠️ Network error. Please check your connection and try again.');
    });
  };

  // ── Append functions ──
  function pmAppendUser(text) {
    const div = document.createElement('div');
    div.className = 'pm-msg user';
    div.innerHTML = `
      <div class="pm-msg-icon">👤</div>
      <div class="pm-bubble">${escHtml(text)}</div>
    `;
    msgs.appendChild(div);
    pmScrollBottom();
  }

  function pmAppendAI(html) {
    const div = document.createElement('div');
    div.className = 'pm-msg ai';
    div.innerHTML = `
      <div class="pm-msg-icon">🤖</div>
      <div class="pm-bubble">${html}</div>
    `;
    msgs.appendChild(div);
    pmScrollBottom();
  }

  function pmShowTyping() {
    const div = document.createElement('div');
    div.className = 'pm-msg ai';
    div.id = 'pm-typing-row';
    div.innerHTML = `
      <div class="pm-msg-icon">🤖</div>
      <div class="pm-typing"><span></span><span></span><span></span></div>
    `;
    msgs.appendChild(div);
    pmScrollBottom();
  }

  function pmHideTyping() {
    document.getElementById('pm-typing-row')?.remove();
  }

  function pmShowFollowups(items) {
    const existing = document.getElementById('pm-dynamic-chips');
    if (existing) existing.remove();

    const wrap = document.createElement('div');
    wrap.id = 'pm-dynamic-chips';
    wrap.style.cssText = 'display:flex;flex-wrap:wrap;gap:6px;padding:4px 14px 10px;';
    items.slice(0, 3).forEach(q => {
      const btn = document.createElement('button');
      btn.className = 'pm-chip';
      btn.textContent = q;
      btn.onclick = () => pmSendPreset(q);
      wrap.appendChild(btn);
    });
    msgs.appendChild(wrap);
    pmScrollBottom();
  }

  function pmScrollBottom() {
    setTimeout(() => { msgs.scrollTop = msgs.scrollHeight; }, 50);
  }

  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }
})();
</script>

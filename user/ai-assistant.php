<?php
session_start();
if (empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit();
}
require_once(__DIR__ . "/../db.php");

if (!function_exists('safe_trim_width')) {
    function safe_trim_width($string, $start, $width, $trimmarker = '...') {
        $string = (string)($string ?? '');
        if (strlen($string) <= $width) {
            return $string;
        }
        $markerLen = strlen($trimmarker);
        return substr($string, $start, max(0, $width - $markerLen)) . $trimmarker;
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($string, $start, $width, $trimmarker = '') {
        return safe_trim_width($string, $start, $width, $trimmarker);
    }
}

$uid = (int)$_SESSION['id_user'];
$uQ  = $conn->query("SELECT * FROM users WHERE id_user='$uid'");
$user = $uQ ? $uQ->fetch_assoc() : [];
$name = htmlspecialchars(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
$firstName = htmlspecialchars($user['firstname'] ?? 'there');
$skills    = htmlspecialchars($user['skills'] ?? 'Not listed');
$qual      = htmlspecialchars($user['qualification'] ?? '');
$stream    = htmlspecialchars($user['stream'] ?? '');
$ug        = htmlspecialchars($user['ug'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Placement Assistant | PlaceMentor</title>
  <meta name="description" content="Chat with PlaceMentor AI for personalized placement advice, company eligibility checks, skill roadmaps, and interview tips.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-dark: #3730a3;
      --secondary: #7c3aed;
      --accent: #06b6d4;
      --success: #10b981;
      --bg: #f0f2ff;
      --card: #ffffff;
      --border: #e2e8f0;
      --muted: #64748b;
      --dark: #0f172a;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Inter', sans-serif; background: var(--bg); }

    /* ── Layout ── */
    .chat-layout {
      display: grid;
      grid-template-columns: 300px 1fr;
      height: 100vh;
      overflow: hidden;
    }

    /* ── Left sidebar ── */
    .chat-sidebar {
      background: linear-gradient(160deg, #312e81 0%, #1e1b4b 100%);
      display: flex;
      flex-direction: column;
      padding: 0;
      overflow: hidden;
    }
    .sidebar-brand {
      padding: 24px 20px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .sidebar-brand h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 22px;
      font-weight: 800;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .sidebar-brand p {
      font-size: 12px;
      color: rgba(255,255,255,0.55);
      margin-top: 4px;
    }

    /* Profile card */
    .profile-card {
      margin: 16px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 14px;
      padding: 16px;
    }
    .profile-card .avatar {
      width: 48px; height: 48px;
      border-radius: 50%;
      background: linear-gradient(135deg, #818cf8, #a78bfa);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 10px;
    }
    .profile-card .p-name {
      font-size: 14px; font-weight: 700; color: #fff;
    }
    .profile-card .p-detail {
      font-size: 11.5px; color: rgba(255,255,255,0.55); margin-top: 3px;
    }
    .profile-card .p-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: rgba(74,222,128,0.15);
      color: #4ade80;
      border: 1px solid rgba(74,222,128,0.3);
      border-radius: 50px;
      padding: 3px 9px;
      font-size: 11px;
      font-weight: 700;
      margin-top: 8px;
    }

    /* Nav links */
    .sidebar-nav {
      padding: 8px 12px;
      flex: 1;
    }
    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      color: rgba(255,255,255,0.65);
      text-decoration: none;
      transition: all 0.2s;
      margin-bottom: 2px;
    }
    .sidebar-nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .sidebar-nav a.active { background: rgba(255,255,255,0.12); color: #fff; }
    .sidebar-nav a .nav-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 14px; }

    .sidebar-footer {
      padding: 14px 16px;
      border-top: 1px solid rgba(255,255,255,0.1);
    }
    .sidebar-footer a {
      display: flex; align-items: center; gap: 8px;
      color: rgba(255,255,255,0.5);
      font-size: 12px;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }
    .sidebar-footer a:hover { color: #fff; }

    /* ── Chat area ── */
    .chat-main {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      background: #f8faff;
    }

    /* Top bar */
    .chat-topbar {
      background: #fff;
      border-bottom: 1px solid var(--border);
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }
    .chat-topbar .topbar-left { display: flex; align-items: center; gap: 12px; }
    .topbar-avatar {
      width: 42px; height: 42px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
    }
    .topbar-title { font-size: 15px; font-weight: 700; color: var(--dark); }
    .topbar-subtitle { font-size: 11.5px; color: var(--muted); display: flex; align-items: center; gap: 5px; }
    .online-dot { width: 7px; height: 7px; background: #4ade80; border-radius: 50%; display: inline-block; }
    .topbar-actions { display: flex; gap: 8px; }
    .topbar-btn {
      background: #f1f5f9;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 7px 14px;
      font-size: 12px;
      font-weight: 700;
      color: var(--muted);
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .topbar-btn:hover { border-color: var(--primary); color: var(--primary); background: #f0f0ff; }

    /* Chips */
    .chips-bar {
      background: #fff;
      border-bottom: 1px solid var(--border);
      padding: 8px 20px;
      display: flex;
      gap: 8px;
      overflow-x: auto;
      flex-shrink: 0;
      scrollbar-width: none;
    }
    .chips-bar::-webkit-scrollbar { display: none; }
    .chip {
      background: #fff;
      border: 1.5px solid #c7d2fe;
      color: #4338ca;
      border-radius: 50px;
      padding: 6px 14px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
      flex-shrink: 0;
    }
    .chip:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* Messages */
    #chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 20px 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      scrollbar-width: thin;
      scrollbar-color: var(--border) transparent;
    }
    #chat-messages::-webkit-scrollbar { width: 4px; }
    #chat-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .msg-row {
      display: flex;
      align-items: flex-end;
      gap: 10px;
      animation: slideUp 0.25s ease;
    }
    .msg-row.user { flex-direction: row-reverse; }
    @keyframes slideUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

    .msg-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 17px; flex-shrink: 0;
    }
    .msg-row.ai .msg-avatar { background: #ede9fe; }
    .msg-row.user .msg-avatar { background: #c7d2fe; }

    .msg-bubble {
      max-width: 72%;
      padding: 12px 16px;
      border-radius: 18px;
      font-size: 13.5px;
      line-height: 1.6;
      word-wrap: break-word;
    }
    .msg-row.ai .msg-bubble {
      background: #fff;
      color: var(--dark);
      border: 1px solid var(--border);
      border-bottom-left-radius: 5px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.05);
    }
    .msg-row.user .msg-bubble {
      background: linear-gradient(135deg, #4f46e5, #6d28d9);
      color: #fff;
      border-bottom-right-radius: 5px;
      box-shadow: 0 4px 16px rgba(79,70,229,0.25);
    }
    .msg-time {
      font-size: 10px;
      color: var(--muted);
      text-align: center;
      margin: 4px 0;
    }

    /* Typing */
    .typing-dots {
      display: flex; gap: 4px; align-items: center;
      padding: 12px 16px;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 18px;
      border-bottom-left-radius: 5px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.05);
      width: fit-content;
    }
    .typing-dots span {
      width: 8px; height: 8px;
      background: #94a3b8;
      border-radius: 50%;
      animation: typingDot 1.3s infinite;
    }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingDot {
      0%, 80%, 100% { transform: scale(0.7); opacity: 0.5; }
      40%            { transform: scale(1.1); opacity: 1; }
    }

    /* Followup chips */
    .followup-chips {
      display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;
    }
    .followup-chip {
      background: #f0f0ff;
      border: 1.5px solid #c7d2fe;
      color: #4338ca;
      border-radius: 50px;
      padding: 5px 12px;
      font-size: 11.5px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .followup-chip:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* Input footer */
    .chat-footer {
      background: #fff;
      border-top: 1px solid var(--border);
      padding: 14px 20px;
      display: flex;
      align-items: flex-end;
      gap: 10px;
      flex-shrink: 0;
    }
    #chat-input {
      flex: 1;
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 12px 16px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      outline: none;
      resize: none;
      max-height: 120px;
      line-height: 1.5;
      color: var(--dark);
      transition: border 0.2s;
    }
    #chat-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
    #send-btn {
      width: 46px; height: 46px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      border: none;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px;
      flex-shrink: 0;
      transition: transform 0.2s, opacity 0.2s;
    }
    #send-btn:hover { transform: scale(1.06); }
    #send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    /* Responsive */
    @media (max-width: 768px) {
      .chat-layout { grid-template-columns: 1fr; }
      .chat-sidebar { display: none; }
    }

    /* Welcome section */
    .welcome-banner {
      background: linear-gradient(135deg, #ede9fe, #e0e7ff);
      border: 1px solid #c4b5fd;
      border-radius: 14px;
      padding: 18px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .welcome-banner .w-icon { font-size: 40px; }
    .welcome-banner h3 { font-size: 16px; font-weight: 800; color: #3730a3; margin-bottom: 4px; }
    .welcome-banner p { font-size: 12.5px; color: #4f46e5; line-height: 1.5; }
  </style>
</head>
<body>

<div class="chat-layout">

  <!-- ── Sidebar ── -->
  <div class="chat-sidebar">
    <div class="sidebar-brand">
      <h1>🎯 PlaceMentor</h1>
      <p>AI-Powered Placement Portal</p>
    </div>

    <!-- Profile Card -->
    <div class="profile-card">
      <div class="avatar"><?= strtoupper(substr($user['firstname'] ?? 'S', 0, 1)) ?></div>
      <div class="p-name"><?= $name ?></div>
      <div class="p-detail"><?= $qual ?><?= $stream ? ' · ' . $stream : '' ?></div>
      <?php if ($ug): ?>
        <div class="p-detail">Academic Score: <?= $ug ?>%</div>
      <?php endif; ?>
      <div class="p-badge"><span>●</span> Skills: <?= safe_trim_width($skills, 0, 30, '...') ?></div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <a href="index.php"><span class="nav-icon">🏠</span> Dashboard</a>
      <a href="ai-assistant.php" class="active"><span class="nav-icon">🤖</span> AI Chat Assistant</a>
      <a href="ai-mock-interview.php"><span class="nav-icon">🎤</span> Mock Interview</a>
      <a href="skill-gap-analysis.php"><span class="nav-icon">🔍</span> Skill Gap Analysis</a>
      <a href="ai-job-recommendations.php"><span class="nav-icon">💼</span> Drive Match</a>
      <a href="resume-review.php"><span class="nav-icon">📊</span> ATS Review</a>
      <a href="ai-resume-builder.php"><span class="nav-icon">📄</span> Resume Builder</a>
      <a href="edit-profile.php"><span class="nav-icon">✏️</span> Edit Profile</a>
    </nav>

    <div class="sidebar-footer">
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

  <!-- ── Chat Main ── -->
  <div class="chat-main">

    <!-- Top Bar -->
    <div class="chat-topbar">
      <div class="topbar-left">
        <div class="topbar-avatar">🤖</div>
        <div>
          <div class="topbar-title">PlaceMentor AI Career Assistant</div>
          <div class="topbar-subtitle">
            <span class="online-dot"></span>
            Context-Aware · Powered by Gemini · Always Online
          </div>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="topbar-btn" onclick="clearChat()"><i class="fas fa-trash-alt"></i> Clear</button>
        <a class="topbar-btn" href="index.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
      </div>
    </div>

    <!-- Quick Chips -->
    <div class="chips-bar">
      <button class="chip" onclick="sendPreset('Am I eligible for any active placement drives based on my profile?')">🎯 Drive Eligibility</button>
      <button class="chip" onclick="sendPreset('What skills should I learn to become a Software Developer?')">💻 Skill Roadmap</button>
      <button class="chip" onclick="sendPreset('How can I improve my resume for ATS?')">📄 Resume Tips</button>
      <button class="chip" onclick="sendPreset('How should I prepare for a technical interview?')">🎤 Interview Prep</button>
      <button class="chip" onclick="sendPreset('Which companies visit campus for placements?')">🏢 Companies</button>
      <button class="chip" onclick="sendPreset('Give me a 30-day placement preparation plan')">📅 Study Plan</button>
      <button class="chip" onclick="sendPreset('What is the difference between HR and technical rounds?')">❓ HR vs Tech Round</button>
      <button class="chip" onclick="sendPreset('How to write a strong cover letter for campus placements?')">📝 Cover Letter</button>
    </div>

    <!-- Messages -->
    <div id="chat-messages">
      <!-- Welcome banner -->
      <div class="welcome-banner">
        <span class="w-icon">🤖</span>
        <div>
          <h3>Hello <?= $firstName ?>! I'm your PlaceMentor AI Career Advisor</h3>
          <p>I know your profile — <strong><?= $qual ?><?= $stream ? ' in ' . $stream : '' ?></strong>, Score: <strong><?= $ug ?: 'N/A' ?></strong>, Skills: <strong><?= safe_trim_width($skills, 0, 60, '...') ?></strong>. Ask me anything about your placement journey!</p>
        </div>
      </div>

      <!-- AI greeting -->
      <div class="msg-row ai" id="greeting-msg">
        <div class="msg-avatar">🤖</div>
        <div>
          <div class="msg-bubble">
            👋 Hi <strong><?= $firstName ?></strong>! I'm your personalized placement advisor. I have access to your full profile details so I can give you highly specific, actionable advice.<br><br>
            Here's what I can help you with:<br>
            • 🎯 Check your eligibility for active company drives<br>
            • 💻 Get a custom skill roadmap for your target role<br>
            • 📄 Get AI tips to improve your resume & ATS score<br>
            • 🎤 Prepare for technical & HR interview rounds<br>
            • 📅 Create a personalized preparation plan<br><br>
            What would you like help with today?
          </div>
          <div class="followup-chips">
            <button class="followup-chip" onclick="sendPreset('Am I eligible for any active placement drives?')">Check my eligibility</button>
            <button class="followup-chip" onclick="sendPreset('What skills should I add to my profile?')">What skills to learn?</button>
            <button class="followup-chip" onclick="sendPreset('How to crack a placement interview?')">Interview tips</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="chat-footer">
      <textarea id="chat-input" rows="1" 
        placeholder="Ask about eligibility, skills, interview prep, companies..."
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"
        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px';"></textarea>
      <button id="send-btn" onclick="sendMessage()">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>

  </div><!-- /chat-main -->
</div><!-- /chat-layout -->

<script>
const messagesEl = document.getElementById('chat-messages');
const inputEl    = document.getElementById('chat-input');
const sendBtnEl  = document.getElementById('send-btn');
let isTyping = false;
let history  = [];

function sendPreset(text) {
  inputEl.value = text;
  sendMessage();
}

function sendMessage() {
  const msg = inputEl.value.trim();
  if (!msg || isTyping) return;

  appendUser(msg);
  history.push({ role: 'user', content: msg });
  inputEl.value = '';
  inputEl.style.height = 'auto';

  showTyping();
  isTyping = true;
  sendBtnEl.disabled = true;

  fetch('../php/ai-chat-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message: msg, history: history.slice(-10) })
  })
  .then(r => r.json())
  .then(data => {
    hideTyping();
    isTyping = false;
    sendBtnEl.disabled = false;

    if (data.status === 'success') {
      const reply = data.reply || 'I could not generate a response.';
      appendAI(reply, data.suggested_followups || []);
      history.push({ role: 'assistant', content: reply });
    } else {
      appendAI(`⚠️ ${data.error || 'Something went wrong. Please try again.'}`, []);
    }
  })
  .catch(() => {
    hideTyping();
    isTyping = false;
    sendBtnEl.disabled = false;
    appendAI('⚠️ Network error. Please check your connection.', []);
  });
}

function appendUser(text) {
  const row = document.createElement('div');
  row.className = 'msg-row user';
  row.innerHTML = `
    <div class="msg-avatar">👤</div>
    <div class="msg-bubble">${esc(text)}</div>
  `;
  messagesEl.appendChild(row);
  scroll();
}

function appendAI(html, followups) {
  const row = document.createElement('div');
  row.className = 'msg-row ai';

  let followupHtml = '';
  if (followups && followups.length) {
    followupHtml = `<div class="followup-chips">${followups.slice(0, 3).map(q => 
      `<button class="followup-chip" onclick="sendPreset(${JSON.stringify(q)})">${esc(q)}</button>`
    ).join('')}</div>`;
  }

  row.innerHTML = `
    <div class="msg-avatar">🤖</div>
    <div>
      <div class="msg-bubble">${html}</div>
      ${followupHtml}
    </div>
  `;
  messagesEl.appendChild(row);
  scroll();
}

function showTyping() {
  const row = document.createElement('div');
  row.className = 'msg-row ai';
  row.id = 'typing-row';
  row.innerHTML = `
    <div class="msg-avatar">🤖</div>
    <div class="typing-dots"><span></span><span></span><span></span></div>
  `;
  messagesEl.appendChild(row);
  scroll();
}

function hideTyping() {
  document.getElementById('typing-row')?.remove();
}

function clearChat() {
  history = [];
  messagesEl.innerHTML = `
    <div class="msg-row ai">
      <div class="msg-avatar">🤖</div>
      <div class="msg-bubble">Chat cleared! I'm ready to help you again, <strong><?= $firstName ?></strong>. What would you like to know? 😊</div>
    </div>`;
}

function scroll() {
  setTimeout(() => { messagesEl.scrollTop = messagesEl.scrollHeight; }, 60);
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}
</script>

</body>
</html>

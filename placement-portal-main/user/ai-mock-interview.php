<?php
session_start();

if (empty($_SESSION['id_user'])) {
  header("Location: ../index.php");
  exit();
}

require_once("../db.php");
require_once("../php/gemini-api.php");

// Fetch User Profile
$uQ = $conn->query("SELECT * FROM users WHERE id_user='$_SESSION[id_user]'");
if (!$uQ || $uQ->num_rows == 0) {
  header("Location: edit-profile.php");
  exit();
}
$userProfile = $uQ->fetch_assoc();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AI Mock Interview Simulator | PlaceMentor</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap">
  <style>
    body { font-family: 'Inter', sans-serif; background: #f4f6ff; }
    h1, h2, h3, h4, .brand-font { font-family: 'Outfit', sans-serif; }
    
    .card-mi { background:#ffffff; border-radius:20px; box-shadow:0 12px 40px rgba(79,70,229,0.08); border:1px solid #e2e8f0; padding:32px; margin-bottom:24px; }
    .badge-score-lg { font-size:36px; font-weight:900; color:#4f46e5; }
    
    .role-btn { background:#f8fafc; color:#475569; font-weight:700; font-size:14px; padding:12px 20px; border-radius:12px; border:2px solid #cbd5e1; cursor:pointer; transition:all 0.2s; display:inline-block; margin:6px; }
    .role-btn:hover, .role-btn.active { background:#4f46e5; color:#ffffff; border-color:#4f46e5; box-shadow:0 6px 18px rgba(79,70,229,0.25); }
    
    .q-card { background:#f8fafc; border-left:5px solid #4f46e5; border-radius:14px; padding:20px; margin-bottom:20px; border:1px solid #e2e8f0; }
    .progress-bar-mi { height:12px; border-radius:50px; background:linear-gradient(90deg, #4f46e5, #7c3aed); }
    
    .score-box { background:#f8fafc; border-radius:14px; padding:20px; text-align:center; border:1px solid #e2e8f0; height:100%; }
    .score-val { font-size:32px; font-weight:900; color:#0f172a; margin-top:4px; }
  </style>
</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">

    <?php include 'header.php'; ?>

    <div class="content-wrapper" style="margin-left: 0px;">
      <section class="content-header">
        <div class="container">
          <div class="row">
            
            <!-- Sidebar -->
            <div class="col-md-3">
              <div class="box box-solid">
                <div class="box-header with-border">
                  <h3 class="box-title">Welcome <b><?php echo htmlspecialchars($_SESSION['name']); ?></b></h3>
                </div>
                <div class="box-body no-padding">
                  <ul class="nav nav-pills nav-stacked">
                    <li><a href="edit-profile.php"><i class="fa fa-user"></i> Edit Profile</a></li>
                    <li><a href="ai-assistant.php"><i class="fa fa-comments text-indigo"></i> AI Career Assistant</a></li>
                    <li class="active"><a href="ai-mock-interview.php"><i class="fa fa-microphone text-purple"></i> AI Mock Interview</a></li>
                    <li><a href="skill-gap-analysis.php"><i class="fa fa-bullseye"></i> AI Skill Gap Analysis</a></li>
                    <li><a href="ai-job-recommendations.php"><i class="fa fa-briefcase"></i> AI Drive Match</a></li>
                    <li><a href="resume-review.php"><i class="fa fa-file-text-o"></i> AI Resume ATS Review</a></li>
                    <li><a href="index.php"><i class="fa fa-address-card-o"></i> My Applications</a></li>
                    <li><a href="mailbox.php"><i class="fa fa-envelope"></i> Mailbox</a></li>
                    <li><a href="settings.php"><i class="fa fa-gear"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Main AI Mock Interview Container -->
            <div class="col-md-9">
              
              <!-- STEP 1: ROLE SELECTION SCREEN -->
              <div id="setupScreen" class="card-mi">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
                  <div>
                    <h2 style="margin:0;font-weight:800;color:#0f172a;" class="brand-font">🎤 AI Mock Interview Simulator</h2>
                    <p class="text-muted" style="margin-top:4px;">Practice simulated technical interviews with real-time AI scoring on Technical Accuracy, Answer Relevance & Communication</p>
                  </div>
                  <span class="badge" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:8px 14px;font-size:12px;">⚡ Interactive AI Evaluator</span>
                </div>

                <div style="margin-bottom:24px;">
                  <h4 style="font-weight:700;color:#1e293b;" class="brand-font">1. Select Interview Target Role:</h4>
                  <div id="roleSelector">
                    <div class="role-btn active" data-role="Java Developer">💻 Java Developer</div>
                    <div class="role-btn" data-role="Software Engineer">⚙️ Software Engineer</div>
                    <div class="role-btn" data-role="Full Stack Developer">🌐 Full Stack Developer</div>
                    <div class="role-btn" data-role="Python / Data Analyst">📊 Python / Data Analyst</div>
                    <div class="role-btn" data-role="DevOps Engineer">☁️ DevOps Engineer</div>
                    <div class="role-btn" data-role="Frontend Developer">🎨 Frontend Developer</div>
                  </div>
                </div>

                <div style="background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #e2e8f0;margin-bottom:24px;">
                  <h4 style="margin-top:0;font-weight:700;color:#0f172a;"><i class="fa fa-info-circle text-indigo"></i> What to expect:</h4>
                  <ul style="margin-bottom:0;padding-left:18px;color:#475569;font-size:13px;line-height:1.7;">
                    <li>You will be asked <strong>4 tailored technical questions</strong> for your selected role.</li>
                    <li>You can listen to the question using <strong>Text-to-Speech (Audio)</strong> or speak your answer via <strong>Voice Input</strong>.</li>
                    <li>After completing the session, AI will evaluate your <strong>Technical Accuracy, Relevance, and Communication</strong> with a detailed question-by-question report.</li>
                  </ul>
                </div>

                <div style="text-align:center;">
                  <button id="btnStartInterview" class="btn btn-indigo btn-lg" style="background:#4f46e5;color:#ffffff;font-weight:700;padding:14px 36px;border-radius:10px;box-shadow:0 6px 20px rgba(79,70,229,0.3);">
                    <i class="fa fa-play-circle"></i> Start AI Mock Interview Now
                  </button>
                </div>
              </div>

              <!-- STEP 2: LIVE INTERVIEW SESSION SCREEN -->
              <div id="sessionScreen" class="card-mi" style="display:none;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                  <div>
                    <span id="roleBadge" class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:700;font-size:12px;padding:6px 14px;border-radius:50px;">Java Developer Interview</span>
                    <h3 id="questionProgress" style="margin:6px 0 0 0;font-weight:800;color:#0f172a;" class="brand-font">Question 1 of 4</h3>
                  </div>
                  <div>
                    <button id="btnListen" class="btn btn-default" style="border-radius:8px;font-weight:600;"><i class="fa fa-volume-up text-indigo"></i> 🔊 Listen to Question</button>
                  </div>
                </div>

                <div style="margin-bottom:16px;">
                  <div style="height:8px;background:#e2e8f0;border-radius:50px;overflow:hidden;">
                    <div id="sessionProgressBar" class="progress-bar-mi" style="width:25%;"></div>
                  </div>
                </div>

                <!-- Question Card -->
                <div class="q-card">
                  <h4 style="margin-top:0;font-weight:700;color:#312e81;" class="brand-font">🤖 Interviewer AI Question:</h4>
                  <p id="questionText" style="font-size:16px;color:#1e293b;font-weight:600;margin-bottom:0;line-height:1.6;">
                    Loading question...
                  </p>
                </div>

                <!-- Candidate Answer Area -->
                <div style="margin-bottom:20px;">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <label style="font-size:14px;color:#0f172a;font-weight:700;">Your Technical Answer:</label>
                    <button id="btnVoiceInput" class="btn btn-warning btn-xs" style="border-radius:50px;font-weight:700;padding:4px 12px;">
                      <i class="fa fa-microphone"></i> 🎙️ Speak Answer (Voice-to-Text)
                    </button>
                  </div>

                  <textarea id="answerText" class="form-control input-lg" rows="5" style="border-radius:12px;font-size:14px;line-height:1.6;" placeholder="Type or speak your technical answer here..."></textarea>
                  <small style="color:#64748b;margin-top:6px;display:block;">Tip: Be specific, explain core logic, and mention relevant frameworks or technical principles.</small>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <button id="btnPrevQ" class="btn btn-default" style="border-radius:8px;font-weight:600;" disabled><i class="fa fa-arrow-left"></i> Previous</button>
                  <button id="btnNextQ" class="btn btn-indigo btn-lg" style="background:#4f46e5;color:#fff;border-radius:8px;font-weight:700;padding:10px 28px;">
                    Next Question &rarr;
                  </button>
                </div>
              </div>

              <!-- STEP 3: FINAL AI EVALUATION REPORT SCREEN -->
              <div id="reportScreen" class="card-mi" style="display:none;">
                
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #e2e8f0;">
                  <div>
                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;color:#64748b;letter-spacing:.06em;">Interview Performance Report</span>
                    <h2 id="reportRoleTitle" style="margin:2px 0 0 0;font-weight:800;color:#0f172a;" class="brand-font">Java Developer Interview Evaluation</h2>
                  </div>
                  
                  <div style="text-align:right;">
                    <span id="reportVerdictBadge" style="background:#dcfce7;color:#15803d;font-weight:800;font-size:14px;padding:6px 16px;border-radius:50px;">
                      🌟 Strong Hire
                    </span>
                    <div id="reportOverallScore" class="badge-score-lg">
                      85<span style="font-size:18px;color:#64748b;">/100</span>
                    </div>
                  </div>
                </div>

                <!-- 3 Dimension Scores -->
                <div class="row" style="margin-bottom:24px;">
                  <div class="col-md-4" style="margin-bottom:12px;">
                    <div class="score-box">
                      <small style="color:#64748b;font-weight:700;text-transform:uppercase;">Technical Accuracy</small>
                      <div id="scoreTech" class="score-val" style="color:#4f46e5;">88%</div>
                    </div>
                  </div>
                  <div class="col-md-4" style="margin-bottom:12px;">
                    <div class="score-box">
                      <small style="color:#64748b;font-weight:700;text-transform:uppercase;">Answer Relevance</small>
                      <div id="scoreRel" class="score-val" style="color:#059652;">85%</div>
                    </div>
                  </div>
                  <div class="col-md-4" style="margin-bottom:12px;">
                    <div class="score-box">
                      <small style="color:#64748b;font-weight:700;text-transform:uppercase;">Communication & Tone</small>
                      <div id="scoreComm" class="score-val" style="color:#7c3aed;">90%</div>
                    </div>
                  </div>
                </div>

                <!-- AI Summary -->
                <div style="background:#f8fafc;border-left:4px solid #4f46e5;padding:18px;border-radius:10px;margin-bottom:24px;">
                  <h4 style="margin-top:0;font-weight:700;color:#0f172a;">🤖 Interviewer Summary:</h4>
                  <p id="reportSummary" style="color:#475569;margin-bottom:0;line-height:1.6;font-size:14px;">
                    Loading report...
                  </p>
                </div>

                <!-- Strengths vs Areas for Improvement -->
                <div class="row" style="margin-bottom:28px;">
                  <div class="col-md-6" style="margin-bottom:16px;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:18px;height:100%;">
                      <h4 style="margin-top:0;color:#166534;font-weight:700;">✓ Key Strengths</h4>
                      <ul id="listStrengths" style="padding-left:18px;margin-bottom:0;font-size:13px;color:#15803d;line-height:1.6;"></ul>
                    </div>
                  </div>
                  <div class="col-md-6" style="margin-bottom:16px;">
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:18px;height:100%;">
                      <h4 style="margin-top:0;color:#c2410c;font-weight:700;">⚡ Areas for Improvement</h4>
                      <ul id="listImprovements" style="padding-left:18px;margin-bottom:0;font-size:13px;color:#9a3412;line-height:1.6;"></ul>
                    </div>
                  </div>
                </div>

                <!-- Question-by-Question Evaluation Accordion -->
                <div style="margin-bottom:24px;">
                  <h3 style="margin-top:0;font-weight:800;color:#0f172a;" class="brand-font">Detailed Question Breakdown & Ideal Answers</h3>
                  <div id="questionBreakdownContainer"></div>
                </div>

                <div style="text-align:center;margin-top:28px;">
                  <button onclick="location.reload();" class="btn btn-indigo btn-lg" style="background:#4f46e5;color:#fff;border-radius:8px;font-weight:700;padding:12px 32px;">
                    <i class="fa fa-refresh"></i> Take Another Mock Interview
                  </button>
                </div>

              </div>

            </div>

          </div>
        </div>
      </section>
    </div>

    <footer class="main-footer" style="margin-left: 0px;">
      <div class="text-center">
        <strong>Copyright &copy; 2025 <a href="learningfromscratch.online">Placement Portal</a>.</strong> All rights reserved.
      </div>
    </footer>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="../js/adminlte.min.js"></script>

  <script>
    // Role questions repository
    const questionBank = {
      'Java Developer': [
        "Explain Object-Oriented Programming (OOP) principles in Java and how inheritance differs from interface implementation.",
        "What is the Java Collections Framework? Explain the difference between HashMap, ConcurrentHashMap, and TreeMap.",
        "How does Exception Handling work in Java? What is the difference between checked and unchecked exceptions?",
        "Explain how Spring Boot handles Dependency Injection and describe building a RESTful API with `@RestController`."
      ],
      'Software Engineer': [
        "Explain the Big-O time and space complexity of common sorting algorithms (QuickSort, MergeSort) and Hash Table lookups.",
        "What are SOLID design principles in software engineering? Give a practical example of Single Responsibility.",
        "Explain relational database normalization vs indexing. How do indexes improve SQL query speed?",
        "How do you manage version control with Git when collaborating on feature branches and resolving merge conflicts?"
      ],
      'Full Stack Developer': [
        "Explain how asynchronous JavaScript (Promises and Async/Await) works with the Event Loop.",
        "What is state management in React? Compare `useState`/`useContext` with global state stores.",
        "How do you secure REST APIs built in Node.js / Express using JWT authentication?",
        "Explain how Docker containers simplify full-stack web application deployment."
      ],
      'Python / Data Analyst': [
        "Explain key Pandas data structures (Series, DataFrame) and how to handle missing data.",
        "What is the difference between Supervised and Unsupervised Machine Learning algorithms?",
        "How do SQL Window Functions (`ROW_NUMBER()`, `RANK()`) assist in analytical queries?",
        "Explain how you visualize data trends and communicate insights to non-technical stakeholders."
      ],
      'DevOps Engineer': [
        "Explain Docker architecture (Client, Daemon, Image, Container) and multi-stage builds.",
        "What is Kubernetes? Explain Pods, Deployments, and Services in container orchestration.",
        "How do you construct a CI/CD pipeline in GitHub Actions or Jenkins?",
        "Explain Infrastructure as Code (IaC) principles and cloud resource security."
      ],
      'Frontend Developer': [
        "Explain semantic HTML5, CSS Flexbox vs CSS Grid layout systems.",
        "What is the Virtual DOM in React and how does reconciliation work?",
        "Explain TypeScript interfaces vs types and why type safety prevents runtime errors.",
        "How do you optimize web app performance and reduce page load times?"
      ]
    };

    let currentRole = 'Java Developer';
    let currentQuestions = [];
    let currentQIndex = 0;
    let userAnswers = [];

    // Role selection handlers
    document.querySelectorAll('.role-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentRole = this.getAttribute('data-role');
      });
    });

    document.getElementById('btnStartInterview').addEventListener('click', function() {
      currentQuestions = questionBank[currentRole] || questionBank['Java Developer'];
      currentQIndex = 0;
      userAnswers = new Array(currentQuestions.length).fill('');

      document.getElementById('setupScreen').style.display = 'none';
      document.getElementById('sessionScreen').style.display = 'block';
      document.getElementById('roleBadge').innerText = `${currentRole} Interview`;

      loadQuestion(0);
    });

    function loadQuestion(index) {
      currentQIndex = index;
      document.getElementById('questionProgress').innerText = `Question ${index + 1} of ${currentQuestions.length}`;
      document.getElementById('sessionProgressBar').style.width = `${((index + 1) / currentQuestions.length) * 100}%`;
      document.getElementById('questionText').innerText = currentQuestions[index];
      document.getElementById('answerText').value = userAnswers[index] || '';

      document.getElementById('btnPrevQ').disabled = (index === 0);
      document.getElementById('btnNextQ').innerText = (index === currentQuestions.length - 1) ? 'Submit Interview & Evaluate 🚀' : 'Next Question \u2192';
    }

    document.getElementById('btnPrevQ').addEventListener('click', function() {
      userAnswers[currentQIndex] = document.getElementById('answerText').value;
      if (currentQIndex > 0) loadQuestion(currentQIndex - 1);
    });

    document.getElementById('btnNextQ').addEventListener('click', function() {
      userAnswers[currentQIndex] = document.getElementById('answerText').value;

      if (currentQIndex < currentQuestions.length - 1) {
        loadQuestion(currentQIndex + 1);
      } else {
        submitInterview();
      }
    });

    // Text-to-Speech audio button
    document.getElementById('btnListen').addEventListener('click', function() {
      if ('speechSynthesis' in window) {
        const text = currentQuestions[currentQIndex];
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.95;
        window.speechSynthesis.speak(utterance);
      } else {
        alert('Text-to-speech is not supported in this browser.');
      }
    });

    // Voice Input (Speech Recognition) button
    document.getElementById('btnVoiceInput').addEventListener('click', function() {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (SpeechRecognition) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'en-US';
        recognition.start();

        const btn = this;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Listening... Speak now!';

        recognition.onresult = function(event) {
          const transcript = event.results[0][0].transcript;
          const currentVal = document.getElementById('answerText').value;
          document.getElementById('answerText').value = currentVal ? `${currentVal} ${transcript}` : transcript;
          btn.innerHTML = '<i class="fa fa-microphone"></i> 🎙️ Speak Answer (Voice-to-Text)';
        };

        recognition.onerror = function() {
          btn.innerHTML = '<i class="fa fa-microphone"></i> 🎙️ Speak Answer (Voice-to-Text)';
        };
      } else {
        alert('Speech Recognition (Voice input) is supported in Chrome, Edge, and modern browsers.');
      }
    });

    function submitInterview() {
      document.getElementById('sessionScreen').style.display = 'none';
      document.getElementById('reportScreen').style.display = 'block';

      const qaPairs = currentQuestions.map((q, i) => ({
        question: q,
        user_answer: userAnswers[i] || ''
      }));

      fetch('../php/ai-mock-interview-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          target_role: currentRole,
          qa_pairs: qaPairs
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          document.getElementById('reportRoleTitle').innerText = `${currentRole} Interview Evaluation`;
          document.getElementById('reportVerdictBadge').innerText = data.verdict || '🌟 Completed';
          document.getElementById('reportOverallScore').innerHTML = `${data.overall_score || 80}<span style="font-size:18px;color:#64748b;">/100</span>`;
          document.getElementById('scoreTech').innerText = `${data.technical_score || 80}%`;
          document.getElementById('scoreRel').innerText = `${data.relevance_score || 80}%`;
          document.getElementById('scoreComm').innerText = `${data.communication_score || 85}%`;
          document.getElementById('reportSummary').innerText = data.summary || 'Simulated interview evaluation completed successfully.';

          // Strengths
          const strUl = document.getElementById('listStrengths');
          strUl.innerHTML = '';
          (data.strengths || []).forEach(s => {
            strUl.innerHTML += `<li>${s}</li>`;
          });

          // Improvements
          const impUl = document.getElementById('listImprovements');
          impUl.innerHTML = '';
          (data.improvements || []).forEach(i => {
            impUl.innerHTML += `<li>${i}</li>`;
          });

          // Question breakdown
          const container = document.getElementById('questionBreakdownContainer');
          container.innerHTML = '';
          (data.question_evaluations || []).forEach((qEval, i) => {
            container.innerHTML += `
              <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:18px;margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                  <strong style="color:#0f172a;font-size:15px;">Q${i+1}: ${qEval.question}</strong>
                  <span class="badge" style="background:#4f46e5;">Score: ${qEval.score}%</span>
                </div>
                <div style="font-size:13px;color:#475569;margin-bottom:8px;">
                  <strong>Your Answer:</strong> <em>${qEval.candidate_answer}</em>
                </div>
                <div style="font-size:13px;color:#059652;margin-bottom:6px;">
                  <strong>AI Feedback:</strong> ${qEval.feedback}
                </div>
                <div style="font-size:13px;color:#1e40af;background:#eff6ff;padding:10px;border-radius:8px;">
                  <strong>💡 Ideal Answer:</strong> ${qEval.ideal_answer}
                </div>
              </div>
            `;
          });
        }
      })
      .catch(err => {
        alert('Error evaluating mock interview. Please try again.');
      });
    }
  </script>
</body>

</html>

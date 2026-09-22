<?php
session_start();
if (isset($_SESSION['id_user']) || isset($_SESSION['id_company'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login — Christ Career Connect</title>
  <link href="../img/christlogo.png" rel="icon">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- Premium CSS -->
  <?php include 'php/head.php' ?>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      font-family: 'Inter', sans-serif;
      background: #09090f;
      color: #f1f5f9;
      overflow-x: hidden;
    }

    /* ── BACKGROUND ── */
    .login-page-bg {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      position: relative;
    }

    /* ── LEFT PANEL ── */
    .login-left {
      background: linear-gradient(145deg, #0c0b18 0%, #16133a 50%, #0a1628 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 48px;
      position: relative;
      overflow: hidden;
    }

    .login-left::before {
      content: '';
      position: absolute;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(79,70,229,0.22) 0%, transparent 65%);
      top: -150px; right: -150px;
      border-radius: 50%;
      animation: blobFloat 8s ease-in-out infinite alternate;
      pointer-events: none;
    }
    .login-left::after {
      content: '';
      position: absolute;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(124,58,237,0.16) 0%, transparent 65%);
      bottom: -100px; left: -100px;
      border-radius: 50%;
      animation: blobFloat 10s ease-in-out infinite alternate-reverse;
      pointer-events: none;
    }

    @keyframes blobFloat {
      0%   { transform: translate(0,0) scale(1); }
      100% { transform: translate(20px,-20px) scale(1.07); }
    }

    .login-brand {
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .login-brand-icon {
      width: 80px; height: 80px;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 36px;
      margin: 0 auto 24px;
      box-shadow: 0 8px 32px rgba(79,70,229,0.45);
      animation: floatIcon 3s ease-in-out infinite alternate;
    }
    @keyframes floatIcon {
      0%   { transform: translateY(0); }
      100% { transform: translateY(-10px); }
    }

    .login-brand h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 32px;
      font-weight: 800;
      background: linear-gradient(135deg, #a5b4fc, #c4b5fd);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 12px;
      letter-spacing: -0.03em;
    }

    .login-brand p {
      color: #64748b;
      font-size: 15px;
      line-height: 1.7;
      max-width: 320px;
    }

    .login-features {
      margin-top: 40px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 320px;
    }

    .login-feature-item {
      display: flex;
      align-items: center;
      gap: 14px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 12px;
      padding: 14px 18px;
      backdrop-filter: blur(10px);
    }

    .login-feature-icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }

    .login-feature-item h4 {
      font-size: 13px;
      font-weight: 700;
      color: #e2e8f0;
      margin-bottom: 2px;
    }

    .login-feature-item p {
      font-size: 11.5px;
      color: #64748b;
      margin: 0;
    }

    /* ── RIGHT PANEL ── */
    .login-right {
      background: #12121e;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 48px;
      position: relative;
    }

    .login-card {
      width: 100%;
      max-width: 400px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(99,102,241,0.20);
      border-radius: 24px;
      padding: 44px 40px;
      backdrop-filter: blur(20px);
      box-shadow: 0 20px 60px rgba(0,0,0,0.40);
      animation: fadeInUp 0.5s cubic-bezier(0.4,0,0.2,1) both;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .login-card-header {
      text-align: center;
      margin-bottom: 36px;
    }

    .login-card-header .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(79,70,229,0.15);
      border: 1px solid rgba(99,102,241,0.30);
      color: #a5b4fc;
      font-size: 11px;
      font-weight: 700;
      padding: 5px 14px;
      border-radius: 50px;
      letter-spacing: 0.08em;
      margin-bottom: 16px;
    }

    .login-card-header h2 {
      font-family: 'Outfit', sans-serif;
      font-size: 26px;
      font-weight: 800;
      color: #f1f5f9 !important;
      margin-bottom: 6px;
      letter-spacing: -0.03em;
    }

    .login-card-header p {
      color: #64748b;
      font-size: 14px;
    }

    /* Form */
    .login-form .form-group {
      margin-bottom: 20px;
    }

    .login-form label {
      font-size: 12.5px;
      font-weight: 600;
      color: #94a3b8;
      display: block;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.07em;
    }

    .login-form .input-wrap {
      position: relative;
    }

    .login-form .input-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #475569;
      font-size: 15px;
      pointer-events: none;
    }

    .login-form input[type="email"],
    .login-form input[type="password"],
    .login-form input[type="text"] {
      width: 100%;
      background: rgba(255,255,255,0.05);
      border: 1.5px solid rgba(255,255,255,0.10);
      border-radius: 10px;
      padding: 13px 14px 13px 42px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      color: #f1f5f9;
      transition: all 0.25s ease;
      outline: none;
    }

    .login-form input:focus {
      border-color: #6366f1;
      background: rgba(99,102,241,0.08);
      box-shadow: 0 0 0 3px rgba(99,102,241,0.20);
    }

    .login-form input::placeholder { color: #334155; }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: #fff;
      border: none;
      border-radius: 50px;
      font-family: 'Inter', sans-serif;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      letter-spacing: 0.03em;
      box-shadow: 0 6px 24px rgba(79,70,229,0.45);
      transition: all 0.25s ease;
      margin-top: 8px;
    }

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 36px rgba(79,70,229,0.60);
    }

    .login-footer-links {
      text-align: center;
      margin-top: 24px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .login-footer-links a {
      color: #6366f1;
      font-size: 13.5px;
      font-weight: 500;
      text-decoration: none;
      transition: color 0.2s;
    }

    .login-footer-links a:hover { color: #a5b4fc; }

    .login-divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,0.07);
      margin: 24px 0;
    }

    /* Alerts */
    .login-alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .login-alert.success {
      background: rgba(5,150,82,0.15);
      border: 1px solid rgba(5,150,82,0.30);
      color: #6ee7b7;
    }
    .login-alert.error {
      background: rgba(225,29,72,0.12);
      border: 1px solid rgba(225,29,72,0.28);
      color: #fca5a5;
    }
    .login-alert.info {
      background: rgba(79,70,229,0.12);
      border: 1px solid rgba(79,70,229,0.28);
      color: #a5b4fc;
    }

    /* Back link top */
    .login-back {
      position: absolute;
      top: 24px;
      left: 24px;
      display: flex;
      align-items: center;
      gap: 6px;
      color: #475569;
      font-size: 13px;
      font-weight: 500;
      text-decoration: none;
      transition: color 0.2s;
    }
    .login-back:hover { color: #a5b4fc; }

    /* Responsive */
    @media (max-width: 768px) {
      .login-page-bg { grid-template-columns: 1fr; }
      .login-left { display: none; }
      .login-right { padding: 40px 24px; }
      .login-card { padding: 32px 24px; }
    }
  </style>
</head>
<body>

<?php include 'php/header.php' ?>

<div class="login-page-bg">
  <!-- LEFT: Brand Panel -->
  <div class="login-left">
    <div class="login-brand">
      <div class="login-brand-icon">🎓</div>
      <h1>Christ Career Connect</h1>
      <p>Your complete placement journey — from resume building to offer letters, all in one powerful platform.</p>
    </div>
    <div class="login-features">
      <div class="login-feature-item">
        <div class="login-feature-icon" style="background:rgba(79,70,229,0.18);">🤖</div>
        <div>
          <h4>AI Career Assistant</h4>
          <p>Get instant answers about drives & eligibility</p>
        </div>
      </div>
      <div class="login-feature-item">
        <div class="login-feature-icon" style="background:rgba(124,58,237,0.18);">🎤</div>
        <div>
          <h4>Mock Interview Simulator</h4>
          <p>Practice with AI-powered interview coaching</p>
        </div>
      </div>
      <div class="login-feature-item">
        <div class="login-feature-icon" style="background:rgba(5,150,82,0.18);">📄</div>
        <div>
          <h4>AI Resume Builder</h4>
          <p>Build ATS-ready resumes in minutes</p>
        </div>
      </div>
      <div class="login-feature-item">
        <div class="login-feature-icon" style="background:rgba(217,119,6,0.18);">🔍</div>
        <div>
          <h4>Skill Gap Analysis</h4>
          <p>Know exactly what to learn next</p>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: Login Form -->
  <div class="login-right">
    <a href="index.php" class="login-back"><i class="fa fa-arrow-left"></i> Back to Home</a>

    <div class="login-card">
      <div class="login-card-header">
        <div class="badge-pill"><i class="fa fa-graduation-cap"></i> STUDENT PORTAL</div>
        <h2>Welcome Back</h2>
        <p>Sign in to your student account</p>
      </div>

      <?php if (isset($_SESSION['registerCompleted'])): ?>
        <div class="login-alert success"><i class="fa fa-check-circle"></i> Registered successfully! Waiting for approval.</div>
        <?php unset($_SESSION['registerCompleted']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['loginError'])): ?>
        <div class="login-alert error"><i class="fa fa-exclamation-circle"></i> Invalid Email or Password. Try again.</div>
        <?php unset($_SESSION['loginError']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['userActivated'])): ?>
        <div class="login-alert info"><i class="fa fa-info-circle"></i> Your account is active. You can now login.</div>
        <?php unset($_SESSION['userActivated']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['loginActiveError'])): ?>
        <div class="login-alert error"><i class="fa fa-exclamation-circle"></i> <?php echo $_SESSION['loginActiveError']; ?></div>
        <?php unset($_SESSION['loginActiveError']); ?>
      <?php endif; ?>

      <form method="post" action="checklogin.php" class="login-form">
        <div class="form-group">
          <label>Email Address</label>
          <div class="input-wrap">
            <i class="fa fa-envelope-o"></i>
            <input type="email" name="email" placeholder="student@christccc.edu" required>
          </div>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="input-wrap">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" placeholder="Enter your password" required>
          </div>
        </div>
        <button type="submit" class="btn-login">Sign In <i class="fa fa-arrow-right"></i></button>
      </form>

      <hr class="login-divider">

      <div class="login-footer-links">
        <a href="register-candidates.php"><i class="fa fa-user-plus"></i> Create new account</a>
        <a href="login.php" style="color:#475569;"><i class="fa fa-exchange"></i> Other login options</a>
      </div>
    </div>
  </div>
</div>

<?php include 'php/footer.php'; ?>
</body>
</html>
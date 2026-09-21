<?php
session_start();
require_once("db.php");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Christ Career Connect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <?php include 'php/head.php' ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { font-family: 'Inter', sans-serif; background: #09090f; color: #f1f5f9; min-height: 100vh; }

    /* PAGE LAYOUT */
    .login-select-page {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: hidden;
    }

    /* BG blobs */
    .login-select-page::before {
      content: '';
      position: fixed;
      width: 800px; height: 800px;
      background: radial-gradient(circle, rgba(79,70,229,0.14) 0%, transparent 65%);
      top: -300px; right: -200px; border-radius: 50%;
      animation: blobFloat 9s ease-in-out infinite alternate; pointer-events: none; z-index: 0;
    }
    .login-select-page::after {
      content: '';
      position: fixed;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(5,150,82,0.10) 0%, transparent 65%);
      bottom: -150px; left: -100px; border-radius: 50%;
      animation: blobFloat 12s ease-in-out infinite alternate-reverse; pointer-events: none; z-index: 0;
    }
    @keyframes blobFloat { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(25px,-25px) scale(1.08); } }

    /* HERO SECTION */
    .hero-section {
      text-align: center;
      padding: 80px 24px 48px;
      position: relative;
      z-index: 1;
    }

    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(79,70,229,0.14); border: 1px solid rgba(99,102,241,0.28);
      color: #a5b4fc; font-size: 11px; font-weight: 700;
      padding: 6px 18px; border-radius: 50px; letter-spacing: 0.08em;
      margin-bottom: 24px;
    }

    .hero-section h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 48px; font-weight: 800; line-height: 1.12;
      background: linear-gradient(135deg, #f1f5f9 0%, #c4b5fd 60%, #6ee7b7 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 16px; letter-spacing: -0.03em;
    }

    .hero-section p {
      color: #475569; font-size: 18px; line-height: 1.7; max-width: 520px; margin: 0 auto;
    }

    /* CARDS GRID */
    .roles-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 24px 80px;
      position: relative;
      z-index: 1;
    }

    .role-card {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 24px;
      padding: 36px 28px;
      text-align: center;
      text-decoration: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      backdrop-filter: blur(16px);
      transition: all 0.28s cubic-bezier(0.4,0,0.2,1);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.5s cubic-bezier(0.4,0,0.2,1) both;
    }

    .role-card:nth-child(1) { animation-delay: 0.0s; }
    .role-card:nth-child(2) { animation-delay: 0.1s; }
    .role-card:nth-child(3) { animation-delay: 0.2s; }

    @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }

    .role-card::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 24px;
      opacity: 0;
      transition: opacity 0.28s ease;
    }
    .role-card:hover::before { opacity: 1; }
    .role-card:hover {
      transform: translateY(-10px);
      border-color: rgba(255,255,255,0.16);
    }

    /* Card 1 – Student */
    .role-card.student::before { background: linear-gradient(145deg, rgba(79,70,229,0.10), rgba(124,58,237,0.06)); }
    .role-card.student:hover { box-shadow: 0 24px 60px rgba(79,70,229,0.30), inset 0 1px 0 rgba(255,255,255,0.10); border-color: rgba(99,102,241,0.40); }

    /* Card 2 – Placement */
    .role-card.placement::before { background: linear-gradient(145deg, rgba(5,150,82,0.10), rgba(16,185,129,0.06)); }
    .role-card.placement:hover { box-shadow: 0 24px 60px rgba(5,150,82,0.26), inset 0 1px 0 rgba(255,255,255,0.10); border-color: rgba(5,150,82,0.40); }

    /* Card 3 – Admin */
    .role-card.admin::before { background: linear-gradient(145deg, rgba(124,58,237,0.10), rgba(79,70,229,0.06)); }
    .role-card.admin:hover { box-shadow: 0 24px 60px rgba(124,58,237,0.30), inset 0 1px 0 rgba(255,255,255,0.10); border-color: rgba(124,58,237,0.40); }

    .role-icon {
      width: 72px; height: 72px;
      border-radius: 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 32px;
      position: relative;
      z-index: 1;
    }

    .role-card.student .role-icon { background: linear-gradient(135deg, rgba(79,70,229,0.20), rgba(124,58,237,0.14)); box-shadow: 0 8px 24px rgba(79,70,229,0.25); }
    .role-card.placement .role-icon { background: linear-gradient(135deg, rgba(5,150,82,0.20), rgba(16,185,129,0.14)); box-shadow: 0 8px 24px rgba(5,150,82,0.25); }
    .role-card.admin .role-icon { background: linear-gradient(135deg, rgba(124,58,237,0.20), rgba(79,70,229,0.14)); box-shadow: 0 8px 24px rgba(124,58,237,0.25); }

    .role-card-label {
      font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
      padding: 4px 12px; border-radius: 50px; position: relative; z-index: 1;
    }
    .role-card.student .role-card-label { color: #a5b4fc; background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.22); }
    .role-card.placement .role-card-label { color: #6ee7b7; background: rgba(5,150,82,0.12); border: 1px solid rgba(5,150,82,0.22); }
    .role-card.admin .role-card-label { color: #c4b5fd; background: rgba(124,58,237,0.12); border: 1px solid rgba(124,58,237,0.22); }

    .role-card h2 {
      font-family: 'Outfit', sans-serif;
      font-size: 22px; font-weight: 800; color: #f1f5f9 !important;
      letter-spacing: -0.02em; position: relative; z-index: 1; margin: 0;
    }

    .role-card p {
      color: #475569; font-size: 13.5px; line-height: 1.65;
      position: relative; z-index: 1; margin: 0;
    }

    .role-cta {
      display: inline-flex; align-items: center; gap: 8px;
      font-size: 14px; font-weight: 700; padding: 11px 24px; border-radius: 50px;
      letter-spacing: 0.02em; position: relative; z-index: 1; transition: all 0.25s ease;
    }
    .role-card.student .role-cta { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; box-shadow: 0 4px 16px rgba(79,70,229,0.40); }
    .role-card.placement .role-cta { background: linear-gradient(135deg, #059652, #10b981); color: #fff; box-shadow: 0 4px 16px rgba(5,150,82,0.38); }
    .role-card.admin .role-cta { background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; box-shadow: 0 4px 16px rgba(124,58,237,0.40); }
    .role-card:hover .role-cta { transform: translateY(-2px); }

    .role-features {
      list-style: none; padding: 0; margin: 0; width: 100%;
      text-align: left; position: relative; z-index: 1;
    }
    .role-features li {
      font-size: 12.5px; color: #475569; padding: 6px 0;
      display: flex; align-items: center; gap: 8px;
      border-bottom: 1px solid rgba(255,255,255,0.04);
    }
    .role-features li:last-child { border-bottom: none; }
    .role-features li i { font-size: 11px; }
    .role-card.student .role-features li i { color: #6366f1; }
    .role-card.placement .role-features li i { color: #059652; }
    .role-card.admin .role-features li i { color: #7c3aed; }

    /* Quotes section */
    .quote-strip {
      background: rgba(255,255,255,0.03); border-top: 1px solid rgba(255,255,255,0.06);
      border-bottom: 1px solid rgba(255,255,255,0.06); padding: 40px 24px;
      text-align: center; position: relative; z-index: 1;
    }
    .quote-strip blockquote {
      font-family: 'Outfit', sans-serif;
      font-size: 17px; font-style: italic; color: #64748b;
      max-width: 600px; margin: 0 auto; line-height: 1.8;
    }
    .quote-strip cite { display: block; margin-top: 12px; color: #334155; font-size: 13px; font-style: normal; font-weight: 600; }

    @media (max-width: 900px) { .roles-grid { grid-template-columns: 1fr; max-width: 400px; } .hero-section h1 { font-size: 32px; } }
    @media (max-width: 576px) { .hero-section { padding: 60px 16px 36px; } .hero-section h1 { font-size: 26px; } }
  </style>
</head>
<body>

<?php include 'php/header.php' ?>

<div class="login-select-page">
  <!-- Hero -->
  <div class="hero-section">
    <div class="hero-badge"><i class="fa fa-mortarboard"></i> CHRIST CAREER CONNECT — 2026</div>
    <h1>Who are you<br>logging in as?</h1>
    <p>Select your role to access your dedicated portal</p>
  </div>

  <!-- Role Cards -->
  <div class="roles-grid">

    <!-- Student Card -->
    <a href="login-candidates.php" class="role-card student">
      <div class="role-icon">🎓</div>
      <div class="role-card-label">Student</div>
      <h2>Student Login</h2>
      <p>Apply for drives, build your resume, practice mock interviews, and track your placements.</p>
      <ul class="role-features">
        <li><i class="fa fa-check-circle"></i> Apply for placement drives</li>
        <li><i class="fa fa-check-circle"></i> AI mock interviews & resume builder</li>
        <li><i class="fa fa-check-circle"></i> Track application status</li>
        <li><i class="fa fa-check-circle"></i> AI career assistant & skill gap</li>
      </ul>
      <span class="role-cta">Login as Student <i class="fa fa-arrow-right"></i></span>
    </a>

    <!-- Placement Cell Card -->
    <a href="login-company.php" class="role-card placement">
      <div class="role-icon">🏢</div>
      <div class="role-card-label">Placement Cell</div>
      <h2>Coordinator Login</h2>
      <p>Post drives, review student profiles, manage applications, and communicate with candidates.</p>
      <ul class="role-features">
        <li><i class="fa fa-check-circle"></i> Post & manage placement drives</li>
        <li><i class="fa fa-check-circle"></i> Review student applications</li>
        <li><i class="fa fa-check-circle"></i> Send emails & notices</li>
        <li><i class="fa fa-check-circle"></i> Mark students as placed</li>
      </ul>
      <span class="role-cta">Login as Coordinator <i class="fa fa-arrow-right"></i></span>
    </a>

    <!-- Admin Card -->
    <a href="admin/index.php" class="role-card admin">
      <div class="role-icon">🛡️</div>
      <div class="role-card-label">Admin</div>
      <h2>Admin Login</h2>
      <p>Full control over the platform — manage users, coordinators, drives, and system settings.</p>
      <ul class="role-features">
        <li><i class="fa fa-check-circle"></i> Approve / reject accounts</li>
        <li><i class="fa fa-check-circle"></i> Manage admins & coordinators</li>
        <li><i class="fa fa-check-circle"></i> View full database & exports</li>
        <li><i class="fa fa-check-circle"></i> Configure Gemini AI (BYOK)</li>
      </ul>
      <span class="role-cta">Admin Panel <i class="fa fa-arrow-right"></i></span>
    </a>

  </div>

  <!-- Quote Strip -->
  <div class="quote-strip">
    <blockquote>
      "Placement is a decisive factor of successful completion of any coursework. It is a dream of every student to get placed in top MNCs."
      <cite>— Training and Placement Officer, Christ University NCR</cite>
    </blockquote>
  </div>
</div>

<?php include 'php/footer.php' ?>
</body>
</html>
<?php
session_start();
if (isset($_SESSION['id_admin'])) {
  header("Location: dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Christ Career Connect</title>
  <link href="../img/christlogo.png" rel="icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/dashboard-premium.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Inter', sans-serif; background: #09090f; color: #f1f5f9; }

    .admin-login-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      background: linear-gradient(145deg, #09090f 0%, #0f0e1a 50%, #0a0a14 100%);
    }

    .admin-login-page::before {
      content: '';
      position: absolute;
      width: 700px; height: 700px;
      background: radial-gradient(circle, rgba(124,58,237,0.18) 0%, transparent 65%);
      top: -200px; right: -200px; border-radius: 50%;
      animation: blobFloat 8s ease-in-out infinite alternate; pointer-events: none;
    }
    .admin-login-page::after {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(79,70,229,0.14) 0%, transparent 65%);
      bottom: -150px; left: -150px; border-radius: 50%;
      animation: blobFloat 11s ease-in-out infinite alternate-reverse; pointer-events: none;
    }
    @keyframes blobFloat { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(25px,-25px) scale(1.08); } }

    .admin-login-card {
      width: 100%;
      max-width: 440px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(124,58,237,0.25);
      border-radius: 28px;
      padding: 52px 44px;
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      box-shadow: 0 24px 80px rgba(0,0,0,0.55), 0 0 80px rgba(124,58,237,0.12);
      position: relative;
      z-index: 2;
      animation: fadeInUp 0.5s cubic-bezier(0.4,0,0.2,1) both;
    }
    @keyframes fadeInUp { from { opacity:0; transform:translateY(32px); } to { opacity:1; transform:translateY(0); } }

    .admin-icon {
      width: 72px; height: 72px;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      border-radius: 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 32px; margin: 0 auto 20px;
      box-shadow: 0 8px 28px rgba(79,70,229,0.50);
      animation: floatIcon 3s ease-in-out infinite alternate;
    }
    @keyframes floatIcon { 0% { transform: translateY(0); } 100% { transform: translateY(-8px); } }

    .admin-login-card h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 26px; font-weight: 800; text-align: center;
      background: linear-gradient(135deg, #a5b4fc, #c4b5fd);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 6px; letter-spacing: -0.03em;
    }
    .admin-login-card .subtitle {
      text-align: center; color: #475569; font-size: 14px; margin-bottom: 36px;
    }
    .badge-admin {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.30);
      color: #c4b5fd; font-size: 10.5px; font-weight: 700;
      padding: 4px 12px; border-radius: 50px; letter-spacing: 0.09em;
      margin-bottom: 20px; text-align: center;
    }
    .admin-login-card-top { text-align: center; }

    .form-group { margin-bottom: 20px; }
    label { font-size: 12px; font-weight: 700; color: #64748b; display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.09em; }
    .input-wrap { position: relative; }
    .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #334155; font-size: 15px; pointer-events: none; }
    .input-wrap input {
      width: 100%; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.09);
      border-radius: 12px; padding: 13px 14px 13px 42px; font-size: 14px;
      font-family: 'Inter', sans-serif; color: #f1f5f9; transition: all 0.25s ease; outline: none;
    }
    .input-wrap input:focus {
      border-color: #7c3aed; background: rgba(124,58,237,0.08); box-shadow: 0 0 0 3px rgba(124,58,237,0.22);
    }
    .input-wrap input::placeholder { color: #1e293b; }

    .btn-admin-login {
      width: 100%; padding: 14px;
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: #fff; border: none; border-radius: 50px;
      font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 700;
      cursor: pointer; letter-spacing: 0.03em;
      box-shadow: 0 6px 24px rgba(79,70,229,0.45); transition: all 0.25s ease; margin-top: 8px;
    }
    .btn-admin-login:hover { transform: translateY(-3px); box-shadow: 0 12px 36px rgba(79,70,229,0.60); }

    .login-alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
    .login-alert.error { background: rgba(225,29,72,0.12); border: 1px solid rgba(225,29,72,0.28); color: #fca5a5; }

    .back-link {
      display: flex; align-items: center; gap: 6px; justify-content: center;
      margin-top: 28px; color: #334155; font-size: 13px; text-decoration: none; transition: color 0.2s;
    }
    .back-link:hover { color: #a5b4fc; }

    .admin-footer {
      position: fixed; bottom: 0; left: 0; right: 0;
      background: rgba(0,0,0,0.40); border-top: 1px solid rgba(255,255,255,0.05);
      padding: 12px; text-align: center; font-size: 12px; color: #334155;
    }
  </style>
</head>
<body>

<div class="admin-login-page">
  <div class="admin-login-card">
    <div class="admin-login-card-top">
      <div class="admin-icon">🛡️</div>
      <div class="badge-admin"><i class="fa fa-lock"></i> ADMIN ACCESS</div>
      <h1>Admin Login</h1>
      <p class="subtitle">Christ Career Connect — Admin Panel</p>
    </div>

    <?php if (isset($_SESSION['loginError'])): ?>
      <div class="login-alert error"><i class="fa fa-exclamation-circle"></i> Invalid Username or Password. Try again.</div>
      <?php unset($_SESSION['loginError']); ?>
    <?php endif; ?>

    <form action="checklogin.php" method="post">
      <div class="form-group">
        <label>Username</label>
        <div class="input-wrap">
          <i class="fa fa-user-circle-o"></i>
          <input type="text" name="username" placeholder="sradmin" required>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fa fa-lock"></i>
          <input type="password" name="password" placeholder="Enter admin password" required>
        </div>
      </div>
      <button type="submit" class="btn-admin-login">
        <i class="fa fa-shield"></i> &nbsp;Sign In as Admin
      </button>
    </form>

    <a href="../index.php" class="back-link"><i class="fa fa-arrow-left"></i> Back to main site</a>
  </div>
</div>

<div class="admin-footer">
  &copy; 2026 Christ Career Connect &nbsp;|&nbsp; Admin Panel &nbsp;|&nbsp; Secured Access Only
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</body>
</html>
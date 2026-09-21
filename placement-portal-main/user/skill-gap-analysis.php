<?php
session_start();

if (empty($_SESSION['id_user'])) {
  header("Location: ../index.php");
  exit();
}

require_once("../db.php");
require_once("../php/ai-resume-matcher.php");

// Fetch User Profile
$uQ = $conn->query("SELECT * FROM users WHERE id_user='$_SESSION[id_user]'");
if (!$uQ || $uQ->num_rows == 0) {
  header("Location: edit-profile.php");
  exit();
}
$userProfile = $uQ->fetch_assoc();

// Get Target Role from GET/POST parameter (Default: Software Developer)
$targetRole = isset($_REQUEST['target_role']) ? trim($_REQUEST['target_role']) : 'Software Developer';

// Run AI Skill Gap Analysis
$analysis = evaluateSkillGap($userProfile, $targetRole);

$matchPct   = $analysis['match_percentage'] ?? 50;
$verdict    = $analysis['verdict'] ?? '⚡ Moderate Skill Gap';
$already    = $analysis['already_have'] ?? [];
$need       = $analysis['need_to_improve'] ?? [];
$summary    = $analysis['summary'] ?? '';
$roadmap    = $analysis['roadmap'] ?? [];
$certs      = $analysis['recommended_certifications'] ?? [];
$projects   = $analysis['recommended_projects'] ?? [];
$isGemini   = $analysis['is_gemini'] ?? false;
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AI Skill Gap Analysis & Learning Roadmap | Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap">
  <style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4, .brand-font { font-family: 'Outfit', sans-serif; }
    
    .card-sg { background:#fff; border-radius:18px; padding:28px; box-shadow:0 10px 30px rgba(79,70,229,0.08); border:1px solid #e2e8f0; margin-bottom:24px; }
    .pill-role { background:#f1f5f9; color:#475569; font-weight:600; font-size:13px; padding:8px 16px; border-radius:50px; text-decoration:none; display:inline-block; margin:4px 2px; transition:all 0.2s; border:1px solid #cbd5e1; }
    .pill-role:hover, .pill-role.active { background:#4f46e5; color:#ffffff; border-color:#4f46e5; box-shadow:0 4px 12px rgba(79,70,229,0.25); text-decoration:none; }
    
    .skill-tag-have { background:#dcfce7; color:#15803d; font-weight:700; font-size:13px; padding:6px 14px; border-radius:8px; display:inline-block; margin:4px; border:1px solid #bbf7d0; }
    .skill-tag-need { background:#ffedd5; color:#c2410c; font-weight:700; font-size:13px; padding:6px 14px; border-radius:8px; display:inline-block; margin:4px; border:1px solid #fed7aa; }
    
    .timeline-phase { position:relative; padding-left:32px; margin-bottom:24px; border-left:3px solid #6366f1; }
    .timeline-phase::before { content:''; position:absolute; left:-9px; top:0; width:15px; height:15px; border-radius:50%; background:#4f46e5; border:3px solid #fff; box-shadow:0 0 0 2px #6366f1; }
    .timeline-title { font-weight:700; color:#0f172a; font-size:16px; margin-bottom:4px; }
    .timeline-badge { background:#e0e7ff; color:#4338ca; font-weight:700; font-size:11px; padding:3px 10px; border-radius:50px; display:inline-block; margin-bottom:6px; }
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
                    <li><a href="resume-review.php"><i class="fa fa-file-text-o"></i> AI Resume ATS Review</a></li>
                    <li class="active"><a href="skill-gap-analysis.php"><i class="fa fa-bullseye"></i> AI Skill Gap Analysis</a></li>
                    <li><a href="index.php"><i class="fa fa-address-card-o"></i> My Applications</a></li>
                    <li><a href="mailbox.php"><i class="fa fa-envelope"></i> Mailbox</a></li>
                    <li><a href="settings.php"><i class="fa fa-gear"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Main Skill Gap Content -->
            <div class="col-md-9 bg-white padding-2">
              
              <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                  <h2 style="margin:0;font-weight:800;color:#0f172a;" class="brand-font">🔍 AI Skill Gap Analysis & Roadmap</h2>
                  <p class="text-muted" style="margin-top:4px;">Compare your profile skills against target industry roles and generate a custom learning roadmap</p>
                </div>
                <div>
                  <?php if ($isGemini): ?>
                    <span class="badge" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:8px 14px;font-size:12px;">⚡ Powered by Google Gemini AI</span>
                  <?php else: ?>
                    <span class="badge" style="background:#e2e8f0;color:#475569;padding:8px 14px;font-size:12px;">⚙️ Built-in Skill Evaluator</span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Role Selector Card -->
              <div class="card-sg" style="background:#f8fafc;border-left:5px solid #4f46e5;">
                <h4 style="margin-top:0;font-weight:700;color:#1e293b;">🎯 Select Target Career Role:</h4>
                
                <div style="margin-bottom:14px;">
                  <a href="skill-gap-analysis.php?target_role=Software+Developer" class="pill-role <?php echo ($targetRole === 'Software Developer') ? 'active' : ''; ?>">💻 Software Developer</a>
                  <a href="skill-gap-analysis.php?target_role=Full+Stack+Developer" class="pill-role <?php echo ($targetRole === 'Full Stack Developer') ? 'active' : ''; ?>">🌐 Full Stack Developer</a>
                  <a href="skill-gap-analysis.php?target_role=Data+Scientist" class="pill-role <?php echo ($targetRole === 'Data Scientist') ? 'active' : ''; ?>">📊 Data Scientist</a>
                  <a href="skill-gap-analysis.php?target_role=DevOps+%2F+Cloud+Engineer" class="pill-role <?php echo ($targetRole === 'DevOps / Cloud Engineer') ? 'active' : ''; ?>">☁️ DevOps / Cloud Engineer</a>
                  <a href="skill-gap-analysis.php?target_role=Frontend+Developer" class="pill-role <?php echo ($targetRole === 'Frontend Developer') ? 'active' : ''; ?>">🎨 Frontend Developer</a>
                </div>

                <form action="skill-gap-analysis.php" method="get" class="form-inline" style="margin-top:10px;">
                  <div class="form-group" style="width:70%;max-width:400px;">
                    <input type="text" name="target_role" class="form-control input-lg" style="width:100%;border-radius:8px;" placeholder="Or type any custom role e.g. Backend Engineer..." value="<?php echo htmlspecialchars($targetRole); ?>" required>
                  </div>
                  <button type="submit" class="btn btn-indigo btn-lg" style="background:#4f46e5;color:#fff;border-radius:8px;font-weight:700;margin-left:8px;">
                    <i class="fa fa-search"></i> Analyze Skill Gap
                  </button>
                </form>
              </div>

              <!-- Skill Gap Analysis Results Card -->
              <div class="card-sg">
                
                <!-- Role Header & Score -->
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #e2e8f0;">
                  <div>
                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;color:#64748b;letter-spacing:.06em;">Target Role Analysis</span>
                    <h3 style="margin:2px 0 0 0;font-weight:800;color:#4f46e5;" class="brand-font">
                      <?php echo htmlspecialchars($targetRole); ?>
                    </h3>
                  </div>

                  <div style="text-align:right;">
                    <span style="background:#e0e7ff;color:#4338ca;font-weight:700;font-size:13px;padding:6px 16px;border-radius:50px;">
                      <?php echo htmlspecialchars($verdict); ?>
                    </span>
                    <div style="font-size:32px;font-weight:900;color:#4f46e5;margin-top:4px;">
                      <?php echo $matchPct; ?>% <span style="font-size:14px;color:#64748b;font-weight:600;">Match</span>
                    </div>
                  </div>
                </div>

                <!-- Match Progress Bar -->
                <div style="margin-bottom:24px;">
                  <div style="height:12px;background:#e2e8f0;border-radius:50px;overflow:hidden;">
                    <div style="width:<?php echo $matchPct; ?>%;height:100%;background:linear-gradient(90deg, #4f46e5, #7c3aed);border-radius:50px;"></div>
                  </div>
                </div>

                <!-- AI Coach Summary -->
                <div style="background:#f8fafc;border-left:4px solid #4f46e5;padding:16px;border-radius:8px;margin-bottom:24px;">
                  <h4 style="margin-top:0;font-weight:700;color:#0f172a;">🤖 AI Career Coach Assessment</h4>
                  <p style="color:#475569;margin-bottom:0;line-height:1.6;font-size:14px;">
                    <?php echo htmlspecialchars($summary); ?>
                  </p>
                </div>

                <!-- 2 Column Comparison: Already Have vs Need to Improve -->
                <div class="row" style="margin-bottom:28px;">
                  
                  <!-- Already Have -->
                  <div class="col-md-6" style="margin-bottom:16px;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:20px;height:100%;">
                      <h4 style="margin-top:0;color:#166534;font-weight:800;" class="brand-font">
                        ✅ Already Have (Current Skills)
                      </h4>
                      <p style="font-size:12px;color:#15803d;margin-bottom:12px;">Skills detected in your candidate profile matching this role:</p>
                      <div>
                        <?php if (!empty($already)): ?>
                          <?php foreach ($already as $sk): ?>
                            <span class="skill-tag-have">✓ <?php echo htmlspecialchars($sk); ?></span>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="text-muted" style="font-size:13px;">No direct matching skills listed in profile yet.</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <!-- Need to Improve / Gaps -->
                  <div class="col-md-6" style="margin-bottom:16px;">
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:20px;height:100%;">
                      <h4 style="margin-top:0;color:#c2410c;font-weight:800;" class="brand-font">
                        ⚡ Need to Improve (Skill Gaps)
                      </h4>
                      <p style="font-size:12px;color:#9a3412;margin-bottom:12px;">Critical industry skills recommended for a <?php echo htmlspecialchars($targetRole); ?>:</p>
                      <div>
                        <?php if (!empty($need)): ?>
                          <?php foreach ($need as $sk): ?>
                            <span class="skill-tag-need">🎯 <?php echo htmlspecialchars($sk); ?></span>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <span class="text-success" style="font-size:13px;font-weight:700;">🎉 You match all core skills for this role!</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                </div>

                <!-- Recommended Learning Roadmap Timeline -->
                <div style="margin-top:20px;padding-top:20px;border-top:1px solid #e2e8f0;">
                  <h3 style="margin-top:0;font-weight:800;color:#0f172a;" class="brand-font">
                    🗺️ Recommended Step-by-Step Learning Roadmap
                  </h3>
                  <p class="text-muted" style="margin-bottom:24px;">Structured 3-phase action plan tailored to bridge your skill gaps for <strong><?php echo htmlspecialchars($targetRole); ?></strong>:</p>

                  <div style="margin-top:16px;">
                    <?php if (!empty($roadmap)): ?>
                      <?php foreach ($roadmap as $step): ?>
                        <div class="timeline-phase">
                          <span class="timeline-badge"><?php echo htmlspecialchars($step['phase'] ?? 'Phase'); ?></span>
                          <div class="timeline-title"><?php echo htmlspecialchars($step['title'] ?? ''); ?></div>
                          
                          <?php if (!empty($step['skills'])): ?>
                            <div style="margin:4px 0 8px 0;">
                              <small style="color:#64748b;font-weight:600;">Key Focus Skills:</small>
                              <?php foreach ($step['skills'] as $s): ?>
                                <span style="background:#eef2ff;color:#4f46e5;font-weight:700;font-size:11px;padding:2px 8px;border-radius:4px;margin-left:4px;"><?php echo htmlspecialchars($s); ?></span>
                              <?php endforeach; ?>
                            </div>
                          <?php endif; ?>

                          <p style="font-size:13px;color:#475569;margin-bottom:0;line-height:1.6;">
                            <?php echo htmlspecialchars($step['description'] ?? ''); ?>
                          </p>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Recommended Certifications & Projects Box -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-top:24px;">
                  <div class="row">
                    <div class="col-md-6" style="margin-bottom:12px;">
                      <h4 style="margin-top:0;font-weight:700;color:#4f46e5;"><i class="fa fa-certificate"></i> Recommended Industry Certifications</h4>
                      <ul style="margin-bottom:0;padding-left:18px;color:#334155;font-size:13px;line-height:1.7;">
                        <?php foreach ($certs as $c): ?>
                          <li><strong><?php echo htmlspecialchars($c); ?></strong></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>

                    <div class="col-md-6">
                      <h4 style="margin-top:0;font-weight:700;color:#059652;"><i class="fa fa-code"></i> Recommended Portfolio Projects</h4>
                      <ul style="margin-bottom:0;padding-left:18px;color:#334155;font-size:13px;line-height:1.7;">
                        <?php foreach ($projects as $p): ?>
                          <li><strong><?php echo htmlspecialchars($p); ?></strong></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </div>
                </div>

                <div style="margin-top:24px;text-align:center;">
                  <a href="edit-profile.php" class="btn btn-indigo btn-lg" style="background:#4f46e5;color:#fff;border-radius:8px;font-weight:700;padding:12px 28px;">
                    <i class="fa fa-pencil"></i> Update Profile Skills Now
                  </a>
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
</body>

</html>

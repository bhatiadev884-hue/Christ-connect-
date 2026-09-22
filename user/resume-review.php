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

// Evaluate General ATS Score
$atsData = evaluateGeneralATSResume($userProfile);

$score = $atsData['ats_score'] ?? 0;
$verdict = $atsData['verdict'] ?? '⚠️ Action Required';
$bg = $atsData['badge_bg'] ?? '#fee2e2';
$color = $atsData['badge_color'] ?? '#b91c1c';
$summary = $atsData['summary'] ?? '';
$isGemini = $atsData['is_gemini'] ?? false;

$strengths = $atsData['strengths'] ?? [];
$flaws = $atsData['flaws'] ?? [];
$recs = $atsData['recommendations'] ?? [];
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AI Resume ATS Review & Score | Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .card-ats { background:#fff; border-radius:16px; padding:28px; box-shadow:0 8px 30px rgba(79,70,229,0.08); border:1px solid #e2e8f0; margin-bottom:24px; }
    .badge-status { font-weight:700; font-size:14px; padding:6px 16px; border-radius:50px; }
    .progress-bar-ats { height:12px; border-radius:50px; background:linear-gradient(90deg, #4f46e5, #7c3aed); }
    .list-icon-check li { margin-bottom:8px; list-style:none; position:relative; padding-left:24px; }
    .list-icon-check li::before { content:'✓'; position:absolute; left:0; color:#059652; font-weight:bold; }
    .list-icon-warn li { margin-bottom:8px; list-style:none; position:relative; padding-left:24px; }
    .list-icon-warn li::before { content:'⚡'; position:absolute; left:0; color:#d97706; font-weight:bold; }
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
                    <li class="active"><a href="resume-review.php"><i class="fa fa-file-text-o"></i> AI Resume ATS Review</a></li>
                    <li><a href="ai-resume-builder.php"><i class="fa fa-magic text-success"></i> AI Resume Builder</a></li>
                    <li><a href="skill-gap-analysis.php"><i class="fa fa-bullseye text-indigo"></i> AI Skill Gap Analysis</a></li>
                    <li><a href="ai-job-recommendations.php"><i class="fa fa-briefcase text-green"></i> AI Drive Match</a></li>
                    <li><a href="index.php"><i class="fa fa-address-card-o"></i> My Applications</a></li>
                    <li><a href="mailbox.php"><i class="fa fa-envelope"></i> Mailbox</a></li>
                    <li><a href="settings.php"><i class="fa fa-gear"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Main ATS Review Content -->
            <div class="col-md-9 bg-white padding-2">
              
              <?php if (isset($_SESSION['profile_updated_success'])): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  🎉 Profile updated successfully! Below is your updated live <strong>AI Resume ATS Score & Optimization Review</strong>.
                </div>
                <?php unset($_SESSION['profile_updated_success']); ?>
              <?php endif; ?>

              <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                  <h2 style="margin:0;font-weight:700;color:#0f172a;">🤖 AI Resume ATS Optimization & Review</h2>
                  <p class="text-muted" style="margin-top:4px;">Automated Applicant Tracking System (ATS) Health Analysis & Recruiter Shortlist Predictor</p>
                </div>
                <div>
                  <a href="edit-profile.php" class="btn btn-primary btn-lg" style="border-radius:8px;font-weight:700;">
                    <i class="fa fa-pencil"></i> Edit Profile / Upload Resume
                  </a>
                </div>
              </div>
              <hr>

              <!-- ATS Main Card -->
              <div class="card-ats">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
                  <div>
                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;color:#64748b;letter-spacing:.06em;">ATS Health Rating</span>
                    <h3 style="margin:2px 0 0 0;font-weight:800;color:#0f172a;">Overall ATS Score</h3>
                    <?php if ($isGemini): ?>
                      <span class="badge" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);margin-top:6px;padding:4px 10px;">⚡ Powered by Google Gemini AI</span>
                    <?php else: ?>
                      <span class="badge" style="background:#e2e8f0;color:#475569;margin-top:6px;padding:4px 10px;">⚙️ Built-in ATS Evaluator</span>
                    <?php endif; ?>
                  </div>

                  <div style="text-align:right;">
                    <span class="badge-status" style="background:<?php echo $bg; ?>;color:<?php echo $color; ?>;">
                      <?php echo $verdict; ?>
                    </span>
                    <div style="font-size:36px;font-weight:900;color:<?php echo $color; ?>;margin-top:4px;">
                      <?php echo $score; ?><span style="font-size:20px;">/100</span>
                    </div>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div style="margin-bottom:20px;">
                  <div style="height:14px;background:#e2e8f0;border-radius:50px;overflow:hidden;">
                    <div class="progress-bar-ats" style="width:<?php echo $score; ?>%;"></div>
                  </div>
                </div>

                <!-- AI Evaluation Summary -->
                <div style="background:#f8fafc;border-left:4px solid #4f46e5;padding:16px;border-radius:8px;margin-bottom:24px;">
                  <h4 style="margin-top:0;font-weight:700;color:#1e293b;">📋 AI Recruiter Evaluation Summary</h4>
                  <p style="color:#475569;margin-bottom:0;line-height:1.6;font-size:14px;">
                    <?php echo htmlspecialchars($summary); ?>
                  </p>
                </div>

                <!-- 2 Column Strengths vs Flaws -->
                <div class="row" style="margin-bottom:24px;">
                  <div class="col-md-6">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:18px;height:100%;">
                      <h4 style="margin-top:0;color:#166534;font-weight:700;">✓ ATS Strengths & Passed Checks</h4>
                      <?php if (!empty($strengths)): ?>
                        <ul class="list-icon-check" style="padding-left:0;margin-bottom:0;font-size:13px;color:#15803d;">
                          <?php foreach ($strengths as $s): ?>
                            <li><?php echo htmlspecialchars($s); ?></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <p style="color:#166534;font-size:13px;margin:0;">No major strengths detected yet.</p>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div style="background:#fffbeb;border:1px solid #fef08a;border-radius:12px;padding:18px;height:100%;">
                      <h4 style="margin-top:0;color:#92400e;font-weight:700;">⚡ ATS Flaws & Skill Gaps</h4>
                      <?php if (!empty($flaws)): ?>
                        <ul class="list-icon-warn" style="padding-left:0;margin-bottom:0;font-size:13px;color:#b45309;">
                          <?php foreach ($flaws as $f): ?>
                            <li><?php echo htmlspecialchars($f); ?></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php else: ?>
                        <p style="color:#92400e;font-size:13px;margin:0;">No critical ATS flaws detected!</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <!-- AI Recommendations to Boost Score -->
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:20px;">
                  <h4 style="margin-top:0;color:#1e40af;font-weight:700;">💡 Actionable AI Recommendations to Boost Your ATS Score</h4>
                  <?php if (!empty($recs)): ?>
                    <ol style="margin-bottom:0;padding-left:20px;color:#1e3a8a;font-size:14px;line-height:1.7;">
                      <?php foreach ($recs as $r): ?>
                        <li><?php echo $r; ?></li>
                      <?php endforeach; ?>
                    </ol>
                  <?php else: ?>
                    <p style="color:#1e3a8a;margin:0;font-size:14px;">Your profile and resume are fully optimized for campus recruiters!</p>
                  <?php endif; ?>
                </div>

                <!-- Quick Resume Upload & Rescan Form Box -->
                <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:20px;margin-top:24px;">
                  <h4 style="margin-top:0;font-weight:700;color:#0f172a;"><i class="fa fa-upload text-indigo"></i> Quick Upload / Replace Resume File</h4>
                  <p style="font-size:13px;color:#64748b;margin-bottom:14px;">Select a new PDF resume file to immediately upload and regenerate your live AI ATS Score Report.</p>
                  
                  <form action="update-profile.php" method="post" enctype="multipart/form-data">
                    <!-- Preserve existing user profile fields -->
                    <input type="hidden" name="fname" value="<?php echo htmlspecialchars($userProfile['firstname'] ?? ''); ?>">
                    <input type="hidden" name="lname" value="<?php echo htmlspecialchars($userProfile['lastname'] ?? ''); ?>">
                    <input type="hidden" name="address" value="<?php echo htmlspecialchars($userProfile['address'] ?? ''); ?>">
                    <input type="hidden" name="city" value="<?php echo htmlspecialchars($userProfile['city'] ?? ''); ?>">
                    <input type="hidden" name="state" value="<?php echo htmlspecialchars($userProfile['state'] ?? ''); ?>">
                    <input type="hidden" name="contactno" value="<?php echo htmlspecialchars($userProfile['contactno'] ?? ''); ?>">
                    <input type="hidden" name="qualification" value="<?php echo htmlspecialchars($userProfile['qualification'] ?? ''); ?>">
                    <input type="hidden" name="stream" value="<?php echo htmlspecialchars($userProfile['stream'] ?? ''); ?>">
                    <input type="hidden" name="skills" value="<?php echo htmlspecialchars($userProfile['skills'] ?? ''); ?>">
                    <input type="hidden" name="aboutme" value="<?php echo htmlspecialchars($userProfile['aboutme'] ?? ''); ?>">
                    <input type="hidden" name="hsc" value="<?php echo htmlspecialchars($userProfile['hsc'] ?? ''); ?>">
                    <input type="hidden" name="ssc" value="<?php echo htmlspecialchars($userProfile['ssc'] ?? ''); ?>">
                    <input type="hidden" name="ug" value="<?php echo htmlspecialchars($userProfile['ug'] ?? ''); ?>">
                    <input type="hidden" name="pg" value="<?php echo htmlspecialchars($userProfile['pg'] ?? ''); ?>">

                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                      <input type="file" name="resume" class="btn btn-default" style="background:#fff;" required>
                      <button type="submit" class="btn btn-success" style="border-radius:6px;font-weight:700;">
                        <i class="fa fa-bolt"></i> Upload & Rescan with AI
                      </button>
                    </div>
                  </form>
                </div>

                <!-- Resume File Preview Box & Embedded Viewer -->
                <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
                  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                    <div>
                      <strong style="color:#0f172a;font-size:16px;">📄 Attached Resume Document</strong> 
                      <?php 
                      $rFile = $userProfile['resume'] ?? '';
                      $rExists = !empty($rFile) && file_exists("../uploads/resume/" . $rFile);
                      if ($rExists): 
                      ?>
                        <span class="text-success" style="font-weight:600;margin-left:8px;"><i class="fa fa-file-pdf-o"></i> <?php echo htmlspecialchars($rFile); ?></span>
                      <?php else: ?>
                        <span class="text-danger" style="font-weight:600;margin-left:8px;">No valid PDF file on server</span>
                      <?php endif; ?>
                    </div>

                    <div>
                      <?php if ($rExists): ?>
                        <a href="../uploads/resume/<?php echo $rFile; ?>" target="_blank" class="btn btn-info" style="border-radius:6px;font-weight:600;">
                          <i class="fa fa-external-link"></i> Open Resume PDF in New Tab
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>

                  <?php if (!empty($userProfile['resume']) && file_exists("../uploads/resume/" . $userProfile['resume'])): ?>
                    <div style="border:1px solid #cbd5e1;border-radius:12px;overflow:hidden;background:#525659;margin-top:10px;">
                      <iframe src="../uploads/resume/<?php echo $userProfile['resume']; ?>" width="100%" height="500px" style="border:none;"></iframe>
                    </div>
                  <?php endif; ?>
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

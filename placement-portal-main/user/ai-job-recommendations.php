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

// Preferred role filter parameter
$prefRoleFilter = isset($_REQUEST['pref_role']) ? trim($_REQUEST['pref_role']) : '';

// Get AI Recommendations
$recommendedDrives = getAIJobRecommendations($userProfile, $conn, $prefRoleFilter);

$isGeminiActive = !empty($recommendedDrives) && isset($recommendedDrives[0]['is_gemini']) && $recommendedDrives[0]['is_gemini'];
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AI Placement Drive Recommendations | Placement Portal</title>
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
    
    .card-rec { background:#fff; border-radius:18px; padding:24px; box-shadow:0 10px 30px rgba(79,70,229,0.08); border:1px solid #e2e8f0; margin-bottom:24px; transition:transform 0.2s, box-shadow 0.2s; }
    .card-rec:hover { transform:translateY(-2px); box-shadow:0 14px 36px rgba(79,70,229,0.12); }
    
    .badge-score { font-weight:800; font-size:15px; padding:6px 16px; border-radius:50px; display:inline-block; }
    .badge-score-high { background:#dcfce7; color:#15803d; }
    .badge-score-med { background:#fef3c7; color:#b45309; }
    
    .match-reason li { margin-bottom:6px; position:relative; padding-left:22px; list-style:none; }
    .match-reason li::before { content:'✓'; position:absolute; left:0; color:#059652; font-weight:bold; }
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
                    <li><a href="skill-gap-analysis.php"><i class="fa fa-bullseye"></i> AI Skill Gap Analysis</a></li>
                    <li class="active"><a href="ai-job-recommendations.php"><i class="fa fa-briefcase"></i> AI Drive Match</a></li>
                    <li><a href="index.php"><i class="fa fa-address-card-o"></i> My Applications</a></li>
                    <li><a href="mailbox.php"><i class="fa fa-envelope"></i> Mailbox</a></li>
                    <li><a href="settings.php"><i class="fa fa-gear"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Main AI Job Recommendations Content -->
            <div class="col-md-9 bg-white padding-2">
              
              <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                  <h2 style="margin:0;font-weight:800;color:#0f172a;" class="brand-font">💼 AI Company & Drive Recommendations</h2>
                  <p class="text-muted" style="margin-top:4px;">Smart placement drive matching evaluated against your Skills, Degree, Academic Scores, Projects & Preferred Role</p>
                </div>
                <div>
                  <?php if ($isGeminiActive): ?>
                    <span class="badge" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:8px 14px;font-size:12px;">⚡ Powered by Google Gemini AI</span>
                  <?php else: ?>
                    <span class="badge" style="background:#e2e8f0;color:#475569;padding:8px 14px;font-size:12px;">⚙️ Built-in Matchmaking Engine</span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Student Profile Criteria Box -->
              <div style="background:#f8fafc;border-left:4px solid #4f46e5;border-radius:14px;padding:20px;margin-bottom:24px;border:1px solid #e2e8f0;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
                  <h4 style="margin:0;font-weight:700;color:#1e293b;">📋 Your Matchmaking Profile Criteria:</h4>
                  <a href="edit-profile.php" class="btn btn-default btn-xs" style="border-radius:6px;font-weight:600;"><i class="fa fa-pencil"></i> Edit Criteria</a>
                </div>
                
                <div class="row" style="font-size:13px;color:#334155;">
                  <div class="col-md-3">
                    <strong>Degree & Stream:</strong><br>
                    <span class="text-primary"><?php echo htmlspecialchars($userProfile['qualification'] ?? 'MCA'); ?> (<?php echo htmlspecialchars($userProfile['stream'] ?? 'CS'); ?>)</span>
                  </div>
                  <div class="col-md-3">
                    <strong>Academic Score / CGPA:</strong><br>
                    <span class="text-success"><?php echo max($userProfile['ug'] ?? 80, $userProfile['pg'] ?? 80); ?>% Marks</span>
                  </div>
                  <div class="col-md-3">
                    <strong>Skills Listed:</strong><br>
                    <span class="text-indigo"><?php echo !empty($userProfile['skills']) ? htmlspecialchars(substr($userProfile['skills'], 0, 30)) . '...' : 'None listed'; ?></span>
                  </div>
                  <div class="col-md-3">
                    <strong>Preferred Role:</strong><br>
                    <span class="text-purple"><?php echo !empty($prefRoleFilter) ? htmlspecialchars($prefRoleFilter) : htmlspecialchars($userProfile['designation'] ?? 'Software Developer'); ?></span>
                  </div>
                </div>

                <!-- Preferred Role Filter Bar -->
                <form action="ai-job-recommendations.php" method="get" class="form-inline" style="margin-top:16px;padding-top:12px;border-top:1px solid #cbd5e1;">
                  <label style="margin-right:8px;font-size:13px;color:#475569;">Filter Drives by Preferred Role:</label>
                  <input type="text" name="pref_role" class="form-control input-sm" style="border-radius:6px;width:240px;" placeholder="e.g. Software Engineer, Analyst..." value="<?php echo htmlspecialchars($prefRoleFilter); ?>">
                  <button type="submit" class="btn btn-indigo btn-sm" style="background:#4f46e5;color:#fff;border-radius:6px;font-weight:700;margin-left:6px;">
                    <i class="fa fa-filter"></i> Refine AI Recommendations
                  </button>
                </form>
              </div>

              <!-- List of Recommended Drives -->
              <?php if (!empty($recommendedDrives)): ?>
                <?php foreach ($recommendedDrives as $drive): ?>
                  <?php 
                  $score = $drive['match_score'] ?? 75;
                  $verdict = $drive['verdict'] ?? '⚡ Good Fit Drive';
                  $badgeClass = ($score >= 80) ? 'badge-score-high' : 'badge-score-med';
                  $reasons = $drive['match_reasons'] ?? [];
                  $skills = $drive['key_matching_skills'] ?? [];
                  $companyName = $drive['companyname'] ?? 'Partner Recruiter';
                  $companyLogo = !empty($drive['logo']) ? "../uploads/logo/" . $drive['logo'] : "../img/logo.png";
                  ?>
                  
                  <div class="card-rec">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                      
                      <div style="display:flex;align-items:center;gap:16px;">
                        <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="Company Logo" style="width:56px;height:56px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0;background:#f8fafc;" onError="this.src='../img/logo.png'">
                        <div>
                          <h3 style="margin:0;font-weight:800;color:#0f172a;" class="brand-font">
                            <a href="view-job-post.php?id=<?php echo $drive['id_jobpost']; ?>" style="color:#0f172a;text-decoration:none;">
                              <?php echo htmlspecialchars($drive['jobtitle']); ?>
                            </a>
                          </h3>
                          <div style="font-size:14px;color:#475569;margin-top:2px;">
                            <strong>🏢 <?php echo htmlspecialchars($companyName); ?></strong> &nbsp;|&nbsp; 
                            📍 <?php echo htmlspecialchars($drive['company_city'] ?? 'India'); ?> &nbsp;|&nbsp; 
                            💰 ₹<?php echo htmlspecialchars($drive['minimumsalary']); ?>/Year
                          </div>
                        </div>
                      </div>

                      <div style="text-align:right;">
                        <span class="badge-score <?php echo $badgeClass; ?>">
                          <?php echo htmlspecialchars($verdict); ?>
                        </span>
                        <div style="font-size:28px;font-weight:900;color:#4f46e5;margin-top:4px;">
                          <?php echo $score; ?>% <span style="font-size:13px;color:#64748b;font-weight:600;">AI Match</span>
                        </div>
                      </div>

                    </div>

                    <!-- Progress Bar -->
                    <div style="margin-bottom:16px;">
                      <div style="height:10px;background:#e2e8f0;border-radius:50px;overflow:hidden;">
                        <div style="width:<?php echo $score; ?>%;height:100%;background:linear-gradient(90deg, #4f46e5, #7c3aed);border-radius:50px;"></div>
                      </div>
                    </div>

                    <!-- Why AI Recommends This Drive -->
                    <div style="background:#f8fafc;border-radius:12px;padding:14px 18px;margin-bottom:16px;border:1px solid #f1f5f9;">
                      <strong style="font-size:12px;text-transform:uppercase;color:#4f46e5;letter-spacing:.05em;display:block;margin-bottom:8px;">
                        🤖 Why AI Recommends This Placement Drive:
                      </strong>
                      <ul class="match-reason" style="padding-left:0;margin-bottom:0;font-size:13px;color:#334155;">
                        <?php foreach ($reasons as $reason): ?>
                          <li><?php echo htmlspecialchars($reason); ?></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>

                    <!-- Job Metadata & Apply Button Bar -->
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-top:12px;border-top:1px solid #f1f5f9;">
                      <div style="font-size:13px;color:#64748b;">
                        🎓 Qualification Required: <strong><?php echo htmlspecialchars($drive['qualification'] ?? 'Any'); ?></strong> &nbsp;|&nbsp; 
                        ⚡ Status: <span class="text-success" style="font-weight:700;"><?php echo htmlspecialchars($drive['eligibility_status']); ?></span>
                      </div>

                      <a href="view-job-post.php?id=<?php echo $drive['id_jobpost']; ?>" class="btn btn-indigo" style="background:#4f46e5;color:#ffffff;font-weight:700;padding:10px 22px;border-radius:8px;text-decoration:none;box-shadow:0 4px 12px rgba(79,70,229,0.25);">
                        View Drive & Apply &rarr;
                      </a>
                    </div>

                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="alert alert-warning text-center" style="border-radius:12px;padding:24px;">
                  <h4>⚠️ No Active Drives Found</h4>
                  <p>There are currently no active placement drives in the database to recommend.</p>
                </div>
              <?php endif; ?>

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

<?php
session_start();

if (empty($_SESSION['id_admin'])) {
  header("Location: index.php");
  exit();
}

require_once("../db.php");
require_once("../php/gemini-api.php");

$msg = "";
$msg_type = "";

// Handle Save API Key
if (isset($_POST['save_gemini_key'])) {
  $key = trim($_POST['gemini_api_key']);
  if (saveGeminiAPIKey($key)) {
    $msg = "Google AI Studio (Gemini 1.5) API Key saved successfully!";
    $msg_type = "success";
  } else {
    $msg = "Failed to save API Key.";
    $msg_type = "danger";
  }
}

// Handle Test API Key
$testResult = null;
if (isset($_POST['test_gemini_key'])) {
  $testKey = trim($_POST['gemini_api_key']);
  if (empty($testKey)) {
    $testKey = getGeminiAPIKey();
  }

  if (!empty($testKey)) {
    $dummyUser = [
      'firstname' => 'Test', 'lastname' => 'Candidate',
      'qualification' => 'MCA', 'stream' => 'Computer Science',
      'skills' => 'Python, MySQL, Web Development', 'aboutme' => 'Enthusiastic software developer.'
    ];
    $dummyJob = [
      'jobtitle' => 'Software Engineer',
      'qualification' => 'MCA', 'experience' => 'Developer',
      'description' => 'Looking for Python and SQL developers with computer science background.'
    ];

    $res = callGeminiResumeAI($testKey, $dummyUser, $dummyJob);
    if (isset($res['score'])) {
      $msg = "🎉 Test Successful! Gemini 1.5 Flash AI responded cleanly with score: " . $res['score'] . "%";
      $msg_type = "success";
      $testResult = $res;
    } else {
      $msg = "❌ Test Failed: " . ($res['error'] ?? 'Invalid response');
      $msg_type = "danger";
    }
  } else {
    $msg = "Please enter an API Key to test.";
    $msg_type = "warning";
  }
}

$currentKey = getGeminiAPIKey();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Google AI Studio BYOK Settings | Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .card-byok { background:#fff; border-radius:16px; padding:28px; box-shadow:0 6px 24px rgba(79,70,229,0.08); border-top:4px solid #4f46e5; margin-bottom:24px; }
    .badge-status { font-weight:700; font-size:12px; padding:5px 12px; border-radius:50px; }
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
                  <h3 class="box-title">Welcome <b>Admin</b></h3>
                </div>
                <div class="box-body no-padding">
                  <ul class="nav nav-pills nav-stacked">
                    <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="active-jobs.php"><i class="fa fa-briefcase"></i> Active Drives</a></li>
                    <li><a href="applications.php"><i class="fa fa-address-card-o"></i> Students Profile</a></li>
                    <li><a href="companies.php"><i class="fa fa-building"></i> Placement Cell / Coordinators</a></li>
                    <li><a href="manage-admins.php"><i class="fa fa-user-secret"></i> Manage Admins</a></li>
                    <li><a href="create-tester-account.php"><i class="fa fa-flask"></i> Create Tester ID</a></li>
                    <li class="active"><a href="ai-settings.php"><i class="fa fa-key"></i> Gemini AI (BYOK)</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9 bg-white padding-2">

              <?php if (!empty($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <?php echo $msg; ?>
                </div>
              <?php endif; ?>

              <h3>🤖 Google AI Studio (Gemini 1.5 Flash) BYOK Configuration</h3>
              <p class="text-muted">Bring Your Own Key (BYOK) System — Enter your free Gemini API key from Google AI Studio to power real LLM resume shortlisting.</p>
              <hr>

              <div class="card-byok">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                  <div>
                    <h4 style="margin:0;font-weight:700;color:#1e293b;">Gemini 1.5 Flash API Key Status</h4>
                    <small style="color:#64748b;">Global Site API Key for AI Resume Eligibility Engine</small>
                  </div>
                  <?php if (!empty($currentKey)): ?>
                    <span class="badge-status" style="background:#dcfce7;color:#15803d;">✅ ACTIVE & CONFIGURED</span>
                  <?php else: ?>
                    <span class="badge-status" style="background:#fee2e2;color:#b91c1c;">⚠️ NOT CONFIGURED (Using Local Engine)</span>
                  <?php endif; ?>
                </div>

                <form method="post" action="ai-settings.php">
                  <div class="form-group">
                    <label>Google Gemini API Key (from Google AI Studio) *</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-key"></i></span>
                      <input type="password" name="gemini_api_key" id="gemini_api_key" class="form-control input-lg" value="<?php echo htmlspecialchars($currentKey); ?>" placeholder="AIzaSy..." required>
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-lg" onclick="toggleKeyVisibility()"><i class="fa fa-eye" id="eyeIcon"></i></button>
                      </span>
                    </div>
                    <small class="text-muted" style="display:block;margin-top:6px;">
                      Don't have a key? Get a free API key from <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:#4f46e5;font-weight:700;">Google AI Studio Console &rarr;</a>
                    </small>
                  </div>

                  <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap;">
                    <button type="submit" name="save_gemini_key" class="btn btn-primary btn-lg">
                      <i class="fa fa-save"></i> Save Global API Key
                    </button>
                    <button type="submit" name="test_gemini_key" class="btn btn-info btn-lg">
                      <i class="fa fa-bolt"></i> Test Connection
                    </button>
                  </div>
                </form>
              </div>

              <?php if ($testResult): ?>
                <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:20px;margin-top:20px;">
                  <h5 style="margin-top:0;font-weight:700;color:#0f172a;">⚡ Raw Gemini AI API Test Response</h5>
                  <pre style="background:#0f172a;color:#38bdf8;padding:14px;border-radius:8px;font-size:13px;"><?php echo json_encode($testResult, JSON_PRETTY_PRINT); ?></pre>
                </div>
              <?php endif; ?>

              <div class="box box-solid margin-top-30" style="border-radius:12px;border:1px solid #e2e8f0;">
                <div class="box-header with-border">
                  <h4 class="box-title" style="font-weight:700;color:#1e293b;">📘 How to Get Your Free Google AI Studio API Key</h4>
                </div>
                <div class="box-body" style="line-height:1.7;color:#475569;">
                  <ol>
                    <li>Go to <strong><a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio (aistudio.google.com)</a></strong>.</li>
                    <li>Sign in with any Google account.</li>
                    <li>Click <strong>"Create API Key"</strong> in Google AI Studio.</li>
                    <li>Copy your API Key (starts with <code>AIzaSy...</code>) and paste it in the box above.</li>
                    <li>Click <strong>"Save Global API Key"</strong> — All student resume shortlisting checks will now run on real Google Gemini 1.5 AI!</li>
                  </ol>
                </div>
              </div>

            </div>
          </div>
        </div>
      </section>
    </div>

    <footer class="main-footer" style="margin-left: 0px;">
      <div class="text-center">
        <strong>Copyright &copy; 2025 Placement Portal.</strong> All rights reserved.
      </div>
    </footer>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="../js/adminlte.min.js"></script>
  <script>
    function toggleKeyVisibility() {
      var input = document.getElementById('gemini_api_key');
      var icon = document.getElementById('eyeIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'fa fa-eye';
      }
    }
  </script>
</body>

</html>

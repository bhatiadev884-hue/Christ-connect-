<?php

//To Handle Session Variables on This Page
session_start();

//If user Not logged in then redirect them back to homepage. 
if (empty($_SESSION['id_user'])) {
  header("Location: ../index.php");
  exit();
}

require_once("../db.php");
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Student Dashboard — Christ Career Connect</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Premium Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <!-- Dashboard Premium Override -->
  <link rel="stylesheet" href="../css/dashboard-premium.css">
</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">



    <?php
    include 'header.php'
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-left: 0px;">

      <section id="candidates" class="content-header">
        <div class="container">
          <div class="row">
            <div class="col-md-3">
              <div id="star" class="box box-solid">
                <div class="box-header with-border">
                  <h3 class="box-title">Welcome <b><?php echo $_SESSION['name']; ?></b></h3>
                </div>
                <div class="box-body no-padding">
                  <ul class="nav nav-pills nav-stacked">
                    <li><a href="edit-profile.php"><i class="fa fa-user"></i> Edit Profile</a></li>
                    <li><a href="ai-assistant.php"><i class="fa fa-comments text-indigo"></i> AI Career Assistant</a></li>
                    <li><a href="ai-mock-interview.php"><i class="fa fa-microphone text-purple"></i> AI Mock Interview</a></li>
                    <li><a href="skill-gap-analysis.php"><i class="fa fa-bullseye text-indigo"></i> AI Skill Gap Analysis</a></li>
                    <li><a href="ai-job-recommendations.php"><i class="fa fa-briefcase text-green"></i> AI Drive Match</a></li>
                    <li><a href="resume-review.php"><i class="fa fa-file-text-o"></i> AI Resume ATS Review</a></li>
                    <li class="active"><a href="index.php"><i class="fa fa-address-card-o"></i> My Applications</a></li>
                    <li><a href="mailbox.php"><i class="fa fa-envelope"></i> Mailbox</a></li>
                    <li><a href="settings.php"><i class="fa fa-gear"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>

                </div>
              </div>
            </div>
            <div class="col-md-9 bg-white padding-2">

              <!-- AI Features Grid (2x2) -->
              <div class="row" style="margin-bottom:24px;">
                
                <!-- Card 1: AI Career Assistant Chatbot -->
                <div class="col-md-6" style="margin-bottom:16px;">
                  <div style="background:linear-gradient(135deg, #eef2ff, #ffffff);border:1px solid #c7d2fe;border-left:5px solid #4f46e5;border-radius:14px;padding:20px;height:100%;box-shadow:0 6px 20px rgba(79,70,229,0.06);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                      <h4 style="margin:0 0 6px 0;font-weight:800;color:#312e81;font-family:'Outfit',sans-serif;">🤖 AI Career Assistant Chatbot</h4>
                      <p style="margin:0 0 14px 0;color:#4338ca;font-size:13px;line-height:1.5;">
                        Ask PlaceMentor AI anything: "Am I eligible for this drive?", "What skills for Java Developer?", or "How to improve my resume?".
                      </p>
                    </div>
                    <div>
                      <a href="ai-assistant.php" class="btn btn-indigo" style="background:#4f46e5;color:#ffffff;font-weight:700;padding:8px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(79,70,229,0.25);">
                        <i class="fa fa-comments"></i> Chat with PlaceMentor AI
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Card 2: AI Mock Interview Simulator -->
                <div class="col-md-6" style="margin-bottom:16px;">
                  <div style="background:linear-gradient(135deg, #f3e8ff, #ffffff);border:1px solid #d8b4fe;border-left:5px solid #7c3aed;border-radius:14px;padding:20px;height:100%;box-shadow:0 6px 20px rgba(124,58,237,0.06);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                      <h4 style="margin:0 0 6px 0;font-weight:800;color:#581c87;font-family:'Outfit',sans-serif;">🎤 AI Mock Interview Simulator</h4>
                      <p style="margin:0 0 14px 0;color:#6b21a8;font-size:13px;line-height:1.5;">
                        Select a target role (e.g. Java Developer) and practice a simulated technical interview with real-time AI scoring & feedback.
                      </p>
                    </div>
                    <div>
                      <a href="ai-mock-interview.php" class="btn" style="background:#7c3aed;color:#ffffff;font-weight:700;padding:8px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(124,58,237,0.25);">
                        <i class="fa fa-microphone"></i> Start Mock Interview
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Card 3: AI Placement Drive Match -->
                <div class="col-md-6" style="margin-bottom:16px;">
                  <div style="background:linear-gradient(135deg, #f0fdf4, #ffffff);border:1px solid #bbf7d0;border-left:5px solid #059652;border-radius:14px;padding:20px;height:100%;box-shadow:0 6px 20px rgba(5,150,82,0.06);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                      <h4 style="margin:0 0 6px 0;font-weight:800;color:#166534;font-family:'Outfit',sans-serif;">💼 AI Placement Drive Match</h4>
                      <p style="margin:0 0 14px 0;color:#15803d;font-size:13px;line-height:1.5;">
                        Discover suitable drives matched by AI based on your Skills, Degree, Academic CGPA, Projects & Preferred Role.
                      </p>
                    </div>
                    <div>
                      <a href="ai-job-recommendations.php" class="btn btn-success" style="background:#059652;color:#ffffff;font-weight:700;padding:8px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(5,150,82,0.25);">
                        <i class="fa fa-briefcase"></i> View Recommended Drives
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Card 4: AI Skill Gap & Roadmap -->
                <div class="col-md-6" style="margin-bottom:16px;">
                  <div style="background:linear-gradient(135deg, #fff7ed, #ffffff);border:1px solid #fed7aa;border-left:5px solid #c2410c;border-radius:14px;padding:20px;height:100%;box-shadow:0 6px 20px rgba(194,65,12,0.06);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                      <h4 style="margin:0 0 6px 0;font-weight:800;color:#9a3412;font-family:'Outfit',sans-serif;">🔍 AI Skill Gap & Roadmap</h4>
                      <p style="margin:0 0 14px 0;color:#c2410c;font-size:13px;line-height:1.5;">
                        Compare your skills against target roles like Software Developer and get a custom step-by-step learning roadmap.
                      </p>
                    </div>
                    <div>
                      <a href="skill-gap-analysis.php" class="btn" style="background:#c2410c;color:#ffffff;font-weight:700;padding:8px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(194,65,12,0.25);">
                        <i class="fa fa-bullseye"></i> Launch Skill Gap Analysis
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Card 5: AI Resume Builder -->
                <div class="col-md-6" style="margin-bottom:16px;">
                  <div style="background:linear-gradient(135deg, #ecfdf5, #ffffff);border:1px solid #a7f3d0;border-left:5px solid #059669;border-radius:14px;padding:20px;height:100%;box-shadow:0 6px 20px rgba(5,150,105,0.06);display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                      <h4 style="margin:0 0 6px 0;font-weight:800;color:#065f46;font-family:'Outfit',sans-serif;">📄 AI Resume Builder</h4>
                      <p style="margin:0 0 14px 0;color:#047857;font-size:13px;line-height:1.5;">
                        Build a professional, ATS-ready resume in minutes. Auto-fill from your profile, choose a stunning template, and let AI write polished content for you.
                      </p>
                    </div>
                    <div>
                      <a href="ai-resume-builder.php" class="btn" style="background:#059669;color:#ffffff;font-weight:700;padding:8px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(5,150,105,0.25);">
                        <i class="fa fa-file-text-o"></i> Build My Resume with AI
                      </a>
                    </div>
                  </div>
                </div>

              </div>


              <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fa fa-info"></i> Update your profile, if you are a new user.
              </div>

              <h2>Applied Drives</h2>
              <p>Below you will find job roles you have applied for</p>

              <?php
              $sql = "SELECT * FROM job_post INNER JOIN apply_job_post ON job_post.id_jobpost=apply_job_post.id_jobpost WHERE apply_job_post.id_user='$_SESSION[id_user]'";
              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
              ?>
                  <?php

                  if ($row['status'] == 0) {
                  ?>
                    <div class="alert alert-info alert-dismissible">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      <i class="icon fa fa-info"></i> Congratulations, you have been placed in <?php echo $row['jobtitle']; ?>.
                    </div>
                  <?php
                  }
                  ?>
                  <div class="attachment-block clearfix padding-2">
                    <h4 class="attachment-heading"><a href="view-job-post.php?id=<?php echo $row['id_jobpost']; ?>"><?php echo $row['jobtitle']; ?></a></h4>
                    <div class="attachment-text padding-2">
                      <div class="pull-left"><i class="fa fa-calendar"></i> <?php echo $row['createdat']; ?></div>
                      <?php

                      if ($row['status'] == 0) {
                        echo '<div class="pull-right"><strong class="text-orange">Placed</strong></div>';
                      } else if ($row['status'] == 1) {
                        echo '<div class="pull-right"><strong class="text-red">Rejected</strong></div>';
                      } else if ($row['status'] == 2) {
                        echo '<div class="pull-right"><strong class="text-green">Applied</strong></div> ';
                      }
                      ?>

                    </div>
                  </div>

              <?php
                }
              }
              ?>

            </div>
          </div>
        </div>
      </section>



    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer" style="margin-left: 0px;">
      <div class="text-center">
        <strong>Copyright &copy; 2025 <a href="learningfromscratch.online">Placement Portal</a>.</strong> All rights
        reserved.
      </div>
    </footer>

    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>

  </div>
  <!-- ./wrapper -->

  <!-- jQuery 3 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../js/adminlte.min.js"></script>
</body>

</html>

<style>
  /* dashboard-premium.css handles all styling */
  li { color: inherit; }
  @media only screen and (max-width: 989px) {
    .box { margin: auto; }
  }
</style>


<script src="../js/sweetalert.js"></script>

<?php
if (isset($_SESSION['status1'])  && $_SESSION['status1'] != '') {

?>

  <script>
    swal({
      title: "<?php echo $_SESSION['status1']; ?>",
      text: " You have successfully applied for the drive.",
      icon: "<?php echo $_SESSION['status_code1']; ?>",
      button: "Okay",
    });
  </script>

<?php

  unset($_SESSION['status1']);
}

?>
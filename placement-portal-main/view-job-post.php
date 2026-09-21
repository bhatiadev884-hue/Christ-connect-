<?php

session_start();
require_once("db.php");
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="css/AdminLTE.min.css">
  <link rel="stylesheet" href="css/_all-skins.min.css">
  <!-- Custom -->
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">

    <?php
    include 'uploads/jobs_header.php'
    ?>


    <div class="content-wrapper" style="margin-left: 0px;">

      <?php

      $sql = "SELECT * FROM job_post INNER JOIN company ON job_post.id_company=company.id_company WHERE id_jobpost='$_GET[id]'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $_SESSION['id_company'] = $row['id_company'];

      ?>

          <section id="candidates" class="content-header">
            <div class="container">
              <div class="row">
                <div class="class col-md-2"></div>
                <div class="col-md-8 bg-white padding-2">
                  <div class="pull-left mx-32">
                    <h2><b><?php echo $row['jobtitle']; ?></b></h2>
                  </div>
                  <div class="pull-right">
                    <a href="jobs.php" class="btn btn-default btn-lg btn-flat margin-top-20"><i class="fa fa-arrow-circle-left"></i> Back</a>
                  </div>
                  <div class="clearfix"></div>
                  <hr>
                  <div>
                    <p><span class="margin-right-10"><i class="fa fa-location-arrow text-green"> Role: </i> <?php echo $row['experience']; ?> </span><span class="margin-right-10"> <i class="fa fa-money text-green"> CTC:</i> <?php echo "Rs " . $row['minimumsalary'] . "    "; ?></span> <span class="margin-right-10"><i class="fa fa-calendar text-green"> Drive Date:</i> <?php echo date("d-M-Y", strtotime($row['createdat'])); ?></span><span class="margin-right-10"><i class="fa fa-location-calendar text-green"> Eligibility: </i> <?php echo $row['maximumsalary'] . "%"; ?> </span></p>
                  </div>

                  <?php
                  // ── AI Resume Shortlist Analyzer ──
                  if (isset($_SESSION["id_user"])) {
                    require_once("php/ai-resume-matcher.php");
                    $uQ = $conn->query("SELECT * FROM users WHERE id_user='$_SESSION[id_user]'");
                    if ($uQ && $uQ->num_rows > 0) {
                      $uProf = $uQ->fetch_assoc();
                      $aiRes = analyzeResumeMatch($uProf, $row);
                      echo renderAIMatchCard($aiRes);
                    }
                  }
                  ?>

                  <div>
                    <?php echo stripcslashes($row['description']); ?>
                  </div>
                  <?php
                  if (isset($_SESSION["id_user"]) && empty($_SESSION['companyLogged'])) { ?>
                    <div style="margin-top:20px;">
                      <a onclick="eligiblefunction()" href="user/checkeligibility.php?id=<?php echo $row['id_jobpost']; ?>" class="btn btn-primary btn-flat me-2">Check Eligibility</a>
                      <a onclick="reallyfunction()" href="apply.php?id=<?php echo $row['id_jobpost']; ?>" class="btn btn-success btn-flat">Apply Now</a>
                    </div>
                  <?php } ?>



                </div>

              </div>
            </div>
          </section>
      <?php
        }
      }
      ?>



    </div>

    <footer class="main-footer" style="margin-left: 0px;">
      <div class="text-center">
        <strong>Copyright &copy; 2022 <a href="learningfromscratch.online">Placement Portal</a>.</strong> All rights
        reserved.
      </div>
    </footer>
    <div class="control-sidebar-bg"></div>

  </div>
  <!-- ./wrapper -->

  <!-- jQuery 3 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- AdminLTE App -->
  <script src="js/adminlte.min.js"></script>
</body>

</html>

<script src="js/sweetalert.js"></script>

<?php
if (isset($_SESSION['status'])  && $_SESSION['status'] != '') {

?>

  <script>
    swal("<?php echo $_SESSION['status'];  ?>");
  </script>

<?php

  unset($_SESSION['status']);
}

?>
<?php
session_start();

if (empty($_SESSION['id_admin'])) {
  header("Location: index.php");
  exit();
}

require_once(__DIR__ . "/../db.php");

$msg = "";
$msg_type = "";

if (isset($_POST['register_coordinator'])) {
  $name        = $conn->real_escape_string( $_POST['name']);
  $companyname = $conn->real_escape_string( $_POST['companyname']);
  $email       = $conn->real_escape_string( $_POST['email']);
  $password    = $conn->real_escape_string( $_POST['password']);
  $contactno   = $conn->real_escape_string( $_POST['contactno']);
  $website     = $conn->real_escape_string( $_POST['website']);
  $country     = $conn->real_escape_string( $_POST['country']);
  $state       = $conn->real_escape_string( $_POST['state']);
  $city        = $conn->real_escape_string( $_POST['city']);
  $aboutme     = $conn->real_escape_string( $_POST['aboutme']);

  // Encrypt password using app standard
  $enc_password = base64_encode(strrev(md5($password)));

  $check = $conn->query("SELECT id_company FROM company WHERE email='$email'");
  if ($check->num_rows == 0) {
    // Insert with active='1' (Directly active when registered by Admin)
    $sql = "INSERT INTO company (name, companyname, country, state, city, contactno, website, email, password, aboutme, logo, active)
            VALUES ('$name', '$companyname', '$country', '$state', '$city', '$contactno', '$website', '$email', '$enc_password', '$aboutme', 'default_logo.png', '1')";

    if ($conn->query($sql)) {
      $msg = "Placement Cell Coordinator '$companyname' ($email) registered and activated successfully!";
      $msg_type = "success";
    } else {
      $msg = "Error registering coordinator: " . $conn->error;
      $msg_type = "danger";
    }
  } else {
    $msg = "A Placement Cell coordinator with email '$email' already exists!";
    $msg_type = "warning";
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Add Placement Cell Coordinator | Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">

    <?php include 'header.php'; ?>

    <div class="content-wrapper" style="margin-left: 0px;">
      <section class="content-header">
        <div class="container">
          <div class="row">
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
                    <li class="active"><a href="companies.php"><i class="fa fa-building"></i> Placement Cell / Coordinators</a></li>
                    <li><a href="manage-admins.php"><i class="fa fa-user-secret"></i> Manage Admins</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-md-9 bg-white padding-2">
              <?php if (!empty($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <?php echo $msg; ?>
                </div>
              <?php endif; ?>

              <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>🏢 Register Placement Cell Coordinator</h3>
                <a href="companies.php" class="btn btn-default pull-right"><i class="fa fa-arrow-left"></i> Back to Coordinators List</a>
              </div>
              <p class="text-muted">Register a new Placement Cell Coordinator account. Accounts registered by Admin are activated immediately.</p>
              <hr>

              <form method="post" action="create-coordinator.php">
                <div class="row">
                  <div class="col-md-6 form-group">
                    <label>Coordinator Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Priya Sharma" required>
                  </div>
                  <div class="col-md-6 form-group">
                    <label>Department / Placement Cell Name *</label>
                    <input type="text" name="companyname" class="form-control" placeholder="e.g. Christ University Placement Cell" required>
                  </div>
                  <div class="col-md-6 form-group">
                    <label>Official Email *</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. placement@christccc.edu" required>
                  </div>
                  <div class="col-md-6 form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password for login" required>
                  </div>
                  <div class="col-md-6 form-group">
                    <label>Contact Phone Number *</label>
                    <input type="text" name="contactno" class="form-control" placeholder="e.g. 9876543210" required>
                  </div>
                  <div class="col-md-6 form-group">
                    <label>Website</label>
                    <input type="text" name="website" class="form-control" placeholder="https://ncr.christuniversity.in/">
                  </div>
                  <div class="col-md-4 form-group">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" value="India">
                  </div>
                  <div class="col-md-4 form-group">
                    <label>State</label>
                    <input type="text" name="state" class="form-control" value="Delhi">
                  </div>
                  <div class="col-md-4 form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" value="Delhi">
                  </div>
                  <div class="col-md-12 form-group">
                    <label>About / Description</label>
                    <textarea name="aboutme" class="form-control" rows="3" placeholder="Official Placement Cell description..."></textarea>
                  </div>
                </div>
                <button type="submit" name="register_coordinator" class="btn btn-primary btn-lg"><i class="fa fa-check-circle"></i> Register & Activate Coordinator</button>
              </form>

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
</body>

</html>

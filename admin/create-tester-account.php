<?php
session_start();

if (empty($_SESSION['id_admin'])) {
  header("Location: index.php");
  exit();
}

require_once("../db.php");

$msg = "";
$msg_type = "";
$created_info = null;

if (isset($_POST['create_tester'])) {
  $role       = $conn->real_escape_string( $_POST['role']);
  $name       = $conn->real_escape_string( $_POST['name']);
  $identifier = $conn->real_escape_string( $_POST['identifier']); // email or username
  $password   = $conn->real_escape_string( $_POST['password']);
  
  if (!empty($role) && !empty($identifier) && !empty($password)) {

    if ($role == 'admin') {
      // Check existing admin
      $check = $conn->query("SELECT id_admin FROM admin WHERE username='$identifier'");
      if ($check->num_rows == 0) {
        $sql = "INSERT INTO admin (username, password) VALUES ('$identifier', '$password')";
        if ($conn->query($sql)) {
          $msg = "✅ Tester Admin account created successfully!";
          $msg_type = "success";
          $created_info = [
            'role' => 'Admin 🛡️',
            'login_url' => 'admin/index.php',
            'user_label' => 'Username',
            'user_val' => $identifier,
            'pass' => $password
          ];
        } else {
          $msg = "Database Error: " . $conn->error;
          $msg_type = "danger";
        }
      } else {
        $msg = "Admin username '$identifier' already exists!";
        $msg_type = "warning";
      }

    } else if ($role == 'company') {
      // Check existing company email
      $check = $conn->query("SELECT id_company FROM company WHERE email='$identifier'");
      if ($check->num_rows == 0) {
        $enc_pass = base64_encode(strrev(md5($password)));
        $compName = !empty($name) ? $name : 'Tester Placement Cell';
        $contact  = '9998887776';
        
        $sql = "INSERT INTO company (name, companyname, country, state, city, contactno, website, email, password, aboutme, logo, active)
                VALUES ('Tester Officer', '$compName', 'India', 'Delhi', 'Delhi', '$contact', 'https://christccc.edu', '$identifier', '$enc_pass', 'Tester Placement Cell Account created by Admin', 'default_logo.png', '1')";
        
        if ($conn->query($sql)) {
          $msg = "✅ Tester Placement Coordinator account created and activated!";
          $msg_type = "success";
          $created_info = [
            'role' => 'Placement Coordinator 🏢',
            'login_url' => 'login-company.php',
            'user_label' => 'Email',
            'user_val' => $identifier,
            'pass' => $password
          ];
        } else {
          $msg = "Database Error: " . $conn->error;
          $msg_type = "danger";
        }
      } else {
        $msg = "Placement Cell email '$identifier' already exists!";
        $msg_type = "warning";
      }

    } else if ($role == 'student') {
      // Check existing student email
      $check = $conn->query("SELECT id_user FROM users WHERE email='$identifier'");
      if ($check->num_rows == 0) {
        $enc_pass = base64_encode(strrev(md5($password)));
        $parts    = explode(' ', trim($name));
        $fname    = $parts[0] ?? 'Tester';
        $lname    = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'Student';
        $hash     = md5(uniqid());

        $sql = "INSERT INTO users (firstname, lastname, email, password, address, city, state, contactno, qualification, stream, passingyear, dob, age, designation, resume, hash, aboutme, skills, active)
                VALUES ('$fname', '$lname', '$identifier', '$enc_pass', 'Christ University Campus', 'Delhi', 'Delhi', '9876543210', 'MCA', 'Computer Science', '2025', '2001-01-01', '23', 'Student Tester', '', '$hash', 'Tester Student Profile created by Admin', 'Python, Java, Web Development', '1')";

        if ($conn->query($sql)) {
          $msg = "✅ Tester Student account created and activated!";
          $msg_type = "success";
          $created_info = [
            'role' => 'Student 🎓',
            'login_url' => 'login-candidates.php',
            'user_label' => 'Email',
            'user_val' => $identifier,
            'pass' => $password
          ];
        } else {
          $msg = "Database Error: " . $conn->error;
          $msg_type = "danger";
        }
      } else {
        $msg = "Student email '$identifier' already exists!";
        $msg_type = "warning";
      }
    }

  } else {
    $msg = "Please select role and fill in all required fields.";
    $msg_type = "warning";
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Create Tester Account | Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .card-tester { background: #fff; border-radius: 14px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-top: 4px solid #4f46e5; }
    .role-select-box { border: 2px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 12px; cursor: pointer; transition: 0.2s; }
    .role-select-box:hover { border-color: #4f46e5; background: #f8fafc; }
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
                    <li class="active"><a href="create-tester-account.php"><i class="fa fa-flask"></i> Create Tester ID</a></li>
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

              <?php if ($created_info): ?>
                <div style="background:#eef2ff;border:1px solid #c7d2fe;padding:20px;border-radius:12px;margin-bottom:24px;">
                  <h4 style="margin-top:0;color:#3730a3;font-weight:700;">🎉 Tester Credentials Ready!</h4>
                  <p style="margin-bottom:8px;"><strong>Role:</strong> <?php echo $created_info['role']; ?></p>
                  <p style="margin-bottom:8px;"><strong><?php echo $created_info['user_label']; ?>:</strong> <code><?php echo $created_info['user_val']; ?></code></p>
                  <p style="margin-bottom:8px;"><strong>Password:</strong> <code><?php echo $created_info['pass']; ?></code></p>
                  <p style="margin-bottom:0;"><strong>Login Link:</strong> <a href="../<?php echo $created_info['login_url']; ?>" target="_blank">http://localhost:8000/<?php echo $created_info['login_url']; ?></a></p>
                </div>
              <?php endif; ?>

              <h3>🧪 Create Tester Account Generator</h3>
              <p class="text-muted">Select the role below to instantly create an active test account for demo and testing purposes.</p>
              <hr>

              <div class="card-tester">
                <form method="post" action="create-tester-account.php">
                  
                  <div class="form-group">
                    <label style="font-weight:700;font-size:15px;color:#1e293b;">Select Role for Tester Account *</label>
                    <select name="role" id="roleSelect" class="form-control input-lg" required onchange="updateFormLabels()">
                      <option value="">-- Choose Account Role --</option>
                      <option value="student">🎓 Student Account</option>
                      <option value="company">🏢 Placement Cell / Coordinator Account</option>
                      <option value="admin">🛡️ Admin Account</option>
                    </select>
                  </div>

                  <div class="form-group margin-top-20">
                    <label id="nameLabel">Display Name / Title</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Test Student / Test Dept">
                  </div>

                  <div class="form-group">
                    <label id="identifierLabel">Email / Username *</label>
                    <input type="text" name="identifier" class="form-control" placeholder="e.g. teststudent@christccc.edu or testadmin" required>
                  </div>

                  <div class="form-group">
                    <label>Password *</label>
                    <input type="text" name="password" class="form-control" value="Test@1234" required>
                    <small class="text-muted">Default test password generated. You can change it if desired.</small>
                  </div>

                  <button type="submit" name="create_tester" class="btn btn-primary btn-lg btn-block margin-top-20">
                    <i class="fa fa-magic"></i> Generate & Activate Tester Account
                  </button>
                </form>
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
    function updateFormLabels() {
      var role = document.getElementById('roleSelect').value;
      var idLabel = document.getElementById('identifierLabel');
      var nameLabel = document.getElementById('nameLabel');

      if (role === 'admin') {
        idLabel.innerText = "Admin Username *";
        nameLabel.innerText = "Admin Name / Alias";
      } else if (role === 'company') {
        idLabel.innerText = "Placement Cell Email *";
        nameLabel.innerText = "Placement Cell / Department Name";
      } else {
        idLabel.innerText = "Student Email *";
        nameLabel.innerText = "Student Full Name";
      }
    }
  </script>
</body>

</html>

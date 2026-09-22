<?php
session_start();

if (empty($_SESSION['id_admin'])) {
  header("Location: index.php");
  exit();
}

require_once("../db.php");

$msg = "";
$msg_type = "";

// ── Handle Add New Admin ──
if (isset($_POST['add_admin'])) {
  $username = $conn->real_escape_string( $_POST['username']);
  $password = $conn->real_escape_string( $_POST['password']);

  if (!empty($username) && !empty($password)) {
    $check = $conn->query("SELECT * FROM admin WHERE username='$username'");
    if ($check->num_rows == 0) {
      $sql = "INSERT INTO admin (username, password) VALUES ('$username', '$password')";
      if ($conn->query($sql)) {
        $msg = "New Admin account '$username' created successfully!";
        $msg_type = "success";
      } else {
        $msg = "Database Error: " . $conn->error;
        $msg_type = "danger";
      }
    } else {
      $msg = "Username '$username' already exists!";
      $msg_type = "warning";
    }
  } else {
    $msg = "Please fill in all fields.";
    $msg_type = "warning";
  }
}

// ── Handle Update Profile / Password ──
if (isset($_POST['update_profile'])) {
  $id_admin_edit = $conn->real_escape_string( $_POST['id_admin']);
  $new_username = $conn->real_escape_string( $_POST['username']);
  $new_password = $conn->real_escape_string( $_POST['password']);

  if (!empty($new_username) && !empty($new_password)) {
    $sql = "UPDATE admin SET username='$new_username', password='$new_password' WHERE id_admin='$id_admin_edit'";
    if ($conn->query($sql)) {
      $msg = "Admin profile updated successfully!";
      $msg_type = "success";
    } else {
      $msg = "Error updating profile: " . $conn->error;
      $msg_type = "danger";
    }
  }
}

// ── Handle Delete Admin ──
if (isset($_GET['delete_id'])) {
  $del_id = $conn->real_escape_string( $_GET['delete_id']);
  if ($del_id != $_SESSION['id_admin']) {
    $conn->query("DELETE FROM admin WHERE id_admin='$del_id'");
    $msg = "Admin account deleted successfully.";
    $msg_type = "success";
  } else {
    $msg = "You cannot delete your own active session account!";
    $msg_type = "danger";
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Manage Admins | Placement Portal</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <link rel="stylesheet" href="../css/_all-skins.min.css">
  <link rel="stylesheet" href="../css/custom.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    .card-box { background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); margin-bottom: 24px; }
    .btn-custom { background: #4f46e5; color: #fff; border: none; font-weight: 600; padding: 8px 20px; border-radius: 6px; }
    .btn-custom:hover { background: #4338ca; color: #fff; }
  </style>
</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">

    <?php include 'header.php'; ?>

    <div class="content-wrapper" style="margin-left: 0px;">
      <section class="content-header">
        <div class="container">
          <div class="row">
            <!-- Sidebar Nav -->
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
                    <li class="active"><a href="manage-admins.php"><i class="fa fa-user-secret"></i> Manage Admins</a></li>
                    <li><a href="../logout.php"><i class="fa fa-arrow-circle-o-right"></i> Logout</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 bg-white padding-2">
              
              <?php if (!empty($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <?php echo $msg; ?>
                </div>
              <?php endif; ?>

              <h3>🛡️ Admin Management & Profile Control</h3>
              <p class="text-muted">Create new administrators or edit existing admin profiles and credentials.</p>
              
              <hr>

              <!-- Row: Add Admin & Edit Current Profile -->
              <div class="row margin-top-20">
                <!-- Create New Admin -->
                <div class="col-md-6">
                  <div class="card-box">
                    <h4 style="color:#4f46e5;font-weight:700;"><i class="fa fa-user-plus"></i> Create New Admin</h4>
                    <form method="post" action="manage-admins.php">
                      <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter admin username" required>
                      </div>
                      <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter admin password" required>
                      </div>
                      <button type="submit" name="add_admin" class="btn btn-custom btn-block"><i class="fa fa-check"></i> Create Admin Account</button>
                    </form>
                  </div>
                </div>

                <!-- Update Logged-in Admin Profile -->
                <?php
                $curr_id = $_SESSION['id_admin'];
                $curr_res = $conn->query("SELECT * FROM admin WHERE id_admin='$curr_id'");
                $curr_admin = $curr_res->fetch_assoc();
                ?>
                <div class="col-md-6">
                  <div class="card-box">
                    <h4 style="color:#059652;font-weight:700;"><i class="fa fa-key"></i> Edit My Profile / Password</h4>
                    <form method="post" action="manage-admins.php">
                      <input type="hidden" name="id_admin" value="<?php echo $curr_admin['id_admin']; ?>">
                      <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($curr_admin['username']); ?>" required>
                      </div>
                      <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control" value="<?php echo htmlspecialchars($curr_admin['password']); ?>" required>
                      </div>
                      <button type="submit" name="update_profile" class="btn btn-success btn-block"><i class="fa fa-save"></i> Save Profile Changes</button>
                    </form>
                  </div>
                </div>
              </div>

              <!-- List All Admins Table -->
              <h4 style="margin-top:30px;font-weight:700;"><i class="fa fa-users"></i> Existing Administrator Accounts</h4>
              <div class="table-responsive margin-top-10">
                <table class="table table-bordered table-striped">
                  <thead style="background:#f8fafc;">
                    <tr>
                      <th>ID</th>
                      <th>Admin Username</th>
                      <th>Password</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql_admins = "SELECT * FROM admin";
                    $result_admins = $conn->query($sql_admins);
                    if ($result_admins->num_rows > 0) {
                      while ($row_admin = $result_admins->fetch_assoc()) {
                    ?>
                        <tr>
                          <td>#<?php echo $row_admin['id_admin']; ?></td>
                          <td>
                            <strong><?php echo htmlspecialchars($row_admin['username']); ?></strong>
                            <?php if ($row_admin['id_admin'] == $_SESSION['id_admin']): ?>
                              <span class="label label-success">You</span>
                            <?php endif; ?>
                          </td>
                          <td><code><?php echo htmlspecialchars($row_admin['password']); ?></code></td>
                          <td>
                            <?php if ($row_admin['id_admin'] != $_SESSION['id_admin']): ?>
                              <a href="manage-admins.php?delete_id=<?php echo $row_admin['id_admin']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this admin account?');">
                                <i class="fa fa-trash"></i> Delete
                              </a>
                            <?php else: ?>
                              <span class="text-muted">Active Session</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                    <?php
                      }
                    }
                    ?>
                  </tbody>
                </table>
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
</body>

</html>

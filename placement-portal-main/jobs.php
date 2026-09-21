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
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">

    <?php
    include 'uploads/jobs_header.php';
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="margin-left: 0px;">

      <section class="content-header">
        <div class="container">
          <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10 latest-job margin-top-50 margin-bottom-20 large">
              <style>
                .large {

                  margin: auto;
                  margin-bottom: 20px;
                }
              </style>
              <h2 class="text-center">Active Drives</h2>
              <div class="input-group input-group-lg">
                <input type="text" id="searchBar" class="form-control" placeholder="Search job">
                <span class="input-group-btn">
                  <button id="searchBtn" type="button" class="btn btn-info btn-flat">Go!</button>
                </span>
              </div>
            </div>
            <div class="col-md-1"></div>
          </div>
        </div>
      </section>

      <section id="candidates" class="content-header">
        <div class="container">
          <div class="row">
            <div class="col-md-1">
            </div>

            <div class="col-md-10">

              <?php

              $limit = 4;

              $sql = "SELECT COUNT(id_jobpost) AS id FROM job_post";
              $result = $conn->query($sql);
              if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $total_records = $row['id'];
                $total_pages = ceil($total_records / $limit);
              } else {
                $total_pages = 1;
              }

              ?>


              <div id="target-content">

              </div>
              <div class="text-center">
                <ul class="pagination text-center" id="pagination"></ul>
              </div>



            </div>
            <div class="col-md-1">
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
    <div class="control-sidebar-bg"></div>

  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <!-- AdminLTE App -->
  <script src="js/adminlte.min.js"></script>
  <script src="js/jquery.twbsPagination.min.js"></script>

  <script>
    function Pagination() {
      $("#pagination").twbsPagination({
        totalPages: <?php echo $total_pages; ?>,
        visible: 5,
        onPageClick: function(e, page) {
          e.preventDefault();
          $("#target-content").html("loading....");
          $("#target-content").load("jobpagination.php?page=" + page);
        }
      });
    }
  </script>

  <script>
    $(function() {
      Pagination();
    });
  </script>

  <script>
    $("#searchBtn").on("click", function(e) {
      e.preventDefault();
      var searchResult = $("#searchBar").val();
      var filter = "searchBar";
      if (searchResult != "") {
        $("#pagination").twbsPagination('destroy');
        Search(searchResult, filter);
      } else {
        $("#pagination").twbsPagination('destroy');
        Pagination();
      }
    });
  </script>

  <script>
    $(".experienceSearch").on("click", function(e) {
      e.preventDefault();
      var searchResult = $(this).data("target");
      var filter = "experience";
      if (searchResult != "") {
        $("#pagination").twbsPagination('destroy');
        Search(searchResult, filter);
      } else {
        $("#pagination").twbsPagination('destroy');
        Pagination();
      }
    });
  </script>

  <script>
    $(".citySearch").on("click", function(e) {
      e.preventDefault();
      var searchResult = $(this).data("target");
      var filter = "city";
      if (searchResult != "") {
        $("#pagination").twbsPagination('destroy');
        Search(searchResult, filter);
      } else {
        $("#pagination").twbsPagination('destroy');
        Pagination();
      }
    });
  </script>

  <script>
    function Search(val, filter) {
      $("#pagination").twbsPagination({
        totalPages: <?php echo $total_pages; ?>,
        visible: 5,
        onPageClick: function(e, page) {
          e.preventDefault();
          val = encodeURIComponent(val);
          $("#target-content").html("loading....");
          $("#target-content").load("search.php?page=" + page + "&search=" + val + "&filter=" + filter);
        }
      });
    }
  </script>


</body>

</html>
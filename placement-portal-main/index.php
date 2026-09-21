<?php

session_start();

require_once("db.php");
?>
<!DOCTYPE html>
<html lang="en">
<title>Home</title>

<head>
    <?php

    include 'php/head.php'

    ?>


</head>

<body>

    <?php

    include 'php/header.php'

    ?>

    <section id="hero-animated" class="hero-animated d-flex align-items-center">
        <!-- Animated background blob 3 -->
        <div class="hero-blob3"></div>

        <div class="container position-relative" style="z-index:2;">
            <div class="row align-items-center g-5">
                <!-- Left text column -->
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="800">
                    <div class="hero-badge">
                        <i class="bi bi-mortarboard-fill"></i> PLACEMENT PORTAL 2026
                    </div>
                    <h2>Welcome to <span>Christ Career Connect</span></h2>
                    <p>Your complete placement journey — from resume building to offer letters, all in one powerful platform.</p>
                    <div class="d-flex gap-3 flex-wrap" style="margin-top:36px;">
                        <a href="login.php" class="btn-get-started">Get Started &nbsp;<i class="bi bi-arrow-right"></i></a>
                        <a href="register-candidates.php" class="hero-btn-secondary">
                            <i class="bi bi-person-plus"></i> Register Free
                        </a>
                    </div>
                    <!-- Mini trust stat strip -->
                    <?php
                    $rJobs     = $conn->query("SELECT COUNT(*) as c FROM job_post");     $rJobsR     = $rJobs->fetch_assoc();
                    $rComp     = $conn->query("SELECT COUNT(*) as c FROM company WHERE active='1'"); $rCompR = $rComp->fetch_assoc();
                    $rStudents = $conn->query("SELECT COUNT(*) as c FROM users WHERE active='1'");   $rStudentsR = $rStudents->fetch_assoc();
                    ?>
                    <div class="hero-stat-strip">
                        <div class="hero-stat-item">
                            <div class="num" style="color:var(--accent);"><?php echo $rJobsR['c']; ?>+</div>
                            <div class="lbl">Drives</div>
                        </div>
                        <div class="hero-stat-item">
                            <div class="num" style="color:var(--accent-violet);"><?php echo $rCompR['c']; ?>+</div>
                            <div class="lbl">Companies</div>
                        </div>
                        <div class="hero-stat-item">
                            <div class="num" style="color:var(--accent-emerald);"><?php echo $rStudentsR['c']; ?>+</div>
                            <div class="lbl">Students</div>
                        </div>
                    </div>
                </div>
                <!-- Right image column -->
                <div class="col-lg-6 text-center" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="100">
                    <img src="assets/img/hero-carousel/hero-carousel-3.svg" class="img-fluid animated" style="max-width:86%;">
                </div>
            </div>
        </div>
    </section>

    <main id="main">

        </div>

        </div>
        </section>
        <section id="clients" class="clients">
            <div class="container" data-aos="zoom-out">

                <div class="clients-slider swiper">
                    <div class="swiper-wrapper align-items-center">
                        <div class="swiper-slide"><img src="assets/img/clients/client-1.svg" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-2.png" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-3.png" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-4.png" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-5.png" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-6.png" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-7.png" class="img-fluid" alt=""></div>
                        <div class="swiper-slide"><img src="assets/img/clients/client-8.png" class="img-fluid" alt=""></div>
                    </div>
                </div>

            </div>
        </section>
        <section id="objectives" class="features" name="objectives">
            <div class="container" data-aos="fade-up">



                <div class="tab-content">

                    <div class="tab-pane active show" id="tab-1">
                        <div class="row gy-4">
                            <div class="col-lg-8 order-2 order-lg-1" data-aos="fade-up" data-aos-delay="100">
                                <h3>Objectives</h3>
                                <p class="fst-itali">
                                    Our Placement Portal caters to several key objectives:
                                </p>
                                <ul>
                                    <li><i class="bi bi-check-circle-fill"></i> Developing the students to meet the Industries recruitment process.
                                    </li>
                                    <li><i class="bi bi-check-circle-fill"></i> To motivate students to develop Technical knowledge and soft skills in
                                        terms of career planning, goal setting.
                                    </li>
                                    <li><i class="bi bi-check-circle-fill"></i> To produce world-class professionals who have excellent analytical skills,
                                        communication skills, team building spirit and ability to work in cross cultural
                                        environment.</li>

                                </ul>
                            </div>
                            <div class="col-lg-4 order-1 order-lg-2 text-center" data-aos="fade-up" data-aos-delay="200">
                                <img src="assets/img/features-1.svg" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                    
                    <section id="statistics" class="content-header">
                        <div class="container">
                            <div class="row mb-4">
                                <div class="col-md-12 text-center">
                                    <h1>Our Statistics</h1>
                                    <p style="color:#64748b;font-size:16px;margin-top:8px;">Real-time numbers from the platform</p>
                                </div>
                            </div>
                            <div class="row g-4">
                                <!-- Card 1 -->
                                <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="0">
                                    <div class="small-box bg-aqua">
                                        <div class="inner">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as c FROM job_post");
                                            $row = $result->fetch_assoc();
                                            ?>
                                            <h3><?php echo $row['c']; ?></h3>
                                            <p>Total Drives</p>
                                        </div>
                                        <div class="icon"><i class="bi bi-briefcase-fill"></i></div>
                                    </div>
                                </div>
                                <!-- Card 2 -->
                                <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                                    <div class="small-box bg-green">
                                        <div class="inner">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as c FROM company WHERE active='1'");
                                            $row = $result->fetch_assoc();
                                            ?>
                                            <h3><?php echo $row['c']; ?></h3>
                                            <p>Companies</p>
                                        </div>
                                        <div class="icon"><i class="bi bi-building-fill"></i></div>
                                    </div>
                                </div>
                                <!-- Card 3 -->
                                <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                                    <div class="small-box bg-yellow">
                                        <div class="inner">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as c FROM users WHERE resume!='' ");
                                            $row = $result->fetch_assoc();
                                            ?>
                                            <h3><?php echo $row['c']; ?></h3>
                                            <p>Resumes</p>
                                        </div>
                                        <div class="icon"><i class="bi bi-file-earmark-person-fill"></i></div>
                                    </div>
                                </div>
                                <!-- Card 4 -->
                                <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                                    <div class="small-box bg-red">
                                        <div class="inner">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as c FROM users WHERE active='1'");
                                            $row = $result->fetch_assoc();
                                            ?>
                                            <h3><?php echo $row['c']; ?></h3>
                                            <p>Active Students</p>
                                        </div>
                                        <div class="icon"><i class="bi bi-people-fill"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


    </main>
    <?php

    include 'php/footer.php';
    ?>



</body>

</html>
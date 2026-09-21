<!-- Main header -->
<header class="header">
    <nav class="navbar">
        <a href="../index.php" class="nav-logo">🎓 Christ Career Connect</a>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link"><i class="fa fa-th-large"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="ai-assistant.php" class="nav-link" style="color:#a5b4fc;"><i class="fa fa-comments"></i> AI Assistant</a>
            </li>
            <li class="nav-item">
                <a href="ai-mock-interview.php" class="nav-link" style="color:#c4b5fd;"><i class="fa fa-microphone"></i> Mock Interview</a>
            </li>
            <li class="nav-item">
                <a href="skill-gap-analysis.php" class="nav-link"><i class="fa fa-bullseye"></i> Skill Gap</a>
            </li>
            <li class="nav-item">
                <a href="ai-job-recommendations.php" class="nav-link"><i class="fa fa-briefcase"></i> Drive Match</a>
            </li>
            <li class="nav-item">
                <a href="resume-review.php" class="nav-link"><i class="fa fa-file-text-o"></i> ATS Review</a>
            </li>
            <li class="nav-item">
                <a href="ai-resume-builder.php" class="nav-link" style="color:#6ee7b7;"><i class="fa fa-pencil-square-o"></i> AI Resume</a>
            </li>
            <li class="nav-item">
                <a href="notice.php" class="nav-link"><i class="fa fa-bell-o"></i> Notices</a>
            </li>
            <li class="nav-item">
                <a href="../jobs.php" class="nav-link"><i class="fa fa-building-o"></i> Active Drives</a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link" style="color:#fca5a5;"><i class="fa fa-sign-out"></i> Logout</a>
            </li>
        </ul>
        <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </nav>

    <!-- Floating AI Chatbot Widget -->
    <?php include __DIR__ . '/ai-chatbot-widget.php'; ?>
</header>

<!-- Premium fonts + dashboard CSS -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/dashboard-premium.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script>
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".nav-menu");
    if (hamburger && navMenu) {
        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
        });
    }
</script>

<script src="../js/sweetalert.js"></script>
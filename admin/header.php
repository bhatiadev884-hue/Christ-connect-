<!-- Admin Main Header -->
<header class="header">
    <nav class="navbar">
        <a href="../index.php" class="nav-logo">🛡️ Admin Panel</a>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link"><i class="fa fa-tachometer"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="active-jobs.php" class="nav-link"><i class="fa fa-briefcase"></i> Active Drives</a>
            </li>
            <li class="nav-item">
                <a href="applications.php" class="nav-link"><i class="fa fa-users"></i> Students</a>
            </li>
            <li class="nav-item">
                <a href="companies.php" class="nav-link"><i class="fa fa-building"></i> Placement Cell</a>
            </li>
            <li class="nav-item">
                <a href="manage-admins.php" class="nav-link"><i class="fa fa-user-secret"></i> Admins</a>
            </li>
            <li class="nav-item">
                <a href="create-tester-account.php" class="nav-link"><i class="fa fa-flask"></i> Tester ID</a>
            </li>
            <li class="nav-item">
                <a href="ai-settings.php" class="nav-link" style="color:#c4b5fd;font-weight:700;"><i class="fa fa-key"></i> Gemini AI</a>
            </li>
            <li class="nav-item">
                <a href="postnotice.php" class="nav-link"><i class="fa fa-bullhorn"></i> Post Notice</a>
            </li>
            <li class="nav-item">
                <a href="database.php" class="nav-link"><i class="fa fa-database"></i> Database</a>
            </li>
            <li class="nav-item">
                <a href="placed.php" class="nav-link"><i class="fa fa-trophy"></i> Placed</a>
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
<header id="header" class="header fixed-top" data-scrollto-offset="0">
    <div class="container-fluid d-flex align-items-center justify-content-between px-3 px-lg-4">

        <!-- ── Brand ── -->
        <a href="index.php" class="logo d-flex align-items-center gap-2 text-decoration-none me-auto me-lg-0">
            <img src="img/christ1.png" alt="CCC Logo" style="height:46px;width:auto;">
            <span style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:var(--text-primary,#0f172a);letter-spacing:-.02em;line-height:1;">
                Christ <span style="background:linear-gradient(135deg,#4f46e5,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Career</span><br>
                <span style="font-size:11px;font-weight:600;letter-spacing:.05em;color:var(--text-muted,#94a3b8);-webkit-text-fill-color:var(--text-muted,#94a3b8);">CONNECT</span>
            </span>
        </a>

        <!-- ── Nav ── -->
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto" href="index.php">Home</a></li>
                <li class="dropdown">
                    <a href="login.php"><span>Login</span> <i class="bi bi-chevron-down dropdown-indicator"></i></a>
                    <ul>
                        <li><a href="admin/index.php"><i class="bi bi-shield-lock me-2"></i>Admin Login</a></li>
                        <li><a href="login-candidates.php"><i class="bi bi-person me-2"></i>Student Login</a></li>
                        <li><a href="login-company.php"><i class="bi bi-building me-2"></i>Placement Cell</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#"><span>Register</span> <i class="bi bi-chevron-down dropdown-indicator"></i></a>
                    <ul>
                        <li><a href="register-candidates.php"><i class="bi bi-person-plus me-2"></i>Students</a></li>
                        <li><a href="register-company.php"><i class="bi bi-building-add me-2"></i>Placement Cell</a></li>
                    </ul>
                </li>
                <li><a class="nav-link scrollto" href="contact.php">Contact</a></li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle d-none"></i>
        </nav>

        <!-- ── Right side: Toggle + CTA ── -->
        <div class="d-flex align-items-center gap-3 ms-3">
            <!-- Dark / Light toggle -->
            <button id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle theme">
                <span class="toggle-icon icon-sun">☀️</span>
                <span class="toggle-icon icon-moon">🌙</span>
            </button>
            <a class="btn-getstarted d-none d-lg-inline-flex align-items-center gap-2" href="faq.php">
                <i class="bi bi-question-circle"></i> FAQ
            </a>
        </div>

    </div>
</header>
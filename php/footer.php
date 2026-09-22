<footer id="footer" class="footer">



    <div class="footer-legal text-center">
        <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

            <div class="d-flex flex-column align-items-center align-items-lg-start">
                <div class="copyright">
                    &copy; Copyright <strong><span>Dev Bhatia</span></strong>. All Rights Reserved
                </div>
                <div class="credits">

                    <a href="https://ncr.christuniversity.in/">Christ Career Connect (CCC)</a>
                </div>
            </div>

            <div class="social-links order-first order-lg-last mb-3 mb-lg-0">

                <a href="https://youtube.com/@christuniversitydelhincr?si=40VBY3sZVUOJFYET" class="youtube"><i class="bi bi-youtube"></i></a>
                <a href="https://www.facebook.com/ncr.christuniversity/" class="facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/christ_university_ncr?igsh=MTJnZWZvbjJhenE1ZQ==" class="instagram"><i class="bi bi-instagram"></i></a>
                <!--
                <a href="#" class="google-plus"><i class="bi bi-skype"></i></a>-->
                
            </div>

        </div>
    </div>

</footer>

<a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="assets/js/main.js"></script>

<!-- ── Premium Dark/Light Mode Script ── -->
<script>
(function() {
    // Apply saved theme immediately (before paint)
    const saved = localStorage.getItem('ccc-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;

        // Sync toggle state
        function syncTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('ccc-theme', theme);
        }

        btn.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme');
            syncTheme(current === 'dark' ? 'light' : 'dark');
        });

        // Keyboard accessibility
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                btn.click();
            }
        });
    });
})();
</script>
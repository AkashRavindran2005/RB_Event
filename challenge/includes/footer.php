<footer class="mt-auto py-5" style="border-top: 1px solid #333; background-color: #000;">
    <div class="container-custom">
        <div class="row gy-4">
            <div class="col-md-5">
                <h5 class="text-white mb-3" style="font-weight: 600;">CyberTech Pro</h5>
                <p class="text-secondary small mb-4" style="max-width: 300px;">
                    Securing the digital frontier with advanced offensive and defensive capabilities. Trusted by
                    industry leaders worldwide.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-secondary hover-white"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-secondary hover-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    <a href="#" class="text-secondary hover-white"><i class="fab fa-github fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <h6 class="text-white mb-3 text-uppercase small" style="letter-spacing: 1px;">Company</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="about.php" class="text-secondary text-decoration-none hover-white">About</a></li>
                    <li><a href="careers.php" class="text-secondary text-decoration-none hover-white">Careers</a></li>
                    <li><a href="contact.php" class="text-secondary text-decoration-none hover-white">Contact</a></li>
                </ul>
            </div>

            <div class="col-md-3 col-6">
                <h6 class="text-white mb-3 text-uppercase small" style="letter-spacing: 1px;">Legal</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="privacy.php" class="text-secondary text-decoration-none hover-white">Privacy Policy</a>
                    </li>
                    <li><a href="terms.php" class="text-secondary text-decoration-none hover-white">Terms of Service</a>
                    </li>
                    <li><a href="api/scoreboard.php" target="_blank" class="text-accent text-decoration-none">Scoreboard
                            API</a></li>
                </ul>
            </div>
        </div>

        <div class="border-top border-secondary mt-5 pt-4 d-flex justify-content-between flex-wrap gap-3">
            <small class="text-secondary">&copy; <?php echo date('Y'); ?> CyberTech Solutions Inc.</small>
            <small class="text-secondary">
                <a href="login_legacy.php" class="text-secondary text-decoration-none opacity-50"
                    style="font-size: 10px;">Legacy Portal</a>
                &nbsp;|&nbsp;
                Designed for Red Teaming.
            </small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const elements = document.querySelectorAll('.bento-card, .display-text');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        elements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        });
    });
</script>
<style>
    .hover-white:hover {
        color: #fff !important;
    }
</style>
</body>

</html>
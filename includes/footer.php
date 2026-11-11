<?php
/**
 * Footer Include
 * Suprova's Gallery
 */
?>
</main><!-- /mainContent -->

<footer class="footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">

                <!-- Brand Column -->
                <div class="footer-col footer-brand">
                    <div class="footer-logo">
                        <span class="logo-icon">✦</span>
                        <span class="logo-text">Suprova's<br><em>Gallery</em></span>
                    </div>
                    <p class="footer-tagline">Where fashion meets elegance. Discover premium women's wear crafted with love and artistry.</p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" class="social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/shop.php">Shop All</a></li>
                        <li><a href="<?= SITE_URL ?>/shop.php?filter=new_arrival">New Arrivals</a></li>
                        <li><a href="<?= SITE_URL ?>/shop.php?filter=sale">Sale</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Customer Care -->
                <div class="footer-col">
                    <h4 class="footer-heading">Customer Care</h4>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/orders.php">Track Order</a></li>
                        <li><a href="<?= SITE_URL ?>/profile.php">My Account</a></li>
                        <li><a href="<?= SITE_URL ?>/wishlist.php">Wishlist</a></li>
                        <li><a href="#">Return Policy</a></li>
                        <li><a href="#">Size Guide</a></li>
                    </ul>
                </div>

                <!-- Contact & Newsletter -->
                <div class="footer-col footer-newsletter-col">
                    <h4 class="footer-heading">Stay in Touch</h4>
                    <div class="footer-contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</p>
                        <p><i class="fas fa-phone"></i> +880 1700-000000</p>
                        <p><i class="fas fa-envelope"></i> hello@suprovasgallery.com</p>
                    </div>
                    <form class="footer-newsletter" id="newsletterForm">
                        <p>Subscribe for exclusive deals:</p>
                        <div class="newsletter-input-group">
                            <input type="email" name="email" placeholder="Your email address" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Suprova's Gallery. All rights reserved. Made with <i class="fas fa-heart" style="color:#FF7EB6"></i> in Bangladesh.</p>
            <div class="footer-payments">
                <span>We accept:</span>
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fas fa-mobile-alt"></i>
                <span>bKash</span>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Scripts -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/cart.js"></script>
<?php if (isset($extraJs)) echo $extraJs; ?>

<script>
// Newsletter form
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[name="email"]').value;
    fetch('<?= SITE_URL ?>/api/newsletter.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.success ? 'success' : 'error', d.message);
        if (d.success) this.reset();
    });
});
</script>
</body>
</html>

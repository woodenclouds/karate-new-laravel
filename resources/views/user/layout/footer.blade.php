<!-- Footer integrated within contact section -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Brand -->
            <div class="footer-brand">
                <div class="footer-logo">MENTORS SPORTS KARATE - DO</div>
                <p class="footer-tagline">
                    Promoting discipline, focus, and self-confidence through structured school-based karate and martial
                    arts training across India.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h4>Quick Links</h4>
                <div class="footer-links">
                    <a href="{{ route('user.index') }}">Home</a>
                    <a href="{{ route('user.about') }}">About Us</a>
                    <a href="{{ route('user.events') }}">Events</a>
                    <a href="{{ route('user.contact') }}">Contact</a>
                </div>
            </div>

            <!-- Support -->
            <div class="footer-section">
                <h4>Support</h4>
                <div class="footer-links">
                    <a href="{{ route('user.privacy_policy') }}">Privacy Policy</a>
                    <a href="{{ route('user.terms-and-conditions') }}">Terms of Service</a>
                    <a href="{{ route('user.refund-policy') }}">Refund Policy</a>
                    <a href="{{ route('user.contact') }}#FAQ">FAQs</a>
                </div>
            </div>

            <!-- Contact Us -->
            <div class="footer-section">
                <h4>Contact Us</h4>
                <div class="footer-links">
                    <a href="{{ route('user.contact') }}">Send a Message</a>
                    <a href="#">Phone: +91 97782 09009</a>
                    <a href="#">Email: mentorssportskaratedo@gmail.com</a>
                    <a href="#">Location: 2nd Floor,ASR plaza Priyadarshini Road Palakkad Kerala India -678001</a>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Mentors Karate Do. All rights reserved.</p>
            <div class="footer-social">
                <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</footer>
<!--============ whatsapp button ================-->
<a href="https://wa.me/919778209009" target="_blank" id="whatsappButton" class="whatsapp-button">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" />
</a>


<script src="{{ asset('assets/user/js/contact1.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const mobileToggle = document.getElementById("menuToggle");
        const sidebarMenu = document.getElementById("sidebarMenu");
        const overlay = document.getElementById("overlay");
        const body = document.body;
        const navLinks = document.querySelectorAll(".sidebar-menu .nav-link");

        // Toggle sidebar
        mobileToggle.addEventListener("click", () => {
            mobileToggle.classList.toggle("active");
            sidebarMenu.classList.toggle("active");
            overlay.classList.toggle("active");
            body.classList.toggle("menu-open");
        });

        // Close on link click
        navLinks.forEach((link) => {
            link.addEventListener("click", () => {
                mobileToggle.classList.remove("active");
                sidebarMenu.classList.remove("active");
                overlay.classList.remove("active");
                body.classList.remove("menu-open");
            });
        });

        // Close on overlay click
        overlay.addEventListener("click", () => {
            mobileToggle.classList.remove("active");
            sidebarMenu.classList.remove("active");
            overlay.classList.remove("active");
            body.classList.remove("menu-open");
        });
    });
</script>

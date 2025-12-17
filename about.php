<?php
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5" style="background: linear-gradient(135deg, #fff5f0, #fff);">
    <div class="container">
        <div class="section-title">
            <h2><i class="fas fa-info-circle"></i> About StreetGo</h2>
            <div class="underline"></div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <!-- CHANGED: Linked to local street food image -->
                <img src="assets/img/street.jpg" alt="Street Food" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h2>Our Story</h2>
                    <p>StreetGo was born from a simple idea: to bring the vibrant, authentic taste of Filipino street food to everyone's doorstep. Founded in 2025, we started as a small team of food enthusiasts who grew up enjoying the bustling street food scene in the Philippines.</p>
                    <p>We partner with the best local street food vendors, ensuring that every item we deliver carries the same authentic taste and quality you'd find on the streets of Mati City, , and beyond.</p>
                    <p>Our mission is to preserve and promote Filipino street food culture while making it accessible to everyone, whether you're craving a quick snack or planning a party with friends.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="section-title">
            <h2>Our Values</h2>
            <div class="underline"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5>Authenticity</h5>
                    <p>We preserve the original recipes and cooking methods that make Filipino street food special</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5>Quality</h5>
                    <p>Fresh ingredients, hygienic preparation, and careful packaging for every order</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5>Community</h5>
                    <p>Supporting local vendors and bringing people together through food</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background: linear-gradient(135deg, #fff5f0, #fff);">
    <div class="container">
        <div class="section-title">
            <h2>Meet Our Team</h2>
            <div class="underline"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 col-lg-3">
                <div class="team-card">
                    <!-- CHANGED: Linked to Charmie's photo -->
                    <img src="assets/img/charmie.jpg" alt="">
                    <h5>Charmie M. Masugod</h5>
                    <p>Founder & CEO</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="team-card">
                    <!-- CHANGED: Linked to Pereze's photo -->
                    <img src="assets/img/perez.jpg" alt="">
                    <h5>Lunc jarry Perez</h5>
                    <p>Operations Manager</p>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="team-card">
                    <!-- CHANGED: Linked to Crisa's photo -->
                    <img src="assets/img/crisa.jpg" alt="">
                    <h5>Crisa Jane Beros</h5>
                    <p>Head of Delivery</p>
                </div>
            </div>
        </div> <!-- End Row -->
    </div> <!-- End Container -->
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <h2 class="display-4 fw-bold" style="color: var(--primary-color);">500+</h2>
                <p class="text-muted">Happy Customers</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-4 fw-bold" style="color: var(--primary-color);">12+</h2>
                <p class="text-muted">Menu Items</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-4 fw-bold" style="color: var(--primary-color);">10+</h2>
                <p class="text-muted">Delivery Areas</p>
            </div>
            <div class="col-6 col-md-3">
                <h2 class="display-4 fw-bold" style="color: var(--primary-color);">1000+</h2>
                <p class="text-muted">Orders Delivered</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Join the StreetGo Family</h2>
        <p>Be part of our mission to bring Filipino street food to every Filipino home</p>
        <a href="/register.php" class="btn btn-light btn-lg me-3">
            <i class="fas fa-user-plus"></i> Sign Up Now
        </a>
        <a href="/menu.php" class="btn btn-outline-light btn-lg">
            <i class="fas fa-utensils"></i> Order Now
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query("SELECT * FROM delivery_areas ORDER BY shipping_fee");
$deliveryAreas = $stmt->fetchAll();
?>

<section class="py-5" style="background: linear-gradient(135deg, #fff5f0, #fff);">
    <div class="container">
        <div class="section-title">
            <h2><i class="fas fa-truck"></i> Our Services</h2>
            <div class="underline"></div>
            <p>Fast, reliable, and affordable Filipino street food delivery</p>
        </div>
    </div>
</section>

<section class="services-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h5>Express Delivery</h5>
                    <p>Our dedicated riders ensure your street food arrives hot and fresh within 30-60 minutes depending on your location.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h5>Easy Ordering</h5>
                    <p>Simple online ordering through our website. Browse menu, add to cart, and checkout in minutes.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h5>Cash on Delivery</h5>
                    <p>Pay when your order arrives. No need for online payments or credit cards.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>Bulk Orders</h5>
                    <p>Planning a party or event? We offer special rates for large orders. Contact us for custom packages.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Extended Hours</h5>
                    <p>Open from 10AM to 10PM on weekdays and 8AM to 11PM on weekends to satisfy your cravings.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>Customer Support</h5>
                    <p>Our friendly support team is ready to assist you with any questions or concerns.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background: linear-gradient(135deg, #fff5f0, #fff);">
    <div class="container">
        <div class="section-title">
            <h2>Delivery Areas & Fees</h2>
            <div class="underline"></div>
            <p>We currently deliver to the following areas in Metro Manila</p>
        </div>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php foreach ($deliveryAreas as $area): ?>
                <div class="delivery-area-card">
                    <div class="area-name">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <?php echo htmlspecialchars($area['area_name']); ?>
                    </div>
                    <div class="area-info">
                        <div class="fee">₱<?php echo number_format($area['shipping_fee'], 2); ?></div>
                        <div class="time"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($area['estimated_time']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="section-title">
            <h2>How It Works</h2>
            <div class="underline"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="display-6">1</span>
                </div>
                <h5>Browse Menu</h5>
                <p class="text-muted">Explore our delicious selection of Filipino street food</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="display-6">2</span>
                </div>
                <h5>Add to Cart</h5>
                <p class="text-muted">Select your favorite items and add them to your cart</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="display-6">3</span>
                </div>
                <h5>Checkout</h5>
                <p class="text-muted">Enter delivery details and confirm your order</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="display-6">4</span>
                </div>
                <h5>Enjoy!</h5>
                <p class="text-muted">Receive your order and enjoy authentic street food</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Order?</h2>
        <p>Experience the best Filipino street food delivered to your doorstep</p>
        <a href="/menu.php" class="btn btn-light btn-lg">
            <i class="fas fa-utensils"></i> Order Now
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

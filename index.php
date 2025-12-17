<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Fetch random featured items
$stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = TRUE ORDER BY RAND() LIMIT 6");
$featuredItems = $stmt->fetchAll();

$categories = [
    ['icon' => 'fa-fire', 'name' => 'Grilled', 'desc' => 'BBQ favorites'],
    ['icon' => 'fa-oil-can', 'name' => 'Fried', 'desc' => 'Crispy treats'],
    ['icon' => 'fa-ice-cream', 'name' => 'Sweets', 'desc' => 'Sweet delights'],
    ['icon' => 'fa-star', 'name' => 'Exotic', 'desc' => 'Unique tastes']
];
?>

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1>Authentic Filipino<br>Street Food</h1>
                <p>Experience the vibrant flavors of the Philippines delivered straight to your doorstep. From Isaw to Taho, we bring the streets to you!</p>
                <a href="menu.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-utensils"></i> View Menu
                </a>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus"></i> Sign Up Now
                </a>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center">
                <!-- CHANGED: Replaced http link with local jpg file -->
                <img src="assets/img/food.jpg" alt="Street Food" class="img-fluid rounded-4 shadow-lg" style="max-width: 80%; transform: rotate(5deg);">
            </div>
        </div>
    </div>
</section>

<section class="categories-section">
    <div class="container">
        <div class="section-title">
            <h2>Browse Categories</h2>
            <div class="underline"></div>
            <p>Explore our delicious selection of Filipino street food</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-3">
                <a href="menu.php?category=<?php echo urlencode($cat['name']); ?>" class="text-decoration-none">
                    <div class="category-card">
                        <i class="fas <?php echo $cat['icon']; ?>"></i>
                        <h5><?php echo $cat['name']; ?></h5>
                        <p><?php echo $cat['desc']; ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="featured-section">
    <div class="container">
        <div class="section-title">
            <h2>Featured Street Foods</h2>
            <div class="underline"></div>
            <p>Try our most popular and authentic Filipino street food items</p>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredItems as $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="food-card">
                    <div class="card-img-wrapper">
                        <!-- Note: Ensure your database 'image_url' fields also point to local files (e.g., assets/images/isaw.jpg) -->
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <span class="category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price">₱<?php echo number_format($item['price'], 2); ?></span>
                            <button onclick="addToCart(<?php echo $item['id']; ?>)" class="btn btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="menu.php" class="btn btn-primary btn-lg">
                <i class="fas fa-utensils"></i> View Full Menu
            </a>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose StreetGo?</h2>
            <div class="underline"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h5>Fast Delivery</h5>
                    <p>Get your favorite street food delivered hot and fresh within 30-60 minutes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h5>Authentic Taste</h5>
                    <p>We partner with the best street food vendors to bring you genuine Filipino flavors</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h5>Affordable Prices</h5>
                    <p>Enjoy street food prices with the convenience of home delivery</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Order?</h2>
        <p>Sign up now and get 10% off your first order!</p>
        <a href="register.php" class="btn btn-light btn-lg">
            <i class="fas fa-user-plus"></i> Create Account
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
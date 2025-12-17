<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

if ($category !== 'all' && !empty($category)) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE is_available = TRUE AND category = ? ORDER BY name");
    $stmt->execute([$category]);
} elseif (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE is_available = TRUE AND (name ILIKE ? OR description ILIKE ?) ORDER BY name");
    $searchTerm = '%' . $search . '%';
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = TRUE ORDER BY category, name");
}
$menuItems = $stmt->fetchAll();

$catStmt = $pdo->query("SELECT DISTINCT category FROM menu_items WHERE is_available = TRUE ORDER BY category");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<section class="py-5" style="background: linear-gradient(135deg, #fff5f0, #fff); min-height: 200px;">
    <div class="container">
        <div class="section-title mb-4">
            <h2><i class="fas fa-utensils"></i> Our Menu</h2>
            <div class="underline"></div>
            <p>Discover the best Filipino street food, freshly prepared and delivered to you</p>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="menu-filters">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <button class="filter-btn <?php echo $category === 'all' ? 'active' : ''; ?>" data-category="all" onclick="window.location.href='menu.php'">
                        <i class="fas fa-th"></i> All Items
                    </button>
                    <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn <?php echo $category === $cat ? 'active' : ''; ?>" data-category="<?php echo htmlspecialchars($cat); ?>" onclick="window.location.href='menu.php?category=<?php echo urlencode($cat); ?>'">
                        <?php echo htmlspecialchars($cat); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <form action="/menu.php" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search food..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (empty($menuItems)): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h4>No items found</h4>
            <p>Try a different search term or browse all categories</p>
            <a href="menu.php" class="btn btn-primary mt-3">View All Items</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($menuItems as $item): ?>
            <div class="col-md-6 col-lg-4 food-card-wrapper" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                <div class="food-card">
                    <div class="card-img-wrapper">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <span class="category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price">₱<?php echo number_format($item['price'], 2); ?></span>
                            <button onclick="addToCart(<?php echo $item['id']; ?>)" class="btn btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<button class="btn btn-add-cart" onclick="addToCart(<?php echo $item['id']; ?>)">
    <i class="fas fa-cart-plus"></i> Add to Cart
</button>


<?php require_once __DIR__ . '/includes/footer.php'; ?>

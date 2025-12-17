<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['id'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5">
    <div class="container text-center">
        <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
        </div>
        <h2 class="mb-3">Order Placed Successfully!</h2>
        <p class="text-muted mb-4">
            Thank you for ordering. Your order ID is <strong>#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></strong>.
            <br>We will process it shortly.
        </p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="orders.php" class="btn btn-primary px-4">
                <i class="fas fa-receipt me-2"></i> View My Orders
            </a>
            <a href="menu.php" class="btn btn-outline-secondary px-4">
                Continue Shopping
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
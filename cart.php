<?php
session_start();
require_once __DIR__ . '/config/database.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ==========================================
// 2. HANDLE AJAX REQUESTS (Update & Remove)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $data = ['status' => 'error', 'message' => 'Something went wrong'];

    try {
        if ($_POST['action'] === 'update_quantity') {
            $menu_item_id = (int)$_POST['menu_item_id'];
            $change = (int)$_POST['change'];
            
            // Get current quantity first
            $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND menu_item_id = ?");
            $stmt->execute([$user_id, $menu_item_id]);
            $current = $stmt->fetchColumn();

            if ($current) {
                $new_qty = $current + $change;
                if ($new_qty > 0) {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND menu_item_id = ?");
                    $stmt->execute([$new_qty, $user_id, $menu_item_id]);
                    $data = ['status' => 'success', 'message' => 'Quantity updated'];
                }
            }
        } 
        elseif ($_POST['action'] === 'remove_item') {
            $menu_item_id = (int)$_POST['menu_item_id'];
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND menu_item_id = ?");
            $stmt->execute([$user_id, $menu_item_id]);
            $data = ['status' => 'success', 'message' => 'Item removed'];
        }
    } catch (Exception $e) {
        $data['message'] = $e->getMessage();
    }

    echo json_encode($data);
    exit; // Stop executing the rest of the page for AJAX
}

// ==========================================
// 3. FETCH CART ITEMS FOR DISPLAY
// ==========================================
$stmt = $pdo->prepare("
    SELECT c.*, m.name, m.description, m.price, m.image_url, m.category 
    FROM cart c 
    JOIN menu_items m ON c.menu_item_id = m.id 
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Include Header (Kausa ra tawagon)
require_once __DIR__ . '/includes/header.php';
?>

<section class="cart-section py-5">
    <div class="container">
        <div class="section-title mb-4">
            <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
            <div class="underline"></div>
        </div>
        
        <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-shopping-cart fa-4x text-muted"></i>
            </div>
            <h4>Your cart is empty</h4>
            <p class="text-muted">Add some delicious Filipino street food to your cart</p>
            <a href="menu.php" class="btn btn-primary mt-3">
                <i class="fas fa-utensils"></i> Browse Menu
            </a>
        </div>
        <?php else: ?>
        
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($cartItems as $item): ?>
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Image -->
                            <div class="col-3 col-md-2">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <!-- Details -->
                            <div class="col-9 col-md-4">
                                <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['category']); ?></p>
                                <p class="text-primary fw-bold mb-0">₱<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <!-- Quantity -->
                            <div class="col-6 col-md-3 mt-3 mt-md-0">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" onclick="updateCartQuantity(<?php echo $item['menu_item_id']; ?>, -1)" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="form-control text-center bg-white"><?php echo $item['quantity']; ?></span>
                                    <button class="btn btn-outline-secondary" onclick="updateCartQuantity(<?php echo $item['menu_item_id']; ?>, 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Total & Remove -->
                            <div class="col-6 col-md-3 mt-3 mt-md-0 text-end">
                                <p class="fw-bold mb-1">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                <button onclick="removeFromCart(<?php echo $item['menu_item_id']; ?>)" class="btn btn-sm btn-link text-danger text-decoration-none p-0">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><i class="fas fa-receipt"></i> Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?php echo count($cartItems); ?> items)</span>
                            <span>₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping</span>
                            <span class="text-muted">Calculated at checkout</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold h5">Total</span>
                            <span class="fw-bold h5 text-primary">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary w-100 py-2 mb-2">
                            <i class="fas fa-credit-card me-2"></i> Proceed to Checkout
                        </a>
                        <a href="menu.php" class="btn btn-outline-secondary w-100 py-2">
                            Add More Items
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</section>

<!-- SCRIPTS PARA MOGANA ANG BUTTONS -->
<script>
    // Function para sa Plus/Minus
    function updateCartQuantity(itemId, change) {
        const formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('menu_item_id', itemId);
        formData.append('change', change);

        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload(); // I-refresh ang page para makita ang bag-ong total
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Function para sa Remove Item
    function removeFromCart(itemId) {
        Swal.fire({
            title: 'Remove item?',
            text: "Are you sure you want to remove this from your cart?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'remove_item');
                formData.append('menu_item_id', itemId);

                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
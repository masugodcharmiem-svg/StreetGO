<?php
session_start();
require_once __DIR__ . '/config/database.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. Kuhaa ang items sa cart
$stmt = $pdo->prepare("
    SELECT c.*, m.name, m.price, m.image_url 
    FROM cart c 
    JOIN menu_items m ON c.menu_item_id = m.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

// Kung walay sulod ang cart, ibalik sa cart page
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Compute Subtotal
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get Delivery Areas
$areaStmt = $pdo->query("SELECT * FROM delivery_areas ORDER BY area_name");
$deliveryAreas = $areaStmt->fetchAll();

// Get User Info
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$error = '';

// 3. Process Order kung ni-submit na
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = sanitize($_POST['delivery_address'] ?? '');
    $delivery_area_id = intval($_POST['delivery_area'] ?? 0);
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($delivery_address) || $delivery_area_id <= 0) {
        $error = 'Please provide delivery address and select delivery area.';
    } else {
        // Kuhaa ang shipping fee base sa gipili nga area
        $areaStmt = $pdo->prepare("SELECT shipping_fee FROM delivery_areas WHERE id = ?");
        $areaStmt->execute([$delivery_area_id]);
        $area = $areaStmt->fetch();
        
        if (!$area) {
            $error = 'Invalid delivery area selected.';
        } else {
            $shipping_fee = $area['shipping_fee'];
            $total = $subtotal + $shipping_fee;
            
            // Start Transaction (Para siguradong masulod tanan or wala)
            $pdo->beginTransaction();
            
            try {
                // Insert Order
                $orderStmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, shipping_fee, delivery_address, payment_method, notes, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $orderStmt->execute([$_SESSION['user_id'], $total, $shipping_fee, $delivery_address, $payment_method, $notes]);
                $orderId = $pdo->lastInsertId();
                
                // Insert Order Items
                foreach ($cartItems as $item) {
                    $itemStmt = $pdo->prepare("
                        INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $itemStmt->execute([$orderId, $item['menu_item_id'], $item['quantity'], $item['price']]);
                }
                
                // Clear Cart
                $clearStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $clearStmt->execute([$_SESSION['user_id']]);
                
                // Commit Transaction
                $pdo->commit();
                
                // Redirect to Success Page (Himo-i nig file unya)
                header('Location: order-success.php?id=' . $orderId);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Failed to place order: ' . $e->getMessage();
            }
        }
    }
}

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/includes/header.php';
?>

<section class="checkout-section py-5">
    <div class="container">
        <div class="section-title mb-4">
            <h2><i class="fas fa-credit-card"></i> Checkout</h2>
            <div class="underline"></div>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Action removed slash for XAMPP support -->
        <form method="POST" action="checkout.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="row">
                <!-- Left Side: Forms -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-4"><i class="fas fa-map-marker-alt text-primary"></i> Delivery Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control bg-light" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Delivery Area *</label>
                                <select name="delivery_area" id="delivery_area" class="form-select" required onchange="calculateShipping()">
                                    <option value="" selected disabled>-- Choose your location --</option>
                                    <?php foreach ($deliveryAreas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>" data-fee="<?php echo $area['shipping_fee']; ?>">
                                        <?php echo htmlspecialchars($area['area_name']); ?> - Fee: ₱<?php echo number_format($area['shipping_fee'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Specific Address *</label>
                                <textarea name="delivery_address" class="form-control" rows="3" required placeholder="House No., Street Name, Landmark..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Order Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Ex: Please put extra sauce, or call when near."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-4"><i class="fas fa-wallet text-primary"></i> Payment Method</h5>
                            
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" value="cod" id="cod" checked>
                                <label class="form-check-label w-100" for="cod">
                                    <strong><i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery (COD)</strong>
                                    <div class="text-muted small">Pay cash when the rider arrives.</div>
                                </label>
                            </div>
                            
                            <div class="form-check p-3 border rounded bg-light">
                                <input class="form-check-input" type="radio" name="payment_method" value="gcash" id="gcash" disabled>
                                <label class="form-check-label w-100" for="gcash">
                                    <strong><i class="fas fa-mobile-alt text-primary"></i> GCash (Coming Soon)</strong>
                                    <div class="text-muted small">Online payment is currently unavailable.</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side: Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-4"><i class="fas fa-receipt"></i> Order Summary</h4>
                            
                            <div class="order-items mb-3" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($cartItems as $item): ?>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span><?php echo htmlspecialchars($item['name']); ?> <span class="text-muted">x<?php echo $item['quantity']; ?></span></span>
                                    <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <!-- Store subtotal in a data attribute for JS -->
                                <span id="subtotal_display" data-amount="<?php echo $subtotal; ?>">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Shipping Fee</span>
                                <span id="shipping_fee_display">₱0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold h5">Total Amount</span>
                                <span class="fw-bold h5 text-primary" id="total_amount_display">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                                <i class="fas fa-check-circle me-2"></i> Place Order Now
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">
                                Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- JAVASCRIPT: MAO NI ANG MAG-UPDATE SA SHIPPING FEE -->
<script>
function calculateShipping() {
    // 1. Get Elements
    const select = document.getElementById('delivery_area');
    const shippingDisplay = document.getElementById('shipping_fee_display');
    const totalDisplay = document.getElementById('total_amount_display');
    const subtotalElement = document.getElementById('subtotal_display');

    // 2. Get Values
    // Kuhaa ang fee gikan sa 'data-fee' attribute sa napili nga option
    const selectedOption = select.options[select.selectedIndex];
    const shippingFee = parseFloat(selectedOption.getAttribute('data-fee')) || 0;
    
    // Kuhaa ang subtotal
    const subtotal = parseFloat(subtotalElement.getAttribute('data-amount'));

    // 3. Calculate Total
    const total = subtotal + shippingFee;

    // 4. Update Display (Format to 2 decimal places with Comma)
    shippingDisplay.innerText = '₱' + shippingFee.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    totalDisplay.innerText = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
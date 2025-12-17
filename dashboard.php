<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$ordersStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$ordersStmt->execute([$_SESSION['user_id']]);
$orders = $ordersStmt->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Invalid request.';
    } elseif (!empty($full_name)) {
        $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        if ($updateStmt->execute([$full_name, $phone, $address, $_SESSION['user_id']])) {
            $_SESSION['full_name'] = $full_name;
            $success = 'Profile updated successfully!';
            $user['full_name'] = $full_name;
            $user['phone'] = $phone;
            $user['address'] = $address;
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/includes/header.php';
?>

<section class="dashboard-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="bg-white rounded-4 p-4 shadow-sm text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                    </div>
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-muted mb-1">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="text-muted"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
                
                <div class="bg-white rounded-4 p-4 shadow-sm mt-4">
                    <h5 class="mb-3"><i class="fas fa-chart-bar text-primary"></i> Order Stats</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Orders</span>
                        <strong><?php echo count($orders); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending</span>
                        <strong><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Delivered</span>
                        <strong><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'delivered')); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#orders">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#profile">
                            <i class="fas fa-user"></i> Edit Profile
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="orders">
                        <?php if (empty($orders)): ?>
                        <div class="empty-state bg-white rounded-4 p-5">
                            <i class="fas fa-shopping-bag"></i>
                            <h4>No orders yet</h4>
                            <p>Start ordering delicious Filipino street food!</p>
                            <a href="/menu.php" class="btn btn-primary mt-3">
                                <i class="fas fa-utensils"></i> Browse Menu
                            </a>
                        </div>
                        <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <div class="bg-white rounded-4 p-4 shadow-sm mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h5>
                                    <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></small>
                                </div>
                                <span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-1"><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars(substr($order['delivery_address'], 0, 50)); ?>...</p>
                                    <p class="mb-0"><i class="fas fa-credit-card text-primary"></i> <?php echo strtoupper($order['payment_method']); ?></p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <h5 class="text-primary mb-0">₱<?php echo number_format($order['total_amount'], 2); ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tab-pane fade" id="profile">
                        <div class="bg-white rounded-4 p-4 shadow-sm">
                            <h5 class="mb-4"><i class="fas fa-user-edit text-primary"></i> Update Profile</h5>
                            
                            <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email (cannot be changed)</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Default Delivery Address</label>
                                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

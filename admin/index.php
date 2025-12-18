<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'delivered'")->fetchColumn();
$menuCount = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

$recentOrders = $pdo->query("
    SELECT o.*, u.username, u.full_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Admin Dashboard - StreetGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-top: 0;">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon orders"><i class="fas fa-shopping-bag"></i></div>
                    <div>
                        <h3><?php echo $orderCount; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon users"><i class="fas fa-users"></i></div>
                    <div>
                        <h3><?php echo $userCount; ?></h3>
                        <p>Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon revenue"><i class="fas fa-peso-sign"></i></div>
                    <div>
                        <h3>₱<?php echo number_format($revenue, 2); ?></h3>
                        <p>Revenue</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="icon items"><i class="fas fa-utensils"></i></div>
                    <div>
                        <h3><?php echo $menuCount; ?></h3>
                        <p>Menu Items</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="data-table">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="/admin/orders.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No orders yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

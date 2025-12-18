<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $posted_token = $_POST['csrf_token'] ?? '';
    
    if (validateCSRFToken($posted_token)) {
        $orderId = intval($_POST['order_id']);
        $status = sanitize($_POST['status']);
        $validStatuses = ['pending', 'processing', 'delivered', 'cancelled'];
        
        if (in_array($status, $validStatuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
        }
    }
}

$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

if (!empty($statusFilter)) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.full_name, u.email, u.phone
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.status = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$statusFilter]);
} else {
    $stmt = $pdo->query("
        SELECT o.*, u.username, u.full_name, u.email, u.phone
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC
    ");
}
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Manage Orders - StreetGo Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-top: 0;">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Orders</h2>
            <div>
                <a href="/admin/orders.php" class="btn btn-outline-secondary btn-sm <?php echo empty($statusFilter) ? 'active' : ''; ?>">All</a>
                <a href="/admin/orders.php?status=pending" class="btn btn-outline-warning btn-sm <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="/admin/orders.php?status=processing" class="btn btn-outline-info btn-sm <?php echo $statusFilter === 'processing' ? 'active' : ''; ?>">Processing</a>
                <a href="/admin/orders.php?status=delivered" class="btn btn-outline-success btn-sm <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
                <a href="/admin/orders.php?status=cancelled" class="btn btn-outline-danger btn-sm <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>
        </div>
        
        <div class="data-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Amount</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No orders found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td>
                            <small><?php echo htmlspecialchars($order['email']); ?></small><br>
                            <small><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></small>
                        </td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><small><?php echo htmlspecialchars(substr($order['delivery_address'], 0, 30)); ?>...</small></td>
                        <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
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

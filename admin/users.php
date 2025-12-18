<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$success = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $posted_token = $_POST['csrf_token'] ?? '';
    
    if (validateCSRFToken($posted_token)) {
        $userId = intval($_POST['user_id']);
        $newStatus = intval($_POST['new_status']);
        
        $stmt = $pdo->prepare("UPDATE users SET is_verified = ? WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$newStatus, $userId])) {
            $success = 'User status updated!';
        }
    }
}

$users = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
           (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id AND status = 'delivered') as total_spent
    FROM users u 
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Manage Users - StreetGo Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-top: 0;">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Users</h2>
            <span class="text-muted"><?php echo count($users); ?> total users</span>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="data-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                            <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td><span class="badge bg-primary"><?php echo $user['order_count']; ?></span></td>
                        <td>₱<?php echo number_format($user['total_spent'], 2); ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Customer</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="toggle_status" value="1">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $user['is_verified'] ? '0' : '1'; ?>">
                                <?php if ($user['is_verified']): ?>
                                <button type="submit" class="btn btn-sm btn-success" title="Click to deactivate">
                                    <i class="fas fa-check-circle"></i> Verified
                                </button>
                                <?php else: ?>
                                <button type="submit" class="btn btn-sm btn-warning" title="Click to verify">
                                    <i class="fas fa-clock"></i> Pending
                                </button>
                                <?php endif; ?>
                            </form>
                            <?php else: ?>
                            <span class="badge bg-success"><i class="fas fa-shield-alt"></i> Admin</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

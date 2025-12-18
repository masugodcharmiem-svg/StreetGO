<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$success = '';
$error = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($posted_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $area_name = sanitize($_POST['area_name']);
            $shipping_fee = floatval($_POST['shipping_fee']);
            $estimated_time = sanitize($_POST['estimated_time']);
            
            if (!empty($area_name) && $shipping_fee >= 0) {
                $stmt = $pdo->prepare("INSERT INTO delivery_areas (area_name, shipping_fee, estimated_time) VALUES (?, ?, ?)");
                if ($stmt->execute([$area_name, $shipping_fee, $estimated_time])) {
                    $success = 'Delivery area added successfully!';
                } else {
                    $error = 'Failed to add delivery area.';
                }
            }
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $area_name = sanitize($_POST['area_name']);
            $shipping_fee = floatval($_POST['shipping_fee']);
            $estimated_time = sanitize($_POST['estimated_time']);
            
            $stmt = $pdo->prepare("UPDATE delivery_areas SET area_name = ?, shipping_fee = ?, estimated_time = ? WHERE id = ?");
            if ($stmt->execute([$area_name, $shipping_fee, $estimated_time, $id])) {
                $success = 'Delivery area updated successfully!';
            } else {
                $error = 'Failed to update delivery area.';
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM delivery_areas WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = 'Delivery area deleted successfully!';
            } else {
                $error = 'Failed to delete delivery area.';
            }
        }
    }
}

$areas = $pdo->query("SELECT * FROM delivery_areas ORDER BY area_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Manage Delivery Areas - StreetGo Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-top: 0;">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Delivery Areas</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Area
            </button>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="data-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Area Name</th>
                        <th>Shipping Fee</th>
                        <th>Estimated Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($areas as $area): ?>
                    <tr>
                        <td>
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <strong><?php echo htmlspecialchars($area['area_name']); ?></strong>
                        </td>
                        <td>₱<?php echo number_format($area['shipping_fee'], 2); ?></td>
                        <td><i class="fas fa-clock text-muted me-1"></i> <?php echo htmlspecialchars($area['estimated_time']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editArea(<?php echo htmlspecialchars(json_encode($area)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this area?')">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $area['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Delivery Area</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Area Name</label>
                            <input type="text" name="area_name" class="form-control" placeholder="e.g., Quezon City" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Fee (₱)</label>
                            <input type="number" name="shipping_fee" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimated Delivery Time</label>
                            <input type="text" name="estimated_time" class="form-control" placeholder="e.g., 30-45 mins">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Area</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Delivery Area</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Area Name</label>
                            <input type="text" name="area_name" id="edit_area_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Fee (₱)</label>
                            <input type="number" name="shipping_fee" id="edit_shipping_fee" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimated Delivery Time</label>
                            <input type="text" name="estimated_time" id="edit_estimated_time" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editArea(area) {
            document.getElementById('edit_id').value = area.id;
            document.getElementById('edit_area_name').value = area.area_name;
            document.getElementById('edit_shipping_fee').value = area.shipping_fee;
            document.getElementById('edit_estimated_time').value = area.estimated_time;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>

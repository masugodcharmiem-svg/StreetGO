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
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = floatval($_POST['price']);
            $category = sanitize($_POST['category']);
            $image_url = sanitize($_POST['image_url']);
            
            if (!empty($name) && $price > 0) {
                $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $description, $price, $category, $image_url])) {
                    $success = 'Menu item added successfully!';
                } else {
                    $error = 'Failed to add menu item.';
                }
            }
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = floatval($_POST['price']);
            $category = sanitize($_POST['category']);
            $image_url = sanitize($_POST['image_url']);
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, category = ?, image_url = ?, is_available = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $price, $category, $image_url, $is_available, $id])) {
                $success = 'Menu item updated successfully!';
            } else {
                $error = 'Failed to update menu item.';
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = 'Menu item deleted successfully!';
            } else {
                $error = 'Failed to delete menu item.';
            }
        }
    }
}

$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY category, name")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Manage Menu - StreetGo Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-top: 0;">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Menu Items</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Item
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
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menuItems as $item): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"></td>
                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td><small><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</small></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php if ($item['is_available']): ?>
                            <span class="badge bg-success">Available</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Unavailable</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
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
                        <h5 class="modal-title">Add Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₱)</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" list="categories" required>
                                <datalist id="categories">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
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
                        <h5 class="modal-title">Edit Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₱)</label>
                                <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" id="edit_category" class="form-control" list="categories" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" id="edit_image_url" class="form-control">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_available" id="edit_is_available">
                            <label class="form-check-label" for="edit_is_available">Available</label>
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
        function editItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_category').value = item.category;
            document.getElementById('edit_image_url').value = item.image_url;
            document.getElementById('edit_is_available').checked = item.is_available;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>

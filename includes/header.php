<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if database config exists, then require it
$db_path = __DIR__ . '/../config/database.php';
if (file_exists($db_path)) {
    require_once $db_path;
}

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>StreetGo - Authentic Filipino Street Food</title>
    
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6.4.2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <!-- Note: Assuming your project folder is named StreetGO -->
    <link href="/StreetGO/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/StreetGO/index.php">
            <i class="fas fa-motorcycle"></i> StreetGo
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="/StreetGO/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/StreetGO/menu.php">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/StreetGO/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/StreetGO/register.php">Register</a>
                </li>
                <!-- Cart Icon -->
                <li class="nav-item">
                    <a class="nav-link cart-link" href="/StreetGO/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- User Account Links -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="/StreetGO/admin/dashboard.php">Admin Dashboard</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="/StreetGO/profile.php">My Orders</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/StreetGO/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-light btn-sm rounded-pill px-4 text-primary fw-bold" href="/StreetGO/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

            </ul>
        </div>
    </div>
</nav>


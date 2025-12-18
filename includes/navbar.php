<?php
// Prevent error if session is not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate Cart Count (Only if user is logged in and $pdo exists)
$cartCount = 0;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmtCart = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmtCart->execute([$_SESSION['user_id']]);
        $cartCount = $stmtCart->fetchColumn() ?: 0;
    } catch (PDOException $e) {
        // Handle error silently
    }
}

// Function to check active page
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreetGo - Filipino Street Food</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #ff6b35; /* Orange for food appetite */
            --secondary-color: #2d3436;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styling */
        .navbar {
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: var(--primary-color) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--secondary-color);
            margin: 0 10px;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #e85a2b;
            border-color: #e85a2b;
        }

        .cart-badge {
            font-size: 0.75rem;
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
        }

        .cart-icon-wrapper {
            position: relative;
            display: inline-block;
            margin-right: 15px;
            color: var(--secondary-color);
        }

        /* Card Styling from your Menu */
        .food-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background: white;
            transition: transform 0.3s;
            height: 100%;
        }
        
        .food-card:hover {
            transform: translateY(-5px);
        }

        .card-img-wrapper {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="/index.php">
            <i class="fas fa-utensils me-2"></i>StreetGo
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('index.php'); ?>" href="/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('menu.php'); ?>" href="/menu.php">Menu</a>
                </li>
            </ul>

            <!-- Right Side (Auth & Cart) -->
            <ul class="navbar-nav ms-auto align-items-center">
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged In State -->
                    
                    <!-- Cart Icon -->
                    <li class="nav-item">
                        <a href="/cart.php" class="cart-icon-wrapper me-3">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="badge rounded-pill bg-danger cart-badge">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Account'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item" href="/dashboard.php"><i class="fas fa-columns me-2 text-muted"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="/orders.php"><i class="fas fa-receipt me-2 text-muted"></i> My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <!-- Guest State -->
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary rounded-pill px-4" href="/register.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content Wrapper Starts -->
<main style="min-height: 80vh;">
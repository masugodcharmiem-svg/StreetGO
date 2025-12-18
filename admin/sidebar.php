<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <div class="logo">
        <i class="fas fa-motorcycle"></i> StreetGo Admin
    </div>
    <nav>
        <a href="/admin/index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="/admin/orders.php" class="nav-link <?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        <a href="/admin/menu.php" class="nav-link <?php echo $currentPage === 'menu.php' ? 'active' : ''; ?>">
            <i class="fas fa-utensils"></i> Menu Items
        </a>
        <a href="/admin/users.php" class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="/admin/areas.php" class="nav-link <?php echo $currentPage === 'areas.php' ? 'active' : ''; ?>">
            <i class="fas fa-map-marker-alt"></i> Delivery Areas
        </a>
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="/" class="nav-link">
            <i class="fas fa-home"></i> Back to Site
        </a>
        <a href="/logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>

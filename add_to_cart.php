<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Set response header to JSON
header('Content-Type: application/json');

// 1. Check kung naka-login ba ang user
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_item_id'])) {
    $user_id = $_SESSION['user_id'];
    $menu_item_id = (int)$_POST['menu_item_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    try {
        // 2. Check kung naa na ba ni nga item sa cart daan
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND menu_item_id = ?");
        $stmt->execute([$user_id, $menu_item_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Kung naa na, dugangan lang ang quantity
            $new_quantity = $existing['quantity'] + $quantity;
            $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND menu_item_id = ?");
            $updateStmt->execute([$new_quantity, $user_id, $menu_item_id]);
        } else {
            // Kung wala pa, i-insert isip bag-ong item
            $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, menu_item_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $insertStmt->execute([$user_id, $menu_item_id, $quantity]);
        }

        // 3. I-return ang success message
        // Optional: Count total items para sa badge update
        $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $countStmt->execute([$user_id]);
        $totalItems = $countStmt->fetchColumn() ?: 0;

        echo json_encode([
            'status' => 'success', 
            'message' => 'Item added to cart successfully!',
            'cart_count' => $totalItems
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
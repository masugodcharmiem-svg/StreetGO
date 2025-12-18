<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in', 'redirect' => '/StreetGO/register.php']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$action = $input['action'] ?? null;

function getCartCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return (int)$row['total'];
}

try {
    if ($action === 'add') {
        $item_id = (int)$input['item_id'];
        $quantity = (int)$input['quantity'];

        // Check if item exists
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND menu_item_id = ?");
        $stmt->execute([$user_id, $item_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND menu_item_id = ?");
            $stmt->execute([$quantity, $user_id, $item_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, menu_item_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $item_id, $quantity]);
        }

        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo, $user_id)]);
        exit;

    } elseif ($action === 'update') {
        $item_id = (int)$input['item_id'];
        $change = (int)$input['change'];

        $stmt = $pdo->prepare("UPDATE cart SET quantity = GREATEST(quantity + ?, 1) WHERE user_id = ? AND menu_item_id = ?");
        $stmt->execute([$change, $user_id, $item_id]);

        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo, $user_id)]);
        exit;

    } elseif ($action === 'remove') {
        $item_id = (int)$input['item_id'];

        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND menu_item_id = ?");
        $stmt->execute([$user_id, $item_id]);

        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo, $user_id)]);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

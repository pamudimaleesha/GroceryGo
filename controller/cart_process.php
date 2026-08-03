<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please login to add items to cart']);
    exit;
}

// Helper function to check stock availability
function checkStockAvailability($conn, $product_id, $requested_quantity) {
    $stmt = $conn->prepare('SELECT qty FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        return ['status' => false, 'message' => 'Product not found'];
    }
    
    if ($product['qty'] < $requested_quantity) {
        return [
            'status' => false, 
            'message' => 'Not enough stock available. Only ' . $product['qty'] . ' items left',
            'available' => $product['qty']
        ];
    }
    
    return ['status' => true];
}

// Helper function to calculate cart total
function getCartTotal($conn, $user_id) {
    $stmt = $conn->prepare('
        SELECT SUM(c.quantity * p.price) as total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ');
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?: 0;
}

// Helper function to get cart count
function getCartCount($conn, $user_id) {
    $stmt = $conn->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

// Add to Cart
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id > 0) {
        // Check stock availability first
        $stock_check = checkStockAvailability($conn, $product_id, $quantity);
        if (!$stock_check['status']) {
            echo json_encode(['status' => 'error', 'message' => $stock_check['message'], 'cartCount' => getCartCount($conn, $user_id)]);
            exit;
        }

        // Check if product already exists in cart
        $stmt = $conn->prepare('SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$user_id, $product_id]);
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Check stock for updated quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stock_check = checkStockAvailability($conn, $product_id, $new_quantity);
            if (!$stock_check['status']) {
                echo json_encode(['status' => 'error', 'message' => $stock_check['message'], 'cartCount' => getCartCount($conn, $user_id)]);
                exit;
            }
            // Item already exists in cart
            echo json_encode([
                'status' => 'exists',
                'message' => 'This item is already in your cart',
                'current_quantity' => $existing_item['quantity'],
                'cartCount' => getCartCount($conn, $user_id)
            ]);
        } else {
            // Insert new item if product doesn't exist
            $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, ?, NOW())');
            if ($stmt->execute([$user_id, $product_id, $quantity])) {
                $total = getCartTotal($conn, $user_id);
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Product added to cart',
                    'cart_total' => $total,
                    'cartCount' => getCartCount($conn, $user_id)
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart', 'cartCount' => getCartCount($conn, $user_id)]);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product', 'cartCount' => getCartCount($conn, $user_id)]);
    }
}

// Remove from Cart
if (isset($_POST['action']) && $_POST['action'] === 'remove') {
    $user_id = $_SESSION['user_id'];
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;

    if ($cart_id > 0) {
        // Verify the cart item belongs to the user
        $stmt = $conn->prepare('DELETE FROM cart WHERE cart_id = ? AND user_id = ?');
        if ($stmt->execute([$cart_id, $user_id])) {
            $total = getCartTotal($conn, $user_id);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Product removed from cart',
                'cart_total' => $total,
                'cartCount' => getCartCount($conn, $user_id)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove product from cart', 'cartCount' => getCartCount($conn, $user_id)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart item', 'cartCount' => getCartCount($conn, $user_id)]);
    }
}

// Update Quantity
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $user_id = $_SESSION['user_id'];
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($cart_id > 0 && $quantity > 0) {
        // Get product_id from cart item
        $stmt = $conn->prepare('SELECT product_id FROM cart WHERE cart_id = ? AND user_id = ?');
        $stmt->execute([$cart_id, $user_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cart_item) {
            // Check stock availability first
            $stock_check = checkStockAvailability($conn, $cart_item['product_id'], $quantity);
            if (!$stock_check['status']) {
                echo json_encode(['status' => 'error', 'message' => $stock_check['message'], 'cartCount' => getCartCount($conn, $user_id)]);
                exit;
            }

            $stmt = $conn->prepare('UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?');
            if ($stmt->execute([$quantity, $cart_id, $user_id])) {
                $total = getCartTotal($conn, $user_id);
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Cart updated successfully',
                    'cart_total' => $total,
                    'cartCount' => getCartCount($conn, $user_id)
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update cart', 'cartCount' => getCartCount($conn, $user_id)]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cart item not found', 'cartCount' => getCartCount($conn, $user_id)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request', 'cartCount' => getCartCount($conn, $user_id)]);
    }
}

// Clear Cart
if (isset($_POST['action']) && $_POST['action'] === 'clear') {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
    if ($stmt->execute([$user_id])) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Cart cleared successfully',
            'cart_total' => 0,
            'cartCount' => getCartCount($conn, $user_id)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to clear cart', 'cartCount' => getCartCount($conn, $user_id)]);
    }
}
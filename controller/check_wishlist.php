<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Default response
$response = [
    'inWishlist' => false
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

// Validate product ID
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_GET['product_id'];

try {
    // Check if product exists in user's wishlist
    $stmt = $conn->prepare('SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$user_id, $product_id]);
    
    $response['inWishlist'] = $stmt->rowCount() > 0;
    
    echo json_encode($response);
} catch (PDOException $e) {
    // Log error but don't expose details to client
    error_log("Wishlist check error: " . $e->getMessage());
    echo json_encode($response);
}

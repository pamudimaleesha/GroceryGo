<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to add items to wishlist',
        'icon' => 'warning'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Handle clear all wishlist items
    if (isset($_POST['clear'])) {
        $stmt = $conn->prepare('DELETE FROM wishlist WHERE user_id = ?');
        if (!$stmt->execute([$user_id])) {
            throw new PDOException("Failed to clear wishlist");
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Wishlist cleared successfully',
            'icon' => 'success',
            'action' => 'cleared',
            'wishlistCount' => 0
        ]);
        exit;
    }

    // Handle add/remove single item
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        
        // First check if product exists
        $product_check = $conn->prepare('SELECT id FROM products WHERE id = ?');
        $product_check->execute([$product_id]);
        if ($product_check->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Product not found',
                'icon' => 'error'
            ]);
            exit;
        }

        // Check if item already exists in wishlist
        $check_stmt = $conn->prepare('SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?');
        $check_stmt->execute([$user_id, $product_id]);
        $exists = $check_stmt->rowCount() > 0;

        // Handle based on action (remove) or existence in wishlist
        if (isset($_POST['action']) && $_POST['action'] === 'remove' || $exists) {
            // Remove from wishlist
            $delete_stmt = $conn->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
            if (!$delete_stmt->execute([$user_id, $product_id])) {
                throw new PDOException("Failed to remove from wishlist");
            }

            $new_count = getWishlistCount($conn, $user_id);
            echo json_encode([
                'status' => 'success',
                'message' => 'Item removed from your wishlist',
                'icon' => 'success',
                'action' => 'removed',
                'wishlistCount' => $new_count
            ]);
        } else {
            // Add to wishlist
            $stmt = $conn->prepare('INSERT INTO wishlist (user_id, product_id, added_at) VALUES (?, ?, NOW())');
            if (!$stmt->execute([$user_id, $product_id])) {
                throw new PDOException("Failed to add to wishlist");
            }
            
            $new_count = getWishlistCount($conn, $user_id);
            echo json_encode([
                'status' => 'success',
                'message' => 'Item added to your wishlist',
                'icon' => 'success',
                'action' => 'added',
                'wishlistCount' => $new_count
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request',
            'icon' => 'error'
        ]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred while updating wishlist',
        'icon' => 'error'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating wishlist',
        'icon' => 'error'
    ]);
}

function getWishlistCount($conn, $user_id) {
    $stmt = $conn->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}